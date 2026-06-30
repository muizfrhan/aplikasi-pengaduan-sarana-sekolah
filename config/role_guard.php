<?php
function role_guard(string $allowed_role): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        redirect('../login.php');
        exit;
    }
    if ($_SESSION['role'] !== $allowed_role) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Akses Ditolak</title>';
        echo '<script src="../assets/vendor/sweetalert2.min.js"></script>';
        echo '<link href="../assets/vendor/bootstrap.min.css" rel="stylesheet">';
        echo '<link href="../assets/vendor/fontawesome-all.min.css" rel="stylesheet">';
        echo '<link href="../assets/css/style.css" rel="stylesheet">';
        echo '</head><body>';
        echo '<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh">';
        echo '<div class="text-center">';
        echo '<i class="fas fa-shield-haltered text-danger" style="font-size:64px;margin-bottom:20px;"></i>';
        echo '<h2 class="fw-bold">403 - Akses Ditolak</h2>';
        echo '<p class="text-muted mb-4">Anda tidak memiliki izin untuk mengakses halaman ini.</p>';
        echo '<a href="../login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i>Kembali ke Login</a>';
        echo '</div></div></body></html>';
        exit;
    }
}
