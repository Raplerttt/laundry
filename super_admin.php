<?php
session_start();
include('config.php');
include('query.php');

// Fungsi cek apakah admin
function checkAdmin() {
    return isset($_SESSION['userEmail']) && $_SESSION['userEmail'] === 'admin@bundalaundry.com';
}

// Jika ada parameter ID dari detail transaksi
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query data transaksi berdasarkan ID
    $stmt = $pdo->prepare("SELECT * FROM checkout_orders WHERE id = ?");
    $stmt->execute([$id]);
    $detail = $stmt->fetch();
}

// Redirect jika bukan admin
if (!checkAdmin()) {
    header('Location: index.php');
    exit();
}

// Ambil email dari session
$email = $_SESSION['userEmail'];

// Ambil nama admin dari DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user ? $user['username'] : 'Admin';

// Hitung total user (selain admin)
$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email != 'admin@bundalaundry.com'");
$stmt_total->execute();
$totalUsers = $stmt_total->fetchColumn();

// Ambil daftar user (selain admin)
$stmt_users = $pdo->prepare("SELECT id, username, email, status FROM users WHERE email != 'admin@bundalaundry.com'");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua order
$stmt_orders = $pdo->prepare("SELECT * FROM orders ORDER BY id DESC");
$stmt_orders->execute();
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

$stmt_transactions = $pdo->prepare("
    SELECT co.*, p.nama_paket 
    FROM checkout_orders co
    LEFT JOIN paket p ON co.paket_id = p.id
");
$stmt_transactions->execute();
$transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);


$stmt_pakets = $pdo->prepare ("SELECT * FROM paket ORDER BY id ASC");
$stmt_pakets->execute();
$pakets = $stmt_pakets->fetchAll(PDO::FETCH_ASSOC);

// Handle update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['action']) && $_POST['action'] === 'update_order_status' &&
        isset($_POST['order_id'], $_POST['new_status'])
    ) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['new_status'];

        $query = "UPDATE orders SET status_pemesanan = :status WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':id', $orderId);

        if ($stmt->execute()) {
            echo "<script>window.location.href='manage_orders.php?success=1';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui status');</script>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $berat = floatval($_POST['berat']);
    $harga_ongkir = intval($_POST['harga_ongkir']);

    $stmt = $pdo->prepare("UPDATE checkout_orders SET berat = :berat, harga_ongkir = :harga_ongkir WHERE id = :id");
    $stmt->execute([
        'berat' => $berat,
        'harga_ongkir' => $harga_ongkir,
        'id' => $id
    ]);

    header("Location: super_admin.php?success=1");
    exit;
}

// Ambil jumlah pesanan yang dikonfirmasi
$query_confirmed = "SELECT COUNT(*) AS confirmed_count FROM orders WHERE status_pemesanan = 'Dikonfirmasi'";
$stmt_confirmed = $pdo->prepare($query_confirmed);
$stmt_confirmed->execute();
$confirmedCount = $stmt_confirmed->fetchColumn();

// Ambil jumlah pesanan yang diproses
$query_processing = "SELECT COUNT(*) AS processing_count FROM orders WHERE status_pemesanan = 'Diproses'";
$stmt_processing = $pdo->prepare($query_processing);
$stmt_processing->execute();
$processingCount = $stmt_processing->fetchColumn();

// Ambil jumlah pesanan yang dibatalkan
$query_cancelled = "SELECT COUNT(*) AS cancelled_count FROM orders WHERE status_pemesanan = 'Dibatalkan'";
$stmt_cancelled = $pdo->prepare($query_cancelled);
$stmt_cancelled->execute();
$cancelledCount = $stmt_cancelled->fetchColumn();

// Hitung jumlah transaksi
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM checkout_orders");
$stmt_count->execute();
$totalTransactions = $stmt_count->fetchColumn();

// Hitung total nominal transaksi
$query_total = "
    SELECT SUM(CAST(REPLACE(p.harga, 'Rp.', '') AS DECIMAL(10, 2))) AS total_transactions
    FROM orders o
    JOIN paket p ON o.paket_id = p.id
    WHERE o.status_pemesanan IN ('Diproses', 'Selesai')
";
$stmt_total_nominal = $pdo->prepare($query_total);
$stmt_total_nominal->execute();
$totalNominalTransactions = $stmt_total_nominal->fetchColumn();

