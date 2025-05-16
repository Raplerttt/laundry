<?php
// update_pembayaran.php

session_start();

// Sertakan file koneksi dan fungsi
include 'config.php';       // Pastikan ada variabel $pdo koneksi PDO
include 'query.php';        // Fungsi simpanKonfirmasiPembayaran()

// Ambil order_id dari parameter GET
$orderId = $_GET['order_id'] ?? null;

if (!$orderId || !is_numeric($orderId)) {
    die('Order tidak ditemukan atau ID tidak valid.');
}

try {
    // Ambil data order + data checkout_orders terkait user_id, termasuk berat & harga_ongkir
    $stmt = $pdo->prepare("
        SELECT o.*, c.first_name, c.last_name, c.phone, c.pickup_address, c.delivery_address, c.berat, c.harga_ongkir
        FROM orders o
        JOIN checkout_orders c ON o.user_id = c.user_id
        WHERE o.id = ?
        ORDER BY c.id DESC
        LIMIT 1
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order tidak ditemukan.");
    }

    // Ambil data paket berdasarkan paket_id yang ada di orders, sekaligus rata-rata rating dari reviews
    $stmt2 = $pdo->prepare("
        SELECT p.nama_paket, p.harga, p.gambar, COALESCE(AVG(r.rating), 0) AS avg_rating
        FROM paket p
        LEFT JOIN reviews r ON r.item_id = p.id
        WHERE p.id = ?
        GROUP BY p.id
        LIMIT 1
    ");
    $stmt2->execute([$order['paket_id']]);
    $selectedPackage = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$selectedPackage) {
        die("Data paket tidak ditemukan.");
    }

    // Hitung total harga: (harga paket * berat) + ongkir
    // Bersihkan harga paket dari format Rp dan titik ribuan, lalu konversi ke float
    $harga_paket_clean = (float) str_replace(['Rp', '.', ','], ['', '', ''], $selectedPackage['harga']);
    $berat = isset($order['berat']) ? (float)$order['berat'] : 1;
    $harga_ongkir = isset($order['harga_ongkir']) ? (float)$order['harga_ongkir'] : 0;
    $total_harga = ($harga_paket_clean * $berat) + $harga_ongkir;

} catch (PDOException $e) {
    die("Terjadi kesalahan database: " . $e->getMessage());
}

// Proses form konfirmasi pembayaran
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil input dengan filter dan validasi
    $paymentMethod = trim($_POST['paymentMethod'] ?? '');
    $agreement1 = isset($_POST['agreement1']);
    $agreement2 = isset($_POST['agreement2']);

    // Validasi
    $errors = [];
    if ($paymentMethod === '') {
        $errors[] = "Metode pembayaran belum dipilih!";
    }
    if (!$agreement1 || !$agreement2) {
        $errors[] = "Anda harus menyetujui semua persyaratan.";
    }

    if (empty($errors)) {
        // Simpan konfirmasi pembayaran melalui fungsi di query.php
        $isSaved = simpanKonfirmasiPembayaran($pdo, $orderId, $paymentMethod, $agreement1, $agreement2);

        if ($isSaved) {
            // Update status pemesanan jadi "Selesai"
            $updateStatus = $pdo->prepare("UPDATE orders SET status_pemesanan = 'Selesai' WHERE id = ?");
            $updateStatus->execute([$orderId]);

            // Tampilkan pesan sukses dan link ke riwayat transaksi
            echo "<div class='w-full lg:w-2/3 bg-white shadow-lg rounded-lg p-8'>
                    <h2 class='text-2xl font-bold text-gray-800 mb-6'>Pembayaran Dikonfirmasi</h2>
                    <p class='text-lg text-gray-800'>Pembayaran dengan metode <strong>" . htmlspecialchars($paymentMethod) . "</strong> telah berhasil dikonfirmasi!</p>
                    <br>
                    <a href='riwayat_transaksi.php' class='text-green-600 underline'>Lihat Riwayat Transaksi</a>
                  </div>";
            exit;
        } else {
            $errors[] = "Gagal menyimpan konfirmasi pembayaran. Coba lagi nanti.";
        }
    }

    // Jika ada error, tampilkan semua pesan error
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p class='text-red-500'>{$error}</p>";
        }
    }
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
      display : none;
    }
  </style>
  <title>Bunda Laundry</title>
