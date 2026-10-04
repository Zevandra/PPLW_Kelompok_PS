<?php
class Unit {
    private DBconnection $db;

    public function __construct(DBconnection $db) {
        $this->db = $db;
    }

    public function getAll(): Respon {
        $query = "
            SELECT u.*, ku.nama as nama_kategori 
            FROM unit u 
            LEFT JOIN kategori_unit ku ON u.kategori_unit = ku.id 
            WHERE u.is_active = true
        ";
        return $this->db->send_query($query);
    }
}
?>