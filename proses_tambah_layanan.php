<?php
session_start();
include('config.php'); // Menggunakan $pdo

$nama_paket = $_POST['nama_paket'];
$harga = $_POST['harga'];
$gambar = "";

// Validasi input
if (empty($nama_paket) || empty($harga)) {
    echo "Nama paket dan harga wajib diisi.";
    exit;
}

// Proses upload gambar jika ada
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $fileName = time() . '_' . basename($_FILES['gambar']['name']);
    $uploadPath = $uploadDir . $fileName;

    // Cek folder upload
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
        $gambar = $uploadPath;
    } else {
        echo "Gagal upload gambar.";
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO paket (nama_paket, harga, gambar) VALUES (:nama_paket, :harga, :gambar)");
    $stmt->execute([
        ':nama_paket' => $nama_paket,
        ':harga' => $harga,
        ':gambar' => $gambar
    ]);

    echo "<script>alert('Layanan berhasil ditambahkan!'); window.location.href='daftar_layanan.php';</script>";
} catch (PDOException $e) {
    echo "Gagal menyimpan data: " . $e->getMessage();
}
?>
