<?php
if ($_SESSION['role'] === 'guru') {
    redirect('index.php?page=dashboard');
    exit;
}
$action = $_GET['action'] ?? 'list';
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

// Update status
if (isset($_POST['update_status']) && $id > 0) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        alert('error', 'Token CSRF tidak valid!');
        redirect('index.php?page=password-reset');
    }
    $status = bersihkan($_POST['status'] ?? '');
    $catatan = bersihkan($_POST['catatan'] ?? '');
    $allowed = ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'];
    if (in_array($status, $allowed)) {
        execute("UPDATE password_reset_requests SET status = ?, catatan = ? WHERE id = ?", [$status, $catatan, $id]);
        $reqUser = fetch(query("SELECT user_id FROM password_reset_requests WHERE id = ?", [$id]));
        catat_aktivitas($_SESSION['user_id'], "Update status reset password", "ID: $id => $status");
        if ($reqUser) {
            $statusLower = strtolower($status);
            $notifLink = $statusLower === 'selesai' || $statusLower === 'ditolak' ? 'reset_password.php' : 'reset_password.php';
            buat_notifikasi((int)$reqUser['user_id'], "Reset Password $status", "Status permintaan reset password Anda telah diperbarui menjadi $status oleh Admin.", $notifLink, "reset_password");
        }
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Status Diperbarui', 'text' => "Status permintaan berhasil diperbarui ke: $status"];
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Status tidak valid!'];
    }
    redirect('index.php?page=password-reset');
}

// Reset password (simpan password baru)
if (isset($_POST['reset_password']) && $id > 0) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        alert('error', 'Token CSRF tidak valid!');
        redirect('index.php?page=password-reset');
    }
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (strlen($password_baru) < 8) {
        $_SESSION['swal'] = ['icon' => 'warning', 'title' => 'Password Terlalu Pendek', 'text' => 'Password minimal 8 karakter!'];
        redirect('index.php?page=password-reset');
    }
    if ($password_baru !== $konfirmasi) {
        $_SESSION['swal'] = ['icon' => 'warning', 'title' => 'Konfirmasi Password Tidak Cocok', 'text' => 'Password dan konfirmasi tidak sama!'];
        redirect('index.php?page=password-reset');
    }

    $req = fetch(query("SELECT * FROM password_reset_requests WHERE id = ?", [$id]));
    if (!$req) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Permintaan tidak ditemukan!'];
        redirect('index.php?page=password-reset');
    }

    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
    execute("UPDATE users SET password = ? WHERE id = ?", [$hash, $req['user_id']]);
    execute("UPDATE password_reset_requests SET status = 'Selesai', catatan = CONCAT(IFNULL(catatan, ''), ' Password telah direset oleh Admin.') WHERE id = ?", [$id]);

    catat_aktivitas($_SESSION['user_id'], "Reset password user", "User ID: {$req['user_id']}, Request ID: $id");
    buat_notifikasi((int)$req['user_id'], "Password Berhasil Direset", "Password akun Anda telah direset oleh Admin. Silakan cek pesan password baru.", "pesan_password.php", "password_baru");

    // Simpan data untuk modal Kirim Pesan
    $_SESSION['reset_data'] = [
        'request_id' => $req['id'],
        'user_id' => $req['user_id'],
        'nama' => $req['nama'],
        'nis' => $req['nis'],
        'password_baru' => $password_baru
    ];
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Password Berhasil Direset', 'text' => 'Password akun berhasil diperbarui. Klik OK untuk mengirim pesan ke User.'];
    redirect('index.php?page=password-reset');
}

