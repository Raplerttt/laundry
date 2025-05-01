<?php
// Koneksi ke database
include('config.php');  // pastikan koneksi sudah dibuat sebelumnya

// ========================== CREATE ==========================
function createUser($username, $email, $password, $full_name) {
    global $pdo;  // Menggunakan PDO yang sudah didefinisikan di db_config.php

    // Hash password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $query = "INSERT INTO users (username, email, password, full_name) 
              VALUES (:username, :email, :password, :full_name) RETURNING id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':full_name', $full_name);
    
    if ($stmt->execute()) {
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];  // Mengembalikan ID user yang baru dibuat
    } else {
        return $stmt->errorInfo();  // Mengembalikan pesan error
    }
}

// ========================== READ ==========================
function getUserById($id) {
    global $pdo;  // Menggunakan PDO

    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);  // Mengembalikan data user
}

function getAllUsers() {
    global $pdo;  // Menggunakan PDO

    $query = "SELECT * FROM users";
    $stmt = $pdo->query($query);  // Tidak perlu prepare jika tidak ada parameter

    return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Mengembalikan semua data user
}

// ========================== UPDATE ==========================
function updateUser($id, $username, $email, $password, $full_name) {
    global $pdo;  // Menggunakan PDO

    // Hash password baru jika ada perubahan
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $query = "UPDATE users 
              SET username = :username, email = :email, password = :password, full_name = :full_name, updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':full_name', $full_name);

    return $stmt->execute();  // Mengembalikan true jika berhasil update
}

// ========================== DELETE ==========================
function deleteUser($id) {
    global $pdo;  // Menggunakan PDO

    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();  // Mengembalikan true jika berhasil menghapus
}

// ========================== GET USER BY EMAIL ==========================
function getUserByEmail($email) {
    global $pdo;  // Menggunakan PDO yang sudah didefinisikan sebelumnya

    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);  // Mengembalikan hasil query
}

// Fungsi untuk membuat pesanan
function createOrder($user_id, $nama_depan, $nama_belakang, $nomer_telepon, $alamat_penjemputan, $alamat_pengantaran, $status_pemesanan) {
    global $conn;

    // Query untuk menyimpan pesanan
    $query = "INSERT INTO orders (user_id, nama_depan, nama_belakang, nomer_telepon, alamat_penjemputan, alamat_pengantaran, status_pemesanan) VALUES (:user_id, :nama_depan, :nama_belakang, :nomer_telepon, :alamat_penjemputan, :alamat_pengantaran, :status_pemesanan)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':nama_depan', $nama_depan);
    $stmt->bindParam(':nama_belakang', $nama_belakang);
    $stmt->bindParam(':nomer_telepon', $nomer_telepon);
    $stmt->bindParam(':alamat_penjemputan', $alamat_penjemputan);
    $stmt->bindParam(':alamat_pengantaran', $alamat_pengantaran);
    $stmt->bindParam(':status_pemesanan', $status_pemesanan);

    return $stmt->execute();
}

// Fungsi untuk mendapatkan pesanan
function getOrder($order_id) {
    global $conn;

    // Query untuk mengambil data pesanan
    $query = "SELECT * FROM orders WHERE id = :order_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTransactionHistory($pdo, $userId) {
    $query = "SELECT 
                o.id,
                o.status_pemesanan,
                p.nama_paket
              FROM orders o
              JOIN order_paket op ON o.id = op.id_order
              JOIN paket p ON op.id_paket = p.id
              WHERE o.user_id = :user_id
              ORDER BY o.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function simpanKonfirmasiPembayaran($pdo, $paymentMethod, $agreement1, $agreement2) {
    $query = "INSERT INTO payment_confirmations (payment_method, agreement1, agreement2) 
              VALUES (:payment_method, :agreement1, :agreement2)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':payment_method', $paymentMethod);
    $stmt->bindParam(':agreement1', $agreement1);
    $stmt->bindParam(':agreement2', $agreement2);
    $stmt->execute();
    return $stmt->rowCount() > 0;  // Mengembalikan true jika data berhasil disimpan
}

function getAllReviews() {
    global $pdo; // penting agar fungsi bisa pakai $pdo dari luar

    try {
        $sql = "SELECT r.*, u.full_name AS name, u.email
                FROM rating r
                JOIN users u ON r.id_user = u.id
                ORDER BY r.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Query error: " . $e->getMessage();
        return [];
    }
}

// Fungsi untuk menambahkan ulasan
function addUlasan($user_id, $item_id, $rating, $review_text) {
    global $pdo;
    
    // Validasi rating antara 1 hingga 5
    if ($rating < 1 || $rating > 5) {
        echo "Rating harus antara 1 hingga 5.";
        return false;
    }

    // Persiapkan query untuk memasukkan data ulasan
    $sql = "INSERT INTO reviews (user_id, item_id, rating, review_text) 
            VALUES (:user_id, :item_id, :rating, :review_text)";
    
    // Menyiapkan statement
    $stmt = $pdo->prepare($sql);
    
    // Bind parameter
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
    $stmt->bindParam(':review_text', $review_text, PDO::PARAM_STR);
    
    // Eksekusi query
    if ($stmt->execute()) {
        echo "Ulasan berhasil ditambahkan.";
        return true;
    } else {
        echo "Terjadi kesalahan saat menambahkan ulasan.";
        return false;
    }
}

?>
