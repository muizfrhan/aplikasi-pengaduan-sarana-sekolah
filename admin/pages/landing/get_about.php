<?php
session_start();
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
cek_admin();
header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $data = fetch(query("SELECT * FROM landing_about WHERE id = ?", [$id]));
    echo json_encode($data ?: ['error' => 'Data tidak ditemukan']);
} else {
    echo json_encode(['error' => 'ID tidak valid']);
}
