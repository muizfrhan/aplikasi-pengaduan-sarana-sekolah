<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

cek_admin();

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

$msg = fetch(query("SELECT * FROM kontak_messages WHERE id=?", [$id]));
if (!$msg) {
    echo json_encode(['error' => 'Pesan tidak ditemukan']);
    exit;
}

// Mark as read
if ($msg['status'] === 'pending') {
    execute("UPDATE kontak_messages SET status='dibaca' WHERE id=?", [$id]);
    $msg['status'] = 'dibaca';
}

$msg['created_at'] = date('d M Y H:i', strtotime($msg['created_at']));
$msg['pesan'] = nl2br(htmlspecialchars($msg['pesan']));

echo json_encode($msg);