</head>
<body>
<?php include('header.php')?>
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
<div class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-8">
    <!-- Form Konfirmasi Pembayaran -->
    <div class="w-full lg:w-2/3 bg-white shadow-lg rounded-lg p-8">
  <h2 class="text-2xl font-bold text-gray-800 mb-6">Konfirmasi Pembayaran</h2>

  <!-- Metode Pembayaran -->
  <form method="POST" action="">
  <div class="mb-4">
  <label for="paymentMethod" class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
  <select id="paymentMethod" name="paymentMethod" class="h-12 w-full border border-gray-300 rounded-md px-3 text-sm bg-white" onchange="toggleQrisImage()">
    <option value="" disabled selected>Pilih Metode Pembayaran</option> <!-- Pilihan default -->
    <option value="qris">QRIS</option>
    <option value="cash">Cash</option>
  </select>
</div>


    <!-- QRIS Image -->
    <div id="qrisImage" class="hidden mt-4 text-center flex justify-center items-center mb-8">
      <img src="https://media.perkakasku.id/image/qrperkakasku.jpeg" alt="QRIS Image" class="w-100 rounded-md">
    </div>

    <!-- Checkbox Persetujuan -->
    <div class="mb-6 flex items-start">
      <input id="agreement1" type="checkbox" name="agreement1" class="mt-1 rounded-full border-gray-300 text-green-600 focus:ring-green-500 mr-3" required>
      <label for="agreement1" class="text-sm text-gray-600">Saya menyetujui</label>
    </div>

    <div class="mb-6 flex items-start">
      <input id="agreement2" type="checkbox" name="agreement2" class="mt-1 rounded-full border-gray-300 text-green-600 focus:ring-green-500 mr-3" required>
      <label for="agreement2" class="text-sm text-gray-600">Saya menyetujui dan setuju dengan syarat & ketentuan yang berlaku.</label>
    </div>

    <!-- Tombol Konfirmasi -->
    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-md font-semibold text-sm transition duration-200">
      Konfirmasi Pembayaran
    </button>

    <!-- Ikon & Info Keamanan -->
    <div class="flex items-center mt-4 text-sm text-gray-500">
      <i class="fas fa-shield-alt text-green-600 mr-2"></i>
      <span>Semua data aman</span>
    </div>
  </form>
</div>
<?php if ($selectedPackage): ?>
<div class="w-full lg:w-1/3 bg-white shadow-lg rounded-lg p-6">
  <h2 class="text-xl font-semibold mb-4">Rincian Pesanan</h2>
  <div class="flex gap-4 mb-4">
    <img src="<?= htmlspecialchars($selectedPackage['gambar'] ?? 'https://via.placeholder.com/100') ?>" alt="Paket" class="w-24 h-24 object-cover rounded-md">
    <div class="flex flex-col justify-between">
      <h3 class="text-sm font-semibold"><?= htmlspecialchars($selectedPackage['nama_paket']) ?></h3>
      <div class="flex items-center text-yellow-500 text-xs">
        <i class="fas fa-star mr-1"></i> <?= htmlspecialchars($selectedPackage['rating'] ?? '4.5') ?>
      </div>
      <p class="text-sm text-gray-600 line-through">Rp. 10.000</p>
      <p class="text-sm text-gray-800 font-semibold">
        Rp. <?= number_format($harga_paket_clean, 0, ',', '.') ?>
      </p>
    </div>
  </div>

  <!-- Detail berat dan harga ongkir -->
  <div class="text-sm text-gray-700 mb-2">
    Berat: <span class="font-semibold"><?= htmlspecialchars($berat) ?> kg</span>
  </div>
  <div class="text-sm text-gray-700 mb-4">
    Harga Ongkir: <span class="font-semibold">Rp. <?= number_format($harga_ongkir, 0, ',', '.') ?></span>
  </div>
  
  <!-- Tampilkan total harga -->
  <div class="border-t pt-4 mt-4 text-right text-lg font-bold">
    Total: Rp. <?= number_format($total_harga, 0, ',', '.') ?>
  </div>
</div>
<?php endif; ?>

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
<script>
  // Fungsi untuk menampilkan gambar QRIS ketika QRIS dipilih
  function toggleQrisImage() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const qrisImage = document.getElementById('qrisImage');
    if (paymentMethod === 'qris') {
      qrisImage.classList.remove('hidden');
    } else {
      qrisImage.classList.add('hidden');
    }
  }
</script>
</body>
</html>