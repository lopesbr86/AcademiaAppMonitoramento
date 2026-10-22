<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $statusLotacao = trim($_POST['status_lotacao'] ?? 'Moderado');

    // Validação de campos vazios
    if (empty($nome) || empty($cidade) || empty($rua) || empty($numero)) {
        header("Location: cadastrar_academia.html?erro=campos_vazios");
        exit;
    }

    // Junta a rua e o número para formar o endereço completo
    $endereco = $rua . ', ' . $numero;

    try {
        // Verifica se já existe uma academia com o mesmo nome na mesma cidade ou endereço idêntico
        $stmt = $pdo->prepare("
            SELECT idAcademia FROM Academia 
            WHERE (LOWER(nome) = LOWER(?) AND LOWER(cidade) = LOWER(?)) 
            OR (LOWER(endereco) = LOWER(?) AND LOWER(cidade) = LOWER(?))
        ");
        $stmt->execute([$nome, $cidade, $endereco, $cidade]);
        
        if ($stmt->fetch()) {
            // Já existe! Retorna com erro de duplicada
            header("Location: cadastrar_academia.html?erro=duplicada");
            exit;
        }

        // Se não existir, insere a nova academia na base de dados
        $stmtInsert = $pdo->prepare("
            INSERT INTO Academia (nome, cidade, endereco, statusLotacaoAtual) 
            VALUES (?, ?, ?, ?)
        ");
        $stmtInsert->execute([$nome, $cidade, $endereco, $statusLotacao]);
        
        // Redireciona de volta para o cadastro com status de sucesso
        header("Location: cadastrar_academia.html?status=sucesso");
        exit;

    } catch (PDOException $e) {
        error_log("Erro ao cadastrar academia: " . $e->getMessage());
        header("Location: cadastrar_academia.html?status=erro");
        exit;
    }
} else {
    header("Location: cadastrar_academia.html");
    exit;
}