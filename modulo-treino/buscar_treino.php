<?php
// Devolve os treinos do usuário logado (mais recentes primeiro).
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['idUsuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Faça login para continuar.']);
    exit;
}

require_once __DIR__ . '/../conexao.php'; // conexao.php fica na pasta acima (raiz do projeto)

try {
    $stmt = $pdo->prepare(
        'SELECT idTreino, idUsuario, idAcademia, dataInicio,
                COALESCE(duracaoMinutos, 0) AS duracaoMinutos
           FROM Treino
          WHERE idUsuario = :idUsuario
          ORDER BY dataInicio DESC
          LIMIT 200'
    );
    $stmt->execute([':idUsuario' => (int) $_SESSION['idUsuario']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    error_log('buscar_treinos: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Não foi possível carregar seus treinos.']);
}