<?php
// patients.php - CRUD for patients
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['user_id'])) {
    responseJson(["status" => "error", "message" => "Unauthorized."], "401 Unauthorized");
}

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY id DESC");
    responseJson(["status" => "success", "data" => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['name'])) {
        responseJson(["status" => "error", "message" => "name is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("INSERT INTO patients (name, phone, addr, allergy, blood_type, dob, gender) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([
        $data['name'],
        $data['phone']      ?? '',
        $data['addr']       ?? '',
        $data['allergy']    ?? 'None',
        $data['blood_type'] ?? 'O+',
        $data['dob']        ?? '',
        $data['gender']     ?? 'Male'
    ]);
    responseJson(["status" => "success", "id" => $pdo->lastInsertId()]);

} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("UPDATE patients SET name=?, phone=?, addr=?, allergy=?, blood_type=?, dob=?, gender=? WHERE id=?");
    $stmt->execute([
        $data['name'],
        $data['phone'],
        $data['addr'],
        $data['allergy'],
        $data['blood_type'],
        $data['dob'],
        $data['gender'],
        $data['id']
    ]);
    responseJson(["status" => "success"]);

} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("DELETE FROM patients WHERE id=?");
    $stmt->execute([$id]);
    responseJson(["status" => "success"]);
}
?>
