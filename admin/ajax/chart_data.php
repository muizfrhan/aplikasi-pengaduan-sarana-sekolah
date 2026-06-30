<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'guru')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
if ($tahun < 2000 || $tahun > 2100) {
    $tahun = (int)date('Y');
}

$bulanData = array_fill(0, 12, 0);
$bulanQuery = query("SELECT MONTH(created_at) as bulan, COUNT(*) as total FROM pengaduan WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at) ORDER BY bulan", [$tahun]);
while ($row = fetch($bulanQuery)) {
    $bulanData[(int)$row['bulan'] - 1] = (int)$row['total'];
}

$statusLabels = ['menunggu', 'diproses', 'selesai', 'ditolak'];
$statusData = array_fill(0, 4, 0);
$statusQuery = query("SELECT status, COUNT(*) as total FROM pengaduan GROUP BY status");
while ($row = fetch($statusQuery)) {
    $idx = array_search($row['status'], $statusLabels);
    if ($idx !== false) {
        $statusData[$idx] = (int)$row['total'];
    }
}

$totalPengaduan = hitung('pengaduan');
$pengaduanBaru = hitung('pengaduan', "status='menunggu'");
$diproses = hitung('pengaduan', "status='diproses'");
$selesai = hitung('pengaduan', "status='selesai'");
$ditolak = hitung('pengaduan', "status='ditolak'");

echo json_encode([
    'tahun' => $tahun,
    'monthly' => $bulanData,
    'status' => $statusData,
    'stats' => [
        'total' => $totalPengaduan,
        'menunggu' => $pengaduanBaru,
        'diproses' => $diproses,
        'selesai' => $selesai,
        'ditolak' => $ditolak
    ]
]);
