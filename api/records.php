<?php
// records.php - CRUD for medical records
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['user_id'])) {
    responseJson(["status" => "error", "message" => "Unauthorized."], "401 Unauthorized");
}

if ($method === 'GET') {
    $patient_id = $_GET['patient_id'] ?? 0;

    if (!$patient_id) {
        responseJson(["status" => "error", "message" => "patient_id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("
        SELECT r.*, d.name AS doctor_name
        FROM medical_records r
        LEFT JOIN doctors d ON r.doctor_id = d.id
        WHERE r.patient_id = ?
        ORDER BY r.record_date DESC
    ");
    $stmt->execute([$patient_id]);
    responseJson(["status" => "success", "data" => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['patient_id']) || empty($data['doctor_id'])) {
        responseJson(["status" => "error", "message" => "patient_id and doctor_id are required."], "400 Bad Request");
    }

    // FIX: Changed SQLite date('now') -> CURDATE() for MySQL
    $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, doctor_id, record_date, diagnosis, therapy) VALUES (?,?,CURDATE(),?,?)");
    $stmt->execute([
        $data['patient_id'],
        $data['doctor_id'],
        $data['diagnosis'] ?? '',
        $data['therapy']   ?? ''
    ]);
    responseJson(["status" => "success", "id" => $pdo->lastInsertId()]);
}
?>
