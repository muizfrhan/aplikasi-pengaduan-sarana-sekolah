<?php
if ($_SESSION['role'] === 'guru') {
    redirect('index.php?page=dashboard');
    exit;
}
$id = (int)($_POST['id'] ?? $_GET['id'] ?? $_GET['delete'] ?? 0);

// Hapus pesan
if (isset($_GET['delete']) && $id > 0) {
    if (!validasi_csrf($_GET['csrf'] ?? '')) {
        alert('error', 'Token CSRF tidak valid!');
        redirect('index.php?page=password-messages');
    }
    execute("DELETE FROM password_messages WHERE id = ?", [$id]);
    catat_aktivitas($_SESSION['user_id'], "Menghapus pesan password", "ID: $id");
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Berhasil Dihapus', 'text' => 'Pesan password berhasil dihapus!'];
    redirect('index.php?page=password-messages');
}

// Kirim pesan baru (Tambah)
if (isset($_POST['kirim_pesan_baru'])) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        alert('error', 'Token CSRF tidak valid!');
        redirect('index.php?page=password-messages');
    }
    $user_id = (int)$_POST['user_id'];
    $judul = bersihkan($_POST['judul'] ?? '');
    $isi_pesan = bersihkan($_POST['isi_pesan'] ?? '');
    $password_baru = bersihkan($_POST['password_baru'] ?? '');
    if ($user_id > 0 && $judul && $isi_pesan && $password_baru) {
        insert("INSERT INTO password_messages (user_id, admin_id, judul, isi_pesan, password_baru) VALUES (?, ?, ?, ?, ?)",
            [$user_id, $_SESSION['user_id'], $judul, $isi_pesan, $password_baru]);
        catat_aktivitas($_SESSION['user_id'], "Mengirim pesan password baru", "User ID: $user_id");
        buat_notifikasi($user_id, "Pesan Password Baru", "Admin mengirimkan password baru untuk akun Anda.", "pesan_password.php", "pesan_password");
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Pesan Berhasil Dikirim', 'text' => 'Pesan password berhasil dikirim ke user!'];
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Semua field harus diisi!'];
    }
    redirect('index.php?page=password-messages');
}

// Edit pesan
if (isset($_POST['edit_pesan']) && $id > 0) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        alert('error', 'Token CSRF tidak valid!');
        redirect('index.php?page=password-messages');
    }
    $judul = bersihkan($_POST['judul'] ?? '');
    $isi_pesan = bersihkan($_POST['isi_pesan'] ?? '');
    $password_baru = bersihkan($_POST['password_baru'] ?? '');
    if ($judul && $isi_pesan && $password_baru) {
        execute("UPDATE password_messages SET judul = ?, isi_pesan = ?, password_baru = ? WHERE id = ?",
            [$judul, $isi_pesan, $password_baru, $id]);
        catat_aktivitas($_SESSION['user_id'], "Mengedit pesan password", "ID: $id");
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Pesan Diperbarui', 'text' => 'Pesan password berhasil diperbarui!'];
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Semua field harus diisi!'];
    }
    redirect('index.php?page=password-messages');
}

// Kirim ulang (buat record baru dengan data yang sama)
if (isset($_POST['kirim_ulang']) && $id > 0) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        alert('error', 'Token CSRF tidak valid!');
        redirect('index.php?page=password-messages');
    }
    $asal = fetch(query("SELECT * FROM password_messages WHERE id = ?", [$id]));
    if ($asal) {
        insert("INSERT INTO password_messages (reset_request_id, user_id, admin_id, judul, isi_pesan, password_baru) VALUES (?, ?, ?, ?, ?, ?)",
            [$asal['reset_request_id'], $asal['user_id'], $_SESSION['user_id'], $asal['judul'], $asal['isi_pesan'], $asal['password_baru']]);
        catat_aktivitas($_SESSION['user_id'], "Mengirim ulang pesan password", "Asal ID: $id");
        buat_notifikasi((int)$asal['user_id'], "Pesan Password Baru", "Admin mengirim ulang password baru untuk akun Anda.", "pesan_password.php", "pesan_password");
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Pesan Dikirim Ulang', 'text' => 'Pesan password berhasil dikirim ulang!'];
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Pesan asal tidak ditemukan!'];
    }
    redirect('index.php?page=password-messages');
}

