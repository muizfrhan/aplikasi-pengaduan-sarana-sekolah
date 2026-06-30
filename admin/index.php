<?php
// ============================================================
// Admin - Dashboard & Router
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_admin();

if ($_SESSION['role'] === 'guru') {
    echo '<!DOCTYPE html><html><head><link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css"><script src="../assets/vendor/sweetalert2/sweetalert2.all.min.js"></script></head><body><script>
    Swal.fire({ icon: "error", title: "Akses Ditolak", text: "Halaman ini hanya untuk Administrator.", confirmButtonColor: "#dc3545" }).then(function() { window.location.href = "../guru/index.php"; });
    </script></body></html>';
    exit;
}

$page = bersihkan($_GET['page'] ?? 'dashboard');

$pages = [
    'dashboard'         => ['file' => 'pages/dashboard.php',              'title' => 'Dashboard'],
    'pengaduan'         => ['file' => 'pages/pengaduan.php',              'title' => 'Data Pengaduan'],
    'kategori'          => ['file' => 'pages/kategori.php',               'title' => 'Kategori'],
    'ruangan'           => ['file' => 'pages/ruangan.php',                'title' => 'Ruangan'],
    'testimoni'         => ['file' => 'pages/testimoni.php',              'title' => 'Testimoni'],
    'user'              => ['file' => 'pages/user.php',                   'title' => 'Data User'],
    'laporan'           => ['file' => 'pages/laporan.php',                'title' => 'Laporan'],
    'pdf-template'      => ['file' => 'pages/pdf_template.php',           'title' => 'Kelola Template PDF'],
    'export-template'   => ['file' => 'pages/export_template.php',        'title' => 'Pengaturan Template Export'],
    'profil'            => ['file' => 'pages/profil.php',                 'title' => 'Profil'],
    'pengaturan'        => ['file' => 'pages/pengaturan.php',             'title' => 'Pengaturan'],
    'landing-hero'      => ['file' => 'pages/landing/hero.php',           'title' => 'Hero Section'],
    'landing-about'     => ['file' => 'pages/landing/about.php',          'title' => 'About Section'],
    'landing-steps'     => ['file' => 'pages/landing/steps.php',          'title' => 'Cara Pengaduan'],
    'landing-statistik' => ['file' => 'pages/landing/statistik.php',      'title' => 'Statistik'],
    'landing-faq'       => ['file' => 'pages/landing/faq.php',            'title' => 'FAQ'],
    'landing-footer'    => ['file' => 'pages/landing/footer.php',         'title' => 'Footer'],
    'landing-setting'   => ['file' => 'pages/landing/setting.php',        'title' => 'Pengaturan Landing'],
    'landing-branding'  => ['file' => 'pages/landing/branding.php',       'title' => 'Branding Website'],
    'password-reset'    => ['file' => 'pages/password_reset.php',          'title' => 'Permintaan Reset Password', 'guru_hidden' => true],
    'password-messages' => ['file' => 'pages/password_messages.php',         'title' => 'Riwayat Pesan Password', 'guru_hidden' => true],
    'pesan-kontak'      => ['file' => 'pages/pesan_kontak.php',              'title' => 'Pesan Kontak'],
];

if (!isset($pages[$page])) {
    $page = 'dashboard';
}

$title = $pages[$page]['title'];

ob_start();
include $pages[$page]['file'];
$page_content = ob_get_clean();

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>
<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_admin.php'; ?>
    <div class="container-fluid px-4 py-4">
        <?= $page_content ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
