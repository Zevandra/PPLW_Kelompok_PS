<?php
require_once __DIR__ . '/callinglibs.php';

$tanggal = $_GET['tanggal'] ?? '';
$jam_mulai = $_GET['jam_mulai'] ?? '';
$durasi = $_GET['durasi'] ?? '';

$ruangDipilih = null;
$ruangTersedia = [];
$error = '';

/*
 * ==================================================
 * 1. USER MEMILIH RUANGAN
 * ==================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pilih_ruangan') {
    $ruangDipilih = [
        'id' => (int) ($_POST['ruang_id'] ?? 0),
        'tanggal' => trim($_POST['tanggal'] ?? ''),
        'jam_mulai' => trim($_POST['jam_mulai'] ?? ''),
        'durasi' => (int) ($_POST['durasi'] ?? 0)
    ];
}

/*
 * ==================================================
 * 2. USER MEMBUAT REQUEST BOOKING
 * ==================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'buat_booking') {
    $nama_depan = trim($_POST['nama_depan'] ?? '');
    $nama_belakang = trim($_POST['nama_belakang'] ?? '');
    $no_wa = trim($_POST['no_wa'] ?? '');
    $ruang_id = (int) ($_POST['ruang_id'] ?? 0);
    $tanggal_booking = trim($_POST['tanggal'] ?? '');
    $jam_booking = trim($_POST['jam_mulai'] ?? '');
    $durasi_booking = (int) ($_POST['durasi'] ?? 0);

    if ($nama_depan === '') {
        $error = 'Nama depan harus diisi.';
    } elseif ($nama_belakang === '') {
        $error = 'Nama belakang harus diisi.';
    } elseif ($no_wa === '') {
        $error = 'Nomor WhatsApp harus diisi.';
    } elseif ($ruang_id <= 0) {
        $error = 'Ruangan belum dipilih.';
    } elseif ($tanggal_booking === '') {
        $error = 'Tanggal booking belum dipilih.';
    } elseif ($jam_booking === '') {
        $error = 'Jam mulai belum dipilih.';
    } elseif ($durasi_booking < 1 || $durasi_booking > 12) {
        $error = 'Durasi harus antara 1 sampai 12 jam.';
    } else {
        try {
            $db = new DBconnection();
            $booking = new Booking($db);
            
            $respon = $booking->buatRequestBooking(
                $nama_depan, $nama_belakang, $no_wa, $ruang_id, 
                $tanggal_booking, $jam_booking, $durasi_booking
            );

            if ($respon->status) {
                // INSTAN REDIRECT KE QRIS
                header("Location: PagePembayaran.php");
                exit();
            } else {
                $error = 'Request booking gagal dibuat: ' . $respon->message;
            }
            $db->close_connection();
        } catch (Throwable $e) {
            $error = 'Terjadi kesalahan saat membuat booking: ' . $e->getMessage();
        }
    }
}

/*
 * ==================================================
 * 3. MENCARI RUANGAN TERSEDIA
 * ==================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tanggal']) && isset($_GET['jam_mulai']) && isset($_GET['durasi'])) {
    $tanggal = trim($_GET['tanggal']);
    $jam_mulai = trim($_GET['jam_mulai']);
    $durasi = (int) $_GET['durasi'];

    if ($tanggal === '') {
        $error = 'Tanggal harus dipilih.';
    } elseif ($jam_mulai === '') {
        $error = 'Jam mulai harus dipilih.';
    } elseif ($durasi < 1 || $durasi > 12) {
        $error = 'Durasi harus antara 1 sampai 12 jam.';
    } elseif ($tanggal < date('Y-m-d')) {
        $error = 'Tanggal tidak boleh sebelum hari ini.';
    } else {
        try {
            $db = new DBconnection();
            $ruang = new Ruang($db);
            $ruangTersedia = $ruang->cariRuangTersedia($tanggal, $jam_mulai, $durasi);
            $db->close_connection();
        } catch (Throwable $e) {
            $error = 'Terjadi kesalahan saat mencari ruangan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Ruangan</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f6f9; color: #333; padding: 20px; max-width: 900px; margin: 0 auto; }
        .search-container, .booking-form-container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h3, h4 { color: #2c3e50; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="date"], input[type="time"], input[type="number"], input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.2s; }
        button:hover { background: #0056b3; }
        .room-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .room-card { background: white; padding: 20px; border-radius: 10px; border-top: 4px solid #28a745; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .room-card h5 { font-size: 1.2em; margin: 0 0 10px 0; color: #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        hr { border: 0; border-top: 1px solid #eee; margin: 15px 0; }
        .nav-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-weight: bold; }
    </style>
</head>
<body>

    <a href="dashboard.php" class="nav-back">&larr; Kembali ke Dashboard</a>

    <!-- BAGIAN 1: FORM PENCARIAN RUANGAN -->
    <div class="search-container">
        <h3>Cari Ruangan Kosong</h3>
        <form action="bookingpage.php" method="GET">
            <div class="form-group">
                <label for="tanggal">Tanggal Main</label>
                <input type="date" id="tanggal" name="tanggal" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($tanggal) ?>" required>
            </div>
            <div class="form-group">
                <label for="jam_mulai">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" value="<?= htmlspecialchars($jam_mulai) ?>" required>
            </div>
            <div class="form-group">
                <label for="durasi">Durasi (Jam)</label>
                <input type="number" id="durasi" name="durasi" min="1" max="12" placeholder="Contoh: 2" value="<?= htmlspecialchars($durasi) ?>" required>
            </div>
            <button type="submit">Cari Ketersediaan</button>
        </form>
    </div>

    <!-- PESAN ERROR -->
    <?php if ($error !== ''): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- BAGIAN 2: HASIL PENCARIAN RUANGAN -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tanggal']) && isset($_GET['jam_mulai']) && isset($_GET['durasi']) && $error === ''): ?>
        <div class="room-results-container">
            <h4>Hasil Pencarian (<?= htmlspecialchars($tanggal) ?> | <?= htmlspecialchars($jam_mulai) ?> | <?= htmlspecialchars($durasi) ?> Jam)</h4>

            <?php if (count($ruangTersedia) > 0): ?>
                <div class="room-grid">
                    <?php foreach ($ruangTersedia as $ruangData): ?>
                        <?php $totalHarga = (float) $ruangData['tarif_per_jam'] * $durasi; ?>
                        <div class="room-card">
                            <h5><?= htmlspecialchars($ruangData['nama']) ?></h5>
                            <p><strong>Kategori:</strong> <?= htmlspecialchars($ruangData['nama_kategori'] ?? 'Umum') ?></p>
                            <p><?= htmlspecialchars($ruangData['deskripsi']) ?></p>
                            <p><strong>Tarif:</strong> Rp <?= number_format((float) $ruangData['tarif_per_jam'], 0, ',', '.') ?> / jam</p>
                            <hr>
                            <p><strong>Total Bayar: Rp <?= number_format($totalHarga, 0, ',', '.') ?></strong></p>

                            <form action="bookingpage.php" method="POST">
                                <input type="hidden" name="action" value="pilih_ruangan">
                                <input type="hidden" name="ruang_id" value="<?= htmlspecialchars($ruangData['id']) ?>">
                                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                                <input type="hidden" name="jam_mulai" value="<?= htmlspecialchars($jam_mulai) ?>">
                                <input type="hidden" name="durasi" value="<?= htmlspecialchars($durasi) ?>">
                                <button type="submit">Pilih Ruangan Ini</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Maaf, tidak ada ruangan yang kosong pada jadwal tersebut. Silakan pilih tanggal atau jam lain.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- BAGIAN 3: FORM DATA PEMESAN -->
    <?php if ($ruangDipilih !== null): ?>
        <div class="booking-form-container">
            <h3>Isi Data Pemesan</h3>
            <p>Ruangan ID: <?= htmlspecialchars($ruangDipilih['id']) ?> | Tanggal: <?= htmlspecialchars($ruangDipilih['tanggal']) ?> | Jam: <?= htmlspecialchars($ruangDipilih['jam_mulai']) ?> | Durasi: <?= htmlspecialchars($ruangDipilih['durasi']) ?> Jam</p>
            
            <form action="bookingpage.php" method="POST">
                <input type="hidden" name="action" value="buat_booking">
                <input type="hidden" name="ruang_id" value="<?= htmlspecialchars($ruangDipilih['id']) ?>">
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($ruangDipilih['tanggal']) ?>">
                <input type="hidden" name="jam_mulai" value="<?= htmlspecialchars($ruangDipilih['jam_mulai']) ?>">
                <input type="hidden" name="durasi" value="<?= htmlspecialchars($ruangDipilih['durasi']) ?>">

                <div class="form-group">
                    <label for="nama_depan">Nama Depan</label>
                    <input type="text" id="nama_depan" name="nama_depan" required>
                </div>
                <div class="form-group">
                    <label for="nama_belakang">Nama Belakang</label>
                    <input type="text" id="nama_belakang" name="nama_belakang" required>
                </div>
                <div class="form-group">
                    <label for="no_wa">Nomor WhatsApp Aktif</label>
                    <input type="text" id="no_wa" name="no_wa" maxlength="14" placeholder="08123456789" required>
                </div>
                <button type="submit" style="background-color: #28a745;">Lanjutkan ke Pembayaran</button>
            </form>
        </div>
    <?php endif; ?>

</body>
</html>