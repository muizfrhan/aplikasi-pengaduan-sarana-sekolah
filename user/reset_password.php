<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_user();

$userId = $_SESSION['user_id'];
$user = fetch(query("SELECT * FROM users WHERE id = ?", [$userId]));

// Kirim permintaan reset
if (isset($_POST['kirim'])) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Token CSRF tidak valid!'];
        redirect('reset_password.php');
    }

    $nama = bersihkan($_POST['nama_lengkap'] ?? '');
    $nis = bersihkan($_POST['nis'] ?? '');
    $kelas = bersihkan($_POST['kelas'] ?? '');
    $no_hp = bersihkan($_POST['no_hp'] ?? '');
    $email = bersihkan($_POST['email'] ?? '');
    $alasan = bersihkan($_POST['alasan'] ?? '');

    if (!$nama || !$nis || !$kelas || !$no_hp || !$alasan) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Semua field wajib diisi!'];
        redirect('reset_password.php');
    }

    // Validasi NIS cocok dengan akun
    if ($nis !== $user['nis']) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'NIS tidak sesuai dengan data akun Anda!'];
        redirect('reset_password.php');
    }

    insert("INSERT INTO password_reset_requests (user_id, nama, nis, kelas, no_hp, email, alasan) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$userId, $nama, $nis, $kelas, $no_hp, $email, $alasan]);

    catat_aktivitas($userId, 'Permintaan Reset Password', 'Mengajukan permintaan reset password');
    buat_notifikasi(0, "Permintaan Reset Password", "$nama mengajukan permintaan reset password.", "../admin/index.php?page=password-reset", "reset_password");
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Permintaan Berhasil Dikirim', 'text' => 'Permintaan reset password Anda telah berhasil dikirim. Silakan tunggu Admin memproses permintaan Anda.'];
    redirect('reset_password.php');
}

// Ambil riwayat permintaan user
$requests = query("SELECT * FROM password_reset_requests WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$title = 'Reset Password';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>

    <div class="container-fluid px-4 py-4">
        <!-- Page Header -->
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
        </div>

        <?php if (isset($_SESSION['swal'])): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $_SESSION['swal']['icon'] ?>',
                title: '<?= $_SESSION['swal']['title'] ?>',
                text: '<?= $_SESSION['swal']['text'] ?>',
                confirmButtonText: 'OK'
            });
        });
        </script>
        <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>

        <?php if (tampilkan_alert()) echo tampilkan_alert(); ?>

        <div class="row g-4">
            <!-- Form Permintaan -->
            <div class="col-lg-5">
                <div class="glass-card" data-aos="fade-up">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-key text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Ajukan Reset Password</h6>
                    </div>
                    <p class="text-muted small mb-3">Isi data di bawah untuk mengajukan reset password. Pastikan NIS sesuai dengan data akun Anda.</p>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= $user['nama_lengkap'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS Anda" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" value="<?= $user['kelas'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= $user['no_hp'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <small class="text-muted">(Opsional)</small></label>
                            <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan Reset Password</label>
                            <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Saya lupa password akun saya." required></textarea>
                        </div>

                        <button type="submit" name="kirim" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Permintaan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Riwayat Permintaan -->
            <div class="col-lg-7">
                <div class="glass-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-history text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Riwayat Permintaan</h6>
                    </div>

                    <?php if (mysqli_num_rows($requests) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Catatan Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($r = fetch($requests)): ?>
                                <?php
                                $statusClass = match ($r['status']) {
                                    'Menunggu' => 'bg-warning text-dark',
                                    'Diproses' => 'bg-info text-white',
                                    'Selesai' => 'bg-success text-white',
                                    'Ditolak' => 'bg-danger text-white',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><small><?= tgl_indonesia($r['created_at'], true) ?></small></td>
                                    <td><?= potong_teks($r['alasan'], 40) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= $r['status'] ?></span></td>
                                    <td><small class="text-muted"><?= $r['catatan'] ?: '-' ?></small></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p class="mb-0">Belum ada permintaan reset password</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
