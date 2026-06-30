<?php
require_once 'database.php';

$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL DEFAULT 0 COMMENT '0=all admins, >0=specific user',
    judul VARCHAR(200) NOT NULL,
    pesan TEXT,
    link VARCHAR(500) DEFAULT '',
    jenis VARCHAR(50) DEFAULT '',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

mysqli_query($koneksi, $sql);
echo "Tabel notifications berhasil dibuat/dicek.\n";
