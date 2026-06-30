<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$pengaduanId = (int)($_POST['pengaduan_id'] ?? 0);
$judul = trim($_POST['judul'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');
$csrfToken = $_POST['csrf_token'] ?? '';

if (!validasi_csrf($csrfToken)) {
    $_SESSION['error'] = 'Token tidak valid';
    header('Location: ../index.php?page=pengaduan&action=detail&id=' . $pengaduanId);
    exit;
}

if ($pengaduanId < 1 || $judul === '' || $catatan === '') {
    $_SESSION['error'] = 'Lengkapi semua field';
    header('Location: ../index.php?page=pengaduan&action=detail&id=' . $pengaduanId);
    exit;
}

$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
        $_SESSION['error'] = 'Tipe file foto tidak didukung';
        header('Location: ../index.php?page=pengaduan&action=detail&id=' . $pengaduanId);
        exit;
    }
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $fotoName = 'note_' . time() . '_' . uniqid() . '.' . $ext;
    $uploadPath = '../../assets/img/' . $fotoName;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        $foto = $fotoName;
    }
}

execute("INSERT INTO catatan_perbaikan (pengaduan_id, user_id, judul, catatan, foto) VALUES (?, ?, ?, ?, ?)", [
    $pengaduanId,
    $_SESSION['user_id'],
    $judul,
    $catatan,
    $foto
]);

$_SESSION['success'] = 'Catatan berhasil ditambahkan';
header('Location: ../index.php?page=pengaduan&action=detail&id=' . $pengaduanId);
