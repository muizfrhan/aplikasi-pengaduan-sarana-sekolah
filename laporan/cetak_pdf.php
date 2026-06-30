<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../login.php');
}

$s = get_export_settings();
$app = get_setting();

$id = (int)($_GET['id'] ?? 0);
$isDetail = false;

if ($id > 0) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'guru') {
        $p = fetch(query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id WHERE p.id = ?", [$id]));
    } else {
        $p = fetch(query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id WHERE p.id = ? AND p.user_id = ?", [$id, $_SESSION['user_id']]));
    }
    if ($p) $isDetail = true;
}

$tm = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$ts = $_GET['tanggal_selesai'] ?? date('Y-m-t');

if (!$isDetail && !$id) {
    $where = "WHERE DATE(p.created_at) BETWEEN ? AND ?";
    $params = [$tm, $ts];
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'guru') { $where .= " AND p.user_id = ?"; $params[] = $_SESSION['user_id']; }
    if (!empty($_GET['kategori'])) { $where .= " AND p.kategori_id = ?"; $params[] = (int)$_GET['kategori']; }
    if (!empty($_GET['status'])) { $where .= " AND p.status = ?"; $params[] = $_GET['status']; }
    if (!empty($_GET['ruangan'])) { $where .= " AND p.ruangan_id = ?"; $params[] = (int)$_GET['ruangan']; }
    $data = query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id $where ORDER BY p.created_at DESC", $params);
    $allRows = [];
    $total = 0; $menunggu = 0; $diproses = 0; $selesai = 0; $ditolak = 0;
    while ($row = fetch($data)) {
        $allRows[] = $row; $total++;
        switch ($row['status']) { case 'menunggu': $menunggu++; break; case 'diproses': $diproses++; break; case 'selesai': $selesai++; break; case 'ditolak': $ditolak++; break; }
    }
}
$no_laporan = 'LAP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
$admin_name = $_SESSION['nama_lengkap'] ?? 'Admin';
$font_family = $s['font_family'] ?: 'Inter, Arial, sans-serif';
$font_size = (int)($s['font_size'] ?: 10);
$header_color = $s['header_color'] ?: '#1E293B';
$footer_color = $s['footer_color'] ?: '#94A3B8';
$table_color = $s['table_color'] ?: '#1E293B';
$show_page_numbers = $s['show_page_numbers'] ?: 'Y';
$show_date_printed = $s['show_date_printed'] ?: 'Y';
$show_time_printed = $s['show_time_printed'] ?: 'Y';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengaduan - <?= $s['app_name'] ?: $app['nama_aplikasi'] ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pdf-header: <?= $header_color ?>;
            --pdf-table-header: <?= $table_color ?>;
            --pdf-footer: <?= $footer_color ?>;
            --pdf-primary: <?= $header_color ?>;
            --pdf-secondary: <?= $header_color ?>;
            --pdf-success: #22C55E;
            --pdf-warning: #F59E0B;
            --pdf-danger: #EF4444;
            --pdf-info: #3B82F6;
        }
        @page { size: A4 portrait; margin: 20mm 20mm 25mm 20mm; }
        <?php if ($show_page_numbers === 'Y'): ?>
        @page { @bottom-center { content: "Halaman " counter(page); font-size: 8px; color: var(--pdf-footer); font-family: '<?= $font_family ?>', 'DejaVu Sans', Arial, sans-serif; } }
        <?php endif; ?>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: '<?= $font_family ?>', 'DejaVu Sans', Arial, sans-serif; font-size: <?= $font_size ?>px; line-height: 1.6; color: var(--pdf-header); background: #fff; position: relative; }
        <?php if ($s['watermark'] === 'Y' && $s['watermark_text']): ?>
        .watermark { position: fixed; top: 50%; left: 50%; font-weight: 800; color: var(--pdf-primary); letter-spacing: 20px; z-index: 0; pointer-events: none; white-space: nowrap; transform: translate(-50%,-50%) rotate(-30deg); font-size: 120px; opacity: 0.04; }
        <?php endif; ?>
        .content { position: relative; z-index: 1; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 3px solid var(--pdf-primary); position: relative; }
        .header::after { content: ''; position: absolute; bottom: -3px; left: 0; width: 80px; height: 3px; background: var(--pdf-success); border-radius: 0 2px 2px 0; }
        .header-left { display: flex; align-items: flex-start; gap: 14px; }
        .logo-img { width: 55px; height: 55px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
        .logo-placeholder { width: 55px; height: 55px; border-radius: 12px; background: linear-gradient(135deg, var(--pdf-primary), var(--pdf-secondary)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
        .school-info h1 { font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 2px; letter-spacing: -0.3px; }
        .school-info .app-name { font-size: 11px; font-weight: 600; color: var(--pdf-primary); margin: 0 0 1px; }
        .school-info .address { font-size: 8.5px; color: var(--pdf-footer); line-height: 1.4; }
        .header-right { text-align: right; flex-shrink: 0; }
        .meta-table { font-size: 8.5px; border-collapse: collapse; }
        .meta-table td { padding: 1px 0; color: #475569; }
        .meta-table td:first-child { color: var(--pdf-footer); padding-right: 6px; }
        .meta-table td:last-child { font-weight: 600; color: var(--pdf-header); }
        .report-title { text-align: center; margin-bottom: 18px; padding: 14px 20px; background: linear-gradient(135deg, #F8FAFC, #EFF6FF); border-radius: 12px; border: 1px solid #E2E8F0; }
        .report-title h2 { font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 4px; letter-spacing: 0.5px; text-transform: uppercase; }
        .report-title p { font-size: 9.5px; color: #64748B; margin: 0; }
        .report-title .periode { display: inline-block; margin-top: 6px; padding: 3px 14px; background: var(--pdf-primary); color: #fff; border-radius: 20px; font-size: 9px; font-weight: 600; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .stat-card { padding: 14px 12px; border-radius: 12px; display: flex; align-items: center; gap: 12px; }
        .stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .stat-info { flex: 1; }
        .stat-info .stat-label { font-size: 9px; font-weight: 500; margin: 0 0 2px; }
        .stat-info .stat-value { font-size: 22px; font-weight: 800; line-height: 1.1; margin: 0; }
        .stat-total { background: linear-gradient(135deg, #EFF6FF, #DBEAFE); border: 1px solid #BFDBFE; }
        .stat-total .stat-icon { background: linear-gradient(135deg, var(--pdf-primary), var(--pdf-secondary)); color: #fff; }
        .stat-total .stat-label { color: #1E40AF; }
        .stat-total .stat-value { color: #1E3A8A; }
        .stat-processed { background: linear-gradient(135deg, #FFF7ED, #FFEDD5); border: 1px solid #FED7AA; }
        .stat-processed .stat-icon { background: linear-gradient(135deg, var(--pdf-warning), #D97706); color: #fff; }
        .stat-processed .stat-label { color: #92400E; }
        .stat-processed .stat-value { color: #78350F; }
        .stat-completed { background: linear-gradient(135deg, #F0FDF4, #DCFCE7); border: 1px solid #BBF7D0; }
        .stat-completed .stat-icon { background: linear-gradient(135deg, var(--pdf-success), #16A34A); color: #fff; }
        .stat-completed .stat-label { color: #166534; }
        .stat-completed .stat-value { color: #14532D; }
        .stat-rejected { background: linear-gradient(135deg, #FEF2F2, #FEE2E2); border: 1px solid #FECACA; }
        .stat-rejected .stat-icon { background: linear-gradient(135deg, var(--pdf-danger), #DC2626); color: #fff; }
        .stat-rejected .stat-label { color: #991B1B; }
        .stat-rejected .stat-value { color: #7F1D1D; }
        .table-wrapper { border-radius: 10px; border: 1px solid #E2E8F0; overflow: hidden; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: var(--pdf-table-header); color: #fff; padding: 10px 8px; font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border: none; }
        thead th:first-child { padding-left: 12px; }
        thead th:last-child { padding-right: 12px; }
        tbody td { padding: 9px 8px; font-size: 9.5px; color: #334155; border-bottom: 1px solid #F1F5F9; }
        tbody td:first-child { padding-left: 12px; }
        tbody td:last-child { padding-right: 12px; }
        tbody tr:nth-child(even) { background: #F8FAFC; }
        tbody tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 8.5px; font-weight: 600; letter-spacing: 0.3px; color: #fff; }
        .badge-menunggu { background: var(--pdf-warning); }
        .badge-diproses { background: var(--pdf-info); }
        .badge-selesai { background: var(--pdf-success); }
        .badge-ditolak { background: var(--pdf-danger); }
        .no-data { text-align: center; padding: 30px 20px; color: var(--pdf-footer); font-size: 11px; }
        .no-data .big-icon { font-size: 36px; display: block; margin-bottom: 8px; opacity: 0.4; }
        .detail-section { margin-bottom: 20px; }
        .detail-card { border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .detail-header { padding: 12px 16px; background: linear-gradient(135deg, var(--pdf-header), #334155); color: #fff; }
        .detail-header h3 { font-size: 13px; font-weight: 700; margin: 0; }
        .detail-body { padding: 12px 16px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
        .detail-item { display: flex; padding: 7px 0; border-bottom: 1px solid #F1F5F9; }
        .detail-item:nth-last-child(-n+2) { border-bottom: none; }
        .detail-label { width: 140px; font-weight: 600; color: #64748B; font-size: 9px; flex-shrink: 0; }
        .detail-value { flex: 1; color: var(--pdf-header); font-size: 9.5px; font-weight: 500; }
        .detail-desc { margin-top: 10px; padding-top: 10px; border-top: 1px solid #F1F5F9; }
        .detail-desc .detail-label { margin-bottom: 4px; }
        .detail-desc .detail-value { font-size: 10px; line-height: 1.6; }
        .detail-foto { margin-top: 6px; }
        .detail-foto-img { max-width: 420px; width: 100%; height: auto; border-radius: 10px; border: 1px solid #E2E8F0; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: block; page-break-inside: avoid; }
        .foto-thumb { width: 56px; height: 56px; border-radius: 8px; overflow: hidden; border: 1px solid #E2E8F0; background: #F8FAFC; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .foto-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .qr-section { text-align: right; margin-bottom: 10px; }
        .qr-placeholder { display: inline-block; padding: 6px 14px; border: 1px dashed var(--pdf-primary); border-radius: 6px; font-size: 9px; color: var(--pdf-primary); font-weight: 600; }
        .footer { text-align: center; padding-top: 12px; margin-top: 20px; border-top: 1px solid #E2E8F0; font-size: 8px; color: var(--pdf-footer); }
        .footer p { margin: 1px 0; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 30px; margin-bottom: 20px; page-break-inside: avoid; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .label { font-size: 9px; font-weight: 600; color: #64748B; margin-bottom: 2px; }
        .signature-box .name { font-size: 11px; font-weight: 700; color: #0F172A; margin: 4px 0 2px; }
        .signature-box .nip { font-size: 8.5px; color: var(--pdf-footer); }
        .signature-line { width: 200px; height: 1px; background: #CBD5E1; margin: 50px auto 4px; }
        .signature-img { width: 180px; height: auto; margin: 20px auto 4px; display: block; }
        .filter-info { display: flex; flex-wrap: wrap; gap: 4px; justify-content: center; margin-top: 6px; }
        .filter-badge { display: inline-block; padding: 2px 10px; background: #F1F5F9; border-radius: 10px; font-size: 8px; color: #64748B; }
    </style>
</head>
<body>

<?php if ($s['watermark'] === 'Y' && $s['watermark_text']): ?>
<div class="watermark"><?= htmlspecialchars($s['watermark_text']) ?></div>
<?php endif; ?>

<div class="content">
    <div class="header">
        <div class="header-left">
            <?php if ($s['logo_school']): ?>
            <img src="../assets/img/pdf/<?= $s['logo_school'] ?>" alt="Logo" class="logo-img">
            <?php else: ?>
            <div class="logo-placeholder"><?= substr($s['school_name'] ?: 'SM', 0, 2) ?></div>
            <?php endif; ?>
            <div class="school-info">
                <h1><?= $s['school_name'] ?: $s['app_name'] ?></h1>
                <div class="app-name"><?= $s['app_name'] ?></div>
                <?php if ($s['school_address'] || $s['phone'] || $s['email'] || $s['website']): ?>
                <div class="address">
                    <?php if ($s['school_address']): ?><i class="fas fa-map-marker-alt"></i> <?= $s['school_address'] ?><br><?php endif; ?>
                    <?php if ($s['phone']): ?><i class="fas fa-phone"></i> <?= $s['phone'] ?><?php endif; ?>
                    <?php if ($s['phone'] && $s['email']): ?> &nbsp;|&nbsp; <?php endif; ?>
                    <?php if ($s['email']): ?><i class="fas fa-envelope"></i> <?= $s['email'] ?><?php endif; ?>
                    <?php if (($s['phone'] || $s['email']) && $s['website']): ?> &nbsp;|&nbsp; <?php endif; ?>
                    <?php if ($s['website']): ?><i class="fas fa-globe"></i> <?= $s['website'] ?><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-right">
            <table class="meta-table">
                <tr><td>Tanggal Cetak</td><td>: <?= tgl_indonesia(date('Y-m-d')) ?></td></tr>
                <tr><td>Jam Cetak</td><td>: <?= date('H:i:s') ?></td></tr>
                <tr><td>Nama Admin</td><td>: <?= $admin_name ?></td></tr>
                <tr><td>No. Laporan</td><td>: <?= $no_laporan ?></td></tr>
            </table>
        </div>
    </div>

    <?php if ($isDetail): ?>

    <?php if ($s['qr_code'] === 'Y'): ?>
    <div class="qr-section">
        <span class="qr-placeholder"><i class="fas fa-qrcode"></i> QR: <?= htmlspecialchars($s['qr_content']) ?></span>
    </div>
    <?php endif; ?>

    <div class="detail-section">
        <div class="detail-card">
            <div class="detail-header">
                <h3><i class="fas fa-file-alt"></i> Detail Pengaduan</h3>
            </div>
            <div class="detail-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Kode Pengaduan</span>
                        <span class="detail-value"><?= $p['kode_pengaduan'] ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nama Pelapor</span>
                        <span class="detail-value"><?= $p['nama_pelapor'] ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">NIS</span>
                        <span class="detail-value"><?= $p['nis'] ?? '-' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kelas</span>
                        <span class="detail-value"><?= $p['kelas'] ?? '-' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">No. HP</span>
                        <span class="detail-value"><?= $p['no_hp'] ?? '-' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kategori</span>
                        <span class="detail-value"><?= $p['nama_kategori'] ?? '-' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ruangan</span>
                        <span class="detail-value"><?= $p['nama_ruangan'] ?? '-' ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal</span>
                        <span class="detail-value"><?= tgl_indonesia($p['created_at'], true) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Judul</span>
                        <span class="detail-value"><?= $p['judul'] ?></span>
                    </div>
                </div>
                <div class="detail-desc">
                    <div class="detail-label">Deskripsi</div>
                    <div class="detail-value"><?= nl2br($p['deskripsi']) ?></div>
                </div>
                <?php if ($p['foto']): ?>
                <div class="detail-desc">
                    <div class="detail-label">Foto Bukti</div>
                    <div class="detail-foto">
                        <img src="../assets/upload/<?= $p['foto'] ?>" alt="Foto Pengaduan" class="detail-foto-img">
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($p['komentar_admin']): ?>
                <div class="detail-desc">
                    <div class="detail-label">Komentar Admin</div>
                    <div class="detail-value"><?= nl2br($p['komentar_admin']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php else: ?>

    <?php if ($s['qr_code'] === 'Y'): ?>
    <div class="qr-section">
        <span class="qr-placeholder"><i class="fas fa-qrcode"></i> QR: <?= htmlspecialchars($s['qr_content']) ?></span>
    </div>
    <?php endif; ?>

    <div class="report-title">
        <h2><?= $s['judul_laporan'] ?: 'LAPORAN PENGADUAN SARANA SEKOLAH' ?></h2>
        <p><?= $s['app_name'] ?></p>
        <div class="periode">
            <i class="fas fa-calendar-alt"></i>
            <?= tgl_indonesia($tm) . ' s.d. ' . tgl_indonesia($ts) ?>
        </div>
        <div class="filter-info">
            <?php if (!empty($_GET['kategori'])): $k = fetch(query("SELECT nama_kategori FROM kategori WHERE id = ?", [(int)$_GET['kategori']])); ?>
            <span class="filter-badge"><i class="fas fa-tag"></i> Kategori: <?= $k['nama_kategori'] ?? '-' ?></span>
            <?php endif; ?>
            <?php if (!empty($_GET['status'])): ?>
            <span class="filter-badge"><i class="fas fa-filter"></i> Status: <?= ucfirst($_GET['status']) ?></span>
            <?php endif; ?>
            <?php if (!empty($_GET['ruangan'])): $r = fetch(query("SELECT nama_ruangan FROM ruangan WHERE id = ?", [(int)$_GET['ruangan']])); ?>
            <span class="filter-badge"><i class="fas fa-door-open"></i> Ruangan: <?= $r['nama_ruangan'] ?? '-' ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($s['show_statistics'] === 'Y'): ?>
    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Pengaduan</div>
                <div class="stat-value"><?= $total ?></div>
            </div>
        </div>
        <div class="stat-card stat-processed">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-info">
                <div class="stat-label">Diproses</div>
                <div class="stat-value"><?= $diproses ?></div>
            </div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Selesai</div>
                <div class="stat-value"><?= $selesai ?></div>
            </div>
        </div>
        <div class="stat-card stat-rejected">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Ditolak</div>
                <div class="stat-value"><?= $ditolak ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $columnDefs = [
        ['key' => 'no', 'label' => 'No'],
        ['key' => 'kode', 'label' => 'Kode'],
        ['key' => 'nama', 'label' => 'Nama Pelapor'],
        ['key' => 'nis', 'label' => 'NIS'],
        ['key' => 'kelas', 'label' => 'Kelas'],
        ['key' => 'no_hp', 'label' => 'No. HP'],
        ['key' => 'kategori', 'label' => 'Kategori'],
        ['key' => 'ruangan', 'label' => 'Ruangan'],
        ['key' => 'judul', 'label' => 'Judul'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'foto', 'label' => 'Foto'],
        ['key' => 'tgl_dibuat', 'label' => 'Tanggal'],
    ];
    $colspan = count($columnDefs);
    ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <?php foreach ($columnDefs as $col): ?>
                    <th><?= $col['label'] ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($allRows) > 0): $no = 1; foreach ($allRows as $row): ?>
                <tr>
                    <td style="text-align:center"><?= $no ?></td>
                    <td><span style="font-weight:600;color:var(--pdf-primary)"><?= $row['kode_pengaduan'] ?></span></td>
                    <td><?= $row['nama_pelapor'] ?></td>
                    <td><?= $row['nis'] ?? '-' ?></td>
                    <td><?= $row['kelas'] ?? '-' ?></td>
                    <td><?= $row['no_hp'] ?? '-' ?></td>
                    <td><?= $row['nama_kategori'] ?? '-' ?></td>
                    <td><?= $row['nama_ruangan'] ?? '-' ?></td>
                    <td><?= $row['judul'] ?></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td style="text-align:center"><?= $row['foto'] ? '<div class="foto-thumb"><img src="../assets/upload/'.$row['foto'].'" alt="Foto"></div>' : '-' ?></td>
                    <td><?= tgl_indonesia($row['created_at']) ?></td>
                </tr>
                <?php $no++; endforeach; else: ?>
                <tr>
                    <td colspan="<?= $colspan ?>">
                        <div class="no-data">
                            <span class="big-icon"><i class="fas fa-inbox"></i></span>
                            Tidak ada data pengaduan
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="signature-section">
        <div class="signature-box">
            <div class="label">Mengetahui,</div>
            <div class="label" style="margin-top:2px"><?= $s['principal_title'] ?: 'Kepala Sekolah' ?></div>
            <?php if ($s['signature'] && file_exists('../assets/img/ttd/' . $s['signature'])): ?>
            <img src="../assets/img/ttd/<?= $s['signature'] ?>" alt="Tanda Tangan" class="signature-img">
            <?php else: ?>
            <div class="signature-line"></div>
            <?php endif; ?>
            <div class="name"><?= $s['principal_name'] ?: '____________________' ?></div>
            <div class="nip">NIP. <?= $s['principal_nip'] ?: '____________________' ?></div>
        </div>
        <div class="signature-box">
            <div class="label"><?= $s['teacher_name'] ?: $admin_name ?>,</div>
            <div class="label" style="margin-top:2px"><?= $s['teacher_title'] ?: 'Admin Sistem' ?></div>
            <?php if ($s['stamp'] && file_exists('../assets/img/stamp/' . $s['stamp'])): ?>
            <img src="../assets/img/stamp/<?= $s['stamp'] ?>" alt="Stempel" class="signature-img">
            <?php endif; ?>
            <?php if (!$s['stamp']): ?>
            <div class="signature-line"></div>
            <?php endif; ?>
            <div class="name"><?= $s['teacher_name'] ?: $admin_name ?></div>
            <div class="nip">NIP. <?= $s['teacher_nip'] ?: '____________________' ?></div>
        </div>
    </div>

    <div class="footer">
        <?php
        $copyright = str_replace('%year%', date('Y'), $s['copyright_text'] ?? '');
        if ($copyright) echo '<p>' . $copyright . '</p>';
        ?>
        <p>
            <?php if ($show_date_printed === 'Y'): ?>
            <i class="fas fa-print"></i> Dicetak pada: <?= tgl_indonesia(date('Y-m-d')) ?>
            <?php endif; ?>
            <?php if ($show_time_printed === 'Y'): ?>
            <?php if ($show_date_printed === 'Y'): ?> | <?php endif; ?>
            Jam: <?= date('H:i:s') ?>
            <?php endif; ?>
            <?php if (!$isDetail && isset($allRows)): ?> | Jumlah Data: <?= count($allRows) ?><?php endif; ?>
        </p>
    </div>
</div>

<script>
    window.print();
</script>
</body>
</html>
