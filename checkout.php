<?php
// Koneksi ke database
include('config.php');  // Pastikan koneksi sudah dibuat sebelumnya
include('query.php');
session_start();

$errors = [];

try {
    $pakets = $pdo->query("SELECT * FROM paket")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data paket: " . $e->getMessage());
}

// Validasi jika data kosong
if (!$pakets || count($pakets) === 0) {
    die('Paket tidak ditemukan.');
}

$userId = $_SESSION['userId'] ?? null;

if (!$userId) {
    // Pengguna belum login
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $countryCode = $_POST['countryCode'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $pickupAddress = $_POST['pickupAddress'] ?? '';
    $deliveryAddress = $_POST['deliveryAddress'] ?? '';
    $paketId = $_POST['paket_id'] ?? null;
    $agreement = isset($_POST['agreement']);

    $errors = [];

    // Validasi input
    if (!$firstName || !$lastName || !$phone || !$pickupAddress || !$deliveryAddress || !$paketId || !$agreement) {
        $errors[] = 'Semua bidang wajib diisi dan setujui syarat & ketentuan.';
    }

    if (empty($errors)) {
        // Simpan ke tabel checkout_orders
        $stmt = $pdo->prepare("INSERT INTO checkout_orders (first_name, last_name, country_code, phone, pickup_address, delivery_address, user_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $countryCode, $phone, $pickupAddress, $deliveryAddress, $userId]);

        // Simpan ke tabel orders
        $stmt2 = $pdo->prepare("INSERT INTO orders (user_id, nama_depan, nama_belakang, nomer_telepon, alamat_penjemputan, alamat_pengantaran, status_pemesanan, paket_id) 
                                VALUES (?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?)");
        $stmt2->execute([$userId, $firstName, $lastName, $phone, $pickupAddress, $deliveryAddress, $paketId]);

        $orderId = $pdo->lastInsertId(); // Ambil ID dari tabel 'orders'

        // Simpan ke tabel order_paket
        $stmt3 = $pdo->prepare("INSERT INTO order_paket (id_order, id_paket) VALUES (?, ?)");
        $stmt3->execute([$orderId, $paketId]);

        // Redirect ke halaman pembayaran
        header("Location: riwayat-transaksi.php");
        exit;
    } else {
        foreach ($errors as $error) {
            echo "<p class='text-red-500'>$error</p>";
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
            <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Nama Belakang</label>
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
        <select id="paket_id" name="paket_id" class="...">
    <option value="">-- Pilih Paket --</option>
    <?php foreach ($pakets as $paket): ?>
        <option value="<?= htmlspecialchars($paket['id']) ?>" <?= ($_POST['paket_id'] ?? '') == $paket['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($paket['nama_paket']) ?> - <?= htmlspecialchars($paket['harga']) ?>
        </option>
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
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md text-sm font-semibold">
            Checkout
        </button>
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