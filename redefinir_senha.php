<?php
require_once 'conexao.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$hash = hash('sha256', $token);
$erro = '';

try {
    $stmt = $pdo->prepare(
        "SELECT idUsuario FROM Usuario
         WHERE tokenRecuperacao = ? AND expiraRecuperacao > NOW()"
    );
    $stmt->execute([$hash]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Não foi possível processar agora. Tente novamente em instantes.');
}

if (!$conta) {
    exit('Link inválido ou expirado. <a href="esqueciSenha.html">Pedir outro</a>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma_senha'] ?? '';

    if ($novaSenha === '' || $novaSenha !== $confirma) {
        $erro = 'As senhas não coincidem ou há campos vazios.';
    } elseif (strlen($novaSenha) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } else {
        // Quem recebeu o link no e-mail provou que o e-mail é seu,
        // então já deixamos o e-mail como confirmado.
        $pdo->prepare(
            "UPDATE Usuario
             SET senha = ?, emailVerificado = 1, tokenRecuperacao = NULL, expiraRecuperacao = NULL
             WHERE idUsuario = ?"
        )->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $conta['idUsuario']]);

        header('Location: login.html?status=senha_alterada');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <link rel="stylesheet" href="style.css">
    <title>GymUp - Nova senha</title>
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <h1>GymUp</h1>
            </div>
            <p>Crie uma nova senha</p>
        </header>

        <main class="app-content">
            <div class="search-card">
                <?php if ($erro): ?>
                    <div class="alerta-feedback erro"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="input-group">
                        <label for="senha">Nova senha</label>
                        <input type="password" id="senha" name="senha" placeholder="********"
                            autocomplete="new-password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirma_senha">Confirmar senha</label>
                        <input type="password" id="confirma_senha" name="confirma_senha" placeholder="********"
                            autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-primary">Salvar nova senha</button>
                </form>
            </div>
        </main>
    </div>
    <script src="mostrar-senha.js"></script>
</body>

</html>