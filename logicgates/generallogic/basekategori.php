<?php
class BaseKategori {
    protected DBconnection $db;
    protected string $table;

    public function __construct(DBconnection $db, string $table) {
        $this->db = $db;
        $this->table = $table;
    }

    public function getAll(): Respon {
        return $this->db->send_query("SELECT * FROM {$this->table} ORDER BY id DESC");
    }

    public function create(string $nama, string $deskripsi, int $admin_id): Respon {
        $query = "INSERT INTO {$this->table} (nama, deskripsi, created_by) VALUES ($1, $2, $3)";
        return $this->db->send_query($query, [$nama, $deskripsi, $admin_id]);
    }

    public function delete(int $id): Respon {
        return $this->db->send_query("DELETE FROM {$this->table} WHERE id = $1", [$id]);
    }
}
?>