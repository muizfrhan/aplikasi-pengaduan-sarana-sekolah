<?php
// ============================================================
// Data Pengaduan - Admin
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_admin();

$title = 'Data Pengaduan';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// ============================================================
// PROSES FORM
// ============================================================

// Update Status
if (isset($_POST['update_status'])) {
    $id_pengaduan = (int)$_POST['id'];
    $status = bersihkan($_POST['status']);
    $komentar = bersihkan($_POST['komentar_admin'] ?? '');
    
    $sql = "UPDATE pengaduan SET status = ?, komentar_admin = ? WHERE id = ?";
    execute($sql, [$status, $komentar, $id_pengaduan]);
    
    // Dapatkan data pengaduan untuk aktivitas
    $p = fetch(query("SELECT judul, user_id FROM pengaduan WHERE id = ?", [$id_pengaduan]));
    catat_aktivitas($_SESSION['user_id'], "Mengubah status pengaduan", "Status pengaduan '{$p['judul']}' menjadi $status");
    
    alert('success', 'Status pengaduan berhasil diperbarui!');
    redirect('pengaduan.php');
}

// Hapus Pengaduan
if (isset($_GET['delete']) && $_GET['delete'] == 1 && $id > 0) {
    $p = fetch(query("SELECT foto FROM pengaduan WHERE id = ?", [$id]));
    if ($p && $p['foto'] && $p['foto'] !== '' && file_exists("../assets/upload/" . $p['foto'])) {
        unlink("../assets/upload/" . $p['foto']);
    }
    execute("DELETE FROM pengaduan WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus pengaduan", "ID pengaduan: $id");
    alert('success', 'Pengaduan berhasil dihapus!');
    redirect('pengaduan.php');
}

