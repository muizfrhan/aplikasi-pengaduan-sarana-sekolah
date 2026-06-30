<?php
// Handle actions
$action = $_GET['action'] ?? '';
$msgId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'read' && $msgId) {
    execute("UPDATE kontak_messages SET status='dibaca' WHERE id=?", [$msgId]);
    alert('success', 'Pesan ditandai sebagai sudah dibaca');
    redirect('index.php?page=pesan-kontak');
}

if ($action === 'delete' && $msgId) {
    $msg = fetch(query("SELECT * FROM kontak_messages WHERE id=?", [$msgId]));
    if ($msg && $msg['lampiran']) {
        $filePath = __DIR__ . '/../../assets/uploads/kontak/' . $msg['lampiran'];
        if (file_exists($filePath)) unlink($filePath);
    }
    execute("DELETE FROM kontak_messages WHERE id=?", [$msgId]);
    alert('success', 'Pesan berhasil dihapus');
    redirect('index.php?page=pesan-kontak');
}

if ($action === 'delete_all') {
    $msgs = fetchAll(query("SELECT * FROM kontak_messages"));
    foreach ($msgs as $m) {
        if ($m['lampiran']) {
            $filePath = __DIR__ . '/../../assets/uploads/kontak/' . $m['lampiran'];
            if (file_exists($filePath)) unlink($filePath);
        }
    }
    execute("DELETE FROM kontak_messages");
    alert('success', 'Semua pesan berhasil dihapus');
    redirect('index.php?page=pesan-kontak');
}

