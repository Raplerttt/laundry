<?php
// Koneksi ke database
include('config.php');  // Pastikan koneksi sudah dibuat sebelumnya
session_start();

$errors = [];
$pakets = []; // Ambil data paket dari database

// Ambil data paket dari database
$query = "SELECT * FROM paket";
$stmt = $pdo->prepare($query);
$stmt->execute();
$pakets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form saat di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $phone = trim($_POST['phone']);
    $countryCode = trim($_POST['countryCode']);
    $paket_id = trim($_POST['paket_id']);
    $pickupAddress = trim($_POST['pickupAddress']);
    $deliveryAddress = trim($_POST['deliveryAddress']);
    $agreement = isset($_POST['agreement']);

    // Validasi
    if (empty($firstName)) {
        $errors[] = "Nama depan harus diisi.";
    }
    if (empty($lastName)) {
        $errors[] = "Nama belakang harus diisi.";
    }
    if (empty($phone)) {
        $errors[] = "Nomor telepon harus diisi.";
    }
    if (empty($paket_id)) {
        $errors[] = "Pilih paket laundry.";
    }
    if (empty($pickupAddress)) {
        $errors[] = "Alamat penjemputan harus diisi.";
    }
    if (empty($deliveryAddress)) {
        $errors[] = "Alamat pengantaran harus diisi.";
    }
    if (!$agreement) {
        $errors[] = "Anda harus setuju dengan syarat dan ketentuan.";
    }

    // Jika tidak ada error, simpan data ke database
    if (empty($errors)) {
        // Ambil user_id dari session (pastikan user sudah login)
        if (!isset($_SESSION['userId'])) {
            $errors[] = "User  ID tidak ditemukan. Silakan login.";
        } else {
            $user_id = $_SESSION['userId'];
// Simpan pesanan ke database
$query = "INSERT INTO orders (user_id, nama_depan, nama_belakang, nomer_telepon, alamat_penjemputan, alamat_pengantaran, status_pemesanan) 
          VALUES (:user_id, :nama_depan, :nama_belakang, :nomer_telepon, :alamat_penjemputan, :alamat_pengantaran, 'Pending')";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id); // Use 'user_id' instead of 'userid'
$stmt->bindParam(':nama_depan', $firstName);
$stmt->bindParam(':nama_belakang', $lastName);
$stmt->bindParam(':nomer_telepon', $phone);
$stmt->bindParam(':alamat_penjemputan', $pickupAddress);
$stmt->bindParam(':alamat_pengantaran', $deliveryAddress);

if ($stmt->execute()) {
    // Ambil ID pesanan yang baru saja dibuat
    $order_id = $pdo->lastInsertId();

    // Simpan paket yang dipilih ke tabel order_paket
    $query = "INSERT INTO order_paket (id_order, id_paket) VALUES (:id_order, :id_paket)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_order', $order_id);
    $stmt->bindParam(':id_paket', $paket_id);
    $stmt->execute();

    // Redirect atau tampilkan pesan sukses
    header("Location: pembayaran.php"); // Ganti dengan halaman sukses yang sesuai
    exit();
} else {
    $errors[] = "Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.";
}
        }
    }
}

