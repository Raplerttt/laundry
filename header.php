<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config.php'); // Pastikan di sini sudah ada $pdo koneksi database

// Cek login
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    // misal redirect login atau set userEmail kosong
    $userEmail = null;
} else {
    $userEmail = $_SESSION['userEmail'];
}

if ($userEmail) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_email = :email AND is_read = FALSE");
    $stmt->execute(['email' => $userEmail]);
    $unreadCount = $stmt->fetchColumn();
} else {
    $unreadCount = 0;
}
?>
<nav class="bg-bar shadow-lg fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16">
            <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
                <div class="flex-shrink-0 flex items-center">
                    <img class="block h-8 w-auto" src="https://tailwindflex.com/images/logo.svg" alt="Logo">
                    <span class="ml-2 text-xl font-bold text-white">Navbar</span>
                </div>
                <div class="flex sm:block sm:ml-6">
                    <div class="flex space-x-4">
                        <a href="index.html" class="text-white hover:text-black hover:bg-bar px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <a href="/riwayat-transaksi.php" class="text-white hover:text-black hover:bg-bar px-3 py-2 rounded-md text-sm font-medium">Riwayat Transaksi</a>
                    </div>
                </div>
            </div>

            <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                <!-- Desktop User Info -->
                <div class="hidden sm:flex sm:items-center" id="user-info-desktop">
                <button class="relative text-white hover:text-black px-3 py-2 rounded-md text-sm font-medium">
    <a href="notification.php" class="flex items-center">
      <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.992 2.992 0 0019 14V9a7 7 0 10-14 0v5a2.992 2.992 0 00-.595 1.595L3 17h5m4 0h-4" />
      </svg>
      <?php if ($unreadCount > 0): ?>
        <span class="absolute top-0 right-0 inline-flex items-center justify-center w-3 h-3 bg-red-500 rounded-full"></span>
      <?php endif; ?>
    </a>
</button>

                    <a href="edit-profile.php">
                        <img src="path/to/avatar.jpg" alt="Avatar" class="h-8 w-8 rounded-full ml-4 cursor-pointer" id="user-avatar-desktop">
                    </a>
                    <!-- Logout Button -->
                    <a href="/logout.php">
                    <button id="logout-button-desktop" class="ml-4 text-white hover:text-black px-3 py-2 rounded-md text-sm font-medium">Logout</button>
                    </a>
                </div>

                <!-- Desktop Auth Buttons -->
                <div class="hidden sm:flex" id="auth-buttons-desktop">
                    <a href="login.html" class="text-white hover:text-black px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="daftar.html" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">Sign Up</a>
                </div>

                <!-- Mobile menu button -->
                <div class="flex sm:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-black hover:bg-gray-700 focus:outline-none" id="mobile-menu-button" aria-expanded="false">
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="hidden sm:hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="index.html" class="text-white hover:text-black hover:bg-bar block px-3 py-2 rounded-md text-base font-medium">Home</a>
            <a href="riwayat-transaksi.html" class="text-white hover:text-black hover:bg-bar block px-3 py-2 rounded-md text-base font-medium">Riwayat Transaksi</a>
        </div>

        <!-- Mobile User Info -->
        <div class="border-t border-gray-700 pt-4 pb-3" id="user-info-mobile">
            <div class="flex items-center px-5">
                <img class="h-10 w-10 rounded-full" src="path/to/avatar.jpg" alt="User Avatar" id="user-avatar-mobile">
                <div class="ml-3">
                    <div class="text-base font-medium leading-none text-white">Username</div>
                </div>
            </div>
        </div>

        <!-- Mobile Auth Buttons -->
        <div class="px-5 pt-4 pb-3 flex flex-col space-y-2" id="auth-buttons-mobile">
            <a href="login.html" class="block w-full text-center text-white bg-gray-700 px-3 py-2 rounded-md text-base font-medium">Login</a>
            <a href="daftar.html" class="block w-full text-center bg-indigo-600 text-white px-3 py-2 rounded-md text-base font-medium">Sign Up</a>
        </div>
    </div>
</nav>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn'); // 'true' berarti user login
  
    const authButtonsDesktop = document.getElementById('auth-buttons-desktop');
    const authButtonsMobile = document.getElementById('auth-buttons-mobile');
    const userInfoDesktop = document.getElementById('user-info-desktop');
    const userInfoMobile = document.getElementById('user-info-mobile');
    const logoutButtonDesktop = document.getElementById('logout-button-desktop');
  
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
  
    // Logout button handler
    logoutButtonDesktop.addEventListener('click', function() {
      sessionStorage.removeItem('isLoggedIn');
      sessionStorage.removeItem('userEmail');
      sessionStorage.removeItem('userId');
      
      // Refresh page after logout
      window.location.reload();
    });
  
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
