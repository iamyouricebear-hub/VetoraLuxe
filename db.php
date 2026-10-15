<?php
// db.php - Production Ready MySQL Connection
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'vetora_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Database connection error."]);
    exit;
}

function responseJson($data, $statusLine = '200 OK') {
    header("HTTP/1.1 $statusLine");
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}
?>