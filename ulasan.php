<?php
session_start();
require_once 'config.php'; // pastikan ini file koneksi PDO kamu

$errors = [];
$successMessage = '';

// Cek login
if (!isset($_SESSION['userId'])) {
    die("Anda harus login untuk mengirim ulasan.");
}

$userId = $_SESSION['userId'];
$itemId = $_GET['id'] ?? null; // item_id bisa dikirim lewat URL, contoh: review.php?item_id=3

if (!$itemId) {
    die("Item tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = trim($_POST['comment'] ?? '');

    // Validasi
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating harus antara 1 sampai 5.";
    }

    if (strlen($comment) < 5) {
        $errors[] = "Ulasan minimal 5 karakter.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (user_id, item_id, rating, review_text, created_at)
                VALUES (:user_id, :item_id, :rating, :review_text, CURRENT_TIMESTAMP)
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':item_id' => $itemId,
                ':rating' => $rating,
                ':review_text' => $comment
            ]);

            $successMessage = "Ulasan berhasil dikirim, terima kasih!";

        // Redirect ke index.php setelah 2 detik
        header("refresh:2;url=index.php");
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat menyimpan ulasan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
    <title>Ulasan</title>
</head>
<body>
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-20">
        <div class="bg-white shadow-2xl rounded-xl w-full max-w-xl mx-4 sm:mx-auto">
            <form method="POST" class="p-4 sm:p-6 flex flex-col gap-4">
                <!-- Header -->
                <div class="text-center">
                    <p class="text-gray-900 text-xl font-bold">Bagikan pengalaman Anda</p>
                    <p class="text-gray-500 text-sm mt-1">
                        Kami ingin mendengar pendapat Anda. Beri ulasan untuk membantu kami meningkatkan layanan.
                    </p>
                </div>

                <!-- Error Message -->
                <?php if (!empty($errors)) : ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error) : ?>
                                <li><?=htmlspecialchars($error)?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (!empty($successMessage)) : ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <?=htmlspecialchars($successMessage)?>
                    </div>
                <?php endif; ?>

                <!-- Star Rating -->
                <div class="flex justify-center gap-2 text-yellow-400 text-2xl">
                    <?php for ($i = 1; $i <= 5; $i++) : 
                        $checked = (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : '';
                    ?>
                        <label class="cursor-pointer hover:scale-110 transition" title="<?=$i?> Bintang">
                            <input type="radio" name="rating" value="<?=$i?>" class="hidden" <?=$checked?> />
                            <i class="fas fa-star"></i>
                        </label>
                    <?php endfor; ?>
                </div>

                <!-- Textarea -->
                <textarea
                    name="comment"
                    id="message"
                    rows="4"
                    class="block w-full p-3 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 resize-none"
                    placeholder="Tuliskan ulasan Anda di sini..."
                ><?=htmlspecialchars($_POST['comment'] ?? '')?></textarea>

                <!-- Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full h-11 font-semibold text-blue-600 border-2 border-blue-500 rounded-lg hover:bg-blue-600 hover:text-white transition duration-300"
                    >
                        Kirim Ulasan
                    </button>
                </div>
                <div class="text-left">
                    <p class="text-gray-500 text-sm mt-1">
                        Semua ulasan pada Laundry. Ulasan diverifikasi dalam waktu 48 jam sebelum diposting untuk memastikan keaslian dan keakuratan.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div> 
</body>
</html>
