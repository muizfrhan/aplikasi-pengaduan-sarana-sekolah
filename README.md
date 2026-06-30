# Aplikasi Pengaduan Sarana Sekolah (APSS)

**UKK SMK RPL Paket 3 Tahun 2026**

Aplikasi Pengaduan Sarana Sekolah adalah sistem berbasis web yang memungkinkan siswa dan guru untuk melaporkan kerusakan fasilitas sekolah secara cepat, mudah, transparant, dan terdokumentasi.

## Teknologi

- **Frontend:** HTML5, CSS3, JavaScript ES6, Bootstrap 5, Font Awesome 6, SweetAlert2, Chart.js, AOS Animation
- **Backend:** PHP 8 Native (Tanpa Framework)
- **Database:** MySQL
- **Server:** XAMPP (Apache + MariaDB)

## Fitur

### Landing Page
- Navbar dengan navigasi lengkap
- Hero section dengan ilustrasi
- Section Tentang, Keunggulan, Statistik, Cara Pengaduan, FAQ
- Footer dengan informasi kontak

### Multi Role Login
- Admin dan User dengan session login
- Remember Me, Show Password, Forgot Password (Dummy)
- Captcha sederhana
- Password hash menggunakan password_hash()

### Dashboard Admin
- Sidebar modern dengan menu lengkap
- Stats cards (Total Pengaduan, Baru, Diproses, Selesai, Ditolak, User)
- Grafik pengaduan bulanan (Chart.js)
- Pie chart status pengaduan
- Aktivitas terbaru
- Notifikasi

### CRUD Lengkap
- Data Pengaduan (Search, Filter, Pagination)
- Kategori
- Ruangan
- Data User
- Profil
- Pengaturan

### Dashboard User
- Stats pribadi
- Buat pengaduan dengan upload foto
- Riwayat pengaduan
- Detail pengaduan dengan timeline progress
- Edit profil

### Laporan
- Filter berdasarkan tanggal, kategori, status, ruangan
- Export PDF (via browser print)
- Export Excel (format XLS)
- Cetak / Print

### Keamanan
- Prepared Statement (SQL Injection Protection)
- Password Hash (password_hash / password_verify)
- Session Login
- Role Validation
- Upload Validation
- XSS Filter

### Fitur Tambahan
- Dark Mode / Light Mode
- Loading Screen
- Back To Top
- Animated Dashboard
- Glassmorphism Design
- Responsive (Desktop, Tablet, Mobile)
- 404, 403, 500 Error Pages
- Realtime Clock
- Animated Counter
- Scroll Animation (AOS)
- Ripple Effect

## Struktur Folder

```
PSK/
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   ├── js/
│   │   ├── main.js
│   │   └── admin.js
│   ├── img/
│   │   └── default.png
│   └── upload/
├── config/
│   └── database.php
├── includes/
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   ├── sidebar_admin.php
│   ├── sidebar_user.php
│   ├── navbar_admin.php
│   └── navbar_user.php
├── admin/
│   ├── index.php
│   ├── pengaduan.php
│   ├── kategori.php
│   ├── ruangan.php
│   ├── user.php
│   ├── laporan.php
│   ├── profil.php
│   ├── pengaturan.php
│   ├── get_kategori.php
│   ├── get_ruangan.php
│   └── get_user.php
├── user/
│   ├── index.php
│   ├── buat_pengaduan.php
│   ├── riwayat.php
│   └── profil.php
├── laporan/
│   ├── cetak_pdf.php
│   ├── cetak_excel.php
│   └── cetak_print.php
├── auth/
│   └── cek_login.php
├── database/
│   └── database.sql
├── index.php
├── login.php
├── logout.php
├── 404.php
├── 403.php
├── 500.php
├── .htaccess
└── README.md
```

## Cara Install

### 1. Persiapan
- Pastikan XAMPP sudah terinstall dan berjalan (Apache & MySQL)

### 2. Database
- Buka phpMyAdmin (http://localhost/phpmyadmin)
- Buat database baru: `db_pengaduan_sekolah`
- Import file `database/database.sql` atau copy-paste isinya ke SQL tab

### 3. Konfigurasi
- Buka file `config/database.php`
- Sesuaikan konfigurasi database jika diperlukan (default: root tanpa password)

### 4. Jalankan Aplikasi
- Copy folder `PSK` ke `C:\xampp\htdocs\`
- Buka browser dan akses: `http://localhost/PSK/`
- Generate default avatar dengan akses: `http://localhost/PSK/generate_default.php`
- Hapus file `generate_default.php` setelah selesai

### 5. Login

**Admin:**
- Username: `admin`
- Password: `admin123`
- Role: Admin

**User:**
- Username: `siswa1`
- Password: `user123`
- Role: User

## Akun Default

| Role | Username | Password | Nama |
|------|----------|----------|------|
| Admin | admin | admin123 | Administrator |
| User | siswa1 | user123 | Siswa Satu |

## Catatan Penting

- Aplikasi ini menggunakan PHP Native tanpa framework
- Gunakan PHP versi 8.0 atau lebih baru
- Pastikan ekstensi `mysqli` dan `gd` aktif di php.ini
- Untuk fitur PDF, gunakan browser print (Ctrl+P) lalu pilih "Save as PDF"
- File upload disimpan di `assets/upload/`
- Foto profil default disimpan di `assets/img/default.png`

## Credits

Dibuat untuk memenuhi kebutuhan UKK SMK RPL Paket 3 Tahun 2026.

---

Copyright &copy; 2026 Aplikasi Pengaduan Sarana Sekolah. All rights reserved.