// Kirim pesan password ke user
if (isset($_POST['kirim_pesan']) && isset($_SESSION['reset_data'])) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Token CSRF tidak valid!'];
        redirect('index.php?page=password-reset');
    }
    $rd = $_SESSION['reset_data'];
    $judul = bersihkan($_POST['judul'] ?? 'Password Baru Akun Anda');
    $isi_pesan = bersihkan($_POST['isi_pesan'] ?? '');

    if (!$isi_pesan) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Isi pesan tidak boleh kosong!'];
        redirect('index.php?page=password-reset');
    }

    insert("INSERT INTO password_messages (reset_request_id, user_id, admin_id, judul, isi_pesan, password_baru) VALUES (?, ?, ?, ?, ?, ?)",
        [$rd['request_id'], $rd['user_id'], $_SESSION['user_id'], $judul, $isi_pesan, $rd['password_baru']]);

    buat_notifikasi((int)$rd['user_id'], "Pesan Password Baru", "Admin mengirimkan password baru untuk akun Anda.", "pesan_password.php", "pesan_password");
    unset($_SESSION['reset_data']);
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Pesan Berhasil Dikirim', 'text' => 'Password baru berhasil dikirim ke User.'];
    redirect('index.php?page=password-reset');
}

$limit = 10;
$hal = (int)($_GET['hal'] ?? 1);
if ($hal < 1) $hal = 1;
$offset = ($hal - 1) * $limit;

$search = bersihkan($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (r.nama LIKE ? OR r.nis LIKE ? OR r.kelas LIKE ? OR r.no_hp LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s, $s, $s];
}

$totalData = (int)fetch(query("SELECT COUNT(*) as total FROM password_reset_requests r $where", $params))['total'];
$totalPages = ceil($totalData / $limit);

$data = query("SELECT r.*, u.username, u.email as user_email FROM password_reset_requests r LEFT JOIN users u ON r.user_id = u.id $where ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset", $params);
?>
<!-- SweetAlert2 from session -->
<?php if (isset($_SESSION['swal'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['reset_data'])): ?>
    Swal.fire({
        icon: '<?= $_SESSION['swal']['icon'] ?>',
        title: '<?= $_SESSION['swal']['title'] ?>',
        text: '<?= $_SESSION['swal']['text'] ?>',
        confirmButtonText: 'Kirim Pesan ke User',
        confirmButtonColor: '#0D6EFD'
    }).then(function() {
        var modal = new bootstrap.Modal(document.getElementById('modalKirimPesan'));
        modal.show();
    });
    <?php else: ?>
    Swal.fire({
        icon: '<?= $_SESSION['swal']['icon'] ?>',
        title: '<?= $_SESSION['swal']['title'] ?>',
        text: '<?= $_SESSION['swal']['text'] ?>'
    });
    <?php endif; ?>
});
</script>
<?php unset($_SESSION['swal']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['reset_data'])): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Pesan Password Belum Dikirim!</strong>
    Anda baru saja mereset password untuk <strong><?= $_SESSION['reset_data']['nama'] ?></strong>.
    <a href="#" onclick="event.preventDefault(); document.getElementById('btnManualKirimPesan').click();" class="alert-link">Klik di sini</a> untuk mengirim pesan password ke user.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<button id="btnManualKirimPesan" class="d-none" data-bs-toggle="modal" data-bs-target="#modalKirimPesan">Kirim Pesan</button>
<?php endif; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Permintaan Reset Password</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reset Password</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="badge bg-warning text-dark me-1">Menunggu: <?= hitung('password_reset_requests', "status='Menunggu'") ?></span>
        <span class="badge bg-info">Diproses: <?= hitung('password_reset_requests', "status='Diproses'") ?></span>
    </div>
</div>

