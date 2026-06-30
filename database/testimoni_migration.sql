-- ============================================================
-- Testimoni Migration
-- Aplikasi Pengaduan Sarana Sekolah
-- ============================================================

CREATE TABLE IF NOT EXISTS testimoni (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    jabatan VARCHAR(255) DEFAULT NULL,
    isi_testimoni TEXT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    rating TINYINT(1) DEFAULT 5 COMMENT 'Rating 1-5',
    status ENUM('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;