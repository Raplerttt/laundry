<?php
include('query.php');  // Memanggil file queries.php untuk mengakses fungsi CRUD

// Memeriksa apakah form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama = $_POST['nama'];  // Nama pengguna
    $email = $_POST['email'];  // Email pengguna
    $nomor_hp = $_POST['nomor_hp'];  // Nomor HP pengguna
    $password = $_POST['password'];  // Password pengguna

    // Panggil fungsi createUser dari queries.php untuk memasukkan data
    $user_id = createUser($nama, $email, $password, $nomor_hp);

    // Cek apakah user berhasil dibuat
    if ($user_id) {
        echo "Akun berhasil dibuat dengan ID: $user_id";
        // Bisa juga mengarahkan ke halaman login
        header("Location: login.html");
    } else {
        echo "Terjadi kesalahan saat pembuatan akun.";
    }
} else {
    echo "Data tidak valid!";
}
?>
