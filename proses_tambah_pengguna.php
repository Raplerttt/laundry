<?php
session_start();
include('query.php'); // File ini harus berisi koneksi PDO dalam variabel $pdo

// Cek apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $query = "INSERT INTO users (username, email, password, full_name) 
                  VALUES (:username, :email, :password, :full_name) RETURNING id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':full_name', $full_name);

        if ($stmt->execute()) {
            $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $_SESSION['success'] = "Pengguna berhasil ditambahkan dengan ID: $id";
            header("Location: daftar_pengguna.php"); // Ganti ke halaman yang sesuai
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan pengguna.";
            header("Location: tambah_pengguna.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "PDO Error: " . $e->getMessage();
        header("Location: tambah_pengguna.php");
        exit();
    }
}
