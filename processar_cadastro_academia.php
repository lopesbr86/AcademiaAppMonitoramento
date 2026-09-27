<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');

    if (!empty($nome) && !empty($endereco)) {
        try {
            // Insere a academia com o status de lotaчуo como NULL (sem votaчѕes)
            $stmt = $pdo->prepare("INSERT INTO Academia (nome, endereco, latitude, longitude, statusLotacaoAtual) VALUES (?, ?, 0, 0, NULL)");
            $stmt->execute([$nome, $endereco]);

            header("Location: cadastrar_academia.html?status=sucesso");
            exit;
        } catch (PDOException $e) {
            header("Location: cadastrar_academia.html?status=erro");
            exit;
        }
    } else {
        header("Location: cadastrar_academia.html?status=erro");
        exit;
    }
}
?>