if ($action === 'export_pdf') {
    // Use composer PDF lib if available, otherwise fallback to HTML
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../vendor/autoload.php';
    }
    // Fallback: simple HTML-based PDF
    header('Content-Type: text/html; charset=utf-8');
    $data = fetchAll(query("SELECT * FROM kontak_messages ORDER BY created_at DESC"));
    ?>
    <html><head><style>
        body { font-family: sans-serif; padding: 40px; }
        h1 { color: #6366F1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #6366F1; color: #fff; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; }
        .pending { background: #FEF3C7; color: #92400E; }
        .dibaca { background: #DBEAFE; color: #1E40AF; }
        .dibalas { background: #D1FAE5; color: #065F46; }
    </style></head>
    <body>
        <h1>Laporan Pesan Kontak</h1>
        <p>Dicetak: <?= date('d M Y H:i') ?></p>
        <table>
            <tr><th>No</th><th>Nama</th><th>Email</th><th>Subjek</th><th>Status</th><th>Tanggal</th></tr>
            <?php $no = 1; foreach ($data as $d): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= htmlspecialchars($d['subjek']) ?></td>
                <td><span class="badge <?= $d['status'] ?>"><?= $d['status'] ?></span></td>
                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </body></html>
    <?php
    exit;
}

// Filter & Search
$search = bersihkan($_GET['search'] ?? '');
$filterStatus = bersihkan($_GET['status'] ?? '');
$where = [];
$params = [];

if ($search) {
    $where[] = "(nama LIKE ? OR email LIKE ? OR subjek LIKE ? OR pesan LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($filterStatus) {
    $where[] = "status=?";
    $params[] = $filterStatus;
}

$sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";
$allMessages = fetchAll(query("SELECT * FROM kontak_messages $sqlWhere ORDER BY created_at DESC", $params));

// Stats
$total = hitung('kontak_messages');
$pending = hitung('kontak_messages', "status='pending'");
$dibaca = hitung('kontak_messages', "status='dibaca'");
$dibalas = hitung('kontak_messages', "status='dibalas'");
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Pesan Kontak</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Pesan Kontak</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="?page=pesan-kontak&action=export_pdf" class="btn btn-sm btn-outline-danger" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>Export PDF
        </a>
        <?php if ($total > 0): ?>
        <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger" onclick="hapusSemua()">
            <i class="fas fa-trash me-1"></i>Hapus Semua
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stats-card glass-card p-3 text-center">
            <h3 class="fw-bold mb-1" style="color:#6366F1"><?= $total ?></h3>
            <small class="text-muted">Total Pesan</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stats-card glass-card p-3 text-center">
            <h3 class="fw-bold mb-1" style="color:#F59E0B"><?= $pending ?></h3>
            <small class="text-muted">Pending</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stats-card glass-card p-3 text-center">
            <h3 class="fw-bold mb-1" style="color:#3B82F6"><?= $dibaca ?></h3>
            <small class="text-muted">Sudah Dibaca</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stats-card glass-card p-3 text-center">
            <h3 class="fw-bold mb-1" style="color:#10B981"><?= $dibalas ?></h3>
            <small class="text-muted">Sudah Dibalas</small>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="card glass-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="pesan-kontak">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Cari Pesan</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama, email, subjek..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="dibaca" <?= $filterStatus === 'dibaca' ? 'selected' : '' ?>>Sudah Dibaca</option>
                    <option value="dibalas" <?= $filterStatus === 'dibalas' ? 'selected' : '' ?>>Sudah Dibalas</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Cari</button>
            </div>
            <div class="col-md-2">
                <a href="?page=pesan-kontak" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-sync me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Messages List -->
<div class="card glass-card">
    <div class="card-body p-0">
        <?php if ($allMessages): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">No</th>
                        <th style="width:160px">Nama</th>
                        <th style="width:180px">Email</th>
                        <th>Subjek</th>
                        <th style="width:100px">Kategori</th>
                        <th style="width:90px">Status</th>
                        <th style="width:100px">Tanggal</th>
                        <th style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($allMessages as $msg): ?>
                    <tr class="<?= $msg['status'] === 'pending' ? 'table-warning' : '' ?>" data-id="<?= $msg['id'] ?>">
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($msg['nama']) ?></strong>
                            <?php if ($msg['no_hp']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($msg['no_hp']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td>
                            <a href="javascript:void(0)" class="text-decoration-none fw-medium" onclick="lihatPesan(<?= $msg['id'] ?>)">
                                <?= htmlspecialchars($msg['subjek']) ?>
                                <?php if ($msg['status'] === 'pending'): ?>
                                <span class="badge bg-warning ms-1" style="font-size:9px">Baru</span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td><span class="badge bg-secondary"><?= $msg['kategori'] ?: '-' ?></span></td>
                        <td>
                            <span class="badge bg-<?= $msg['status'] === 'pending' ? 'warning' : ($msg['status'] === 'dibaca' ? 'primary' : 'success') ?>">
                                <?= $msg['status'] === 'dibaca' ? 'Dibaca' : ($msg['status'] === 'dibalas' ? 'Dibalas' : 'Pending') ?>
                            </span>
                        </td>
                        <td><small class="text-muted"><?= date('d/m/Y', strtotime($msg['created_at'])) ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary" onclick="lihatPesan(<?= $msg['id'] ?>)" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($msg['status'] === 'pending'): ?>
                                <a href="?page=pesan-kontak&action=read&id=<?= $msg['id'] ?>" class="btn btn-sm btn-outline-success" title="Tandai Dibaca">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="hapusPesan(<?= $msg['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h6 class="fw-bold">Belum Ada Pesan</h6>
            <p class="text-muted mb-0">Belum ada pesan kontak yang masuk.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
            <div class="modal-header" style="border-bottom:1px solid rgba(0,0,0,0.06);padding:20px 24px">
                <h6 class="fw-bold mb-0"><i class="fas fa-envelope me-2 text-primary"></i>Detail Pesan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function lihatPesan(id) {
    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
    var body = document.getElementById('detailBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    var url = '../admin/pages/get_pesan_kontak.php?id=' + id;
    // Fallback if running from guru context
    if (typeof window.__adminBase === 'undefined') {
        // Check if this path works
    }
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                body.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            var lampiranHtml = '';
            if (data.lampiran) {
                var ext = data.lampiran.split('.').pop().toLowerCase();
                if (['jpg','jpeg','png'].indexOf(ext) !== -1) {
                    lampiranHtml = '<div class="mt-3"><label class="small fw-semibold text-muted mb-1">Lampiran</label><br><img src="../assets/uploads/kontak/' + data.lampiran + '" class="img-fluid rounded-3" style="max-height:200px"></div>';
                } else {
                    lampiranHtml = '<div class="mt-3"><a href="../assets/uploads/kontak/' + data.lampiran + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file me-1"></i>Lihat Lampiran</a></div>';
                }
            }
            body.innerHTML =
                '<div class="row g-3">' +
                    '<div class="col-md-6"><label class="small fw-semibold text-muted">Nama</label><p class="fw-bold mb-0">' + data.nama + '</p></div>' +
                    '<div class="col-md-6"><label class="small fw-semibold text-muted">Email</label><p class="mb-0"><a href="mailto:' + data.email + '">' + data.email + '</a></p></div>' +
                    (data.no_hp ? '<div class="col-md-6"><label class="small fw-semibold text-muted">No. HP</label><p class="mb-0">' + data.no_hp + '</p></div>' : '') +
                    '<div class="col-md-6"><label class="small fw-semibold text-muted">Kategori</label><p class="mb-0">' + (data.kategori || '-') + '</p></div>' +
                    '<div class="col-12"><label class="small fw-semibold text-muted">Subjek</label><p class="fw-bold mb-0">' + data.subjek + '</p></div>' +
                    '<div class="col-12"><label class="small fw-semibold text-muted">Pesan</label><div style="background:#F8FAFC;padding:16px;border-radius:12px;line-height:1.7">' + data.pesan + '</div></div>' +
                    (data.lampiran ? '<div class="col-12">' + lampiranHtml + '</div>' : '') +
                    '<div class="col-6"><label class="small fw-semibold text-muted">Status</label><p class="mb-0"><span class="badge bg-' + (data.status === 'pending' ? 'warning' : data.status === 'dibaca' ? 'primary' : 'success') + '">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span></p></div>' +
                    '<div class="col-6"><label class="small fw-semibold text-muted">Dikirim</label><p class="mb-0">' + data.created_at + '</p></div>' +
                '</div>';
        })
        .catch(function() {
            body.innerHTML = '<div class="alert alert-danger">Gagal memuat detail pesan</div>';
        });
}

function hapusPesan(id) {
    Swal.fire({
        title: 'Hapus Pesan',
        text: 'Apakah Anda yakin ingin menghapus pesan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.location.href = '?page=pesan-kontak&action=delete&id=' + id;
        }
    });
}

function hapusSemua() {
    Swal.fire({
        title: 'Hapus Semua Pesan',
        text: 'Apakah Anda yakin ingin menghapus semua pesan kontak? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.location.href = '?page=pesan-kontak&action=delete_all';
        }
    });
}
</script>
