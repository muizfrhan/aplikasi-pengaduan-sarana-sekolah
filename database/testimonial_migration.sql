-- ============================================================
-- Testimonials Migration (New System)
-- Aplikasi Pengaduan Sarana Sekolah
-- ============================================================

CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    kelas VARCHAR(100) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    rating TINYINT(1) NOT NULL DEFAULT 5 COMMENT 'Rating 1-5',
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    foto_testimoni VARCHAR(255) DEFAULT NULL COMMENT 'Upload foto testimoni',
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    alasan_tolak TEXT DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
