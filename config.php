<?php

$host = "localhost";
$port = "5432";
$dbname = "laundry";
$user = "mac";        // Sesuaikan dengan username PostgreSQL kamu
$password = "";       // Sesuaikan dengan password PostgreSQL kamu

try {
    // Membuat objek PDO untuk koneksi ke database
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Set error mode untuk menangani kesalahan
} catch (PDOException $e) {
    // Menangani jika koneksi gagal
    echo "Koneksi gagal: " . $e->getMessage();
    die();  // Menghentikan script jika koneksi gagal
}
?>
