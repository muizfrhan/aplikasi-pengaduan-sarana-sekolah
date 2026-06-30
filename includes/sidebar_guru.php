<?php
require_once __DIR__ . '/../config/database.php';
$page = $_GET['page'] ?? 'dashboard';
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
            <span class="badge bg-success">Guru</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-menu-header">Menu Utama</div>

        <a href="index.php?page=dashboard" class="sidebar-link <?= $page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-menu-header">Pengaduan</div>

        <a href="index.php?page=pengaduan" class="sidebar-link <?= $page === 'pengaduan' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i>
            <span>Data Pengaduan</span>
            <?php $newCount = hitung('pengaduan', "status='menunggu'"); ?>
            <?php if ($newCount > 0): ?>
            <span class="badge bg-danger ms-auto"><?= $newCount ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-menu-header">Testimoni</div>

        <a href="index.php?page=testimoni" class="sidebar-link <?= $page === 'testimoni' ? 'active' : '' ?>">
            <i class="fas fa-comment-dots"></i>
            <span>Kelola Testimoni</span>
            <?php $pendingTestimoni = hitung('testimonials', "status='pending'"); ?>
            <?php if ($pendingTestimoni > 0): ?>
            <span class="badge bg-warning ms-auto"><?= $pendingTestimoni ?></span>
            <?php endif; ?>
        </a>

        <a href="index.php?page=pesan-kontak" class="sidebar-link <?= $page === 'pesan-kontak' ? 'active' : '' ?>">
            <i class="fas fa-headset"></i>
            <span>Pesan Kontak</span>
            <?php $pesanBaru = @hitung('kontak_messages', "status='pending'"); ?>
            <?php if ($pesanBaru > 0): ?>
            <span class="badge bg-warning ms-auto"><?= $pesanBaru ?></span>
            <?php endif; ?>
        </a>

        <div class="sidebar-menu-header">Laporan</div>

        <div class="sidebar-dropdown">
            <a href="#laporanMenu" class="sidebar-link <?= $page === 'laporan' || $page === 'export-template' ? 'active' : '' ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?= $page === 'laporan' || $page === 'export-template' ? 'true' : 'false' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
                <i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
            </a>
            <div class="collapse <?= $page === 'laporan' || $page === 'export-template' ? 'show' : '' ?>" id="laporanMenu">
                <a href="index.php?page=laporan" class="sidebar-link sidebar-sub-link <?= $page === 'laporan' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar sub-icon"></i>
                    <span>Data Laporan</span>
                </a>
                <a href="index.php?page=export-template" class="sidebar-link sidebar-sub-link <?= $page === 'export-template' ? 'active' : '' ?>">
                    <i class="fas fa-file-export sub-icon"></i>
                    <span>Template Export</span>
                </a>
            </div>
        </div>

        <div class="sidebar-menu-header">Pengaturan</div>

        <a href="index.php?page=profil" class="sidebar-link <?= $page === 'profil' ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i>
            <span>Profil</span>
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

<style>
.sidebar-dropdown .sidebar-link[data-bs-toggle="collapse"] .sidebar-chevron {
    transition: transform 0.3s ease;
    font-size: 12px;
}
.sidebar-dropdown .sidebar-link[data-bs-toggle="collapse"][aria-expanded="true"] .sidebar-chevron {
    transform: rotate(90deg);
}
.sidebar-sub-link {
    padding-left: 46px !important;
    font-size: 13px;
    transition: all 0.2s ease-in-out;
    position: relative;
}
.sidebar-sub-link .sub-icon {
    width: 20px !important;
    text-align: center;
    font-size: 15px;
    transition: transform 0.2s ease-in-out;
}
.sidebar-sub-link:hover {
    background: rgba(255,255,255,0.08);
}
.sidebar-sub-link:hover .sub-icon {
    transform: scale(1.15);
}
.sidebar-sub-link.active {
    background: var(--bs-primary) !important;
    color: #fff !important;
}
.sidebar-sub-link.active .sub-icon {
    color: #fff !important;
}
.sidebar-sub-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 18px;
    background: #fff;
    border-radius: 0 3px 3px 0;
}
.sidebar-dropdown .collapse {
    transition: all 0.3s ease-in-out;
}
</style>
