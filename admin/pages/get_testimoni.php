<?php
require_once '../../config/database.php';
session_start();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $data = fetch(query("SELECT * FROM testimonials WHERE id = ?", [$id]));
    if ($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
}
