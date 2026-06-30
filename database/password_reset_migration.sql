-- ============================================================
-- Password Reset Requests - Database Migration
-- Aplikasi Pengaduan Sarana Sekolah
-- ============================================================

CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nis VARCHAR(30) NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    alasan TEXT NOT NULL,
    catatan TEXT DEFAULT NULL,
    status ENUM('Menunggu','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
