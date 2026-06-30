<?php
$sekolah = fetch(query("SELECT * FROM pdf_sekolah WHERE id=1"));
if (!$sekolah) {
    execute("INSERT INTO pdf_sekolah (id) VALUES (1)");
    $sekolah = fetch(query("SELECT * FROM pdf_sekolah WHERE id=1"));
}

$header = fetch(query("SELECT * FROM pdf_header WHERE id=1"));
if (!$header) {
    execute("INSERT INTO pdf_header (id) VALUES (1)");
    $header = fetch(query("SELECT * FROM pdf_header WHERE id=1"));
}

$tabel = fetch(query("SELECT * FROM pdf_tabel WHERE id=1"));
if (!$tabel) {
    execute("INSERT INTO pdf_tabel (id) VALUES (1)");
    $tabel = fetch(query("SELECT * FROM pdf_tabel WHERE id=1"));
}

$warna = fetch(query("SELECT * FROM pdf_warna WHERE id=1"));
if (!$warna) {
    execute("INSERT INTO pdf_warna (id) VALUES (1)");
    $warna = fetch(query("SELECT * FROM pdf_warna WHERE id=1"));
}

$statistik = fetch(query("SELECT * FROM pdf_statistik WHERE id=1"));
if (!$statistik) {
    execute("INSERT INTO pdf_statistik (id) VALUES (1)");
    $statistik = fetch(query("SELECT * FROM pdf_statistik WHERE id=1"));
}

$footer = fetch(query("SELECT * FROM pdf_footer WHERE id=1"));
if (!$footer) {
    execute("INSERT INTO pdf_footer (id) VALUES (1)");
    $footer = fetch(query("SELECT * FROM pdf_footer WHERE id=1"));
}

$ttd = fetch(query("SELECT * FROM pdf_ttd WHERE id=1"));
if (!$ttd) {
    execute("INSERT INTO pdf_ttd (id) VALUES (1)");
    $ttd = fetch(query("SELECT * FROM pdf_ttd WHERE id=1"));
}

$watermark = fetch(query("SELECT * FROM pdf_watermark WHERE id=1"));
if (!$watermark) {
    execute("INSERT INTO pdf_watermark (id) VALUES (1)");
    $watermark = fetch(query("SELECT * FROM pdf_watermark WHERE id=1"));
}

$qr = fetch(query("SELECT * FROM pdf_qr WHERE id=1"));
if (!$qr) {
    execute("INSERT INTO pdf_qr (id) VALUES (1)");
    $qr = fetch(query("SELECT * FROM pdf_qr WHERE id=1"));
}

$tab = bersihkan($_GET['tab'] ?? 'sekolah');