$limit = 10;
$hal = (int)($_GET['hal'] ?? 1);
if ($hal < 1) $hal = 1;
$offset = ($hal - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$filter_status = bersihkan($_GET['filter_status'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (pm.judul LIKE ? OR u.nama_lengkap LIKE ? OR u.nis LIKE ? OR u.username LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s, $s, $s];
}
if ($filter_status === 'Belum Dibaca' || $filter_status === 'Sudah Dibaca') {
    $where .= " AND pm.status_baca = ?";
    $params[] = $filter_status;
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM password_messages pm LEFT JOIN users u ON pm.user_id = u.id $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT pm.*, u.nama_lengkap, u.nis, u.username, u.email, a.nama_lengkap as admin_nama FROM password_messages pm LEFT JOIN users u ON pm.user_id = u.id LEFT JOIN users a ON pm.admin_id = a.id $where ORDER BY pm.created_at DESC LIMIT $limit OFFSET $offset", $params);
?>
<?php if (isset($_SESSION['swal'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?= $_SESSION['swal']['icon'] ?>',
        title: '<?= $_SESSION['swal']['title'] ?>',
        text: '<?= $_SESSION['swal']['text'] ?>'
    });
});
</script>
<?php unset($_SESSION['swal']); ?>
<?php endif; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Riwayat Pesan Password</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Riwayat Pesan Password</li>
            </ol>
        </nav>
    </div>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKirimBaru">
            <i class="fas fa-paper-plane me-1"></i>Kirim Pesan Baru
        </button>
    </div>
</div>

<!-- Search & Filter -->
<div class="glass-card mb-4" data-aos="fade-up">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="page" value="password-messages">
        <div class="col-md-5">
            <label class="form-label">Cari Pesan</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari judul, nama user, atau NIS..." value="<?= $search ?>">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Filter Status</label>
            <select name="filter_status" class="form-select">
                <option value="">Semua Status</option>
                <option value="Belum Dibaca" <?= $filter_status === 'Belum Dibaca' ? 'selected' : '' ?>>Belum Dibaca</option>
                <option value="Sudah Dibaca" <?= $filter_status === 'Sudah Dibaca' ? 'selected' : '' ?>>Sudah Dibaca</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <div>
                <button type="submit" class="btn btn-primary me-1"><i class="fas fa-search me-1"></i>Cari</button>
                <?php if ($search || $filter_status): ?>
                <a href="index.php?page=password-messages" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Reset</a>
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
                    <th>Tanggal</th>
                    <th>User</th>
                    <th>Judul</th>
                    <th>Password</th>
                    <th>Status Baca</th>
                    <th>Dibaca</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($data) > 0): ?>
                <?php $no = $offset + 1; while ($r = fetch($data)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><small><?= tgl_indonesia($r['created_at'], true) ?></small></td>
                    <td>
                        <strong><?= $r['nama_lengkap'] ?: 'User#' . $r['user_id'] ?></strong>
                        <small class="d-block text-muted">NIS: <?= $r['nis'] ?: '-' ?></small>
                    </td>
                    <td><?= $r['judul'] ?></td>
                    <td>
                        <div class="input-group input-group-sm" style="max-width:200px">
                            <input type="password" class="form-control form-control-sm password-toggle-<?= $r['id'] ?>" value="<?= $r['password_baru'] ?>" readonly>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePasswordView(<?= $r['id'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        <?php if ($r['status_baca'] === 'Sudah Dibaca'): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Sudah Dibaca</span>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Belum Dibaca</span>
                        <?php endif; ?>
                    </td>
                    <td><small><?= $r['read_at'] ? tgl_indonesia($r['read_at'], true) : '-' ?></small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalLihat<?= $r['id'] ?>" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $r['id'] ?>" title="Edit Pesan">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="kirimUlang(<?= $r['id'] ?>)" title="Kirim Ulang">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="hapusPesan(<?= $r['id'] ?>)" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p class="mb-0">Belum ada pesan password terkirim</p>
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
            <a class="page-link" href="?page=password-messages&hal=<?= $i ?>&search=<?= urlencode($search) ?>&filter_status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- ==================== MODAL KIRIM PESAN BARU ==================== -->
<div class="modal fade" id="modalKirimBaru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                <input type="hidden" name="kirim_pesan_baru" value="1">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Kirim Pesan Password Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pilih User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Pilih User --</option>
                                <?php
                                $users = query("SELECT id, nama_lengkap, nis, username FROM users WHERE role = 'user' ORDER BY nama_lengkap ASC");
                                while ($u = fetch($users)):
                                ?>
                                <option value="<?= $u['id'] ?>"><?= $u['nama_lengkap'] ?> (<?= $u['nis'] ?: $u['username'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Judul Pesan <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required value="Password Baru Akun Anda">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="password_baru" class="form-control" required placeholder="Masukkan password baru">
                                <button type="button" class="btn btn-outline-secondary" onclick="generatePassword(this)" title="Generate Password">
                                    <i class="fas fa-dice"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea name="isi_pesan" class="form-control" rows="5" required placeholder="Contoh: Yth. [Nama User], password baru akun Anda adalah: [Password]. Silakan login dan ganti password Anda segera."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Kirim Pesan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL LIHAT ==================== -->
<?php mysqli_data_seek($data, 0); while ($r = fetch($data)): ?>
<div class="modal fade" id="modalLihat<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Detail Pesan Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">User</small>
                        <strong><?= $r['nama_lengkap'] ?: 'User#' . $r['user_id'] ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">NIS</small>
                        <strong><?= $r['nis'] ?: '-' ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Dikirim Oleh</small>
                        <strong><?= $r['admin_nama'] ?: 'Admin#' . $r['admin_id'] ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Status</small>
                        <?php if ($r['status_baca'] === 'Sudah Dibaca'): ?>
                        <span class="badge bg-success">Sudah Dibaca <?= $r['read_at'] ? '(' . tgl_indonesia($r['read_at'], true) . ')' : '' ?></span>
                        <?php else: ?>
                        <span class="badge bg-warning text-dark">Belum Dibaca</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Tanggal Kirim</small>
                        <strong><?= tgl_indonesia($r['created_at'], true) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Password Baru</small>
                        <div class="input-group input-group-sm">
                            <input type="password" class="form-control" id="lihatPass_<?= $r['id'] ?>" value="<?= $r['password_baru'] ?>" readonly>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePass('lihatPass_<?= $r['id'] ?>', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted d-block">Judul</small>
                    <h6 class="fw-bold"><?= $r['judul'] ?></h6>
                </div>
                <div>
                    <small class="text-muted d-block">Isi Pesan</small>
                    <p class="mb-0" style="white-space:pre-wrap"><?= $r['isi_pesan'] ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDIT ==================== -->
<div class="modal fade" id="modalEdit<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                <input type="hidden" name="edit_pesan" value="1">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Pesan Password</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama User</label>
                            <input type="text" class="form-control" value="<?= $r['nama_lengkap'] ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIS</label>
                            <input type="text" class="form-control" value="<?= $r['nis'] ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru</label>
                            <input type="text" name="password_baru" class="form-control" value="<?= $r['password_baru'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Judul Pesan</label>
                            <input type="text" name="judul" class="form-control" value="<?= $r['judul'] ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Isi Pesan</label>
                            <textarea name="isi_pesan" class="form-control" rows="5" required><?= $r['isi_pesan'] ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

<script>
// Toggle password in table
function togglePasswordView(id) {
    var input = document.querySelector('.password-toggle-' + id);
    var btn = input.nextElementSibling;
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Toggle password in modal lihat
function togglePass(id, btn) {
    var input = document.getElementById(id);
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Hapus pesan
function hapusPesan(id) {
    var csrf = '<?= generate_csrf() ?>';
    Swal.fire({
        icon: 'warning',
        title: 'Konfirmasi Hapus',
        text: 'Yakin ingin menghapus pesan password ini?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#DC3545'
    }).then(function(result) {
        if (result.isConfirmed) {
            window.location.href = 'index.php?page=password-messages&id=' + id + '&delete=' + id + '&csrf=' + csrf;
        }
    });
}

// Generate random password for Kirim Pesan Baru modal
function generatePassword(btn) {
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
    var pass = '';
    for (var i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    var input = btn.closest('.input-group').querySelector('input');
    input.value = pass;
}

// Kirim ulang
function kirimUlang(id) {
    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi Kirim Ulang',
        text: 'Yakin ingin mengirim ulang pesan ini? Pesan baru akan dibuat dengan konten yang sama.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim Ulang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0D6EFD'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = 'csrf_token';
            csrf.value = '<?= generate_csrf() ?>';
            form.appendChild(csrf);
            var kirim = document.createElement('input');
            kirim.type = 'hidden';
            kirim.name = 'kirim_ulang';
            kirim.value = '1';
            form.appendChild(kirim);
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
