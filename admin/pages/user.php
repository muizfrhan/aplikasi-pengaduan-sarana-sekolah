<?php
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if (isset($_POST['simpan'])) {
    $username = bersihkan($_POST['username']);
    $password = $_POST['password'];
    $nama_lengkap = bersihkan($_POST['nama_lengkap']);
    $email = bersihkan($_POST['email']);
    $nis = bersihkan($_POST['nis']);
    $kelas = bersihkan($_POST['kelas']);
    $no_hp = bersihkan($_POST['no_hp']);
    $role = bersihkan($_POST['role']);
    $is_active = bersihkan($_POST['is_active']);

    if ($id > 0) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            execute("UPDATE users SET username=?, password=?, nama_lengkap=?, email=?, nis=?, kelas=?, no_hp=?, role=?, is_active=? WHERE id=?",
                [$username, $hash, $nama_lengkap, $email, $nis, $kelas, $no_hp, $role, $is_active, $id]);
        } else {
            execute("UPDATE users SET username=?, nama_lengkap=?, email=?, nis=?, kelas=?, no_hp=?, role=?, is_active=? WHERE id=?",
                [$username, $nama_lengkap, $email, $nis, $kelas, $no_hp, $role, $is_active, $id]);
        }
        catat_aktivitas($_SESSION['user_id'], "Mengedit user", $username);
        alert('success', 'User berhasil diperbarui!');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        execute("INSERT INTO users (username, password, nama_lengkap, email, nis, kelas, no_hp, role, is_active) VALUES (?,?,?,?,?,?,?,?,?)",
            [$username, $hash, $nama_lengkap, $email, $nis, $kelas, $no_hp, $role, $is_active]);
        catat_aktivitas($_SESSION['user_id'], "Menambah user", $username);
        alert('success', 'User berhasil ditambahkan!');
    }
    redirect('index.php?page=user');
}

if (isset($_GET['delete']) && $id > 0) {
    $target = fetch(query("SELECT role FROM users WHERE id = ?", [$id]));
    if ($target && $target['role'] === 'guru' && $_SESSION['role'] !== 'admin') {
        alert('error', 'Hanya admin yang dapat menghapus akun guru!');
        redirect('index.php?page=user');
    }
    execute("DELETE FROM users WHERE id = ? AND role != 'admin'", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus user", "ID: $id");
    alert('success', 'User berhasil dihapus!');
    redirect('index.php?page=user');
}

$editData = null;
if ($action === 'edit' && $id > 0) {
    $editData = fetch(query("SELECT * FROM users WHERE id = ?", [$id]));
}

$limit = 10;
$hal = (int)($_GET['hal'] ?? 1);
if ($hal < 1) $hal = 1;
$offset = ($hal - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND CONCAT_WS(' ', username, nama_lengkap, email, role, nis, kelas, no_hp, IF(is_active='Y','aktif','nonaktif')) LIKE ?";
    $params = ["%$search%"];
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM users $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT u.*, (SELECT COUNT(*) FROM pengaduan WHERE user_id = u.id) as jumlah_pengaduan FROM users u $where ORDER BY u.role, u.nama_lengkap LIMIT $limit OFFSET $offset", $params);
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Data User</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">User</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser">
        <i class="fas fa-plus me-1"></i>Tambah User
    </button>
</div>

<div class="glass-card mb-4">
    <form method="GET" class="row g-3" id="searchForm">
        <input type="hidden" name="page" value="user">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" id="searchInput" placeholder="Cari username, nama, email, role, status..." value="<?= $search ?>" autocomplete="off">
                <?php if ($search): ?>
                <a href="?page=user" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                <?php endif; ?>
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
                    <th>Foto</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Pengaduan</th>
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
                    <td>
                        <img src="../assets/img/<?= $row['foto'] ?? 'default.png' ?>" alt="" width="36" height="36" class="rounded-circle" style="object-fit: cover;">
                    </td>
                    <td><strong><?= $row['username'] ?></strong></td>
                    <td><?= $row['nama_lengkap'] ?></td>
                    <td><?= $row['email'] ?? '-' ?></td>
                    <td>
                        <?php if ($row['role'] === 'admin'): ?>
                        <span class="badge bg-primary">Admin</span>
                        <?php elseif ($row['role'] === 'guru'): ?>
                        <span class="badge bg-success">Guru</span>
                        <?php else: ?>
                        <span class="badge bg-info">User</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['is_active'] === 'Y'): ?>
                        <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['jumlah_pengaduan'] ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($row['role'] !== 'admin'):
                                $canEdit = true;
                                if ($row['role'] === 'guru' && $_SESSION['role'] !== 'admin') { $canEdit = false; }
                                if ($canEdit):
                            ?>
                            <a href="index.php?page=user&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="editUser(<?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="index.php?page=user&delete=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="badge bg-secondary">System</span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="badge bg-secondary">System</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        Tidak ada data user
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
                <?php if ($hal > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=user&hal=<?= $hal - 1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $hal ? 'active' : '' ?>"><a class="page-link" href="?page=user&hal=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($hal < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=user&hal=<?= $hal + 1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card p-0">
            <div class="modal-header border-0">
                <h6 class="fw-bold mb-0" id="modalUserTitle">Tambah User</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?page=user">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUserId" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="editPassword" class="form-control" placeholder="Kosongkan jika tidak diubah">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" id="editNama" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" id="editNis" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" id="editKelas" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" id="editNoHp" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="editRole" class="form-select">
                                <option value="user">User</option>
                                <option value="guru">Guru</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="editActive" class="form-select">
                                <option value="Y">Aktif</option>
                                <option value="N">Nonaktif</option>
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
// Live search dengan debounce
(function() {
    var input = document.getElementById('searchInput');
    var form = document.getElementById('searchForm');
    if (!input || !form) return;
    var timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            form.submit();
        }, 400);
    });
})();

function editUser(id) {
    fetch('pages/get_user.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalUserTitle').textContent = 'Edit User';
            document.getElementById('editUserId').value = data.id;
            document.getElementById('editUsername').value = data.username;
            document.getElementById('editPassword').placeholder = 'Kosongkan jika tidak diubah';
            document.getElementById('editNama').value = data.nama_lengkap;
            document.getElementById('editEmail').value = data.email || '';
            document.getElementById('editNis').value = data.nis || '';
            document.getElementById('editKelas').value = data.kelas || '';
            document.getElementById('editNoHp').value = data.no_hp || '';
            document.getElementById('editRole').value = data.role;
            document.getElementById('editActive').value = data.is_active;
            document.getElementById('editPassword').required = false;
            document.querySelector('#modalUser form').action = 'index.php?page=user&action=edit&id=' + id;
        });
}

document.getElementById('modalUser').addEventListener('show.bs.modal', function(e) {
    if (!e.relatedTarget || !e.relatedTarget.hasAttribute('onclick')) {
        document.getElementById('modalUserTitle').textContent = 'Tambah User';
        document.getElementById('editUserId').value = '0';
        document.getElementById('editUsername').value = '';
        document.getElementById('editPassword').value = '';
        document.getElementById('editPassword').required = true;
        document.getElementById('editNama').value = '';
        document.getElementById('editEmail').value = '';
        document.getElementById('editNis').value = '';
        document.getElementById('editKelas').value = '';
        document.getElementById('editNoHp').value = '';
        document.getElementById('editRole').value = 'user';
        document.getElementById('editActive').value = 'Y';
        document.querySelector('#modalUser form').action = 'index.php?page=user';
    }
});
</script>
