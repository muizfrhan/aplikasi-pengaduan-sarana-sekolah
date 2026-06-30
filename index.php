<?php
session_start();
require_once 'config/database.php';

$setting = get_setting();
$landingSet = fetch(query("SELECT * FROM landing_setting WHERE id = 1"));
$hero = fetch(query("SELECT * FROM landing_hero WHERE id = 1 AND status='tampil'"));
$aboutList = fetchAll(query("SELECT * FROM landing_about WHERE status='tampil' ORDER BY urutan ASC"));
$steps = fetchAll(query("SELECT * FROM landing_steps WHERE status='tampil' ORDER BY urutan ASC"));
$stats = fetchAll(query("SELECT * FROM landing_statistics WHERE status='tampil' ORDER BY urutan ASC"));
$faqs = fetchAll(query("SELECT * FROM landing_faq WHERE status='tampil' ORDER BY urutan ASC"));
$footerData = fetch(query("SELECT * FROM landing_footer WHERE id = 1"));
$brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
$testimonials = fetchAll(query("SELECT * FROM testimonials WHERE status='approved' ORDER BY created_at DESC"));
// Fallback to old table if no data
if (count($testimonials) === 0) {
    $testimonials = fetchAll(query("SELECT * FROM testimoni WHERE status='tampil' ORDER BY created_at DESC"));
}

// ============================================================
// Auto-calculate stat value based on tipe
// ============================================================
function hitungStat(string $tipe, string $fallback = '0'): string {
    $angka = match ($tipe) {
        'pengaduan:all'     => hitung('pengaduan'),
        'pengaduan:menunggu' => hitung('pengaduan', "status='menunggu'"),
        'pengaduan:diproses' => hitung('pengaduan', "status='diproses'"),
        'pengaduan:selesai'  => hitung('pengaduan', "status='selesai'"),
        'pengaduan:ditolak'  => hitung('pengaduan', "status='ditolak'"),
        'ruangan:all'       => hitung('ruangan'),
        'kategori:all'      => hitung('kategori'),
        'users:all'         => hitung('users'),
        'users:user'        => hitung('users', "role='user'"),
        'users:admin'       => hitung('users', "role='admin'"),
        'manual'            => $fallback,
        default             => $fallback,
    };
    return (string)$angka;
}

$totalPengaduan = hitung('pengaduan');
$selesai = hitung('pengaduan', "status='selesai'");
$diproses = hitung('pengaduan', "status='diproses'");
$totalUser = hitung('users', "role='user'");
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $brand['nama_lengkap'] ?: ($landingSet['nama_website'] ?? $setting['nama_aplikasi']) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $brand && $brand['favicon'] ? 'assets/img/'.$brand['favicon'] : ($landingSet['favicon'] ? 'assets/img/landing/'.$landingSet['favicon'] : 'assets/img/favicon.ico') ?>">
    <link href="assets/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-all.min.css" rel="stylesheet">
    <link href="assets/vendor/aos.css" rel="stylesheet">
    <link href="assets/vendor/sweetalert2-bootstrap-4.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-title span { background: linear-gradient(135deg, #2563EB, #38BDF8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .section-title span { color: #2563EB; }
    </style>
