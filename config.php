<?php

use PSpell\Config;

$host = 'localhost';
$db   = 'blog_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    exit('Database error: ' . $e->getMessage());
}

$secretKey = 'your-secret-key'; // Use env variable in production

Configuration::instance([
    'cloud_name' => 'dbsnwi990',
    'api_key'    => '677652729849593',
    'api_secret' => '**********',
    ],
    'url' => [
        'secure' => true
    ]
]);
?>
