<?php
session_start();
require_once 'config.php';

// Ambil user yang sedang login
$userId = $_SESSION['user_id'] ?? null;
$user = [];

if ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name']; // pastikan <input name="name">
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Hash password jika tidak kosong
  $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

  $sql = "UPDATE users SET name = :name, nomer_telepon = :phone, email = :email";
  if ($hashedPassword) {
      $sql .= ", password = :password";
  }
  $sql .= " WHERE id = :id";

  $stmt = $pdo->prepare($sql);

  $params = [
      'name' => $name,
      'phone' => $phone,
      'email' => $email,
      'id' => $userId,
  ];

  if ($hashedPassword) {
      $params['password'] = $hashedPassword;
  }

  $stmt->execute($params);
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

<?php include('header.php')?>
    <div class="w-full">
        <div class="w-full">
          <section class="relative w-full flex items-center justify-center text-center">
            <div class="relative w-full">
              <img class="w-full h-100 object-cover rounded-md" src="https://images.unsplash.com/photo-1680725779155-456faadefa26" alt="Random image">
              <div class="absolute inset-0 bg-gray-700 opacity-60 rounded-md"></div>
              <div class="absolute inset-0 flex items-center justify-center">
                <h2 class="text-white text-3xl font-bold">Masuk</h2>
              </div>
            </div>
          </section>
        </div>
    </div>
    <div class="w-full h-full py-10 flex gap-8 items-start justify-center bg-gray-900 dark:bg-white">
        <!-- Wrapper with background and shadow -->
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-4xl">
    
            <div class="flex gap-8">
    
            <!-- Profile picture with pencil icon for change -->
            <div class="w-[150px] h-[150px] bg-gray-500 rounded-lg flex items-center justify-center relative">
                <img src="https://via.placeholder.com/150" alt="Profile Picture" class="w-full h-full object-cover rounded-full" />
    
    <!-- Change profile picture (pencil icon) -->
                <div class="absolute bottom-0 right-0 p-2 rounded-full cursor-pointer">
                    <input type="file" id="profile-pic" class="hidden" />
        <!-- Pencil icon (Font Awesome) -->
        <label for="profile-pic" class="text-[#19425A] text-lg">
            <i class="fas fa-pencil-alt"></i>
        </label>
    </div>
            </div>
                <!-- Right side: Profile form -->
                <div class="flex-1 text-black p-6 rounded-lg justify-end">
    
                <form action="" method="POST">
    <!-- Full Name -->
    <div class="mb-4">
        <label for="full-name" class="block text-sm font-medium">Nama Lengkap</label>
        <input type="text" id="full-name" name="full-name" class="w-full p-3 mt-1 rounded-md border border-gray-200"
            value="<?= htmlspecialchars($user['name'] ?? '') ?>" required />
    </div>

    <!-- Phone Number -->
    <div class="mb-4">
        <label for="phone" class="block text-sm font-medium">No. HP</label>
        <input type="text" id="phone" name="phone" class="w-full p-3 mt-1 rounded-md border border-gray-200"
            value="<?= htmlspecialchars($user['nomer_telepon'] ?? '') ?>" required />
    </div>

    <!-- Email -->
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium">Email</label>
        <input type="email" id="email" name="email" class="w-full p-3 mt-1 rounded-md border border-gray-200"
            value="<?= htmlspecialchars($user['email'] ?? '') ?>" required />
    </div>

    <!-- Password -->
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium">Kata Sandi (kosongkan jika tidak diubah)</label>
        <input type="password" id="password" name="password" class="w-full p-3 mt-1 rounded-md border border-gray-200"
            placeholder="Kata Sandi baru" />
    </div>

    <!-- Buttons -->
    <div class="flex gap-4 justify-center">
        <button type="submit" class="w-1/2 bg-blue-500 p-3 rounded-md text-white font-semibold hover:bg-blue-600">Simpan</button>
        <a href="dashboard.php" class="w-1/2 bg-red-500 p-3 rounded-md text-white font-semibold hover:bg-red-600 text-center">Keluar</a>
    </div>
</form>
                </div>
    
            </div>
        </div>
    </div>
    
    <footer class="xl:mt-20 mx-auto w-full relative text-center bg-bar text-white">
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