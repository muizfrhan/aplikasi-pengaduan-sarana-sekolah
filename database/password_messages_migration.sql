-- ============================================================
-- Password Messages - Database Migration
-- Aplikasi Pengaduan Sarana Sekolah
-- ============================================================

CREATE TABLE IF NOT EXISTS password_messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    reset_request_id INT(11) DEFAULT NULL,
    user_id INT(11) NOT NULL,
    admin_id INT(11) NOT NULL,
    judul VARCHAR(255) NOT NULL DEFAULT 'Password Baru Akun Anda',
    isi_pesan TEXT NOT NULL,
    password_baru VARCHAR(255) NOT NULL,
    status_baca ENUM('Belum Dibaca','Sudah Dibaca') NOT NULL DEFAULT 'Belum Dibaca',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (reset_request_id) REFERENCES password_reset_requests(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
