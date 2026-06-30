<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'guru')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'], $_POST['id'])) {
    $id = (int)$_POST['id'];
    execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = 0 OR user_id = ?)", [$id, $_SESSION['user_id']]);
    $n = fetch(query("SELECT link FROM notifications WHERE id = ?", [$id]));
    echo json_encode(['status' => 'ok', 'link' => $n ? $n['link'] : '']);
    exit;
}

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }
    execute("UPDATE notifications SET is_read = 1 WHERE (user_id = 0 OR user_id = ?) AND is_read = 0", [$_SESSION['user_id']]);
    echo json_encode(['success' => true]);
    exit;
}

// Polling: GET
$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$userId = (int)$_SESSION['user_id'];

$unread = (int)fetch(query("SELECT COUNT(*) as c FROM notifications WHERE (user_id = 0 OR user_id = ?) AND is_read = 0", [$userId]))['c'];

$sql = "SELECT id, judul, pesan, link, jenis, created_at FROM notifications WHERE (user_id = 0 OR user_id = ?) ORDER BY id DESC LIMIT 20";
$result = query($sql, [$userId]);
$latest = [];
$maxId = $lastId;

while ($row = fetch($result)) {
    $id = (int)$row['id'];
    if ($id > $maxId) $maxId = $id;
    if ($lastId > 0 && $id <= $lastId) continue;
    $latest[] = [
        'id' => $id,
        'judul' => $row['judul'],
        'pesan' => $row['pesan'],
        'link' => $row['link'],
        'jenis' => $row['jenis'],
        'created_at' => $row['created_at'],
        'waktu' => waktu_relatif($row['created_at'])
    ];
}

// On first load (last_id=0), don't return latest (already rendered by PHP)
if ($lastId === 0) {
    $latest = [];
}

if (empty($latest) && $maxId < $lastId) $maxId = $lastId;

echo json_encode([
    'unread' => $unread,
    'latest' => $latest,
    'max_id' => $maxId
]);
