<?php
// ============================================================
// Dashboard User
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_user();

$title = 'Dashboard';

$userId = $_SESSION['user_id'];
$totalPengaduan = hitung('pengaduan', "user_id = $userId");
$menunggu = hitung('pengaduan', "user_id = $userId AND status='menunggu'");
$diproses = hitung('pengaduan', "user_id = $userId AND status='diproses'");
$selesai = hitung('pengaduan', "user_id = $userId AND status='selesai'");
$ditolak = hitung('pengaduan', "user_id = $userId AND status='ditolak'");
$unreadMsg = hitung('password_messages', "user_id = $userId AND status_baca='Belum Dibaca'");

$nama = $_SESSION['nama_lengkap'];

$riwayat = query("SELECT p.*, k.nama_kategori, r.nama_ruangan 
                  FROM pengaduan p 
                  LEFT JOIN kategori k ON p.kategori_id = k.id 
                  LEFT JOIN ruangan r ON p.ruangan_id = r.id 
                  WHERE p.user_id = ? 
                  ORDER BY p.created_at DESC LIMIT 5", [$userId]);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>

    <div class="container-fluid px-4 py-4">
        <!-- Welcome Hero Card -->
        <div class="welcome-hero mb-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <div class="welcome-content">
                        <span class="welcome-tag"><i class="fas fa-graduation-cap me-1"></i>Dashboard Siswa</span>
                        <h2 class="welcome-title">Selamat Datang, <?= $nama ?>! <span class="wave-emoji">👋</span></h2>
                        <p class="welcome-desc">Silakan gunakan menu di bawah untuk membuat pengaduan atau melihat
                            perkembangan laporan Anda.</p>
                        <div class="welcome-actions">
                            <a href="buat_pengaduan.php" class="btn-hero btn-hero-primary ripple-btn">
                                <i class="fas fa-plus-circle me-2"></i>Buat Pengaduan
                            </a>
                            <a href="riwayat.php" class="btn-hero btn-hero-secondary">
                                <i class="fas fa-history me-2"></i>Lihat Riwayat
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="welcome-image-area">
                        <div class="img-blob"></div>
                        <div class="img-glass-1"></div>
                        <div class="img-glass-2"></div>
                        <div class="img-grid-pattern"></div>
                        <div class="skl-image-wrap" id="sklTilt">
                            <img src="../assets/img/skl.png" alt="Sekolah" class="skl-image" id="sklImage">
                            <div class="skl-shadow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards - 5 cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="0">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stats-info">
                        <h3 class="counter" data-target="<?= $totalPengaduan ?>">0</h3>
                        <p>Total Pengaduan</p>
                    </div>
                    <div class="stats-trend text-primary">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-info">
                        <h3 class="counter" data-target="<?= $menunggu ?>">0</h3>
                        <p>Menunggu</p>
                    </div>
                    <div class="stats-trend text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stats-info">
                        <h3 class="counter" data-target="<?= $diproses ?>">0</h3>
                        <p>Diproses</p>
                    </div>
                    <div class="stats-trend text-info">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-info">
                        <h3 class="counter" data-target="<?= $selesai ?>">0</h3>
                        <p>Selesai</p>
                    </div>
                    <div class="stats-trend text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-info">
                        <h3 class="counter" data-target="<?= $ditolak ?>">0</h3>
                        <p>Ditolak</p>
                    </div>
                    <div class="stats-trend text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="stats-info">
                        <h3 class="counter" data-target="<?= $unreadMsg ?>">0</h3>
                        <p>Pesan Baru</p>
                    </div>
                    <div class="stats-trend text-info">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="glass-card chart-card" data-aos="fade-up">
                    <div class="card-header-custom d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0" style="font-size:1.125rem"><i
                                class="fas fa-chart-bar me-2 text-primary"></i>Grafik Pengaduan Bulanan</h6>
                        <span class="badge bg-primary" style="font-size:0.75rem">Tahun <?= date('Y') ?></span>
                    </div>
                    <div class="chart-container">
                        <canvas id="chartBulananUser"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="glass-card chart-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header-custom d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0" style="font-size:1.125rem"><i
                                class="fas fa-chart-pie me-2 text-primary"></i>Status Pengaduan</h6>
                    </div>
                    <div class="chart-container">
                        <canvas id="chartStatusUser"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat + Aksi Cepat -->
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="glass-card" data-aos="fade-up">
                    <div class="card-header-custom d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold"><i class="fas fa-history me-2 text-primary"></i>Riwayat Pengaduan</h6>
                        <a href="riwayat.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <?php if (mysqli_num_rows($riwayat) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Ruangan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($r = fetch($riwayat)): ?>
                                <tr>
                                    <td><span class="badge bg-dark"><?= $r['kode_pengaduan'] ?></span></td>
                                    <td><?= potong_teks($r['judul'], 25) ?></td>
                                    <td><?= $r['nama_kategori'] ?? '-' ?></td>
                                    <td><?= $r['nama_ruangan'] ?? '-' ?></td>
                                    <td><small><?= tgl_indonesia($r['created_at']) ?></small></td>
                                    <td><?= status_badge($r['status']) ?></td>
                                    <td>
                                        <a href="riwayat.php?action=detail&id=<?= $r['id'] ?>"
                                            class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p class="mb-2">Belum ada pengaduan</p>
                        <a href="buat_pengaduan.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Buat Pengaduan Sekarang
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-header-custom d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold"><i class="fas fa-bolt me-2 text-primary"></i>Aksi Cepat</h6>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="buat_pengaduan.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Buat Pengaduan
                        </a>
                        <a href="riwayat.php" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i>Riwayat Pengaduan
                        </a>
                        <a href="profil.php" class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i>Edit Profil
                        </a>
                        <a href="../laporan/cetak_pdf.php" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Download Laporan
                        </a>
                    </div>
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Status</h6>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2"
                            style="width:10px;height:10px;border-radius:50%;padding:0">&nbsp;</span>
                        <small class="text-muted">Menunggu proses admin</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2"
                            style="width:10px;height:10px;border-radius:50%;padding:0">&nbsp;</span>
                        <small class="text-muted">Sedang ditangani</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success me-2"
                            style="width:10px;height:10px;border-radius:50%;padding:0">&nbsp;</span>
                        <small class="text-muted">Sudah selesai</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-danger me-2"
                            style="width:10px;height:10px;border-radius:50%;padding:0">&nbsp;</span>
                        <small class="text-muted">Pengaduan ditolak</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- INFORMASI & PANDUAN TAMBAHAN -->
        <!-- ============================================================ -->

        <!-- Row: Panduan Pengguna + Alur Pengaduan -->
        <div class="row g-4 mb-4">
            <!-- Panduan Menggunakan Aplikasi -->
            <div class="col-lg-6">
                <div class="glass-card h-100" data-aos="fade-up">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-book-open text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Panduan Menggunakan Aplikasi</h6>
                    </div>
                    <ol class="guide-list list-unstyled mb-0">
                        <li class="guide-item">
                            <span class="guide-badge bg-primary"><i class="fas fa-check text-white"></i></span>
                            <span>Klik menu <strong>"Buat Pengaduan"</strong></span>
                        </li>
                        <li class="guide-item">
                            <span class="guide-badge bg-primary"><i class="fas fa-check text-white"></i></span>
                            <span>Isi seluruh data dengan lengkap dan benar</span>
                        </li>
                        <li class="guide-item">
                            <span class="guide-badge bg-primary"><i class="fas fa-check text-white"></i></span>
                            <span>Upload foto kerusakan yang jelas</span>
                        </li>
                        <li class="guide-item">
                            <span class="guide-badge bg-primary"><i class="fas fa-check text-white"></i></span>
                            <span>Klik tombol <strong>Kirim Pengaduan</strong></span>
                        </li>
                        <li class="guide-item">
                            <span class="guide-badge bg-warning"><i class="fas fa-hourglass-half text-white"></i></span>
                            <span>Tunggu Admin melakukan verifikasi</span>
                        </li>
                        <li class="guide-item">
                            <span class="guide-badge bg-info"><i class="fas fa-sync-alt text-white"></i></span>
                            <span>Pantau status melalui menu <strong>Riwayat Pengaduan</strong></span>
                        </li>
                        <li class="guide-item">
                            <span class="guide-badge bg-success"><i class="fas fa-check-double text-white"></i></span>
                            <span>Jika pengaduan selesai, lihat komentar Admin</span>
                        </li>
                    </ol>
                </div>
            </div>
            <!-- Alur Pengaduan -->
            <div class="col-lg-6">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-diagram-project text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Alur Pengaduan</h6>
                    </div>
                    <!-- Horizontal Timeline (Desktop) -->
                    <div class="timeline-horizontal d-none d-md-flex">
                        <div class="tl-step">
                            <div class="tl-icon bg-primary shadow-sm"><i class="fas fa-edit text-white"></i></div>
                            <div class="tl-label">Buat Pengaduan</div>
                        </div>
                        <div class="tl-arrow"><i class="fas fa-arrow-down text-primary"></i></div>
                        <div class="tl-step">
                            <div class="tl-icon bg-info shadow-sm"><i class="fas fa-paper-plane text-white"></i></div>
                            <div class="tl-label">Kirim Laporan</div>
                        </div>
                        <div class="tl-arrow"><i class="fas fa-arrow-down text-primary"></i></div>
                        <div class="tl-step">
                            <div class="tl-icon bg-warning shadow-sm"><i class="fas fa-search text-white"></i></div>
                            <div class="tl-label">Admin Verifikasi</div>
                        </div>
                        <div class="tl-arrow"><i class="fas fa-arrow-down text-primary"></i></div>
                        <div class="tl-step">
                            <div class="tl-icon bg-secondary shadow-sm"><i class="fas fa-tools text-white"></i></div>
                            <div class="tl-label">Sedang Diproses</div>
                        </div>
                        <div class="tl-arrow"><i class="fas fa-arrow-down text-primary"></i></div>
                        <div class="tl-step">
                            <div class="tl-icon bg-success shadow-sm"><i class="fas fa-check-circle text-white"></i>
                            </div>
                            <div class="tl-label">Pengaduan Selesai</div>
                        </div>
                    </div>
                    <!-- Vertical Timeline (Mobile) -->
                    <div class="timeline-vertical d-flex d-md-none">
                        <div class="tv-step">
                            <div class="tv-dot bg-primary"></div>
                            <div class="tv-content">Buat Pengaduan</div>
                        </div>
                        <div class="tv-step">
                            <div class="tv-dot bg-info"></div>
                            <div class="tv-content">Kirim Laporan</div>
                        </div>
                        <div class="tv-step">
                            <div class="tv-dot bg-warning"></div>
                            <div class="tv-content">Admin Verifikasi</div>
                        </div>
                        <div class="tv-step">
                            <div class="tv-dot bg-secondary"></div>
                            <div class="tv-content">Sedang Diproses</div>
                        </div>
                        <div class="tv-step">
                            <div class="tv-dot bg-success"></div>
                            <div class="tv-content">Pengaduan Selesai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row: Informasi Penting + Status + Tips -->
        <div class="row g-4 mb-4">
            <!-- Informasi Penting -->
            <div class="col-md-4">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="0">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-info-circle text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Informasi Penting</h6>
                    </div>
                    <ul class="info-list list-unstyled mb-0">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Pastikan data yang dimasukkan benar
                        </li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Foto harus jelas dan tidak buram</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Maksimal ukuran foto 2 MB</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Format foto JPG, JPEG, PNG atau WEBP
                        </li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Pengaduan palsu akan ditolak</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Gunakan bahasa yang sopan</li>
                    </ul>
                </div>
            </div>
            <!-- Penjelasan Status -->
            <div class="col-md-4">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-traffic-light text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Penjelasan Status</h6>
                    </div>
                    <div class="status-legend">
                        <div class="status-item">
                            <span class="badge bg-warning status-dot"></span>
                            <div>
                                <strong>Menunggu</strong>
                                <small class="text-muted d-block">Pengaduan sedang menunggu verifikasi Admin</small>
                            </div>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-info status-dot"></span>
                            <div>
                                <strong>Diproses</strong>
                                <small class="text-muted d-block">Pengaduan sedang ditangani</small>
                            </div>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-success status-dot"></span>
                            <div>
                                <strong>Selesai</strong>
                                <small class="text-muted d-block">Pengaduan telah selesai diperbaiki</small>
                            </div>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-danger status-dot"></span>
                            <div>
                                <strong>Ditolak</strong>
                                <small class="text-muted d-block">Pengaduan tidak dapat diproses</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tips -->
            <div class="col-md-4">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="200">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-lightbulb text-warning fs-5"></i>
                        <h6 class="fw-bold mb-0">Tips Pengaduan yang Baik</h6>
                    </div>
                    <ul class="tips-list list-unstyled mb-0">
                        <li><i class="fas fa-check-circle text-warning me-2"></i>Judul jelas dan deskriptif</li>
                        <li><i class="fas fa-check-circle text-warning me-2"></i>Foto tidak buram atau gelap</li>
                        <li><i class="fas fa-check-circle text-warning me-2"></i>Pilih kategori yang sesuai</li>
                        <li><i class="fas fa-check-circle text-warning me-2"></i>Pilih ruangan yang benar</li>
                        <li><i class="fas fa-check-circle text-warning me-2"></i>Jelaskan kerusakan secara detail</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Row: Aksi Cepat -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="glass-card" data-aos="fade-up">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-bolt text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Aksi Cepat</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="buat_pengaduan.php" class="quick-action btn btn-primary w-100">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                <span>Buat Pengaduan</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="riwayat.php" class="quick-action btn btn-info text-white w-100">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <span>Riwayat Pengaduan</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="pesan_password.php"
                                class="quick-action btn btn-secondary text-white w-100 <?= $unreadMsg > 0 ? 'position-relative' : '' ?>">
                                <i class="fas fa-envelope-open-text fa-2x mb-2"></i>
                                <span>Pesan Password</span>
                                <?php if ($unreadMsg > 0): ?>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadMsg ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="profil.php" class="quick-action btn btn-success text-white w-100">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <span>Profil Saya</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="../laporan/cetak_pdf.php" class="quick-action btn btn-secondary text-white w-100">
                                <i class="fas fa-download fa-2x mb-2"></i>
                                <span>Download Laporan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row: FAQ + Kontak -->
        <div class="row g-4 mb-4">
            <!-- FAQ -->
            <div class="col-lg-8">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="0">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-question-circle text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Pertanyaan Umum (FAQ)</h6>
                    </div>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq1">
                                    Bagaimana cara membuat pengaduan?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Klik menu <strong>"Buat Pengaduan"</strong> di sidebar atau tombol Aksi Cepat di
                                    atas. Isi formulir dengan lengkap, upload foto, lalu klik Kirim.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq2">
                                    Kapan pengaduan saya diproses?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Admin akan memverifikasi pengaduan Anda dalam 1x24 jam. Proses perbaikan tergantung
                                    pada tingkat kerusakan dan ketersediaan sarana.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq3">
                                    Apakah bisa mengubah pengaduan yang sudah dikirim?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pengaduan yang sudah dikirim tidak dapat diubah. Jika ada kesalahan, silakan hubungi
                                    Admin melalui menu Kontak atau buat pengaduan baru.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq4">
                                    Mengapa pengaduan saya ditolak?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pengaduan dapat ditolak jika data tidak lengkap, foto tidak jelas, kategori/ruangan
                                    tidak sesuai, atau pengaduan dianggap tidak valid.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kontak -->
            <div class="col-lg-4">
                <div class="glass-card h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-headset text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0">Kontak Bantuan</h6>
                    </div>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon bg-primary-subtle rounded-circle">
                                <i class="fas fa-user-tie text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted">Admin Sarana Sekolah</small>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon bg-success-subtle rounded-circle">
                                <i class="fas fa-phone text-success"></i>
                            </div>
                            <div>
                                <small class="text-muted">Nomor HP</small>
                                <div class="fw-semibold">-</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon bg-info-subtle rounded-circle">
                                <i class="fas fa-envelope text-info"></i>
                            </div>
                            <div>
                                <small class="text-muted">Email</small>
                                <div class="fw-semibold">-</div>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon bg-warning-subtle rounded-circle">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div>
                                <small class="text-muted">Jam Operasional</small>
                                <div class="fw-semibold">07.00 - 16.00 WIB</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        /* ===== Dashboard User - Custom Components ===== */

        /* --- Guide List --- */
        .guide-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .guide-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            transition: var(--transition);
        }

        .guide-item:hover {
            transform: translateX(6px);
            box-shadow: var(--shadow-hover);
        }

        .guide-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 13px;
        }

        /* --- Info & Tips List --- */
        .info-list li,
        .tips-list li {
            padding: 6px 0;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
        }

        .info-list li i,
        .tips-list li i {
            margin-top: 3px;
            flex-shrink: 0;
        }

        /* --- Status Legend --- */
        .status-legend {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .status-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .status-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            padding: 0;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .status-item strong {
            font-size: 14px;
        }

        .status-item small {
            font-size: 12px;
        }

        /* --- Quick Actions --- */
        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            border: none;
        }

        .quick-action:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        /* --- Horizontal Timeline --- */
        .timeline-horizontal {
            flex-direction: column;
            align-items: center;
            gap: 0;
            padding: 10px 0;
        }

        .tl-step {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
        }

        .tl-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .tl-label {
            font-size: 14px;
            font-weight: 500;
        }

        .tl-arrow {
            text-align: center;
            padding: 4px 0 4px 22px;
            font-size: 14px;
            opacity: 0.6;
        }

        .tl-arrow i {
            transform: rotate(0deg);
        }

        /* --- Vertical Timeline (Mobile) --- */
        .timeline-vertical {
            flex-direction: column;
            gap: 0;
            padding-left: 20px;
            position: relative;
        }

        .timeline-vertical::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 10px;
            bottom: 10px;
            width: 2px;
            background: var(--border);
            opacity: 0.3;
        }

        .tv-step {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 0;
            position: relative;
        }

        .tv-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            flex-shrink: 0;
            z-index: 1;
        }

        .tv-content {
            font-size: 14px;
            font-weight: 500;
        }

        /* --- Contact Info --- */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 14px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            transition: var(--transition);
        }

        .contact-item:hover {
            transform: translateX(4px);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .contact-icon i {
            font-size: 16px;
        }

        /* --- Accordion Overrides for Glass Theme --- */
        .accordion-item {
            background: transparent;
            border: 1px solid var(--glass-border);
            border-radius: 12px !important;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .accordion-item:first-of-type,
        .accordion-item:last-of-type {
            border-radius: 12px !important;
        }

        .accordion-button {
            background: var(--glass-bg);
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            padding: 14px 18px;
            box-shadow: none !important;
        }

        .accordion-button:not(.collapsed) {
            background: var(--glass-bg);
            color: var(--primary);
        }

        .accordion-button::after {
            background-size: 14px;
        }

        .accordion-body {
            padding: 14px 18px;
            font-size: 13px;
            color: var(--text-muted);
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
        }

        /* --- Welcome Hero Card --- */
        .welcome-hero {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px 44px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
            animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes heroFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .welcome-hero::before {
            content: '';
            position: absolute;
            top: -60%;
            left: -10%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, transparent 70%);
            pointer-events: none;
            animation: heroBlob 7s ease-in-out infinite;
        }

        .welcome-hero::after {
            content: '';
            position: absolute;
            bottom: -40%;
            right: 30%;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);
            pointer-events: none;
            animation: heroBlob 9s ease-in-out infinite reverse;
        }

        @keyframes heroBlob {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -20px) scale(1.05);
            }

            66% {
                transform: translate(-20px, 15px) scale(0.95);
            }
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 16px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(124, 58, 237, 0.08));
            border: 1px solid rgba(37, 99, 235, 0.15);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 18px;
            letter-spacing: 0.3px;
        }

        [data-bs-theme="dark"] .welcome-tag {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.1));
            border-color: rgba(59, 130, 246, 0.2);
        }

        .welcome-title {
            font-size: 30px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 10px;
            line-height: 1.25;
        }

        .wave-emoji {
            display: inline-block;
            animation: waveHand 2.5s ease-in-out infinite;
            transform-origin: 70% 70%;
        }

        @keyframes waveHand {

            0%,
            100% {
                transform: rotate(0deg);
            }

            20% {
                transform: rotate(14deg);
            }

            40% {
                transform: rotate(-8deg);
            }

            60% {
                transform: rotate(6deg);
            }

            80% {
                transform: rotate(-3deg);
            }
        }

        .welcome-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
            max-width: 480px;
            line-height: 1.7;
        }

        /* --- Buttons --- */
        .welcome-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-hero {
            display: inline-flex;
            align-items: center;
            padding: 11px 24px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, #2563EB, #7C3AED);
            color: #fff;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.4);
            color: #fff;
        }

        .btn-hero-primary:active {
            transform: translateY(-1px) scale(0.98);
        }

        .btn-hero-secondary {
            background: var(--glass-bg);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-hero-secondary:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.1);
        }

        /* --- Ripple Effect --- */
        .ripple-btn {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .ripple-btn .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: rippleAnim 0.6s ease-out;
            pointer-events: none;
        }

        @keyframes rippleAnim {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* --- Image Area --- */
        .welcome-image-area {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
            z-index: 1;
        }

        .img-blob {
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(124, 58, 237, 0.12));
            filter: blur(40px);
            animation: blobFloat 6s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes blobFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(15px, -15px) scale(1.08);
            }
        }

        .img-glass-1 {
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            top: 5%;
            right: 5%;
            animation: glassDrift 8s ease-in-out infinite;
            z-index: 0;
        }

        .img-glass-2 {
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            bottom: 10%;
            left: 5%;
            animation: glassDrift 10s ease-in-out infinite reverse;
            z-index: 0;
        }

        @keyframes glassDrift {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(10px, -10px);
            }
        }

        .img-grid-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(37, 99, 235, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.03) 1px, transparent 1px);
            background-size: 24px 24px;
            border-radius: 16px;
            z-index: 0;
            opacity: 0.5;
        }

        /* --- Image Wrapper --- */
        .skl-image-wrap {
            position: relative;
            z-index: 1;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-style: preserve-3d;
            perspective: 800px;
            animation: sklEntry 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both,
                sklFloat 5s ease-in-out 1s infinite;
        }

        @keyframes sklEntry {
            from {
                opacity: 0;
                transform: scale(0.5) rotate(-10deg);
            }

            to {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes sklFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .skl-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            position: relative;
            z-index: 2;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow:
                0 20px 60px rgba(37, 99, 235, 0.2),
                0 0 0 4px rgba(255, 255, 255, 0.06),
                0 0 0 8px rgba(37, 99, 235, 0.04);
            will-change: transform;
            backface-visibility: hidden;
        }

        .skl-image-wrap:hover .skl-image {
            transform: scale(1.03);
            box-shadow:
                0 30px 80px rgba(37, 99, 235, 0.3),
                0 0 0 4px rgba(255, 255, 255, 0.08),
                0 0 0 10px rgba(37, 99, 235, 0.06);
        }

        .skl-shadow {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 70%;
            height: 20px;
            background: radial-gradient(ellipse, rgba(0, 0, 0, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            animation: shadowPulse 5s ease-in-out 1s infinite;
        }

        @keyframes shadowPulse {

            0%,
            100% {
                opacity: 0.6;
                transform: translateX(-50%) scale(1);
            }

            50% {
                opacity: 0.3;
                transform: translateX(-50%) scale(0.85);
            }
        }

        /* --- Dark mode overrides --- */
        [data-bs-theme="dark"] .skl-image {
            box-shadow:
                0 20px 60px rgba(59, 130, 246, 0.15),
                0 0 0 4px rgba(255, 255, 255, 0.03),
                0 0 0 8px rgba(59, 130, 246, 0.02);
        }

        [data-bs-theme="dark"] .skl-image-wrap:hover .skl-image {
            box-shadow:
                0 30px 80px rgba(59, 130, 246, 0.25),
                0 0 0 4px rgba(255, 255, 255, 0.05),
                0 0 0 10px rgba(59, 130, 246, 0.04);
        }

        [data-bs-theme="dark"] .img-blob {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(139, 92, 246, 0.1));
        }

        [data-bs-theme="dark"] .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: var(--text);
        }

        [data-bs-theme="dark"] .btn-hero-secondary:hover {
            background: rgba(59, 130, 246, 0.12);
            border-color: var(--primary);
        }

        .welcome-hero .row {
            position: relative;
            z-index: 1;
        }

        /* --- Dark mode overrides for new components --- */
        [data-bs-theme="dark"] .guide-item,
        [data-bs-theme="dark"] .contact-item {
            background: rgba(255, 255, 255, 0.03);
        }

        [data-bs-theme="dark"] .accordion-button {
            background: rgba(255, 255, 255, 0.03);
        }

        [data-bs-theme="dark"] .accordion-body {
            background: rgba(0, 0, 0, 0.15);
        }

        [data-bs-theme="dark"] .contact-icon.bg-primary-subtle {
            background: rgba(59, 130, 246, 0.15) !important;
        }

        [data-bs-theme="dark"] .contact-icon.bg-success-subtle {
            background: rgba(16, 185, 129, 0.15) !important;
        }

        [data-bs-theme="dark"] .contact-icon.bg-info-subtle {
            background: rgba(56, 189, 248, 0.15) !important;
        }

        [data-bs-theme="dark"] .contact-icon.bg-warning-subtle {
            background: rgba(245, 158, 11, 0.15) !important;
        }

        /* --- Responsive --- */
        @media (max-width: 992px) {
            .welcome-hero {
                padding: 32px 28px;
            }

            .welcome-title {
                font-size: 26px;
            }

            .welcome-desc {
                max-width: 100%;
            }

            .skl-image-wrap {
                width: 180px;
                height: 180px;
            }

            .img-blob {
                width: 220px;
                height: 220px;
            }
        }

        @media (max-width: 768px) {
            .welcome-hero {
                padding: 28px 24px;
            }

            .welcome-title {
                font-size: 24px;
            }

            .skl-image-wrap {
                width: 160px;
                height: 160px;
            }

            .img-blob {
                width: 200px;
                height: 200px;
            }

            .welcome-image-area {
                min-height: 160px;
            }

            .btn-hero {
                padding: 10px 20px;
                font-size: 13px;
            }
        }

        @media (max-width: 576px) {
            .welcome-hero {
                padding: 24px 18px;
                border-radius: 18px;
            }

            .welcome-title {
                font-size: 22px;
            }

            .welcome-desc {
                font-size: 13px;
                margin-bottom: 18px;
            }

            .welcome-tag {
                font-size: 11px;
                padding: 4px 12px;
            }

            .welcome-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn-hero {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
            }

            .welcome-image-area {
                margin-top: 8px;
                min-height: 140px;
            }

            .skl-image-wrap {
                width: 140px;
                height: 140px;
            }

            .img-blob {
                width: 170px;
                height: 170px;
            }

            .img-glass-1 {
                width: 70px;
                height: 70px;
            }

            .img-glass-2 {
                width: 45px;
                height: 45px;
            }

            .guide-item {
                padding: 8px 12px;
            }

            .quick-action {
                padding: 14px 8px;
                font-size: 12px;
            }

            .quick-action i {
                font-size: 1.5rem !important;
            }

            .tl-icon {
                width: 36px;
                height: 36px;
                font-size: 15px;
            }
        }

        @media (max-width: 375px) {
            .welcome-hero {
                padding: 20px 14px;
                border-radius: 16px;
            }

            .welcome-title {
                font-size: 20px;
            }

            .skl-image-wrap {
                width: 120px;
                height: 120px;
            }

            .img-blob {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 320px) {
            .welcome-hero {
                padding: 16px 12px;
            }

            .welcome-title {
                font-size: 18px;
            }

            .welcome-desc {
                font-size: 12px;
            }

            .btn-hero {
                font-size: 12px;
                padding: 10px 16px;
            }

            .skl-image-wrap {
                width: 100px;
                height: 100px;
            }

            .img-blob {
                width: 130px;
                height: 130px;
            }

            .img-glass-1 {
                display: none;
            }

            .img-glass-2 {
                display: none;
            }
        }
        </style>

    </div>
</div>

<script>
<?php
    $bulanData = [];
    for ($i = 1; $i <= 12; $i++) {
        $bulan = str_pad($i, 2, '0', STR_PAD_LEFT);
        $result = query("SELECT COUNT(*) as total FROM pengaduan WHERE user_id = ? AND DATE_FORMAT(created_at, '%m') = ? AND DATE_FORMAT(created_at, '%Y') = ?", [$userId, $bulan, date('Y')]);
        $row = fetch($result);
        $bulanData[] = (int)$row['total'];
    }
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    ?>
const chartLabels = <?= json_encode($namaBulan) ?>;
const chartData = <?= json_encode($bulanData) ?>;
const statusLabels = ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'];
const statusColors = ['#F59E0B', '#38BDF8', '#10B981', '#EF4444'];
const statusBorderColors = ['#D97706', '#0284C7', '#059669', '#DC2626'];

window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') return;

    new Chart(document.getElementById('chartBulananUser').getContext('2d'), {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Jumlah Pengaduan',
                data: chartData,
                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                borderColor: '#2563EB',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(37, 99, 235, 0.6)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: {
                        size: 12,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.y + ' Pengaduan';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            hover: {
                mode: 'index',
                intersect: false
            }
        }
    });

    new Chart(document.getElementById('chartStatusUser').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: [<?= $menunggu ?>, <?= $diproses ?>, <?= $selesai ?>, <?= $ditolak ?>],
                backgroundColor: statusColors,
                borderColor: statusBorderColors,
                borderWidth: 2,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            animation: {
                animateRotate: true,
                duration: 800,
                easing: 'easeInOutQuart'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        font: {
                            size: 12
                        },
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: {
                        size: 12,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a, b) {
                                return a + b;
                            }, 0);
                            var value = ctx.parsed;
                            var pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return ctx.label + ': ' + value + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
});

// --- 3D Tilt Effect on School Image ---
(function() {
    var wrap = document.getElementById('sklTilt');
    if (!wrap) return;
    var img = wrap.querySelector('.skl-image');
    if (!img) return;
    var rect, cx, cy;

    function onEnter() {
        rect = wrap.getBoundingClientRect();
        cx = rect.left + rect.width / 2;
        cy = rect.top + rect.height / 2;
    }

    function onMove(e) {
        var x, y;
        if (e.touches) {
            x = e.touches[0].clientX;
            y = e.touches[0].clientY;
        } else {
            x = e.clientX;
            y = e.clientY;
        }
        var dx = (x - cx) / (rect.width / 2);
        var dy = (y - cy) / (rect.height / 2);
        var rotX = -dy * 12;
        var rotY = dx * 12;
        img.style.transform = 'perspective(800px) rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg) scale(1.03)';
    }

    function onLeave() {
        img.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) scale(1)';
    }
    wrap.addEventListener('mouseenter', onEnter);
    wrap.addEventListener('mousemove', onMove);
    wrap.addEventListener('mouseleave', onLeave);
    wrap.addEventListener('touchstart', function(e) {
        onEnter();
        onMove(e);
    });
    wrap.addEventListener('touchmove', onMove);
    wrap.addEventListener('touchend', onLeave);
})();

// --- Ripple Effect on Buttons ---
(function() {
    var btns = document.querySelectorAll('.ripple-btn');
    btns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var ripple = document.createElement('span');
            ripple.className = 'ripple-effect';
            var rect = btn.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left - size / 2;
            var y = (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top - size / 2;
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            btn.appendChild(ripple);
            setTimeout(function() {
                ripple.remove();
            }, 600);
        });
    });
})();
</script>

<?php include '../includes/footer.php'; ?>