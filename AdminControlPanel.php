<?php
require_once __DIR__ . '/callinglibs.php';
session_start();

// 1. Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php");
    exit();
}

$db = new DBconnection();
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$pesan_aksi = '';

/*
 * ==================================================
 * PROSES AKSI (POST) - CRUD & APPROVAL
 * ==================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Action: Approve / Reject Booking
    if ($action === 'approve_booking' || $action === 'reject_booking') {
        $booking_id = (int) $_POST['booking_id'];
        $status = ($action === 'approve_booking') ? 'approved' : 'rejected';
        
        $bookingClass = new Booking($db);
        $respon = $bookingClass->updateStatus($booking_id, $status, $admin_id);
        
        $pesan_aksi = $respon->status ? "Booking #$booking_id berhasil di-$status!" : "Gagal update booking.";
    }

    // Action: Tambah Kategori Ruang
    elseif ($action === 'add_kategori_ruang') {
        $kr = new KategoriRuang($db);
        $respon = $kr->create($_POST['nama'], $_POST['deskripsi'], $admin_id);
        $pesan_aksi = $respon->status ? "Kategori Ruang berhasil ditambahkan!" : "Gagal menambah kategori.";
    }

    // Action: Tambah Ruangan Baru
    elseif ($action === 'add_ruang') {
        $query = "INSERT INTO ruang (nama, jumlah_unit, kategori_ruang, tarif_per_jam, deskripsi, created_by) 
                  VALUES ($1, $2, $3, $4, $5, $6)";
        $params = [
            $_POST['nama'], 
            (int)$_POST['jumlah_unit'], 
            (int)$_POST['kategori_id'], 
            (float)$_POST['tarif'], 
            $_POST['deskripsi'], 
            $admin_id
        ];
        
        $respon = $db->send_query($query, $params);
        $pesan_aksi = $respon->status ? "Ruangan berhasil ditambahkan!" : "Gagal menambah ruangan.";
    }

    // Action: Tambah Kategori Unit
    elseif ($action === 'add_kategori_unit') {
        $ku = new KategoriUnit($db);
        $respon = $ku->create($_POST['nama'], $_POST['deskripsi'], $admin_id);
        $pesan_aksi = $respon->status ? "Kategori Unit berhasil ditambahkan!" : "Gagal menambah kategori unit.";
    }

    // Action: Tambah Unit Baru
    elseif ($action === 'add_unit') {
        $query = "INSERT INTO unit (nama, jumlah_unit, kategori_unit, deskripsi, created_by) 
                  VALUES ($1, $2, $3, $4, $5)";
        $params = [
            $_POST['nama'], 
            (int)$_POST['jumlah_unit'], 
            (int)$_POST['kategori_id'], 
            $_POST['deskripsi'], 
            $admin_id
        ];
        
        $respon = $db->send_query($query, $params);
        $pesan_aksi = $respon->status ? "Unit berhasil ditambahkan!" : "Gagal menambah unit.";
    }
}

/*
 * ==================================================
 * FETCH DATA UNTUK DITAMPILKAN KE TABEL
 * ==================================================
 */
// Ambil pending bookings
$bookingClass = new Booking($db);
$pendingBookings = $bookingClass->getAllPending()->data ?? [];

// Ambil Kategori Ruang
$kategoriClass = new KategoriRuang($db);
$listKategori = $kategoriClass->getAll()->data ?? [];

// Ambil List Ruangan
$ruangClass = new Ruang($db);
$listRuang = $ruangClass->getAll()->data ?? [];

// Ambil Kategori Unit
$kategoriUnitClass = new KategoriUnit($db);
$listKategoriUnit = $kategoriUnitClass->getAll()->data ?? [];

// Ambil List Unit
$unitClass = new Unit($db);
$listUnit = $unitClass->getAll()->data ?? [];

