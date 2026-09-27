<?php
session_start();
include 'conexao.php';

$id_usuario = $_SESSION['usuario_id'] ?? 1; // Exemplo de ID da sessão
$senha_atual = $_POST['senha_atual'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirma_senha = $_POST['confirma_nova_senha'] ?? '';

// 1. Verifica se as novas senhas coincidem
if ($nova_senha !== $confirma_senha) {
    header("Location: alterarSenha.html?status=erro_coincidencia");
    exit;
}

// 2. Busca o utilizador na base de dados para checar a senha antiga
$stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

// 3. Valida se o utilizador existe e se a senha atual confere (usando password_verify)
if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
    // A senha antiga não consta ou está errada no banco de dados!
    header("Location: alterarSenha.html?status=senha_incorreta");
    exit;
}

// 4. Se tudo estiver correto, atualiza com a nova senha
$nova_senha_criptografada = password_hash($nova_senha, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
$update->execute([$nova_senha_criptografada, $id_usuario]);

header("Location: alterarSenha.html?status=sucesso");
exit;
?>