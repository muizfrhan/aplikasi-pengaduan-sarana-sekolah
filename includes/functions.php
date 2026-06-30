<?php
// ============================================================
// Fungsi-fungsi tambahan
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('../login.php');
        exit;
    }
}

// Cek role admin/guru
function cek_admin() {
    cek_login();
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'guru') {
        redirect('../index.php');
        exit;
    }
}

// Cek role user
function cek_user() {
    cek_login();
    if ($_SESSION['role'] !== 'user') {
        redirect('../admin/index.php');
        exit;
    }
}

// Format nominal
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Potong teks
function potong_teks($teks, $limit = 50) {
    if (strlen($teks) > $limit) {
        return substr($teks, 0, $limit) . '...';
    }
    return $teks;
}

// Generate random color
function random_color() {
    $colors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];
    return $colors[array_rand($colors)];
}

// Get status badge
function status_badge($status) {
    $badges = [
        'menunggu' => 'bg-warning text-dark',
        'diproses' => 'bg-info text-white',
        'selesai' => 'bg-success text-white',
        'ditolak' => 'bg-danger text-white'
    ];
    $icons = [
        'menunggu' => 'fas fa-clock',
        'diproses' => 'fas fa-spinner',
        'selesai' => 'fas fa-check-circle',
        'ditolak' => 'fas fa-times-circle'
    ];
    $label = ucfirst($status);
    $badgeClass = $badges[$status] ?? 'bg-secondary';
    $icon = $icons[$status] ?? 'fas fa-circle';
    return "<span class='badge $badgeClass'><i class='$icon me-1'></i>$label</span>";
}

// Get status progress
function status_progress($status) {
    $steps = ['menunggu', 'diproses', 'selesai'];
    $index = array_search($status, $steps);
    $current = array_search($status, $steps);
    if ($status === 'ditolak') {
        $current = 0;
    }
    
    $html = '<div class="status-progress">';
    foreach ($steps as $i => $step) {
        $isActive = $i <= $current;
        $isCurrent = $i === $current;
        $class = $isActive ? 'active' : '';
        $class .= $isCurrent ? ' current' : '';
        
        $icons = ['fas fa-clock', 'fas fa-spinner', 'fas fa-check-circle'];
        $labels = ['Menunggu', 'Diproses', 'Selesai'];
        
        $html .= "<div class='step $class'>";
        $html .= "<div class='step-icon'><i class='{$icons[$i]}'></i></div>";
        $html .= "<div class='step-label'>{$labels[$i]}</div>";
        $html .= "</div>";
        
        if ($i < count($steps) - 1) {
            $html .= "<div class='step-line " . ($i < $current ? 'active' : '') . "'></div>";
        }
    }
    $html .= '</div>';
    
    if ($status === 'ditolak') {
        $html .= "<div class='text-danger mt-2'><i class='fas fa-times-circle me-1'></i>Pengaduan Ditolak</div>";
    }
    
    return $html;
}

// Get export settings (single unified template)
function get_export_settings(): array {
    $defaults = [
        'school_name' => 'SMK ....',
        'app_name' => 'Aplikasi Pengaduan Sarana Sekolah',
        'judul_laporan' => 'LAPORAN PENGADUAN SARANA SEKOLAH',
        'school_address' => '',
        'phone' => '',
        'email' => '',
        'website' => '',
        'logo_school' => null,
        'logo_app' => null,
        'principal_name' => '',
        'principal_nip' => '',
        'principal_title' => 'Kepala Sekolah',
        'teacher_name' => '',
        'teacher_nip' => '',
        'teacher_title' => 'Admin Sistem',
        'signature' => null,
        'stamp' => null,
        'header_color' => '#1E293B',
        'footer_color' => '#94A3B8',
        'table_color' => '#1E293B',
        'font_family' => 'Inter, Arial, sans-serif',
        'font_size' => 10,
        'logo_position' => 'left',
        'signature_position' => 'bottom',
        'watermark' => 'N',
        'watermark_text' => '',
        'show_statistics' => 'Y',
        'show_page_numbers' => 'Y',
        'show_date_printed' => 'Y',
        'show_time_printed' => 'Y',
        'copyright_text' => 'Copyright &copy; %year% Aplikasi Pengaduan Sarana Sekolah',
        'qr_code' => 'N',
        'qr_content' => 'url',
    ];
    $s = fetch(query("SELECT * FROM export_settings WHERE id = 1"));
    if ($s) {
        foreach ($defaults as $key => $val) {
            if (isset($s[$key]) && $s[$key] !== null && $s[$key] !== '') {
                $defaults[$key] = $s[$key];
            }
        }
    }
    return $defaults;
}
