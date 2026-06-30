-- ============================================================
-- Aplikasi Pengaduan Sarana Sekolah
-- UKK SMK RPL Paket 3 Tahun 2026
-- Database: db_pengaduan_sekolah
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_pengaduan_sekolah;
USE db_pengaduan_sekolah;

-- ============================================================
-- Tabel: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    nis VARCHAR(30) DEFAULT NULL,
    kelas VARCHAR(50) DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT 'default.png',
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    is_active ENUM('Y','N') NOT NULL DEFAULT 'Y',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password: admin123 (hash bcrypt)
INSERT INTO users (username, password, nama_lengkap, email, role, is_active) VALUES
('admin', '$2y$10$xmYagvGRFc4ujf3/2BadEOknDkYdcvZ822nSGXmRY5aERsv6dZm12', 'Administrator', 'admin@sekolah.sch.id', 'admin', 'Y');

-- Password: user123 (hash bcrypt)
INSERT INTO users (username, password, nama_lengkap, email, nis, kelas, no_hp, role, is_active) VALUES
('siswa1', '$2y$10$y0nek565gX5FS2vjTGZ8NeNceBZw7wcYLZC7qgz5v7T9Z8lCLbBv2', 'Siswa Satu', 'siswa1@sekolah.sch.id', '2024001', 'XII RPL 1', '081234567890', 'user', 'Y');

-- ============================================================
-- Tabel: kategori
-- ============================================================
CREATE TABLE IF NOT EXISTS kategori (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-tag',
    gambar_icon VARCHAR(255) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO kategori (nama_kategori, icon, deskripsi) VALUES
('Bangku', 'fas fa-chair', 'Kerusakan bangku atau kursi belajar'),
('Meja', 'fas fa-desktop', 'Kerusakan meja belajar atau meja guru'),
('Papan Tulis', 'fas fa-chalkboard', 'Kerusakan papan tulis atau whiteboard'),
('Komputer', 'fas fa-desktop', 'Kerusakan komputer atau laptop sekolah'),
('Proyektor', 'fas fa-projector', 'Kerusakan proyektor atau LCD'),
('Laboratorium', 'fas fa-flask', 'Kerusakan alat atau fasilitas laboratorium'),
('Toilet', 'fas fa-toilet', 'Kerusakan fasilitas toilet'),
('Lampu', 'fas fa-lightbulb', 'Kerusakan lampu ruangan'),
('AC', 'fas fa-snowflake', 'Kerusakan AC atau pendingin ruangan'),
('Internet', 'fas fa-wifi', 'Gangguan jaringan internet atau WiFi'),
('Listrik', 'fas fa-bolt', 'Gangguan instalasi listrik'),
('Air', 'fas fa-water', 'Gangguan saluran atau kran air'),
('Lainnya', 'fas fa-ellipsis-h', 'Kerusakan sarana lainnya');

-- ============================================================
-- Tabel: ruangan
-- ============================================================
CREATE TABLE IF NOT EXISTS ruangan (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_ruangan VARCHAR(100) NOT NULL,
    lokasi VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO ruangan (nama_ruangan, lokasi) VALUES
('RPL 1', 'Gedung A Lantai 1'),
('RPL 2', 'Gedung A Lantai 1'),
('Lab Komputer', 'Gedung A Lantai 2'),
('Perpustakaan', 'Gedung B Lantai 1'),
('Aula', 'Gedung B Lantai 2'),
('Mushola', 'Gedung C'),
('Kantor Guru', 'Gedung A Lantai 1');

-- ============================================================
-- Tabel: pengaduan
-- ============================================================
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    kode_pengaduan VARCHAR(20) NOT NULL UNIQUE,
    user_id INT(11) DEFAULT NULL,
    nama_pelapor VARCHAR(100) NOT NULL,
    nis VARCHAR(30) DEFAULT NULL,
    kelas VARCHAR(50) DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    kategori_id INT(11) DEFAULT NULL,
    ruangan_id INT(11) DEFAULT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    status ENUM('menunggu','diproses','selesai','ditolak') NOT NULL DEFAULT 'menunggu',
    komentar_admin TEXT DEFAULT NULL,
    tgl_kejadian DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL,
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: aktivitas
-- ============================================================
CREATE TABLE IF NOT EXISTS aktivitas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) DEFAULT NULL,
    aksi VARCHAR(100) NOT NULL,
    keterangan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: setting
-- ============================================================
CREATE TABLE IF NOT EXISTS setting (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_aplikasi VARCHAR(100) NOT NULL DEFAULT 'Aplikasi Pengaduan Sarana Sekolah',
    singkatan VARCHAR(20) NOT NULL DEFAULT 'APSS',
    logo VARCHAR(255) DEFAULT NULL,
    favicon VARCHAR(255) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    website VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    tentang TEXT DEFAULT NULL,
    footer TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO setting (nama_aplikasi, singkatan, alamat, telepon, email, website, deskripsi, footer) VALUES
('Aplikasi Pengaduan Sarana Sekolah', 'APSS',
 'Jl. Pendidikan No. 123, Kota Pelajar',
 '021-12345678',
 'info@sekolah.sch.id',
 'https://sekolah.sch.id',
 'Aplikasi untuk melaporkan kerusakan sarana dan prasarana sekolah secara cepat, mudah, dan transparan.',
 'Copyright &copy; 2026 Aplikasi Pengaduan Sarana Sekolah. All rights reserved.');
