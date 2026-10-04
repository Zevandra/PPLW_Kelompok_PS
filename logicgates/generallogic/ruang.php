<?php
class Ruang {
    private DBconnection $db;

    public function __construct(DBconnection $db) {
        $this->db = $db;
    }

    public function getAll(): Respon {
        $query = "SELECT r.*, kr.nama as nama_kategori FROM ruang r LEFT JOIN kategori_ruang kr ON r.kategori_ruang = kr.id";
        return $this->db->send_query($query);
    }

    // Required by your bookingpage.php
    public function cariRuangTersedia(string $tanggal, string $jam_mulai, int $durasi): array {
        $query = "
            SELECT r.id, r.nama, r.deskripsi, r.tarif_per_jam, r.foto, kr.nama as nama_kategori
            FROM ruang r
            LEFT JOIN kategori_ruang kr ON r.kategori_ruang = kr.id
            WHERE r.is_active = true
            AND r.id NOT IN (
                SELECT ruang_id 
                FROM request_booking
                WHERE pesan_untuk_tanggal = $1
                AND approval_status IN ('approved', 'pending')
                AND (
                    $2::time < (jam_mulai + durasi) 
                    AND 
                    ($2::time + ($3 || ' hours')::interval) > jam_mulai
                )
            )";
        
        $respon = $this->db->send_query($query, [$tanggal, $jam_mulai, $durasi]);
        return $respon->status ? $respon->data : [];
    }
}
?>