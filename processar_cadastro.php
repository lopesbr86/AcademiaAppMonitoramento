<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirma_senha)) {
        header("Location: cadastro.html?status=erro_senha");
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

        $stmtInsert = $pdo->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
        $stmtInsert->execute([$nome, $email, $senha_hash]);

        header("Location: cadastro.html?status=sucesso");
        exit;

    } catch (PDOException $e) {
        header("Location: cadastro.html?status=erro_conexao");
        exit;
    }
}
?>