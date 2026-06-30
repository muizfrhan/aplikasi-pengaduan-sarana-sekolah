<?php
// ============================================================
// Halaman Login
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once 'config/database.php';

$brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'guru') {
        redirect('guru/index.php');
    } elseif ($_SESSION['role'] === 'admin') {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

$error = '';
$captcha_error = '';

// Generate captcha hanya saat GET (pertama kali)
// Saat POST gunakan nilai yang sudah tersimpan di session
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $angka1 = rand(1, 20);
    $angka2 = rand(1, 20);
    $hasil = 0;
    $operator = ['+', '-', '*'][array_rand(['+', '-', '*'])];
    switch ($operator) {
        case '+': $hasil = $angka1 + $angka2; break;
        case '-': $hasil = $angka1 - $angka2; break;
        case '*': $hasil = $angka1 * $angka2; break;
    }
    $_SESSION['captcha_hasil'] = $hasil;
    $_SESSION['captcha_soal'] = "$angka1 $operator $angka2";
}
$captcha_soal = $_SESSION['captcha_soal'] ?? '1 + 1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = bersihkan($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha_jawab = (int)($_POST['captcha'] ?? 0);
    $remember = isset($_POST['remember']);
    
    // Validasi captcha
    if ($captcha_jawab !== (int)$_SESSION['captcha_hasil']) {
        $captcha_error = 'Jawaban captcha salah!';
        // Generate new captcha
        $angka1 = rand(1, 20);
        $angka2 = rand(1, 20);
        $hasil = 0;
        $operator = ['+', '-', '*'][array_rand(['+', '-', '*'])];
        switch ($operator) {
            case '+': $hasil = $angka1 + $angka2; break;
            case '-': $hasil = $angka1 - $angka2; break;
            case '*': $hasil = $angka1 * $angka2; break;
        }
        $_SESSION['captcha_hasil'] = $hasil;
        $_SESSION['captcha_soal'] = "$angka1 $operator $angka2";
        $captcha_soal = "$angka1 $operator $angka2";
    } else {
        // Cek user
        $sql = "SELECT * FROM users WHERE username = ? AND is_active = 'Y'";
        $result = query($sql, [$username]);
        $user = fetch($result);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['foto'] = $user['foto'];
            
            // Remember me
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/');
                // Simpan token ke session
                $_SESSION['remember_token'] = $token;
            }
            
            // Catat aktivitas
            catat_aktivitas($user['id'], 'Login', 'User login ke sistem');
            
            // Redirect sesuai role
            if ($user['role'] === 'guru') {
                redirect('guru/index.php');
            } elseif ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('user/index.php');
            }
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= $brand['nama_website'] ?? get_setting('nama_aplikasi') ?></title>
    <link rel="icon" type="image/x-icon"
        href="<?= $brand && $brand['favicon'] ? 'assets/img/'.$brand['favicon'] : 'assets/img/favicon.ico' ?>">
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

    .login-wrapper {
        width: 100%;
        max-width: 420px;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 40px 30px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .login-logo {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-logo i {
        font-size: 48px;
        color: #2563EB;
        background: linear-gradient(135deg, #2563EB, #38BDF8);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .login-logo h4 {
        font-weight: 800;
        color: #0F172A;
        margin-top: 15px;
        font-family: 'Porkys', 'Inter', sans-serif;
        letter-spacing: 1px;
    }

    .login-logo p {
        color: #64748B;
        font-size: 14px;
    }

    .form-control {
        border-radius: 12px;
        padding: 12px 16px;
        border: 2px solid #E2E8F0;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .input-group-text {
        border-radius: 12px;
        border: 2px solid #E2E8F0;
        background: #F8FAFC;
    }

    .btn-login {
        background: linear-gradient(135deg, #2563EB, #38BDF8);
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        color: white;
        transition: all 0.3s;
        width: 100%;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
    }

    .captcha-box {
        background: linear-gradient(135deg, #F1F5F9, #E2E8F0);
        border-radius: 12px;
        padding: 12px 20px;
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: 3px;
        color: #0F172A;
        user-select: none;
    }

    .form-check-input:checked {
        background-color: #2563EB;
        border-color: #2563EB;
    }

    .back-link {
        text-align: center;
        margin-top: 20px;
    }

    .back-link a {
        color: #64748B;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s;
    }

    .back-link a:hover {
        color: #2563EB;
    }

    .password-toggle {
        cursor: pointer;
    }

    @media (max-width: 575.98px) {
        .login-card {
            padding: 30px 20px;
        }

        .login-logo i {
            font-size: 36px;
        }

        .login-logo h4 {
            font-size: 1.2rem;
        }

        .captcha-box {
            font-size: 16px;
            padding: 10px 16px;
        }

        .btn-login {
            padding: 10px;
        }

        .form-control {
            padding: 10px 14px;
        }
    }

    @media (max-width: 374.98px) {
        .login-card {
            padding: 24px 16px;
        }

        .login-wrapper {
            max-width: 100%;
        }
    }
    </style>
</head>

<body>
    <div class="login-wrapper" data-aos="fade-up">
        <div class="login-card">
            <div class="login-logo">
                <?php if ($brand && $brand['logo']): ?>
                <img src="assets/img/<?= $brand['logo'] ?>" alt="<?= $brand['nama_website'] ?>"
                    style="height:56px;width:auto;margin-bottom:8px;object-fit:contain;">
                <?php else: ?>
                <i class="fas fa-school"></i>
                <?php endif; ?>
                <h4><?= $brand['nama_website'] ?? get_setting('nama_aplikasi') ?></h4>
                <?php if ($brand && $brand['tagline']): ?>
                <p><?= $brand['tagline'] ?></p>
                <?php else: ?>
                <p>Silakan login untuk melanjutkan</p>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($captcha_error): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-calculator me-2"></i><?= $captcha_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-user me-1"></i>Username
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username"
                            required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-lock me-1"></i>Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Masukkan password" required>
                        <span class="input-group-text password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calculator me-1"></i>Captcha
                    </label>
                    <div class="captcha-box mb-2">
                        <?= $captcha_soal ?> = ?
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-question"></i></span>
                        <input type="number" name="captcha" class="form-control" placeholder="Masukkan jawaban"
                            required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember Me
                        </label>
                    </div>
                    <!-- <a href="forgot_password.php" class="text-decoration-none" style="color: #2563EB; font-size: 14px;">
                        <i class="fas fa-key me-1"></i>Lupa Password?
                    </a> -->
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>

            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda</a>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/sweetalert2.min.js"></script>
    <script src="assets/vendor/aos.js"></script>
    <script>
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    function togglePassword() {
        const password = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>

</html>