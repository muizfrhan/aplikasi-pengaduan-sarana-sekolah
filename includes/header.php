<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' . get_setting('nama_aplikasi') : get_setting('nama_aplikasi') ?></title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    
    <!-- Bootstrap 5 -->
    <link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="../assets/vendor/fontawesome-all.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="../assets/vendor/aos.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="../assets/vendor/sweetalert2-bootstrap-4.css" rel="stylesheet">
    <script src="../assets/vendor/sweetalert2.min.js"></script>
    
    <!-- Chart.js (CSS inline via JS) -->
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($_SESSION['role'])): ?>
    <link href="../assets/css/admin.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>

<!-- Loading Screen -->
<div id="loading-screen">
    <div class="loader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Memuat...</div>
    </div>
</div>
<script>setTimeout(function(){var e=document.getElementById('loading-screen');if(e)e.classList.add('hidden')},4000)</script>

<!-- Back to Top -->
<button id="back-to-top" class="btn btn-primary rounded-circle shadow">
    <i class="fas fa-arrow-up"></i>
</button>
