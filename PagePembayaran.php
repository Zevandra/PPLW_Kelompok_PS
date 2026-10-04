<?php
require_once __DIR__ . '/callinglibs.php';
 
// Jika form disubmit dan ada file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    $uploadDir = __DIR__ . '/uploads/';
    
    // Pastikan folder uploads ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['bukti_bayar']['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $targetPath)) {
        $db = new DBconnection();
        // Update booking terakhir yang dibuat
        $query = "UPDATE request_booking SET bukti_pembayaran = $1 WHERE id = (SELECT id FROM request_booking ORDER BY created_at DESC LIMIT 1)";
        $db->send_query($query, [$fileName]);
        $db->close_connection();
        
        // Redirect langsung ke dashboard
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran QRIS</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; text-align: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: inline-block; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; cursor: pointer; margin-top: 15px;}
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Scan QRIS untuk Pembayaran</h2>
        <img src="https://via.placeholder.com/300x300.png?text=QRIS+Kelompok+PS" alt="QRIS" style="margin: 15px 0;">
        
        <!-- FORM TANPA JS INTERFERENCE -->
        <form action="PagePembayaran.php" method="POST" enctype="multipart/form-data">
            <label style="display:block; margin-bottom:10px; font-weight:bold;">Upload Bukti Transfer:</label>
            <input type="file" name="bukti_bayar" accept="image/*" required><br>
            <button type="submit">Konfirmasi Pembayaran</button>
        </form>
    </div>
</body>
</html>