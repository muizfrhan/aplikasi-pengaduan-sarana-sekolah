<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_user();

$title = 'Pesan Password';
$userId = $_SESSION['user_id'];

// AJAX handler: mark as read
if (isset($_POST['mark_read']) && isset($_POST['id'])) {
    header('Content-Type: application/json');
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid!']);
        exit;
    }
    $msgId = (int)$_POST['id'];
    $msg = fetch(query("SELECT * FROM password_messages WHERE id = ? AND user_id = ?", [$msgId, $userId]));
    if ($msg && $msg['status_baca'] === 'Belum Dibaca') {
        execute("UPDATE password_messages SET status_baca = 'Sudah Dibaca', read_at = NOW() WHERE id = ?", [$msgId]);
        echo json_encode(['status' => 'ok', 'message' => 'Pesan berhasil dibuka.']);
    } else {
        echo json_encode(['status' => 'ok', 'message' => 'Pesan sudah dibaca sebelumnya.']);
    }
    exit;
}

$limit = 10;
$hal = (int)($_GET['hal'] ?? 1);
if ($hal < 1) $hal = 1;
$offset = ($hal - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE pm.user_id = ?";
$params = [$userId];
if ($search) {
    $where .= " AND (pm.judul LIKE ? OR pm.isi_pesan LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM password_messages pm $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT pm.*, a.nama_lengkap as admin_nama FROM password_messages pm LEFT JOIN users a ON pm.admin_id = a.id $where ORDER BY pm.created_at DESC LIMIT $limit OFFSET $offset", $params);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Pesan Password</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pesan Password</li>
                    </ol>
                </nav>
            </div>
            <span class="badge bg-info"><?= hitung('password_messages', "user_id = $userId") ?> Pesan</span>
        </div>

        <!-- Search -->
        <div class="glass-card mb-4" data-aos="fade-up">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Cari Pesan</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari judul atau isi pesan..." value="<?= $search ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary me-1"><i class="fas fa-search me-1"></i>Cari</button>
                        <?php if ($search): ?>
                        <a href="pesan_password.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div class="glass-card" data-aos="fade-up" data-aos-delay="50">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($data) > 0): ?>
                        <?php $no = $offset + 1; while ($r = fetch($data)): ?>
                        <tr class="<?= $r['status_baca'] === 'Belum Dibaca' ? 'table-active fw-bold' : '' ?>">
                            <td><?= $no++ ?></td>
                            <td>
                                <?= $r['judul'] ?>
                                <?php if ($r['status_baca'] === 'Belum Dibaca'): ?>
                                <span class="badge bg-danger ms-1 badge-baru">Baru</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= tgl_indonesia($r['created_at'], true) ?></small></td>
                            <td class="status-cell">
                                <?php if ($r['status_baca'] === 'Sudah Dibaca'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Sudah Dibaca</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Belum Dibaca</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info text-white btn-lihat-pesan" 
                                        data-id="<?= $r['id'] ?>"
                                        data-judul="<?= htmlspecialchars($r['judul'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-isi="<?= htmlspecialchars($r['isi_pesan'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-admin="<?= htmlspecialchars($r['admin_nama'] ?: 'Admin', ENT_QUOTES, 'UTF-8') ?>"
                                        data-tanggal="<?= tgl_indonesia($r['created_at'], true) ?>"
                                        data-password="<?= htmlspecialchars($r['password_baru'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-status="<?= $r['status_baca'] ?>"
                                        title="Lihat Pesan">
                                    <i class="fas fa-envelope-open"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <p class="mb-0">Belum ada pesan password</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4" data-aos="fade-up">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $hal ? 'active' : '' ?>">
                    <a class="page-link" href="pesan_password.php?hal=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== MODAL DETAIL PESAN ==================== -->
<div class="modal fade" id="modalDetailPesan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Detail Pesan Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Judul Pesan</small>
                        <strong id="detailJudul" class="d-block"></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Dikirim Oleh</small>
                        <strong id="detailAdmin" class="d-block"></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Tanggal Kirim</small>
                        <strong id="detailTanggal" class="d-block"></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Status</small>
                        <strong id="detailStatus" class="d-block"></strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Password Baru</small>
                        <div class="input-group">
                            <input type="password" class="form-control" id="detailPassword" readonly>
                            <button type="button" class="btn btn-outline-secondary" id="toggleDetailPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-warning mt-1 d-block"><i class="fas fa-exclamation-triangle me-1"></i>Segera ganti password setelah login untuk keamanan.</small>
                    </div>
                </div>
                <hr>
                <div>
                    <small class="text-muted d-block mb-1">Isi Pesan</small>
                    <p id="detailIsi" style="white-space:pre-wrap" class="mb-0"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="reset_password.php" class="btn btn-warning"><i class="fas fa-key me-1"></i>Ganti Password</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Ripple effect for action buttons */
.btn-lihat-pesan {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border-radius: 12px;
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.btn-lihat-pesan:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}
.btn-lihat-pesan:active {
    transform: translateY(0);
}
.btn-lihat-pesan:disabled {
    opacity: 0.6;
    transform: none;
    box-shadow: none;
}
/* Ripple */
.btn-lihat-pesan .ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    transform: scale(0);
    animation: rippleAnim 0.6s ease-out;
    pointer-events: none;
}
@keyframes rippleAnim {
    to { transform: scale(4); opacity: 0; }
}
/* Loading spinner */
.btn-lihat-pesan .spinner-border-sm {
    width: 14px;
    height: 14px;
    border-width: 2px;
}
/* Responsive: icon-only button */
@media (max-width: 576px) {
    .btn-lihat-pesan {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    .table td, .table th {
        padding: 0.5rem 0.4rem;
        font-size: 13px;
    }
    .page-header h5 {
        font-size: 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = '<?= generate_csrf() ?>';
    var modalEl = document.getElementById('modalDetailPesan');
    var modalInstance = null;

    // ========== LIhat Pesan ==========
    document.querySelectorAll('.btn-lihat-pesan').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var self = this;
            var id = self.dataset.id;
            var judul = self.dataset.judul;
            var isi = self.dataset.isi;
            var admin = self.dataset.admin;
            var tanggal = self.dataset.tanggal;
            var password = self.dataset.password;
            var status = self.dataset.status;

            // Disable + loading
            self.disabled = true;
            var origHtml = self.innerHTML;
            self.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            // -- Ripple effect --
            var rect = self.getBoundingClientRect();
            var ripple = document.createElement('span');
            ripple.className = 'ripple';
            var size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            self.appendChild(ripple);
            setTimeout(function() { ripple.remove(); }, 600);

            // Fill modal
            document.getElementById('detailJudul').textContent = judul;
            document.getElementById('detailAdmin').textContent = admin;
            document.getElementById('detailTanggal').textContent = tanggal;
            document.getElementById('detailPassword').value = password;

            var statusBadge = '';
            if (status === 'Sudah Dibaca') {
                statusBadge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Sudah Dibaca</span>';
            } else {
                statusBadge = '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Belum Dibaca</span>';
            }
            document.getElementById('detailStatus').innerHTML = statusBadge;

            // Use textContent for isi to prevent XSS (data-isi is already HTML-escaped)
            document.getElementById('detailIsi').textContent = isi;

            // Show modal
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(modalEl);
            }
            modalInstance.show();

            // Re-enable button
            self.disabled = false;
            self.innerHTML = origHtml;

            // ========== Mark as read via AJAX ==========
            if (status === 'Belum Dibaca') {
                var formData = new FormData();
                formData.append('mark_read', '1');
                formData.append('id', id);
                formData.append('csrf_token', csrfToken);

                fetch('pesan_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function(res) {
                    if (res.status === 'ok') {
                        // Row styling
                        var tr = self.closest('tr');
                        tr.classList.remove('table-active', 'fw-bold');

                        // Update status cell
                        var statusCell = tr.querySelector('.status-cell');
                        if (statusCell) {
                            statusCell.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Sudah Dibaca</span>';
                        }

                        // Remove "Baru" badge
                        var baruBadge = tr.querySelector('.badge-baru');
                        if (baruBadge) baruBadge.remove();

                        // Update data-status attribute
                        self.dataset.status = 'Sudah Dibaca';

                        // Also update the navbar/sidebar counts by forcing re-check
                        // (handled on next page load -- no need to live-update all badges)
                    }
                })
                .catch(function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Pesan',
                        text: 'Terjadi kesalahan saat mengambil data.',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    });

    // ========== Toggle password visibility ==========
    document.getElementById('toggleDetailPassword').addEventListener('click', function() {
        var input = document.getElementById('detailPassword');
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // ========== Cleanup modal backdrop ==========
    modalEl.addEventListener('hidden.bs.modal', function() {
        document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