<!-- Search -->
<div class="glass-card mb-4" data-aos="fade-up">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="page" value="password-reset">
        <div class="col-md-8">
            <label class="form-label">Cari Permintaan</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama, NIS, kelas, atau No HP..." value="<?= $search ?>">
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <div>
                <button type="submit" class="btn btn-primary me-1"><i class="fas fa-search me-1"></i>Cari</button>
                <?php if ($search): ?>
                <a href="index.php?page=password-reset" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Reset</a>
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
                    <th>Nama User</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th>No HP</th>
                    <th>Alasan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($data) > 0): ?>
                <?php $no = $offset + 1; while ($r = fetch($data)): ?>
                <?php
                $statusClass = match ($r['status']) {
                    'Menunggu' => 'bg-warning text-dark',
                    'Diproses' => 'bg-info text-white',
                    'Selesai' => 'bg-success text-white',
                    'Ditolak' => 'bg-danger text-white',
                    default => 'bg-secondary'
                };
                $canReset = in_array($r['status'], ['Menunggu', 'Diproses']);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <strong><?= $r['nama'] ?></strong>
                        <small class="d-block text-muted"><?= $r['user_email'] ?: 'Email tidak tersedia' ?></small>
                    </td>
                    <td><?= $r['nis'] ?></td>
                    <td><?= $r['kelas'] ?></td>
                    <td><?= $r['no_hp'] ?></td>
                    <td style="max-width:200px">
                        <span class="d-inline-block text-truncate" style="max-width:180px" title="<?= $r['alasan'] ?>">
                            <?= $r['alasan'] ?>
                        </span>
                        <?php if ($r['catatan']): ?>
                        <br><small class="text-muted"><i class="fas fa-comment me-1"></i><?= potong_teks($r['catatan'], 50) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><small><?= tgl_indonesia($r['created_at'], true) ?></small></td>
                    <td><span class="badge <?= $statusClass ?>"><?= $r['status'] ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalStatus<?= $r['id'] ?>" title="Update Status">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($canReset): ?>
                            <button class="btn btn-sm btn-warning btn-reset-pass"
                                    data-id="<?= $r['id'] ?>"
                                    data-nama="<?= $r['nama'] ?>"
                                    data-nis="<?= $r['nis'] ?>"
                                    title="Reset Password">
                                <i class="fas fa-key"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p class="mb-0">Belum ada permintaan reset password</p>
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
            <a class="page-link" href="?page=password-reset&hal=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- ==================== MODAL UPDATE STATUS ==================== -->
<?php mysqli_data_seek($data, 0); while ($r = fetch($data)): ?>
<div class="modal fade" id="modalStatus<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="hidden" name="update_status" value="1">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Update Status Permintaan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><strong>Nama:</strong> <?= $r['nama'] ?> (NIS: <?= $r['nis'] ?>)</p>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Menunggu" <?= $r['status'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Diproses" <?= $r['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $r['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="Ditolak" <?= $r['status'] === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Admin</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Opsional: berikan catatan untuk user"><?= $r['catatan'] ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

<!-- ==================== SINGLE MODAL RESET PASSWORD ==================== -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formResetPassword">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                <input type="hidden" name="reset_password" value="1">
                <input type="hidden" name="id" id="resetId" value="">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-key me-2 text-warning"></i>Reset Password User</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan mereset password untuk <strong id="resetNama"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama User</label>
                        <input type="text" class="form-control" id="resetNamaField" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" class="form-control" id="resetNisField" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <small class="text-danger">*Minimal 8 karakter</small></label>
                        <div class="input-group">
                            <input type="password" name="password_baru" id="passwordBaru" class="form-control" minlength="8" required placeholder="Masukkan password baru">
                            <button type="button" class="btn btn-outline-secondary toggle-password" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" name="konfirmasi_password" id="konfirmasiPassword" class="form-control" minlength="8" required placeholder="Ulangi password baru">
                            <button type="button" class="btn btn-outline-secondary toggle-password" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="lihatPassword">
                        <label class="form-check-label" for="lihatPassword">Lihat Password</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnSimpanPassword">
                        <i class="fas fa-save me-1"></i>Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL KIRIM PESAN PASSWORD ==================== -->
<div class="modal fade" id="modalKirimPesan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                <input type="hidden" name="kirim_pesan" value="1">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-envelope me-2 text-primary"></i>Kirim Password Baru ke User</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama User</label>
                            <input type="text" class="form-control" id="pesanNama" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIS</label>
                            <input type="text" class="form-control" id="pesanNis" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="pesanPassword" readonly>
                                <button type="button" class="btn btn-outline-secondary" id="togglePesanPassword" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Judul Pesan</label>
                            <input type="text" name="judul" class="form-control" id="pesanJudul" required value="Password Baru Akun Anda">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Isi Pesan <small class="text-danger">*Wajib diisi</small></label>
                            <textarea name="isi_pesan" class="form-control" id="pesanIsi" rows="5" required placeholder="Contoh: Yth. [Nama User], password baru akun Anda adalah: [Password]. Silakan login dan ganti password Anda segera."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Kirim ke User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==================== KIRIM PESAN MODAL AUTO-FILL ====================
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['reset_data'])): ?>
    var rd = <?= json_encode($_SESSION['reset_data']) ?>;
    document.getElementById('pesanNama').value = rd.nama;
    document.getElementById('pesanNis').value = rd.nis;
    document.getElementById('pesanPassword').value = rd.password_baru;
    document.getElementById('pesanIsi').value =
        'Yth. ' + rd.nama + ',\n\n' +
        'Password baru akun Anda adalah: ' + rd.password_baru + '\n\n' +
        'Silakan login menggunakan password baru tersebut. Setelah berhasil login, kami sarankan untuk segera mengganti password dengan yang lebih aman dan mudah diingat.\n\n' +
        'Terima kasih,\n' +
        'Admin PSK';
    <?php endif; ?>
});

