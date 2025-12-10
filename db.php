<?php
// db.php - simple PDO connection (configure your DB params here)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'italie_db';
$DB_USER = 'root';
$DB_PASS = 'YoUs1708';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // For production, hide details and log errors instead
    echo "Erreur de connexion à la base de données.";
    error_log($e->getMessage());
    exit;
}
