<?php
// ============================================================
// Laporan - Admin
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_admin();

$title = 'Laporan';

$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-t');
$filter_kategori = (int)($_GET['kategori'] ?? 0);
$filter_status = $_GET['status'] ?? '';
$filter_ruangan = (int)($_GET['ruangan'] ?? 0);

$where = "WHERE DATE(p.created_at) BETWEEN ? AND ?";
$params = [$tanggal_mulai, $tanggal_selesai];

if ($filter_kategori > 0) {
    $where .= " AND p.kategori_id = ?";
    $params[] = $filter_kategori;
}
if ($filter_status) {
    $where .= " AND p.status = ?";
    $params[] = $filter_status;
}
if ($filter_ruangan > 0) {
    $where .= " AND p.ruangan_id = ?";
    $params[] = $filter_ruangan;
}

$data = query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id $where ORDER BY p.created_at DESC", $params);
$kategoriList = fetchAll(query("SELECT * FROM kategori ORDER BY nama_kategori"));
$ruanganList = fetchAll(query("SELECT * FROM ruangan ORDER BY nama_ruangan"));

// Stats - hitung manual
$total = 0; $menunggu = 0; $diproses = 0; $selesai = 0; $ditolak = 0;
$statsResult = query("SELECT status, COUNT(*) as jml FROM pengaduan p $where GROUP BY status", $params);
while ($row = fetch($statsResult)) {
    $total += $row['jml'];
    switch ($row['status']) {
        case 'menunggu': $menunggu = $row['jml']; break;
        case 'diproses': $diproses = $row['jml']; break;
        case 'selesai': $selesai = $row['jml']; break;
        case 'ditolak': $ditolak = $row['jml']; break;
    }
}
// Re-query data for table
$data = query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id $where ORDER BY p.created_at DESC", $params);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Laporan Pengaduan</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="../laporan/cetak_excel.php?tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_selesai=<?= $tanggal_selesai ?>&kategori=<?= $filter_kategori ?>&status=<?= $filter_status ?>&ruangan=<?= $filter_ruangan ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </a>
                <a href="../laporan/cetak_pdf.php?tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_selesai=<?= $tanggal_selesai ?>&kategori=<?= $filter_kategori ?>&status=<?= $filter_status ?>&ruangan=<?= $filter_ruangan ?>" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </a>
                <a href="../laporan/cetak_print.php?tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_selesai=<?= $tanggal_selesai ?>&kategori=<?= $filter_kategori ?>&status=<?= $filter_status ?>&ruangan=<?= $filter_ruangan ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-print me-1"></i>Print
                </a>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="glass-card mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?= $tanggal_mulai ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="<?= $tanggal_selesai ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Kategori</label>
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filter_kategori === $k['id'] ? 'selected' : '' ?>><?= $k['nama_kategori'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="menunggu" <?= $filter_status === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="diproses" <?= $filter_status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="ditolak" <?= $filter_status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Ruangan</label>
                    <select name="ruangan" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <?php foreach ($ruanganList as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $filter_ruangan === $r['id'] ? 'selected' : '' ?>><?= $r['nama_ruangan'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="glass-card text-center py-3">
                    <h4 class="fw-bold text-primary mb-0"><?= $total ?></h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card text-center py-3">
                    <h4 class="fw-bold text-warning mb-0"><?= $menunggu ?></h4>
                    <small class="text-muted">Menunggu</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card text-center py-3">
                    <h4 class="fw-bold text-info mb-0"><?= $diproses ?></h4>
                    <small class="text-muted">Diproses</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card text-center py-3">
                    <h4 class="fw-bold text-success mb-0"><?= $selesai ?></h4>
                    <small class="text-muted">Selesai</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card text-center py-3">
                    <h4 class="fw-bold text-danger mb-0"><?= $ditolak ?></h4>
                    <small class="text-muted">Ditolak</small>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="printTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Ruangan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($data) > 0): 
                            $no = 1;
                            while ($row = fetch($data)):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge bg-dark"><?= $row['kode_pengaduan'] ?></span></td>
                            <td><?= $row['nama_pelapor'] ?></td>
                            <td><?= potong_teks($row['judul'], 40) ?></td>
                            <td><?= $row['nama_kategori'] ?? '-' ?></td>
                            <td><?= $row['nama_ruangan'] ?? '-' ?></td>
                            <td><small><?= tgl_indonesia($row['created_at']) ?></small></td>
                            <td><?= status_badge($row['status']) ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada data laporan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
