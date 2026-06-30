-- ============================================================
-- PDF Template Migration
-- Aplikasi Pengaduan Sarana Sekolah
-- UKK SMK RPL Paket 3 Tahun 2026
-- ============================================================

CREATE TABLE IF NOT EXISTS pdf_sekolah (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    logo VARCHAR(255) DEFAULT NULL,
    nama_sekolah VARCHAR(255) NOT NULL DEFAULT 'SMK ....',
    nama_aplikasi VARCHAR(255) NOT NULL DEFAULT 'Aplikasi Pengaduan Sarana Sekolah',
    judul_laporan VARCHAR(255) NOT NULL DEFAULT 'LAPORAN PENGADUAN SARANA SEKOLAH',
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    website VARCHAR(100) DEFAULT NULL,
    kepala_sekolah VARCHAR(255) DEFAULT NULL,
    nip_kepala VARCHAR(50) DEFAULT NULL,
    admin VARCHAR(255) DEFAULT NULL,
    nip_admin VARCHAR(50) DEFAULT NULL,
    kota VARCHAR(100) DEFAULT NULL,
    provinsi VARCHAR(100) DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_sekolah (id, nama_sekolah, nama_aplikasi, judul_laporan, kepala_sekolah, nip_kepala, admin, nip_admin) VALUES
(1, 'SMK ....', 'Aplikasi Pengaduan Sarana Sekolah', 'LAPORAN PENGADUAN SARANA SEKOLAH', NULL, NULL, NULL, NULL)
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_header (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    tampil_logo ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_nama_sekolah ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_alamat ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_nomor_laporan ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_admin ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_qr ENUM('Y','N') NOT NULL DEFAULT 'N',
    tampil_watermark ENUM('Y','N') NOT NULL DEFAULT 'Y',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_header (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_tabel (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    kolom_no ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_kode ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_nama ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_nis ENUM('Y','N') NOT NULL DEFAULT 'N',
    kolom_kelas ENUM('Y','N') NOT NULL DEFAULT 'N',
    kolom_no_hp ENUM('Y','N') NOT NULL DEFAULT 'N',
    kolom_judul ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_kategori ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_ruangan ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_deskripsi ENUM('Y','N') NOT NULL DEFAULT 'N',
    kolom_status ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_komentar ENUM('Y','N') NOT NULL DEFAULT 'N',
    kolom_tanggal ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_foto ENUM('Y','N') NOT NULL DEFAULT 'N',
    kolom_tgl_dibuat ENUM('Y','N') NOT NULL DEFAULT 'Y',
    kolom_updated ENUM('Y','N') NOT NULL DEFAULT 'N',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_tabel (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_warna (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    primary_color VARCHAR(20) NOT NULL DEFAULT '#2563EB',
    secondary_color VARCHAR(20) NOT NULL DEFAULT '#1D4ED8',
    header_color VARCHAR(20) NOT NULL DEFAULT '#1E293B',
    table_header VARCHAR(20) NOT NULL DEFAULT '#1E293B',
    footer_color VARCHAR(20) NOT NULL DEFAULT '#94A3B8',
    badge_success VARCHAR(20) NOT NULL DEFAULT '#22C55E',
    badge_warning VARCHAR(20) NOT NULL DEFAULT '#F59E0B',
    badge_danger VARCHAR(20) NOT NULL DEFAULT '#EF4444',
    badge_info VARCHAR(20) NOT NULL DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_warna (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_statistik (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    tampil_total ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_diproses ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_selesai ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_ditolak ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tampil_user ENUM('Y','N') NOT NULL DEFAULT 'N',
    tampil_ruangan ENUM('Y','N') NOT NULL DEFAULT 'N',
    tampil_kategori ENUM('Y','N') NOT NULL DEFAULT 'N',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_statistik (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_footer (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    copyright VARCHAR(255) DEFAULT 'Copyright &copy; %year% Aplikasi Pengaduan Sarana Sekolah',
    nama_sekolah VARCHAR(255) DEFAULT 'SMK ....',
    website VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    kalimat_footer TEXT DEFAULT NULL,
    nomor_halaman ENUM('Y','N') NOT NULL DEFAULT 'Y',
    tanggal_cetak ENUM('Y','N') NOT NULL DEFAULT 'Y',
    jam_cetak ENUM('Y','N') NOT NULL DEFAULT 'Y',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_footer (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_ttd (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    tampil_ttd ENUM('Y','N') NOT NULL DEFAULT 'Y',
    ttd1_nama VARCHAR(255) DEFAULT NULL,
    ttd1_jabatan VARCHAR(255) DEFAULT 'Kepala Sekolah',
    ttd1_nip VARCHAR(50) DEFAULT NULL,
    ttd1_file VARCHAR(255) DEFAULT NULL,
    ttd2_nama VARCHAR(255) DEFAULT NULL,
    ttd2_jabatan VARCHAR(255) DEFAULT 'Admin Sistem',
    ttd2_nip VARCHAR(50) DEFAULT NULL,
    ttd2_file VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_ttd (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_watermark (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    aktif ENUM('Y','N') NOT NULL DEFAULT 'Y',
    isi VARCHAR(100) NOT NULL DEFAULT 'APSS',
    opacity DECIMAL(3,2) NOT NULL DEFAULT 0.04,
    ukuran INT(11) NOT NULL DEFAULT 120,
    posisi VARCHAR(20) NOT NULL DEFAULT 'tengah',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_watermark (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS pdf_qr (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    aktif ENUM('Y','N') NOT NULL DEFAULT 'N',
    isi VARCHAR(255) NOT NULL DEFAULT 'url',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pdf_qr (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;
