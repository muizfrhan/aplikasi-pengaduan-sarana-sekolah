<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';

if (!validasi_csrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

if ($id < 1 || !in_array($status, ['diproses', 'selesai'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$pengaduan = fetch(query("SELECT * FROM pengaduan WHERE id_pengaduan = ?", [$id]));
if (!$pengaduan) {
    http_response_code(404);
    echo json_encode(['error' => 'Pengaduan not found']);
    exit;
}

if ($status === 'diproses' && $pengaduan['status'] !== 'menunggu') {
    http_response_code(400);
    echo json_encode(['error' => 'Hanya pengaduan dengan status menunggu yang dapat diproses']);
    exit;
}

if ($status === 'selesai' && $pengaduan['status'] !== 'diproses' && $pengaduan['status'] !== 'menunggu') {
    http_response_code(400);
    echo json_encode(['error' => 'Pengaduan harus dalam status diproses atau menunggu untuk diselesaikan']);
    exit;
}

execute("UPDATE pengaduan SET status = ? WHERE id_pengaduan = ?", [$status, $id]);

$notifTitle = $status === 'diproses' ? 'Pengaduan Diproses' : 'Pengaduan Selesai';
$notifMsg  = $status === 'diproses' ? "Pengaduan \"{$pengaduan['judul']}\" sedang diproses." : "Pengaduan \"{$pengaduan['judul']}\" telah selesai.";
$notifJenis = $status === 'diproses' ? 'pengaduan_diproses' : 'pengaduan_selesai';

execute("INSERT INTO notifications (user_id, judul, pesan, jenis, link) VALUES (?, ?, ?, ?, ?)", [
    $pengaduan['user_id'],
    $notifTitle,
    $notifMsg,
    $notifJenis,
    "admin/index.php?page=pengaduan&action=detail&id=$id"
]);

echo json_encode(['success' => true, 'status' => $status, 'message' => "Status berhasil diperbarui"]);
