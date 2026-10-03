<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

const NIVEIS = ['Baixo' => 1, 'Moderado' => 2, 'Alto' => 3];
const JANELA_MINUTOS = 90;

function responder(array $dados, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($dados);
    exit;
}

// Converte a média dos votos recentes (1 a 3) em categoria
function statusPorMedia(float $media): string
{
    if ($media <= 1.5) {
        return 'Baixo';
    }
    if ($media <= 2.3) {
        return 'Moderado';
    }
    return 'Alto';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(["sucesso" => false, "erro" => "Método não permitido."], 405);
}

if (empty($_SESSION['idUsuario'])) {
    responder(["sucesso" => false, "erro" => "Você precisa estar logado para votar."], 401);
}

$idUsuario     = (int) $_SESSION['idUsuario'];
$idAcademia    = filter_input(INPUT_POST, 'idAcademia', FILTER_VALIDATE_INT);
$statusLotacao = $_POST['status_lotacao'] ?? '';

if (!$idAcademia || !is_string($statusLotacao) || !array_key_exists($statusLotacao, NIVEIS)) {
    responder(["sucesso" => false, "erro" => "Dados incompletos ou inválidos."], 400);
}

try {
    $stmt = $pdo->prepare("SELECT 1 FROM Academia WHERE idAcademia = ?");
    $stmt->execute([$idAcademia]);

    if (!$stmt->fetchColumn()) {
        responder(["sucesso" => false, "erro" => "Academia não encontrada."], 404);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO RegistroLotacao (idAcademia, idUsuario, nivelLotacao, dataHora, presencaValidada)
         VALUES (?, ?, ?, NOW(), 0)"
    );
    $stmt->execute([$idAcademia, $idUsuario, NIVEIS[$statusLotacao]]);

    $stmt = $pdo->prepare(
        "SELECT AVG(nivelLotacao) FROM RegistroLotacao
         WHERE idAcademia = ? AND dataHora >= (NOW() - INTERVAL " . JANELA_MINUTOS . " MINUTE)"
    );
    $stmt->execute([$idAcademia]);
    $statusAtual = statusPorMedia((float) $stmt->fetchColumn());

    $stmt = $pdo->prepare("UPDATE Academia SET statusLotacaoAtual = ? WHERE idAcademia = ?");
    $stmt->execute([$statusAtual, $idAcademia]);

    $pdo->commit();

    responder(["sucesso" => true, "mensagem" => "Voto registrado com sucesso!", "status" => $statusAtual]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao registrar voto: " . $e->getMessage());
    responder(["sucesso" => false, "erro" => "Não foi possível registrar o voto."], 500);
}