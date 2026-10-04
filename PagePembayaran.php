<?php
require_once __DIR__ . '/callinglibs.php';

$db = new DBconnection();

// Ambil data request booking terakhir beserta tarif ruang dan durasinya
$query = "
    SELECT rb.*, r.nama as nama_ruang, r.tarif_per_jam 
    FROM request_booking rb 
    JOIN ruang r ON rb.ruang_id = r.id 
    ORDER BY rb.created_at DESC 
    LIMIT 1
";
$res = $db->send_query($query);
$latestBooking = (!empty($res->data)) ? $res->data[0] : null;

$totalHarga = 0;
$deposit = 0;

if ($latestBooking) {
    // Parse durasi interval PostgreSQL (misal "03:00:00" atau angka) jadi total jam
    $durasiStr = $latestBooking['durasi'];
    $hours = (int)substr($durasiStr, 0, 2); // Ambil jam dari format interval
    if ($hours <= 0) $hours = 1; // Fallback aman
    
    $totalHarga = (float)$latestBooking['tarif_per_jam'] * $hours;
    $deposit = $totalHarga * 0.5; // Deposit 50%
}

// Jika form disubmit untuk upload bukti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar']) && $latestBooking) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileName = time() . '_' . basename($_FILES['bukti_bayar']['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $targetPath)) {
        $updateQuery = "UPDATE request_booking SET bukti_pembayaran = $1 WHERE id = $2";
        $db->send_query($updateQuery, [$fileName, $latestBooking['id']]);
        $db->close_connection();
        
        header("Location: dashboard.php");
        exit();
    }
}
$db->close_connection();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Deposit QRIS</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; text-align: center; padding-top: 40px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: inline-block; max-width: 400px; width: 100%; text-align: left;}
        h2 { text-align: center; color: #2c3e50; }
        .price-box { background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .price-box p { margin: 5px 0; font-size: 1.05em; }
        button { background: #28a745; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; font-weight: bold; cursor: pointer; margin-top: 15px;}
        button:hover { background: #218838; }
        input[type="file"] { width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Konfirmasi Pembayaran Deposit</h2>
        <?php if ($latestBooking): ?>
            <div class="price-box">
                <p><strong>Ruangan:</strong> <?= htmlspecialchars($latestBooking['nama_ruang']) ?></p>
                <p>Total Harga Sewa: Rp <?= number_format($totalHarga, 0, ',', '.') ?></p>
                <hr style="border: 0; border-top: 1px solid #bce8f1; margin: 10px 0;">
                <p style="font-size: 1.2em; color: #0c5460;"><strong>Wajib Bayar Deposit (50%):</strong></p>
                <p style="font-size: 1.4em; color: #d9534f; font-weight: bold;">Rp <?= number_format($deposit, 0, ',', '.') ?></p>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <img src="https://via.placeholder.com/250x250.png?text=QRIS+DEPOSIT+50%" alt="QRIS" style="margin-bottom: 15px;">
        </div>
        
        <form action="PagePembayaran.php" method="POST" enctype="multipart/form-data">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Upload Bukti Transfer Deposit:</label>
            <input type="file" name="bukti_bayar" accept="image/*" required>
            <button type="submit">Kirim Bukti Pembayaran</button>
        </form>
    </div>
</body>
</html>