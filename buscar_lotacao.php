<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$id_academia = $_GET['id_academia'] ?? 0;

if (empty($id_academia)) {
    echo json_encode(["erro" => "ID da academia inválido"]);
    exit;
}

try {
    // A Regra de Negócio: Consulta apenas votos feitos nos últimos 90 minutos
    $sql = "SELECT AVG(nivel_lotacao) as media_lotacao, COUNT(*) as total_votos 
            FROM registros_lotacao 
            id_academia = ? AND data_hora >= (NOW() - INTERVAL 90 MINUTE)";
            
   
    $sql_correto = "SELECT AVG(nivel_lotacao) as media_lotacao, COUNT(*) as total_votos 
                    FROM registros_lotacao 
                    WHERE id_academia = ? AND data_hora >= (NOW() - INTERVAL 90 MINUTE)";

    $stmt = $pdo->prepare($sql_correto);
    $stmt->execute([$id_academia]);
    $resultado = $stmt->fetch();

    $total_votos = intval($resultado['total_votos']);
    
    if ($total_votos === 0) {
        $status = "Sem dados recentes";
        $cor = "cinza";
    } else {
        $media = floatval($resultado['media_lotacao']);
        
        // Define o status visual baseado na média dos votos recentes
        if ($media <= 1.5) {
            $status = "🟢 Vazia / Tranquila";
            $cor = "verde";
        } elseif ($media <= 2.3) {
            $status = "🟡 Moderada";
            $cor = "amarelo";
        } else {
            $status = "🔴 Lotada";
            $cor = "vermelho";
        }
    }

    // Retorna os dados em JSON para o JavaScript consumir na tela
    echo json_encode([
        "status" => $status,
        "cor" => $cor,
        "total_votos_recentes" => $total_votos
    ]);

} catch (PDOException $e) {
    echo json_encode(["erro" => $e->getMessage()]);
}
?>