<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!validasi_csrf($csrf)) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid']);
    exit;
}

$nama    = bersihkan($_POST['nama'] ?? '');
$email   = bersihkan($_POST['email'] ?? '');
$no_hp   = bersihkan($_POST['no_hp'] ?? '');
$subjek  = bersihkan($_POST['subjek'] ?? '');
$kategori = bersihkan($_POST['kategori'] ?? '');
$pesan   = bersihkan($_POST['pesan'] ?? '');
$privacy = isset($_POST['privacy']);

if (!$nama || !$email || !$subjek || !$pesan) {
    echo json_encode(['success' => false, 'message' => 'Harap isi semua field wajib']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
    exit;
}

$lampiran = '';
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../assets/uploads/kontak/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed) && $_FILES['lampiran']['size'] <= 2 * 1024 * 1024) {
        $lampiran = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        move_uploaded_file($_FILES['lampiran']['tmp_name'], $uploadDir . $lampiran);
    }
}

$sql = "INSERT INTO kontak_messages (nama, email, no_hp, subjek, kategori, pesan, lampiran, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
$id = insert($sql, [$nama, $email, $no_hp, $subjek, $kategori, $pesan, $lampiran]);

if ($id) {
    // Notify all admins and gurus
    $admins = query("SELECT id FROM users WHERE role IN ('admin','guru') AND is_active='Y'");
    while ($admin = fetch($admins)) {
        buat_notifikasi(
            $admin['id'],
            'Pesan Kontak Baru',
            "Pesan baru dari $nama: $subjek",
            'admin/index.php?page=pesan-kontak',
            'kontak'
        );
    }
    catat_aktivitas(0, 'Mengirim pesan kontak', "Pesan dari $nama: $subjek");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesan']);
}
