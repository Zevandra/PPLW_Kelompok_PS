<?php
// KategoriRuang.php
class KategoriRuang extends BaseKategori {
    public function __construct(DBconnection $db) {
        parent::__construct($db, 'kategori_ruang');
    }
}
?>