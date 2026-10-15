<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';
session_start();

// Pega o ID do utilizador logado para verificar os favoritos
$idUsuario = $_SESSION['idUsuario'] ?? $_SESSION['usuario_id'] ?? 0;

$latUsuario = floatval($_GET['lat'] ?? 0);
$lonUsuario = floatval($_GET['lon'] ?? 0);
$termo_cidade = trim($_GET['cidade'] ?? '');
$termo_academia = trim($_GET['academia'] ?? '');

try {
    // Adicionamos a subconsulta EXISTS para verificar se a academia é favorita do utilizador
    $sql = "SELECT 
                a.idAcademia AS id, 
                a.nome, 
                a.endereco, 
                a.latitude, 
                a.longitude, 
                a.cidade,
                a.statusLotacaoAtual,
                (SELECT MAX(r.dataHora) FROM RegistroLotacao r WHERE r.idAcademia = a.idAcademia) AS ultimaAtualizacao,
                EXISTS(SELECT 1 FROM Favorito f WHERE f.idAcademia = a.idAcademia AND f.idUsuario = :idUsuario) AS ehFavorita
            FROM Academia a WHERE 1=1";
            
    $params = ['idUsuario' => $idUsuario];

    // Adiciona o filtro da cidade, se o utilizador digitou algo
    if (!empty($termo_cidade)) {
        $sql .= " AND a.cidade LIKE :cidade";
        $params['cidade'] = "%$termo_cidade%";
    }

    // Adiciona o filtro do nome da academia, se o utilizador digitou algo
    if (!empty($termo_academia)) {
        $sql .= " AND a.nome LIKE :academia";
        $params['academia'] = "%$termo_academia%";
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
        
        // Converte o resultado do EXISTS para booleano real (true/false)
        $acab['ehFavorita'] = (bool)$acab['ehFavorita'];
        
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