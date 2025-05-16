<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
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
</body>
</html>
