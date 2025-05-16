<?php
session_start();
include('config.php');
header('Content-Type: application/json');

if (!isset($_SESSION['userEmail']) || !isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$userEmail = $_SESSION['userEmail'];

if ($userEmail === 'admin@bundalaundry.com') {
    $stmt = $pdo->prepare("SELECT id, user_email, message, created_at FROM notifications ORDER BY created_at DESC");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT id, user_email, message, created_at FROM notifications WHERE user_email = :user_email ORDER BY created_at DESC");
    $stmt->execute(['user_email' => $userEmail]);
}

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return "a few moments ago";
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    return floor($diff / 86400) . " days ago";
}

foreach ($notifications as &$notif) {
    $notif['time_ago'] = timeAgo($notif['created_at']);
}

echo json_encode(['notifications' => $notifications]);
