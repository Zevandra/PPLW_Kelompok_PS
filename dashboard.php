<?php
require_once __DIR__ . '/callinglibs.php';

$db = new DBconnection();

// Ambil data ruangan
$ruangClass = new Ruang($db);
$listRuang = $ruangClass->getAll()->data ?? [];

// Ambil data unit
$unitClass = new Unit($db);
$listUnit = $unitClass->getAll()->data ?? [];

$db->close_connection();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKU ADMIN PE ES - Booking Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; color: #333; }
        .hero { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: white; padding: 80px 20px; text-align: center; }
        .hero h1 { margin: 0 0 15px 0; font-size: 3em; }
        .hero p { font-size: 1.2em; margin-bottom: 30px; opacity: 0.9; }
        .btn-booking { background-color: #ff4757; color: white; padding: 15px 35px; text-decoration: none; font-size: 1.2em; border-radius: 30px; font-weight: bold; transition: 0.3s; box-shadow: 0 4px 15px rgba(255, 71, 87, 0.4); }
        .btn-booking:hover { background-color: #ff6b81; transform: translateY(-2px); }
        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .section-title { text-align: center; margin-bottom: 40px; font-size: 2.2em; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 25px; transition: 0.3s; border-top: 4px solid #203a43; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; color: #2c3e50; font-size: 1.5em; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .badge { display: inline-block; padding: 5px 10px; background: #eccc68; color: #2f3542; border-radius: 20px; font-size: 0.8em; font-weight: bold; margin-bottom: 15px; }
        .price { font-size: 1.3em; font-weight: bold; color: #2ed573; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="hero">
        <h1>AKU ADMIN PE ES Facilities</h1>
        <p>Lokasi: Pusat Kota Surabaya. Menyediakan Ruangan & Fasilitas Terbaik untuk Kebutuhan Anda.</p>
        <a href="bookingpage.php" class="btn-booking">Booking Jadwal Sekarang</a>
    </div>

    <div class="container">
        <!-- Fasilitas Ruangan -->
        <h2 class="section-title">Katalog Ruangan</h2>
        <div class="grid">
            <?php if(empty($listRuang)): ?>
                <p style="text-align:center; grid-column: 1/-1;">Belum ada data ruangan yang aktif.</p>
            <?php else: ?>
                <?php foreach($listRuang as $r): ?>
                    <div class="card">
                        <?php if(!empty($r['foto'])): ?>
    <img src="uploads/<?= htmlspecialchars($r['foto']) ?>" style="width:100%; height:150px; object-fit:cover; border-radius:6px; margin-bottom:10px;">
<?php endif; ?>
                        <span class="badge"><?= htmlspecialchars($r['nama_kategori'] ?? 'Umum') ?></span>
                        <h3><?= htmlspecialchars($r['nama']) ?></h3>
                        <p><?= htmlspecialchars($r['deskripsi']) ?></p>
                        <div class="price">Rp <?= number_format((float)$r['tarif_per_jam'], 0, ',', '.') ?> / jam</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Unit Tersedia -->
        <h2 class="section-title" style="margin-top: 70px;">Unit Tersedia</h2>
        <div class="grid">
            <?php if(empty($listUnit)): ?>
                <p style="text-align:center; grid-column: 1/-1;">Belum ada data unit yang aktif.</p>
            <?php else: ?>
                <?php foreach($listUnit as $u): ?>
                    <div class="card" style="border-top-color: #ff4757;">
                        <?php if(!empty($u['foto'])): ?>
    <img src="uploads/<?= htmlspecialchars($u['foto']) ?>" style="width:100%; height:130px; object-fit:cover; border-radius:6px; margin-bottom:10px;">
<?php endif; ?>
                        <span class="badge" style="background: #7bed9f;"><?= htmlspecialchars($u['nama_kategori'] ?? 'Umum') ?></span>
                        <h3><?= htmlspecialchars($u['nama']) ?></h3>
                        <p><?= htmlspecialchars($u['deskripsi']) ?></p>
                        <p><strong>Stock Tersedia:</strong> <?= $u['jumlah_unit'] ?> unit</p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>