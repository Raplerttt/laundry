<?php
include 'query.php';
include 'config.php';

session_start();
if (!isset($_SESSION['userId'])) {
    // Redirect ke halaman login atau tampilkan pesan error
    header("Location: login.html");
    exit;
}
$userId = $_SESSION['userId'];
$transactions = getTransactionHistory($pdo, $userId);
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
<div class="max-w-7xl mx-auto mx-auto sm:px-3 lg:px-8">
  <div class="px-4 py-6 sm:px-0">
  <section>
    <h1 class="text-3xl text-center font-bold px-8 py-10">Riwayat Transaksi</h1>
    <table class="w-full mt-2 border border-gray-300 text-xs text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border-b border-gray-300 border-r border-gray-300">No</th>
                <th class="p-2 border-b border-gray-300 border-r border-gray-300">Paket Layanan</th>
                <th class="p-2 border-b border-gray-300 border-r border-gray-300">Status</th>
                <th class="p-2 border-b border-gray-300">Aksi</th>
            </tr>
        </thead>
<tbody>
    <?php if (empty($transactions)): ?>
        <tr>
            <td colspan="4" class="p-2 text-center">Tidak ada riwayat transaksi.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($transactions as $index => $transaction): ?>
            <tr>
                <td class="p-2 border-b border-gray-200 border-r border-gray-300"><?= $index + 1 ?></td>
                <td class="p-2 border-b border-gray-200 border-r border-gray-300"><?= htmlspecialchars($transaction['nama_paket']) ?></td>
                <td class="p-2 border-b border-gray-200 border-r border-gray-300 font-semibold">
                    <?= htmlspecialchars($transaction['status_pemesanan']) ?>
                </td>
                <td class="p-2 border-b border-gray-200 space-x-1">
                    <?php if ($transaction['status_pemesanan'] === 'Menunggu Pembayaran'): ?>
                      <a href="pembayaran.php?order_id=<?= $transaction['id'] ?>" class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs">Bayar Sekarang</a>
                        <a href="bukti.php?id=<?= $transaction['id'] ?>" class="px-2 py-1 bg-gray-200 rounded text-xs">Lihat Bukti Pesanan</a>

                    <?php elseif ($transaction['status_pemesanan'] === 'Selesai'): ?>
                        <a href="ulasan.php?id=<?= $transaction['id'] ?>" class="px-2 py-1 bg-blue-200 text-blue-800 rounded text-xs">Beri Ulasan</a>

                    <?php else: ?>
                        <span class="text-gray-400 text-xs italic">Menunggu Konfirmasi Admin</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
    </table>
</section>

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
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn'); // 'true' berarti user login
  
    const authButtonsDesktop = document.getElementById('auth-buttons-desktop');
    const authButtonsMobile = document.getElementById('auth-buttons-mobile');
    const userInfoDesktop = document.getElementById('user-info-desktop');
    const userInfoMobile = document.getElementById('user-info-mobile');
  
    console.log('isLoggedIn:', isLoggedIn);
  
    if (isLoggedIn) {
      // User logged in
      authButtonsDesktop.classList.add('hidden');
      authButtonsMobile.classList.add('hidden');
      userInfoDesktop.classList.remove('hidden');
      userInfoMobile.classList.remove('hidden');
    } else {
      // User not logged in
      authButtonsDesktop.classList.remove('hidden');
      authButtonsMobile.classList.remove('hidden');
      userInfoDesktop.classList.add('hidden');
      userInfoMobile.classList.add('hidden');
    }
  
    // Toggle mobile menu
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
  
    mobileMenuButton.addEventListener('click', function () {
      const expanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
      mobileMenuButton.setAttribute('aria-expanded', String(!expanded));
      mobileMenu.classList.toggle('hidden');
    });
  });
</script>
</body>
</html>