<?php
// Cek apakah pengguna sudah login dan memiliki email admin
session_start();
include('config.php');

// Cek jika tombol delete_user ditekan
if (isset($_POST['delete_user'])) {
    // Ambil ID pengguna dari form
    $user_id = $_POST['user_id'];

    // Query untuk menghapus pengguna
    $delete_query = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->execute([$user_id]);

    // Redirect setelah berhasil
    header('Location: admin.php');
    exit();
}
?>
