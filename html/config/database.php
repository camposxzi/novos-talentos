
<?php

$host = 'localhost';
$port = '5432';
$dbname = 'novos_talentos';
$user = 'postgres';
$password = 'postgre';
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
