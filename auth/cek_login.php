<?php
// ============================================================
// Cek Login & Role
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once 'functions.php';

function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('../login.php');
        exit;
    }
}

function cek_admin() {
    cek_login();
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'guru') {
        redirect('../index.php');
        exit;
    }
}

function cek_user() {
    cek_login();
    if ($_SESSION['role'] !== 'user') {
        redirect('../admin/index.php');
        exit;
    }
}
