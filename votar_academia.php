<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

// Verifica se o utilizador estс realmente logado
if (!isset($_SESSION['idUsuario']) || empty($_SESSION['idUsuario'])) {
    echo json_encode([
        "sucesso" => false, 
        "erro" => "Precisa de estar com sessуo iniciada para realizar uma votaчуo."
    ]);
    exit;
}

$idUsuario = $_SESSION['idUsuario'];
$idAcademia = $_POST['idAcademia'] ?? null;
$nivelLotacao = $_POST['status_lotacao'] ?? null;

if (!empty($idAcademia) && !empty($nivelLotacao)) {
    try {
        // Insere o voto na tabela de registo associado ao utilizador autenticado
        $stmt = $pdo->prepare("INSERT INTO RegistroLotacao (idAcademia, idUsuario, nivelLotacao, dataHora, presencaValidada) VALUES (?, ?, ?, NOW(), 0)");
        $stmt->execute([$idAcademia, $idUsuario, $nivelLotacao]);

        // Atualiza o status atual na tabela Academia
        $stmtUpdate = $pdo->prepare("UPDATE Academia SET statusLotacaoAtual = ? WHERE idAcademia = ?");
        $stmtUpdate.execute([$nivelLotacao, $idAcademia]);

        echo json_encode([
            "sucesso" => true, 
            "mensagem" => "Voto registado com sucesso!"
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["sucesso" => false, "erro" => "Erro na base de dados: " . $e->getMessage()]);
        exit;
    }
}

echo json_encode(["sucesso" => false, "erro" => "Dados incompletos ou invсlidos."]);
?>