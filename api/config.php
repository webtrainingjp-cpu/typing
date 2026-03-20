<?php

declare(strict_types=1);

$config = [
    'db' => [
        'dsn' => 'mysql:host=mysql328.phy.lolipop.lan;dbname=LAA1514303-typing;charset=utf8mb4',
        'user' => 'LAA1514303',
        'pass' => 'KugaBIzi73d5VGSD',
        'schema' => 'LAA1514303-typing',
    ],
];

$localConfigPath = __DIR__ . '/config.local.php';

if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $config = array_replace_recursive($config, $localConfig);
    }
}

return $config;
