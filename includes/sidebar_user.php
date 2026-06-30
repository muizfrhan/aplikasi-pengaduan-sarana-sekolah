<?php
require_once __DIR__ . '/../config/database.php';
$current_page = basename($_SERVER['PHP_SELF']);
$userId = $_SESSION['user_id'] ?? 0;
$pendingCount = hitung('pengaduan', "user_id = $userId AND status='menunggu'");
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-branding">
            <img src="../assets/img/apss_logo.png" alt="Aplikasi Pengaduan Sarana Sekolah" class="sidebar-logo-img" aria-label="Aplikasi Pengaduan Sarana Sekolah">
            <div class="sidebar-brand-text">
                <div class="sidebar-brand-title">Aplikasi Pengaduan<br>Sarana Sekolah</div>
                <div class="sidebar-brand-sub">Sistem Pelaporan Digital</div>
            </div>
        </div>
        <button class="sidebar-toggler d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <img src="../assets/img/<?= $_SESSION['foto'] ?? 'default.png' ?>" alt="Avatar">
        </div>
        <div class="sidebar-user-info">
            <h6><?= $_SESSION['nama_lengkap'] ?></h6>
            <span class="badge bg-success">User</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-menu-header">Menu Utama</div>

        <a href="index.php" class="sidebar-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-menu-header">Pengaduan</div>

        <a href="buat_pengaduan.php" class="sidebar-link <?= $current_page === 'buat_pengaduan.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Buat Pengaduan</span>
        </a>

        <a href="riwayat.php" class="sidebar-link <?= $current_page === 'riwayat.php' ? 'active' : '' ?>">
            <i class="fas fa-history"></i>
            <span>Riwayat Pengaduan</span>
            <?php if ($pendingCount > 0): ?>
            <span class="badge bg-warning ms-auto"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-menu-header">Testimoni</div>

        <a href="buat_testimoni.php" class="sidebar-link <?= $current_page === 'buat_testimoni.php' ? 'active' : '' ?>">
            <i class="fas fa-comment-dots"></i>
            <span>Kirim Testimoni</span>
        </a>

        <a href="riwayat_testimoni.php" class="sidebar-link <?= $current_page === 'riwayat_testimoni.php' ? 'active' : '' ?>">
            <i class="fas fa-history"></i>
            <span>Riwayat Testimoni</span>
        </a>

        <div class="sidebar-menu-header">Pesan</div>

        <a href="pesan_password.php" class="sidebar-link <?= $current_page === 'pesan_password.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope-open-text"></i>
            <span>Pesan Password</span>
            <?php $unreadMsg = hitung('password_messages', "user_id = $userId AND status_baca='Belum Dibaca'"); ?>
            <?php if ($unreadMsg > 0): ?>
            <span class="badge bg-danger ms-auto"><?= $unreadMsg ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-menu-header">Pengaturan</div>

        <a href="profil.php" class="sidebar-link <?= $current_page === 'profil.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>

        <a href="reset_password.php" class="sidebar-link <?= $current_page === 'reset_password.php' ? 'active' : '' ?>">
            <i class="fas fa-key"></i>
            <span>Reset Password</span>
        </a>

        <div class="sidebar-menu-header">Aksi</div>

        <a href="../logout.php" class="sidebar-link text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="dark-mode-toggle">
            <i class="fas fa-moon"></i>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="darkModeToggle">
            </div>
            <i class="fas fa-sun"></i>
        </div>
    </div>
</nav>