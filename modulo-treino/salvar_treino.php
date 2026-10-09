<?php
// Grava um treino para o usuário logado.
// Recebe JSON: { "idAcademia": 1, "dataInicio": "2026-10-09 18:30:00", "duracaoMinutos": 60 }
session_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

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

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    responder(400, ['erro' => 'Dados inválidos.']);
}

// idUsuario SEMPRE vem da sessão, nunca do navegador
$idUsuario      = (int) $_SESSION['idUsuario'];
$idAcademia     = filter_var($entrada['idAcademia'] ?? null, FILTER_VALIDATE_INT);
$duracaoMinutos = filter_var($entrada['duracaoMinutos'] ?? null, FILTER_VALIDATE_INT);
$dataInicio     = (string) ($entrada['dataInicio'] ?? '');

if (!$idAcademia || $idAcademia < 1) {
    responder(422, ['erro' => 'Escolha a academia onde você treinou.']);
}
if ($duracaoMinutos === false || $duracaoMinutos < 1 || $duracaoMinutos > 600) {
    responder(422, ['erro' => 'A duração deve ser entre 1 e 600 minutos.']);
}

$data = DateTime::createFromFormat('Y-m-d H:i:s', $dataInicio);
$erros = DateTime::getLastErrors();
if (!$data || ($erros && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
    responder(422, ['erro' => 'Data e horário de início inválidos.']);
}
if ($data > new DateTime()) {
    responder(422, ['erro' => 'O início do treino não pode estar no futuro.']);
}

require_once __DIR__ . '/../conexao.php'; // conexao.php fica na pasta acima (raiz do projeto)

try {
    // Confere se a academia existe (evita erro de chave estrangeira)
    $chk = $pdo->prepare('SELECT 1 FROM Academia WHERE idAcademia = :idAcademia');
    $chk->execute([':idAcademia' => $idAcademia]);
    if (!$chk->fetchColumn()) {
        responder(422, ['erro' => 'Academia não encontrada.']);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO Treino (idUsuario, idAcademia, dataInicio, duracaoMinutos)
         VALUES (:idUsuario, :idAcademia, :dataInicio, :duracaoMinutos)'
    );
    $stmt->execute([
        ':idUsuario'      => $idUsuario,
        ':idAcademia'     => $idAcademia,
        ':dataInicio'     => $data->format('Y-m-d H:i:s'),
        ':duracaoMinutos' => $duracaoMinutos,
    ]);

    responder(201, ['sucesso' => true, 'idTreino' => (int) $pdo->lastInsertId()]);
} catch (PDOException $e) {
    error_log('salvar_treino: ' . $e->getMessage());
    responder(500, ['erro' => 'Não foi possível salvar o treino.']);
}