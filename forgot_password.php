<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

if (isset($_POST['kirim'])) {
    if (!validasi_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Token CSRF tidak valid!'];
        redirect('forgot_password.php');
    }

    $nama = bersihkan($_POST['nama_lengkap'] ?? '');
    $nis = bersihkan($_POST['nis'] ?? '');
    $kelas = bersihkan($_POST['kelas'] ?? '');
    $no_hp = bersihkan($_POST['no_hp'] ?? '');
    $email = bersihkan($_POST['email'] ?? '');
    $alasan = bersihkan($_POST['alasan'] ?? '');

    if (!$nama || !$nis || !$kelas || !$no_hp || !$alasan) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Semua field wajib diisi!'];
        redirect('forgot_password.php');
    }

    // Cari user berdasarkan NIS
    $user = fetch(query("SELECT * FROM users WHERE nis = ? AND is_active = 'Y'", [$nis]));

    if (!$user) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Data tidak ditemukan! Pastikan NIS Anda terdaftar di sistem.'];
        redirect('forgot_password.php');
    }

    // Validasi nama lengkap cocok
    if (strtolower($user['nama_lengkap']) !== strtolower($nama)) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Nama lengkap tidak sesuai dengan data NIS tersebut!'];
        redirect('forgot_password.php');
    }

    // Validasi kelas
    if (strtolower($user['kelas']) !== strtolower($kelas)) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Permintaan Gagal', 'text' => 'Kelas tidak sesuai dengan data akun!'];
        redirect('forgot_password.php');
    }

    // Cek apakah sudah ada permintaan pending
    $pending = fetch(query("SELECT COUNT(*) as total FROM password_reset_requests WHERE user_id = ? AND status IN ('Menunggu','Diproses')", [$user['id']]));
    if ($pending['total'] > 0) {
        $_SESSION['swal'] = ['icon' => 'warning', 'title' => 'Sudah Ada Permintaan', 'text' => 'Anda masih memiliki permintaan reset password yang sedang diproses. Silakan tunggu atau hubungi Admin.'];
        redirect('forgot_password.php');
    }

    // Buat permintaan
    insert("INSERT INTO password_reset_requests (user_id, nama, nis, kelas, no_hp, email, alasan) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$user['id'], $nama, $nis, $kelas, $no_hp, $email, $alasan]);

    catat_aktivitas((int)$user['id'], 'Permintaan Reset Password', 'Mengajukan permintaan reset password via lupa password');
    buat_notifikasi(0, "Permintaan Reset Password", "$nama mengajukan permintaan reset password.", "admin/index.php?page=password-reset", "reset_password");
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Permintaan Berhasil Dikirim', 'text' => 'Permintaan reset password Anda telah berhasil dikirim. Silakan tunggu Admin memproses permintaan Anda.'];
    redirect('forgot_password.php');
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - <?= get_setting('nama_aplikasi') ?></title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link href="assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-all.min.css" rel="stylesheet">
    <link href="assets/vendor/aos.css" rel="stylesheet">
    <link href="assets/vendor/sweetalert2-bootstrap-4.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
            padding: 20px;
        }
        .forgot-wrapper {
            width: 100%;
            max-width: 500px;
        }
        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="forgot-wrapper" data-aos="fade-up">
        <div class="forgot-card">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-key" style="font-size:48px;background:linear-gradient(135deg,#2563EB,#38BDF8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"></i>
                </div>
                <h4 class="fw-bold" style="font-family:'Porkys','Inter',sans-serif;letter-spacing:1px;">Lupa Password</h4>
                <p class="text-muted" style="font-size:14px;">Masukkan data Anda untuk mengajukan reset password</p>
            </div>

            <?php if (isset($_SESSION['swal'])): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?= $_SESSION['swal']['icon'] ?>',
                    title: '<?= $_SESSION['swal']['title'] ?>',
                    text: '<?= $_SESSION['swal']['text'] ?>',
                    confirmButtonText: 'OK'
                }).then(function() {
                    <?php if ($_SESSION['swal']['icon'] === 'success'): ?>
                    window.location.href = 'login.php';
                    <?php endif; ?>
                });
            });
            </script>
            <?php unset($_SESSION['swal']); ?>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">NIS <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                        <input type="text" name="kelas" class="form-control" placeholder="Contoh: XII RPL 1" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">No HP <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email <small class="text-muted">(Opsional)</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Alamat email (jika ada)">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alasan Reset Password <span class="text-danger">*</span></label>
                    <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Saya lupa password akun saya." required></textarea>
                </div>

                <button type="submit" name="kirim" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Permintaan
                </button>

                <div class="text-center">
                    <a href="login.php" class="text-decoration-none" style="color: #64748B; font-size: 14px;">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/sweetalert2.min.js"></script>
    <script src="assets/vendor/aos.js"></script>
    <script>AOS.init({duration:800});</script>
</body>
</html>
