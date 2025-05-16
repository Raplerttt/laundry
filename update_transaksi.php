<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $berat = $_POST['berat'] ?? null;
    $harga_ongkir = $_POST['harga_ongkir'] ?? null;

    if (!$id || $berat === null || $harga_ongkir === null) {
        die('Data tidak lengkap.');
    }

    if (!is_numeric($berat) || !is_numeric($harga_ongkir)) {
        die('Berat dan harga ongkir harus berupa angka.');
    }

    try {
        // Update checkout_orders
        $sql1 = "UPDATE checkout_orders
                 SET berat = :berat, harga_ongkir = :harga_ongkir
                 WHERE id = :id";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindValue(':berat', $berat);
        $stmt1->bindValue(':harga_ongkir', (int)$harga_ongkir, PDO::PARAM_INT);
        $stmt1->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt1->execute();

        // Ambil user_id dari checkout_orders
        $sqlGetUser = "SELECT user_id FROM checkout_orders WHERE id = :id";
        $stmtGetUser = $pdo->prepare($sqlGetUser);
        $stmtGetUser->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtGetUser->execute();
        $user = $stmtGetUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die('User tidak ditemukan.');
        }

        $user_id = $user['user_id'];

        // Update status_pemesanan di orders untuk order terbaru user tersebut
        $sql2 = "
            UPDATE orders
            SET status_pemesanan = 'Menunggu Pembayaran'
            WHERE id = (
                SELECT id FROM orders
                WHERE user_id = :user_id
                ORDER BY id DESC
                LIMIT 1
            )
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt2->execute();

        header('Location: transaksi.php?update=success');
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Request method tidak valid.";
}
