<?php
session_start();
include('query.php'); // Memasukkan file query.php untuk digunakan

// Cek apakah pengguna sudah login dan memiliki email admin
function checkAdmin() {
    if (isset($_SESSION['email']) && $_SESSION['email'] === 'admin@bundalaundry.com') {
        return true;
    }
    return false;
}

// Jika pengguna bukan admin, arahkan ke halaman lain (misalnya homepage)
if (!checkAdmin()) {
    header('Location: index.php');  // Mengarahkan pengguna ke halaman utama atau halaman login
    exit(); // Pastikan eksekusi script berhenti setelah redirection
}
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
         <span class="text-white text-lg font-bold">Admin</span>
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
         <a href="#" 
            @click.prevent="activePage = 'all-users'; sidebarOpen = false" 
            class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
            :class="{ 'bg-gray-700': activePage === 'users' }">
             <i class="fas fa-users mr-3"></i>Daftar Pengguna
         </a>
         <a href="#" 
            @click.prevent="activePage = 'transactions'; sidebarOpen = false" 
            class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
            :class="{ 'bg-gray-700': activePage === 'transactions' }">
             <i class="fas fa-clipboard-list mr-3"></i>Daftar Transaksi
         </a>
         <a href="#" 
            @click.prevent="activePage = 'services'; sidebarOpen = false" 
            class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
            :class="{ 'bg-gray-700': activePage === 'services' }">
             <i class="fas fa-cogs mr-3"></i>Daftar Layanan
         </a>
         <a href="#" 
            @click.prevent="activePage = 'finances'; sidebarOpen = false" 
            class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
            :class="{ 'bg-gray-700': activePage === 'finances' }">
             <i class="fas fa-wallet mr-3"></i>Daftar Keuangan
         </a>
         <a href="#" 
            @click.prevent="activePage = 'customer-reports'; sidebarOpen = false" 
            class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
            :class="{ 'bg-gray-700': activePage === 'customer-reports' }">
             <i class="fas fa-file-alt mr-3"></i>Laporan Pelanggan
         </a>
         <a href="#" 
            @click.prevent="activePage = 'logout'; sidebarOpen = false" 
            class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700"
            :class="{ 'bg-gray-700': activePage === 'logout' }">
             <i class="fas fa-sign-out-alt mr-3"></i>Logout
         </a>
     </nav>
 
     <!-- Profile Section at the Bottom -->
     <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gray-900 mt-4">
         <div class="flex items-center">
             <!-- Profile Picture -->
             <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center">
                 <i class="fas fa-user text-white"></i> <!-- Placeholder for profile icon -->
             </div>
             <div class="ml-3 text-white">
                 <p class="text-sm font-medium">Admin Name</p>
                 <p class="text-xs">admin@example.com</p>
             </div>
         </div>
     </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col md:ml-64">
            <header class="bg-white shadow-sm h-16 flex items-center px-4 fixed top-0 right-0 left-0 md:left-64 z-30">
                <button @click="sidebarOpen = true" class="text-gray-500 md:hidden" aria-label="Open sidebar">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                <div class="flex items-center justify-between w-full">
                    <!-- Search Box -->
                    <div class="relative flex-1">
                        <input type="text" placeholder="Search..."
                            class="pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500"
                            aria-label="Search">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    
                    <!-- Right side: Notifications and User Avatar -->
                    <div class="ml-4 flex items-center justify-end">
                        <button class="text-gray-500 mr-4" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                        </button>
                    </div>
                </div>
            </header>            

            <main class="p-6 mt-16 flex-1 overflow-y-auto">
                <!-- Dashboard Content -->
                <div x-show="activePage === 'dashboard'">
                    <!-- Dashboard Overview -->
                    <h1 class="text-2xl font-bold mb-6">Dashboard Overview</h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <!-- Total Users -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-users text-blue-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Total Pengguna</p>
                                    <h3 class="text-xl font-bold">12334</h3>
                                </div>
                            </div>
                        </div>
                        <!-- Total Transactions -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-green-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Total Transaksi</p>
                                    <h3 class="text-xl font-bold">$12,345</h3>
                                </div>
                            </div>
                        </div>
                        <!-- Total Services -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-chart-line text-purple-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Total Layanan</p>
                                    <h3 class="text-xl font-bold">+15%</h3>
                                </div>
                            </div>
                        </div>
                        <!-- Total Reports -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-tasks text-yellow-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Total Laporan</p>
                                    <h3 class="text-xl font-bold">23</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- Manage Orders Section -->
                    <h2 class="text-xl font-semibold mt-8 mb-4">Manage Orders</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Confirm Orders -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Pesanan Dikonfirmasi</p>
                                    <h3 class="text-xl font-bold">5</h3>
                                </div>
                            </div>
                            <button class="mt-4 bg-blue-500 text-white py-2 px-4 rounded-lg w-full">Konfirmasi Pesanan</button>
                        </div>
                        <!-- Processing Orders -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-cogs text-orange-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Pesanan Diproses</p>
                                    <h3 class="text-xl font-bold">3</h3>
                                </div>
                            </div>
                            <button class="mt-4 bg-yellow-500 text-white py-2 px-4 rounded-lg w-full">Proses Pesanan</button>
                        </div>
                        <!-- Cancel Orders -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-ban text-red-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Pesanan Dibatalkan</p>
                                    <h3 class="text-xl font-bold">2</h3>
                                </div>
                            </div>
                            <button class="mt-4 bg-red-500 text-white py-2 px-4 rounded-lg w-full">Batalkan Pesanan</button>
                        </div>
                        <!-- Completed Orders -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-teal-500 text-2xl mr-4"></i>
                                <div>
                                    <p class="text-gray-500">Pesanan Selesai</p>
                                    <h3 class="text-xl font-bold">10</h3>
                                </div>
                            </div>
                            <button class="mt-4 bg-teal-500 text-white py-2 px-4 rounded-lg w-full">Selesaikan Pesanan</button>
                        </div>
                    </div>
                
                    <!-- Order Confirmation Section -->
                    <h2 class="text-xl font-semibold mt-8 mb-4">Konfirmasi Pesanan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                        <!-- Order Card -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-xl font-bold">ID Pesanan #12345</h3>
                            <p class="text-gray-500 mt-2">Nama Pelanggan: John Doe</p>
                            <p class="text-gray-500 mt-1">Tanggal Pesanan: 2025-04-25</p>
                            <p class="text-yellow-500 font-semibold mt-2">Status: Pending</p>
                            <div class="mt-4">
                                <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="confirmOrder(12348)">Konfirmasi</button>
                                <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="cancelOrder(12348)">Batalkan</button>
                                <button class="bg-green-500 text-white py-1 px-3 text-sm rounded-lg mt-2" @click="processOrder(12348)">Proses</button>
                            </div>
                        </div>
                
                        <!-- Order Card -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-xl font-bold">ID Pesanan #12346</h3>
                            <p class="text-gray-500 mt-2">Nama Pelanggan: Jane Smith</p>
                            <p class="text-gray-500 mt-1">Tanggal Pesanan: 2025-04-24</p>
                            <p class="text-yellow-500 font-semibold mt-2">Status: Pending</p>
                            <div class="mt-4">
                                <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="confirmOrder(12348)">Konfirmasi</button>
                                <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="cancelOrder(12348)">Batalkan</button>
                                <button class="bg-green-500 text-white py-1 px-3 text-sm rounded-lg mt-2" @click="processOrder(12348)">Proses</button>
                            </div>
                        </div>
                
                        <!-- Order Card -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-xl font-bold">ID Pesanan #12347</h3>
                            <p class="text-gray-500 mt-2">Nama Pelanggan: Alice Cooper</p>
                            <p class="text-gray-500 mt-1">Tanggal Pesanan: 2025-04-23</p>
                            <p class="text-yellow-500 font-semibold mt-2">Status: Pending</p>
                            <div class="mt-4">
                                <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="confirmOrder(12348)">Konfirmasi</button>
                                <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="cancelOrder(12348)">Batalkan</button>
                                <button class="bg-green-500 text-white py-1 px-3 text-sm rounded-lg mt-2" @click="processOrder(12348)">Proses</button>
                            </div>
                        </div>
                
                        <!-- Order Card -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-xl font-bold">ID Pesanan #12348</h3>
                            <p class="text-gray-500 mt-2">Nama Pelanggan: Bob Marley</p>
                            <p class="text-gray-500 mt-1">Tanggal Pesanan: 2025-04-22</p>
                            <p class="text-yellow-500 font-semibold mt-2">Status: Pending</p>
                            <div class="mt-4">
                                <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="confirmOrder(12348)">Konfirmasi</button>
                                <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg mr-2" @click="cancelOrder(12348)">Batalkan</button>
                                <button class="bg-green-500 text-white py-1 px-3 text-sm rounded-lg mt-2" @click="processOrder(12348)">Proses</button>
                            </div>
                        </div>                        
                    </div>
                </div>                      

                <!-- All Users Content -->
                <div x-show="activePage === 'all-users'">
                    <h1 class="text-2xl font-bold mb-6">Daftar Pengguna</h1>
                
                    <!-- Button to Add New User -->
                    <div class="mb-4">
                        <button class="bg-green-500 text-white py-2 px-4 rounded-lg text-sm" @click="addUser">Tambah Pengguna</button>
                    </div>
                    
                    <!-- Table for Users -->
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
                                <!-- Example row 1 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">John Doe</td>
                                    <td class="px-4 py-2">john@example.com</td>
                                    <td class="px-4 py-2 text-green-500">Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Example row 2 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">Jane Smith</td>
                                    <td class="px-4 py-2">jane@example.com</td>
                                    <td class="px-4 py-2 text-yellow-500">Tidak Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Additional rows can be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div x-show="activePage === 'transactions'">
                    <h1 class="text-2xl font-bold mb-6">Daftar Transaksi</h1>
                
                    <!-- Button to Add New User -->
                    <div class="mb-4">
                        <button class="bg-green-500 text-white py-2 px-4 rounded-lg text-sm" @click="addUser">Tambah Transaksi</button>
                    </div>
                    
                    <!-- Table for Users -->
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
                                <!-- Example row 1 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">John Doe</td>
                                    <td class="px-4 py-2">john@example.com</td>
                                    <td class="px-4 py-2 text-green-500">Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Example row 2 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">Jane Smith</td>
                                    <td class="px-4 py-2">jane@example.com</td>
                                    <td class="px-4 py-2 text-yellow-500">Tidak Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Additional rows can be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="activePage === 'services'">
                    <h1 class="text-2xl font-bold mb-6">Daftar Layanan</h1>
                
                    <!-- Button to Add New User -->
                    <div class="mb-4">
                        <button class="bg-green-500 text-white py-2 px-4 rounded-lg text-sm" @click="addUser">Tambah Layanan</button>
                    </div>
                    
                    <!-- Table for Users -->
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
                                <!-- Example row 1 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">John Doe</td>
                                    <td class="px-4 py-2">john@example.com</td>
                                    <td class="px-4 py-2 text-green-500">Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Example row 2 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">Jane Smith</td>
                                    <td class="px-4 py-2">jane@example.com</td>
                                    <td class="px-4 py-2 text-yellow-500">Tidak Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Additional rows can be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="activePage === 'finances'">
                    <h1 class="text-2xl font-bold mb-6">Daftar Keuangan</h1>
                
                    <!-- Button to Add New User -->
                    <div class="mb-4 flex items-center space-x-4">
                        <div>
                            <label for="startDate" class="block text-sm font-semibold text-gray-700">Tanggal Awal</label>
                            <input type="date" id="startDate" class="py-2 px-4 border rounded-lg text-sm" />
                        </div>
                        <div>
                            <label for="endDate" class="block text-sm font-semibold text-gray-700">Tanggal Akhir</label>
                            <input type="date" id="endDate" class="py-2 px-4 border rounded-lg text-sm" />
                        </div>
                        <div>
                            <button class="bg-blue-500 text-white py-2 px-4 rounded-lg text-sm mt-5" @click="searchData">Cari</button>
                        </div>
                        <div>
                            <button class="bg-gray-500 text-white py-2 px-4 rounded-lg text-sm mt-5" @click="printReport">Cetak</button>
                        </div>
                    </div>
                    
                    <!-- Table for Users -->
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
                                <!-- Example row 1 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">John Doe</td>
                                    <td class="px-4 py-2">john@example.com</td>
                                    <td class="px-4 py-2 text-green-500">Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Example row 2 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">Jane Smith</td>
                                    <td class="px-4 py-2">jane@example.com</td>
                                    <td class="px-4 py-2 text-yellow-500">Tidak Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Additional rows can be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="activePage === 'customer-reports'">
                    <h1 class="text-2xl font-bold mb-6">Laporan pelanggan</h1>
                
                    <!-- Button to Add New User -->
                    <div class="mb-4 flex items-center space-x-4">
                        <div>
                            <label for="startDate" class="block text-sm font-semibold text-gray-700">Tanggal Awal</label>
                            <input type="date" id="startDate" class="py-2 px-4 border rounded-lg text-sm" />
                        </div>
                        <div>
                            <label for="endDate" class="block text-sm font-semibold text-gray-700">Tanggal Akhir</label>
                            <input type="date" id="endDate" class="py-2 px-4 border rounded-lg text-sm" />
                        </div>
                        <div>
                            <button class="bg-blue-500 text-white py-2 px-4 rounded-lg text-sm mt-5" @click="searchData">Cari</button>
                        </div>
                        <div>
                            <button class="bg-gray-500 text-white py-2 px-4 rounded-lg text-sm mt-5" @click="printReport">Cetak</button>
                        </div>
                    </div>
                    
                    <!-- Table for Users -->
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
                                <!-- Example row 1 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">John Doe</td>
                                    <td class="px-4 py-2">john@example.com</td>
                                    <td class="px-4 py-2 text-green-500">Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Example row 2 -->
                                <tr class="border-b">
                                    <td class="px-4 py-2">Jane Smith</td>
                                    <td class="px-4 py-2">jane@example.com</td>
                                    <td class="px-4 py-2 text-yellow-500">Tidak Aktif</td>
                                    <td class="px-4 py-2">
                                        <button class="bg-blue-500 text-white py-1 px-3 text-sm rounded-lg">Lihat</button>
                                        <button class="bg-red-500 text-white py-1 px-3 text-sm rounded-lg">Hapus</button>
                                    </td>
                                </tr>
                
                                <!-- Additional rows can be added here -->
                            </tbody>
                        </table>
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
</html>