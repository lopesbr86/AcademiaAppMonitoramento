<?php
// Carrega as variáveis do arquivo .env (se existir).
// Variáveis já definidas no servidor/hospedagem têm prioridade.
function carregarEnv(string $caminho): void
{
    if (!is_readable($caminho)) {
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor, " \t\"'");

        if (getenv($chave) === false) {
            putenv("$chave=$valor");
        }
    }
}

// Lê uma variável obrigatória; falha se não existir.
function env(string $chave): string
{
    $valor = getenv($chave);

    if ($valor === false || $valor === '') {
        throw new RuntimeException("Variável de ambiente $chave não definida.");
    }

    return $valor;
}

carregarEnv(__DIR__ . '/.env');

$host    = env('DB_HOST');
$porta   = env('DB_PORT');
$db      = env('DB_NAME');
$usuario = env('DB_USER');
$senha   = env('DB_PASS');

try {
    $dsn = "mysql:host=$host;port=$porta;dbname=$db;charset=utf8mb4";

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);

} catch (PDOException $e) {
    throw new PDOException("Erro na ligação com a base de dados: " . $e->getMessage(), (int) $e->getCode());
}