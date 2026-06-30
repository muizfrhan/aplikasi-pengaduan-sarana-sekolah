<?php
// ============================================================
// Ruangan - Admin CRUD
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_admin();

$title = 'Ruangan';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if (isset($_POST['simpan'])) {
    $nama_ruangan = bersihkan($_POST['nama_ruangan']);
    $lokasi = bersihkan($_POST['lokasi']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    
    if ($id > 0) {
        execute("UPDATE ruangan SET nama_ruangan = ?, lokasi = ?, deskripsi = ? WHERE id = ?", [$nama_ruangan, $lokasi, $deskripsi, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit ruangan", $nama_ruangan);
        alert('success', 'Ruangan berhasil diperbarui!');
    } else {
        execute("INSERT INTO ruangan (nama_ruangan, lokasi, deskripsi) VALUES (?, ?, ?)", [$nama_ruangan, $lokasi, $deskripsi]);
        catat_aktivitas($_SESSION['user_id'], "Menambah ruangan", $nama_ruangan);
        alert('success', 'Ruangan berhasil ditambahkan!');
    }
    redirect('ruangan.php');
}

if (isset($_GET['delete']) && $id > 0) {
    execute("DELETE FROM ruangan WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus ruangan", "ID: $id");
    alert('success', 'Ruangan berhasil dihapus!');
    redirect('ruangan.php');
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM ruangan WHERE id = ?", [$id]));
}

$data = query("SELECT r.*, (SELECT COUNT(*) FROM pengaduan WHERE ruangan_id = r.id) as jumlah_pengaduan FROM ruangan r ORDER BY r.nama_ruangan");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Data Ruangan</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Ruangan</li>
                    </ol>
                </nav>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRuangan">
                <i class="fas fa-plus me-1"></i>Tambah Ruangan
            </button>
        </div>
        
        <div class="row g-4">
            <?php while ($row = fetch($data)): ?>
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="ruangan-icon">
                            <i class="fas fa-door-open text-primary"></i>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="ruangan.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRuangan" onclick="editRuangan(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="ruangan.php?delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <h6 class="fw-bold"><?= $row['nama_ruangan'] ?></h6>
                    <?php if ($row['lokasi']): ?>
                    <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= $row['lokasi'] ?></small>
                    <?php endif; ?>
                    <?php if ($row['deskripsi']): ?>
                    <small class="text-muted d-block mb-2"><?= potong_teks($row['deskripsi'], 50) ?></small>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <small class="text-muted">
                            <i class="fas fa-clipboard me-1"></i><?= $row['jumlah_pengaduan'] ?> Pengaduan
                        </small>
                        <small class="text-muted"><?= tgl_indonesia($row['created_at']) ?></small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRuangan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalTitle">Tambah Ruangan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="ruangan.php">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ruangan" id="editNama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" id="editLokasi" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editRuangan(id) {
    fetch('get_ruangan.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Ruangan';
            document.getElementById('editId').value = data.id;
            document.getElementById('editNama').value = data.nama_ruangan;
            document.getElementById('editLokasi').value = data.lokasi;
            document.getElementById('editDeskripsi').value = data.deskripsi;
            document.querySelector('#modalRuangan form').action = 'ruangan.php?action=edit&id=' + id;
        });
}
</script>

<?php include '../includes/footer.php'; ?>
