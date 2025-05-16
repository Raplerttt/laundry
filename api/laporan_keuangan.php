<?php
require 'config.php';

$start = $_GET['start'] ?? date('Y-01-01');
$end = $_GET['end'] ?? date('Y-m-d');

$sql = "
SELECT
  EXTRACT(YEAR FROM c.created_at) AS tahun,
  EXTRACT(MONTH FROM c.created_at) AS bulan,
  SUM(CAST(regexp_replace(p.harga, '[^0-9]', '', 'g') AS INTEGER) + c.harga_ongkir) AS pemasukan,
  COALESCE(SUM(pg.total_pengeluaran), 0) AS pengeluaran,
  SUM(CAST(regexp_replace(p.harga, '[^0-9]', '', 'g') AS INTEGER) + c.harga_ongkir) - COALESCE(SUM(pg.total_pengeluaran), 0) AS laba
FROM checkout_orders c
JOIN paket p ON c.paket_id = p.id
LEFT JOIN (
  SELECT
    EXTRACT(YEAR FROM tanggal) AS tahun,
    EXTRACT(MONTH FROM tanggal) AS bulan,
    SUM(jumlah) AS total_pengeluaran
  FROM pengeluaran
  GROUP BY 1, 2
) pg ON pg.tahun = EXTRACT(YEAR FROM c.created_at)
   AND pg.bulan = EXTRACT(MONTH FROM c.created_at)
WHERE c.created_at BETWEEN :start AND :end
GROUP BY EXTRACT(YEAR FROM c.created_at), EXTRACT(MONTH FROM c.created_at)
ORDER BY tahun, bulan;
";

try {
    // Debug: tampilkan query dan parameter
    error_log("SQL Query: " . $sql);
    error_log("Params: start = $start, end = $end");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['start' => $start, 'end' => $end]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: tampilkan hasil fetch
    error_log("Result: " . json_encode($data));
    
    header('Content-Type: application/json');
    echo json_encode($data);
} catch (PDOException $e) {
    // Tangkap error dan tampilkan dengan jelas di log
    error_log("PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