</head>
<body class="landing-page">
    <div id="loading-screen">
        <div class="loader">
            <div class="loader-spinner"></div>
            <div class="loader-text">Memuat...</div>
        </div>
    </div>

    <button id="back-to-top" class="btn btn-primary rounded-circle shadow">
        <i class="fas fa-arrow-up"></i>
    </button>

    <nav class="navbar navbar-landing fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <?php if ($brand && $brand['logo']): ?>
                <img src="assets/img/<?= $brand['logo'] ?>" alt="<?= $brand['nama_website'] ?>" class="navbar-logo me-2">
                <?php elseif ($hero && $hero['logo']): ?>
                <img src="assets/img/landing/<?= $hero['logo'] ?>" alt="Logo" class="navbar-logo me-2">
                <?php else: ?>
                <i class="fas fa-school text-primary me-2"></i>
                <?php endif; ?>
                <span class="navbar-brand-text"><?= $brand['nama_website'] ?? 'APSS' ?></span>
            </a>

            <!-- Desktop Menu -->
            <div class="d-none d-lg-flex align-items-center gap-1">
                <a class="nav-link active" href="#home" aria-current="page">Home</a>
                <a class="nav-link" href="#tentang">Tentang</a>
                <a class="nav-link" href="#fitur">Fitur</a>
                <a class="nav-link" href="#demo">Demo</a>
                <a class="nav-link" href="#statistik">Statistik</a>
                <a class="nav-link" href="#testimoni">Testimoni</a>
                <a class="nav-link" href="#faq">FAQ</a>
                <a class="nav-link" href="#kontak">Kontak</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') ? 'admin/index.php' : 'user/index.php' ?>" class="btn btn-primary-modern btn-sm ms-2">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary-modern btn-sm ms-2">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Actions -->
            <div class="d-flex d-lg-none align-items-center gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') ? 'admin/index.php' : 'user/index.php' ?>" class="btn btn-primary-modern mobile-login-btn">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary-modern mobile-login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
                <?php endif; ?>
                <button class="hamburger-btn" id="hamburgerBtn" aria-label="Buka menu navigasi" aria-expanded="false">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
            </div>

        </div>
    </nav>

    <!-- Premium Mobile Menu (outside navbar for correct z-index stacking) -->
    <div class="premium-menu" id="premiumMenu" aria-hidden="true">
        <div class="premium-menu-overlay" id="premiumOverlay"></div>
        <aside class="premium-menu-panel" id="premiumPanel" role="dialog" aria-modal="true" aria-label="Menu navigasi">
            <div class="menu-header">
                <div class="menu-brand">
                    <div class="menu-logo">
                        <?php if ($brand && $brand['logo']): ?>
                        <img src="assets/img/<?= $brand['logo'] ?>" alt="<?= $brand['nama_website'] ?>" style="width:44px;height:44px;object-fit:contain;border-radius:14px;">
                        <?php else: ?>
                        <i class="fas fa-school"></i>
                        <?php endif; ?>
                    </div>
                    <div class="menu-brand-text">
                        <span class="menu-brand-name"><?= $brand['nama_website'] ?? 'APSS' ?></span>
                        <span class="menu-brand-desc"><?= $brand['nama_lengkap'] ?? 'Aplikasi Pengaduan Sarana Sekolah' ?></span>
                    </div>
                </div>
                <span class="menu-badge"><?= $brand['versi'] ? 'v' . $brand['versi'] : 'v1.0.0' ?></span>
            </div>
            <div class="menu-divider"></div>
            <div class="menu-profile">
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="profile-avatar">
                    <?php if (!empty($_SESSION['foto']) && file_exists('assets/upload/'.$_SESSION['foto'])): ?>
                    <img src="assets/upload/<?= $_SESSION['foto'] ?>" alt="Foto Profil">
                    <?php else: ?>
                    <div class="profile-avatar-fallback"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                    <span class="profile-status online"></span>
                </div>
                <div class="profile-info">
                    <span class="profile-name"><?= $_SESSION['nama_lengkap'] ?></span>
                    <span class="profile-role"><?= ucfirst($_SESSION['role']) ?></span>
                </div>
                <?php else: ?>
                <div class="profile-avatar">
                    <div class="profile-avatar-fallback guest"><i class="fas fa-user"></i></div>
                </div>
                <div class="profile-info">
                    <span class="profile-name">Guest</span>
                    <span class="profile-role">Silakan login untuk mengakses dashboard</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="menu-divider"></div>
            <nav class="menu-nav" aria-label="Menu navigasi utama">
                <a class="menu-item active" href="#home" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-home"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Home</span>
                        <span class="menu-item-desc">Kembali ke halaman utama</span>
                    </span>
                </a>
                <a class="menu-item" href="#tentang" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-info-circle"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Tentang</span>
                        <span class="menu-item-desc">Informasi tentang APSS</span>
                    </span>
                </a>
                <a class="menu-item" href="#fitur" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Fitur</span>
                        <span class="menu-item-desc">Fitur unggulan APSS</span>
                    </span>
                </a>
                <a class="menu-item" href="#demo" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-play-circle"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Demo Aplikasi</span>
                        <span class="menu-item-desc">Lihat cara kerja APSS</span>
                    </span>
                </a>
                <a class="menu-item" href="#statistik" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-chart-bar"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Statistik</span>
                        <span class="menu-item-desc">Data pengaduan sekolah</span>
                    </span>
                </a>
                <a class="menu-item" href="#testimoni" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-comment-dots"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Testimoni</span>
                        <span class="menu-item-desc">Apa kata mereka tentang APSS</span>
                    </span>
                </a>
                <a class="menu-item" href="#faq" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-question-circle"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">FAQ</span>
                        <span class="menu-item-desc">Pertanyaan yang sering diajukan</span>
                    </span>
                </a>
                <a class="menu-item" href="#kontak" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-envelope"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Kontak</span>
                        <span class="menu-item-desc">Hubungi kami</span>
                    </span>
                </a>
            </nav>
            <div class="menu-divider"></div>
            <div class="menu-cta">
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') ? 'admin/index.php' : 'user/index.php' ?>" class="menu-cta-btn gradient" data-nav>
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <?php else: ?>
                <a href="login.php" class="menu-cta-btn gradient" data-nav>
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                <?php endif; ?>
            </div>
            <div class="menu-footer">
                <span>v1.0.0</span>
                <span>&copy; 2026 APSS</span>
                <span>Powered by SMK RPL</span>
            </div>
        </aside>
    </div>

    <!-- Hero Section -->
    <?php if ($hero): ?>
    <section id="home" class="hero-section" <?= $hero['bg_image'] ? "style='background:linear-gradient(135deg, rgba(248,250,252,0.9), rgba(226,232,240,0.9)), url(assets/img/landing/{$hero['bg_image']});background-size:cover;background-position:center'" : '' ?>>
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="hero-title"><?= nl2br($hero['judul']) ?></h1>
                    <?php if ($hero['subtitle']): ?>
                    <p class="hero-subtitle"><?= $hero['subtitle'] ?></p>
                    <?php endif; ?>
                    <div class="hero-buttons d-flex gap-3 flex-wrap">
                        <?php if ($hero['button1_teks']): ?>
                        <a href="<?= $hero['button1_link'] ?>" class="btn btn-primary-modern">
                            <i class="fas fa-plus-circle me-2"></i><?= $hero['button1_teks'] ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($hero['button2_teks']): ?>
                        <a href="<?= $hero['button2_link'] ?>" class="btn btn-outline-modern">
                            <i class="fas fa-info-circle me-2"></i><?= $hero['button2_teks'] ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image text-center position-relative">
                        <?php if ($hero['ilustrasi']): ?>
                        <img src="assets/img/landing/<?= $hero['ilustrasi'] ?>" alt="Ilustrasi" class="img-fluid rounded-4 shadow-lg">
                        <?php else: ?>
                        <i class="fas fa-school school-illustration"></i>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- About Section -->
    <?php if ($aboutList): ?>
    <section id="tentang" class="section-padding" style="background: white;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">Tentang <span>APSS</span></h2>
                <p class="section-subtitle">Solusi cerdas untuk pengelolaan pengaduan sarana sekolah</p>
            </div>
            <div class="row g-4">
                <?php foreach ($aboutList as $i => $a): ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <?php if ($a['gambar']): ?>
                            <img src="assets/img/landing/<?= $a['gambar'] ?>" alt="" loading="lazy" style="width:30px;height:30px;object-fit:cover;border-radius:8px">
                            <?php else: ?>
                            <i class="<?= $a['icon'] ?>"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold mb-2"><?= $a['judul'] ?></h5>
                        <p class="text-muted mb-0"><?= $a['deskripsi'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Fitur Section -->
    <?php if ($steps): ?>
    <section id="fitur" class="section-padding" style="background: #F8FAFC;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">Fitur <span>Utama</span></h2>
                <p class="section-subtitle">Fitur unggulan yang memudahkan pengelolaan pengaduan sarana sekolah</p>
            </div>
            <div class="row g-4 justify-content-center steps-row">
                <?php foreach ($steps as $i => $s): ?>
                <div class="col-12 col-sm-6 col-lg" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                    <div class="step-card">
                        <div class="step-number" style="background:<?= $s['warna_card'] ?>"><?= $s['nomor'] ?></div>
                        <div class="feature-icon mx-auto" style="background:<?= $s['warna_card'] ?>15;color:<?= $s['warna_card'] ?>">
                            <?php if ($s['gambar']): ?>
                            <img src="assets/img/landing/<?= $s['gambar'] ?>" alt="" loading="lazy" style="width:24px;height:24px;object-fit:cover;border-radius:6px">
                            <?php else: ?>
                            <i class="<?= $s['icon'] ?>"></i>
                            <?php endif; ?>
                        </div>
                        <h6 class="fw-bold"><?= $s['judul'] ?></h6>
                        <small class="text-muted"><?= $s['deskripsi'] ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>



    <!-- Demo Aplikasi Section -->
    <section id="demo" class="section-padding demo-section position-relative overflow-hidden">
        <!-- Animated Background -->
        <div class="demo-bg-layer">
            <div class="demo-blob demo-blob-1"></div>
            <div class="demo-blob demo-blob-2"></div>
            <div class="demo-blob demo-blob-3"></div>
            <div class="demo-grid-pattern"></div>
            <div class="demo-glow-circle"></div>
            <div class="demo-particle demo-particle-1"><i class="fas fa-circle"></i></div>
            <div class="demo-particle demo-particle-2"><i class="fas fa-circle"></i></div>
            <div class="demo-particle demo-particle-3"><i class="fas fa-circle"></i></div>
            <div class="demo-particle demo-particle-4"><i class="fas fa-circle"></i></div>
            <div class="demo-particle demo-particle-5"><i class="fas fa-circle"></i></div>
            <div class="demo-float-shape demo-shape-1"><i class="fas fa-shield-halved"></i></div>
            <div class="demo-float-shape demo-shape-2"><i class="fas fa-bolt"></i></div>
            <div class="demo-float-shape demo-shape-3"><i class="fas fa-gear"></i></div>
        </div>

        <div class="container position-relative" style="z-index:2">
            <!-- Header -->
            <div class="text-center mb-5" data-demo-aos="fade-up">
                <span class="demo-badge"><i class="fas fa-rocket me-1"></i>Demo Aplikasi</span>
                <h2 class="section-title"><span>Demo</span> Aplikasi</h2>
                <p class="section-subtitle">Lihat bagaimana Aplikasi Pengaduan Sarana Sekolah membantu siswa melaporkan kerusakan sarana dengan cepat, mudah, dan transparan.</p>
            </div>

            <div class="row g-5 align-items-start">
                <!-- Left Column: Timeline -->
                <div class="col-lg-6" data-demo-aos="fade-right">
                    <div class="demo-timeline-wrap">
                        <?php
                        $stepsData = [
                            ['icon' => 'fa-lock', 'title' => 'Login ke Sistem', 'desc' => 'Masuk menggunakan akun yang telah diberikan oleh sekolah.', 'color' => '#6366F1'],
                            ['icon' => 'fa-pen-to-square', 'title' => 'Isi Form Pengaduan', 'desc' => 'Lengkapi formulir pengaduan dengan kategori, ruangan, judul, deskripsi, dan foto pendukung.', 'color' => '#8B5CF6'],
                            ['icon' => 'fa-paper-plane', 'title' => 'Pengaduan Dikirim', 'desc' => 'Laporan tersimpan ke sistem dan langsung diteruskan kepada Admin dan Guru.', 'color' => '#06B6D4'],
                            ['icon' => 'fa-check-double', 'title' => 'Verifikasi Admin/Guru', 'desc' => 'Admin atau Guru akan memeriksa laporan, memberikan tanggapan, dan memperbarui status pengaduan.', 'color' => '#10B981'],
                            ['icon' => 'fa-chart-line', 'title' => 'Status Dipantau', 'desc' => 'Siswa dapat memantau perkembangan pengaduan secara realtime melalui dashboard.', 'color' => '#F59E0B'],
                            ['icon' => 'fa-flag-checkered', 'title' => 'Pengaduan Selesai', 'desc' => 'Setelah masalah selesai ditangani, status berubah menjadi "Selesai" dan riwayat tetap tersimpan.', 'color' => '#EF4444']
                        ];
                        $totalSteps = count($stepsData);
                        foreach ($stepsData as $i => $s):
                            $num = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                            $isLast = $i === $totalSteps - 1;
                        ?>
                        <div class="demo-step <?= $i === 0 ? 'demo-step-active' : '' ?>" data-step="<?= $i ?>" role="button" tabindex="0" aria-label="<?= $s['title'] ?>">
                            <div class="demo-step-marker">
                                <div class="demo-step-dot" style="background:<?= $s['color'] ?>;box-shadow:0 0 20px <?= $s['color'] ?>40">
                                    <i class="fas <?= $s['icon'] ?>"></i>
                                </div>
                                <?php if (!$isLast): ?>
                                <div class="demo-step-line"></div>
                                <?php endif; ?>
                            </div>
                            <div class="demo-step-card">
                                <div class="demo-step-num" style="color:<?= $s['color'] ?>"><?= $num ?></div>
                                <h5 class="demo-step-title"><?= $s['title'] ?></h5>
                                <p class="demo-step-desc"><?= $s['desc'] ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right Column: Preview -->
                <div class="col-lg-6" data-demo-aos="fade-left">
                    <div class="demo-preview-wrap" id="demoPreviewWrap">
                        <!-- Preview Card 0: Login -->
                        <div class="demo-preview-card demo-preview-active" data-preview="0">
                            <div class="demo-preview-glow"></div>
                            <div class="demo-preview-header" style="background:linear-gradient(135deg,#6366F1,#8B5CF6)">
                                <div class="demo-preview-header-dots">
                                    <span style="background:#EF4444"></span>
                                    <span style="background:#F59E0B"></span>
                                    <span style="background:#10B981"></span>
                                </div>
                                <span class="demo-preview-header-title"><i class="fas fa-lock me-1"></i>Halaman Login</span>
                            </div>
                            <div class="demo-preview-body">
                                <div class="demo-preview-illust">
                                    <div class="demo-preview-login-card">
                                        <div class="demo-preview-avatar"><i class="fas fa-user-graduate"></i></div>
                                        <div class="demo-preview-field"><span class="demo-preview-field-label">Username</span><span class="demo-preview-field-val">siswa123</span></div>
                                        <div class="demo-preview-field"><span class="demo-preview-field-label">Password</span><span class="demo-preview-field-val">â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢</span></div>
                                        <div class="demo-preview-login-btn" style="background:#6366F1">Masuk</div>
                                    </div>
                                    <div class="demo-preview-badge" style="background:#6366F1;color:#fff">
                                        <i class="fas fa-check-circle me-1"></i>Login Berhasil
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Card 1: Form Pengaduan -->
                        <div class="demo-preview-card" data-preview="1">
                            <div class="demo-preview-glow"></div>
                            <div class="demo-preview-header" style="background:linear-gradient(135deg,#8B5CF6,#A855F7)">
                                <div class="demo-preview-header-dots">
                                    <span style="background:#EF4444"></span>
                                    <span style="background:#F59E0B"></span>
                                    <span style="background:#10B981"></span>
                                </div>
                                <span class="demo-preview-header-title"><i class="fas fa-pen-to-square me-1"></i>Form Pengaduan</span>
                            </div>
                            <div class="demo-preview-body">
                                <div class="demo-preview-illust">
                                    <div class="demo-preview-form-card">
                                        <div class="demo-preview-form-row">
                                            <span class="demo-preview-form-label">Kategori</span>
                                            <span class="demo-preview-form-input">Sarana Kelas</span>
                                        </div>
                                        <div class="demo-preview-form-row">
                                            <span class="demo-preview-form-label">Ruangan</span>
                                            <span class="demo-preview-form-input">Ruang XII RPL 1</span>
                                        </div>
                                        <div class="demo-preview-form-row">
                                            <span class="demo-preview-form-label">Judul</span>
                                            <span class="demo-preview-form-input">Kursi Rusak</span>
                                        </div>
                                        <div class="demo-preview-form-row">
                                            <span class="demo-preview-form-label">Deskripsi</span>
                                            <span class="demo-preview-form-input demo-preview-form-textarea">Kursi bagian dudukan...</span>
                                        </div>
                                        <div class="demo-preview-form-upload">
                                            <i class="fas fa-camera me-1"></i>Upload Foto
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Card 2: Dikirim -->
                        <div class="demo-preview-card" data-preview="2">
                            <div class="demo-preview-glow" style="background:radial-gradient(ellipse at center,rgba(6,182,212,0.3),transparent 70%)"></div>
                            <div class="demo-preview-header" style="background:linear-gradient(135deg,#06B6D4,#22D3EE)">
                                <div class="demo-preview-header-dots">
                                    <span style="background:#EF4444"></span>
                                    <span style="background:#F59E0B"></span>
                                    <span style="background:#10B981"></span>
                                </div>
                                <span class="demo-preview-header-title"><i class="fas fa-paper-plane me-1"></i>Pengaduan Terkirim</span>
                            </div>
                            <div class="demo-preview-body">
                                <div class="demo-preview-illust">
                                    <div class="demo-preview-success-icon" style="color:#06B6D4">
                                        <i class="fas fa-circle-check"></i>
                                    </div>
                                    <h6 class="fw-bold mt-3 mb-1">Pengaduan Berhasil Dikirim!</h6>
                                    <p class="text-muted small mb-3">Laporan Anda telah tercatat di sistem</p>
                                    <div class="demo-preview-status-badge" style="background:#FEF3C7;color:#92400E">
                                        <i class="fas fa-clock me-1"></i>Menunggu
                                    </div>
                                    <div class="demo-preview-timeline-mini mt-3">
                                        <div class="demo-preview-tl-item demo-preview-tl-done"><i class="fas fa-check-circle"></i> Dibuat</div>
                                        <div class="demo-preview-tl-item demo-preview-tl-active"><i class="fas fa-spinner fa-spin"></i> Diproses</div>
                                        <div class="demo-preview-tl-item"><i class="far fa-circle"></i> Selesai</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Card 3: Admin/Guru -->
                        <div class="demo-preview-card" data-preview="3">
                            <div class="demo-preview-glow"></div>
                            <div class="demo-preview-header" style="background:linear-gradient(135deg,#10B981,#34D399)">
                                <div class="demo-preview-header-dots">
                                    <span style="background:#EF4444"></span>
                                    <span style="background:#F59E0B"></span>
                                    <span style="background:#10B981"></span>
                                </div>
                                <span class="demo-preview-header-title"><i class="fas fa-check-double me-1"></i>Verifikasi Admin</span>
                            </div>
                            <div class="demo-preview-body">
                                <div class="demo-preview-illust">
                                    <div class="demo-preview-admin-card">
                                        <div class="demo-preview-admin-header">
                                            <i class="fas fa-user-shield"></i>
                                            <span>Admin Panel</span>
                                        </div>
                                        <div class="demo-preview-admin-item">
                                            <span class="demo-preview-admin-name">Kursi Rusak</span>
                                            <span class="demo-preview-admin-action" style="background:#10B981">Terima</span>
                                        </div>
                                        <div class="demo-preview-admin-item">
                                            <span class="demo-preview-admin-name">Meja Pecah</span>
                                            <span class="demo-preview-admin-action" style="background:#EF4444">Tolak</span>
                                        </div>
                                        <div class="demo-preview-admin-item">
                                            <span class="demo-preview-admin-name">AC Mati</span>
                                            <span class="demo-preview-admin-action" style="background:#F59E0B">Proses</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Card 4: Pantau -->
                        <div class="demo-preview-card" data-preview="4">
                            <div class="demo-preview-glow"></div>
                            <div class="demo-preview-header" style="background:linear-gradient(135deg,#F59E0B,#FBBF24)">
                                <div class="demo-preview-header-dots">
                                    <span style="background:#EF4444"></span>
                                    <span style="background:#F59E0B"></span>
                                    <span style="background:#10B981"></span>
                                </div>
                                <span class="demo-preview-header-title"><i class="fas fa-chart-line me-1"></i>Dashboard Pantauan</span>
                            </div>
                            <div class="demo-preview-body">
                                <div class="demo-preview-illust">
                                    <div class="demo-preview-dashboard-mini">
                                        <div class="demo-preview-stat-row">
                                            <div class="demo-preview-stat-card" style="border-left:4px solid #6366F1">
                                                <span class="demo-preview-stat-num">3</span>
                                                <span class="demo-preview-stat-label">Menunggu</span>
                                            </div>
                                            <div class="demo-preview-stat-card" style="border-left:4px solid #F59E0B">
                                                <span class="demo-preview-stat-num">2</span>
                                                <span class="demo-preview-stat-label">Diproses</span>
                                            </div>
                                            <div class="demo-preview-stat-card" style="border-left:4px solid #10B981">
                                                <span class="demo-preview-stat-num">5</span>
                                                <span class="demo-preview-stat-label">Selesai</span>
                                            </div>
                                        </div>
                                        <div class="demo-preview-progress">
                                            <span>Progres: 50%</span>
                                            <div class="demo-preview-progress-bar" style="width:50%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Card 5: Selesai -->
                        <div class="demo-preview-card" data-preview="5">
                            <div class="demo-preview-glow" style="background:radial-gradient(ellipse at center,rgba(239,68,68,0.2),transparent 70%)"></div>
                            <div class="demo-preview-header" style="background:linear-gradient(135deg,#EF4444,#F87171)">
                                <div class="demo-preview-header-dots">
                                    <span style="background:#EF4444"></span>
                                    <span style="background:#F59E0B"></span>
                                    <span style="background:#10B981"></span>
                                </div>
                                <span class="demo-preview-header-title"><i class="fas fa-party-horn me-1"></i>Selesai</span>
                            </div>
                            <div class="demo-preview-body">
                                <div class="demo-preview-illust">
                                    <div class="demo-preview-success-icon" style="color:#10B981;font-size:48px">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <h6 class="fw-bold mt-3 mb-1">Pengaduan Selesai!</h6>
                                    <p class="text-muted small mb-2">Masalah telah ditangani</p>
                                    <div class="demo-preview-status-badge" style="background:#D1FAE5;color:#065F46">
                                        <i class="fas fa-check-circle me-1"></i>Selesai
                                    </div>
                                    <div class="demo-preview-timeline-mini mt-3">
                                        <div class="demo-preview-tl-item demo-preview-tl-done"><i class="fas fa-check-circle"></i> Dibuat</div>
                                        <div class="demo-preview-tl-item demo-preview-tl-done"><i class="fas fa-check-circle"></i> Diproses</div>
                                        <div class="demo-preview-tl-item demo-preview-tl-done"><i class="fas fa-check-circle"></i> Selesai</div>
                                    </div>
                                    <div class="demo-preview-rating mt-2">
                                        <i class="fas fa-star" style="color:#F59E0B"></i>
                                        <i class="fas fa-star" style="color:#F59E0B"></i>
                                        <i class="fas fa-star" style="color:#F59E0B"></i>
                                        <i class="fas fa-star" style="color:#F59E0B"></i>
                                        <i class="fas fa-star" style="color:#F59E0B"></i>
                                        <span class="ms-2 small text-muted">5.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Overlay arrows -->
                        <button class="demo-preview-arrow demo-prev-arrow" aria-label="Previous step" type="button"><i class="fas fa-chevron-left"></i></button>
                        <button class="demo-preview-arrow demo-next-arrow" aria-label="Next step" type="button"><i class="fas fa-chevron-right"></i></button>

                        <!-- Preview Step Indicator -->
                        <div class="demo-preview-indicator">
                            <?php foreach ($stepsData as $i => $s): ?>
                            <span class="demo-preview-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" style="background:<?= $s['color'] ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="text-center mt-5 pt-3" data-demo-aos="fade-up">
                <div class="demo-cta-wrap">
                    <a href="<?= isset($_SESSION['user_id']) ? 'user/index.php' : 'login.php' ?>" class="demo-btn demo-btn-primary">
                        <i class="fas fa-<?= isset($_SESSION['user_id']) ? 'tachometer-alt' : 'sign-in-alt' ?> me-2"></i>
                        Coba Demo
                    </a>
                    <a href="#tentang" class="demo-btn demo-btn-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        Pelajari Fitur
                    </a>
                </div>
            </div>

            <!-- Step Counter -->
            <div class="demo-step-counter text-center mt-4">
                <span class="demo-step-current">01</span>
                <span class="demo-step-separator">/</span>
                <span class="demo-step-total">06</span>
            </div>
        </div>
    </section>

    <style>
    /* ============================================================
       Demo Aplikasi Section â€” Premium Modern Design
       ============================================================ */
    .demo-section {
        background: #F8FAFC;
        padding-top: 6rem;
        padding-bottom: 6rem;
    }
    /* --- Background Layers --- */
    .demo-bg-layer { position:absolute; inset:0; z-index:0; pointer-events:none; overflow:hidden; }
    .demo-blob {
        position:absolute; border-radius:50%; filter:blur(80px); opacity:0.4;
        animation:demoBlobFloat 8s ease-in-out infinite;
    }
    .demo-blob-1 { width:500px; height:500px; background:radial-gradient(circle,#6366F1,#8B5CF6); top:-10%; left:-5%; }
    .demo-blob-2 { width:400px; height:400px; background:radial-gradient(circle,#06B6D4,#10B981); bottom:-5%; right:-3%; animation-delay:-3s; }
    .demo-blob-3 { width:300px; height:300px; background:radial-gradient(circle,#F59E0B,#EF4444); top:40%; left:50%; animation-delay:-6s; opacity:0.25; }
    @keyframes demoBlobFloat {
        0%,100%{transform:translate(0,0)scale(1)}
        33%{transform:translate(30px,-30px)scale(1.05)}
        66%{transform:translate(-20px,20px)scale(0.95)}
    }
    .demo-grid-pattern {
        position:absolute; inset:0;
        background-image:radial-gradient(rgba(99,102,241,0.08) 1px,transparent 1px);
        background-size:40px 40px; opacity:0.5;
    }
    .demo-glow-circle {
        position:absolute; width:600px; height:600px; border-radius:50%;
        background:radial-gradient(circle,rgba(99,102,241,0.08),transparent 70%);
        top:20%; left:30%; animation:demoGlowPulse 4s ease-in-out infinite;
    }
    @keyframes demoGlowPulse {
        0%,100%{transform:scale(1);opacity:0.5}
        50%{transform:scale(1.2);opacity:1}
    }
    .demo-particle {
        position:absolute; font-size:6px; color:rgba(99,102,241,0.2);
        animation:demoParticleFloat 12s linear infinite;
    }
    .demo-particle-1 { top:15%; left:10%; animation-delay:0s; }
    .demo-particle-2 { top:60%; left:85%; animation-delay:-3s; }
    .demo-particle-3 { top:80%; left:20%; animation-delay:-6s; }
    .demo-particle-4 { top:30%; left:75%; animation-delay:-9s; }
    .demo-particle-5 { top:50%; left:5%; animation-delay:-2s; }
    @keyframes demoParticleFloat {
        0%{transform:translateY(0)rotate(0);opacity:0}
        10%{opacity:0.4}
        90%{opacity:0.4}
        100%{transform:translateY(-120px)rotate(360deg);opacity:0}
    }
    .demo-float-shape {
        position:absolute; font-size:20px; opacity:0.1; animation:demoShapeFloat 6s ease-in-out infinite;
    }
    .demo-shape-1 { top:20%; left:5%; color:#6366F1; animation-delay:0s; font-size:28px; }
    .demo-shape-2 { top:70%; right:8%; color:#06B6D4; animation-delay:-2s; font-size:22px; }
    .demo-shape-3 { top:40%; left:90%; color:#F59E0B; animation-delay:-4s; font-size:18px; }
    @keyframes demoShapeFloat {
        0%,100%{transform:translateY(0)rotate(0deg)}
        50%{transform:translateY(-25px)rotate(15deg)}
    }
    /* --- Badge --- */
    .demo-badge {
        display:inline-flex;align-items:center;
        padding:6px 20px; border-radius:50px;
        background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.12));
        color:#6366F1; font-size:14px; font-weight:600;
        backdrop-filter:blur(10px); border:1px solid rgba(99,102,241,0.2);
        margin-bottom:16px;
    }
    /* --- Timeline --- */
    .demo-timeline-wrap { position:relative; padding-left:10px; }
    .demo-step {
        display:flex; gap:20px; position:relative; margin-bottom:0;
        cursor:pointer; transition:all 0.4s cubic-bezier(0.4,0,0.2,1);
        padding:16px 20px; border-radius:20px;
    }
    .demo-step:hover { background:rgba(99,102,241,0.04); }
    .demo-step.demo-step-active .demo-step-card {
        transform:translateX(8px);
    }
    .demo-step.demo-step-active .demo-step-card {
        box-shadow:none;
    }
    .demo-step.demo-step-active .demo-step-dot {
        transform:scale(1.15);
        box-shadow:0 0 0 8px rgba(99,102,241,0.12),0 0 30px rgba(99,102,241,0.25) !important;
    }
    .demo-step:not(:last-child) { padding-bottom:24px; }
    .demo-step-marker {
        display:flex; flex-direction:column; align-items:center; flex-shrink:0;
        width:52px; position:relative;
    }
    .demo-step-dot {
        width:52px; height:52px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-size:18px;
        transition:all 0.4s cubic-bezier(0.4,0,0.2,1);
        position:relative; z-index:2;
    }
    .demo-step-line {
        width:2px; flex:1; min-height:30px;
        background:linear-gradient(180deg,rgba(99,102,241,0.3),rgba(99,102,241,0.08));
        margin:4px 0; position:relative; z-index:1;
    }
    .demo-step:last-child .demo-step-marker { min-height:0; }
    .demo-step-card { flex:1; min-width:0; transition:all 0.4s cubic-bezier(0.4,0,0.2,1); }
    .demo-step-num {
        font-size:32px; font-weight:800; line-height:1; opacity:0.15;
        margin-bottom:2px; font-family:'Inter',system-ui,sans-serif;
        letter-spacing:-1px;
    }
    .demo-step-title {
        font-size:17px; font-weight:700; margin-bottom:4px; color:#1E293B;
    }
    .demo-step-desc {
        font-size:14px; color:#64748B; margin-bottom:0; line-height:1.6; max-width:400px;
    }
    @media (max-width:991.98px) {
        .demo-step-desc { max-width:100%; }
    }
    /* --- Preview Wrap --- */
    .demo-preview-wrap {
        position:relative; min-height:420px;
        background:rgba(255,255,255,0.6);
        backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px);
        border-radius:24px; border:1px solid rgba(255,255,255,0.5);
        box-shadow:0 8px 40px rgba(0,0,0,0.06),0 1px 3px rgba(0,0,0,0.04);
        overflow:hidden; padding:0;
        transition:all 0.4s ease;
    }
    .demo-preview-wrap:hover {
        box-shadow:0 12px 48px rgba(99,102,241,0.12),0 2px 8px rgba(0,0,0,0.06);
        transform:translateY(-4px);
    }
    /* --- Preview Card --- */
    .demo-preview-card {
        position:absolute; inset:0; display:flex; flex-direction:column;
        opacity:0; transform:translateY(12px) scale(0.97);
        transition:all 0.6s cubic-bezier(0.4,0,0.2,1);
        pointer-events:none;
    }
    .demo-preview-card.demo-preview-active {
        opacity:1; transform:translateY(0) scale(1);
        pointer-events:auto; position:relative;
    }
    .demo-preview-glow {
        position:absolute; inset:0;
        background:radial-gradient(ellipse at 50% 0%,rgba(99,102,241,0.12),transparent 70%);
        pointer-events:none; z-index:0;
    }
    .demo-preview-header {
        display:flex; align-items:center; gap:10px;
        padding:14px 20px; color:#fff; font-size:14px; font-weight:600;
        position:relative; z-index:1;
    }
    .demo-preview-header-dots {
        display:flex; gap:6px; align-items:center;
    }
    .demo-preview-header-dots span {
        width:10px; height:10px; border-radius:50%; display:block;
    }
    .demo-preview-header-title { margin-left:auto; }
    .demo-preview-body {
        flex:1; padding:24px; display:flex; align-items:center; justify-content:center;
        position:relative; z-index:1;
    }
    /* --- Preview Illustrations --- */
    .demo-preview-illust {
        width:100%; max-width:320px; margin:0 auto;
        display:flex; flex-direction:column; align-items:center;
    }
    /* Login Card */
    .demo-preview-login-card {
        width:100%; background:#fff; border-radius:16px; padding:20px;
        box-shadow:0 4px 16px rgba(0,0,0,0.06);
    }
    .demo-preview-avatar {
        width:48px; height:48px; border-radius:50%;
        background:linear-gradient(135deg,#6366F1,#8B5CF6); color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-size:20px; margin:0 auto 16px;
    }
    .demo-preview-field {
        display:flex; justify-content:space-between; align-items:center;
        padding:10px 0; border-bottom:1px solid #F1F5F9;
    }
    .demo-preview-field:last-of-type { border-bottom:none; }
    .demo-preview-field-label { font-size:13px; color:#94A3B8; font-weight:500; }
    .demo-preview-field-val { font-size:14px; color:#1E293B; font-weight:600; }
    .demo-preview-login-btn {
        width:100%; margin-top:16px; padding:10px; border-radius:12px;
        color:#fff; font-weight:600; font-size:14px; text-align:center;
        cursor:default;
    }
    .demo-preview-badge {
        display:inline-flex;align-items:center; gap:4px;
        padding:6px 16px; border-radius:50px; font-size:13px; font-weight:600;
        margin-top:12px; animation:demoBadgePop 0.4s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes demoBadgePop {
        0%{transform:scale(0);opacity:0}
        60%{transform:scale(1.1)}
        100%{transform:scale(1);opacity:1}
    }
    /* Form Card */
    .demo-preview-form-card {
        width:100%; background:#fff; border-radius:16px; padding:18px;
        box-shadow:0 4px 16px rgba(0,0,0,0.06);
    }
    .demo-preview-form-row {
        display:flex; justify-content:space-between; align-items:center;
        padding:8px 0; border-bottom:1px solid #F1F5F9;
    }
    .demo-preview-form-row:last-of-type { border-bottom:none; }
    .demo-preview-form-label { font-size:12px; color:#94A3B8; font-weight:500; }
    .demo-preview-form-input {
        font-size:13px; color:#1E293B; font-weight:600;
        background:#F8FAFC; padding:4px 12px; border-radius:8px;
    }
    .demo-preview-form-textarea {
        max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .demo-preview-form-upload {
        margin-top:12px; padding:8px; border:2px dashed #E2E8F0; border-radius:10px;
        text-align:center; font-size:13px; color:#94A3B8; cursor:default;
    }
    /* Success / Status */
    .demo-preview-success-icon {
        font-size:40px; animation:demoSuccessPop 0.6s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes demoSuccessPop {
        0%{transform:scale(0)rotate(-20deg);opacity:0}
        60%{transform:scale(1.15)rotate(3deg)}
        100%{transform:scale(1)rotate(0);opacity:1}
    }
    .demo-preview-status-badge {
        display:inline-flex;align-items:center; gap:4px;
        padding:4px 14px; border-radius:50px; font-size:13px; font-weight:600;
    }
    .demo-preview-timeline-mini {
        width:100%; display:flex; justify-content:center; gap:16px;
    }
    .demo-preview-tl-item {
        display:flex; flex-direction:column; align-items:center; gap:4px;
        font-size:11px; color:#94A3B8; font-weight:500;
    }
    .demo-preview-tl-item i { font-size:16px; }
    .demo-preview-tl-done { color:#10B981; }
    .demo-preview-tl-active { color:#F59E0B; }
    /* Admin Card */
    .demo-preview-admin-card {
        width:100%; background:#fff; border-radius:16px; overflow:hidden;
        box-shadow:0 4px 16px rgba(0,0,0,0.06);
    }
    .demo-preview-admin-header {
        display:flex; align-items:center; gap:8px;
        padding:12px 16px; background:#F8FAFC; font-size:14px; font-weight:600; color:#1E293B;
        border-bottom:1px solid #E2E8F0;
    }
    .demo-preview-admin-item {
        display:flex; justify-content:space-between; align-items:center;
        padding:10px 16px; border-bottom:1px solid #F1F5F9;
    }
    .demo-preview-admin-item:last-child { border-bottom:none; }
    .demo-preview-admin-name { font-size:13px; font-weight:500; color:#1E293B; }
    .demo-preview-admin-action {
        font-size:11px; color:#fff; font-weight:600;
        padding:3px 12px; border-radius:50px; cursor:default;
    }
    /* Dashboard Mini */
    .demo-preview-dashboard-mini { width:100%; }
    .demo-preview-stat-row {
        display:flex; gap:8px; margin-bottom:12px;
    }
    .demo-preview-stat-card {
        flex:1; background:#fff; border-radius:12px; padding:12px;
        box-shadow:0 2px 8px rgba(0,0,0,0.04);
    }
    .demo-preview-stat-num {
        font-size:22px; font-weight:800; color:#1E293B; line-height:1.2;
    }
    .demo-preview-stat-label { font-size:11px; color:#94A3B8; font-weight:500; }
    .demo-preview-progress {
        background:#fff; border-radius:12px; padding:14px;
        font-size:13px; font-weight:600; color:#1E293B;
        box-shadow:0 2px 8px rgba(0,0,0,0.04);
    }
    .demo-preview-progress-bar {
        height:6px; border-radius:3px; background:linear-gradient(90deg,#6366F1,#8B5CF6);
        margin-top:8px; transition:width 0.6s ease;
    }
    .demo-preview-rating { font-size:16px; }
    /* --- Arrows --- */
    .demo-preview-arrow {
        position:absolute; top:50%; transform:translateY(-50%); z-index:5;
        width:36px; height:36px; border-radius:50%;
        border:none; background:rgba(255,255,255,0.9);
        backdrop-filter:blur(8px); box-shadow:0 2px 8px rgba(0,0,0,0.1);
        display:flex; align-items:center; justify-content:center;
        cursor:pointer; transition:all 0.3s ease;
        font-size:14px; color:#1E293B;
    }
    .demo-preview-arrow:hover {
        background:#fff; box-shadow:0 4px 16px rgba(0,0,0,0.15);
        transform:translateY(-50%)scale(1.08);
    }
    .demo-prev-arrow { left:12px; }
    .demo-next-arrow { right:12px; }
    /* --- Pagination Dots --- */
    .demo-preview-indicator {
        display:flex; justify-content:center; gap:8px;
        padding:16px; position:relative; z-index:5;
    }
    .demo-preview-dot {
        width:8px; height:8px; border-radius:50%; opacity:0.3;
        cursor:pointer; transition:all 0.4s cubic-bezier(0.4,0,0.2,1);
    }
    .demo-preview-dot.active { width:28px; border-radius:4px; opacity:1; }
    .demo-preview-dot:hover { opacity:0.6; }
    /* --- Step Counter --- */
    .demo-step-counter {
        font-size:16px; font-weight:700; letter-spacing:2px;
        font-family:'Inter',system-ui,sans-serif;
    }
    .demo-step-current { color:#6366F1; }
    .demo-step-separator { color:#CBD5E1; margin:0 4px; }
    .demo-step-total { color:#94A3B8; }
    /* --- CTA Buttons --- */
    .demo-cta-wrap {
        display:flex; justify-content:center; gap:16px; flex-wrap:wrap;
    }
    .demo-btn {
        display:inline-flex; align-items:center;
        padding:14px 32px; border-radius:16px; font-weight:600; font-size:15px;
        text-decoration:none; transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
        border:none; cursor:pointer;
    }
    .demo-btn-primary {
        background:linear-gradient(135deg,#6366F1,#8B5CF6);
        color:#fff; box-shadow:0 4px 16px rgba(99,102,241,0.3);
    }
    .demo-btn-primary:hover {
        transform:translateY(-2px); box-shadow:0 8px 32px rgba(99,102,241,0.4);
        color:#fff;
    }
    .demo-btn-secondary {
        background:rgba(255,255,255,0.8);
        backdrop-filter:blur(12px); border:1px solid #E2E8F0;
        color:#1E293B;
    }
    .demo-btn-secondary:hover {
        background:#fff; border-color:#CBD5E1;
        transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,0.06);
        color:#1E293B;
    }
    /* --- Responsive --- */
    @media (max-width:991.98px) {
        .demo-preview-wrap { min-height:380px; margin-top:40px; }
        .demo-step { padding:12px 16px; }
        .demo-step-dot { width:44px; height:44px; font-size:16px; }
        .demo-step-marker { width:44px; }
        .demo-step-num { font-size:26px; }
        .demo-step-title { font-size:16px; }
        .demo-step-desc { font-size:13px; }
    }
    @media (max-width:767.98px) {
        .demo-section { padding-top:4rem; padding-bottom:4rem; }
        .demo-preview-wrap { min-height:340px; margin-top:24px; }
        .demo-step { padding:10px 12px; gap:14px; }
        .demo-step-dot { width:40px; height:40px; font-size:14px; }
        .demo-step-marker { width:40px; }
        .demo-step-num { font-size:22px; }
        .demo-step-title { font-size:15px; }
        .demo-step-desc { font-size:12px; }
        .demo-step:not(:last-child) { padding-bottom:16px; }
        .demo-btn { padding:12px 24px; font-size:14px; }
        .demo-cta-wrap { gap:12px; flex-direction:column; align-items:center; }
        .demo-preview-stat-row { flex-direction:column; gap:6px; }
        .demo-preview-timeline-mini { gap:10px; }
        .demo-preview-tl-item { font-size:10px; }
        .demo-preview-arrow { width:30px; height:30px; font-size:12px; }
    }
    @media (max-width:575.98px) {
        .demo-preview-wrap { min-height:300px; border-radius:18px; }
        .demo-preview-body { padding:16px; }
        .demo-preview-illust { max-width:260px; }
        .demo-preview-login-card,
        .demo-preview-form-card,
        .demo-preview-admin-card { padding:14px; }
        .demo-preview-stat-card { padding:10px; }
        .demo-preview-stat-num { font-size:18px; }
    }
    /* --- Dark Mode --- */
    [data-bs-theme="dark"] .demo-section { background:#0F172A; }
    [data-bs-theme="dark"] .demo-grid-pattern { background-image:radial-gradient(rgba(99,102,241,0.06) 1px,transparent 1px); }
    [data-bs-theme="dark"] .demo-preview-wrap {
        background:rgba(30,41,59,0.6);
        border-color:rgba(255,255,255,0.06);
        box-shadow:0 8px 40px rgba(0,0,0,0.3);
    }
    [data-bs-theme="dark"] .demo-preview-wrap:hover {
        box-shadow:0 12px 48px rgba(99,102,241,0.15);
    }
    [data-bs-theme="dark"] .demo-step-title { color:#E2E8F0; }
    [data-bs-theme="dark"] .demo-step-desc { color:#94A3B8; }
    [data-bs-theme="dark"] .demo-step:hover { background:rgba(99,102,241,0.06); }
    [data-bs-theme="dark"] .demo-preview-login-card,
    [data-bs-theme="dark"] .demo-preview-form-card,
    [data-bs-theme="dark"] .demo-preview-admin-card,
    [data-bs-theme="dark"] .demo-preview-stat-card,
    [data-bs-theme="dark"] .demo-preview-progress {
        background:#1E293B; box-shadow:0 4px 16px rgba(0,0,0,0.2);
    }
    [data-bs-theme="dark"] .demo-preview-field { border-color:#334155; }
    [data-bs-theme="dark"] .demo-preview-field-val { color:#E2E8F0; }
    [data-bs-theme="dark"] .demo-preview-form-row { border-color:#334155; }
    [data-bs-theme="dark"] .demo-preview-form-input { background:#334155; color:#E2E8F0; }
    [data-bs-theme="dark"] .demo-preview-form-upload { border-color:#334155; color:#64748B; }
    [data-bs-theme="dark"] .demo-preview-admin-header { background:#0F172A; border-color:#334155; color:#E2E8F0; }
    [data-bs-theme="dark"] .demo-preview-admin-item { border-color:#334155; }
    [data-bs-theme="dark"] .demo-preview-admin-name { color:#E2E8F0; }
    [data-bs-theme="dark"] .demo-preview-stat-num { color:#E2E8F0; }
    [data-bs-theme="dark"] .demo-btn-secondary {
        background:rgba(30,41,59,0.6); border-color:#334155; color:#E2E8F0;
    }
    [data-bs-theme="dark"] .demo-btn-secondary:hover {
        background:#1E293B; border-color:#475569; color:#E2E8F0;
    }
    [data-bs-theme="dark"] .demo-preview-arrow {
        background:rgba(30,41,59,0.8); color:#E2E8F0;
    }
    [data-bs-theme="dark"] .demo-preview-arrow:hover { background:#1E293B; }
    [data-bs-theme="dark"] .demo-badge {
        background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(139,92,246,0.2));
        border-color:rgba(99,102,241,0.3);
    }
    [data-bs-theme="dark"] .demo-step-separator { color:#334155; }
    [data-bs-theme="dark"] .demo-step-total { color:#64748B; }
    </style>
    <!-- Stats Section -->
    <section id="statistik" class="stats-section">
        <div class="container">
            <div class="row text-center g-4">
                <?php if ($stats): ?>
                <?php foreach ($stats as $s):
                    $tipe = $s['tipe'] ?? 'manual';
                    $nilai = hitungStat($tipe, $s['angka']);
                ?>
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
                    <div class="stats-icon-custom" style="background:<?= $s['warna'] ?>20;color:<?= $s['warna'] ?>">
                        <i class="<?= $s['icon'] ?>"></i>
                    </div>
                    <div class="stats-number counter" data-target="<?= $nilai ?>">0</div>
                    <div class="stats-label"><?= $s['nama'] ?></div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
                    <div class="stats-icon-custom" style="background:#2563EB20;color:#2563EB"><i class="fas fa-clipboard-list"></i></div>
                    <div class="stats-number counter" data-target="<?= $totalPengaduan ?>">0</div>
                    <div class="stats-label">Total Pengaduan</div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-icon-custom" style="background:#10B98120;color:#10B981"><i class="fas fa-check-circle"></i></div>
                    <div class="stats-number counter" data-target="<?= $selesai ?>">0</div>
                    <div class="stats-label">Selesai Diproses</div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-icon-custom" style="background:#F59E0B20;color:#F59E0B"><i class="fas fa-spinner"></i></div>
                    <div class="stats-number counter" data-target="<?= $diproses ?>">0</div>
                    <div class="stats-label">Sedang Diproses</div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-icon-custom" style="background:#8B5CF620;color:#8B5CF6"><i class="fas fa-users"></i></div>
                    <div class="stats-number counter" data-target="<?= $totalUser ?>">0</div>
                    <div class="stats-label">Pengguna Aktif</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Testimoni Section -->
    <!-- ============================================================
    TESTIMONI SECTION
    ============================================================ -->
    <?php
    $totalTestimoni = hitung('testimonials', "status='approved'");
    $totalTestimoni += hitung('testimoni', "status='tampil'");
    $avgRating = 0;
    if ($testimonials) {
        $sum = 0; $cnt = 0;
        foreach ($testimonials as $t) { $sum += (int)($t['rating'] ?? 5); $cnt++; }
        $avgRating = $cnt > 0 ? round($sum / $cnt, 1) : 0;
    }
    ?>
    <section id="testimoni" class="testimoni-section position-relative overflow-hidden">
        <div class="testimoni-bg-gradient"></div>

        <!-- Animated Background Shapes -->
        <div class="testimoni-shapes" aria-hidden="true">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>

        <div class="container position-relative" style="z-index:2">

            <!-- Header -->
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="testimoni-badge"><i class="fas fa-star me-1" style="color:#f59e0b"></i> Testimoni Terverifikasi</span>
                <h2 class="testimoni-title">Apa Kata Pengguna <span>Kami?</span></h2>
                <p class="testimoni-subtitle">Lihat pengalaman siswa, guru, dan pengguna setelah menggunakan Aplikasi Pengaduan Sarana Sekolah</p>
            </div>

            <!-- Statistics -->
            <div class="testimoni-stats" data-aos="fade-up" data-aos-delay="50">
                <div class="row g-3 justify-content-center">
                    <div class="col-6 col-md-3">
                        <div class="ts-card">
                            <div class="ts-icon"><i class="fas fa-star"></i></div>
                            <div class="ts-value"><span class="ts-counter" data-target="<?= $avgRating ?>">0</span><small>/5</small></div>
                            <div class="ts-rating"><?php $r = round($avgRating); for ($i=1;$i<=5;$i++): ?><i class="fas fa-star<?= $i<=$r?' active':'' ?>"></i><?php endfor; ?></div>
                            <div class="ts-label">Rata-rata Rating</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ts-card">
                            <div class="ts-icon"><i class="fas fa-users"></i></div>
                            <div class="ts-value"><span class="ts-counter" data-target="<?= $totalUser ?>">0</span></div>
                            <div class="ts-label">Total Pengguna</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ts-card">
                            <div class="ts-icon"><i class="fas fa-comment-dots"></i></div>
                            <div class="ts-value"><span class="ts-counter" data-target="<?= $totalTestimoni ?>">0</span></div>
                            <div class="ts-label">Total Testimoni</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ts-card">
                            <div class="ts-icon"><i class="fas fa-smile"></i></div>
                            <?php $kepuasan = $totalTestimoni > 0 ? round(($totalTestimoni / ($totalTestimoni + 1)) * 100) : 0; ?>
                            <div class="ts-value"><span class="ts-counter" data-target="<?= $kepuasan ?>">0</span><small>%</small></div>
                            <div class="ts-label">Tingkat Kepuasan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="testimoni-toolbar" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-2 align-items-center justify-content-between">
                    <div class="col-md-5 col-lg-4">
                        <div class="ts-search-wrap">
                            <i class="fas fa-search ts-search-icon"></i>
                            <input type="text" class="ts-search-input" id="tsSearch" placeholder="Cari nama, kelas, judul..." aria-label="Cari testimoni">
                        </div>
                    </div>
                    <div class="col-md-7 col-lg-8">
                        <div class="ts-filter-wrap">
                            <button class="ts-filter-btn active" data-filter="all">Semua</button>
                            <button class="ts-filter-btn" data-filter="5"><i class="fas fa-star"></i> 5</button>
                            <button class="ts-filter-btn" data-filter="4"><i class="fas fa-star"></i> 4</button>
                            <button class="ts-filter-btn" data-filter="terbaru"><i class="fas fa-clock"></i> Terbaru</button>
                            <button class="ts-filter-btn" data-filter="terlama"><i class="fas fa-history"></i> Terlama</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skeleton Loading -->
            <div class="testimoni-skeleton-wrap" id="tsSkeleton">
                <div class="row g-4">
                    <?php for ($s = 0; $s < 3; $s++): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="ts-skeleton-card">
                            <div class="ts-skel-shine"></div>
                            <div class="ts-skel-avatar"></div>
                            <div class="ts-skel-line w-50"></div>
                            <div class="ts-skel-line w-75"></div>
                            <div class="ts-skel-line w-25"></div>
                            <div class="ts-skel-line w-100"></div>
                            <div class="ts-skel-line w-60"></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Carousel -->
            <div class="testimoni-carousel-wrap" id="tsCarousel" style="display:none">
                <div class="swiper testimoniSwiper" id="testimoniSwiper">
                    <div class="swiper-wrapper" id="tsWrapper">
                        <?php if ($testimonials): foreach ($testimonials as $t):
                            $nama = $t['nama'] ?? ($t['nama_lengkap'] ?? '');
                            $kelas = $t['kelas'] ?? ($t['jabatan'] ?? '');
                            $isi = $t['isi'] ?? ($t['isi_testimoni'] ?? '');
                            $judul = $t['judul'] ?? '';
                            $rating = (int)($t['rating'] ?? 5);
                            $foto = $t['foto'] ?? '';
                            $foto_testimoni = $t['foto_testimoni'] ?? '';
                            $created = $t['created_at'] ?? '';
                            $fotoPath = 'assets/img/testimoni/foto/' . $foto_testimoni;
                            $fotoExists = $foto_testimoni && file_exists($fotoPath);
                            $avatarPath = '';
                            if ($foto && file_exists("assets/img/" . $foto)) {
                                $avatarPath = "assets/img/" . $foto;
                            }
                        ?>
                        <div class="swiper-slide" data-rating="<?= $rating ?>" data-date="<?= $created ?>">
                            <div class="testimoni-card-modern">
                                <div class="testimoni-card-inner">
                                    <div class="testimoni-card-glow"></div>
                                    <div class="testimoni-card-content">
                                        <?php if ($fotoExists): ?>
                                        <div class="testimoni-featured-img mb-3">
                                            <img src="<?= $fotoPath ?>" alt="Foto testimoni" class="img-fluid" loading="lazy">
                                        </div>
                                        <?php endif; ?>
                                        <div class="testimoni-rating mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i <= $rating ? ' active' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <?php if ($judul): ?>
                                        <h5 class="testimoni-judul"><?= htmlspecialchars($judul) ?></h5>
                                        <?php endif; ?>
                                        <p class="testimoni-isi"><?= htmlspecialchars($isi) ?></p>
                                        <div class="testimoni-footer">
                                            <div class="testimoni-author-row">
                                                <div class="testimoni-avatar-wrap">
                                                    <?php if ($avatarPath): ?>
                                                    <img src="<?= $avatarPath ?>" alt="<?= $nama ?>" class="testimoni-avatar-img" loading="lazy">
                                                    <?php else: ?>
                                                    <div class="testimoni-avatar-placeholder"><?= strtoupper(substr($nama, 0, 1)) ?></div>
                                                    <?php endif; ?>
                                                    <div class="testimoni-verified-badge"><i class="fas fa-check"></i></div>
                                                </div>
                                                <div class="testimoni-author-info">
                                                    <h6 class="testimoni-author-name"><?= htmlspecialchars($nama) ?></h6>
                                                    <span class="testimoni-author-role"><?= htmlspecialchars($kelas ?: 'Siswa') ?></span>
                                                </div>
                                            </div>
                                            <div class="testimoni-date">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?= date('d M Y', strtotime($created)) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <!-- Navigation -->
                    <div class="swiper-button-next testimoni-next-custom" aria-label="Testimoni selanjutnya"></div>
                    <div class="swiper-button-prev testimoni-prev-custom" aria-label="Testimoni sebelumnya"></div>
                    <div class="swiper-pagination testimoni-pagination-custom"></div>
                </div>
            </div>

            <!-- Empty State (via JS) -->
            <div class="testimoni-empty-wrap" id="tsEmpty" style="display:none">
                <div class="testimoni-empty-glass">
                    <div class="testimoni-empty-floating">
                        <div class="testimoni-empty-icon-wrap">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                    </div>
                    <h4 class="testimoni-empty-title">Belum Ada Testimoni</h4>
                    <p class="testimoni-empty-desc">Saat ini belum ada testimoni yang dipublikasikan. Jadilah pengguna pertama yang membagikan pengalaman Anda setelah menggunakan aplikasi ini.</p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="testimoni-empty-btn">
                        <i class="fas fa-pen me-2"></i>Kirim Testimoni
                    </a>
                    <?php else: ?>
                    <a href="user/buat_testimoni.php" class="testimoni-empty-btn">
                        <i class="fas fa-pen me-2"></i>Kirim Testimoni
                    </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <style>
    /* ============================================================
       TESTIMONI SECTION — Premium Redesign
       ============================================================ */
    .testimoni-section {
        padding: 90px 0 80px;
        min-height: 600px;
    }
    .testimoni-bg-gradient {
        position: absolute; inset: 0;
        background: linear-gradient(160deg, #F8FAFC 0%, #EFF6FF 30%, #F0F4FF 60%, #F8FAFC 100%);
        z-index: 0;
    }
    [data-bs-theme="dark"] .testimoni-bg-gradient {
        background: linear-gradient(160deg, #0B1120 0%, #111827 30%, #0F172A 60%, #080E1A 100%);
    }

    /* ---- Animated Shapes ---- */
    .testimoni-shapes { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
    .testimoni-shapes .shape {
        position: absolute; border-radius: 50%;
        filter: blur(60px);
        opacity: 0.15;
        animation: tsFloat 8s ease-in-out infinite;
    }
    .shape-1 { width: 300px; height: 300px; background: #6366F1; top: -80px; left: 10%; }
    .shape-2 { width: 200px; height: 200px; background: #8B5CF6; bottom: 10%; right: 5%; animation-delay: -2s; }
    .shape-3 { width: 150px; height: 150px; background: #3B82F6; top: 40%; left: 5%; animation-delay: -4s; }
    .shape-4 { width: 100px; height: 100px; background: #EC4899; top: 20%; right: 20%; animation-delay: -6s; }
    .shape-5 { width: 80px; height: 80px; background: #F59E0B; bottom: 5%; left: 40%; animation-delay: -1s; }
    @keyframes tsFloat {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-20px) scale(1.05); }
    }
    [data-bs-theme="dark"] .testimoni-shapes .shape { opacity: 0.08; }

    /* ---- Header ---- */
    .testimoni-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 16px 6px 12px;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08));
        border: 1px solid rgba(99,102,241,0.18);
        border-radius: 20px;
        font-size: 0.8rem; font-weight: 600; color: #6366F1;
        margin-bottom: 14px; letter-spacing: 0.3px;
    }
    .testimoni-title {
        font-size: clamp(1.6rem, 4vw, 2.4rem);
        font-weight: 800; color: #0F172A; margin-bottom: 10px;
    }
    .testimoni-title span { background: linear-gradient(135deg, #6366F1, #8B5CF6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .testimoni-subtitle { font-size: clamp(0.9rem, 1.5vw, 1.05rem); color: #64748B; max-width: 540px; margin: 0 auto; }
    [data-bs-theme="dark"] .testimoni-title { color: #F1F5F9; }
    [data-bs-theme="dark"] .testimoni-subtitle { color: #94A3B8; }
    [data-bs-theme="dark"] .testimoni-badge { background: rgba(99,102,241,0.18); border-color: rgba(99,102,241,0.25); color: #A5B4FC; }

    /* ---- Statistics ---- */
    .testimoni-stats { margin-bottom: 40px; }
    .ts-card {
        background: rgba(255,255,255,0.5);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 20px; padding: 22px 16px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.16,1,0.3,1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .ts-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 36px rgba(99,102,241,0.10);
        background: rgba(255,255,255,0.7);
    }
    .ts-icon {
        width: 44px; height: 44px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 10px;
        font-size: 18px;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.12));
        color: #6366F1;
    }
    .ts-value { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: #0F172A; line-height: 1.2; }
    .ts-value small { font-size: 0.6em; font-weight: 600; color: #94A3B8; margin-left: 2px; }
    .ts-rating { margin: 4px 0; display: flex; justify-content: center; gap: 2px; }
    .ts-rating .fa-star { font-size: 11px; color: #E2E8F0; }
    .ts-rating .fa-star.active { color: #F59E0B; }
    .ts-label { font-size: 0.78rem; color: #64748B; font-weight: 500; }
    [data-bs-theme="dark"] .ts-card {
        background: rgba(30,41,59,0.5);
        border-color: rgba(255,255,255,0.06);
    }
    [data-bs-theme="dark"] .ts-card:hover { background: rgba(30,41,59,0.7); }
    [data-bs-theme="dark"] .ts-value { color: #F1F5F9; }
    [data-bs-theme="dark"] .ts-label { color: #94A3B8; }

    /* ---- Toolbar ---- */
    .testimoni-toolbar { margin-bottom: 30px; }
    .ts-search-wrap {
        position: relative;
        background: rgba(255,255,255,0.6);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .ts-search-wrap:focus-within {
        background: #fff;
        border-color: #6366F1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
    }
    .ts-search-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #94A3B8; font-size: 14px;
        pointer-events: none;
    }
    .ts-search-input {
        width: 100%; padding: 10px 14px 10px 40px;
        border: none; background: transparent;
        font-size: 0.9rem; color: #0F172A;
        outline: none;
    }
    .ts-search-input::placeholder { color: #94A3B8; }
    .ts-filter-wrap {
        display: flex; gap: 6px; flex-wrap: wrap;
        background: rgba(255,255,255,0.5);
        backdrop-filter: blur(8px);
        border-radius: 14px; padding: 4px;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .ts-filter-btn {
        padding: 7px 14px; border: none; background: transparent;
        border-radius: 10px; font-size: 0.82rem; font-weight: 600;
        color: #64748B; cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
    }
    .ts-filter-btn i { font-size: 0.7rem; margin-right: 3px; }
    .ts-filter-btn.active {
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        color: #fff;
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }
    .ts-filter-btn:hover:not(.active) { color: #6366F1; background: rgba(99,102,241,0.06); }
    [data-bs-theme="dark"] .ts-search-wrap { background: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .ts-search-wrap:focus-within { background: #1E293B; border-color: #6366F1; }
    [data-bs-theme="dark"] .ts-search-input { color: #F1F5F9; }
    [data-bs-theme="dark"] .ts-filter-wrap { background: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .ts-filter-btn { color: #94A3B8; }
    [data-bs-theme="dark"] .ts-filter-btn:hover:not(.active) { color: #A5B4FC; }

    /* ---- Skeleton ---- */
    .testimoni-skeleton-wrap { margin-bottom: 20px; }
    .ts-skeleton-card {
        position: relative; overflow: hidden;
        background: rgba(255,255,255,0.5);
        backdrop-filter: blur(8px);
        border-radius: 24px; padding: 28px;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .ts-skel-shine {
        position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: tsShimmer 1.8s infinite;
    }
    @keyframes tsShimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .ts-skel-avatar {
        width: 48px; height: 48px; border-radius: 50%;
        background: rgba(0,0,0,0.06); margin-bottom: 16px;
    }
    .ts-skel-line {
        height: 12px; border-radius: 6px;
        background: rgba(0,0,0,0.06); margin-bottom: 10px;
    }
    .ts-skel-line.w-50 { width: 50%; } .ts-skel-line.w-75 { width: 75%; }
    .ts-skel-line.w-25 { width: 25%; } .ts-skel-line.w-100 { width: 100%; }
    .ts-skel-line.w-60 { width: 60%; }
    [data-bs-theme="dark"] .ts-skeleton-card { background: rgba(30,41,59,0.5); }
    [data-bs-theme="dark"] .ts-skel-avatar,
    [data-bs-theme="dark"] .ts-skel-line { background: rgba(255,255,255,0.08); }
    [data-bs-theme="dark"] .ts-skel-shine { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent); }

    /* ---- Cards ---- */
    .testimoni-carousel-wrap { margin-bottom: 10px; }
    .testimoni-card-modern { padding: 10px; height: 100%; }
    .testimoni-card-inner {
        position: relative;
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.7);
        border-radius: 24px; padding: 28px;
        height: 100%;
        box-shadow: 0 8px 32px rgba(0,0,0,0.04), 0 2px 8px rgba(0,0,0,0.02);
        transition: all 0.4s cubic-bezier(0.16,1,0.3,1);
        overflow: hidden;
    }
    .testimoni-card-inner:hover {
        transform: translateY(-8px);
        box-shadow: 0 24px 56px rgba(99,102,241,0.12), 0 6px 20px rgba(0,0,0,0.04);
        border-color: rgba(99,102,241,0.2);
    }
    .testimoni-card-glow {
        position: absolute; top: -50%; right: -50%;
        width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
        pointer-events: none; transition: all 0.6s ease;
    }
    .testimoni-card-inner:hover .testimoni-card-glow { transform: scale(1.8); opacity: 0.8; }
    .testimoni-card-content { position: relative; z-index: 1; }
    .testimoni-rating { display: flex; gap: 4px; }
    .testimoni-rating .fa-star {
        font-size: 15px; color: #E2E8F0;
        transition: all 0.3s ease;
    }
    .testimoni-rating .fa-star.active { color: #F59E0B; text-shadow: 0 0 6px rgba(245,158,11,0.3); }
    .testimoni-judul {
        font-size: 1rem; font-weight: 700; margin-bottom: 8px; color: #1E293B;
    }
    .testimoni-isi {
        font-size: 0.88rem; line-height: 1.7; color: #475569; margin-bottom: 20px;
    }
    .testimoni-footer {
        border-top: 1px solid rgba(0,0,0,0.04); padding-top: 16px; margin-top: auto;
    }
    .testimoni-author-row {
        display: flex; align-items: center; gap: 12px;
    }
    .testimoni-avatar-wrap { position: relative; flex-shrink: 0; }
    .testimoni-avatar-img {
        width: 48px; height: 48px; border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(99,102,241,0.15);
        transition: all 0.3s ease;
    }
    .testimoni-card-inner:hover .testimoni-avatar-img { border-color: rgba(99,102,241,0.35); }
    .testimoni-avatar-placeholder {
        width: 48px; height: 48px; border-radius: 50%;
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 700;
    }
    .testimoni-verified-badge {
        position: absolute; bottom: -2px; right: -2px;
        width: 18px; height: 18px;
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; color: #fff;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(99,102,241,0.35);
    }
    .testimoni-author-name { font-size: 0.88rem; font-weight: 700; margin-bottom: 0; color: #1E293B; }
    .testimoni-author-role { font-size: 0.75rem; color: #64748B; }
    .testimoni-date { font-size: 0.72rem; color: #94A3B8; margin-top: 8px; }
    .testimoni-featured-img img { border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); width: 100%; max-height: 140px; object-fit: cover; }

    [data-bs-theme="dark"] .testimoni-card-inner {
        background: rgba(30,41,59,0.7);
        border-color: rgba(255,255,255,0.06);
    }
    [data-bs-theme="dark"] .testimoni-judul { color: #F1F5F9; }
    [data-bs-theme="dark"] .testimoni-isi { color: #94A3B8; }
    [data-bs-theme="dark"] .testimoni-author-name { color: #E2E8F0; }
    [data-bs-theme="dark"] .testimoni-card-inner:hover { border-color: rgba(99,102,241,0.25); box-shadow: 0 24px 56px rgba(99,102,241,0.06); }

    /* ---- Swiper overrides ---- */
    .testimoniSwiper .swiper-slide { height: auto; }
    .testimoni-next-custom, .testimoni-prev-custom {
        width: 44px; height: 44px;
        background: rgba(255,255,255,0.85); backdrop-filter: blur(10px);
        border: 1px solid rgba(0,0,0,0.06); border-radius: 50%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        transition: all 0.3s ease; color: #6366F1;
        display: flex; align-items: center; justify-content: center;
    }
    .testimoni-next-custom::after, .testimoni-prev-custom::after { font-size: 16px; font-weight: 700; }
    .testimoni-next-custom:hover, .testimoni-prev-custom:hover {
        background: #fff; box-shadow: 0 8px 24px rgba(99,102,241,0.15);
        transform: scale(1.08); color: #4F46E5;
    }
    .testimoni-pagination-custom { position: relative; margin-top: 24px; }
    .testimoni-pagination-custom .swiper-pagination-bullet {
        width: 8px; height: 8px;
        background: rgba(99,102,241,0.2); opacity: 1;
        transition: all 0.3s ease; border-radius: 4px;
    }
    .testimoni-pagination-custom .swiper-pagination-bullet-active {
        width: 28px; background: linear-gradient(90deg, #6366F1, #8B5CF6);
    }
    [data-bs-theme="dark"] .testimoni-next-custom, [data-bs-theme="dark"] .testimoni-prev-custom {
        background: rgba(30,41,59,0.85); border-color: rgba(255,255,255,0.08); color: #A5B4FC;
    }
    [data-bs-theme="dark"] .testimoni-next-custom:hover, [data-bs-theme="dark"] .testimoni-prev-custom:hover {
        background: #1E293B; color: #C7D2FE;
    }

    /* ---- Empty State ---- */
    .testimoni-empty-wrap { padding: 40px 0; }
    .testimoni-empty-glass {
        position: relative;
        max-width: 480px; margin: 0 auto;
        background: rgba(255,255,255,0.5);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 32px; padding: 60px 40px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .testimoni-empty-glass::before {
        content: ''; position: absolute; top: -60px; right: -60px;
        width: 150px; height: 150px; border-radius: 50%;
        background: radial-gradient(circle, rgba(99,102,241,0.1), transparent);
        pointer-events: none;
    }
    .testimoni-empty-floating {
        animation: tsFloat 4s ease-in-out infinite;
    }
    .testimoni-empty-icon-wrap {
        width: 90px; height: 90px; margin: 0 auto;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.12));
        font-size: 36px; color: #6366F1;
        box-shadow: 0 8px 24px rgba(99,102,241,0.08);
    }
    .testimoni-empty-title {
        font-size: 1.4rem; font-weight: 800; margin: 24px 0 8px; color: #0F172A;
    }
    .testimoni-empty-desc {
        font-size: 0.9rem; color: #64748B; margin-bottom: 28px; line-height: 1.6;
    }
    .testimoni-empty-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 12px 28px;
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        color: #fff; border-radius: 14px;
        font-weight: 600; font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 8px 24px rgba(99,102,241,0.25);
    }
    .testimoni-empty-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(99,102,241,0.35); color: #fff; }
    [data-bs-theme="dark"] .testimoni-empty-glass {
        background: rgba(30,41,59,0.5);
        border-color: rgba(255,255,255,0.06);
    }
    [data-bs-theme="dark"] .testimoni-empty-title { color: #F1F5F9; }
    [data-bs-theme="dark"] .testimoni-empty-desc { color: #94A3B8; }

    /* ---- Responsive ---- */
    @media (max-width: 768px) {
        .testimoni-section { padding: 60px 0 50px; }
        .testimoni-card-inner { padding: 20px; }
        .testimoni-judul { font-size: 0.95rem; }
        .testimoni-isi { font-size: 0.84rem; }
        .testimoni-avatar-img, .testimoni-avatar-placeholder { width: 40px; height: 40px; }
        .testimoni-avatar-placeholder { font-size: 16px; }
        .testimoni-next-custom, .testimoni-prev-custom { display: none; }
        .ts-filter-wrap { justify-content: center; }
        .testimoni-empty-glass { padding: 40px 24px; }
        .ts-card { padding: 16px 12px; }
        .ts-icon { width: 36px; height: 36px; font-size: 15px; }
    }
    @media (max-width: 576px) {
        .ts-filter-wrap { padding: 3px; }
        .ts-filter-btn { font-size: 0.75rem; padding: 6px 10px; }
        .testimoni-empty-glass { border-radius: 24px; padding: 32px 20px; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var hasTestimonials = <?= $testimonials ? 'true' : 'false' ?>;
        var skeleton = document.getElementById('tsSkeleton');
        var carousel = document.getElementById('tsCarousel');
        var empty = document.getElementById('tsEmpty');
        var swiperEl = document.querySelector('.testimoniSwiper');
        var wrapper = document.getElementById('tsWrapper');

        function showState(type) {
            if (skeleton) skeleton.style.display = type === 'skeleton' ? 'block' : 'none';
            if (carousel) carousel.style.display = type === 'carousel' ? 'block' : 'none';
            if (empty) empty.style.display = type === 'empty' ? 'block' : 'none';
        }

        // Show skeleton -> then real content
        showState('skeleton');
        setTimeout(function() {
            if (hasTestimonials) {
                showState('carousel');
                initSwiper();
            } else {
                showState('empty');
            }
        }, <?= $testimonials ? 600 : 400 ?>);

        var testimoniSwiper = null;

        function initSwiper() {
            if (!swiperEl) return;
            var slides = wrapper ? wrapper.querySelectorAll('.swiper-slide') : [];
            if (slides.length === 0) { showState('empty'); return; }

            // Show only first 3 slides after skeleton (progressive loading)
            if (slides.length > 3) {
                for (var i = 3; i < slides.length; i++) {
                    slides[i].style.display = 'none';
                }
                setTimeout(function() {
                    for (var i = 3; i < slides.length; i++) {
                        slides[i].style.display = '';
                    }
                    if (testimoniSwiper) testimoniSwiper.update();
                }, 300);
            }

            testimoniSwiper = new Swiper('.testimoniSwiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: slides.length > 1,
                autoplay: slides.length > 1 ? {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                } : false,
                speed: 700,
                grabCursor: true,
                pagination: {
                    el: '.testimoni-pagination-custom',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.testimoni-next-custom',
                    prevEl: '.testimoni-prev-custom',
                },
                breakpoints: {
                    640: { slidesPerView: Math.min(2, slides.length), spaceBetween: 24 },
                    1024: { slidesPerView: Math.min(3, slides.length), spaceBetween: 28 },
                },
                on: {
                    init: function() {
                        carousel.style.opacity = '0';
                        carousel.style.transition = 'opacity 0.5s ease';
                        setTimeout(function() { carousel.style.opacity = '1'; }, 100);
                    }
                }
            });
        }

        // ---- Filter & Search ----
        var filterBtns = document.querySelectorAll('.ts-filter-btn');
        var searchInput = document.getElementById('tsSearch');
        var allSlides = [];

        function collectSlides() {
            if (!wrapper) return;
            allSlides = [];
            wrapper.querySelectorAll('.swiper-slide').forEach(function(s) {
                allSlides.push(s);
            });
        }

        function applyFilterAndSearch() {
            if (!wrapper) return;
            collectSlides();
            var activeFilter = document.querySelector('.ts-filter-btn.active');
            var filterVal = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
            var query = searchInput ? searchInput.value.toLowerCase().trim() : '';

            var visible = [];
            allSlides.forEach(function(slide) {
                var rating = parseInt(slide.getAttribute('data-rating') || '0');
                var date = slide.getAttribute('data-date') || '';
                var text = slide.textContent.toLowerCase();

                // Filter
                var matchFilter = false;
                if (filterVal === 'all') matchFilter = true;
                else if (filterVal === 'terbaru') matchFilter = true;
                else if (filterVal === 'terlama') matchFilter = true;
                else if (filterVal === '5' && rating === 5) matchFilter = true;
                else if (filterVal === '4' && rating === 4) matchFilter = true;
                else matchFilter = false;

                // Search
                var matchSearch = !query || text.indexOf(query) !== -1;

                if (matchFilter && matchSearch) {
                    visible.push({ el: slide, rating: rating, date: date });
                }
            });

            // Sort
            if (filterVal === 'terbaru') {
                visible.sort(function(a, b) { return b.date.localeCompare(a.date); });
            } else if (filterVal === 'terlama') {
                visible.sort(function(a, b) { return a.date.localeCompare(b.date); });
            } else if (filterVal === 'all') {
                visible.sort(function(a, b) { return b.date.localeCompare(a.date); });
            }

            // Reorder DOM
            visible.forEach(function(item, i) {
                item.el.style.order = i;
                item.el.style.display = '';
            });
            allSlides.forEach(function(s) {
                var found = false;
                for (var i = 0; i < visible.length; i++) {
                    if (visible[i].el === s) { found = true; break; }
                }
                if (!found) s.style.display = 'none';
            });

            if (testimoniSwiper) {
                testimoniSwiper.update();
                if (visible.length === 0) testimoniSwiper.autoplay.stop();
                else if (visible.length < 2) testimoniSwiper.autoplay.stop();
                else testimoniSwiper.autoplay.start();
            }

            // Show empty if no results
            var emptyMsg = document.getElementById('tsSearchEmpty');
            if (visible.length === 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.id = 'tsSearchEmpty';
                    emptyMsg.className = 'text-center py-5';
                    emptyMsg.innerHTML = '<div style="font-size:2rem;color:#94A3B8;margin-bottom:8px"><i class="fas fa-search"></i></div><p style="color:#94A3B8">Tidak ada testimoni yang cocok dengan pencarian Anda</p>';
                    if (wrapper.parentNode) wrapper.parentNode.appendChild(emptyMsg);
                }
                emptyMsg.style.display = 'block';
            } else {
                if (emptyMsg) emptyMsg.style.display = 'none';
            }
        }

        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                filterBtns.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                applyFilterAndSearch();
            });
        });

        if (searchInput) {
            var searchTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(applyFilterAndSearch, 300);
            });
        }

        // ---- Realtime polling ----
        function pollTestimonials() {
            fetch('ajax/testimoni_check.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.changed && data.html) {
                        if (wrapper) wrapper.innerHTML = data.html;
                        if (testimoniSwiper) { testimoniSwiper.destroy(); testimoniSwiper = null; }
                        if (skeleton) { skeleton.style.display = 'block'; }
                        setTimeout(function() {
                            if (carousel) carousel.style.display = 'block';
                            if (skeleton) skeleton.style.display = 'none';
                            if (empty) empty.style.display = 'none';
                            initSwiper();
                            applyFilterAndSearch();
                        }, 400);
                    }
                })
                .catch(function() {});
        }

        // Poll every 30 seconds
        <?php if ($testimonials): ?>
        setInterval(pollTestimonials, 30000);
        <?php endif; ?>
    });
    </script>
    <!-- FAQ Section -->
    <?php if ($faqs): ?>
    <section id="faq" class="section-padding" style="background: white;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">Frequently <span>Asked Questions</span></h2>
                <p class="section-subtitle">Pertanyaan yang sering diajukan</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-accordion accordion" id="faqAccordion">
                        <?php foreach ($faqs as $i => $f): ?>
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="<?= $i * 50 ?>">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $i ?>" aria-expanded="false" aria-controls="faqCollapse<?= $i ?>">
                                    <?= $f['pertanyaan'] ?>
                                </button>
                            </h2>
                            <div id="faqCollapse<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= $f['jawaban'] ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>



    <script>
    /* ============================================================
       Demo Aplikasi â€” Interactive Preview & Intersection Observer
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function() {
        var demoSection = document.getElementById('demo');
        if (!demoSection) return;

        var steps = demoSection.querySelectorAll('.demo-step');
        var previews = demoSection.querySelectorAll('.demo-preview-card');
        var dots = demoSection.querySelectorAll('.demo-preview-dot');
        var totalSteps = steps.length;
        var currentStep = 0;
        var autoTimer = null;
        var isTransitioning = false;

        // --- Intersection Observer for AOS-like animations ---
        var aosEls = demoSection.querySelectorAll('[data-demo-aos]');
        var aosObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var anim = el.dataset.demoAos;
                    el.style.opacity = '0';
                    el.style.transform = anim === 'fade-up' ? 'translateY(30px)' :
                                         anim === 'fade-right' ? 'translateX(-30px)' :
                                         anim === 'fade-left' ? 'translateX(30px)' : 'translateY(20px)';
                    el.style.transition = 'all 0.7s cubic-bezier(0.4,0,0.2,1)';
                    requestAnimationFrame(function() {
                        el.style.opacity = '1';
                        el.style.transform = 'translate(0,0)';
                    });
                    aosObserver.unobserve(el);
                }
            });
        }, { threshold: 0.15 });
        aosEls.forEach(function(el) { aosObserver.observe(el); });

        // --- Helper: activate step ---
        function activateStep(index) {
            if (isTransitioning || index === currentStep) return;
            isTransitioning = true;
            currentStep = index;

            // Update timeline steps
            steps.forEach(function(s, i) {
                s.classList.toggle('demo-step-active', i === index);
            });

            // Update preview cards
            previews.forEach(function(p, i) {
                p.classList.toggle('demo-preview-active', i === index);
            });

            // Update dots
            dots.forEach(function(d, i) {
                d.classList.toggle('active', i === index);
            });

            // Update step counter
            var counter = demoSection.querySelector('.demo-step-current');
            if (counter) {
                counter.textContent = String(index + 1).padStart(2, '0');
            }

            setTimeout(function() { isTransitioning = false; }, 600);
        }

        // --- Click/hover on timeline steps ---
        steps.forEach(function(step, i) {
            step.addEventListener('click', function() {
                activateStep(i);
                resetAutoTimer();
            });
            step.addEventListener('mouseenter', function() {
                activateStep(i);
                resetAutoTimer();
            });
            // Keyboard support
            step.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    activateStep(i);
                    resetAutoTimer();
                }
            });
        });

        // --- Arrow buttons ---
        var prevArrow = demoSection.querySelector('.demo-prev-arrow');
        var nextArrow = demoSection.querySelector('.demo-next-arrow');
        if (prevArrow) {
            prevArrow.addEventListener('click', function() {
                var next = (currentStep - 1 + totalSteps) % totalSteps;
                activateStep(next);
                resetAutoTimer();
            });
        }
        if (nextArrow) {
            nextArrow.addEventListener('click', function() {
                var next = (currentStep + 1) % totalSteps;
                activateStep(next);
                resetAutoTimer();
            });
        }

        // --- Dot pagination ---
        dots.forEach(function(dot, i) {
            dot.addEventListener('click', function() {
                activateStep(i);
                resetAutoTimer();
            });
        });

        // --- Auto rotation ---
        function startAutoTimer() {
            stopAutoTimer();
            autoTimer = setInterval(function() {
                var next = (currentStep + 1) % totalSteps;
                activateStep(next);
            }, 5000);
        }
        function stopAutoTimer() {
            if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
        }
        function resetAutoTimer() {
            stopAutoTimer();
            startAutoTimer();
        }
        startAutoTimer();

        // --- Pause on hover over preview ---
        var previewWrap = demoSection.querySelector('.demo-preview-wrap');
        if (previewWrap) {
            previewWrap.addEventListener('mouseenter', stopAutoTimer);
            previewWrap.addEventListener('mouseleave', startAutoTimer);
        }

        // --- Keyboard navigation (left/right arrows) ---
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                var prev = (currentStep - 1 + totalSteps) % totalSteps;
                activateStep(prev);
                resetAutoTimer();
            } else if (e.key === 'ArrowRight') {
                var next = (currentStep + 1) % totalSteps;
                activateStep(next);
                resetAutoTimer();
            }
        });
    });
    </script>

    <!-- ============================================================
    KONTAK SECTION
    ============================================================ -->
    <?php
    $kontakAlamat  = $footerData['alamat'] ?? $setting['alamat'] ?? '';
    $kontakTelp   = $footerData['no_telepon'] ?? $setting['telepon'] ?? '';
    $kontakEmail  = $footerData['email'] ?? $setting['email'] ?? '';
    $kontakWeb    = $setting['website'] ?? '';
    $hasKontak    = $kontakAlamat || $kontakTelp || $kontakEmail || $kontakWeb;
    ?>
    <section id="kontak" class="kontak-section position-relative overflow-hidden">
        <div class="kontak-bg-gradient"></div>

        <!-- Animated Background -->
        <div class="kontak-shapes" aria-hidden="true">
            <div class="kshape kshape-1"></div>
            <div class="kshape kshape-2"></div>
            <div class="kshape kshape-3"></div>
            <div class="kshape kshape-4"></div>
        </div>

        <div class="container position-relative" style="z-index:2">

            <!-- Header -->
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="testimoni-badge"><i class="fas fa-headset me-1"></i> Contact Center</span>
                <h2 class="testimoni-title">Hubungi <span>Kami</span></h2>
                <p class="testimoni-subtitle">Kami siap membantu Anda apabila memiliki pertanyaan, kendala, atau membutuhkan informasi mengenai Aplikasi Pengaduan Sarana Sekolah.</p>
            </div>

            <div class="row g-4">
                <!-- LEFT: Informasi Kontak -->
                <div class="col-lg-5" data-aos="fade-right" data-aos-delay="50">
                    <?php if ($hasKontak): ?>
                    <div class="kontak-info-grid">
                        <?php if ($kontakAlamat): ?>
                        <div class="kinfo-card">
                            <div class="kinfo-icon" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.12));color:#6366F1">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="kinfo-body">
                                <h6>Alamat Sekolah</h6>
                                <p><?= htmlspecialchars($kontakAlamat) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($kontakTelp): ?>
                        <div class="kinfo-card">
                            <div class="kinfo-icon" style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(5,150,105,0.12));color:#10B981">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="kinfo-body">
                                <h6>Nomor Telepon</h6>
                                <p><a href="tel:<?= htmlspecialchars($kontakTelp) ?>"><?= htmlspecialchars($kontakTelp) ?></a></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($kontakEmail): ?>
                        <div class="kinfo-card">
                            <div class="kinfo-icon" style="background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(37,99,235,0.12));color:#3B82F6">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="kinfo-body">
                                <h6>Email</h6>
                                <p><a href="mailto:<?= htmlspecialchars($kontakEmail) ?>"><?= htmlspecialchars($kontakEmail) ?></a></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($kontakWeb): ?>
                        <div class="kinfo-card">
                            <div class="kinfo-icon" style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(217,119,6,0.12));color:#F59E0B">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="kinfo-body">
                                <h6>Website</h6>
                                <p><a href="<?= htmlspecialchars($kontakWeb) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($kontakWeb) ?></a></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="kinfo-card">
                            <div class="kinfo-icon" style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(124,58,237,0.12));color:#8B5CF6">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="kinfo-body">
                                <h6>Jam Operasional</h6>
                                <p>Senin - Jumat: 07.00 - 16.00<br>Sabtu: 07.00 - 12.00<br>Minggu: Libur</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="kontak-social" data-aos="fade-up" data-aos-delay="100">
                        <h6 class="fw-bold mb-3" style="color:#0F172A">Ikuti Kami</h6>
                        <div class="d-flex gap-3 flex-wrap">
                            <?php $ig = $footerData['instagram'] ?? ''; ?>
                            <?php $fb = $footerData['facebook'] ?? ''; ?>
                            <?php $yt = $footerData['youtube'] ?? ''; ?>
                            <?php if ($ig): ?>
                            <a href="<?= $ig ?>" class="ksocial-link" target="_blank" rel="noopener" aria-label="Instagram" data-tooltip="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($fb): ?>
                            <a href="<?= $fb ?>" class="ksocial-link" target="_blank" rel="noopener" aria-label="Facebook" data-tooltip="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($yt): ?>
                            <a href="<?= $yt ?>" class="ksocial-link" target="_blank" rel="noopener" aria-label="YouTube" data-tooltip="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <?php endif; ?>
                            <a href="https://wa.me/<?= str_replace(['+','-',' '], '', $kontakTelp) ?>" class="ksocial-link" target="_blank" rel="noopener" aria-label="WhatsApp" data-tooltip="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="mailto:<?= $kontakEmail ?>" class="ksocial-link" target="_blank" rel="noopener" aria-label="Email" data-tooltip="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Empty State Info -->
                    <div class="kontak-empty-info" data-aos="fade-up">
                        <div class="kinfo-empty-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h5>Informasi Kontak Belum Tersedia</h5>
                        <p>Saat ini informasi kontak sekolah belum diisi. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- RIGHT: Form Kontak -->
                <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
                    <div class="kontak-form-card">
                        <div class="kontak-form-header">
                            <h5><i class="fas fa-paper-plane me-2"></i>Kirim Pesan</h5>
                            <p>Isi form di bawah ini untuk menghubungi kami</p>
                        </div>
                        <form id="kontakForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="kform-label" for="kname">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="kform-input" id="kname" name="nama" required placeholder="Masukkan nama lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="kform-label" for="kemail">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="kform-input" id="kemail" name="email" required placeholder="Masukkan email">
                                </div>
                                <div class="col-md-6">
                                    <label class="kform-label" for="khp">Nomor HP</label>
                                    <input type="tel" class="kform-input" id="khp" name="no_hp" placeholder="08xxxxxxxxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="kform-label" for="ksubjek">Subjek <span class="text-danger">*</span></label>
                                    <input type="text" class="kform-input" id="ksubjek" name="subjek" required placeholder="Judul pesan">
                                </div>
                                <div class="col-12">
                                    <label class="kform-label" for="kkategori">Kategori Pertanyaan</label>
                                    <select class="kform-input" id="kkategori" name="kategori">
                                        <option value="">Pilih kategori</option>
                                        <option value="informasi">Informasi</option>
                                        <option value="pengaduan">Pengaduan</option>
                                        <option value="saran">Saran & Masukan</option>
                                        <option value="kendala">Kendala Aplikasi</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="kform-label" for="kpesan">Pesan <span class="text-danger">*</span></label>
                                    <textarea class="kform-input kform-textarea" id="kpesan" name="pesan" rows="4" required placeholder="Tulis pesan Anda..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="kform-label" for="klampiran">Upload Lampiran (Opsional)</label>
                                    <div class="kform-file-wrap">
                                        <input type="file" class="kform-file" id="klampiran" name="lampiran" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                        <div class="kform-file-placeholder">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Klik atau seret file ke sini</span>
                                            <small>Maks 2MB (JPG, PNG, PDF, DOC)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="kform-checkbox-wrap">
                                        <input type="checkbox" id="kprivacy" name="privacy" required>
                                        <label for="kprivacy">Saya menyetujui kebijakan privasi dan pengelolaan data.</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="kform-submit" id="kontakSubmit">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- FAQ Mini -->
                    <div class="kontak-faq-mini" data-aos="fade-up" data-aos-delay="150">
                        <h6 class="fw-bold mb-3" style="color:#0F172A"><i class="fas fa-question-circle me-2" style="color:#6366F1"></i>Pertanyaan Umum</h6>
                        <div class="accordion" id="kontakFaqAccordion">
                            <div class="kfaq-item">
                                <button class="kfaq-trigger" data-bs-toggle="collapse" data-bs-target="#kfaq1" aria-expanded="false">
                                    Bagaimana cara membuat pengaduan?
                                    <i class="fas fa-chevron-down kfaq-arrow"></i>
                                </button>
                                <div id="kfaq1" class="collapse" data-bs-parent="#kontakFaqAccordion">
                                    <div class="kfaq-body">Login ke akun Anda, lalu klik menu "Buat Pengaduan", isi form lengkap dengan foto dan kirim. Admin akan memproses pengaduan Anda.</div>
                                </div>
                            </div>
                            <div class="kfaq-item">
                                <button class="kfaq-trigger" data-bs-toggle="collapse" data-bs-target="#kfaq2" aria-expanded="false">
                                    Bagaimana cara menghubungi Admin?
                                    <i class="fas fa-chevron-down kfaq-arrow"></i>
                                </button>
                                <div id="kfaq2" class="collapse" data-bs-parent="#kontakFaqAccordion">
                                    <div class="kfaq-body">Anda dapat menghubungi Admin melalui form kontak di atas, atau langsung melalui nomor telepon dan email yang tersedia.</div>
                                </div>
                            </div>
                            <div class="kfaq-item">
                                <button class="kfaq-trigger" data-bs-toggle="collapse" data-bs-target="#kfaq3" aria-expanded="false">
                                    Berapa lama pengaduan diproses?
                                    <i class="fas fa-chevron-down kfaq-arrow"></i>
                                </button>
                                <div id="kfaq3" class="collapse" data-bs-parent="#kontakFaqAccordion">
                                    <div class="kfaq-body">Pengaduan umumnya diproses dalam 1-3 hari kerja tergantung pada kompleksitas masalah yang dilaporkan.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps -->
            <?php if ($kontakAlamat): ?>
            <div class="kontak-map" data-aos="fade-up" data-aos-delay="50">
                <div class="kontak-map-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color:#0F172A"><i class="fas fa-map-marked-alt me-2" style="color:#EF4444"></i>Lokasi Sekolah</h6>
                        <a href="https://www.google.com/maps/search/<?= urlencode($kontakAlamat) ?>" target="_blank" rel="noopener" class="kmap-btn">
                            <i class="fas fa-external-link-alt me-1"></i>Buka di Google Maps
                        </a>
                    </div>
                    <div class="kmap-embed" id="kmapContainer">
                        <div class="kmap-placeholder">
                            <i class="fas fa-map"></i>
                            <p>Maps akan dimuat saat halaman selesai</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CTA Banner -->
            <div class="kontak-cta" data-aos="fade-up" data-aos-delay="50">
                <div class="kontak-cta-inner">
                    <div class="kontak-cta-content">
                        <h3>Butuh Bantuan?</h3>
                        <p>Tim kami siap membantu Anda kapan saja selama jam operasional.</p>
                    </div>
                    <div class="kontak-cta-actions">
                        <a href="https://wa.me/<?= str_replace(['+','-',' '], '', $kontakTelp) ?>" class="kontak-cta-btn whatsapp">
                            <i class="fab fa-whatsapp me-2"></i>Hubungi Kami
                        </a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="kontak-cta-btn login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <style>
    /* ============================================================
       KONTAK SECTION — Premium Redesign
       ============================================================ */
    .kontak-section { padding: 90px 0 80px; }
    .kontak-bg-gradient {
        position: absolute; inset: 0;
        background: linear-gradient(160deg, #F8FAFC 0%, #FEF2F2 30%, #FFF7ED 60%, #F8FAFC 100%);
        z-index: 0;
    }
    [data-bs-theme="dark"] .kontak-bg-gradient {
        background: linear-gradient(160deg, #0B1120 0%, #1A1110 30%, #14100C 60%, #080E1A 100%);
    }

    /* ---- Kontak Shapes ---- */
    .kontak-shapes { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
    .kontak-shapes .kshape {
        position: absolute; border-radius: 50%;
        filter: blur(70px);
        opacity: 0.1;
        animation: kshapeFloat 10s ease-in-out infinite;
    }
    .kshape-1 { width: 280px; height: 280px; background: #EF4444; top: -60px; right: 10%; }
    .kshape-2 { width: 200px; height: 200px; background: #F59E0B; bottom: 15%; left: 5%; animation-delay: -3s; }
    .kshape-3 { width: 160px; height: 160px; background: #6366F1; top: 50%; right: 5%; animation-delay: -5s; }
    .kshape-4 { width: 100px; height: 100px; background: #10B981; top: 10%; left: 30%; animation-delay: -7s; }
    @keyframes kshapeFloat {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-25px) scale(1.08); }
    }

    /* ---- Info Grid ---- */
    .kontak-info-grid { display: flex; flex-direction: column; gap: 14px; }
    .kinfo-card {
        display: flex; align-items: center; gap: 16px;
        background: rgba(255,255,255,0.55);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 18px; padding: 18px 20px;
        transition: all 0.35s cubic-bezier(0.16,1,0.3,1);
        box-shadow: 0 4px 16px rgba(0,0,0,0.02);
    }
    .kinfo-card:hover { transform: translateX(6px); box-shadow: 0 8px 28px rgba(0,0,0,0.04); background: rgba(255,255,255,0.7); }
    .kinfo-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .kinfo-body h6 { font-size: 0.85rem; font-weight: 700; margin-bottom: 2px; color: #0F172A; }
    .kinfo-body p { font-size: 0.82rem; color: #475569; margin-bottom: 0; line-height: 1.5; }
    .kinfo-body a { color: #6366F1; text-decoration: none; }
    .kinfo-body a:hover { text-decoration: underline; }
    [data-bs-theme="dark"] .kinfo-card { background: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .kinfo-card:hover { background: rgba(30,41,59,0.65); }
    [data-bs-theme="dark"] .kinfo-body h6 { color: #F1F5F9; }
    [data-bs-theme="dark"] .kinfo-body p { color: #94A3B8; }

    /* ---- Social ---- */
    .kontak-social { margin-top: 28px; padding: 20px; background: rgba(255,255,255,0.4); backdrop-filter: blur(12px); border-radius: 18px; border: 1px solid rgba(255,255,255,0.5); }
    .ksocial-link {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; color: #64748B; text-decoration: none;
        background: rgba(255,255,255,0.6);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.3s ease; position: relative;
    }
    .ksocial-link:hover {
        transform: translateY(-3px); color: #fff;
        box-shadow: 0 6px 16px rgba(99,102,241,0.2);
    }
    .ksocial-link:hover:nth-child(1) { background: linear-gradient(135deg,#833AB4,#FD1D1D); border-color: transparent; }
    .ksocial-link:hover:nth-child(2) { background: #1877F2; border-color: transparent; }
    .ksocial-link:hover:nth-child(3) { background: #FF0000; border-color: transparent; }
    .ksocial-link:hover:nth-child(4) { background: #25D366; border-color: transparent; }
    .ksocial-link:hover:nth-child(5) { background: #EA4335; border-color: transparent; }
    .ksocial-link::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px);
        left: 50%; transform: translateX(-50%) scale(0.8);
        background: #0F172A; color: #fff; padding: 4px 10px;
        border-radius: 6px; font-size: 0.7rem; font-weight: 500;
        white-space: nowrap; opacity: 0; pointer-events: none;
        transition: all 0.2s ease;
    }
    .ksocial-link:hover::after { opacity: 1; transform: translateX(-50%) scale(1); }
    [data-bs-theme="dark"] .kontak-social { background: rgba(30,41,59,0.4); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .ksocial-link { background: rgba(30,41,59,0.6); color: #94A3B8; border-color: rgba(255,255,255,0.06); }

    /* ---- Empty Info ---- */
    .kontak-empty-info {
        text-align: center; padding: 50px 30px;
        background: rgba(255,255,255,0.4); backdrop-filter: blur(12px);
        border-radius: 24px; border: 1px solid rgba(255,255,255,0.5);
    }
    .kinfo-empty-icon {
        width: 70px; height: 70px; margin: 0 auto 16px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 28px; color: #6366F1;
        background: linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.12));
    }
    .kontak-empty-info h5 { font-weight: 700; color: #0F172A; margin-bottom: 8px; }
    .kontak-empty-info p { color: #64748B; margin-bottom: 0; font-size: 0.9rem; }
    [data-bs-theme="dark"] .kontak-empty-info { background: rgba(30,41,59,0.4); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .kontak-empty-info h5 { color: #F1F5F9; }
    [data-bs-theme="dark"] .kontak-empty-info p { color: #94A3B8; }

    /* ---- Form Card ---- */
    .kontak-form-card {
        background: rgba(255,255,255,0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 24px; padding: 32px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .kontak-form-header { margin-bottom: 24px; }
    .kontak-form-header h5 { font-weight: 700; color: #0F172A; }
    .kontak-form-header p { font-size: 0.85rem; color: #64748B; margin-bottom: 0; }
    .kform-label {
        display: block; font-size: 0.8rem; font-weight: 600; color: #0F172A; margin-bottom: 6px;
    }
    .kform-input {
        width: 100%; padding: 10px 14px;
        background: rgba(255,255,255,0.7);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px; font-size: 0.88rem; color: #0F172A;
        transition: all 0.3s ease; outline: none;
    }
    .kform-input:focus { border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); background: #fff; }
    .kform-input::placeholder { color: #94A3B8; }
    .kform-textarea { resize: vertical; min-height: 110px; }
    .kform-file-wrap {
        position: relative; border: 2px dashed rgba(0,0,0,0.1);
        border-radius: 14px; padding: 24px; text-align: center; cursor: pointer;
        transition: all 0.3s ease;
    }
    .kform-file-wrap:hover { border-color: #6366F1; background: rgba(99,102,241,0.04); }
    .kform-file { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .kform-file-placeholder i { font-size: 28px; color: #6366F1; display: block; margin-bottom: 6px; }
    .kform-file-placeholder span { display: block; font-size: 0.85rem; color: #475569; }
    .kform-file-placeholder small { display: block; font-size: 0.72rem; color: #94A3B8; margin-top: 4px; }
    .kform-checkbox-wrap { display: flex; align-items: flex-start; gap: 10px; }
    .kform-checkbox-wrap input[type="checkbox"] {
        margin-top: 3px; width: 18px; height: 18px; flex-shrink: 0;
        accent-color: #6366F1; cursor: pointer;
    }
    .kform-checkbox-wrap label { font-size: 0.82rem; color: #475569; cursor: pointer; }
    .kform-submit {
        width: 100%; padding: 12px 24px; border: none;
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        color: #fff; border-radius: 14px; font-weight: 600; font-size: 0.95rem;
        cursor: pointer; transition: all 0.3s ease;
        box-shadow: 0 8px 24px rgba(99,102,241,0.25);
    }
    .kform-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(99,102,241,0.35); }
    .kform-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    [data-bs-theme="dark"] .kontak-form-card { background: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .kontak-form-header h5 { color: #F1F5F9; }
    [data-bs-theme="dark"] .kform-label { color: #E2E8F0; }
    [data-bs-theme="dark"] .kform-input { background: rgba(15,23,42,0.5); border-color: rgba(255,255,255,0.08); color: #F1F5F9; }
    [data-bs-theme="dark"] .kform-input:focus { background: #0F172A; border-color: #6366F1; }
    [data-bs-theme="dark"] .kform-file-wrap { border-color: rgba(255,255,255,0.1); }
    [data-bs-theme="dark"] .kform-file-placeholder span { color: #94A3B8; }
    [data-bs-theme="dark"] .kform-checkbox-wrap label { color: #94A3B8; }

    /* ---- FAQ Mini ---- */
    .kontak-faq-mini { margin-top: 20px; }
    .kfaq-item {
        background: rgba(255,255,255,0.4); backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 14px; margin-bottom: 8px; overflow: hidden;
        transition: all 0.3s ease;
    }
    .kfaq-item:hover { background: rgba(255,255,255,0.55); }
    .kfaq-trigger {
        width: 100%; padding: 14px 18px; border: none; background: none;
        display: flex; justify-content: space-between; align-items: center;
        font-size: 0.88rem; font-weight: 600; color: #0F172A; cursor: pointer;
        text-align: left; transition: color 0.3s ease;
    }
    .kfaq-trigger[aria-expanded="true"] { color: #6366F1; }
    .kfaq-arrow { font-size: 12px; color: #94A3B8; transition: transform 0.3s ease; }
    .kfaq-trigger[aria-expanded="true"] .kfaq-arrow { transform: rotate(180deg); color: #6366F1; }
    .kfaq-body { padding: 0 18px 14px; font-size: 0.84rem; color: #475569; line-height: 1.6; }
    [data-bs-theme="dark"] .kfaq-item { background: rgba(30,41,59,0.4); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .kfaq-trigger { color: #E2E8F0; }
    [data-bs-theme="dark"] .kfaq-trigger[aria-expanded="true"] { color: #A5B4FC; }
    [data-bs-theme="dark"] .kfaq-body { color: #94A3B8; }

    /* ---- Map ---- */
    .kontak-map { margin-top: 40px; }
    .kontak-map-card {
        background: rgba(255,255,255,0.5); backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 24px; padding: 24px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.03);
    }
    .kmap-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 8px 16px; background: rgba(239,68,68,0.1);
        border-radius: 10px; font-size: 0.8rem; font-weight: 600; color: #EF4444;
        text-decoration: none; transition: all 0.3s ease;
    }
    .kmap-btn:hover { background: #EF4444; color: #fff; transform: translateY(-1px); }
    .kmap-embed {
        width: 100%; height: 260px; border-radius: 16px; overflow: hidden;
        background: #E2E8F0; position: relative;
    }
    .kmap-placeholder {
        width: 100%; height: 100%; display: flex; flex-direction: column;
        align-items: center; justify-content: center; color: #94A3B8; gap: 8px;
    }
    .kmap-placeholder i { font-size: 36px; }
    .kmap-placeholder p { font-size: 0.85rem; margin-bottom: 0; }
    [data-bs-theme="dark"] .kontak-map-card { background: rgba(30,41,59,0.5); border-color: rgba(255,255,255,0.06); }
    [data-bs-theme="dark"] .kmap-embed { background: #1E293B; }

    /* ---- CTA ---- */
    .kontak-cta { margin-top: 50px; }
    .kontak-cta-inner {
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        border-radius: 28px; padding: 48px 40px;
        display: flex; align-items: center; justify-content: space-between; gap: 30px;
        flex-wrap: wrap; position: relative; overflow: hidden;
        box-shadow: 0 20px 60px rgba(99,102,241,0.25);
    }
    .kontak-cta-inner::before {
        content: ''; position: absolute; top: -60px; right: -60px;
        width: 200px; height: 200px; border-radius: 50%;
        background: rgba(255,255,255,0.08); pointer-events: none;
    }
    .kontak-cta-inner::after {
        content: ''; position: absolute; bottom: -40px; left: -40px;
        width: 150px; height: 150px; border-radius: 50%;
        background: rgba(255,255,255,0.05); pointer-events: none;
    }
    .kontak-cta-content { position: relative; z-index: 1; }
    .kontak-cta-content h3 { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 6px; }
    .kontak-cta-content p { color: rgba(255,255,255,0.85); margin-bottom: 0; font-size: 0.95rem; }
    .kontak-cta-actions { display: flex; gap: 12px; flex-wrap: wrap; position: relative; z-index: 1; }
    .kontak-cta-btn {
        display: inline-flex; align-items: center;
        padding: 12px 24px; border-radius: 14px;
        font-weight: 600; font-size: 0.9rem; text-decoration: none;
        transition: all 0.3s ease; cursor: pointer;
    }
    .kontak-cta-btn.whatsapp { background: #25D366; color: #fff; }
    .kontak-cta-btn.whatsapp:hover { background: #1DA851; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,211,102,0.3); }
    .kontak-cta-btn.login { background: rgba(255,255,255,0.2); color: #fff; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); }
    .kontak-cta-btn.login:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }

    /* ---- Kontak Submit Loader ---- */
    @keyframes kspin { to { transform: rotate(360deg); } }
    .kform-submit.loading { pointer-events: none; opacity: 0.7; }
    .kform-submit.loading .kbtn-text { visibility: hidden; }
    .kform-submit.loading::after {
        content: ''; position: absolute; width: 20px; height: 20px;
        border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
        border-radius: 50%; animation: kspin 0.6s linear infinite;
        top: 50%; left: 50%; margin: -10px 0 0 -10px;
    }
    .kform-submit { position: relative; }

    /* ---- Responsive ---- */
    @media (max-width: 992px) {
        .kontak-cta-inner { padding: 36px 28px; flex-direction: column; text-align: center; }
        .kontak-cta-actions { justify-content: center; }
    }
    @media (max-width: 768px) {
        .kontak-section { padding: 60px 0 50px; }
        .kontak-form-card { padding: 24px; }
        .kontak-cta-inner { padding: 32px 24px; }
        .kontak-cta-content h3 { font-size: 1.3rem; }
        .kmap-embed { height: 200px; }
    }
    @media (max-width: 576px) {
        .kontak-form-card { border-radius: 18px; padding: 20px; }
        .kform-submit { font-size: 0.88rem; }
        .kontak-cta-inner { border-radius: 20px; padding: 28px 20px; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---- Google Maps Lazy Load ----
        var mapContainer = document.getElementById('kmapContainer');
        if (mapContainer) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var iframe = document.createElement('iframe');
                        iframe.setAttribute('src', 'https://www.google.com/maps?q=<?= urlencode($kontakAlamat) ?>&output=embed');
                        iframe.setAttribute('width', '100%');
                        iframe.setAttribute('height', '100%');
                        iframe.setAttribute('style', 'border:0;position:absolute;inset:0');
                        iframe.setAttribute('allowfullscreen', '');
                        iframe.setAttribute('loading', 'lazy');
                        iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
                        mapContainer.innerHTML = '';
                        mapContainer.style.position = 'relative';
                        mapContainer.appendChild(iframe);
                        observer.disconnect();
                    }
                });
            }, { rootMargin: '200px' });
            observer.observe(mapContainer);
        }

        // ---- File Input Label ----
        var fileInput = document.getElementById('klampiran');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                var placeholder = this.closest('.kform-file-wrap').querySelector('.kform-file-placeholder span');
                if (this.files && this.files[0]) {
                    placeholder.textContent = this.files[0].name;
                } else {
                    placeholder.textContent = 'Klik atau seret file ke sini';
                }
            });
        }

        // ---- Contact Form Submit ----
        var kontakForm = document.getElementById('kontakForm');
        if (kontakForm) {
            kontakForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate required fields
                var nama = document.getElementById('kname').value.trim();
                var email = document.getElementById('kemail').value.trim();
                var subjek = document.getElementById('ksubjek').value.trim();
                var pesan = document.getElementById('kpesan').value.trim();
                var privacy = document.getElementById('kprivacy').checked;

                if (!nama || !email || !subjek || !pesan) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Mohon Dilengkapi',
                        text: 'Harap isi semua field yang bertanda (*)',
                        confirmButtonColor: '#6366F1'
                    });
                    return;
                }

                if (!privacy) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kebijakan Privasi',
                        text: 'Harap setujui kebijakan privasi sebelum mengirim',
                        confirmButtonColor: '#6366F1'
                    });
                    return;
                }

                var submitBtn = document.getElementById('kontakSubmit');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                var formData = new FormData(this);

                fetch('ajax/kirim_pesan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pesan Berhasil Dikirim',
                            text: 'Terima kasih telah menghubungi kami. Pesan Anda akan segera ditindaklanjuti.',
                            confirmButtonColor: '#6366F1',
                            confirmButtonText: 'Tutup'
                        });
                        kontakForm.reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim Pesan',
                            text: data.message || 'Terjadi kesalahan, silakan coba lagi.',
                            confirmButtonColor: '#6366F1'
                        });
                    }
                })
                .catch(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim Pesan',
                        text: 'Terjadi kesalahan jaringan, silakan coba lagi.',
                        confirmButtonColor: '#6366F1'
                    });
                })
                .finally(function() {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                });
            });
        }
    });
    </script>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-brand fw-bold mb-3">
                        <?php if ($brand && $brand['logo']): ?>
                        <img src="assets/img/<?= $brand['logo'] ?>" alt="<?= $brand['nama_website'] ?>" loading="lazy" style="height:30px;width:auto" class="me-2">
                        <?php elseif ($footerData && $footerData['logo_footer']): ?>
                        <img src="assets/img/landing/<?= $footerData['logo_footer'] ?>" alt="Logo" loading="lazy" style="height:30px;width:auto" class="me-2">
                        <?php else: ?>
                        <i class="fas fa-school text-primary me-2"></i>
                        <?php endif; ?>
                        <?= $brand['nama_website'] ?? ($footerData['nama_sekolah'] ?? 'APSS') ?>
                    </h5>
                    <?php if ($brand && $brand['tagline']): ?>
                    <p class="fw-medium text-white-50 mb-2" style="font-size:14px"><?= $brand['tagline'] ?></p>
                    <?php endif; ?>
                    <p><?= $setting['deskripsi'] ?></p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3">Kontak</h5>
                    <p class="mb-2"><i class="fas fa-map-marker-alt me-2" style="color:#38BDF8"></i><?= $footerData['alamat'] ?? $setting['alamat'] ?? '-' ?></p>
                    <p class="mb-2"><i class="fas fa-phone me-2" style="color:#38BDF8"></i><?= $footerData['no_telepon'] ?? $setting['telepon'] ?? '-' ?></p>
                    <p class="mb-2"><i class="fas fa-envelope me-2" style="color:#38BDF8"></i><?= $footerData['email'] ?? $setting['email'] ?? '-' ?></p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3">Link Cepat</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="#home">Home</a>
                        <a href="#tentang">Tentang</a>
                        <a href="#fitur">Fitur</a>
                        <a href="#demo">Demo</a>
                        <a href="#statistik">Statistik</a>
                        <a href="#testimoni">Testimoni</a>
                        <a href="#faq">FAQ</a>
                        <a href="#kontak">Kontak</a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3">Sosial Media</h5>
                    <?php if ($footerData && ($footerData['instagram'] || $footerData['facebook'] || $footerData['youtube'])): ?>
                    <div>
                        <?php if ($footerData['instagram']): ?>
                        <a href="<?= $footerData['instagram'] ?>" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if ($footerData['facebook']): ?>
                        <a href="<?= $footerData['facebook'] ?>" class="social-link" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if ($footerData['youtube']): ?>
                        <a href="<?= $footerData['youtube'] ?>" class="social-link" target="_blank"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <p class="mb-0">Ikuti kami di media sosial</p>
                    <?php endif; ?>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="copyright mb-0"><?= $footerData['copyright'] ?? $setting['footer'] ?></p>
            </div>
        </div>
    </footer>

    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery.min.js"></script>
    <script src="assets/vendor/sweetalert2.min.js"></script>
    <script src="assets/vendor/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        const isMobile = window.innerWidth < 768;
        AOS.init({
            duration: isMobile ? 400 : 800,
            easing: 'ease-in-out',
            once: true,
            disable: window.innerWidth < 576
        });
    </script>
</body>
</html>