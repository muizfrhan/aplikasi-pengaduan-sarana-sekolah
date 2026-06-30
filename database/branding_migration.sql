-- ============================================================
-- Website Branding - Database Migration
-- Aplikasi Pengaduan Sarana Sekolah
-- ============================================================

CREATE TABLE IF NOT EXISTS website_branding (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    logo VARCHAR(255) DEFAULT NULL,
    favicon VARCHAR(255) DEFAULT NULL,
    nama_website VARCHAR(255) DEFAULT 'APSS',
    nama_lengkap VARCHAR(255) DEFAULT 'Aplikasi Pengaduan Sarana Sekolah',
    deskripsi TEXT DEFAULT NULL,
    versi VARCHAR(50) DEFAULT '1.0.0',
    nama_sekolah VARCHAR(255) DEFAULT NULL,
    tagline VARCHAR(255) DEFAULT NULL,
    primary_color VARCHAR(20) DEFAULT '#2563EB',
    secondary_color VARCHAR(20) DEFAULT '#0F172A',
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO website_branding (nama_website, nama_lengkap, deskripsi, versi, nama_sekolah, tagline, primary_color, secondary_color, status) VALUES
('APSS', 'Aplikasi Pengaduan Sarana Sekolah', 'Sistem digital untuk melaporkan kerusakan sarana dan prasarana sekolah secara cepat, mudah, dan transparan.', '1.0.0', 'SMK Negeri 1 Contoh', 'Cepat • Mudah • Transparan', '#2563EB', '#0F172A', 'aktif');
