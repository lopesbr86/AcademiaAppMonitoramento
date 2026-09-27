<?php
// Configuraчѕes de acesso р base de dados MySQL na nuvem (Aiven)
$host = 'gymupapp-bd-gymup-app.l.aivencloud.com';
$porta = '22812';
$db   = 'gymup_db';
$usuario = 'avnadmin';
$senha = 'AVNS_9bBtxq5co9SxfUDibXI';

try {
    $dsn = "mysql:host=$host;port=$porta;dbname=$db;charset=utf8mb4";
    
    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $usuario, $senha, $opcoes);

} catch (\PDOException $e) {
    throw new \PDOException("Erro na ligaчуo com a base de dados: " . $e->getMessage(), (int)$e->getCode());
}
?>