// Mengambil ID paket yang dipilih dari URL, jika ada
$selectedPaketId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$selectedPaketId) {
    die("ID paket tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style type="text/tailwindcss">
    @theme {
      --color-bar: #2375A5;
    }

    .hidden {
      display: none;
    }
  </style>
  <title>Bunda Laundry</title>
</head>
<body>
<?php include('header.php') ?>
<div class="w-full">
  <div class="w-full">
    <section class="relative w-full flex items-center justify-center text-center">
      <div class="relative w-full">
        <img class="w-full h-100 object-cover rounded-md" src="https://images.unsplash.com/photo-1680725779155-456faadefa26" alt="Random image">
        <div class="absolute inset-0 bg-gray-700 opacity-60 rounded-md"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <h2 class="text-white text-3xl font-bold">Checkout</h2>
        </div>
      </div>
    </section>
  </div>
</div>
<div class="max-w-7xl mx-auto mt-8 sm:px-y lg:px-8">
    <div class="px-8 py-10 bg-white shadow-lg rounded-lg">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-8">Informasi Pelanggan</h2>
<!-- Form HTML di sini -->
<form method="POST" action="">
    <div class="grid sm:grid-cols-2 grid-cols-1 gap-6 mb-6">
        <div>
            <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">Nama Depan</label>
            <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($_POST['firstName'] ?? '') ?>" class="h-12 w-full border border-gray-300 rounded-md px-3 text-sm" />
        </div>
        <div>
            <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Nama Belakang </label>
            <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($_POST['lastName'] ?? '') ?>" class="h-12 w-full border border-gray-300 rounded-md px-3 text-sm" />
        </div>
    </div>

    <div class="mb-6">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
        <div class="flex">
            <select name="countryCode" id="countryCode" class="h-12 rounded-l-md border border-gray-300 text-sm px-2 bg-white">
                <option value="+62" <?= ($_POST['countryCode'] ?? '') == '+62' ? 'selected' : '' ?>>🇮🇩 +62</option>
            </select>
            <input type="tel" id="phone" name="phone" placeholder="81234567890" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" class="h-12 w-full border-t border-b border-r border-gray-300 rounded-r-md px-3 text-sm" />
        </div>
    </div>

    <div class="mb-6">
    <label for="paket_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Paket Laundry</label>
    <select id="paket_id" name="paket_id" class="h-12 w-full border border-gray-300 rounded-md px-3 text-sm">
        <option value="">-- Pilih Paket --</option>
        <?php foreach ($pakets as $paket): ?>
            <option value="<?= htmlspecialchars($paket['id']) ?>"><?= htmlspecialchars($paket['nama_paket']) ?></option>
        <?php endforeach; ?>
    </select>
    </div>

    <div class="mb-6">
        <label for="pickupAddress" class="block text-sm font-medium text-gray-700 mb-1">Alamat Penjemputan</label>
        <textarea id="pickupAddress" name="pickupAddress" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"><?= htmlspecialchars($_POST['pickupAddress'] ?? '') ?></textarea>
    </div>

    <div class="mb-6">
        <label for="deliveryAddress" class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengantaran</label>
        <textarea id="deliveryAddress" name="deliveryAddress" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"><?= htmlspecialchars($_POST['deliveryAddress'] ?? '') ?></textarea>
    </div>

    <div class="mb-6 flex items-start">
        <input type="checkbox" id="agreement" name="agreement" class="h-4 w-4 text-green-600 border-gray-300 rounded" <?= isset($_POST['agreement']) ? 'checked' : '' ?> />
        <label for="agreement" class="ml-2 text-sm text-gray-700">Saya setuju dengan syarat dan ketentuan yang berlaku</label>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 text-sm text-red-600">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li>• <?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="flex justify-end">
    <a href="pembayaran.php?paket_id=<?= $paket['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md text-sm font-semibold">
        Checkout
    </a>
</div>

</form>
</div>
<footer class="xl:mt-16 mx-auto w-full relative text-center bg-bar text-white">
    <div class="px-6 py-8 md:py-14 xl:pt-20 xl:pb-12 text-center">
      <h2 class="font-bold text-3xl xl:text-4xl leading-snug">Kami menawarkan berbagai pilihan paket <br> laundry dengan harga terbaik dan layanan <br> cuci berkualitas. dapatkan kemudahan mencuci <br> pakaian tanpa ribet!</h2>
      <p class="text-white py-6">Pilih Paket Laundry Sesuai Kebutuhan Anda</p>
      <div class="mt-8 xl:mt-10">
        <nav class="flex flex-wrap justify-center text-lg font-medium">
          <div class="px-5 py-2"><a href="#">Home</a></div>
          <div class="px-5 py-2"><a href="#">Daftar Paket Layanan</a></div>
          <div class="px-5 py-2"><a href="#">Riwayat Transaksi</a></div>
          <div class="px-5 py-2"><a href="#">Terms</a></div>
          <div class="px-5 py-2"><a href="#">Twitter</a></div>
        </nav>
        <p class="mt-7 text-base">© 2023 Copyright, Bunda Laundry</p>
      </div>
    </div>
</footer>
</body>
</html>