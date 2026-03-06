<?php
$host = '127.0.0.1';
$db   = 'test_db';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

if (!extension_loaded('pdo')) {
    die('PDO extension is not loaded.');
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    3 => 2, // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    19 => 2, // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    3 => false, // Эмуляция подготовленных запросов
];

try {
    $pdo = new \PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
