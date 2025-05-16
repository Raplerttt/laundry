<?php
session_start();
include('config.php'); // koneksi PDO ke $pdo

header('Content-Type: application/json');

if (!isset($_SESSION['userEmail'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['userEmail'];

$data = json_decode(file_get_contents('php://input'), true);
$old = trim($data['old_password'] ?? '');
$new = trim($data['new_password'] ?? '');
$confirm = trim($data['confirm_password'] ?? '');

if (empty($old) || empty($new) || empty($confirm)) {
    http_response_code(400);
    echo json_encode(['message' => 'All fields are required.']);
    exit;
}

if ($new !== $confirm) {
    http_response_code(400);
    echo json_encode(['message' => 'New passwords do not match.']);
    exit;
}

if (strlen($new) < 6) {
    http_response_code(400);
    echo json_encode(['message' => 'Password must be at least 6 characters.']);
    exit;
}

// Ambil hash password dari DB
$stmt = $pdo->prepare("SELECT password FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($old, $user['password'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Old password is incorrect.']);
    exit;
}

// Update password
$newHash = password_hash($new, PASSWORD_DEFAULT);
$update = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
$success = $update->execute(['password' => $newHash, 'email' => $email]);

if ($success) {
    echo json_encode(['message' => 'Password successfully updated.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to update password.']);
}
