<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../config/auth_guru.php';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = ['dashboard', 'pengaduan', 'laporan', 'export-template', 'testimoni', 'profil', 'pesan-kontak'];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guru Dashboard - APSS</title>
    <link rel="stylesheet" href="../assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome-all.min.css">
    <link rel="stylesheet" href="../assets/vendor/sweetalert2-bootstrap-4.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/img/apss_logo.png" type="image/png">
</head>
<body>
    <?php include '../includes/sidebar_guru.php'; ?>

    <div class="main-content" id="mainContent">
        <?php include '../includes/navbar_guru.php'; ?>

        <div class="content-wrapper">
            <div class="container-fluid px-4 py-4">
                <?php
                if ($page === 'export-template') {
                    include '../admin/pages/export_template.php';
                } elseif ($page === 'pesan-kontak') {
                    include '../admin/pages/pesan_kontak.php';
                } else {
                    $pageFile = "pages/$page.php";
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo '<div class="alert alert-danger">Halaman tidak ditemukan.</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/sweetalert2.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <?= tampilkan_alert() ?>
</body>
</html>
