<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
cek_admin();

$id = (int)($_GET['id'] ?? 0);
$data = fetch(query("SELECT * FROM users WHERE id = ?", [$id]));
header('Content-Type: application/json');
echo json_encode($data);
