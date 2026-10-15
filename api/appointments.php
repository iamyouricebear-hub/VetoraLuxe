<?php
// appointments.php - CRUD for appointments
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['user_id'])) {
    responseJson(["status" => "error", "message" => "Unauthorized."], "401 Unauthorized");
}

if ($method === 'GET') {
    $query = "
        SELECT a.*, p.name AS patient_name, d.name AS doctor_name
        FROM appointments a
        LEFT JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors  d ON a.doctor_id  = d.id
        ORDER BY a.apt_date DESC, a.apt_time ASC
    ";
    $stmt = $pdo->query($query);
    responseJson(["status" => "success", "data" => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['apt_date']) || empty($data['apt_time'])) {
        responseJson(["status" => "error", "message" => "patient_id, doctor_id, apt_date, and apt_time are required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, apt_date, apt_time, status) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $data['patient_id'],
        $data['doctor_id'],
        $data['apt_date'],
        $data['apt_time'],
        $data['status'] ?? 'Scheduled'
    ]);
    responseJson(["status" => "success", "id" => $pdo->lastInsertId()]);

} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("UPDATE appointments SET apt_date=?, apt_time=?, status=? WHERE id=?");
    $stmt->execute([$data['apt_date'], $data['apt_time'], $data['status'], $data['id']]);
    responseJson(["status" => "success"]);

} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=?");
    $stmt->execute([$id]);
    responseJson(["status" => "success"]);
}
?>