$db->close_connection();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Control Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f6f9; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; min-width: 400px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        button { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold;}
        .btn-success { background-color: #28a745; }
        .btn-danger { background-color: #dc3545; }
        .btn-primary { background-color: #007bff; width: 100%; padding: 10px;}
        .alert { padding: 15px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Admin Control Panel</h2>
        <div>
            <span style="margin-right: 15px;">Halo, <strong><?= htmlspecialchars($admin_name) ?></strong></span>
            <a href="logout.php" style="background-color: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">Logout</a>
        </div>
    </div>

    <?php if ($pesan_aksi): ?>
        <div class="alert"><?= htmlspecialchars($pesan_aksi) ?></div>
    <?php endif; ?>

    <div class="container">
        
        <!-- SECTION 1: APPROVAL BOOKING -->
        <div class="card" style="flex-basis: 100%;">
            <h3>Pending Booking Requests</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nama Pemesan</th>
                    <th>WhatsApp</th>
                    <th>Tanggal Main</th>
                    <th>Jam & Durasi</th>
                    <th>Aksi</th>
                </tr>
                <?php if (count($pendingBookings) > 0): ?>
                    <?php foreach ($pendingBookings as $b): ?>
                    <tr>
                        <td>#<?= $b['id'] ?></td>
                        <td><?= htmlspecialchars($b['nama_depan'] . ' ' . $b['nama_belakang']) ?></td>
                        <td><?= htmlspecialchars($b['no_wa']) ?></td>
                        <td><?= htmlspecialchars($b['pesan_untuk_tanggal']) ?></td>
                        <td><?= htmlspecialchars($b['jam_mulai']) ?> (<?= $b['durasi'] ?>)</td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="approve_booking">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" class="btn-success">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="reject_booking">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" class="btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center;">Tidak ada request booking pending.</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- SECTION 2: TAMBAH KATEGORI RUANG -->
        <div class="card">
            <h3>Tambah Kategori Ruang Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_kategori_ruang">
                
                <label>Nama Kategori Ruang</label>
                <input type="text" name="nama" required placeholder="Contoh: VIP Room">
                
                <label>Deskripsi</label>
                <textarea name="deskripsi" required placeholder="Kapasitas 10 orang, AC, dll..."></textarea>
                
                <button type="submit" class="btn-primary">Simpan Kategori Ruang</button>
            </form>

            <h4 style="margin-top: 30px;">List Kategori Ruang</h4>
            <table>
                <tr><th>ID</th><th>Nama Kategori</th></tr>
                <?php foreach ($listKategori as $kat): ?>
                <tr>
                    <td><?= $kat['id'] ?></td>
                    <td><?= htmlspecialchars($kat['nama']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- SECTION 3: TAMBAH RUANGAN -->
        <div class="card">
            <h3>Tambah Ruangan Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_ruang">
                
                <label>Nama Ruangan</label>
                <input type="text" name="nama" required placeholder="Contoh: Ruang A1">
                
                <label>Jumlah Unit Tersedia</label>
                <input type="number" name="jumlah_unit" required value="1">
                
                <label>Pilih Kategori Ruang</label>
                <select name="kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($listKategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Tarif per Jam (Rp)</label>
                <input type="number" name="tarif" required placeholder="50000">

                <label>Deskripsi</label>
                <textarea name="deskripsi" required placeholder="Deskripsi ruangan..."></textarea>
                
                <button type="submit" class="btn-primary">Simpan Ruangan</button>
            </form>
        </div>

        <!-- SECTION 4: TAMBAH KATEGORI UNIT -->
        <div class="card">
            <h3>Tambah Kategori Unit Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_kategori_unit">
                
                <label>Nama Kategori Unit</label>
                <input type="text" name="nama" required placeholder="Contoh: Konsol Game">
                
                <label>Deskripsi</label>
                <textarea name="deskripsi" required placeholder="PS5, Proyektor, dll..."></textarea>
                
                <button type="submit" class="btn-primary" style="background-color: #17a2b8;">Simpan Kategori Unit</button>
            </form>

            <h4 style="margin-top: 30px;">List Kategori Unit</h4>
            <table>
                <tr><th>ID</th><th>Nama Kategori</th></tr>
                <?php foreach ($listKategoriUnit as $kat): ?>
                <tr>
                    <td><?= $kat['id'] ?></td>
                    <td><?= htmlspecialchars($kat['nama']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- SECTION 5: TAMBAH UNIT -->
        <div class="card">
            <h3>Tambah Unit Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_unit">
                
                <label>Nama Unit</label>
                <input type="text" name="nama" required placeholder="Contoh: PlayStation 5">
                
                <label>Stock Unit Tersedia</label>
                <input type="number" name="jumlah_unit" required value="1">
                
                <label>Pilih Kategori Unit</label>
                <select name="kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($listKategoriUnit as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Deskripsi</label>
                <textarea name="deskripsi" required placeholder="Deskripsi unit tambahan..."></textarea>
                
                <button type="submit" class="btn-primary" style="background-color: #17a2b8;">Simpan Unit</button>
            </form>
        </div>

    </div>

</body>
</html>