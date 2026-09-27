<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: login.html?status=erro_campos");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['idUsuario'] = $usuario['idUsuario']; 
            $_SESSION['nomeUsuario'] = $usuario['nome'];

            header("Location: index.html?status=sucesso");
            exit;
        } else {
            header("Location: login.html?status=erro_login");
            exit;
        }

    } catch (PDOException $e) {
        header("Location: login.html?status=erro_conexao");
        exit;
    }
}
?>