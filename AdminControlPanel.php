<?php
require_once __DIR__ . '/callinglibs.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: AdminLogin.php");
    exit();
}

$db = new DBconnection();
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// AMBIL PESAN DARI SESSION (MENCEGAH POPUP FORM RESUBMISSION)
$pesan_aksi = '';
if (isset($_SESSION['pesan_aksi'])) {
    $pesan_aksi = $_SESSION['pesan_aksi'];
    unset($_SESSION['pesan_aksi']);
}

/*
 * ==================================================
 * PROSES AKSI (POST) - CRUD & APPROVAL
 * ==================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $pesan_temp = "";

    // --- BOOKING ---
    if ($action === 'approve_booking' || $action === 'reject_booking') {
        $booking_id = (int) $_POST['booking_id'];
        $status = ($action === 'approve_booking') ? 'approved' : 'rejected';
        $bookingClass = new Booking($db);
        $respon = $bookingClass->updateStatus($booking_id, $status, $admin_id);
        $pesan_temp = $respon->status ? "Booking #$booking_id di-$status!" : "Gagal update booking.";
    }

    // --- KATEGORI RUANG ---
    elseif ($action === 'add_kategori_ruang') {
        $kr = new KategoriRuang($db);
        $respon = $kr->create($_POST['nama'], $_POST['deskripsi'], $admin_id);
        $pesan_temp = $respon->status ? "Kategori Ruang ditambahkan!" : "Gagal menambah kategori (Mungkin nama sudah ada).";
    } elseif ($action === 'edit_kategori_ruang') {
        $query = "UPDATE kategori_ruang SET nama=$1, deskripsi=$2, updated_by=$3 WHERE id=$4";
        $respon = $db->send_query($query, [$_POST['nama'], $_POST['deskripsi'], $admin_id, $edit_id]);
        $pesan_temp = $respon->status ? "Kategori Ruang diupdate!" : "Gagal update.";
    } elseif ($action === 'delete_kategori_ruang') {
        $respon = $db->send_query("DELETE FROM kategori_ruang WHERE id=$1", [(int)$_POST['delete_id']]);
        $pesan_temp = $respon->status ? "Kategori Ruang dihapus!" : "Gagal hapus (mungkin ada ruangan yg pakai kategori ini).";
    }

    // --- RUANGAN ---
    elseif ($action === 'add_ruang') {
        $query = "INSERT INTO ruang (nama, jumlah_unit, kategori_ruang, tarif_per_jam, deskripsi, created_by) VALUES ($1, $2, $3, $4, $5, $6)";
        $respon = $db->send_query($query, [$_POST['nama'], (int)$_POST['jumlah_unit'], (int)$_POST['kategori_id'], (float)$_POST['tarif'], $_POST['deskripsi'], $admin_id]);
        $pesan_temp = $respon->status ? "Ruangan ditambahkan!" : "Gagal menambah ruangan (Nama mungkin sudah ada).";
    } elseif ($action === 'edit_ruang') {
        $query = "UPDATE ruang SET nama=$1, jumlah_unit=$2, kategori_ruang=$3, tarif_per_jam=$4, deskripsi=$5, updated_by=$6 WHERE id=$7";
        $respon = $db->send_query($query, [$_POST['nama'], (int)$_POST['jumlah_unit'], (int)$_POST['kategori_id'], (float)$_POST['tarif'], $_POST['deskripsi'], $admin_id, $edit_id]);
        $pesan_temp = $respon->status ? "Ruangan diupdate!" : "Gagal update ruangan.";
    } elseif ($action === 'delete_ruang') {
        $respon = $db->send_query("DELETE FROM ruang WHERE id=$1", [(int)$_POST['delete_id']]);
        $pesan_temp = $respon->status ? "Ruangan dihapus!" : "Gagal hapus ruangan.";
    }

    // --- KATEGORI UNIT ---
    elseif ($action === 'add_kategori_unit') {
        $ku = new KategoriUnit($db);
        $respon = $ku->create($_POST['nama'], $_POST['deskripsi'], $admin_id);
        $pesan_temp = $respon->status ? "Kategori Unit ditambahkan!" : "Gagal menambah kategori unit.";
    } elseif ($action === 'edit_kategori_unit') {
        $query = "UPDATE kategori_unit SET nama=$1, deskripsi=$2, updated_by=$3 WHERE id=$4";
        $respon = $db->send_query($query, [$_POST['nama'], $_POST['deskripsi'], $admin_id, $edit_id]);
        $pesan_temp = $respon->status ? "Kategori Unit diupdate!" : "Gagal update.";
    } elseif ($action === 'delete_kategori_unit') {
        $respon = $db->send_query("DELETE FROM kategori_unit WHERE id=$1", [(int)$_POST['delete_id']]);
        $pesan_temp = $respon->status ? "Kategori Unit dihapus!" : "Gagal hapus.";
    }

    // --- UNIT ---
    elseif ($action === 'add_unit') {
        $query = "INSERT INTO unit (nama, jumlah_unit, kategori_unit, deskripsi, created_by) VALUES ($1, $2, $3, $4, $5)";
        $respon = $db->send_query($query, [$_POST['nama'], (int)$_POST['jumlah_unit'], (int)$_POST['kategori_id'], $_POST['deskripsi'], $admin_id]);
        $pesan_temp = $respon->status ? "Unit ditambahkan!" : "Gagal menambah unit (Nama mungkin sudah ada).";
    } elseif ($action === 'edit_unit') {
        $query = "UPDATE unit SET nama=$1, jumlah_unit=$2, kategori_unit=$3, deskripsi=$4, updated_by=$5 WHERE id=$6";
        $respon = $db->send_query($query, [$_POST['nama'], (int)$_POST['jumlah_unit'], (int)$_POST['kategori_id'], $_POST['deskripsi'], $admin_id, $edit_id]);
        $pesan_temp = $respon->status ? "Unit diupdate!" : "Gagal update unit.";
    } elseif ($action === 'delete_unit') {
        $respon = $db->send_query("DELETE FROM unit WHERE id=$1", [(int)$_POST['delete_id']]);
        $pesan_temp = $respon->status ? "Unit dihapus!" : "Gagal hapus unit.";
    }

    // REDIRECT PATTERN: Set session dan redirect ke diri sendiri pakai GET
    $_SESSION['pesan_aksi'] = $pesan_temp;
    header("Location: AdminControlPanel.php");
    exit();
}

/*
 * ==================================================
 * FETCH DATA
 * ==================================================
 */
