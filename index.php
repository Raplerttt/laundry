<?php
include 'config.php';
include 'query.php';
session_start();

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
  // Jika belum login, arahkan ke halaman login
  header("Location: login.html");
  exit;
}

try {
    $stmt = $pdo->query("SELECT 
    p.id, 
    p.nama_paket, 
    p.harga,
    COALESCE(ROUND(AVG(r.rating)::numeric, 1), 0) AS avg_rating
 FROM paket p
 LEFT JOIN rating r ON p.id = r.id_paket
 GROUP BY p.id");

    $pakets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Query error: " . $e->getMessage();
    die();
}

// Panggil fungsi untuk mendapatkan semua ulasan
$reviews = getAllReviews();

// Mengelompokkan ulasan berdasarkan item_id (paket_id)
$reviewsGrouped = [];
foreach ($reviews as $review) {
    $reviewsGrouped[$review['item_id']][] = $review;
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
      display: none;
    }
  </style>
  <title>Bunda Laundry</title>
</head>
<body>
<?php include('header.php')?>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <div class="border-gray-200 rounded-lg h-96 flex items-center justify-center">
        <section class="relative flex items-center justify-center min-h-screen px-6 text-center">
          <div class="absolute inset-0 bg-custom-radial"></div>
          <div class="relative z-10 max-w-2xl">
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900">"Layanan Bunda Laundry Cepat & Bersih"</h1>
            <p class="mt-4 text-lg text-gray-600">"Kami menyediakan layanan laundry berkualitas dengan harga terjangkau. Ambil dan antar gratis untuk area tertentu!"</p>
            <p class="mt-6 inline-block px-6 py-3 text-black border-1 border-black rounded-lg shadow-lg transition">Layanan Kami</p>
          </div>
        </section>
      </div>
    </div>
</div>
<div class="bg-[#19425A]">
  <div class="px-2 py-3 sm:px-0">
      <section>
          <div class="relative items-center w-full px-5 py-12 mx-auto md:px-12 lg:px-24 max-w-7xl">
              <div class="grid w-full grid-cols-1 gap-6 mx-auto sm:grid-cols-2 lg:grid-cols-4">
                  <div class="p-6 text-center sm:text-left">
                      <h1 class="mx-auto mb-6 mt-2 text-2xl font-semibold leading-none tracking-tighter text-white lg:text-4xl">Keunggulan Layanan</h1>
                      <p class="mx-auto text-sm leading-relaxed text-white">Layanan Antar Jemput Gratis</p>
                  </div>
                  <div class="p-6">
                      <div class="flex flex-col items-center sm:items-start">
                          <div class="bg-[#1CD7AF] p-3 rounded-lg mb-6">
                              <i class="fas fa-clock fa-2x text-white"></i>
                          </div>
                          <h1 class="mb-2 text-xl font-semibold leading-none tracking-tighter text-white lg:text-xl">Cepat dan Efisien</h1>
                          <p class="mx-auto text-sm leading-relaxed text-white">
                              Pakaian bersih dalam waktu 24 jam
                          </p>
                      </div>
                  </div>
                  <div class="p-6">
                      <div class="flex flex-col items-center sm:items-start">
                          <div class="bg-[#FC5B11] p-3 rounded-lg mb-6">
                              <i class="fas fa-leaf fa-2x text-white"></i>
                          </div>
                          <h1 class="mb-2 text-xl font-semibold leading-none tracking-tighter text-white lg:text-xl">Kualitas Terjamin</h1>
                          <p class="mx-auto text-sm leading-relaxed text-white">
                              Menggunakan detergen ramah lingkungan
                          </p>
                      </div>
                  </div>
                  <div class="p-6">
                      <div class="flex flex-col items-center sm:items-start">
                          <div class="bg-[#479ABE] p-3 rounded-lg mb-6">
                              <i class="fas fa-tags fa-2x text-white"></i>
                          </div>
                          <h1 class="mb-2 text-xl font-semibold leading-none tracking-tighter text-white lg:text-xl">Harga Terjangkau</h1>
                          <p class="mx-auto text-sm leading-relaxed text-white">
                              Berbagai paket hemat sesuai kebutuhan
                          </p>
                      </div>
                  </div>
              </div>
          </div>
      </section>
  </div>
</div>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
  <div class="px-4 py-6 sm:px-0">
    <section>
      <h1 class="text-3xl font-bold text-gray-800 mb-5 text-center">Layanan Kami</h1>
      <p class="text-center mb-10">Kami menyediakan berbagai layanan laundry berkualitas untuk memenuhi kebutuhan Anda. <br> Pilih layanan yang sesuai dan nikmati pakaian bersih, wangi, serta rapi!</p>
      
<div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
<?php foreach ($pakets as $paket): ?>
    <?php
        $stars = floor($paket['avg_rating']);
        $empty = 5 - $stars;
    ?>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <a href="detail-paket.php?id=<?= $paket['id'] ?>">
            <img class="w-full object-cover" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSzJOBc888horStIw5v_cpA6OOGG39NMeDgEg&s" alt="Product Image" />
            <div class="p-5">
                <h3 class="text-md font-semibold text-gray-800"><?= htmlspecialchars($paket['nama_paket']) ?></h3>
                <div class="flex items-center justify-between mt-2">
                    <div class="flex items-center">
                        <?php for ($i = 0; $i < $stars; $i++): ?>
                            <i class="fas fa-star text-yellow-500 text-sm"></i>
                        <?php endfor; ?>
                        <?php for ($i = 0; $i < $empty; $i++): ?>
                            <i class="fas fa-star text-gray-300 text-sm"></i>
                        <?php endfor; ?>
                        <span class="ml-1 text-xs text-gray-600">(<?= number_format($paket['avg_rating'], 1) ?>)</span>
                    </div>
                    <span class="text-lg font-bold text-orange-500"><?= htmlspecialchars($paket['harga']) ?>/Kg</span>
                </div>
            </div>
        </a>
    </div>
<?php endforeach; ?>
</div>
  </section>
  </div>
</div>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
  <div class="px-4 py-6 sm:px-0">
  <section>
    <h2 class="text-3xl font-bold text-gray-800 mb-5 text-center">Testimoni Pelanggan</h2>
    <ul role="list" class="mx-auto mt-10 grid max-w-2xl grid-cols-1 gap-6 sm:gap-8 lg:mt-10 lg:max-w-none lg:grid-cols-3">
        <?php foreach ($reviews as $review): ?>
        <li>
            <ul role="list" class="flex flex-col gap-y-6 sm:gap-y-8">
                <li>
                    <figure class="relative rounded-2xl bg-white p-6 shadow-xl shadow-slate-900/10">
                        <svg aria-hidden="true" width="105" height="78" class="absolute left-6 top-6 fill-slate-100">
                            <path d="M25.086 77.292c-4.821 0-9.115-1.205-12.882-3.616-3.767-2.561-6.78-6.102-9.04-10.622C1.054 58.534 0 53.411 0 47.686c0-5.273.904-10.396 2.712-15.368 1.959-4.972 4.746-9.567 8.362-13.786a59.042 59.042 0 0 1 12.43-11.3C28.325 3.917 33.599 1.507 39.324 0l11.074 13.786c-6.479 2.561-11.677 5.951-15.594 10.17-3.767 4.219-5.65 7.835-5.65 10.848 0 1.356.377 2.863 1.13 4.52.904 1.507 2.637 3.089 5.198 4.746 3.767 2.41 6.328 4.972 7.684 7.684 1.507 2.561 2.26 5.5 2.26 8.814 0 5.123-1.959 9.19-5.876 12.204-3.767 3.013-8.588 4.52-14.464 4.52Zm54.24 0c-4.821 0-9.115-1.205-12.882-3.616-3.767-2.561-6.78-6.102-9.04-10.622-2.11-4.52-3.164-9.643-3.164-15.368 0-5.273.904-10.396 2.712-15.368 1.959-4.972 4.746-9.567 8.362-13.786a59.042 59.042 0 0 1 12.43-11.3C82.565 3.917 87.839 1.507 93.564 0l11.074 13.786c-6.479 2.561-11.677 5.951-15.594 10.17-3.767 4.219-5.65 7.835-5.65 10.848 0 1.356.377 2.863 1.13 4.52.904 1.507 2.637 3.089 5.198 4.746 3.767 2.41 6.328 4.972 7.684 7.684 1.507 2.561 2.26 5.5 2.26 8.814 0 5.123-1.959 9.19-5.876 12.204-3.767 3.013-8.588 4.52-14.464 4.52Z"></path>
                        </svg>
                        <blockquote class="relative">
                            <p class="text-lg tracking-tight text-slate-900"><?= htmlspecialchars($review['review_text']) ?></p>
                        </blockquote>
                        <figcaption class="relative mt-6 flex items-center justify-between border-t border-slate-100 pt-6">
                          <div>
        <div class="font-display text-base text-slate-900"><?= htmlspecialchars($review['name']) ?></div>
    </div>
    <?php if (!empty($review['profile_picture'] ?? null)): ?>
        <div class="overflow-hidden rounded-full bg-slate-50">
            <img alt="" class="h-14 w-14 object-cover" style="color:transparent"
                src="<?= htmlspecialchars($review['profile_picture']) ?>">
        </div>
    <?php endif; ?>
</figcaption>

                    </figure>
                </li>
            </ul>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
  </div>
</div>
<footer class="mt-10 xl:mt-10 mx-auto w-full relative text-center bg-bar text-white">
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