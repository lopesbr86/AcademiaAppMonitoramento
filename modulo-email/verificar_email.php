<?php
require_once __DIR__ . '/../conexao.php';

$token = $_GET['token'] ?? '';

if (strlen($token) === 64) {
    try {
        $stmt = $pdo->prepare(
            "UPDATE Usuario
             SET emailVerificado = 1, tokenVerificacao = NULL
             WHERE tokenVerificacao = ?"
        );
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {
            header('Location: ../login.html?status=verificado');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Erro ao verificar e-mail: ' . $e->getMessage());
    }
}

// Token ausente, inválido ou já usado
header('Location: ../login.html?status=link_invalido');
exit;