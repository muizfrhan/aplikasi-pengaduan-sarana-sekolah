<?php
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if (isset($_POST['simpan'])) {
    $pertanyaan = bersihkan($_POST['pertanyaan']);
    $jawaban = bersihkan($_POST['jawaban']);
    $urutan = (int)$_POST['urutan'];
    $status = bersihkan($_POST['status']);

    if ($id > 0) {
        execute("UPDATE landing_faq SET pertanyaan = ?, jawaban = ?, urutan = ?, status = ? WHERE id = ?",
            [$pertanyaan, $jawaban, $urutan, $status, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit FAQ", potong_teks($pertanyaan, 50));
        alert('success', 'FAQ berhasil diperbarui!');
    } else {
        execute("INSERT INTO landing_faq (pertanyaan, jawaban, urutan, status) VALUES (?, ?, ?, ?)",
            [$pertanyaan, $jawaban, $urutan, $status]);
        catat_aktivitas($_SESSION['user_id'], "Menambah FAQ", potong_teks($pertanyaan, 50));
        alert('success', 'FAQ berhasil ditambahkan!');
    }
    redirect('index.php?page=landing-faq');
}

if (isset($_GET['delete']) && $id > 0) {
    execute("DELETE FROM landing_faq WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus FAQ", "ID: $id");
    alert('success', 'FAQ berhasil dihapus!');
    redirect('index.php?page=landing-faq');
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM landing_faq WHERE id = ?", [$id]));
}

$limit = 5;
$page = (int)($_GET['p'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (pertanyaan LIKE ? OR jawaban LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s];
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM landing_faq $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT * FROM landing_faq $where ORDER BY urutan ASC LIMIT $limit OFFSET $offset", $params);
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Data FAQ</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">FAQ</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFaq">
        <i class="fas fa-plus me-1"></i>Tambah FAQ
    </button>
</div>

<div class="glass-card mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="page" value="landing-faq">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari pertanyaan atau jawaban..." value="<?= $search ?>">
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
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
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
                    <td><strong><?= potong_teks($row['pertanyaan'], 60) ?></strong></td>
                    <td><small class="text-muted"><?= potong_teks($row['jawaban'], 80) ?></small></td>
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
                            <a href="index.php?page=landing-faq&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFaq" onclick="editFaq(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="index.php?page=landing-faq&delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fas fa-question-circle fa-2x mb-2 d-block"></i>
                        Tidak ada data FAQ
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
                <li class="page-item"><a class="page-link" href="?page=landing-faq&p=<?= $page - 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=landing-faq&p=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=landing-faq&p=<?= $page + 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalFaq" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalTitle">Tambah FAQ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=landing-faq">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="pertanyaan" id="editPertanyaan" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jawaban <span class="text-danger">*</span></label>
                        <textarea name="jawaban" id="editJawaban" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Urutan <span class="text-danger">*</span></label>
                            <input type="number" name="urutan" id="editUrutan" class="form-control" required>
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
function editFaq(id) {
    fetch('pages/landing/get_faq.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit FAQ';
            document.getElementById('editId').value = data.id;
            document.getElementById('editPertanyaan').value = data.pertanyaan;
            document.getElementById('editJawaban').value = data.jawaban;
            document.getElementById('editUrutan').value = data.urutan;
            document.getElementById('editStatus').value = data.status;
            document.querySelector('#modalFaq form').action = 'index.php?page=landing-faq&action=edit&id=' + id;
        });
}
document.getElementById('modalFaq').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Tambah FAQ';
    document.getElementById('editId').value = 0;
    document.querySelector('#modalFaq form').action = 'index.php?page=landing-faq';
});
</script>
