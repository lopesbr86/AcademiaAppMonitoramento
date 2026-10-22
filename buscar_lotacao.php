<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

$id_academia = $_GET['id_academia'] ?? 0;

if (empty($id_academia)) {
    echo json_encode(["erro" => "ID da academia inválido"]);
    exit;
}

try {
    // 0. Limpeza automática: Remove votos com mais de 48 horas para evitar poluição no banco
    $stmtLimpeza = $pdo->prepare("DELETE FROM RegistroLotacao WHERE dataHora < (NOW() - INTERVAL 48 HOUR)");
    $stmtLimpeza->execute();

    // 1. Tenta calcular a média dos votos feitos nos últimos 90 minutos
    $sql = "SELECT AVG(nivelLotacao) as media_lotacao, COUNT(*) as total_votos 
            FROM RegistroLotacao 
            WHERE idAcademia = ? AND dataHora >= (NOW() - INTERVAL 90 MINUTE)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_academia]);
    $resultado = $stmt->fetch();

    $total_votos = intval($resultado['total_votos']);
    
    if ($total_votos > 0) {
        $media = floatval($resultado['media_lotacao']);
        
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
    } else {
        // 2. Fallback: Puxa a última informação DESDE QUE seja de hoje (últimas 24 horas no máximo)
        $sqlUltimo = "SELECT nivelLotacao, dataHora,
                    TIMESTAMPDIFF(MINUTE, dataHora, NOW()) as minutos_atras 
                    FROM RegistroLotacao 
                    WHERE idAcademia = ? AND dataHora >= (NOW() - INTERVAL 24 HOUR)
                    ORDER BY dataHora DESC LIMIT 1";
                    
        $stmtUltimo = $pdo->prepare($sqlUltimo);
        $stmtUltimo->execute([$id_academia]);
        $ultimoVoto = $stmtUltimo->fetch();

        if (!$ultimoVoto) {
            // Se não houver votos nem nas últimas 24h, limpa o status para não mostrar dados obsoletos
            $status = "Sem dados recentes";
            $cor = "cinza";
        } else {
            $nivel = (int)$ultimoVoto['nivelLotacao'];
            $minutos = (int)$ultimoVoto['minutos_atras'];
            
            if ($minutos < 60) {
                $tempoTexto = "há {$minutos} min";
            } else {
                $horas = floor($minutos / 60);
                $tempoTexto = "há {$horas}h";
            }

            if ($nivel === 1) {
                $status = "🟢 Vazia ({$tempoTexto})";
                $cor = "cinza";
            } elseif ($nivel === 2) {
                $status = "🟡 Moderada ({$tempoTexto})";
                $cor = "cinza";
            } else {
                $status = "🔴 Lotada ({$tempoTexto})";
                $cor = "cinza";
            }
        }
    }

    echo json_encode([
        "status" => $status,
        "cor" => $cor,
        "total_votos_recentes" => $total_votos
    ]);

} catch (PDOException$e) {
    echo json_encode(["erro" => $e->getMessage()]);
}
?>