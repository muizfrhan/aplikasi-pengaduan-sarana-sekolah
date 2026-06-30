<?php
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= " AND (p.judul LIKE ? OR p.lokasi LIKE ? OR u.nama_lengkap LIKE ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = $s; $params[] = $s;
}
if ($statusFilter !== '') {
    $where .= " AND p.status = ?";
    $params[] = $statusFilter;
}
if ($dateFrom !== '') {
    $where .= " AND p.tanggal >= ?";
    $params[] = $dateFrom . ' 00:00:00';
}
if ($dateTo !== '') {
    $where .= " AND p.tanggal <= ?";
    $params[] = $dateTo . ' 23:59:59';
}

$totalQuery = query("SELECT COUNT(*) as c FROM pengaduan p JOIN users u ON p.user_id = u.id WHERE $where", $params);
$totalData = (int)fetch($totalQuery)['c'];

$items = query("SELECT p.*, u.nama_lengkap, u.nis FROM pengaduan p JOIN users u ON p.user_id = u.id WHERE $where ORDER BY p.id DESC", $params);
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h2>Laporan Pengaduan</h2>
        <p class="text-muted">Lihat dan ekspor data pengaduan</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="../laporan/cetak_pdf.php?<?= http_build_query(['search' => $search, 'status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) ?>" target="_blank" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <a href="../laporan/cetak_excel.php?<?= http_build_query(['search' => $search, 'status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) ?>" target="_blank" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Excel
        </a>
        <a href="../laporan/cetak_print.php?<?= http_build_query(['search' => $search, 'status' => $statusFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo]) ?>" target="_blank" class="btn btn-info btn-sm">
            <i class="fas fa-print me-1"></i>Print
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="page" value="laporan">
            <div class="col-12 col-sm-6 col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= $statusFilter === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="diproses" <?= $statusFilter === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= $statusFilter === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>" title="Dari tanggal">
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>" title="Sampai tanggal">
            </div>
            <div class="col-6 col-sm-4 col-md-1">
                <button type="submit" class="btn btn-primary w-100" title="Cari"><i class="fas fa-search"></i></button>
            </div>
            <div class="col-12 col-sm-4 col-md-2">
                <a href="index.php?page=laporan" class="btn btn-outline-secondary w-100"><i class="fas fa-sync me-1"></i>Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Pelapor</th>
                        <th>NIS</th>
                        <th>Lokasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($items) > 0): ?>
                        <?php $no = 1; while ($p = fetch($items)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($p['judul']) ?></td>
                            <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($p['nis'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($p['lokasi']) ?></td>
                            <td><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                            <td>
                                <span class="badge <?= $badgeClass[$p['status']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="text-muted small">Total: <?= $totalData ?> data</div>
    </div>
</div>
