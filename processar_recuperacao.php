<?php
require_once 'conexao.php';
require_once 'modulo-email/enviar_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: esqueciSenha.html?status=erro_formato");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT idUsuario FROM Usuario WHERE email = ?");
        $stmt->execute([$email]);
        $conta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conta) {
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token); // no banco fica só o hash do token

            $pdo->prepare(
                "UPDATE Usuario
                 SET tokenRecuperacao = ?, expiraRecuperacao = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                 WHERE idUsuario = ?"
            )->execute([$hash, $conta['idUsuario']]);

            try {
                $link = rtrim(env('APP_URL'), '/') . '/redefinir_senha.php?token=' . $token;

                $html = '<h2>Redefinir senha</h2>'
                    . '<p>Clique no botão para criar uma nova senha. O link vale por 1 hora.</p>'
                    . '<p><a href="' . $link . '" style="background:#0284c7;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;">Redefinir senha</a></p>'
                    . '<p>Se você não pediu isso, ignore esta mensagem.</p>';

                enviarEmail($email, 'Redefinir sua senha no GymUp', $html);
            } catch (RuntimeException $e) {
                error_log($e->getMessage());
            }
        }

        // Resposta igual exista o e-mail ou não, para ninguém descobrir quem tem conta
        header("Location: esqueciSenha.html?status=enviado");
        exit;

    } catch (PDOException $e) {
        header("Location: esqueciSenha.html?status=erro_conexao");
        exit;
    }
}
?>