// Toggle password visibility for modal kirim pesan
document.getElementById('togglePesanPassword').addEventListener('click', function() {
    var input = document.getElementById('pesanPassword');
    var icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Auto-close backdrop when modal kirim pesan is hidden
document.getElementById('modalKirimPesan').addEventListener('hidden.bs.modal', function() {
    var backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(function(b) { b.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});

// ==================== RESET PASSWORD MODAL ====================
document.querySelectorAll('.btn-reset-pass').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var nama = this.getAttribute('data-nama');
        var nis = this.getAttribute('data-nis');

        document.getElementById('resetId').value = id;
        document.getElementById('resetNama').textContent = nama;
        document.getElementById('resetNamaField').value = nama;
        document.getElementById('resetNisField').value = nis;
        document.getElementById('passwordBaru').value = '';
        document.getElementById('konfirmasiPassword').value = '';
        document.getElementById('lihatPassword').checked = false;

        var modal = new bootstrap.Modal(document.getElementById('modalResetPassword'));
        modal.show();
    });
});

// Toggle password visibility (all inputs with .toggle-password)
document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = this.closest('.input-group').querySelector('input');
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// "Lihat Password" checkbox toggles both password fields
document.getElementById('lihatPassword').addEventListener('change', function() {
    var type = this.checked ? 'text' : 'password';
    document.getElementById('passwordBaru').type = type;
    document.getElementById('konfirmasiPassword').type = type;
});

// Client-side validation with SweetAlert2 before submit
document.getElementById('formResetPassword').addEventListener('submit', function(e) {
    var pass = document.getElementById('passwordBaru').value;
    var konfirm = document.getElementById('konfirmasiPassword').value;

    if (pass.length < 8) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Password Terlalu Pendek',
            text: 'Password minimal 8 karakter!'
        });
        return;
    }

    if (pass !== konfirm) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Password Tidak Cocok',
            text: 'Password dan konfirmasi tidak sama!'
        });
        return;
    }

    // Konfirmasi reset
    e.preventDefault();
    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi Reset Password',
        text: 'Yakin ingin mereset password user ini?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#F59E0B'
    }).then(function(result) {
        if (result.isConfirmed) {
            // Close modal then submit
            var modalEl = document.getElementById('modalResetPassword');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            // Submit form via AJAX to avoid page flicker
            var form = document.getElementById('formResetPassword');
            var formData = new FormData(form);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(function() {
                // Reload page to reflect changes
                window.location.reload();
            });
        }
    });
});

// Auto-close backdrop when modal is hidden
document.getElementById('modalResetPassword').addEventListener('hidden.bs.modal', function() {
    var backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(function(b) { b.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>
