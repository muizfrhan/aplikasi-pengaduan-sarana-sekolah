<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../login.php');
}

$s = get_export_settings();
$app = get_setting();

$show_date_printed = $s['show_date_printed'] ?: 'Y';
$show_time_printed = $s['show_time_printed'] ?: 'Y';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Pengaduan_' . date('YmdHis') . '.xls"');
header('Cache-Control: max-age=0');

$tm = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$ts = $_GET['tanggal_selesai'] ?? date('Y-m-t');

$where = "WHERE DATE(p.created_at) BETWEEN ? AND ?";
$params = [$tm, $ts];
if (!empty($_GET['kategori'])) { $where .= " AND p.kategori_id = ?"; $params[] = (int)$_GET['kategori']; }
if (!empty($_GET['status'])) { $where .= " AND p.status = ?"; $params[] = $_GET['status']; }
if (!empty($_GET['ruangan'])) { $where .= " AND p.ruangan_id = ?"; $params[] = (int)$_GET['ruangan']; }

$data = query("SELECT p.*, k.nama_kategori, r.nama_ruangan FROM pengaduan p LEFT JOIN kategori k ON p.kategori_id = k.id LEFT JOIN ruangan r ON p.ruangan_id = r.id $where ORDER BY p.created_at DESC", $params);

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
    ['key' => 'deskripsi', 'label' => 'Deskripsi'],
    ['key' => 'status', 'label' => 'Status'],
    ['key' => 'komentar', 'label' => 'Komentar'],
    ['key' => 'foto', 'label' => 'Foto'],
    ['key' => 'tgl_dibuat', 'label' => 'Tanggal'],
    ['key' => 'updated', 'label' => 'Diupdate'],
];
$table_color = $s['table_color'] ?: '#1E293B';
$colspan = count($columnDefs);
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Laporan Pengaduan</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table { border-collapse: collapse; font-family: <?= $s['font_family'] ?: 'Arial, sans-serif' ?>; font-size: <?= $s['font_size'] ?: 11 ?>px; }
        th { background: <?= $table_color ?>; color: white; padding: 8px 10px; border: 1px solid #ccc; text-align: center; }
        td { padding: 6px 10px; border: 1px solid #ccc; }
        .title { font-size: 14px; font-weight: bold; text-align: center; }
        .subtitle { font-size: 10px; text-align: center; color: #666; margin-bottom: 10px; }
        .footer-text { font-size: 9px; text-align: center; color: #999; margin-top: 10px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="<?= $colspan ?>" class="title"><?= $s['school_name'] ?: $s['app_name'] ?></td>
        </tr>
        <tr>
            <td colspan="<?= $colspan ?>" class="subtitle">
                <?= $s['judul_laporan'] ?: 'Laporan Pengaduan' ?> | <?= tgl_indonesia($tm) ?> - <?= tgl_indonesia($ts) ?>
            </td>
        </tr>
        <tr>
            <?php foreach ($columnDefs as $col): ?>
            <th><?= $col['label'] ?></th>
            <?php endforeach; ?>
        </tr>
        <?php if (mysqli_num_rows($data) > 0): $no = 1;
            while ($row = fetch($data)): ?>
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
                    case 'deskripsi': echo strip_tags($row['deskripsi'] ?? ''); break;
                    case 'status': echo ucfirst($row['status']); break;
                    case 'komentar': echo strip_tags($row['komentar_admin'] ?? '') ?: '-'; break;
                    case 'foto': echo $row['foto'] ?: '-'; break;
                    case 'tgl_dibuat': echo tgl_indonesia($row['created_at']); break;
                    case 'updated': echo $row['updated_at'] ? tgl_indonesia($row['updated_at'], true) : '-'; break;
                }
                ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php $no++; endwhile; else: ?>
        <tr><td colspan="<?= $colspan ?>" style="text-align:center">Tidak ada data</td></tr>
        <?php endif; ?>
        <tr>
            <td colspan="<?= $colspan ?>" class="footer-text">
                <?= str_replace('%year%', date('Y'), $s['copyright_text'] ?? '') ?>
                <?php if ($show_date_printed === 'Y'): ?> | Dicetak: <?= tgl_indonesia(date('Y-m-d')) ?><?php endif; ?>
                <?php if ($show_time_printed === 'Y'): ?> | <?= date('H:i:s') ?><?php endif; ?>
            </td>
        </tr>
    </table>
</body>
</html>
