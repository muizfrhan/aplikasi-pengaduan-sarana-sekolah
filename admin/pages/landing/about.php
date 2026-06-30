<?php
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if (isset($_POST['simpan'])) {
    $judul = bersihkan($_POST['judul']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    $icon = bersihkan($_POST['icon']);
    $urutan = (int)($_POST['urutan']);
    $status = bersihkan($_POST['status']);
    $gambar = $_POST['gambar_lama'] ?? '';

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
        execute("UPDATE landing_about SET judul=?, deskripsi=?, icon=?, gambar=?, urutan=?, status=? WHERE id=?", [$judul, $deskripsi, $icon, $gambar, $urutan, $status, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit about section", $judul);
        alert('success', 'About berhasil diperbarui!');
    } else {
        execute("INSERT INTO landing_about (judul, deskripsi, icon, gambar, urutan, status) VALUES (?, ?, ?, ?, ?, ?)", [$judul, $deskripsi, $icon, $gambar, $urutan, $status]);
        catat_aktivitas($_SESSION['user_id'], "Menambah about section", $judul);
        alert('success', 'About berhasil ditambahkan!');
    }
    redirect('index.php?page=landing-about');
}

if (isset($_GET['delete']) && $id > 0) {
    $row = fetch(query("SELECT gambar FROM landing_about WHERE id = ?", [$id]));
    if ($row && $row['gambar'] && file_exists('../assets/img/landing/' . $row['gambar'])) {
        unlink('../assets/img/landing/' . $row['gambar']);
    }
    execute("DELETE FROM landing_about WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus about section", "ID: $id");
    alert('success', 'About berhasil dihapus!');
    redirect('index.php?page=landing-about');
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM landing_about WHERE id = ?", [$id]));
}

$limit = 5;
$page = (int)($_GET['p'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND judul LIKE ?";
    $params[] = "%$search%";
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM landing_about $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT * FROM landing_about $where ORDER BY urutan ASC LIMIT $limit OFFSET $offset", $params);
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">About Section</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">About Section</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAbout">
        <i class="fas fa-plus me-1"></i>Tambah
    </button>
</div>

<div class="glass-card mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="page" value="landing-about">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari judul..." value="<?= $search ?>">
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
                    <th>Icon</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Gambar</th>
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
                    <td><i class="<?= $row['icon'] ?> text-primary"></i></td>
                    <td><strong><?= $row['judul'] ?></strong></td>
                    <td><small><?= potong_teks($row['deskripsi'] ?? '', 50) ?></small></td>
                    <td>
                        <?php if ($row['gambar']): ?>
                        <img src="../assets/img/landing/<?= $row['gambar'] ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $row['status'] === 'tampil' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td><?= $row['urutan'] ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="index.php?page=landing-about&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAbout" onclick="editAbout(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="index.php?page=landing-about&delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                        Tidak ada data
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
                <li class="page-item"><a class="page-link" href="?page=landing-about&p=<?= $page - 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=landing-about&p=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=landing-about&p=<?= $page + 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalAbout" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalTitle">Tambah About</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=landing-about" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <input type="hidden" name="gambar_lama" id="editGambarLama" value="">
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="judul" id="editJudul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon <span class="text-danger">*</span></label>
                        <select name="icon" id="editIcon" class="form-select" required>
                            <option value="fas fa-mouse-pointer">Mouse Pointer</option>
                            <option value="fas fa-bolt">Bolt</option>
                            <option value="fas fa-eye">Eye</option>
                            <option value="fas fa-cogs">Cogs</option>
                            <option value="fas fa-star">Star</option>
                            <option value="fas fa-heart">Heart</option>
                            <option value="fas fa-shield-alt">Shield</option>
                            <option value="fas fa-rocket">Rocket</option>
                            <option value="fas fa-globe">Globe</option>
                            <option value="fas fa-leaf">Leaf</option>
                            <option value="fas fa-handshake">Handshake</option>
                            <option value="fas fa-chart-line">Chart Line</option>
                            <option value="fas fa-users">Users</option>
                            <option value="fas fa-book">Book</option>
                            <option value="fas fa-graduation-cap">Graduation Cap</option>
                            <option value="fas fa-check-circle">Check Circle</option>
                            <option value="fas fa-clipboard-list">Clipboard</option>
                            <option value="fas fa-door-open">Door</option>
                            <option value="fas fa-edit">Edit</option>
                            <option value="fas fa-camera">Camera</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <div class="mb-2" id="gambarPreview">
                            <i class="fas fa-image text-muted" style="font-size:32px"></i>
                        </div>
                        <input type="file" name="gambar" id="editGambar" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewModalImage(this)">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" id="editUrutan" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="tampil">Tampil</option>
                                <option value="sembunyi">Sembunyi</option>
                            </select>
                        </div>
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
function editAbout(id) {
    fetch('pages/landing/get_about.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit About';
            document.getElementById('editId').value = data.id;
            document.getElementById('editJudul').value = data.judul;
            document.getElementById('editDeskripsi').value = data.deskripsi || '';
            document.getElementById('editIcon').value = data.icon;
            document.getElementById('editUrutan').value = data.urutan;
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editGambarLama').value = data.gambar || '';
            document.querySelector('#modalAbout form').action = 'index.php?page=landing-about&action=edit&id=' + id;
            const preview = document.getElementById('gambarPreview');
            if (data.gambar) {
                preview.innerHTML = '<img src="../assets/img/landing/' + data.gambar + '" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">';
            } else {
                preview.innerHTML = '<i class="fas fa-image text-muted" style="font-size:32px"></i>';
            }
        });
}
document.getElementById('modalAbout').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Tambah About';
    document.getElementById('editId').value = 0;
    document.getElementById('editGambarLama').value = '';
    document.getElementById('gambarPreview').innerHTML = '<i class="fas fa-image text-muted" style="font-size:32px"></i>';
    document.querySelector('#modalAbout form').action = 'index.php?page=landing-about';
});
function previewModalImage(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('gambarPreview').innerHTML = '<img src="' + e.target.result + '" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">';
        };
        reader.readAsDataURL(file);
    }
}
</script>
