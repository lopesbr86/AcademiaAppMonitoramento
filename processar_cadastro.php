<?php
require_once 'conexao.php';
require_once 'modulo-email/enviar_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirma_senha)) {
        header("Location: cadastro.html?status=erro_senha");
        exit;
    }

    // Valida o formato do e-mail (a validação do navegador pode ser burlada)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: cadastro.html?status=erro_formato");
        exit;
    }

    if ($senha !== $confirma_senha) {
        header("Location: cadastro.html?status=erro_senha");
        exit;
    }

    try {
        $stmtCheck = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE email = ?");
        $stmtCheck->execute([$email]);

        if ($stmtCheck->rowCount() > 0) {
            header("Location: cadastro.html?status=erro_email");
            exit;
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32)); // 64 caracteres, impossível de adivinhar

        $stmtInsert = $pdo->prepare(
            "INSERT INTO Usuario (nome, email, senha, tokenVerificacao) VALUES (?, ?, ?, ?)"
        );
        $stmtInsert->execute([$nome, $email, $senha_hash, $token]);

        // Envia o e-mail de confirmação
        $enviado = false;
        try {
            $link = rtrim(env('APP_URL'), '/') . '/modulo-email/verificar_email.php?token=' . $token;
            $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');

            $html = '<h2>Bem-vindo ao GymUp, ' . $nomeSeguro . '!</h2>'
                . '<p>Clique no botão abaixo para confirmar seu e-mail:</p>'
                . '<p><a href="' . $link . '" style="background:#0284c7;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;">Confirmar e-mail</a></p>'
                . '<p>Se você não criou uma conta, ignore esta mensagem.</p>';

            $enviado = enviarEmail($email, 'Confirme seu e-mail no GymUp', $html);
        } catch (RuntimeException $e) {
            error_log($e->getMessage()); // ex.: APP_URL não definido no .env
        }

        header("Location: cadastro.html?status=" . ($enviado ? 'sucesso' : 'erro_envio'));
        exit;

    } catch (PDOException $e) {
        header("Location: cadastro.html?status=erro_conexao");
        exit;
    }
}
?>