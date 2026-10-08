<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

$latUsuario = floatval($_GET['lat'] ?? 0);
$lonUsuario = floatval($_GET['lon'] ?? 0);
$termo_cidade = trim($_GET['cidade'] ?? '');
$termo_academia = trim($_GET['academia'] ?? '');

try {
    // A base da consulta usa WHERE 1=1 para facilitar adicionar os filtros à frente
    $sql = "SELECT 
                a.idAcademia AS id, 
                a.nome, 
                a.endereco, 
                a.latitude, 
                a.longitude, 
                a.cidade,
                a.statusLotacaoAtual,
                (SELECT MAX(r.dataHora) FROM RegistroLotacao r WHERE r.idAcademia = a.idAcademia) AS ultimaAtualizacao
            FROM Academia a WHERE 1=1";
            
    $params = [];

    // Adiciona o filtro da cidade, se o utilizador digitou algo
    if (!empty($termo_cidade)) {
        $sql .= " AND a.cidade LIKE ?";
        $params[] = "%$termo_cidade%";
    }

    // Adiciona o filtro do nome da academia, se o utilizador digitou algo
    if (!empty($termo_academia)) {
        $sql .= " AND a.nome LIKE ?";
        $params[] = "%$termo_academia%";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $academias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $academiasProcessadas = [];

    foreach ($academias as $acab) {
        if (!empty($acab['latitude']) && !empty($acab['longitude']) && $latUsuario != 0) {
            $earthRadius = 6371; 
            
            $dLat = deg2rad($acab['latitude'] - $latUsuario);
            $dLon = deg2rad($acab['longitude'] - $lonUsuario);
            
            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($latUsuario)) * cos(deg2rad($acab['latitude'])) *
                 sin($dLon/2) * sin($dLon/2);
                 
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distanciaKm = $earthRadius * $c;

            $acab['distancia'] = round($distanciaKm, 2); 
        } else {
            $acab['distancia'] = null; 
        }
        $academiasProcessadas[] = $acab;
    }

    usort($academiasProcessadas, function($a, $b) {
        return ($a['distancia'] ?? 9999) <=> ($b['distancia'] ?? 9999);
    });

    echo json_encode($academiasProcessadas);

} catch (PDOException $e) {
    echo json_encode(["erro" => "Erro ao consultar a base de dados: " . $e->getMessage()]);
}
?>