$bookingClass = new Booking($db);
$pendingBookings = $bookingClass->getAllPending()->data ?? [];
$kategoriClass = new KategoriRuang($db);
$listKategori = $kategoriClass->getAll()->data ?? [];
$ruangClass = new Ruang($db);
$listRuang = $ruangClass->getAll()->data ?? [];
$kategoriUnitClass = new KategoriUnit($db);
$listKategoriUnit = $kategoriUnitClass->getAll()->data ?? [];
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
        .table-responsive { overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        button { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold;}
        .btn-success { background-color: #28a745; }
        .btn-danger { background-color: #dc3545; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-primary { background-color: #007bff; width: 100%; padding: 10px;}
        .alert { padding: 15px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-top: 15px; }
        hr { border: 0; border-top: 2px dashed #ddd; margin: 30px 0; }
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
            <div class="table-responsive">
                <table>
                    <tr><th>ID</th><th>Nama Pemesan</th><th>WhatsApp</th><th>Tanggal Main</th><th>Jam & Durasi</th><th>Bukti Bayar</th><th>Aksi</th></tr>
                    <?php if (count($pendingBookings) > 0): ?>
                        <?php foreach ($pendingBookings as $b): ?>
                        <tr>
                            <td>#<?= $b['id'] ?></td>
                            <td><?= htmlspecialchars($b['nama_depan'] . ' ' . $b['nama_belakang']) ?></td>
                            <td><?= htmlspecialchars($b['no_wa']) ?></td>
                            <td><?= htmlspecialchars($b['pesan_untuk_tanggal']) ?></td>
                            <td><?= htmlspecialchars($b['jam_mulai']) ?> (<?= $b['durasi'] ?>)</td>
                            <td>
    <?php if (!empty($b['bukti_pembayaran'])): ?>
        <a href="uploads/<?= htmlspecialchars($b['bukti_pembayaran']) ?>" target="_blank" style="color: blue; text-decoration: underline;">Lihat Bukti</a>
    <?php else: ?>
        <span style="color:red;">Belum upload</span>
    <?php endif; ?>
</td>
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
        </div>

        <!-- SECTION 2: KATEGORI RUANG -->
        <div class="card">
            <h3 id="formTitle_katRuang">Tambah Kategori Ruang</h3>
            <form method="POST">
                <input type="hidden" name="action" id="action_katRuang" value="add_kategori_ruang">
                <input type="hidden" name="edit_id" id="id_katRuang" value="">
                <label>Nama Kategori</label>
                <input type="text" name="nama" id="nama_katRuang" required>
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="desk_katRuang" required></textarea>
                <button type="submit" class="btn-primary" id="btn_katRuang">Simpan Kategori</button>
                <button type="button" class="btn-warning" style="width:100%; margin-top:5px; display:none;" id="btnCancel_katRuang" onclick="cancelEdit('katRuang')">Batal Edit</button>
            </form>
            <hr>
            <h4>List Kategori Ruang</h4>
            <div class="table-responsive">
                <table>
                    <tr><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr>
                    <?php foreach ($listKategori as $kat): ?>
                    <tr>
                        <td><?= htmlspecialchars($kat['nama']) ?></td>
                        <td><?= htmlspecialchars($kat['deskripsi']) ?></td>
                        <td>
                            <button class="btn-warning" onclick="editForm('katRuang', <?= $kat['id'] ?>, '<?= htmlspecialchars(addslashes($kat['nama'])) ?>', '<?= htmlspecialchars(addslashes($kat['deskripsi'])) ?>')">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                <input type="hidden" name="action" value="delete_kategori_ruang">
                                <input type="hidden" name="delete_id" value="<?= $kat['id'] ?>">
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- SECTION 3: RUANGAN -->
        <div class="card">
            <h3 id="formTitle_ruang">Tambah Ruangan</h3>
            <form method="POST">
                <input type="hidden" name="action" id="action_ruang" value="add_ruang">
                <input type="hidden" name="edit_id" id="id_ruang" value="">
                <label>Nama Ruangan</label>
                <input type="text" name="nama" id="nama_ruang" required>
                <label>Jumlah Unit Tersedia</label>
                <input type="number" name="jumlah_unit" id="qty_ruang" required value="1">
                <label>Kategori Ruang</label>
                <select name="kategori_id" id="kat_ruang" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach ($listKategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Tarif/Jam (Rp)</label>
                <input type="number" name="tarif" id="tarif_ruang" required>
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="desk_ruang" required></textarea>
                <button type="submit" class="btn-primary" id="btn_ruang">Simpan Ruangan</button>
                <button type="button" class="btn-warning" style="width:100%; margin-top:5px; display:none;" id="btnCancel_ruang" onclick="cancelEdit('ruang')">Batal Edit</button>
            </form>
            <hr>
            <h4>List Ruangan</h4>
            <div class="table-responsive">
                <table>
                    <tr><th>Nama</th><th>Kategori</th><th>Unit</th><th>Tarif</th><th>Aksi</th></tr>
                    <?php foreach ($listRuang as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nama']) ?></td>
                        <td><?= htmlspecialchars($r['nama_kategori'] ?? '') ?></td>
                        <td><?= $r['jumlah_unit'] ?></td>
                        <td>Rp <?= number_format((float)$r['tarif_per_jam'], 0, ',', '.') ?></td>
                        <td>
                            <button class="btn-warning" onclick="editRuang(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['nama'])) ?>', <?= $r['jumlah_unit'] ?>, <?= $r['kategori_ruang'] ?? 0 ?>, <?= $r['tarif_per_jam'] ?>, '<?= htmlspecialchars(addslashes($r['deskripsi'])) ?>')">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                <input type="hidden" name="action" value="delete_ruang">
                                <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- SECTION 4: KATEGORI UNIT -->
        <div class="card">
            <h3 id="formTitle_katUnit">Tambah Kategori Unit</h3>
            <form method="POST">
                <input type="hidden" name="action" id="action_katUnit" value="add_kategori_unit">
                <input type="hidden" name="edit_id" id="id_katUnit" value="">
                <label>Nama Kategori</label>
                <input type="text" name="nama" id="nama_katUnit" required>
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="desk_katUnit" required></textarea>
                <button type="submit" class="btn-primary" style="background-color: #17a2b8;" id="btn_katUnit">Simpan Kategori</button>
                <button type="button" class="btn-warning" style="width:100%; margin-top:5px; display:none;" id="btnCancel_katUnit" onclick="cancelEdit('katUnit')">Batal Edit</button>
            </form>
            <hr>
            <h4>List Kategori Unit</h4>
            <div class="table-responsive">
                <table>
                    <tr><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr>
                    <?php foreach ($listKategoriUnit as $kat): ?>
                    <tr>
                        <td><?= htmlspecialchars($kat['nama']) ?></td>
                        <td><?= htmlspecialchars($kat['deskripsi']) ?></td>
                        <td>
                            <button class="btn-warning" onclick="editForm('katUnit', <?= $kat['id'] ?>, '<?= htmlspecialchars(addslashes($kat['nama'])) ?>', '<?= htmlspecialchars(addslashes($kat['deskripsi'])) ?>')">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                <input type="hidden" name="action" value="delete_kategori_unit">
                                <input type="hidden" name="delete_id" value="<?= $kat['id'] ?>">
                                <button type="submit" class="btn-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- SECTION 5: UNIT -->
        <div class="card">
            <h3 id="formTitle_unit">Tambah Unit</h3>
            <form method="POST">
                <input type="hidden" name="action" id="action_unit" value="add_unit">
                <input type="hidden" name="edit_id" id="id_unit" value="">
                <label>Nama Unit</label>
                <input type="text" name="nama" id="nama_unit" required>
                <label>Stock Tersedia</label>
                <input type="number" name="jumlah_unit" id="qty_unit" required value="1">
                <label>Kategori Unit</label>
                <select name="kategori_id" id="kat_unit" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach ($listKategoriUnit as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="desk_unit" required></textarea>
                <button type="submit" class="btn-primary" style="background-color: #17a2b8;" id="btn_unit">Simpan Unit</button>
                <button type="button" class="btn-warning" style="width:100%; margin-top:5px; display:none;" id="btnCancel_unit" onclick="cancelEdit('unit')">Batal Edit</button>
            </form>
            <hr>
            <h4>List Unit</h4>
            <div class="table-responsive">
                <table>
                    <tr><th>Nama</th><th>Kategori</th><th>Stock</th><th>Aksi</th></tr>
                    <?php foreach ($listUnit as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nama']) ?></td>
                        <td><?= htmlspecialchars($u['nama_kategori'] ?? '') ?></td>
                        <td><?= $u['jumlah_unit'] ?></td>
                        <td>
                            <button class="btn-warning" onclick="editUnit(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nama'])) ?>', <?= $u['jumlah_unit'] ?>, <?= $u['kategori_unit'] ?? 0 ?>, '<?= htmlspecialchars(addslashes($u['deskripsi'])) ?>')">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                <input type="hidden" name="action" value="delete_unit">
                                <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-danger">Del</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- SCRIPT UNTUK INLINE EDITING -->
    <script>
        // Edit Kategori Ruang & Kategori Unit
        function editForm(type, id, nama, deskripsi) {
            document.getElementById('formTitle_' + type).innerText = 'Edit Kategori';
            document.getElementById('action_' + type).value = 'edit_kategori_' + (type === 'katRuang' ? 'ruang' : 'unit');
            document.getElementById('id_' + type).value = id;
            document.getElementById('nama_' + type).value = nama;
            document.getElementById('desk_' + type).value = deskripsi;
            document.getElementById('btn_' + type).innerText = 'Update Kategori';
            document.getElementById('btnCancel_' + type).style.display = 'block';
            document.getElementById('formTitle_' + type).scrollIntoView({behavior: "smooth"});
        }

        // Edit Ruang
        function editRuang(id, nama, qty, katId, tarif, deskripsi) {
            document.getElementById('formTitle_ruang').innerText = 'Edit Ruangan';
            document.getElementById('action_ruang').value = 'edit_ruang';
            document.getElementById('id_ruang').value = id;
            document.getElementById('nama_ruang').value = nama;
            document.getElementById('qty_ruang').value = qty;
            document.getElementById('kat_ruang').value = katId;
            document.getElementById('tarif_ruang').value = tarif;
            document.getElementById('desk_ruang').value = deskripsi;
            document.getElementById('btn_ruang').innerText = 'Update Ruangan';
            document.getElementById('btnCancel_ruang').style.display = 'block';
            document.getElementById('formTitle_ruang').scrollIntoView({behavior: "smooth"});
        }

        // Edit Unit
        function editUnit(id, nama, qty, katId, deskripsi) {
            document.getElementById('formTitle_unit').innerText = 'Edit Unit';
            document.getElementById('action_unit').value = 'edit_unit';
            document.getElementById('id_unit').value = id;
            document.getElementById('nama_unit').value = nama;
            document.getElementById('qty_unit').value = qty;
            document.getElementById('kat_unit').value = katId;
            document.getElementById('desk_unit').value = deskripsi;
            document.getElementById('btn_unit').innerText = 'Update Unit';
            document.getElementById('btnCancel_unit').style.display = 'block';
            document.getElementById('formTitle_unit').scrollIntoView({behavior: "smooth"});
        }

        // Cancel Edit & Reset Form
        function cancelEdit(type) {
            document.getElementById('formTitle_' + type).innerText = 'Tambah Data Baru';
            
            if(type === 'katRuang') document.getElementById('action_katRuang').value = 'add_kategori_ruang';
            if(type === 'katUnit') document.getElementById('action_katUnit').value = 'add_kategori_unit';
            if(type === 'ruang') document.getElementById('action_ruang').value = 'add_ruang';
            if(type === 'unit') document.getElementById('action_unit').value = 'add_unit';
            
            document.getElementById('id_' + type).value = '';
            document.getElementById('nama_' + type).value = '';
            
            if(document.getElementById('desk_' + type)) document.getElementById('desk_' + type).value = '';
            if(document.getElementById('qty_' + type)) document.getElementById('qty_' + type).value = '1';
            if(document.getElementById('kat_' + type)) document.getElementById('kat_' + type).value = '';
            if(document.getElementById('tarif_' + type)) document.getElementById('tarif_' + type).value = '';
            
            document.getElementById('btn_' + type).innerText = 'Simpan Baru';
            document.getElementById('btnCancel_' + type).style.display = 'none';
        }
    </script>
</body>
</html>