<?php
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if (isset($_POST['simpan'])) {
    $nomor = (int)$_POST['nomor'];
    $judul = bersihkan($_POST['judul']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    $icon = bersihkan($_POST['icon']);
    $gambar = $_POST['gambar_lama'] ?? '';
    $warna_card = bersihkan($_POST['warna_card']);
    $urutan = (int)$_POST['urutan'];
    $status = bersihkan($_POST['status']);

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['gambar'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($gambar && file_exists('../assets/img/landing/' . $gambar)) {
                unlink('../assets/img/landing/' . $gambar);
            }
            $gambar = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    if ($id > 0) {
        execute("UPDATE landing_steps SET nomor = ?, judul = ?, deskripsi = ?, icon = ?, gambar = ?, warna_card = ?, urutan = ?, status = ? WHERE id = ?",
            [$nomor, $judul, $deskripsi, $icon, $gambar, $warna_card, $urutan, $status, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit step cara pengaduan", $judul);
        alert('success', 'Step berhasil diperbarui!');
    } else {
        execute("INSERT INTO landing_steps (nomor, judul, deskripsi, icon, gambar, warna_card, urutan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$nomor, $judul, $deskripsi, $icon, $gambar, $warna_card, $urutan, $status]);
        catat_aktivitas($_SESSION['user_id'], "Menambah step cara pengaduan", $judul);
        alert('success', 'Step berhasil ditambahkan!');
    }
    redirect('index.php?page=landing-steps');
}

if (isset($_GET['delete']) && $id > 0) {
    $s = fetch(query("SELECT gambar FROM landing_steps WHERE id = ?", [$id]));
    if ($s && $s['gambar'] && file_exists('../assets/img/landing/' . $s['gambar'])) {
        unlink('../assets/img/landing/' . $s['gambar']);
    }
    execute("DELETE FROM landing_steps WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus step cara pengaduan", "ID: $id");
    alert('success', 'Step berhasil dihapus!');
    redirect('index.php?page=landing-steps');
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM landing_steps WHERE id = ?", [$id]));
}

$limit = 5;
$page = (int)($_GET['p'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (judul LIKE ? OR deskripsi LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s];
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM landing_steps $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT * FROM landing_steps $where ORDER BY urutan ASC LIMIT $limit OFFSET $offset", $params);
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Data Cara Pengaduan</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Cara Pengaduan</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalStep">
        <i class="fas fa-plus me-1"></i>Tambah Step
    </button>
</div>

<div class="glass-card mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="page" value="landing-steps">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari judul atau deskripsi..." value="<?= $search ?>">
            </div>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Cari</button>
        </div>
    </form>
</div>

<div class="glass-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor</th>
                    <th>Icon</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Warna</th>
                    <th>Status</th>
                    <th>Urutan</th>
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
                    <td><span class="badge bg-primary"><?= $row['nomor'] ?></span></td>
                    <td>
                        <?php if ($row['gambar']): ?>
                        <img src="../assets/img/landing/<?= $row['gambar'] ?>" alt="" width="32" height="32" style="object-fit:cover;border-radius:6px;">
                        <?php else: ?>
                        <i class="<?= $row['icon'] ?> text-primary" style="font-size:20px"></i>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= $row['judul'] ?></strong></td>
                    <td><small class="text-muted"><?= potong_teks($row['deskripsi'], 50) ?></small></td>
                    <td>
                        <span class="badge" style="background-color:<?= $row['warna_card'] ?>"><?= $row['warna_card'] ?></span>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'tampil'): ?>
                        <span class="badge bg-success">Tampil</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Sembunyi</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['urutan'] ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="index.php?page=landing-steps&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalStep" onclick="editStep(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="index.php?page=landing-steps&delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <i class="fas fa-list fa-2x mb-2 d-block"></i>
                        Tidak ada data step
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
                <li class="page-item"><a class="page-link" href="?page=landing-steps&p=<?= $page - 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=landing-steps&p=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=landing-steps&p=<?= $page + 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalStep" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalTitle">Tambah Step</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=landing-steps" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <input type="hidden" name="gambar_lama" id="editGambarLama" value="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor <span class="text-danger">*</span></label>
                            <input type="number" name="nomor" id="editNomor" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Urutan <span class="text-danger">*</span></label>
                            <input type="number" name="urutan" id="editUrutan" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="tampil">Tampil</option>
                                <option value="sembunyi">Sembunyi</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="judul" id="editJudul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon (Font Awesome)</label>
                            <select name="icon" id="editIcon" class="form-select">
                                <option value="fas fa-clipboard-list">Clipboard List</option>
                                <option value="fas fa-edit">Edit</option>
                                <option value="fas fa-camera">Camera</option>
                                <option value="fas fa-paper-plane">Paper Plane</option>
                                <option value="fas fa-cog">Cog</option>
                                <option value="fas fa-check-circle">Check Circle</option>
                                <option value="fas fa-circle">Circle</option>
                                <option value="fas fa-arrow-right">Arrow Right</option>
                                <option value="fas fa-hand-pointer">Hand Pointer</option>
                                <option value="fas fa-search">Search</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Warna Card</label>
                            <input type="color" name="warna_card" id="editWarna" class="form-control form-control-color" value="#2563EB">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar <small class="text-muted">(opsional, upload gambar kustom)</small></label>
                        <div class="mb-2" id="gambarPreview">
                            <i class="fas fa-image text-muted" style="font-size:32px"></i>
                        </div>
                        <input type="file" name="gambar" id="editGambar" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
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
function editStep(id) {
    fetch('pages/landing/get_steps.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Step';
            document.getElementById('editId').value = data.id;
            document.getElementById('editNomor').value = data.nomor;
            document.getElementById('editJudul').value = data.judul;
            document.getElementById('editDeskripsi').value = data.deskripsi;
            document.getElementById('editIcon').value = data.icon;
            document.getElementById('editWarna').value = data.warna_card;
            document.getElementById('editUrutan').value = data.urutan;
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editGambarLama').value = data.gambar || '';
            document.querySelector('#modalStep form').action = 'index.php?page=landing-steps&action=edit&id=' + id;
            const preview = document.getElementById('gambarPreview');
            if (data.gambar) {
                preview.innerHTML = '<img src="../assets/img/landing/' + data.gambar + '" alt="Preview" style="max-height:80px;border-radius:6px;">';
            } else if (data.icon) {
                preview.innerHTML = '<i class="' + data.icon + ' text-primary" style="font-size:32px"></i>';
            } else {
                preview.innerHTML = '<i class="fas fa-image text-muted" style="font-size:32px"></i>';
            }
        });
}
document.getElementById('modalStep').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Tambah Step';
    document.getElementById('editId').value = 0;
    document.getElementById('editGambarLama').value = '';
    document.getElementById('gambarPreview').innerHTML = '<i class="fas fa-image text-muted" style="font-size:32px"></i>';
    document.querySelector('#modalStep form').action = 'index.php?page=landing-steps';
});
document.getElementById('editGambar').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('gambarPreview').innerHTML = '<img src="' + ev.target.result + '" alt="Preview" style="max-height:80px;border-radius:6px;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>
