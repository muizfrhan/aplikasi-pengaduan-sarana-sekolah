-- ============================================================
-- Tabel: kontak_messages
-- Menyimpan pesan dari form kontak landing page
-- ============================================================
CREATE TABLE IF NOT EXISTS `kontak_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `no_hp` VARCHAR(20) DEFAULT NULL,
    `subjek` VARCHAR(200) NOT NULL,
    `kategori` VARCHAR(100) DEFAULT NULL,
    `pesan` TEXT NOT NULL,
    `lampiran` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending','dibaca','dibalas') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings untuk kontak landing page
INSERT IGNORE INTO `setting` (`id`, `nama_aplikasi`, `singkatan`, `alamat`, `telepon`, `email`, `website`, `deskripsi`, `footer`) 
VALUES (1, 'Aplikasi Pengaduan Sarana Sekolah', 'APSS', NULL, NULL, NULL, NULL, NULL, NULL);
