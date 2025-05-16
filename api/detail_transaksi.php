<?php
session_start();
include('../config.php'); // sesuaikan path
// Fungsi cek admin
function checkAdmin() {
    return isset($_SESSION['userEmail']) && $_SESSION['userEmail'] === 'admin@bundalaundry.com';
}

// Cek apakah user admin, jika tidak beri 403 Forbidden
if (!checkAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter ID diperlukan']);
    exit;
}

$id = $_GET['id'];

// Prepare dan execute query
$stmt = $pdo->prepare("
    SELECT co.*, p.nama_paket 
    FROM checkout_orders co
    LEFT JOIN paket p ON co.paket_id = p.id
    WHERE co.id = ?
");
$stmt->execute([$id]);
$detail = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$detail) {
    http_response_code(404);
    echo json_encode(['error' => 'Data tidak ditemukan']);
    exit;
}

// Kirim data JSON
echo json_encode($detail);