if (isset($_POST['simpan_sekolah'])) {
    $logo = $_POST['logo_lama'] ?? '';

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['logo'], '../assets/img/pdf/');
        if ($upload['status']) {
            if ($logo && file_exists('../assets/img/pdf/' . $logo)) {
                unlink('../assets/img/pdf/' . $logo);
            }
            $logo = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    $nama_sekolah = bersihkan($_POST['nama_sekolah']);
    $nama_aplikasi = bersihkan($_POST['nama_aplikasi']);
    $judul_laporan = bersihkan($_POST['judul_laporan']);
    $alamat = bersihkan($_POST['alamat']);
    $telepon = bersihkan($_POST['telepon']);
    $email = bersihkan($_POST['email']);
    $website = bersihkan($_POST['website']);
    $kota = bersihkan($_POST['kota']);
    $provinsi = bersihkan($_POST['provinsi']);
    $status = bersihkan($_POST['status']);
    $kepala_sekolah = bersihkan($_POST['kepala_sekolah']);
    $nip_kepala = bersihkan($_POST['nip_kepala']);
    $admin = bersihkan($_POST['admin']);
    $nip_admin = bersihkan($_POST['nip_admin']);

    execute("UPDATE pdf_sekolah SET logo=?, nama_sekolah=?, nama_aplikasi=?, judul_laporan=?, alamat=?, telepon=?, email=?, website=?, kota=?, provinsi=?, status=?, kepala_sekolah=?, nip_kepala=?, admin=?, nip_admin=? WHERE id=1", [$logo, $nama_sekolah, $nama_aplikasi, $judul_laporan, $alamat, $telepon, $email, $website, $kota, $provinsi, $status, $kepala_sekolah, $nip_kepala, $admin, $nip_admin]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate identitas sekolah PDF");
    alert('success', 'Identitas sekolah berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=sekolah');
}

if (isset($_POST['simpan_header'])) {
    $tampil_logo = bersihkan($_POST['tampil_logo']);
    $tampil_nama_sekolah = bersihkan($_POST['tampil_nama_sekolah']);
    $tampil_alamat = bersihkan($_POST['tampil_alamat']);
    $tampil_nomor_laporan = bersihkan($_POST['tampil_nomor_laporan']);
    $tampil_admin = bersihkan($_POST['tampil_admin']);
    $tampil_qr = bersihkan($_POST['tampil_qr']);
    $tampil_watermark = bersihkan($_POST['tampil_watermark']);

    execute("UPDATE pdf_header SET tampil_logo=?, tampil_nama_sekolah=?, tampil_alamat=?, tampil_nomor_laporan=?, tampil_admin=?, tampil_qr=?, tampil_watermark=? WHERE id=1", [$tampil_logo, $tampil_nama_sekolah, $tampil_alamat, $tampil_nomor_laporan, $tampil_admin, $tampil_qr, $tampil_watermark]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan header PDF");
    alert('success', 'Pengaturan header berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=header');
}

if (isset($_POST['simpan_tabel'])) {
    $kolom_no = bersihkan($_POST['kolom_no']);
    $kolom_kode = bersihkan($_POST['kolom_kode']);
    $kolom_nama = bersihkan($_POST['kolom_nama']);
    $kolom_nis = bersihkan($_POST['kolom_nis']);
    $kolom_kelas = bersihkan($_POST['kolom_kelas']);
    $kolom_no_hp = bersihkan($_POST['kolom_no_hp']);
    $kolom_judul = bersihkan($_POST['kolom_judul']);
    $kolom_kategori = bersihkan($_POST['kolom_kategori']);
    $kolom_ruangan = bersihkan($_POST['kolom_ruangan']);
    $kolom_deskripsi = bersihkan($_POST['kolom_deskripsi']);
    $kolom_status = bersihkan($_POST['kolom_status']);
    $kolom_komentar = bersihkan($_POST['kolom_komentar']);
    $kolom_tanggal = bersihkan($_POST['kolom_tanggal']);
    $kolom_foto = bersihkan($_POST['kolom_foto']);
    $kolom_tgl_dibuat = bersihkan($_POST['kolom_tgl_dibuat']);
    $kolom_updated = bersihkan($_POST['kolom_updated']);

    execute("UPDATE pdf_tabel SET kolom_no=?, kolom_kode=?, kolom_nama=?, kolom_nis=?, kolom_kelas=?, kolom_no_hp=?, kolom_judul=?, kolom_kategori=?, kolom_ruangan=?, kolom_deskripsi=?, kolom_status=?, kolom_komentar=?, kolom_tanggal=?, kolom_foto=?, kolom_tgl_dibuat=?, kolom_updated=? WHERE id=1", [$kolom_no, $kolom_kode, $kolom_nama, $kolom_nis, $kolom_kelas, $kolom_no_hp, $kolom_judul, $kolom_kategori, $kolom_ruangan, $kolom_deskripsi, $kolom_status, $kolom_komentar, $kolom_tanggal, $kolom_foto, $kolom_tgl_dibuat, $kolom_updated]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan tabel PDF");
    alert('success', 'Pengaturan tabel berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=tabel');
}

if (isset($_POST['simpan_warna'])) {
    $primary_color = bersihkan($_POST['primary_color']);
    $secondary_color = bersihkan($_POST['secondary_color']);
    $header_color = bersihkan($_POST['header_color']);
    $table_header = bersihkan($_POST['table_header']);
    $footer_color = bersihkan($_POST['footer_color']);
    $badge_success = bersihkan($_POST['badge_success']);
    $badge_warning = bersihkan($_POST['badge_warning']);
    $badge_danger = bersihkan($_POST['badge_danger']);
    $badge_info = bersihkan($_POST['badge_info']);

    execute("UPDATE pdf_warna SET primary_color=?, secondary_color=?, header_color=?, table_header=?, footer_color=?, badge_success=?, badge_warning=?, badge_danger=?, badge_info=? WHERE id=1", [$primary_color, $secondary_color, $header_color, $table_header, $footer_color, $badge_success, $badge_warning, $badge_danger, $badge_info]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan warna PDF");
    alert('success', 'Pengaturan warna berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=warna');
}

if (isset($_POST['simpan_statistik'])) {
    $tampil_total = bersihkan($_POST['tampil_total']);
    $tampil_diproses = bersihkan($_POST['tampil_diproses']);
    $tampil_selesai = bersihkan($_POST['tampil_selesai']);
    $tampil_ditolak = bersihkan($_POST['tampil_ditolak']);
    $tampil_user = bersihkan($_POST['tampil_user']);
    $tampil_ruangan = bersihkan($_POST['tampil_ruangan']);
    $tampil_kategori = bersihkan($_POST['tampil_kategori']);

    execute("UPDATE pdf_statistik SET tampil_total=?, tampil_diproses=?, tampil_selesai=?, tampil_ditolak=?, tampil_user=?, tampil_ruangan=?, tampil_kategori=? WHERE id=1", [$tampil_total, $tampil_diproses, $tampil_selesai, $tampil_ditolak, $tampil_user, $tampil_ruangan, $tampil_kategori]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan statistik PDF");
    alert('success', 'Pengaturan statistik berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=statistik');
}

if (isset($_POST['simpan_footer'])) {
    $copyright = bersihkan($_POST['copyright']);
    $footer_nama_sekolah = bersihkan($_POST['footer_nama_sekolah']);
    $footer_website = bersihkan($_POST['footer_website']);
    $footer_email = bersihkan($_POST['footer_email']);
    $kalimat_footer = bersihkan($_POST['kalimat_footer']);
    $nomor_halaman = bersihkan($_POST['nomor_halaman']);
    $tanggal_cetak = bersihkan($_POST['tanggal_cetak']);
    $jam_cetak = bersihkan($_POST['jam_cetak']);

    execute("UPDATE pdf_footer SET copyright=?, nama_sekolah=?, website=?, email=?, kalimat_footer=?, nomor_halaman=?, tanggal_cetak=?, jam_cetak=? WHERE id=1", [$copyright, $footer_nama_sekolah, $footer_website, $footer_email, $kalimat_footer, $nomor_halaman, $tanggal_cetak, $jam_cetak]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan footer PDF");
    alert('success', 'Pengaturan footer berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=footer');
}

if (isset($_POST['simpan_ttd'])) {
    $tampil_ttd = bersihkan($_POST['tampil_ttd']);
    $ttd1_nama = bersihkan($_POST['ttd1_nama']);
    $ttd1_jabatan = bersihkan($_POST['ttd1_jabatan']);
    $ttd1_nip = bersihkan($_POST['ttd1_nip']);
    $ttd2_nama = bersihkan($_POST['ttd2_nama']);
    $ttd2_jabatan = bersihkan($_POST['ttd2_jabatan']);
    $ttd2_nip = bersihkan($_POST['ttd2_nip']);

    $ttd1_file = $_POST['ttd1_file_lama'] ?? '';
    $ttd2_file = $_POST['ttd2_file_lama'] ?? '';

    if (isset($_FILES['ttd1_file']) && $_FILES['ttd1_file']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['ttd1_file'], '../assets/img/pdf/');
        if ($upload['status']) {
            if ($ttd1_file && file_exists('../assets/img/pdf/' . $ttd1_file)) {
                unlink('../assets/img/pdf/' . $ttd1_file);
            }
            $ttd1_file = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    if (isset($_FILES['ttd2_file']) && $_FILES['ttd2_file']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['ttd2_file'], '../assets/img/pdf/');
        if ($upload['status']) {
            if ($ttd2_file && file_exists('../assets/img/pdf/' . $ttd2_file)) {
                unlink('../assets/img/pdf/' . $ttd2_file);
            }
            $ttd2_file = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    execute("UPDATE pdf_ttd SET tampil_ttd=?, ttd1_nama=?, ttd1_jabatan=?, ttd1_nip=?, ttd1_file=?, ttd2_nama=?, ttd2_jabatan=?, ttd2_nip=?, ttd2_file=? WHERE id=1", [$tampil_ttd, $ttd1_nama, $ttd1_jabatan, $ttd1_nip, $ttd1_file, $ttd2_nama, $ttd2_jabatan, $ttd2_nip, $ttd2_file]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan tanda tangan PDF");
    alert('success', 'Pengaturan tanda tangan berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=ttd');
}

if (isset($_POST['simpan_watermark'])) {
    $wm_aktif = bersihkan($_POST['wm_aktif']);
    $wm_isi = bersihkan($_POST['wm_isi']);
    $wm_opacity = bersihkan($_POST['wm_opacity']);
    $wm_ukuran = bersihkan($_POST['wm_ukuran']);
    $wm_posisi = bersihkan($_POST['wm_posisi']);

    execute("UPDATE pdf_watermark SET aktif=?, isi=?, opacity=?, ukuran=?, posisi=? WHERE id=1", [$wm_aktif, $wm_isi, $wm_opacity, $wm_ukuran, $wm_posisi]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan watermark PDF");
    alert('success', 'Pengaturan watermark berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=watermark');
}

if (isset($_POST['simpan_qr'])) {
    $qr_aktif = bersihkan($_POST['qr_aktif']);
    $qr_isi = bersihkan($_POST['qr_isi']);

    execute("UPDATE pdf_qr SET aktif=?, isi=? WHERE id=1", [$qr_aktif, $qr_isi]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan QR code PDF");
    alert('success', 'Pengaturan QR code berhasil diperbarui!');
    redirect('index.php?page=pdf-template&tab=qr');
}
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Template Laporan</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=laporan">Laporan</a></li>
                <li class="breadcrumb-item active">Template Laporan</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="../laporan/cetak_pdf.php" target="_blank" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i>Preview PDF
        </a>
        <a href="../laporan/cetak_excel.php" target="_blank" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Preview Excel
        </a>
        <a href="../laporan/cetak_print.php" target="_blank" class="btn btn-secondary btn-sm">
            <i class="fas fa-print me-1"></i>Preview Print
        </a>
    </div>
</div>

<div class="glass-card">
    <h6 class="fw-bold mb-4"><i class="fas fa-file-alt me-2 text-primary"></i>Pengaturan Template Laporan</h6>
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle me-2"></i>
        Pengaturan ini berlaku untuk semua format laporan: <strong>PDF</strong>, <strong>Excel</strong>, dan <strong>Print</strong>.
    </div>

    <ul class="nav nav-tabs" id="pdfTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'sekolah' ? 'active' : '' ?>" id="sekolah-tab" data-bs-toggle="tab" data-bs-target="#sekolah" type="button" role="tab">Identitas Sekolah</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'header' ? 'active' : '' ?>" id="header-tab" data-bs-toggle="tab" data-bs-target="#header" type="button" role="tab">Header</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'tabel' ? 'active' : '' ?>" id="tabel-tab" data-bs-toggle="tab" data-bs-target="#tabel" type="button" role="tab">Tabel</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'warna' ? 'active' : '' ?>" id="warna-tab" data-bs-toggle="tab" data-bs-target="#warna" type="button" role="tab">Warna</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'statistik' ? 'active' : '' ?>" id="statistik-tab" data-bs-toggle="tab" data-bs-target="#statistik" type="button" role="tab">Statistik</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'footer' ? 'active' : '' ?>" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer" type="button" role="tab">Footer</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'ttd' ? 'active' : '' ?>" id="ttd-tab" data-bs-toggle="tab" data-bs-target="#ttd" type="button" role="tab">Tanda Tangan</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'watermark' ? 'active' : '' ?>" id="watermark-tab" data-bs-toggle="tab" data-bs-target="#watermark" type="button" role="tab">Watermark</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'qr' ? 'active' : '' ?>" id="qr-tab" data-bs-toggle="tab" data-bs-target="#qr" type="button" role="tab">QR Code</button>
        </li>
    </ul>

    <div class="tab-content pt-4">
        <div class="tab-pane fade <?= $tab === 'sekolah' ? 'show active' : '' ?>" id="sekolah" role="tabpanel">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="logo_lama" value="<?= $sekolah['logo'] ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo Sekolah</label>
                        <div class="mb-2">
                            <?php if ($sekolah['logo']): ?>
                            <img src="../assets/img/pdf/<?= $sekolah['logo'] ?>" alt="Preview" style="width:100px;height:100px;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                            <?php else: ?>
                            <div style="width:100px;height:100px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                                <small class="text-muted"><i class="fas fa-image"></i></small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_logo')">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                        <div id="preview_logo" class="mt-1"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                            <option value="aktif" <?= $sekolah['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $sekolah['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sekolah" class="form-control" value="<?= $sekolah['nama_sekolah'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_aplikasi" class="form-control" value="<?= $sekolah['nama_aplikasi'] ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                    <input type="text" name="judul_laporan" class="form-control" value="<?= $sekolah['judul_laporan'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= $sekolah['alamat'] ?? '' ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="<?= $sekolah['telepon'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $sekolah['email'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="<?= $sekolah['website'] ?? '' ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" class="form-control" value="<?= $sekolah['kota'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="provinsi" class="form-control" value="<?= $sekolah['provinsi'] ?? '' ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah" class="form-control" value="<?= $sekolah['kepala_sekolah'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NIP Kepala Sekolah</label>
                        <input type="text" name="nip_kepala" class="form-control" value="<?= $sekolah['nip_kepala'] ?? '' ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Admin</label>
                        <input type="text" name="admin" class="form-control" value="<?= $sekolah['admin'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NIP Admin</label>
                        <input type="text" name="nip_admin" class="form-control" value="<?= $sekolah['nip_admin'] ?? '' ?>">
                    </div>
                </div>

                <button type="submit" name="simpan_sekolah" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Identitas Sekolah
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'header' ? 'show active' : '' ?>" id="header" role="tabpanel">
            <form method="POST">
                <div class="mb-3">
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan Logo</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_logo" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_logo" value="Y" <?= $header['tampil_logo'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan Nama Sekolah</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_nama_sekolah" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_nama_sekolah" value="Y" <?= $header['tampil_nama_sekolah'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan Alamat</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_alamat" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_alamat" value="Y" <?= $header['tampil_alamat'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan Nomor Laporan</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_nomor_laporan" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_nomor_laporan" value="Y" <?= $header['tampil_nomor_laporan'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan Admin</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_admin" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_admin" value="Y" <?= $header['tampil_admin'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan QR Code</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_qr" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_qr" value="Y" <?= $header['tampil_qr'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tampilkan Watermark</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_watermark" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_watermark" value="Y" <?= $header['tampil_watermark'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="simpan_header" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Header
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'tabel' ? 'show active' : '' ?>" id="tabel" role="tabpanel">
            <form method="POST">
                <div class="row">
                    <?php
                    $kolom_fields = [
                        'kolom_no' => 'No',
                        'kolom_kode' => 'Kode',
                        'kolom_nama' => 'Nama',
                        'kolom_nis' => 'NIS',
                        'kolom_kelas' => 'Kelas',
                        'kolom_no_hp' => 'No. HP',
                        'kolom_judul' => 'Judul',
                        'kolom_kategori' => 'Kategori',
                        'kolom_ruangan' => 'Ruangan',
                        'kolom_deskripsi' => 'Deskripsi',
                        'kolom_status' => 'Status',
                        'kolom_komentar' => 'Komentar',
                        'kolom_tanggal' => 'Tanggal',
                        'kolom_foto' => 'Foto',
                        'kolom_tgl_dibuat' => 'Tgl Dibuat',
                        'kolom_updated' => 'Updated'
                    ];
                    $i = 0;
                    foreach ($kolom_fields as $key => $label):
                    ?>
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input type="hidden" name="<?= $key ?>" value="N">
                            <input type="checkbox" class="form-check-input" id="<?= $key ?>" name="<?= $key ?>" value="Y" <?= $tabel[$key] === 'Y' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $key ?>"><?= $label ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="simpan_tabel" class="btn btn-primary mt-3">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Tabel
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'warna' ? 'show active' : '' ?>" id="warna" role="tabpanel">
            <form method="POST">
                <div class="row">
                    <?php
                    $warna_fields = [
                        'primary_color' => 'Primary Color',
                        'secondary_color' => 'Secondary Color',
                        'header_color' => 'Header Color',
                        'table_header' => 'Table Header',
                        'footer_color' => 'Footer Color',
                        'badge_success' => 'Badge Success',
                        'badge_warning' => 'Badge Warning',
                        'badge_danger' => 'Badge Danger',
                        'badge_info' => 'Badge Info'
                    ];
                    foreach ($warna_fields as $key => $label):
                    ?>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><?= $label ?></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="<?= $key ?>_picker" value="<?= $warna[$key] ?>" onchange="document.getElementById('<?= $key ?>').value=this.value" style="max-width:50px;">
                            <input type="text" class="form-control" id="<?= $key ?>" name="<?= $key ?>" value="<?= $warna[$key] ?>" oninput="document.getElementById('<?= $key ?>_picker').value=this.value">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="simpan_warna" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Warna
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'statistik' ? 'show active' : '' ?>" id="statistik" role="tabpanel">
            <form method="POST">
                <?php
                $statistik_fields = [
                    'tampil_total' => ['label' => 'Total Pengaduan', 'icon' => 'fa-chart-bar', 'color' => '#3B82F6'],
                    'tampil_diproses' => ['label' => 'Diproses', 'icon' => 'fa-spinner', 'color' => '#F59E0B'],
                    'tampil_selesai' => ['label' => 'Selesai', 'icon' => 'fa-check-circle', 'color' => '#22C55E'],
                    'tampil_ditolak' => ['label' => 'Ditolak', 'icon' => 'fa-times-circle', 'color' => '#EF4444'],
                    'tampil_user' => ['label' => 'User', 'icon' => 'fa-users', 'color' => '#8B5CF6'],
                    'tampil_ruangan' => ['label' => 'Ruangan', 'icon' => 'fa-door-open', 'color' => '#EC4899'],
                    'tampil_kategori' => ['label' => 'Kategori', 'icon' => 'fa-tags', 'color' => '#14B8A6']
                ];
                foreach ($statistik_fields as $key => $field):
                ?>
                <div class="row align-items-center mb-2">
                    <div class="col">
                        <span class="badge" style="background:<?= $field['color'] ?>20;color:<?= $field['color'] ?>;padding:8px 12px;border-radius:8px;">
                            <i class="fas <?= $field['icon'] ?> me-1"></i>
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $field['color'] ?>;margin-right:6px;"></span>
                            <?= $field['label'] ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="form-check form-switch">
                            <input type="hidden" name="<?= $key ?>" value="N">
                            <input type="checkbox" class="form-check-input" role="switch" name="<?= $key ?>" value="Y" <?= $statistik[$key] === 'Y' ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <button type="submit" name="simpan_statistik" class="btn btn-primary mt-3">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Statistik
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'footer' ? 'show active' : '' ?>" id="footer" role="tabpanel">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Copyright</label>
                    <input type="text" name="copyright" class="form-control" value="<?= $footer['copyright'] ?? '' ?>">
                    <small class="text-muted">Gunakan <code>%year%</code> untuk tahun otomatis.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" name="footer_nama_sekolah" class="form-control" value="<?= $footer['nama_sekolah'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="footer_website" class="form-control" value="<?= $footer['website'] ?? '' ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="footer_email" class="form-control" value="<?= $footer['email'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kalimat Footer</label>
                    <textarea name="kalimat_footer" class="form-control" rows="2"><?= $footer['kalimat_footer'] ?? '' ?></textarea>
                </div>
                <div class="mb-3">
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Nomor Halaman</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="nomor_halaman" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="nomor_halaman" value="Y" <?= $footer['nomor_halaman'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Tanggal Cetak</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tanggal_cetak" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tanggal_cetak" value="Y" <?= $footer['tanggal_cetak'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center mb-2">
                        <div class="col"><label class="form-label mb-0">Jam Cetak</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="jam_cetak" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="jam_cetak" value="Y" <?= $footer['jam_cetak'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="simpan_footer" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Footer
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'ttd' ? 'show active' : '' ?>" id="ttd" role="tabpanel">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <div class="row align-items-center">
                        <div class="col"><label class="form-label mb-0">Tampilkan Tanda Tangan</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="tampil_ttd" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="tampil_ttd" value="Y" <?= $ttd['tampil_ttd'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="glass-card p-3">
                            <h6 class="fw-bold mb-3">Penandatangan 1</h6>
                            <input type="hidden" name="ttd1_file_lama" value="<?= $ttd['ttd1_file'] ?>">
                            <div class="mb-2">
                                <?php if ($ttd['ttd1_file']): ?>
                                <img src="../assets/img/pdf/<?= $ttd['ttd1_file'] ?>" alt="Preview" style="width:120px;height:60px;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                                <?php else: ?>
                                <div style="width:120px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                                    <small class="text-muted"><i class="fas fa-signature"></i></small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">File Tanda Tangan</label>
                                <input type="file" name="ttd1_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_ttd1')">
                                <div id="preview_ttd1" class="mt-1"></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nama</label>
                                <input type="text" name="ttd1_nama" class="form-control" value="<?= $ttd['ttd1_nama'] ?? '' ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="ttd1_jabatan" class="form-control" value="<?= $ttd['ttd1_jabatan'] ?? '' ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">NIP</label>
                                <input type="text" name="ttd1_nip" class="form-control" value="<?= $ttd['ttd1_nip'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="glass-card p-3">
                            <h6 class="fw-bold mb-3">Penandatangan 2</h6>
                            <input type="hidden" name="ttd2_file_lama" value="<?= $ttd['ttd2_file'] ?>">
                            <div class="mb-2">
                                <?php if ($ttd['ttd2_file']): ?>
                                <img src="../assets/img/pdf/<?= $ttd['ttd2_file'] ?>" alt="Preview" style="width:120px;height:60px;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                                <?php else: ?>
                                <div style="width:120px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                                    <small class="text-muted"><i class="fas fa-signature"></i></small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">File Tanda Tangan</label>
                                <input type="file" name="ttd2_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_ttd2')">
                                <div id="preview_ttd2" class="mt-1"></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Nama</label>
                                <input type="text" name="ttd2_nama" class="form-control" value="<?= $ttd['ttd2_nama'] ?? '' ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Jabatan</label>
                                <input type="text" name="ttd2_jabatan" class="form-control" value="<?= $ttd['ttd2_jabatan'] ?? '' ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">NIP</label>
                                <input type="text" name="ttd2_nip" class="form-control" value="<?= $ttd['ttd2_nip'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="simpan_ttd" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Tanda Tangan
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'watermark' ? 'show active' : '' ?>" id="watermark" role="tabpanel">
            <form method="POST">
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col"><label class="form-label mb-0">Aktif</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="wm_aktif" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="wm_aktif" value="Y" <?= $watermark['aktif'] === 'Y' ? 'checked' : '' ?> onchange="toggleWatermarkPreview()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Isi Watermark</label>
                        <input type="text" name="wm_isi" id="wm_isi" class="form-control" value="<?= $watermark['isi'] ?>" oninput="toggleWatermarkPreview()">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Opacity</label>
                        <input type="number" name="wm_opacity" id="wm_opacity" class="form-control" step="0.01" min="0" max="1" value="<?= $watermark['opacity'] ?>" oninput="toggleWatermarkPreview()">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ukuran (px)</label>
                        <input type="number" name="wm_ukuran" id="wm_ukuran" class="form-control" value="<?= $watermark['ukuran'] ?>" oninput="toggleWatermarkPreview()">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Posisi</label>
                        <select name="wm_posisi" id="wm_posisi" class="form-select" onchange="toggleWatermarkPreview()">
                            <option value="tengah" <?= $watermark['posisi'] === 'tengah' ? 'selected' : '' ?>>Tengah</option>
                            <option value="atas" <?= $watermark['posisi'] === 'atas' ? 'selected' : '' ?>>Atas</option>
                            <option value="bawah" <?= $watermark['posisi'] === 'bawah' ? 'selected' : '' ?>>Bawah</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Preview</label>
                    <div id="watermarkPreview" style="width:100%;height:150px;border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--bg);font-family:Arial,sans-serif;color:#333;">
                        <span id="watermarkText" style="font-weight:bold;">APSS</span>
                    </div>
                </div>
                <button type="submit" name="simpan_watermark" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan Watermark
                </button>
            </form>
        </div>

        <div class="tab-pane fade <?= $tab === 'qr' ? 'show active' : '' ?>" id="qr" role="tabpanel">
            <form method="POST">
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col"><label class="form-label mb-0">Aktif</label></div>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input type="hidden" name="qr_aktif" value="N">
                                <input type="checkbox" class="form-check-input" role="switch" name="qr_aktif" value="Y" <?= $qr['aktif'] === 'Y' ? 'checked' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Isi QR Code</label>
                    <select name="qr_isi" class="form-select">
                        <option value="url" <?= $qr['isi'] === 'url' ? 'selected' : '' ?>>URL</option>
                        <option value="nomor" <?= $qr['isi'] === 'nomor' ? 'selected' : '' ?>>Nomor</option>
                        <option value="token" <?= $qr['isi'] === 'token' ? 'selected' : '' ?>>Token</option>
                    </select>
                </div>
                <button type="submit" name="simpan_qr" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan QR Code
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function previewFile(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).innerHTML = '<img src="' + e.target.result + '" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">';
        };
        reader.readAsDataURL(file);
    }
}

function toggleWatermarkPreview() {
    const preview = document.getElementById('watermarkPreview');
    const text = document.getElementById('watermarkText');
    const isi = document.getElementById('wm_isi').value;
    const opacity = document.getElementById('wm_opacity').value;
    const ukuran = document.getElementById('wm_ukuran').value;
    const posisi = document.getElementById('wm_posisi').value;

    text.textContent = isi || 'APSS';
    text.style.fontSize = (ukuran || 120) + 'px';
    text.style.opacity = opacity || 0.04;

    if (posisi === 'tengah') {
        preview.style.alignItems = 'center';
        preview.style.justifyContent = 'center';
    } else if (posisi === 'atas') {
        preview.style.alignItems = 'flex-start';
        preview.style.justifyContent = 'center';
    } else if (posisi === 'bawah') {
        preview.style.alignItems = 'flex-end';
        preview.style.justifyContent = 'center';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (document.getElementById('wm_isi')) {
            toggleWatermarkPreview();
        }
    }, 100);
});
</script>
