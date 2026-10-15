<?php
// transactions.php - CRUD for transactions
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['user_id'])) {
    responseJson(["status" => "error", "message" => "Unauthorized."], "401 Unauthorized");
}

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT t.*, p.name AS patient_name
        FROM transactions t
        LEFT JOIN patients p ON t.patient_id = p.id
        ORDER BY t.transaction_date DESC, t.id DESC
    ");
    $data = $stmt->fetchAll();

    $sumStmt = $pdo->query("SELECT SUM(amount) AS total FROM transactions");
    $total   = $sumStmt->fetch()['total'] ?? 0;

    responseJson(["status" => "success", "data" => $data, "total" => $total]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['patient_id']) || empty($data['service_name']) || !isset($data['amount'])) {
        responseJson(["status" => "error", "message" => "patient_id, service_name, and amount are required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("INSERT INTO transactions (patient_id, service_name, amount, transaction_date) VALUES (?,?,?,CURDATE())");
    $stmt->execute([$data['patient_id'], $data['service_name'], $data['amount']]);
    responseJson(["status" => "success", "id" => $pdo->lastInsertId()]);

} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("UPDATE transactions SET patient_id=?, service_name=?, amount=? WHERE id=?");
    $stmt->execute([$data['patient_id'], $data['service_name'], $data['amount'], $data['id']]);
    responseJson(["status" => "success"]);

} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        responseJson(["status" => "error", "message" => "id is required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id=?");
    $stmt->execute([$id]);
    responseJson(["status" => "success"]);
}
?>
