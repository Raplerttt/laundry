<?php
session_start();
include('config.php');
header('Content-Type: application/json');

if (!isset($_SESSION['userEmail']) || $_SESSION['userEmail'] !== 'admin@bundalaundry.com') {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$userEmail = trim($data['user_email'] ?? '');
$message = trim($data['message'] ?? '');

if (!$userEmail || !$message) {
    http_response_code(400);
    echo json_encode(['message' => 'User email and message are required.']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO notifications (user_email, sender_email, message) VALUES (:user_email, :sender_email, :message)");
$success = $stmt->execute([
    'user_email' => $userEmail,
    'sender_email' => 'Bundalaundry',
    'message' => $message
]);


if ($success) {
    echo json_encode(['message' => 'Notification sent successfully']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to send notification']);
}
