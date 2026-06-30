<?php
// ============================================================
// Riwayat Pengaduan - User
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_user();

$title = 'Riwayat Pengaduan';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Hapus
if (isset($_GET['delete']) && $id > 0) {
    $p = fetch(query("SELECT foto, user_id FROM pengaduan WHERE id = ? AND user_id = ?", [$id, $_SESSION['user_id']]));
    if ($p) {
        if ($p['foto'] && file_exists("../assets/upload/" . $p['foto'])) {
            unlink("../assets/upload/" . $p['foto']);
        }
        $result = execute("DELETE FROM pengaduan WHERE id = ? AND user_id = ?", [$id, $_SESSION['user_id']]);
        if ($result > 0) {
            alert('success', 'Pengaduan berhasil dihapus.');
        } else {
            alert('error', 'Pengaduan gagal dihapus.');
        }
    } else {
        alert('error', 'Pengaduan tidak ditemukan.');
    }
    redirect('riwayat.php');
}

// ============================================================
// DETAIL
// ============================================================
if ($action === 'detail' && $id > 0):
    $p = fetch(query("SELECT p.*, k.nama_kategori, k.gambar_icon, k.icon as kategori_icon, r.nama_ruangan 
                      FROM pengaduan p 
                      LEFT JOIN kategori k ON p.kategori_id = k.id 
                      LEFT JOIN ruangan r ON p.ruangan_id = r.id 
                      WHERE p.id = ? AND p.user_id = ?", [$id, $_SESSION['user_id']]));
    if (!$p):
        redirect('riwayat.php');
    endif;
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Detail Pengaduan</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="riwayat.php">Riwayat</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="riwayat.php" class="btn btn-outline-secondary">
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
                        <img src="../assets/upload/<?= $p['foto'] ?>" alt="Foto" class="img-fluid rounded" style="max-height: 400px; object-fit: cover; width: 100%;">
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama</small>
                            <strong><?= $p['nama_pelapor'] ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">NIS</small>
                            <strong><?= $p['nis'] ?? '-' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Kelas</small>
                            <strong><?= $p['kelas'] ?? '-' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Kategori / Ruangan</small>
                            <strong>
                                <?php if ($p['gambar_icon']): ?>
                                <img src="../assets/img/kategori/<?= $p['gambar_icon'] ?>" alt="" style="width:20px;height:20px;object-fit:cover;border-radius:6px;display:inline-block;vertical-align:middle" class="me-1">
                                <?php else: ?>
                                <i class="<?= $p['kategori_icon'] ?? 'fas fa-tag' ?> me-1 text-primary"></i>
                                <?php endif; ?>
                                <?= $p['nama_kategori'] ?? '-' ?> / <?= $p['nama_ruangan'] ?? '-' ?>
                            </strong>
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
                <div class="glass-card">
                    <h6 class="fw-bold mb-3"><i class="fas fa-clock me-2 text-primary"></i>Timeline Progress</h6>
                    <?= status_progress($p['status']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
// ============================================================
// LIST
// ============================================================
else:

$userId = $_SESSION['user_id'];

// Pagination
$limit = 10;
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = ?", [$userId]))['total'];
$totalPages = ceil($totalData / $limit);

$riwayat = query("SELECT p.*, k.nama_kategori, r.nama_ruangan 
                  FROM pengaduan p 
                  LEFT JOIN kategori k ON p.kategori_id = k.id 
                  LEFT JOIN ruangan r ON p.ruangan_id = r.id 
                  WHERE p.user_id = ? 
                  ORDER BY p.created_at DESC 
                  LIMIT $limit OFFSET $offset", [$userId]);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Riwayat Pengaduan</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Riwayat</li>
                    </ol>
                </nav>
            </div>
            <a href="buat_pengaduan.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Buat Pengaduan
            </a>
        </div>
        
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Ruangan</th>
                            <th>Status</th>
                            <th>Komentar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($riwayat) > 0):
                            $no = $offset + 1;
                            while ($r = fetch($riwayat)):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge bg-dark"><?= $r['kode_pengaduan'] ?></span></td>
                            <td><small><?= tgl_indonesia($r['created_at']) ?></small></td>
                            <td><?= potong_teks($r['judul'], 30) ?></td>
                            <td><?= $r['nama_kategori'] ?? '-' ?></td>
                            <td><?= $r['nama_ruangan'] ?? '-' ?></td>
                            <td><?= status_badge($r['status']) ?></td>
                            <td>
                                <?= $r['komentar_admin'] ? '<i class="fas fa-check-circle text-success" title="Ada komentar"></i>' : '<i class="fas fa-minus text-muted"></i>' ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="riwayat.php?action=detail&id=<?= $r['id'] ?>" class="btn btn-sm btn-info text-white" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($r['status'] === 'menunggu'): ?>
                                    <a href="buat_pengaduan.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="riwayat.php?delete=1&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="../laporan/cetak_pdf.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <p class="mb-2">Belum ada pengaduan</p>
                                <a href="buat_pengaduan.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Buat Pengaduan Sekarang
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">Menampilkan <?= $offset + 1 ?>-<?= min($offset + $limit, $totalData) ?> dari <?= $totalData ?> data</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a></li>
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
