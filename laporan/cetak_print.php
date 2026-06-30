<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../login.php');
}

$s = get_export_settings();
$app = get_setting();

$tm = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$ts = $_GET['tanggal_selesai'] ?? date('Y-m-t');

$where = "WHERE DATE(p.created_at) BETWEEN ? AND ?";
$params = [$tm, $ts];
if (!empty($_GET['kategori'])) { $where .= " AND p.kategori_id = ?"; $params[] = (int)$_GET['kategori']; }
if (!empty($_GET['status'])) { $where .= " AND p.status = ?"; $params[] = $_GET['status']; }
if (!empty($_GET['ruangan'])) { $where .= " AND p.ruangan_id = ?"; $params[] = (int)$_GET['ruangan']; }

$data = query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id $where ORDER BY p.created_at DESC", $params);

$allRows = []; $total = 0; $menunggu = 0; $diproses = 0; $selesai = 0; $ditolak = 0;
while ($row = fetch($data)) {
    $allRows[] = $row; $total++;
    switch ($row['status']) { case 'menunggu': $menunggu++; break; case 'diproses': $diproses++; break; case 'selesai': $selesai++; break; case 'ditolak': $ditolak++; break; }
}

$columnDefs = [
    ['key'=>'no','label'=>'No'],
    ['key'=>'kode','label'=>'Kode'],
    ['key'=>'nama','label'=>'Nama Pelapor'],
    ['key'=>'nis','label'=>'NIS'],
    ['key'=>'kelas','label'=>'Kelas'],
    ['key'=>'no_hp','label'=>'No. HP'],
    ['key'=>'kategori','label'=>'Kategori'],
    ['key'=>'ruangan','label'=>'Ruangan'],
    ['key'=>'judul','label'=>'Judul'],
    ['key'=>'status','label'=>'Status'],
    ['key'=>'foto','label'=>'Foto'],
    ['key'=>'tgl_dibuat','label'=>'Tanggal'],
];
$colspan = count($columnDefs);
$header_color = $s['header_color'] ?: '#1E293B';
$footer_color = $s['footer_color'] ?: '#94A3B8';
$table_color = $s['table_color'] ?: '#1E293B';
$show_statistics = $s['show_statistics'] ?: 'Y';
$show_date_printed = $s['show_date_printed'] ?: 'Y';
$show_time_printed = $s['show_time_printed'] ?: 'Y';
$admin_name = $_SESSION['nama_lengkap'] ?? 'Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Print Laporan - <?= $s['app_name'] ?: $app['nama_aplikasi'] ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --p-primary: <?= $header_color ?>;
            --p-table-header: <?= $table_color ?>;
            --p-footer: <?= $footer_color ?>;
            --p-success: #22C55E;
            --p-warning: #F59E0B;
            --p-danger: #EF4444;
            --p-info: #3B82F6;
        }
        @page { margin: 15mm; }
        body { font-family: '<?= $s['font_family'] ?: 'Inter, Arial, sans-serif' ?>', Arial, sans-serif; font-size: <?= $s['font_size'] ?: 12 ?>px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--p-primary); }
        .header h1 { margin: 0; font-size: 18px; color: #0F172A; }
        .header p { margin: 3px 0; font-size: 11px; color: #666; }
        .report-title { text-align: center; margin-bottom: 15px; }
        .report-title h2 { font-size: 16px; font-weight: bold; margin: 0 0 5px; text-transform: uppercase; }
        .report-title .periode { display: inline-block; padding: 4px 16px; background: var(--p-primary); color: #fff; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .stats { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-box { padding: 10px 16px; border-radius: 8px; text-align: center; min-width: 100px; }
        .stat-box .val { font-size: 20px; font-weight: bold; }
        .stat-box .lbl { font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 7px 8px; border: 1px solid #ddd; text-align: left; font-size: 10px; }
        th { background: var(--p-table-header); color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; color: #fff; }
        .badge-menunggu { background: var(--p-warning); }
        .badge-diproses { background: var(--p-info); }
        .badge-selesai { background: var(--p-success); }
        .badge-ditolak { background: var(--p-danger); }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .label { font-size: 11px; font-weight: 600; color: #666; }
        .signature-line { width: 200px; height: 1px; background: #CBD5E1; margin: 50px auto 4px; }
        .signature-box .name { font-size: 12px; font-weight: bold; margin: 4px 0 2px; }
        .signature-box .nip { font-size: 10px; color: #999; }
        .text-center { text-align: center; }
        @media print {
            .no-print { display: none; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center;margin-bottom:20px;">
        <button onclick="window.print()" style="padding:10px 30px;background:var(--p-primary);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;">
            <i class="fas fa-print"></i> Cetak / Print
        </button>
    </div>

    <div class="header">
        <?php if ($s['logo_school']): ?>
        <img src="../assets/img/pdf/<?= $s['logo_school'] ?>" alt="Logo" style="height:50px;margin-bottom:8px;">
        <?php endif; ?>
        <h1><?= $s['school_name'] ?: $s['app_name'] ?></h1>
        <?php if ($s['school_address'] || $s['phone'] || $s['email']): ?>
        <p><?= $s['school_address'] ?: '' ?><?= $s['school_address'] && $s['phone'] ? ' | ' : '' ?><?= $s['phone'] ? 'Telp: '.$s['phone'] : '' ?><?= ($s['school_address'] || $s['phone']) && $s['email'] ? ' | ' : '' ?><?= $s['email'] ? 'Email: '.$s['email'] : '' ?></p>
        <?php endif; ?>
    </div>

    <div class="report-title">
        <h2><?= $s['judul_laporan'] ?: 'Laporan Pengaduan' ?></h2>
        <div class="periode">
            <?= tgl_indonesia($tm) ?> - <?= tgl_indonesia($ts) ?>
        </div>
    </div>

    <?php if ($show_statistics === 'Y'): ?>
    <div class="stats">
        <div class="stat-box" style="background:#EFF6FF">
            <div class="val" style="color:#1E3A8A"><?= $total ?></div>
            <div class="lbl">Total</div>
        </div>
        <div class="stat-box" style="background:#FFF7ED">
            <div class="val" style="color:#78350F"><?= $diproses ?></div>
            <div class="lbl">Diproses</div>
        </div>
        <div class="stat-box" style="background:#F0FDF4">
            <div class="val" style="color:#14532D"><?= $selesai ?></div>
            <div class="lbl">Selesai</div>
        </div>
        <div class="stat-box" style="background:#FEF2F2">
            <div class="val" style="color:#7F1D1D"><?= $ditolak ?></div>
            <div class="lbl">Ditolak</div>
        </div>
    </div>
    <?php endif; ?>

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
                <?php foreach ($columnDefs as $col): ?>
                <td>
                    <?php
                    switch ($col['key']) {
                        case 'no': echo $no; break;
                        case 'kode': echo $row['kode_pengaduan']; break;
                        case 'nama': echo $row['nama_pelapor']; break;
                        case 'nis': echo $row['nis'] ?? '-'; break;
                        case 'kelas': echo $row['kelas'] ?? '-'; break;
                        case 'no_hp': echo $row['no_hp'] ?? '-'; break;
                        case 'kategori': echo $row['nama_kategori'] ?? '-'; break;
                        case 'ruangan': echo $row['nama_ruangan'] ?? '-'; break;
                        case 'judul': echo $row['judul']; break;
                        case 'status': echo '<span class="badge badge-' . $row['status'] . '">' . ucfirst($row['status']) . '</span>'; break;
                        case 'foto': echo $row['foto'] ? '<i class="fas fa-image" style="color:var(--p-primary)"></i>' : '-'; break;
                        case 'tgl_dibuat': echo tgl_indonesia($row['created_at']); break;
                    }
                    ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php $no++; endforeach; ?>
            <tr style="font-weight:bold;background:#e8f0fe">
                <td colspan="<?= $colspan ?>" style="text-align:right">Total Data: <?= count($allRows) ?> Pengaduan</td>
            </tr>
            <?php else: ?>
            <tr><td colspan="<?= $colspan ?>" class="text-center">Tidak ada data laporan</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div class="label">Mengetahui,</div>
            <div class="label" style="margin-top:2px"><?= $s['principal_title'] ?: 'Kepala Sekolah' ?></div>
            <div class="signature-line"></div>
            <div class="name"><?= $s['principal_name'] ?: '____________________' ?></div>
            <div class="nip">NIP. <?= $s['principal_nip'] ?: '____________________' ?></div>
        </div>
        <div class="signature-box">
            <div class="label"><?= $s['teacher_name'] ?: $admin_name ?>,</div>
            <div class="label" style="margin-top:2px"><?= $s['teacher_title'] ?: 'Admin Sistem' ?></div>
            <div class="signature-line"></div>
            <div class="name"><?= $s['teacher_name'] ?: $admin_name ?></div>
            <div class="nip">NIP. <?= $s['teacher_nip'] ?: '____________________' ?></div>
        </div>
    </div>

    <div class="footer">
        <p><?= str_replace('%year%', date('Y'), $s['copyright_text'] ?? '') ?></p>
        <p>
            Dicetak pada: <?= tgl_indonesia(date('Y-m-d H:i:s'), true) ?>
            | Jumlah Data: <?= count($allRows) ?>
        </p>
    </div>
</body>
</html>
