<?php
$isGuru = $_SESSION['role'] === 'guru';

// Approve
if (isset($_POST['approve'])) {
    $id = (int)$_POST['id'];
    execute("UPDATE testimonials SET status='approved', approved_by=?, approved_at=NOW() WHERE id=? AND status='pending'", [$_SESSION['user_id'], $id]);
    $t = fetch(query("SELECT judul, user_id, nama FROM testimonials WHERE id=?", [$id]));
    if ($t) {
        catat_aktivitas($_SESSION['user_id'], "Menyetujui testimoni", $t['judul']);
        buat_notifikasi((int)$t['user_id'], "Testimoni Disetujui", "Testimoni Anda \"{$t['judul']}\" telah disetujui.", "index.php?page=testimoni", "testimoni_approved");
    }
    alert('success', 'Testimoni berhasil dipublikasikan!');
    redirect('index.php?page=testimoni');
}

// Reject
if (isset($_POST['reject'])) {
    $id = (int)$_POST['id'];
    $alasan = bersihkan($_POST['alasan_tolak'] ?? '');
    execute("UPDATE testimonials SET status='rejected', alasan_tolak=?, approved_by=?, approved_at=NOW() WHERE id=? AND status='pending'", [$alasan, $_SESSION['user_id'], $id]);
    $t = fetch(query("SELECT judul, user_id, nama FROM testimonials WHERE id=?", [$id]));
    if ($t) {
        catat_aktivitas($_SESSION['user_id'], "Menolak testimoni", $t['judul']);
        buat_notifikasi((int)$t['user_id'], "Testimoni Ditolak", "Testimoni Anda \"{$t['judul']}\" ditolak. Alasan: $alasan", "index.php?page=testimoni", "testimoni_rejected");
    }
    alert('success', 'Testimoni berhasil ditolak.');
    redirect('index.php?page=testimoni');
}

$search = bersihkan($_GET['search'] ?? '');
$statusFilter = bersihkan($_GET['status'] ?? '');

$where = '1=1';
$params = [];
if ($search !== '') {
    $where .= " AND (t.nama LIKE ? OR t.kelas LIKE ? OR t.judul LIKE ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = $s; $params[] = $s;
}
if ($statusFilter !== '') {
    $where .= " AND t.status = ?";
    $params[] = $statusFilter;
}

$items = query("SELECT t.* FROM testimonials t WHERE $where ORDER BY t.created_at DESC", $params);

