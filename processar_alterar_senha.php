<?php
session_start();
require_once __DIR__ . '/modulo-alterar-senha/autoload.php';

// Só aceita envio do formulário (POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: alterarSenha.html');
    exit;
}

// Sem login na sessão, não há quem trocar a senha.
if (empty($_SESSION['idUsuario'])) {
    header('Location: login.html');
    exit;
}

try {
    require_once 'conexao.php'; // cria a variável $pdo

    $alterador = new AlteradorDeSenha(new UsuarioRepositorio($pdo));
    $alterador->alterar(
        (int) $_SESSION['idUsuario'],
        (string) ($_POST['senha_atual'] ?? ''),
        (string) ($_POST['nova_senha'] ?? ''),
        (string) ($_POST['confirma_nova_senha'] ?? '')
    );

    // Renova o ID da sessão depois de uma mudança sensível.
    session_regenerate_id(true);
    header('Location: alterarSenha.html?status=sucesso');

} catch (AlteracaoSenhaException $e) {
    header('Location: alterarSenha.html?status=' . $e->getStatus());

} catch (Throwable $e) {
    // Detalhes técnicos vão para o log, nunca para a tela.
    error_log('Erro ao alterar senha: ' . $e->getMessage());
    header('Location: alterarSenha.html?status=erro_servidor');
}

exit;
