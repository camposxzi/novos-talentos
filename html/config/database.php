<?php

$config = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'novos_talentos',
    'user' => 'postgres',
    'password' => ''
];

$arquivoLocal = __DIR__ . '/database.local.php';

if (file_exists($arquivoLocal)) {
    $config = array_merge($config, require $arquivoLocal);
}

// Configurações do Render
$variaveis = [
    'host' => 'DB_HOST',
    'port' => 'DB_PORT',
    'dbname' => 'DB_NAME',
    'user' => 'DB_USER',
    'password' => 'DB_PASSWORD'
];

foreach ($variaveis as $campo => $variavel) {
    $valor = getenv($variavel);

    if ($valor !== false && $valor !== '') {
        $config[$campo] = $valor;
    }
}

try {
    $pdo = new PDO(
        "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
        $config['user'],
        $config['password']
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro na conexão com PostgreSQL: " . $e->getMessage());
    die("Não foi possível conectar ao banco de dados.");
}