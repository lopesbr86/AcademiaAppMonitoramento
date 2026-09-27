<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado (ajuste a variável de sessão conforme o seu login, ex: $_SESSION['email'] ou $_SESSION['usuario_id'])
if (!isset($_SESSION['email'])) {
    // Para testes iniciais onde a sessão ainda não foi iniciada no login, 
    // podemos direcionar para o login ou simular o apagado pelo último e-mail.
    header("Location: login.html");
    exit;
}

$email_usuario = $_SESSION['email'];

try {
    // Prepara o comando SQL para excluir o registo da base de dados na Aiven
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE email = ?");
    $stmt->execute([$email_usuario]);

    // Destrói a sessão e redireciona para o cadastro/login com aviso
    session_destroy();
    header("Location: cadastro.html?status=conta_excluida");
    exit;

} catch (PDOException $e) {
    header("Location: perfil.html?status=erro_exclusao");
    exit;
}
?>