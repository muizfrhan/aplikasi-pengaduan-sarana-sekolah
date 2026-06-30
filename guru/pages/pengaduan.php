<?php
$action = $_GET['action'] ?? 'list';

if ($action === 'detail' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pengaduan = fetch(query("SELECT p.*, u.nama_lengkap, u.foto FROM pengaduan p JOIN users u ON p.user_id = u.id WHERE p.id = ?", [$id]));
    if (!$pengaduan) {
        echo '<script>window.location.href="index.php?page=pengaduan";</script>';
        exit;
    }
    $fotoBarang = !empty($pengaduan['foto_barang']) ? explode(',', $pengaduan['foto_barang']) : [];
    $fotoBukti  = !empty($pengaduan['foto_bukti']) ? explode(',', $pengaduan['foto_bukti']) : [];
    $catatanList = query("SELECT c.*, u.nama_lengkap FROM catatan_perbaikan c LEFT JOIN users u ON c.user_id = u.id WHERE c.pengaduan_id = ? ORDER BY c.created_at ASC", [$id]);
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h2>Detail Pengaduan</h2>
        <p class="text-muted">Informasi lengkap pengaduan #<?= $id ?></p>
    </div>
    <a href="index.php?page=pengaduan" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><?= htmlspecialchars($pengaduan['judul']) ?></h5>
                <?php
                $badgeClass = ['menunggu' => 'bg-warning', 'diproses' => 'bg-info', 'selesai' => 'bg-success', 'ditolak' => 'bg-danger'];
                ?>
                <span class="badge <?= $badgeClass[$pengaduan['status']] ?? 'bg-secondary' ?> fs-6">
                    <?= ucfirst($pengaduan['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small">Lokasi</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($pengaduan['lokasi']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="text-muted small">Deskripsi</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($pengaduan['deskripsi'])) ?></p>
                </div>
                <?php if (!empty($pengaduan['keterangan'])): ?>
                <div class="mb-4">
                    <label class="text-muted small">Keterangan Tambahan</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($pengaduan['keterangan'])) ?></p>
                </div>
                <?php endif; ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Tanggal</label>
                        <p class="fw-bold mb-0"><?= date('d/m/Y H:i', strtotime($pengaduan['tanggal'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Dilaporkan Oleh</label>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($pengaduan['nama_lengkap']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-image me-2"></i>Foto Barang</h5>
            </div>
            <div class="card-body">
                <?php if (count($fotoBarang) > 0): ?>
                    <div class="row g-2">
                    <?php foreach ($fotoBarang as $fb): ?>
                        <div class="col-6">
                            <a href="../assets/img/<?= htmlspecialchars(trim($fb)) ?>" target="_blank">
                                <img src="../assets/img/<?= htmlspecialchars(trim($fb)) ?>" class="img-fluid rounded" style="height:100px;object-fit:cover;width:100%">
                            </a>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Tidak ada foto</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-image me-2"></i>Foto Bukti</h5>
            </div>
            <div class="card-body">
                <?php if (count($fotoBukti) > 0): ?>
                    <div class="row g-2">
                    <?php foreach ($fotoBukti as $fb): ?>
                        <div class="col-6">
                            <a href="../assets/img/<?= htmlspecialchars(trim($fb)) ?>" target="_blank">
                                <img src="../assets/img/<?= htmlspecialchars(trim($fb)) ?>" class="img-fluid rounded" style="height:100px;object-fit:cover;width:100%">
                            </a>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Tidak ada foto</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Catatan Perbaikan</h5>
        <?php if ($pengaduan['status'] !== 'selesai' && $pengaduan['status'] !== 'ditolak'): ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="fas fa-plus me-1"></i>Tambah Catatan
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($catatanList) > 0): ?>
            <div class="timeline">
            <?php while ($c = fetch($catatanList)): ?>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($c['judul']) ?></strong>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                        </div>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($c['catatan'])) ?></p>
                        <small class="text-muted">- <?= htmlspecialchars($c['nama_lengkap'] ?? 'Sistem') ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Belum ada catatan perbaikan.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($pengaduan['status'] !== 'selesai' && $pengaduan['status'] !== 'ditolak'): ?>
<div class="row g-3 mt-3">
    <div class="col-md-6">
        <?php if ($pengaduan['status'] === 'menunggu'): ?>
        <form method="POST" action="ajax/update_pengaduan.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="status" value="diproses">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
            <button type="submit" class="btn btn-info w-100" onclick="return confirm('Teruskan pengaduan ini?')">
                <i class="fas fa-play me-2"></i>Proses Pengaduan
            </button>
        </form>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <form method="POST" action="ajax/update_pengaduan.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="status" value="selesai">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Tandai pengaduan ini selesai?')">
                <i class="fas fa-check me-2"></i>Selesaikan Pengaduan
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="ajax/add_note.php" enctype="multipart/form-data">
                <input type="hidden" name="pengaduan_id" value="<?= $id ?>">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Catatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto (opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline { position: relative; padding-left: 30px; }
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item { position: relative; margin-bottom: 20px; }
.timeline-marker {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #0d6efd;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #0d6efd;
}
.timeline-content {
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
}
</style>

<?php
} else {
    $search = $_GET['search'] ?? '';
    $statusFilter = $_GET['status'] ?? '';

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

    $page = max(1, (int)($_GET['p'] ?? 1));
    $limit = 15;
    $offset = ($page - 1) * $limit;

    $totalQuery = query("SELECT COUNT(*) as c FROM pengaduan p JOIN users u ON p.user_id = u.id WHERE $where", $params);
    $totalData = (int)fetch($totalQuery)['c'];
    $totalPages = max(1, ceil($totalData / $limit));

    $items = query("SELECT p.*, u.nama_lengkap, u.foto FROM pengaduan p JOIN users u ON p.user_id = u.id WHERE $where ORDER BY p.id DESC LIMIT $limit OFFSET $offset", $params);
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h2>Data Pengaduan</h2>
        <p class="text-muted">Kelola pengaduan sarana sekolah</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3" id="filterForm">
            <input type="hidden" name="page" value="pengaduan">
            <div class="col-12 col-sm-6 col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari judul, lokasi, pelapor..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= $statusFilter === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="diproses" <?= $statusFilter === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= $statusFilter === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Cari</button>
            </div>
            <div class="col-12 col-sm-4 col-md-2">
                <a href="index.php?page=pengaduan" class="btn btn-outline-secondary w-100"><i class="fas fa-sync me-1"></i>Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($items) > 0): ?>
                        <?php $no = $offset + 1; while ($p = fetch($items)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <a href="index.php?page=pengaduan&action=detail&id=<?= $p['id_pengaduan'] ?>" class="fw-bold text-decoration-none">
                                    <?= htmlspecialchars($p['judul']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="../assets/img/<?= $p['foto'] ?? 'default.png' ?>" class="rounded-circle" width="28" height="28" style="object-fit:cover">
                                    <small><?= htmlspecialchars($p['nama_lengkap']) ?></small>
                                </div>
                            </td>
                            <td><small><?= htmlspecialchars($p['lokasi']) ?></small></td>
                            <td><small><?= date('d/m/Y', strtotime($p['tanggal'])) ?></small></td>
                            <td>
                                <span class="badge <?= $badgeClass[$p['status']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=pengaduan&action=detail&id=<?= $p['id_pengaduan'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data pengaduan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=pengaduan&p=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
<?php } ?>