// Search & Filter
$search = bersihkan($_GET['search'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$filter_kategori = (int)($_GET['kategori'] ?? 0);
$filter_ruangan = (int)($_GET['ruangan'] ?? 0);
$filter_tanggal_mulai = bersihkan($_GET['tanggal_mulai'] ?? '');
$filter_tanggal_selesai = bersihkan($_GET['tanggal_selesai'] ?? '');

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (p.judul LIKE ? OR p.kode_pengaduan LIKE ? OR p.nama_pelapor LIKE ? OR p.nis LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}
if ($filter_status) {
    $where .= " AND p.status = ?";
    $params[] = $filter_status;
}
if ($filter_kategori > 0) {
    $where .= " AND p.kategori_id = ?";
    $params[] = $filter_kategori;
}
if ($filter_ruangan > 0) {
    $where .= " AND p.ruangan_id = ?";
    $params[] = $filter_ruangan;
}
if ($filter_tanggal_mulai && $filter_tanggal_selesai) {
    $where .= " AND DATE(p.created_at) BETWEEN ? AND ?";
    $params[] = $filter_tanggal_mulai;
    $params[] = $filter_tanggal_selesai;
}

// Pagination
$limit = 10;
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$countSql = "SELECT COUNT(*) as total FROM pengaduan p $where";
$countResult = query($countSql, $params);
$totalData = (int)fetch($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// Ambil data
$sql = "SELECT p.*, k.nama_kategori, r.nama_ruangan 
        FROM pengaduan p 
        LEFT JOIN kategori k ON p.kategori_id = k.id 
        LEFT JOIN ruangan r ON p.ruangan_id = r.id 
        $where 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$data = query($sql, $params);

// Ambil data untuk filter
$kategoriList = fetchAll(query("SELECT * FROM kategori ORDER BY nama_kategori"));
$ruanganList = fetchAll(query("SELECT * FROM ruangan ORDER BY nama_ruangan"));

// ============================================================
// TAMPILAN DETAIL
// ============================================================
if ($action === 'detail' && $id > 0):
    $p = fetch(query("SELECT p.*, k.nama_kategori, k.gambar_icon, k.icon as kategori_icon, r.nama_ruangan, u.nama_lengkap as admin_nama 
                      FROM pengaduan p 
                      LEFT JOIN kategori k ON p.kategori_id = k.id 
                      LEFT JOIN ruangan r ON p.ruangan_id = r.id 
                      LEFT JOIN users u ON p.user_id = u.id 
                      WHERE p.id = ?", [$id]));
    if (!$p):
        redirect('pengaduan.php');
    endif;
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Detail Pengaduan</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="pengaduan.php">Pengaduan</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="pengaduan.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><?= $p['judul'] ?></h5>
                            <small class="text-muted">
                                <i class="fas fa-hashtag"></i> <?= $p['kode_pengaduan'] ?> | 
                                <i class="fas fa-calendar"></i> <?= tgl_indonesia($p['created_at'], true) ?>
                            </small>
                        </div>
                        <?= status_badge($p['status']) ?>
                    </div>
                    
                    <?php if ($p['foto']): ?>
                    <div class="mb-3">
                        <img src="../assets/upload/<?= $p['foto'] ?>" alt="Foto Pengaduan" class="img-fluid rounded" style="max-height: 400px; object-fit: cover; width: 100%;" onclick="previewImage(this.src)">
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block">Nama Pelapor</small>
                                <strong><?= $p['nama_pelapor'] ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block">NIS</small>
                                <strong><?= $p['nis'] ?? '-' ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block">Kelas</small>
                                <strong><?= $p['kelas'] ?? '-' ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block">No. HP</small>
                                <strong><?= $p['no_hp'] ?? '-' ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block">Kategori</small>
                                <strong>
                                    <?php if ($p['gambar_icon']): ?>
                                    <img src="../assets/img/kategori/<?= $p['gambar_icon'] ?>" alt="" style="width:20px;height:20px;object-fit:cover;border-radius:6px;display:inline-block;vertical-align:middle" class="me-1">
                                    <?php else: ?>
                                    <i class="<?= $p['kategori_icon'] ?? 'fas fa-tag' ?> me-1 text-primary"></i>
                                    <?php endif; ?>
                                    <?= $p['nama_kategori'] ?? '-' ?>
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block">Ruangan</small>
                                <strong><i class="fas fa-door-open me-1 text-primary"></i><?= $p['nama_ruangan'] ?? '-' ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Deskripsi</small>
                        <p class="mb-0"><?= nl2br($p['deskripsi']) ?></p>
                    </div>
                    
                    <?php if ($p['komentar_admin']): ?>
                    <div class="alert alert-info">
                        <small class="fw-bold d-block"><i class="fas fa-comment me-1"></i>Komentar Admin:</small>
                        <?= nl2br($p['komentar_admin']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Status Timeline -->
                <div class="glass-card mb-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-clock me-2 text-primary"></i>Timeline Progress</h6>
                    <?= status_progress($p['status']) ?>
                </div>
                
                <!-- Update Status -->
                <div class="glass-card">
                    <h6 class="fw-bold mb-3"><i class="fas fa-edit me-2 text-primary"></i>Update Status</h6>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="menunggu" <?= $p['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="diproses" <?= $p['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= $p['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditolak" <?= $p['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Komentar</label>
                            <textarea name="komentar_admin" class="form-control" rows="3" placeholder="Berikan komentar..."><?= $p['komentar_admin'] ?></textarea>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
// ============================================================
// TAMPILAN LIST
// ============================================================
else:
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Data Pengaduan</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaduan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="../laporan/cetak_excel.php" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </a>
                <a href="../laporan/cetak_pdf.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </a>
                <a href="../laporan/cetak_print.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-print me-1"></i>Print
                </a>
            </div>
        </div>
        
        <!-- Filter Card -->
        <div class="glass-card mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Judul, kode, nama..." value="<?= $search ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="menunggu" <?= $filter_status === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="diproses" <?= $filter_status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="ditolak" <?= $filter_status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Kategori</label>
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filter_kategori === $k['id'] ? 'selected' : '' ?>><?= $k['nama_kategori'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Ruangan</label>
                    <select name="ruangan" class="form-select form-select-sm">
                        <option value="">Semua Ruangan</option>
                        <?php foreach ($ruanganList as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $filter_ruangan === $r['id'] ? 'selected' : '' ?>><?= $r['nama_ruangan'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Table -->
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($data) > 0): 
                            $no = $offset + 1;
                            while ($row = fetch($data)):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge bg-dark"><?= $row['kode_pengaduan'] ?></span></td>
                            <td><?= $row['nama_pelapor'] ?></td>
                            <td>
                                <a href="pengaduan.php?action=detail&id=<?= $row['id'] ?>" class="text-decoration-none fw-semibold">
                                    <?= potong_teks($row['judul'], 30) ?>
                                </a>
                            </td>
                            <td><?= $row['nama_kategori'] ?? '-' ?></td>
                            <td><?= $row['nama_ruangan'] ?? '-' ?></td>
                            <td><small><?= tgl_indonesia($row['created_at']) ?></small></td>
                            <td><?= status_badge($row['status']) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="pengaduan.php?action=detail&id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="pengaduan.php?delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Tidak ada data pengaduan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">Menampilkan <?= $offset + 1 ?>-<?= min($offset + $limit, $totalData) ?> dari <?= $totalData ?> data</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&ruangan=<?= $filter_ruangan ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&ruangan=<?= $filter_ruangan ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&ruangan=<?= $filter_ruangan ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php endif; ?>
