<?php
session_start();
include('config.php');

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: login.html");
    exit;
}

$userEmail = $_SESSION['userEmail'];

// Tandai notifikasi sudah dibaca
$updateStmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_email = :email");
$updateStmt->execute(['email' => $userEmail]);

// Ambil semua notifikasi user
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_email = :email ORDER BY created_at DESC");
$stmt->execute(['email' => $userEmail]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return "a few moments ago";
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    return floor($diff / 86400) . " days ago";
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
    <title>Document</title>
    <style type="text/tailwindcss">
        @theme {
          --color-bar: #2375A5;
        }

        .hidden {
          display: none;
        }
      </style>
</head>
<body>
<?php include('header.php'); ?>
    <div class="w-full">
        <div class="w-full">
          <section class="relative w-full flex items-center justify-center text-center">
            <div class="relative w-full">
              <img class="w-full h-100 object-cover rounded-md" src="https://images.unsplash.com/photo-1680725779155-456faadefa26" alt="Random image">
              <div class="absolute inset-0 bg-gray-700 opacity-60 rounded-md"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <h2 class="text-white text-3xl font-bold">Notification</h2>
              </div>
            </div>
          </section>
        </div>
    </div>
    <?php if (empty($notifications)): ?>
  <p class="text-gray-400 italic text-center mt-10">Belum ada notifikasi baru.</p>
<?php else: ?>
  <div class="space-y-6 max-w-3xl mx-auto px-4">
    <?php foreach ($notifications as $notif): ?>
      <div class="bg-white shadow-lg rounded-lg p-6 flex items-center gap-6 hover:shadow-xl transition-shadow duration-300">
        <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-400 text-white text-4xl">
          <i class="fas fa-tshirt"></i>
        </div>
        <div class="flex-1">
          <p class="font-semibold text-gray-800 text-lg mb-1"><?= htmlspecialchars($notif['sender_email']) ?></p>
          <p class="text-gray-700 text-base leading-relaxed"><?= htmlspecialchars($notif['message']) ?></p>
          <p class="text-sm text-gray-500 mt-2 italic"><?= timeAgo($notif['created_at']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

  </div>
    <footer class="mt-20 xl:mt-32 mx-auto w-full relative text-center bg-bar text-white">
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