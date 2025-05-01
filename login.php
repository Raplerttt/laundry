<?php

include('query.php');  // Memanggil query.php untuk menggunakan fungsi getUserByEmail

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mendapatkan input email dan password dari form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Mendapatkan data pengguna berdasarkan email
    $user = getUserByEmail($email);

    if ($user) {
        // Memeriksa password pengguna
        if (password_verify($password, $user['password'])) {
            // Login berhasil
            // Set sesi pengguna
            $_SESSION['isLoggedIn'] = true;
            $_SESSION['userEmail'] = $email;
            $_SESSION['userId'] = $user['id'];

            // Kirimkan data ke JavaScript untuk disimpan di sessionStorage
            echo "<script>
                    sessionStorage.setItem('isLoggedIn', 'true');
                    sessionStorage.setItem('userEmail', '$email');
                    sessionStorage.setItem('userId', '" . $user['id'] . "');
                    window.location.href = 'index.php';  // Redirect ke halaman utama setelah login
                  </script>";
            exit();
        } else {
            echo "Password salah.";
        }
    } else {
        echo "Email tidak ditemukan.";
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
    <title>Bunda Laundry</title>
    <style type="text/tailwindcss">
        @theme {
          --color-bar: #2375A5;
        }
      </style>
</head>
<body>
<nav class="bg-bar shadow-lg fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
          <div class="relative flex items-center justify-between h-16">
            <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
              <div class="flex-shrink-0 flex items-center">
                <img class="block h-8 w-auto" src="https://tailwindflex.com/images/logo.svg" alt="Logo">
                <span class="ml-2 text-xl font-bold text-white">Navbar</span>
              </div>
            </div>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
              <div class="hidden sm:flex sm:items-center">
                <a href="login.html" class="text-white hover:text-black px-3 py-2 rounded-md text-sm font-medium">Login</a>
                <a href="Daftar.html" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Sign Up</a>
              </div>
              <div class="sm:hidden">
                <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-expanded="false" id="mobile-menu-button">
                  <span class="sr-only">Open main menu</span>
                  <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="sm:hidden hidden" id="mobile-menu">
          <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="#" class="bg-gray-100 text-gray-900 block px-3 py-2 rounded-md text-base font-medium">Home</a>
            <div class="pt-4 pb-3 border-t border-gray-200">
              <div class="flex items-center px-3 space-y-2 flex-col">
                <a href="login.html" class="block w-full text-center text-gray-900 bg-gray-100 px-3 py-2 rounded-md text-base font-medium">Login</a>
                <a href="#" class="block w-full text-center bg-indigo-600 text-white px-3 py-2 rounded-md text-base font-medium">Sign Up</a>
              </div>
            </div>
          </div>
        </div>
</nav>
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
<div class="flex flex-col md:flex-row min-h-screen">
        <!-- Form Section -->
        <div class="w-full md:w-1/2 bg-gray-100 p-8 flex items-center justify-center">
          <div class="max-w-sm w-full text-gray-600 space-y-5">
            <div class="text-center pb-8">
              <h3 class="text-gray-800 text-2xl font-bold sm:text-3xl">
                Log in to your account
              </h3>
            </div>
            <form action="login.php" method="POST" class="space-y-5">
              <div>
                  <label class="font-medium">Email</label>
                  <input type="email" name="email" required class="w-full mt-2 px-3 py-2 text-gray-500 bg-transparent outline-none border focus:border-indigo-600 shadow-sm rounded-lg" />
              </div>
              <div>
                  <label class="font-medium">Password</label>
                  <input type="password" name="password" required class="w-full mt-2 px-3 py-2 text-gray-500 bg-transparent outline-none border focus:border-indigo-600 shadow-sm rounded-lg" />
              </div>
              <!-- Pesan error jika login gagal -->
              <?php if (isset($error_message)): ?>
                  <div class="text-red-600 mt-2">
                      <?php echo $error_message; ?>
                  </div>
              <?php endif; ?>
              <div class="flex items-center justify-between text-sm">
                  <div class="flex items-center gap-x-3">
                      <input type="checkbox" id="remember-me-checkbox" class="checkbox-item peer hidden" />
                      <label for="remember-me-checkbox" class="relative flex w-5 h-5 bg-white peer-checked:bg-indigo-600 rounded-md border ring-offset-2 ring-indigo-600 duration-150 peer-active:ring cursor-pointer after:absolute after:inset-x-0 after:top-[3px] after:m-auto after:w-1.5 after:h-2.5 after:border-r-2 after:border-b-2 after:border-white after:rotate-45"></label>
                      <span>Ingat saya</span>
                  </div>
                  <a href="javascript:void(0)" class="text-center text-indigo-600 hover:text-indigo-500">Lupa Password</a>
              </div>
              <button class="w-full px-4 py-2 text-white font-medium bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-600 rounded-lg duration-150">Masuk</button>
            </form>
            <p class="text-center">
              Belum mempunyai akun
              <a href="javascript:void(0)" class="font-medium text-indigo-600 hover:text-indigo-500">Daftar</a>
            </p>
            <p class="text-center">
                <a href="javascript:void(0)" class="font-medium text-indigo-600 hover:text-indigo-500">Masuk Sebagai Admin</a>
              </p>
          </div>
        </div>
      
        <!-- Image Section -->
        <div class="w-full md:w-1/2 hidden md:block">
          <img src="https://picsum.photos/800/600" alt="Beautiful Image" class="object-cover w-full h-full" />
        </div>
</div>
<footer class="mx-auto w-full relative text-center bg-bar text-white">
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