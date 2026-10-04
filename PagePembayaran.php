<?php
require_once __DIR__ . '/callinglibs.php';
// Assuming they got here after bookingpage.php redirected them.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran QRIS</title>
</head>
<body>
    <div style="text-align: center; margin-top: 50px;">
        <h2>Scan QRIS untuk Pembayaran</h2>
        <!-- Dummy QRIS Image -->
        <img src="https://via.placeholder.com/300x300.png?text=QRIS+Dummy" alt="QRIS">
        
        <form id="paymentForm" action="index.php" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
            <label>Upload Bukti Transfer:</label><br><br>
            <input type="file" name="bukti_bayar" accept="image/*" required><br><br>
            <button type="submit">Konfirmasi Pembayaran</button>
        </form>
    </div>

    <script>
        // THIS IS HOW YOU DO THE POPUP
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Stop instant redirect
            alert("Request berhasil dibuat! Akan segera dikonfirmasi oleh admin by WA. Terima Kasih.");
            this.submit(); // Continue submitting the form to index.php
        });
    </script>
</body>
</html>