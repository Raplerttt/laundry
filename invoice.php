<?php
include('query.php');

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) die("Order ID tidak valid.");

$stmt = $pdo->prepare("
    SELECT co.*, p.nama_paket, p.harga 
    FROM checkout_orders co
    LEFT JOIN paket p ON co.paket_id = p.id
    WHERE co.id = :order_id
    LIMIT 1
");
$stmt->execute(['order_id' => $order_id]);
$order = $stmt->fetch();

if (!$order) die("Order tidak ditemukan.");

$customerName = $order['first_name'] . ' ' . $order['last_name'];
$pickupAddress = $order['pickup_address'];
$deliveryAddress = $order['delivery_address'];
$phone = $order['phone'];
$createdAt = date('d M Y', strtotime($order['created_at']));

// Bersihkan string harga "Rp.7000" -> 7000
$harga_raw = $order['harga']; // Contoh: "Rp.7000"
$harga_numeric = (int) preg_replace('/[^0-9]/', '', $harga_raw); // ambil angka saja

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

$totalAmount = $harga_numeric;

// Misal dapat dari DB: 
$harga_raw = "Rp.7000";

// Ubah ke angka murni
$harga_paket = (int) preg_replace('/[^0-9]/', '', $harga_raw);
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Invoice Order #<?php echo htmlspecialchars($order['id']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* A4 size print styles */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            .no-print { display: none; }
            body {
                margin: 0; padding: 0;
                height: 297mm; width: 210mm;
                overflow: hidden;
            }
            .min-h-screen {
                min-height: auto;
                height: 297mm; width: 210mm;
                margin: 0 auto;
                padding: 0;
                display: block;
            }
            .invoice-box {
                box-shadow: none;
                border: 1px solid #e5e7eb;
                height: 287mm; width: 200mm;
                padding: 5mm;
                margin: 0 auto;
                overflow: hidden;
                font-size: 12px;
            }
            .invoice-box * {
                margin-bottom: 0.2rem !important;
                line-height: 1.1 !important;
            }
            .text-3xl { font-size: 1.25rem !important; }
            .text-xl { font-size: 1rem !important; }
            .text-lg { font-size: 0.875rem !important; }
            table { font-size: 0.75rem !important; }
            th, td { padding: 0.2rem 0.5rem !important; }
            .mt-6 { margin-top: 0.5rem !important; }
            .pb-6 { padding-bottom: 0.5rem !important; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans" x-data="invoiceData()">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="invoice-box bg-white p-8 rounded-xl shadow-lg">
                <!-- Header -->
                <div class="flex justify-between items-center border-b pb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Invoice</h1>
                        <p class="text-gray-600">Order Reference: #<?php echo htmlspecialchars($order['id']); ?></p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-xl font-semibold text-gray-900">Bunda Laundry</h2>
                        <p class="text-gray-600">Cibarengkok, Sukajadi, Kota Bandung</p>
                    </div>
                </div>

                <!-- Billing Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Pelanggan</h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($customerName); ?></p>
                        <p class="text-gray-600">Phone: <?php echo htmlspecialchars($phone); ?></p>
                        <p class="text-gray-600">Alamat Penjemputan: <?php echo nl2br(htmlspecialchars($pickupAddress)); ?></p>
                        <p class="text-gray-600">Alamat Pengantaran: <?php echo nl2br(htmlspecialchars($deliveryAddress)); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-600">Tanggal Pesanan: <?php echo $createdAt; ?></p>
                        <p class="text-gray-600">Status Pembayaran: <span class="font-medium">VALID</span></p>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 font-semibold text-gray-900 border border-gray-300">Paket</th>
                                <th class="py-2 px-4 font-semibold text-gray-900 border border-gray-300">Qty</th>
                                <th class="py-2 px-4 font-semibold text-gray-900 border border-gray-300">Harga</th>
                                <th class="py-2 px-4 font-semibold text-gray-900 border border-gray-300 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b">
                                <td class="py-2 px-4 border border-gray-300">
                                    <?php echo htmlspecialchars($order['nama_paket'] ?: 'Laundry Service'); ?>
                                </td>
                                <td class="py-2 px-4 border border-gray-300">1</td>
                                <td><?= formatRupiah($totalAmount) ?></td>
                                <td class="py-2 px-4 border border-gray-300 text-right">Rp <?php echo number_format($harga_paket, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="mt-6 flex justify-end">
                    <div class="w-full md:w-1/3">
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span>Rp. <?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (0%)</span>
                                <span>0.00 </span>
                            </div>
                            <div class="flex justify-between font-semibold text-lg border-t pt-2">
                                <span>Total</span>
                                <span>Rp. <?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center text-gray-600">
                    <p>Terima Kasih sudah mencuci di Bunda Laundry</p>
                    <p>Jika terdapat pertanyaan, hubungi +6287771177155</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-center gap-4 no-print mt-4">
                <button 
                    @click="window.print()"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition duration-300"
                >
                    Print Invoice
                </button>
                <button 
                    @click="downloadPDF()"
                    class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition duration-300"
                >
                    Download PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        function invoiceData() {
            return {
                order: {
                    id: '<?php echo htmlspecialchars($order['id']); ?>',
                    totalAmount: <?php echo $totalAmount; ?>
                },
                customer: {
                    name: '<?php echo addslashes($customerName); ?>',
                    pickup: '<?php echo addslashes($pickupAddress); ?>',
                    postalCode: '<?php echo addslashes($deliveryAddress); ?>',
                },
                payment: {
                    status: 'VALID',
                    transactionDate: '<?php echo $createdAt; ?>'
                },
                invoiceDate: new Date().toLocaleDateString(),
                get subtotal() {
                    return this.order.totalAmount;
                },
                get tax() {
                    return 0;
                },
                get total() {
                    return this.subtotal + this.tax;
                },
                downloadPDF() {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                    const invoiceElement = document.querySelector('.invoice-box');
                html2canvas(invoiceElement).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const imgProps = doc.getImageProperties(imgData);
                    const pdfWidth = doc.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                    doc.save(`invoice_${this.order.id}.pdf`);
                });
            }
        };
    }
</script>
</body>
</html>