$query_total_paket = "
    SELECT COUNT(*) AS total_paket
    FROM paket
";
$stmt_total_paket = $pdo->prepare($query_total_paket);
$stmt_total_paket->execute();
$totalPaket = $stmt_total_paket->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <div x-data="{
        sidebarOpen: false,
        activePage: 'dashboard'
    }" class="min-h-screen flex" @keydown.escape="sidebarOpen = false">
        <!-- Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 transform transition-transform duration-300 ease-in-out md:translate-x-0"
               :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">
            <div class="flex items-center justify-between h-16 px-4 bg-gray-900">
                <span class="text-white text-lg font-bold">Superadmin</span>
                <button @click="sidebarOpen = false" class="text-white md:hidden" aria-label="Close sidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="mt-5 overflow-y-auto h-[calc(100vh-4rem)]">
                <a href="#" 
                   @click.prevent="activePage = 'dashboard'; sidebarOpen = false" 
                   class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
                   :class="{ 'bg-gray-700': activePage === 'dashboard' }">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>

                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-2 text-gray-100 hover:bg-gray-700"
                            :aria-expanded="open"
                            aria-controls="users-menu">
                        <div class="flex items-center">
                            <i class="fas fa-users mr-3"></i>Users
                        </div>
                        <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="open" 
                         id="users-menu"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="bg-gray-700">
                        <a href="#" @click.prevent="activePage = 'all-users'; sidebarOpen = false" 
                           class="block px-8 py-2 text-gray-200 hover:bg-gray-600"
                           :class="{ 'bg-gray-600': activePage === 'all-users' }">All Users</a>
                        <a href="#" @click.prevent="activePage = 'add-user'; sidebarOpen = false" 
                           class="block px-8 py-2 text-gray-200 hover:bg-gray-600"
                           :class="{ 'bg-gray-600': activePage === 'add-user' }">Add User</a>
                    </div>
                </div>

                <a href="#" 
                   @click.prevent="activePage = 'daftar-transaksi'; sidebarOpen = false" 
                   class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
                   :class="{ 'bg-gray-700': activePage === 'daftar-transaksi' }">
                    <i class="fas fa-chart-bar mr-3"></i>Daftar Transaksi
                </a>

                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-2 text-gray-100 hover:bg-gray-700"
                            :aria-expanded="open"
                            aria-controls="users-menu">
                        <div class="flex items-center">
                            <i class="fas fa-users mr-3"></i>Layanan
                        </div>
                        <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="open" 
                         id="users-menu"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="bg-gray-700">
                         <a href="#" 
                        @click.prevent="activePage = 'daftar-layanan'; sidebarOpen = false" 
                        class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
                        :class="{ 'bg-gray-700': activePage === 'daftar-layanan' }">
                        <i class="fas fa-chart-bar mr-3"></i>Daftar Layanan
                        </a>
                        <a href="#" 
                        @click.prevent="activePage = 'add-layanan'; sidebarOpen = false" 
                        class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
                        :class="{ 'bg-gray-700': activePage === 'add-layanan' }">
                        <i class="fas fa-chart-bar mr-3"></i>Tambah Layanan
                        </a>
                    </div>
                </div>

                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-2 text-gray-100 hover:bg-gray-700"
                            :aria-expanded="open"
                            aria-controls="settings-menu">
                        <div class="flex items-center">
                            <i class="fas fa-cog mr-3"></i>Settings
                        </div>
                        <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="open"
                         id="settings-menu"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="bg-gray-700">
                        <a href="#" @click.prevent="activePage = 'general'; sidebarOpen = false" 
                           class="block px-8 py-2 text-gray-200 hover:bg-gray-600"
                           :class="{ 'bg-gray-600': activePage === 'general' }">General</a>
                        <a href="#" @click.prevent="activePage = 'security'; sidebarOpen = false" 
                           class="block px-8 py-2 text-gray-200 hover:bg-gray-600"
                           :class="{ 'bg-gray-600': activePage === 'security' }">Security</a>
                        <a href="#" @click.prevent="activePage = 'notifications'; sidebarOpen = false" 
                           class="block px-8 py-2 text-gray-200 hover:bg-gray-600"
                           :class="{ 'bg-gray-600': activePage === 'notifications' }">Notifications</a>
                    </div>
                </div>

                <a href="#" 
                   @click.prevent="activePage = 'analytics'; sidebarOpen = false" 
                   class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
                   :class="{ 'bg-gray-700': activePage === 'analytics' }">
                    <i class="fas fa-chart-bar mr-3"></i>Analytics
                </a>
                <a href="/logout.php" 
                   @click.prevent="activePage = 'logout'; sidebarOpen = false" 
                   class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
                   :class="{ 'bg-gray-700': activePage === 'logout' }">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col md:ml-64">
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 fixed top-0 right-0 left-0 md:left-64 z-30">
                <button @click="sidebarOpen = true" class="text-gray-500 md:hidden" aria-label="Open sidebar">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <div class="flex items-center">
                    <div class="relative">
                        <input type="text" placeholder="Search..."
                               class="pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                               aria-label="Search">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="ml-4 flex items-center">
                        <button class="text-gray-500 mr-4" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                        </button>
                        <div class="w-8 h-8 rounded-full bg-gray-300" role="img" aria-label="User avatar"></div>
                    </div>
                </div>
            </header>

            <main class="p-6 mt-16 flex-1 overflow-y-auto">
                <!-- Dashboard Content -->
                <div x-show="activePage === 'dashboard'">
                    <h1 class="text-2xl font-bold mb-6">Dashboard Overview</h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-users text-blue-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Total Users</p>
                                    <h3 class="text-xl font-bold"><?php echo ($totalUsers); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-green-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Revenue</p>
                                    <h3 class="text-xl font-bold">Rp.<?= number_format($totalTransactions, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-chart-line text-purple-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Growth</p>
                                    <h3 class="text-xl font-bold"><?= $totalPaket; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-tasks text-yellow-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Tasks</p>
                                    <h3 class="text-xl font-bold">23</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="text-xl font-semibold mt-8 mb-4">Manage Orders</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl mr-4"></i>
                            <div>
                            <p class="text-gray-500">Pesanan Dikonfirmasi</p>
                            <h3 class="text-xl font-bold"><?= $confirmedCount; ?></h3>
                            </div>
                            </div>
                            <button class="mt-4 bg-blue-500 text-white py-2 px-4 rounded-lg w-full">Konfirmasi pesanan</button>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl mr-4"></i>
                            <div>
                            <p class="text-gray-500">Pesanan Diproses</p>
                            <h3 class="text-xl font-bold"><?= $confirmedCount; ?></h3>
                            </div>
                            </div>
                            <button class="mt-4 bg-yellow-500 text-white py-2 px-4 rounded-lg w-full">Proses Pesanan</button>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl mr-4"></i>
                            <div>
                            <p class="text-gray-500">Pesanan Dibatalkan</p>
                            <h3 class="text-xl font-bold"><?= $confirmedCount; ?></h3>
                            </div>
                            </div>
                            <button class="mt-4 bg-red-500 text-white py-2 px-4 rounded-lg w-full">Batalkan Pesanan</button>
                        </div>
                    </div>
                    <h2 class="text-xl font-semibold mt-8 mb-4">Konfirmasi Pesanan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                    <?php foreach ($orders as $order): ?>
                    <!-- Order Card -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-xl font-bold">ID Pesanan #<?= $order['id']; ?></h3>
                        <p class="text-gray-500 mt-2">Nama Pelanggan: <?= htmlspecialchars($order['nama_depan'] . ' ' . $order['nama_belakang']); ?></p>
                        <p class="text-gray-500 mt-1">Alamat Jemput: <?= htmlspecialchars($order['alamat_penjemputan']); ?></p>
                        <p class="text-gray-500 mt-1">Alamat Antar: <?= htmlspecialchars($order['alamat_pengantaran']); ?></p>
                        <p class="text-yellow-500 font-semibold mt-2">Status: <?= $order['status_pemesanan']; ?></p>
<!-- 
                        <div class="mt-4">
                            <button 
                                @click.prevent="activePage = 'transactions'; sidebarOpen = false" 
                                class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 text-sm rounded-lg transition duration-200">
                                Detail Transaksi
                            </button>
                        </div> -->
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>

                <!-- All Users Content -->
                <div x-show="activePage === 'all-users'">
                    <h1 class="text-2xl font-bold mb-6">All Users</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">Nama Pengguna</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                    <tbody>
                    <?php 
                    // Cek apakah ada data pengguna
                    if (!empty($users)) {
                        foreach ($users as $user): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2">
                        <!-- Menggunakan htmlspecialchars dengan pengecekan null -->
                        <?php echo htmlspecialchars($user['username'] ?? 'Nama Tidak Tersedia'); ?>
                        </td>
                        <td class="px-4 py-2">
                        <?php echo htmlspecialchars($user['email'] ?? 'Email Tidak Tersedia'); ?>
                        </td>
                        <td class="px-4 py-2 
                        <?php echo ($user['status'] == 'Aktif') ? 'text-green-500' : 'text-yellow-500'; ?>">
                        <?php echo htmlspecialchars($user['status'] ?? 'Status Tidak Ditemukan'); ?>
                        </td>
                        <td class="px-4 py-2">
                        <!-- Tombol untuk mengubah status -->
                    <form action="change_status.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
                        <button type="submit" class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">
                            <?php echo ($user['status'] == 'Aktif') ? 'Non-Aktifkan' : 'Aktifkan'; ?>
                            <?php
                            ?>
                        </button>
                    </form>
                        </td>
                        <td class="px-4 py-2">
                            <!-- Tombol untuk menghapus pengguna -->
                            <form action="delete_user.php" method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
                                <button type="submit" name="delete_user" class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; 
                } else {
                echo "<tr><td colspan='4' class='px-4 py-2 text-center'>Tidak ada data pengguna.</td></tr>";
                }
            ?>
        </tbody>
    </table>
    </div>
</div>

                <!-- Add User Content -->
                <div x-show="activePage === 'add-user'">
                    <h1 class="text-2xl font-bold mb-6">Add User</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                    <h1 class="text-xl mb-4 font-bold">Form Tambah Pengguna</h1>
        <form action="proses_tambah_pengguna.php" method="POST">
            <label class="block mb-2">Username:</label>
            <input type="text" name="username" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Nama Lengkap:</label>
            <input type="text" name="full_name" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Email:</label>
            <input type="email" name="email" required class="w-full px-3 py-2 mb-4 border rounded">

            <label class="block mb-2">Password:</label>
            <input type="password" name="password" required class="w-full px-3 py-2 mb-4 border rounded">

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Simpan</button>
        </form>
                    </div>
                </div>

                <!-- User Roles Content -->
                <div x-show="activePage === 'daftar-transaksi'">
                    <h1 class="text-2xl font-bold mb-6">Daftar Transaksi</h1>
                <div class="bg-white p-6 rounded-lg shadow">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden shadow-md">
  <thead class="bg-blue-600 text-white">
    <tr>
      <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Nama Lengkap</th>
      <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Alamat Penjemputan</th>
      <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Alamat Pengantaran</th>
      <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Tanggal Transaksi</th>
      <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Nama Paket</th>
      <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Aksi</th>
    </tr>
  </thead>
  <tbody class="bg-white divide-y divide-gray-200">
    <?php if (!empty($transactions)): ?>
        <?php foreach ($transactions as $trx): ?>
      <tr class="hover:bg-gray-50 transition">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($trx['first_name'] . ' ' . $trx['last_name']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($trx['pickup_address']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($trx['delivery_address']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars(date('d M Y, H:i', strtotime($trx['created_at']))); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($trx['nama_paket'] ?? '-'); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-3">
          <a href="invoice.php?id=<?= htmlspecialchars($trx['id']); ?>" class="text-blue-600 hover:text-blue-900 font-semibold">Detail</a>

          <!-- Tombol edit yang buka modal -->
          <button 
            class="edit-btn text-yellow-600 hover:text-yellow-900 font-semibold bg-transparent border-none p-0 cursor-pointer" 
            data-id="<?= htmlspecialchars($trx['id']); ?>"
            data-berat="<?= htmlspecialchars($trx['berat'] ?? ''); ?>"
            data-harga_ongkir="<?= htmlspecialchars($trx['harga_ongkir'] ?? ''); ?>"
          >Edit</button>

          <form action="hapus_transaksi.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');" class="inline">
            <input type="hidden" name="id" value="<?= htmlspecialchars($trx['id']); ?>">
            <button type="submit" 
                    class="text-red-600 hover:text-red-900 font-semibold bg-transparent border-none p-0 cursor-pointer">
              Hapus
            </button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" class="text-center py-6 text-gray-500 italic">Tidak ada transaksi.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
<!-- Modal Popup untuk Edit -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl p-6 w-[90%] max-w-md relative">
    <h2 class="text-xl font-semibold mb-4">Edit Berat & Ongkir</h2>
    <form id="editForm" method="POST" action="update_transaksi.php" class="space-y-4">
      <input type="hidden" name="id" id="trxId" />

      <div>
        <label for="berat" class="block text-sm font-medium text-gray-700">Berat (kg):</label>
        <input type="number" step="0.01" name="berat" id="berat" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
      </div>

      <div>
        <label for="harga_ongkir" class="block text-sm font-medium text-gray-700">Harga Ongkir (Rp):</label>
        <input type="number" name="harga_ongkir" id="harga_ongkir" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
      </div>

      <div class="flex justify-end space-x-3 pt-2">
        <button type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">Simpan</button>
        <button type="button" id="closeModal"
          class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition">Batal</button>
      </div>
    </form>
  </div>
</div>

                </div>
                </div>
                <!-- daftar-layanan Content -->
                <div x-show="activePage === 'daftar-layanan'">
                    <h1 class="text-2xl font-bold mb-6">Daftar layanan</h1>
                <div class="bg-white p-6 rounded-lg shadow">
<table class="min-w-full table-auto">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Nama Layanan</th>
            <th class="px-4 py-2 text-left">Harga</th>
            <th class="px-4 py-2 text-left">Foto</th>
            <th class="px-4 py-2 text-left">Aksi</th>
        </tr>
    </thead>
<tbody>
        <?php if (!empty($pakets)): ?>
            <?php foreach ($pakets as $paket): ?>
                <tr class="border-b">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($paket['nama_paket']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($paket['harga']); ?></td>
                    <td class="px-4 py-2">
                        <?php if (!empty($paket['gambar'])): ?>
                            <img src="<?php echo htmlspecialchars($paket['gambar']); ?>" alt="Foto Paket" class="w-16 h-16 object-cover rounded">
                        <?php else: ?>
                            <span class="text-gray-500">Tidak ada gambar</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2">
                        <a href="edit_paket.php?id=<?php echo $paket['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                        <a href="hapus_paket.php?id=<?php echo $paket['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin ingin menghapus paket ini?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center py-4 text-gray-500">Tidak ada paket.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
                </div>
                </div>
                <!-- General Settings Content -->
                <div x-show="activePage === 'add-layanan'">
                    <h1 class="text-2xl font-bold mb-6">General Settings</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                    <form action="proses_tambah_layanan.php" method="POST" enctype="multipart/form-data">
                        <input type="text" name="nama_paket" placeholder="Nama Layanan">
                        <input type="text" name="harga" placeholder="Harga">
                        <input type="file" name="gambar">
                        <button type="submit">Tambah</button>
                    </form>
                    </div>
                </div>
                <!-- General Settings Content -->
                <div x-show="activePage === 'general'">
                    <h1 class="text-2xl font-bold mb-6">General Settings</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <p>This is the General Settings page content</p>
                    </div>
                </div>

                <!-- Security Settings Content -->
                <div x-show="activePage === 'security'">
                    <h1 class="text-2xl font-bold mb-6">Security Settings</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <p>This is the Security Settings page content</p>
                    </div>
                </div>

                <!-- Notifications Settings Content -->
                <div x-show="activePage === 'notifications'">
                    <h1 class="text-2xl font-bold mb-6">Notifications Settings</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <p>This is the Notifications Settings page content</p>
                    </div>
                </div>

                <!-- Analytics Content -->
                <div x-show="activePage === 'analytics'">
                    <h1 class="text-2xl font-bold mb-6">Analytics</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <p>This is the Analytics page content</p>
                    </div>
                </div>

                <!-- Logout Content -->
                <div x-show="activePage === 'logout'">
                    <h1 class="text-2xl font-bold mb-6">Logout</h1>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <p>You have been logged out</p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
<!-- Panggil file JS eksternal -->
<script src="editTransactionModal.js"></script>

</html>