<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';
session_start();

$idUsuario = $_SESSION['idUsuario'] ?? $_SESSION['usuario_id'] ?? null;
$idAcademia = $_GET['id'] ?? null;

if (!$idUsuario || !$idAcademia) {
    echo json_encode(["sucesso" => false, "erro" => "Utilizador não autenticado ou ID inválido."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Favorito WHERE idUsuario = :idUsuario AND idAcademia = :idAcademia");
    $stmt->execute(['idUsuario' => $idUsuario, 'idAcademia' => $idAcademia]);
    $favorito = $stmt->fetch();

    if ($favorito) {
        $stmtDel = $pdo->prepare("DELETE FROM Favorito WHERE idUsuario = :idUsuario AND idAcademia = :idAcademia");
        $stmtDel->execute(['idUsuario' => $idUsuario, 'idAcademia' => $idAcademia]);
        echo json_encode(["sucesso" => true, "favorito" => false]);
    } else {
        // Remove anterior para manter apenas uma favorita principal (ou remova esta linha se preferir múltiplas)
        $pdo->prepare("DELETE FROM Favorito WHERE idUsuario = :idUsuario")->execute(['idUsuario' => $idUsuario]);
        
        $stmtIns = $pdo->prepare("INSERT INTO Favorito (idUsuario, idAcademia) VALUES (:idUsuario, :idAcademia)");
        $stmtIns->execute(['idUsuario' => $idUsuario, 'idAcademia' => $idAcademia]);
        echo json_encode(["sucesso" => true, "favorito" => true]);
    }
} catch (PDOException $e) {
    echo json_encode(["sucesso" => false, "erro" => $e->getMessage()]);
}
?>