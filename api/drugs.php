<?php
// drugs.php - CRUD for drugs / medicine stock
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['user_id'])) {
    responseJson(["status" => "error", "message" => "Unauthorized."], "401 Unauthorized");
}

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM drugs ORDER BY name ASC");
    responseJson(["status" => "success", "data" => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['name'])) {
        responseJson(["status" => "error", "message" => "name is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("INSERT INTO drugs (name, stock, price) VALUES (?,?,?)");
    $stmt->execute([
        $data['name'],
        $data['stock'] ?? 0,
        $data['price'] ?? 0
    ]);
    responseJson(["status" => "success", "id" => $pdo->lastInsertId()]);

} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("UPDATE drugs SET name=?, stock=?, price=? WHERE id=?");
    $stmt->execute([$data['name'], $data['stock'], $data['price'], $data['id']]);
    responseJson(["status" => "success"]);

} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("DELETE FROM drugs WHERE id=?");
    $stmt->execute([$id]);
    responseJson(["status" => "success"]);
}
?>
