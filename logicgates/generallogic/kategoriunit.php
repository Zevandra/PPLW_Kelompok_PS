<?php
// KategoriUnit.php
class KategoriUnit extends BaseKategori {
    public function __construct(DBconnection $db) {
        parent::__construct($db, 'kategori_unit');
    }
}
?>