-- ============================================================
-- Landing Page CMS - Database Migration
-- Aplikasi Pengaduan Sarana Sekolah
-- ============================================================

-- 1. Hero Section (single row)
CREATE TABLE IF NOT EXISTS landing_hero (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL DEFAULT 'Aplikasi Pengaduan Sarana Sekolah',
    subtitle TEXT DEFAULT NULL,
    button1_teks VARCHAR(100) DEFAULT 'Buat Pengaduan',
    button1_link VARCHAR(255) DEFAULT 'login.php',
    button2_teks VARCHAR(100) DEFAULT 'Pelajari',
    button2_link VARCHAR(255) DEFAULT '#tentang',
    bg_image VARCHAR(255) DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    ilustrasi VARCHAR(255) DEFAULT NULL,
    status ENUM('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_hero (judul, subtitle, button1_teks, button1_link, button2_teks, button2_link, status) VALUES
('Solusi Cerdas untuk Pelaporan Sarana Sekolah', 'Laporkan kerusakan fasilitas sekolah dengan mudah, cepat, dan transparan. Pantau status pengaduan Anda secara real-time.', 'Buat Pengaduan', 'login.php', 'Pelajari Lebih Lanjut', '#tentang', 'tampil');

-- 2. About Section (multiple rows)
CREATE TABLE IF NOT EXISTS landing_about (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-star',
    gambar VARCHAR(255) DEFAULT NULL,
    urutan INT(11) DEFAULT 0,
    status ENUM('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_about (judul, deskripsi, icon, urutan, status) VALUES
('Mudah Digunakan', 'Antarmuka yang sederhana dan intuitif, memudahkan siswa dan guru dalam melaporkan kerusakan fasilitas sekolah.', 'fas fa-mouse-pointer', 1, 'tampil'),
('Responsive Cepat', 'Setiap pengaduan langsung diterima oleh tim admin dan diproses dengan cepat maksimal 3x24 jam.', 'fas fa-bolt', 2, 'tampil'),
('Transparan', 'Pantau status pengaduan secara real-time. Anda akan mendapat notifikasi setiap ada perubahan status.', 'fas fa-eye', 3, 'tampil'),
('Terintegrasi', 'Terintegrasi dengan berbagai kategori sarana sekolah mulai dari bangku, meja, komputer, dan fasilitas lainnya.', 'fas fa-cogs', 4, 'tampil');

-- 3. Steps / Cara Pengaduan (multiple rows)
CREATE TABLE IF NOT EXISTS landing_steps (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nomor INT(11) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-circle',
    gambar VARCHAR(255) DEFAULT NULL,
    warna_card VARCHAR(20) DEFAULT '#2563EB',
    urutan INT(11) DEFAULT 0,
    status ENUM('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_steps (nomor, judul, deskripsi, icon, warna_card, urutan, status) VALUES
(1, 'Pilih Menu Pengaduan', 'Login ke akun Anda, lalu pilih menu \"Buat Pengaduan\" pada dashboard.', 'fas fa-clipboard-list', '#2563EB', 1, 'tampil'),
(2, 'Isi Form Pengaduan', 'Lengkapi data pengaduan seperti kategori, ruangan, judul, dan deskripsi kerusakan.', 'fas fa-edit', '#38BDF8', 2, 'tampil'),
(3, 'Upload Foto', 'Ambil dan upload foto kerusakan sebagai bukti pendukung pengaduan.', 'fas fa-camera', '#10B981', 3, 'tampil'),
(4, 'Klik Kirim', 'Periksa kembali data Anda, lalu klik tombol Kirim untuk mengirim pengaduan.', 'fas fa-paper-plane', '#F59E0B', 4, 'tampil'),
(5, 'Admin Memproses', 'Admin akan memproses dan meninjau pengaduan yang Anda kirimkan.', 'fas fa-cog', '#8B5CF6', 5, 'tampil'),
(6, 'Pengaduan Selesai', 'Pengaduan selesai diproses. Anda dapat melihat hasilnya di riwayat pengaduan.', 'fas fa-check-circle', '#EF4444', 6, 'tampil');

-- 4. Statistics (multiple rows)
-- tipe: menentukan sumber data otomatis (pengaduan:all, pengaduan:selesai, ruangan:all, users:user, dll)
-- Jika tipe = 'manual', maka angka diambil dari kolom `angka`
CREATE TABLE IF NOT EXISTS landing_statistics (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    angka VARCHAR(50) NOT NULL DEFAULT '0',
    tipe VARCHAR(50) NOT NULL DEFAULT 'manual',
    icon VARCHAR(50) DEFAULT 'fas fa-chart-bar',
    warna VARCHAR(20) DEFAULT '#2563EB',
    urutan INT(11) DEFAULT 0,
    status ENUM('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_statistics (nama, angka, tipe, icon, warna, urutan, status) VALUES
('Pengaduan Masuk', '0', 'pengaduan:all', 'fas fa-clipboard-list', '#2563EB', 1, 'tampil'),
('Pengaduan Selesai', '0', 'pengaduan:selesai', 'fas fa-check-circle', '#10B981', 2, 'tampil'),
('Ruangan Tersedia', '0', 'ruangan:all', 'fas fa-door-open', '#F59E0B', 3, 'tampil'),
('Siswa Terdaftar', '0', 'users:user', 'fas fa-users', '#8B5CF6', 4, 'tampil');

-- Untuk database existing: tambahkan kolom tipe (MySQL 8.0+)
ALTER TABLE landing_statistics ADD COLUMN IF NOT EXISTS tipe VARCHAR(50) NOT NULL DEFAULT 'manual' AFTER angka;

-- 5. FAQ (multiple rows)
CREATE TABLE IF NOT EXISTS landing_faq (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    pertanyaan TEXT NOT NULL,
    jawaban TEXT NOT NULL,
    urutan INT(11) DEFAULT 0,
    status ENUM('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_faq (pertanyaan, jawaban, urutan, status) VALUES
('Apa itu Aplikasi Pengaduan Sarana Sekolah?', 'Aplikasi Pengaduan Sarana Sekolah (APSS) adalah platform digital yang memungkinkan siswa dan guru melaporkan kerusakan fasilitas sekolah secara cepat, mudah, dan transparan.', 1, 'tampil'),
('Siapa saja yang dapat menggunakan aplikasi ini?', 'Aplikasi ini dapat digunakan oleh seluruh warga sekolah termasuk siswa, guru, dan staf sekolah yang telah memiliki akun.', 2, 'tampil'),
('Bagaimana cara membuat pengaduan?', 'Login menggunakan akun yang tersedia, lalu pilih menu \"Buat Pengaduan\". Isi form dengan lengkap termasuk kategori, ruangan, deskripsi, dan foto pendukung.', 3, 'tampil'),
('Berapa lama pengaduan diproses?', 'Setiap pengaduan akan diproses maksimal 3x24 jam. Status pengaduan dapat dipantau secara real-time melalui menu \"Riwayat Pengaduan\".', 4, 'tampil'),
('Apakah data saya aman?', 'Ya, data Anda dilindungi dengan sistem keamanan terenkripsi dan hanya digunakan untuk keperluan penanganan pengaduan sekolah.', 5, 'tampil'),
('Bagaimana jika pengaduan saya ditolak?', 'Jika pengaduan ditolak, Admin akan memberikan komentar atau alasan penolakan. Anda dapat menghubungi Admin untuk informasi lebih lanjut.', 6, 'tampil');

-- 6. Footer (single row)
CREATE TABLE IF NOT EXISTS landing_footer (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_sekolah VARCHAR(255) DEFAULT 'APSS',
    alamat TEXT DEFAULT NULL,
    no_telepon VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    instagram VARCHAR(100) DEFAULT NULL,
    facebook VARCHAR(100) DEFAULT NULL,
    youtube VARCHAR(100) DEFAULT NULL,
    copyright TEXT DEFAULT 'Copyright 2026 APSS. All rights reserved.',
    logo_footer VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_footer (nama_sekolah, alamat, email, copyright) VALUES
('APSS', 'Jl. Pendidikan No. 123, Kota Pelajar', 'info@sekolah.sch.id', 'Copyright 2026 Aplikasi Pengaduan Sarana Sekolah. All rights reserved.');

-- 7. Landing Settings (single row)
CREATE TABLE IF NOT EXISTS landing_setting (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_website VARCHAR(255) DEFAULT 'APSS - Aplikasi Pengaduan Sarana Sekolah',
    logo VARCHAR(255) DEFAULT NULL,
    favicon VARCHAR(255) DEFAULT NULL,
    primary_color VARCHAR(20) DEFAULT '#2563EB',
    secondary_color VARCHAR(20) DEFAULT '#0F172A',
    bg_color VARCHAR(20) DEFAULT '#F8FAFC',
    bg_image VARCHAR(255) DEFAULT NULL,
    footer_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_setting (nama_website, primary_color, secondary_color, bg_color) VALUES
('APSS - Aplikasi Pengaduan Sarana Sekolah', '#2563EB', '#0F172A', '#F8FAFC');
