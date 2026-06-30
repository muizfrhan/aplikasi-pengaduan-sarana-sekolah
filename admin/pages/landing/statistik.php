<?php
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

function autoAngka(string $tipe): string {
    require_once __DIR__ . '/../../../config/database.php';
    $angka = match ($tipe) {
        'pengaduan:all'      => hitung('pengaduan'),
        'pengaduan:menunggu' => hitung('pengaduan', "status='menunggu'"),
        'pengaduan:diproses' => hitung('pengaduan', "status='diproses'"),
        'pengaduan:selesai'  => hitung('pengaduan', "status='selesai'"),
        'pengaduan:ditolak'  => hitung('pengaduan', "status='ditolak'"),
        'ruangan:all'        => hitung('ruangan'),
        'kategori:all'       => hitung('kategori'),
        'users:all'          => hitung('users'),
        'users:user'         => hitung('users', "role='user'"),
        'users:admin'        => hitung('users', "role='admin'"),
        default              => '0',
    };
    return (string)$angka;
}

if (isset($_POST['simpan'])) {
    $nama = bersihkan($_POST['nama']);
    $tipe = bersihkan($_POST['tipe'] ?? 'manual');
    $angka = ($tipe === 'manual') ? bersihkan($_POST['angka']) : autoAngka($tipe);
    $icon = bersihkan($_POST['icon']);
    $warna = bersihkan($_POST['warna']);
    $urutan = (int)$_POST['urutan'];
    $status = bersihkan($_POST['status']);

    if ($id > 0) {
        execute("UPDATE landing_statistics SET nama = ?, angka = ?, tipe = ?, icon = ?, warna = ?, urutan = ?, status = ? WHERE id = ?",
            [$nama, $angka, $tipe, $icon, $warna, $urutan, $status, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit statistik", $nama);
        alert('success', 'Statistik berhasil diperbarui!');
    } else {
        execute("INSERT INTO landing_statistics (nama, angka, tipe, icon, warna, urutan, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$nama, $angka, $tipe, $icon, $warna, $urutan, $status]);
        catat_aktivitas($_SESSION['user_id'], "Menambah statistik", $nama);
        alert('success', 'Statistik berhasil ditambahkan!');
    }
    redirect('index.php?page=landing-statistik');
}

if (isset($_GET['delete']) && $id > 0) {
    execute("DELETE FROM landing_statistics WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus statistik", "ID: $id");
    alert('success', 'Statistik berhasil dihapus!');
    redirect('index.php?page=landing-statistik');
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM landing_statistics WHERE id = ?", [$id]));
}

$limit = 5;
$page = (int)($_GET['p'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (nama LIKE ? OR tipe LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s];
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM landing_statistics $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT * FROM landing_statistics $where ORDER BY urutan ASC LIMIT $limit OFFSET $offset", $params);
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Data Statistik</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Statistik</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalStatistik">
        <i class="fas fa-plus me-1"></i>Tambah Statistik
    </button>
</div>

<div class="glass-card mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="page" value="landing-statistik">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama statistik..." value="<?= $search ?>">
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
                    <th>Nama</th>
                    <th>Angka</th>
                    <th>Tipe</th>
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
                    <td><i class="<?= $row['icon'] ?> text-primary" style="font-size:20px"></i></td>
                    <td><strong><?= $row['nama'] ?></strong></td>
                    <td>
                        <?php
                        $tipe = $row['tipe'] ?? 'manual';
                        if ($tipe === 'manual'):
                            echo $row['angka'];
                        else:
                            echo '<span class="badge bg-info">' . autoAngka($tipe) . '</span>';
                        endif;
                        ?>
                    </td>
                    <td><code><?= $tipe ?></code></td>
                    <td>
                        <span class="badge" style="background-color:<?= $row['warna'] ?>"><?= $row['warna'] ?></span>
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
                            <a href="index.php?page=landing-statistik&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalStatistik" onclick="editStatistik(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="index.php?page=landing-statistik&delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                        Tidak ada data statistik
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
                <li class="page-item"><a class="page-link" href="?page=landing-statistik&p=<?= $page - 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=landing-statistik&p=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=landing-statistik&p=<?= $page + 1 ?>&search=<?= $search ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalStatistik" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalTitle">Tambah Statistik</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=landing-statistik">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="editNama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Data <span class="text-danger">*</span></label>
                        <select name="tipe" id="editTipe" class="form-select" onchange="toggleAngkaManual()">
                            <option value="pengaduan:all">📋 Total Pengaduan</option>
                            <option value="pengaduan:menunggu">⏳ Pengaduan Menunggu</option>
                            <option value="pengaduan:diproses">🔄 Pengaduan Diproses</option>
                            <option value="pengaduan:selesai">✅ Pengaduan Selesai</option>
                            <option value="pengaduan:ditolak">❌ Pengaduan Ditolak</option>
                            <option value="ruangan:all">🚪 Total Ruangan</option>
                            <option value="kategori:all">📂 Total Kategori</option>
                            <option value="users:all">👥 Total Pengguna</option>
                            <option value="users:user">🎓 Total Siswa</option>
                            <option value="users:admin">🛡️ Total Admin</option>
                            <option value="manual">✏️ Manual (isi sendiri)</option>
                        </select>
                        <small class="text-muted">Pilih sumber data otomatis. Pilih "Manual" jika ingin mengisi angka sendiri.</small>
                    </div>
                    <div class="mb-3" id="fieldAngkaManual">
                        <label class="form-label">Angka <span class="text-danger">*</span></label>
                        <input type="text" name="angka" id="editAngka" class="form-control" placeholder="Contoh: 500+">
                        <small class="text-muted">Hanya diisi jika Tipe Data = Manual</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome)</label>
                        <select name="icon" id="editIcon" class="form-select">
                            <option value="fas fa-clipboard-list">Clipboard List</option>
                            <option value="fas fa-check-circle">Check Circle</option>
                            <option value="fas fa-door-open">Door Open</option>
                            <option value="fas fa-users">Users</option>
                            <option value="fas fa-school">School</option>
                            <option value="fas fa-book">Book</option>
                            <option value="fas fa-chalkboard">Chalkboard</option>
                            <option value="fas fa-laptop">Laptop</option>
                            <option value="fas fa-flask">Flask</option>
                            <option value="fas fa-chart-bar">Chart Bar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" name="warna" id="editWarna" class="form-control form-control-color" value="#2563EB">
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
function toggleAngkaManual() {
    var tipe = document.getElementById('editTipe').value;
    var field = document.getElementById('fieldAngkaManual');
    var input = document.getElementById('editAngka');
    if (tipe === 'manual') {
        field.style.display = 'block';
        input.required = true;
    } else {
        field.style.display = 'none';
        input.required = false;
    }
}

function editStatistik(id) {
    fetch('pages/landing/get_statistics.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Statistik';
            document.getElementById('editId').value = data.id;
            document.getElementById('editNama').value = data.nama;
            document.getElementById('editTipe').value = data.tipe || 'manual';
            document.getElementById('editAngka').value = data.angka;
            document.getElementById('editIcon').value = data.icon;
            document.getElementById('editWarna').value = data.warna;
            document.getElementById('editUrutan').value = data.urutan;
            document.getElementById('editStatus').value = data.status;
            toggleAngkaManual();
            document.querySelector('#modalStatistik form').action = 'index.php?page=landing-statistik&action=edit&id=' + id;
        });
}
document.getElementById('modalStatistik').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Tambah Statistik';
    document.getElementById('editId').value = 0;
    document.getElementById('editTipe').value = 'pengaduan:all';
    document.getElementById('editAngka').value = '';
    toggleAngkaManual();
    document.querySelector('#modalStatistik form').action = 'index.php?page=landing-statistik';
});
toggleAngkaManual();
</script>
