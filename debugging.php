<?php
// Memulai session jika diperlukan
session_start();

// Memasukkan file config untuk koneksi database
include('config.php');

// Query untuk mengambil data pengguna yang bukan admin
$query = "SELECT username, email FROM users WHERE email != 'admin@bundalaundry.com'";

// Menyiapkan query dengan PDO
$stmt = $pdo->prepare($query);

// Menjalankan query
$stmt->execute();

// Mengambil semua data pengguna
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debugging: tampilkan hasil query
echo "<h2>Hasil Query:</h2>";
echo "<pre>";
print_r($users);  // Menampilkan data pengguna yang diambil
echo "</pre>";

// Menampilkan pesan jika tidak ada data
if (empty($users)) {
    echo "<p>Data pengguna tidak ditemukan.</p>";
} else {
    echo "<p>Data pengguna berhasil diambil.</p>";
}

// Debugging: cek status koneksi PDO
echo "<h2>Status Koneksi PDO:</h2>";
echo "<pre>";
var_dump($pdo);  // Menampilkan status koneksi PDO
echo "</pre>";
?>
