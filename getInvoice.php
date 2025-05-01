<?php
include 'config.php';
session_start();

// Cek apakah parameter user_id ada
if (!isset($_GET['userId'])) {
    die("User ID tidak ditemukan.");
}

$userId = $_GET['userId']; // Mengambil user_id dari URL

// Query SQL untuk mengambil data pesanan berdasarkan user_id
$sql = "
    SELECT o.*, p.nama_paket, p.harga, c.first_name, c.last_name, c.pickup_address, c.delivery_address
    FROM orders o
    JOIN checkout_orders c ON o.id = c.id
    JOIN order_paket op ON o.id = op.id_order
    JOIN paket p ON op.id_paket = p.id
    WHERE c.user_id = :userId
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $userId]);
$order = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pastikan ada data yang ditemukan
if (empty($order)) {
    die("Pesanan tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Laundry Service</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Include CSS file -->
</head>
<body>

<!-- Invoice Section -->
<section class="invoice-section">
    <h1 class="text-center">Invoice untuk Pengguna #<?= htmlspecialchars($userId) ?></h1>
    
    <!-- Rincian Pemesan -->
    <div class="order-details">
        <h3>Informasi Pemesan</h3>
        <?php foreach ($order as $item): ?>
            <p><strong>Nama:</strong> <?= htmlspecialchars($item['first_name']) ?> <?= htmlspecialchars($item['last_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($item['email']) ?></p>
            <p><strong>No. HP:</strong> <?= htmlspecialchars($item['phone']) ?></p>
            <p><strong>Alamat Penjemputan:</strong> <?= htmlspecialchars($item['pickup_address']) ?></p>
            <p><strong>Alamat Pengantaran:</strong> <?= htmlspecialchars($item['delivery_address']) ?></p>
        <?php endforeach; ?>
    </div>

    <!-- Rincian Pesanan -->
    <div class="order-summary">
        <h3>Rincian Pesanan</h3>
        <?php foreach ($order as $item): ?>
            <p><strong>Nama Paket:</strong> <?= htmlspecialchars($item['nama_paket']) ?></p>
            <p><strong>Harga Normal:</strong> Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
            <p><strong>Diskon:</strong> Rp <?= number_format($item['diskon'], 0, ',', '.') ?></p>
            <p><strong>Total Harga:</strong> Rp <?= number_format($item['harga'] - $item['diskon'], 0, ',', '.') ?></p>
        <?php endforeach; ?>
    </div>

    <!-- Metode Pembayaran -->
    <div class="payment-method">
        <h3>Metode Pembayaran</h3>
        <p><strong>Metode:</strong> <?= htmlspecialchars($order[0]['payment_method']) ?></p>
        <p><strong>Status Pembayaran:</strong> <?= $order[0]['payment_status'] == 1 ? 'Lunas' : 'Belum Lunas' ?></p>
    </div>

    <!-- Link untuk melihat bukti pembayaran -->
    <div class="payment-proof">
        <a href="path/to/payment-proof/<?= $order[0]['id'] ?>" class="button">Lihat Bukti Pembayaran</a>
    </div>

    <!-- Tanggal Pemesanan -->
    <div class="order-date">
        <p><strong>Tanggal Pemesanan:</strong> <?= date('d-m-Y H:i', strtotime($order[0]['created_at'])) ?></p>
    </div>
</section>

</body>
</html>
