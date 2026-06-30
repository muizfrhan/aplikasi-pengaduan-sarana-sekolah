<?php
// ============================================================
// Logout
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    catat_aktivitas($_SESSION['user_id'], 'Logout', 'User logout dari sistem');
}

session_destroy();

// Hapus cookie remember me
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

redirect('login.php');