$totalAll = hitung('testimonials');
$totalPending = hitung('testimonials', "status='pending'");
$totalApproved = hitung('testimonials', "status='approved'");
$totalRejected = hitung('testimonials', "status='rejected'");
?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Kelola Testimoni</h5>
        <p class="text-muted mb-0">Atur dan moderasi testimoni pengguna</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card bg-primary text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between"><div><h6 class="mb-1 opacity-75">Total</h6><h3 class="mb-0"><?= $totalAll ?></h3></div><div class="stat-icon fs-1 opacity-50"><i class="fas fa-comment-dots"></i></div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card bg-warning text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between"><div><h6 class="mb-1 opacity-75">Pending</h6><h3 class="mb-0"><?= $totalPending ?></h3></div><div class="stat-icon fs-1 opacity-50"><i class="fas fa-clock"></i></div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card bg-success text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between"><div><h6 class="mb-1 opacity-75">Disetujui</h6><h3 class="mb-0"><?= $totalApproved ?></h3></div><div class="stat-icon fs-1 opacity-50"><i class="fas fa-check-circle"></i></div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card bg-danger text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between"><div><h6 class="mb-1 opacity-75">Ditolak</h6><h3 class="mb-0"><?= $totalRejected ?></h3></div><div class="stat-icon fs-1 opacity-50"><i class="fas fa-times-circle"></i></div></div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="page" value="testimoni">
            <div class="col-12 col-sm-6 col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, kelas, judul..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-6 col-sm-4 col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Cari</button>
            </div>
            <div class="col-12 col-sm-4 col-md-2">
                <a href="index.php?page=testimoni" class="btn btn-outline-secondary w-100"><i class="fas fa-sync me-1"></i>Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:45px">Foto</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Rating</th>
                        <th>Judul</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($items) > 0): ?>
                        <?php while ($t = fetch($items)): ?>
                        <tr>
                            <td>
                                <?php if ($t['foto'] && $t['foto'] !== 'default.png'): ?>
                                <img src="../assets/img/<?= $t['foto'] ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                                <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:14px;font-weight:700"><?= strtoupper(substr($t['nama'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($t['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($t['kelas'] ?? '-') ?></td>
                            <td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= $t['rating'] ? ' text-warning' : ' text-muted' ?>" style="font-size:11px"></i>
                                <?php endfor; ?>
                            </td>
                            <td><?= htmlspecialchars($t['judul']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'approved'): ?>
                                <span class="badge bg-success">Disetujui</span>
                                <?php elseif ($t['status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Ditolak</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= tgl_indonesia($t['created_at']) ?></small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-info" onclick="detailTestimoni(<?= $t['id'] ?>)" title="Detail"><i class="fas fa-eye"></i></button>
                                    <?php if ($t['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success" onclick="approveTestimoni(<?= $t['id'] ?>)" title="Setujui"><i class="fas fa-check"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectTestimoni(<?= $t['id'] ?>)" title="Tolak"><i class="fas fa-times"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data testimoni.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-eye me-2 text-primary"></i>Detail Testimoni</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Setujui Testimoni?</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="approveId" value="0">
                    <p>Testimoni ini akan dipublikasikan di halaman utama.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                    <button type="submit" name="approve" class="btn btn-success">Ya, Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0"><i class="fas fa-times-circle me-2 text-danger"></i>Tolak Testimoni</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="rejectId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan</label>
                        <textarea name="alasan_tolak" class="form-control" rows="4" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reject" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function detailTestimoni(id) {
    fetch('../admin/pages/get_testimoni.php?id=' + id)
        .then(r => r.json())
        .then(d => {
            let stars = '';
            for (let i = 1; i <= 5; i++) stars += '<i class="fas fa-star' + (i <= d.rating ? ' text-warning' : ' text-muted') + '" style="font-size:14px"></i> ';
            let badge = d.status === 'approved' ? 'bg-success' : d.status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark';
            let label = d.status === 'approved' ? 'Disetujui' : d.status === 'rejected' ? 'Ditolak' : 'Pending';
            let fotoHtml = d.foto ?
                '<img src="../assets/img/' + d.foto + '" class="rounded-circle" width="64" height="64" style="object-fit:cover">' :
                '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px;font-weight:700">' + d.nama.charAt(0).toUpperCase() + '</div>';
            let fotoTestimoniHtml = '';
            if (d.foto_testimoni) fotoTestimoniHtml = '<div class="mb-3"><label class="text-muted small">Foto Testimoni</label><br><img src="../assets/img/testimoni/foto/' + d.foto_testimoni + '" class="img-fluid rounded" style="max-height:200px;object-fit:cover"></div>';
            let alasanHtml = '';
            if (d.alasan_tolak) alasanHtml = '<div class="mb-3"><label class="text-muted small">Alasan Ditolak</label><div class="p-2 bg-danger-subtle rounded">' + escHtml(d.alasan_tolak) + '</div></div>';

            document.getElementById('detailBody').innerHTML =
                '<div class="d-flex align-items-center gap-3 mb-4">' + fotoHtml +
                '<div><h6 class="fw-bold mb-0">' + escHtml(d.nama) + '</h6><small class="text-muted">' + escHtml(d.kelas || '-') + '</small></div>' +
                '<span class="ms-auto badge ' + badge + ' fs-6">' + label + '</span></div>' +
                '<div class="mb-3"><label class="text-muted small">Judul</label><h5>' + escHtml(d.judul) + '</h5></div>' +
                '<div class="mb-3"><label class="text-muted small">Rating</label><div>' + stars + '</div></div>' +
                '<div class="mb-3"><label class="text-muted small">Isi Testimoni</label><p class="mb-0">' + nl2br(escHtml(d.isi)) + '</p></div>' +
                fotoTestimoniHtml + alasanHtml +
                '<div class="text-muted small">Dikirim: ' + d.created_at + '</div>';
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        });
}

function approveTestimoni(id) { document.getElementById('approveId').value = id; new bootstrap.Modal(document.getElementById('approveModal')).show(); }
function rejectTestimoni(id) { document.getElementById('rejectId').value = id; new bootstrap.Modal(document.getElementById('rejectModal')).show(); }
function escHtml(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function nl2br(s) { return (s + '').replace(/\n/g, '<br>'); }
</script>
