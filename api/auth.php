<?php
// auth.php - Login, Logout, Session Check
session_start();
require '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // LOGIN
    $data = json_decode(file_get_contents("php://input"), true);
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        responseJson(["status" => "error", "message" => "Email and password are required."], "400 Bad Request");
    }

    $stmt = $pdo->prepare("SELECT id, role, name, image_url, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];
        unset($user['password']); // do not send password back to client
        responseJson(["status" => "success", "user" => $user]);
    } else {
        responseJson(["status" => "error", "message" => "Invalid credentials."], "401 Unauthorized");
    }

} elseif ($method === 'DELETE') {
    // LOGOUT
    session_destroy();
    responseJson(["status" => "success"]);

} elseif ($method === 'GET') {
    // CHECK SESSION
    if (isset($_SESSION['user_id'])) {
        responseJson(["status" => "success", "user" => [
            "id"   => $_SESSION['user_id'],
            "role" => $_SESSION['role'],
            "name" => $_SESSION['name']
        ]]);
    } else {
        responseJson(["status" => "error", "message" => "Not authenticated."], "401 Unauthorized");
    }
}
?>
