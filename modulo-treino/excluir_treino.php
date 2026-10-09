<?php
// Exclui um treino do usuário logado.
// Recebe JSON: { "idTreino": 12 }
session_start();
header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $dados): void {
    http_response_code($codigo);
    echo json_encode($dados);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['erro' => 'Método não permitido.']);
}

if (empty($_SESSION['idUsuario'])) {
    responder(401, ['erro' => 'Faça login para continuar.']);
}

$entrada  = json_decode(file_get_contents('php://input'), true);
$idTreino = filter_var($entrada['idTreino'] ?? null, FILTER_VALIDATE_INT);

if (!$idTreino || $idTreino < 1) {
    responder(422, ['erro' => 'Treino inválido.']);
}

require_once __DIR__ . '/../conexao.php'; // conexao.php fica na pasta acima (raiz do projeto)

try {
    // O "AND idUsuario" garante que cada pessoa só exclui os próprios treinos
    $stmt = $pdo->prepare(
        'DELETE FROM Treino WHERE idTreino = :idTreino AND idUsuario = :idUsuario'
    );
    $stmt->execute([
        ':idTreino'  => $idTreino,
        ':idUsuario' => (int) $_SESSION['idUsuario'],
    ]);

    if ($stmt->rowCount() === 0) {
        responder(404, ['erro' => 'Treino não encontrado.']);
    }

    responder(200, ['sucesso' => true]);
} catch (PDOException $e) {
    error_log('excluir_treino: ' . $e->getMessage());
    responder(500, ['erro' => 'Não foi possível excluir o treino.']);
}