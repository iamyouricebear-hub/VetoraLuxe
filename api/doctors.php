<?php
// doctors.php - CRUD for doctors
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['user_id'])) {
    responseJson(["status" => "error", "message" => "Unauthorized."], "401 Unauthorized");
}

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM doctors ORDER BY id DESC");
    responseJson(["status" => "success", "data" => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') {
        responseJson(["status" => "error", "message" => "Forbidden."], "403 Forbidden");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['name']) || empty($data['specialization'])) {
        responseJson(["status" => "error", "message" => "name and specialization are required."], "400 Bad Request");
    }

    $img = $data['image_url'] ?? 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=100&h=100&fit=crop';

    $stmt = $pdo->prepare("INSERT INTO doctors (name, specialization, practice_hours, status, image_url) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $data['name'],
        $data['specialization'],
        $data['practice_hours'] ?? 'Mon-Fri 09:00-17:00',
        'Active',
        $img
    ]);
    responseJson(["status" => "success", "id" => $pdo->lastInsertId()]);

} elseif ($method === 'PUT') {
    if ($_SESSION['role'] !== 'admin') {
        responseJson(["status" => "error", "message" => "Forbidden."], "403 Forbidden");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("UPDATE doctors SET name=?, specialization=?, practice_hours=?, status=?, image_url=? WHERE id=?");
    $stmt->execute([
        $data['name'],
        $data['specialization'],
        $data['practice_hours'],
        $data['status'] ?? 'Active',
        $data['image_url'],
        $data['id']
    ]);
    responseJson(["status" => "success"]);

} elseif ($method === 'DELETE') {
    if ($_SESSION['role'] !== 'admin') {
        responseJson(["status" => "error", "message" => "Forbidden."], "403 Forbidden");
    }

    $id = $_GET['id'] ?? 0;

    if (!$id) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id=?");
    $stmt->execute([$id]);
    responseJson(["status" => "success"]);
}
?>
