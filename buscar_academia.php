<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$cidade = trim($_GET['cidade'] ?? '');
$termo_academia = trim($_GET['academia'] ?? '');

if (empty($cidade)) {
    echo json_encode(["erro" => "Por favor, informe a cidade para realizar a busca."]);
    exit;
}

try {
    // Consulta buscando a academia e a data do último registo na tabela RegistroLotacao
    $sql = "SELECT 
                a.idAcademia AS id, 
                a.nome, 
                a.endereco, 
                a.latitude, 
                a.longitude, 
                a.statusLotacaoAtual,
                (SELECT MAX(r.dataHora) FROM RegistroLotacao r WHERE r.idAcademia = a.idAcademia) AS ultimaAtualizacao
            FROM Academia a 
            WHERE a.endereco LIKE ?";
            
    $params = ["%$cidade%"];

    if (!empty($termo_academia)) {
        $sql .= " AND a.nome LIKE ?";
        $params[] = "%$termo_academia%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $academias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($academias)) {
        echo json_encode($academias);
    } else {
        echo json_encode([]);
    }

} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao consultar a base de dados: " . $e->getMessage()]);
}
?>