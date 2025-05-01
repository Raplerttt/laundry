<?php
// Include koneksi database
require_once 'config.php';

// Ambil ID paket dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Mendapatkan ID paket yang dipilih dari query string
$selectedPaketId = isset($_GET['id']) ? $_GET['id'] : null;

// Query ambil data paket
$stmt = $pdo->prepare("SELECT * FROM paket WHERE id = ?");
$stmt->execute([$id]);
$paket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paket) {
    echo "Paket tidak ditemukan.";
    exit;
}

// Query ambil rata-rata rating
$stmtRating = $pdo->prepare("SELECT ROUND(AVG(rating)::numeric, 1) AS avg_rating, COUNT(*) as total_reviews FROM rating WHERE id_paket = ?");
$stmtRating->execute([$id]);
$ratingData = $stmtRating->fetch(PDO::FETCH_ASSOC);

// Siapkan nilai rating
$avgRating = $ratingData['avg_rating'] ?? 0;
$totalReviews = $ratingData['total_reviews'] ?? 0;
$filledStars = floor($avgRating); // Jumlah bintang penuh
$emptyStars = 5 - $filledStars;   // Sisa bintang kosong
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
  <?php include('header.php')?>
<div class="w-full">
  <div class="w-full">
    <section class="relative w-full flex items-center justify-center text-center">
      <div class="relative w-full">
        <img class="w-full h-100 object-cover rounded-md" src="https://images.unsplash.com/photo-1680725779155-456faadefa26" alt="Random image">
        <div class="absolute inset-0 bg-gray-700 opacity-60 rounded-md"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <h2 class="text-white text-3xl font-bold">Detail Paket</h2>
        </div>
      </div>
    </section>
  </div>
</div>
<div class="max-w-7xl mx-auto sm:px-3 lg:px-8">
  <div class="px-4 py-6 sm:px-0">
    <section class="relative w-full flex items-center justify-center text-center">
      <img class="w-full h-120 object-cover" src="https://picsum.photos/800/600" alt="">
    </section>
    <div class="text-2xl font-bold mt-8 text-center">
      <h1><?php echo htmlspecialchars($paket['nama_paket']); ?></h1>
      <p class="text-orange-500 text-xl mt-2"><?php echo htmlspecialchars($paket['harga']); ?></p>
    </div>
  </div>
</div>
<hr>
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
  <div class="px-4 py-6 sm:px-0 flex flex-col lg:flex-row gap-12 text-sm">
    <!-- Konten Kiri -->
    <div class="lg:w-2/3 w-full">
      <!-- Rating -->
      <div class="flex mb-4 mt-2">
      <?php for ($i = 0; $i < $filledStars; $i++): ?>
        <i class="fas fa-star text-yellow-500 text-xs"></i>
      <?php endfor; ?>
      <?php for ($i = 0; $i < $emptyStars; $i++): ?>
        <i class="fas fa-star text-gray-300 text-xs"></i>
      <?php endfor; ?>
      <span class="ml-2 text-xs text-gray-600">
        <?php echo number_format($avgRating, 1); ?> (<?php echo $totalReviews; ?> Reviews)
      </span>
      </div>

      <!-- Fitur layanan -->
      <p><i class="fas fa-check text-green-500 mr-2"></i>Kota Bandung</p>

      <h1 class="mt-4 font-semibold text-base">Layanan Termasuk :</h1>
      <p><i class="fas fa-check text-green-500 mr-2"></i>Cuci Setrika</p>
      <p><i class="fas fa-check text-green-500 mr-2"></i>Pewangi Premium</p>
      <p><i class="fas fa-check text-green-500 mr-2"></i>Lipatan Rapi</p>
      <p><i class="fas fa-check text-green-500 mr-2"></i>Kemasan Ramah Lingkungan</p>
      <p><i class="fas fa-check text-green-500 mr-2"></i>Garansi Kepuasan - Cuci Ulang Gratis</p>

      <!-- Deskripsi -->
      <h1 class="mt-4 font-semibold text-base">Deskripsi :</h1>
      <p><i class="fas fa-check text-green-500 mr-2"></i>Durasi 2-3 hari</p>

      <!-- Harga Ongkir -->
      <h1 class="mt-6 font-semibold text-base">Harga Ongkir Pengantaran</h1>
      <table class="w-full mt-2 border border-gray-300 text-xs text-left">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border-b border-gray-300 border-r border-gray-300">Jarak</th>
            <th class="p-2 border-b border-gray-300">Harga</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="p-2 border-b border-gray-200 border-r border-gray-300">0 - 3 km</td>
            <td class="p-2 border-b border-gray-200">Gratis</td>
          </tr>
          <tr>
            <td class="p-2 border-b border-gray-200 border-r border-gray-300">3 - 7 km</td>
            <td class="p-2 border-b border-gray-200">Rp 5.000</td>
          </tr>
          <tr>
            <td class="p-2 border-b border-gray-200 border-r border-gray-300">7 - 10 km</td>
            <td class="p-2 border-b border-gray-200">Rp 10.000</td>
          </tr>
        </tbody>
      </table>      
    </div>

    <!-- Peta Kanan -->
    <div class="lg:w-1/3 w-full">
      <h2 class="text-base font-semibold mb-2">Lokasi Kami</h2>
      <div class="rounded-md overflow-hidden shadow-md">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63394.14894769275!2d107.5607558!3d-6.9032736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e7cbff11097f%3A0x9b5e6b4de5469c4b!2sBandung!5e0!3m2!1sen!2sid!4v1682224052587!5m2!1sen!2sid" 
          width="100%" 
          height="250" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

      <!-- Pesan Sekarang -->
      <div class="mt-4 p-3 bg-gray-50 rounded-lg shadow text-xs">
      <h1 class="text-sm font-semibold mb-3">Pesan Sekarang</h1>
      <div class="flex justify-between items-center mb-1">
        <span>Sub Total</span>
        <span class="font-medium"><?php echo htmlspecialchars($paket['harga']); ?></span>
      </div>
      <div class="flex justify-between items-center mb-1">
        <span>Pajak</span>
        <span class="font-medium">Rp 1000</span>
      </div>
      <div>
  <?php if ($selectedPaketId == $paket['id']): ?>
    <p class="text-green-500">Paket ini sedang dipilih!</p>
  <?php endif; ?>
  <a href="checkout.php?id=<?php echo $paket['id']; ?>">
    <button class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-md transition duration-200">
      Pesan Sekarang
    </button>
  </a>
</div>

    </div>
    </div>    
  </div>
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