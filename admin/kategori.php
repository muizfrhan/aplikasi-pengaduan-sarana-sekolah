<?php
// ============================================================
// Kategori - Admin CRUD
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_admin();

$title = 'Kategori';
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Proses Simpan
if (isset($_POST['simpan'])) {
    $nama_kategori = bersihkan($_POST['nama_kategori']);
    $icon = bersihkan($_POST['icon']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    $gambar_icon = $_POST['gambar_icon_lama'] ?? '';
    
    // Handle upload gambar
    if (isset($_FILES['gambar_icon']) && $_FILES['gambar_icon']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['gambar_icon'], '../assets/img/kategori/');
        if ($upload['status']) {
            if ($gambar_icon && file_exists('../assets/img/kategori/' . $gambar_icon)) {
                unlink('../assets/img/kategori/' . $gambar_icon);
            }
            $gambar_icon = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }
    
    if ($id > 0) {
        execute("UPDATE kategori SET nama_kategori = ?, icon = ?, gambar_icon = ?, deskripsi = ? WHERE id = ?", [$nama_kategori, $icon, $gambar_icon, $deskripsi, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit kategori", $nama_kategori);
        alert('success', 'Kategori berhasil diperbarui!');
    } else {
        execute("INSERT INTO kategori (nama_kategori, icon, gambar_icon, deskripsi) VALUES (?, ?, ?, ?)", [$nama_kategori, $icon, $gambar_icon, $deskripsi]);
        catat_aktivitas($_SESSION['user_id'], "Menambah kategori", $nama_kategori);
        alert('success', 'Kategori berhasil ditambahkan!');
    }
    redirect('kategori.php');
}

// Proses Hapus
if (isset($_GET['delete']) && $id > 0) {
    execute("DELETE FROM kategori WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus kategori", "ID: $id");
    alert('success', 'Kategori berhasil dihapus!');
    redirect('kategori.php');
}

// Ambil data untuk edit
$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM kategori WHERE id = ?", [$id]));
}

// Ambil semua kategori
$data = query("SELECT k.*, (SELECT COUNT(*) FROM pengaduan WHERE kategori_id = k.id) as jumlah_pengaduan FROM kategori k ORDER BY k.nama_kategori");
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_admin.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Data Kategori</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kategori</li>
                    </ol>
                </nav>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKategori">
                <i class="fas fa-plus me-1"></i>Tambah Kategori
            </button>
        </div>
        
        <div class="row g-4">
            <?php while ($row = fetch($data)): ?>
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kategori-icon">
                            <?php if ($row['gambar_icon']): ?>
                            <img src="../assets/img/kategori/<?= $row['gambar_icon'] ?>" alt="<?= $row['nama_kategori'] ?>">
                            <?php else: ?>
                            <i class="<?= $row['icon'] ?> text-primary"></i>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="kategori.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalKategori" onclick="editKategori(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="kategori.php?delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <h6 class="fw-bold"><?= $row['nama_kategori'] ?></h6>
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

<!-- Modal -->
<div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalTitle">Tambah Kategori</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="kategori.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <input type="hidden" name="gambar_icon_lama" id="editGambarLama" value="">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="editNama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome)</label>
                        <select name="icon" id="editIcon" class="form-select">
                            <option value="fas fa-tag">Tag</option>
                            <option value="fas fa-chair">Bangku</option>
                            <option value="fas fa-desktop">Desktop</option>
                            <option value="fas fa-chalkboard">Papan Tulis</option>
                            <option value="fas fa-projector">Proyektor</option>
                            <option value="fas fa-flask">Laboratorium</option>
                            <option value="fas fa-toilet">Toilet</option>
                            <option value="fas fa-lightbulb">Lampu</option>
                            <option value="fas fa-snowflake">AC</option>
                            <option value="fas fa-wifi">Internet</option>
                            <option value="fas fa-bolt">Listrik</option>
                            <option value="fas fa-water">Air</option>
                            <option value="fas fa-ellipsis-h">Lainnya</option>
                            <option value="fas fa-tools">Tools</option>
                            <option value="fas fa-cogs">Cogs</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Icon <small class="text-muted">(opsional, upload gambar kustom)</small></label>
                        <div class="kategori-preview mb-2" id="iconPreview">
                            <i class="fas fa-image"></i>
                        </div>
                        <input type="file" name="gambar_icon" id="editGambar" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
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
function editKategori(id) {
    fetch('get_kategori.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Kategori';
            document.getElementById('editId').value = data.id;
            document.getElementById('editNama').value = data.nama_kategori;
            document.getElementById('editIcon').value = data.icon;
            document.getElementById('editDeskripsi').value = data.deskripsi;
            document.getElementById('editGambarLama').value = data.gambar_icon || '';
            document.querySelector('#modalKategori form').action = 'kategori.php?action=edit&id=' + id;
            // Update preview
            const preview = document.getElementById('iconPreview');
            if (data.gambar_icon) {
                preview.innerHTML = '<img src=\"../assets/img/kategori/' + data.gambar_icon + '\" alt=\"Preview\">';
            } else {
                preview.innerHTML = '<i class=\"' + data.icon + ' text-primary\" style=\"font-size:32px\"></i>';
            }
        });
}
// Reset form when modal is hidden
document.getElementById('modalKategori').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('editId').value = 0;
    document.getElementById('editGambarLama').value = '';
    document.getElementById('iconPreview').innerHTML = '<i class="fas fa-image"></i>';
    document.querySelector('#modalKategori form').action = 'kategori.php';
});
// Live preview when file selected
document.getElementById('editGambar').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('iconPreview').innerHTML = '<img src=\"' + ev.target.result + '\" alt=\"Preview\">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
