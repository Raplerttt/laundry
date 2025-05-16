<?php
session_start();
include('config.php'); // Menghubungkan dengan database

// Debugging - cek apakah user_id diterima dengan benar
if (isset($_POST['user_id'])) {
    echo "user_id yang diterima: " . $_POST['user_id']; // Debugging untuk melihat ID yang diterima
    echo "<br>";
}

if (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && !empty($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    // Query untuk mengubah status pengguna
    $query = "UPDATE users SET status = CASE WHEN status = 'Aktif' THEN 'Non-Aktif' ELSE 'Aktif' END WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);

    // Redirect atau beri pesan sukses
    header('Location: admin.php');  // Mengarahkan kembali ke halaman admin setelah status diubah
    exit();
} else {
    // Jika user_id tidak valid, tampilkan pesan error dan ID yang diterima
    echo "ID pengguna tidak valid!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;
    $newStatus = $_POST['new_status'] ?? null;

    if ($orderId && $newStatus) {
        $query = "UPDATE orders SET status_pemesanan = :status WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':id', $orderId);

        if ($stmt->execute()) {
            header("Location: manage_orders.php?success=1");
            exit;
        } else {
            echo "Gagal mengubah status.";
        }
    } else {
        echo "Data tidak lengkap.";
    }
}
?>
