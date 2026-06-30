<?php
$s = get_export_settings();
$isGuru = $_SESSION['role'] === 'guru';
$tab = bersihkan($_GET['tab'] ?? ($isGuru ? 'ttd' : 'sekolah'));

if (isset($_POST['simpan_sekolah'])) {
    if ($isGuru) {
        alert('warning', 'Anda tidak memiliki izin untuk menggunakan fitur ini.');
        redirect('index.php?page=export-template&tab=sekolah');
    }
    $logo_school = $_POST['logo_school_lama'] ?? null;
    $logo_app = $_POST['logo_app_lama'] ?? null;

    if (isset($_FILES['logo_school']) && $_FILES['logo_school']['error'] === UPLOAD_ERR_OK) {
        $u = upload_foto($_FILES['logo_school'], '../assets/img/pdf/');
        if ($u['status']) {
            if ($logo_school && file_exists('../assets/img/pdf/' . $logo_school)) unlink('../assets/img/pdf/' . $logo_school);
            $logo_school = $u['file'];
        } else { alert('error', $u['message']); }
    }
    if (isset($_FILES['logo_app']) && $_FILES['logo_app']['error'] === UPLOAD_ERR_OK) {
        $u = upload_foto($_FILES['logo_app'], '../assets/img/pdf/');
        if ($u['status']) {
            if ($logo_app && file_exists('../assets/img/pdf/' . $logo_app)) unlink('../assets/img/pdf/' . $logo_app);
            $logo_app = $u['file'];
        } else { alert('error', $u['message']); }
    }

    execute("UPDATE export_settings SET school_name=?, app_name=?, judul_laporan=?, school_address=?, phone=?, email=?, website=?, logo_school=?, logo_app=?, updated_by=? WHERE id=1", [
        bersihkan($_POST['school_name']), bersihkan($_POST['app_name']), bersihkan($_POST['judul_laporan']),
        bersihkan($_POST['school_address']), bersihkan($_POST['phone']), bersihkan($_POST['email']),
        bersihkan($_POST['website']), $logo_school, $logo_app, $_SESSION['user_id']
    ]);
    catat_aktivitas($_SESSION['user_id'], "Mengupdate identitas sekolah (export)");
    alert('success', 'Identitas sekolah berhasil diperbarui!');
    redirect('index.php?page=export-template&tab=sekolah');
}

if (isset($_POST['simpan_ttd'])) {
    if ($isGuru) {
        $signature = $_POST['signature_lama'] ?? null;

        if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            $u = upload_foto($_FILES['signature'], '../assets/img/ttd/');
            if ($u['status']) {
                if ($signature && file_exists('../assets/img/ttd/' . $signature)) unlink('../assets/img/ttd/' . $signature);
                $signature = $u['file'];
            } else { alert('error', $u['message']); }
        }

        execute("UPDATE export_settings SET teacher_name=?, teacher_nip=?, teacher_title=?, signature=?, signature_position=?, updated_by=? WHERE id=1", [
            bersihkan($_POST['teacher_name']), bersihkan($_POST['teacher_nip']), bersihkan($_POST['teacher_title']),
            $signature, bersihkan($_POST['signature_position']), $_SESSION['user_id']
        ]);
        catat_aktivitas($_SESSION['user_id'], "Mengupdate data penandatangan guru (export)");
        alert('success', 'Data Template Guru berhasil diperbarui.');
        redirect('index.php?page=export-template&tab=ttd');
    }

    $signature = $_POST['signature_lama'] ?? null;
    $stamp = $_POST['stamp_lama'] ?? null;

    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $u = upload_foto($_FILES['signature'], '../assets/img/ttd/');
        if ($u['status']) {
            if ($signature && file_exists('../assets/img/ttd/' . $signature)) unlink('../assets/img/ttd/' . $signature);
            $signature = $u['file'];
        } else { alert('error', $u['message']); }
    }
    if (isset($_FILES['stamp']) && $_FILES['stamp']['error'] === UPLOAD_ERR_OK) {
        $u = upload_foto($_FILES['stamp'], '../assets/img/stamp/');
        if ($u['status']) {
            if ($stamp && file_exists('../assets/img/stamp/' . $stamp)) unlink('../assets/img/stamp/' . $stamp);
            $stamp = $u['file'];
        } else { alert('error', $u['message']); }
    }

    execute("UPDATE export_settings SET principal_name=?, principal_nip=?, principal_title=?, teacher_name=?, teacher_nip=?, teacher_title=?, signature=?, stamp=?, signature_position=?, updated_by=? WHERE id=1", [
        bersihkan($_POST['principal_name']), bersihkan($_POST['principal_nip']), bersihkan($_POST['principal_title']),
        bersihkan($_POST['teacher_name']), bersihkan($_POST['teacher_nip']), bersihkan($_POST['teacher_title']),
        $signature, $stamp, bersihkan($_POST['signature_position']), $_SESSION['user_id']
    ]);
    catat_aktivitas($_SESSION['user_id'], "Mengupdate penandatangan (export)");
    alert('success', 'Pengaturan penandatangan berhasil diperbarui!');
    redirect('index.php?page=export-template&tab=ttd');
}

if (isset($_POST['simpan_tampilan'])) {
    if ($isGuru) {
        alert('warning', 'Anda tidak memiliki izin untuk menggunakan fitur ini.');
        redirect('index.php?page=export-template&tab=tampilan');
    }
    execute("UPDATE export_settings SET header_color=?, footer_color=?, table_color=?, font_family=?, font_size=?, logo_position=?, updated_by=? WHERE id=1", [
        bersihkan($_POST['header_color']), bersihkan($_POST['footer_color']), bersihkan($_POST['table_color']),
        bersihkan($_POST['font_family']), (int)$_POST['font_size'], bersihkan($_POST['logo_position']),
        $_SESSION['user_id']
    ]);
    catat_aktivitas($_SESSION['user_id'], "Mengupdate tampilan (export)");
    alert('success', 'Pengaturan tampilan berhasil diperbarui!');
    redirect('index.php?page=export-template&tab=tampilan');
}

if (isset($_POST['simpan_lainnya'])) {
    if ($isGuru) {
        alert('warning', 'Anda tidak memiliki izin untuk menggunakan fitur ini.');
        redirect('index.php?page=export-template&tab=lainnya');
    }
    execute("UPDATE export_settings SET watermark=?, watermark_text=?, show_statistics=?, show_page_numbers=?, show_date_printed=?, show_time_printed=?, copyright_text=?, qr_code=?, qr_content=?, updated_by=? WHERE id=1", [
        bersihkan($_POST['watermark']), bersihkan($_POST['watermark_text']),
        bersihkan($_POST['show_statistics']), bersihkan($_POST['show_page_numbers']),
        bersihkan($_POST['show_date_printed']), bersihkan($_POST['show_time_printed']),
        bersihkan($_POST['copyright_text']), bersihkan($_POST['qr_code']), bersihkan($_POST['qr_content']),
        $_SESSION['user_id']
    ]);
    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan lainnya (export)");
    alert('success', 'Pengaturan lainnya berhasil diperbarui!');
    redirect('index.php?page=export-template&tab=lainnya');
}
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1"><?= $isGuru ? 'Template Export' : 'Pengaturan Template Export' ?></h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=laporan">Laporan</a></li>
                <li class="breadcrumb-item active"><?= $isGuru ? 'Template Export' : 'Pengaturan Template Export' ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
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
    <h6 class="fw-bold mb-4"><i class="fas fa-file-export me-2 text-primary"></i><?= $isGuru ? 'Template Export Laporan' : 'Pengaturan Template Export' ?></h6>
    <?php if ($isGuru): ?>
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle me-2"></i>
        Pengaturan template ini mengikuti konfigurasi dari <strong>Admin</strong>. Guru hanya dapat mengubah data penandatangan.
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle me-2"></i>
        Pengaturan ini berlaku untuk semua format laporan: <strong>PDF</strong>, <strong>Excel</strong>, dan <strong>Print</strong>.
    </div>
    <?php endif; ?>

    <ul class="nav nav-tabs flex-nowrap overflow-auto" id="exportTabs" role="tablist" style="scrollbar-width:thin;white-space:nowrap">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'sekolah' ? 'active' : '' ?>" id="sekolah-tab" data-bs-toggle="tab" data-bs-target="#sekolah" type="button" role="tab">Identitas Sekolah</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'ttd' ? 'active' : '' ?>" id="ttd-tab" data-bs-toggle="tab" data-bs-target="#ttd" type="button" role="tab">Penandatangan</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'tampilan' ? 'active' : '' ?>" id="tampilan-tab" data-bs-toggle="tab" data-bs-target="#tampilan" type="button" role="tab">Tampilan</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $tab === 'lainnya' ? 'active' : '' ?>" id="lainnya-tab" data-bs-toggle="tab" data-bs-target="#lainnya" type="button" role="tab">Lainnya</button>
        </li>
    </ul>

    <div class="tab-content pt-4">
        <!-- Tab 1: Identitas Sekolah -->
        <div class="tab-pane fade <?= $tab === 'sekolah' ? 'show active' : '' ?>" id="sekolah" role="tabpanel">
            <?php if (!$isGuru): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="logo_school_lama" value="<?= $s['logo_school'] ?>">
                <input type="hidden" name="logo_app_lama" value="<?= $s['logo_app'] ?>">
            <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo Sekolah</label>
                        <div class="mb-2">
                            <?php if ($s['logo_school']): ?>
                            <img src="../assets/img/pdf/<?= $s['logo_school'] ?>" alt="Preview" style="max-width:100px;max-height:100px;width:100%;height:auto;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                            <?php else: ?>
                            <div style="width:100px;height:100px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);"><small class="text-muted"><i class="fas fa-image"></i></small></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isGuru): ?>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</small>
                        <?php else: ?>
                        <input type="file" name="logo_school" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_logo_school')">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                        <div id="preview_logo_school" class="mt-1"></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo Aplikasi</label>
                        <div class="mb-2">
                            <?php if ($s['logo_app']): ?>
                            <img src="../assets/img/pdf/<?= $s['logo_app'] ?>" alt="Preview" style="max-width:100px;max-height:100px;width:100%;height:auto;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                            <?php else: ?>
                            <div style="width:100px;height:100px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);"><small class="text-muted"><i class="fas fa-image"></i></small></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isGuru): ?>
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</small>
                        <?php else: ?>
                        <input type="file" name="logo_app" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_logo_app')">
                        <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                        <div id="preview_logo_app" class="mt-1"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['school_name'] ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <input type="text" name="school_name" class="form-control" value="<?= $s['school_name'] ?>" required>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['app_name'] ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <input type="text" name="app_name" class="form-control" value="<?= $s['app_name'] ?>" required>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                    <?php if ($isGuru): ?>
                    <input type="text" class="form-control" value="<?= $s['judul_laporan'] ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                    <?php else: ?>
                    <input type="text" name="judul_laporan" class="form-control" value="<?= $s['judul_laporan'] ?>" required>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Sekolah</label>
                    <?php if ($isGuru): ?>
                    <textarea class="form-control" rows="2" readonly style="cursor:not-allowed;opacity:0.85;"><?= htmlspecialchars($s['school_address'] ?? '') ?></textarea>
                    <?php else: ?>
                    <textarea name="school_address" class="form-control" rows="2"><?= htmlspecialchars($s['school_address'] ?? '') ?></textarea>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Telepon</label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['phone'] ?? '' ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <input type="text" name="phone" class="form-control" value="<?= $s['phone'] ?? '' ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['email'] ?? '' ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <input type="email" name="email" class="form-control" value="<?= $s['email'] ?? '' ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Website</label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['website'] ?? '' ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <input type="url" name="website" class="form-control" value="<?= $s['website'] ?? '' ?>">
                        <?php endif; ?>
                    </div>
                </div>

            <?php if ($isGuru): ?>
            <div class="text-muted small"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</div>
            <?php else: ?>
                <button type="submit" name="simpan_sekolah" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Identitas Sekolah
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Penandatangan -->
        <div class="tab-pane fade <?= $tab === 'ttd' ? 'show active' : '' ?>" id="ttd" role="tabpanel">
            <?php if ($isGuru): ?>
            <div class="alert alert-warning mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Anda dapat mengubah <strong>Nama</strong>, <strong>NIP</strong>, <strong>Jabatan</strong>, dan <strong>Tanda Tangan</strong> Anda sendiri.
                Data <strong>Kepala Sekolah</strong> dan <strong>Stempel</strong> dikelola oleh Admin.
            </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="signature_lama" value="<?= $s['signature'] ?>">
                <?php if (!$isGuru): ?>
                <input type="hidden" name="stamp_lama" value="<?= $s['stamp'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="glass-card p-3">
                            <h6 class="fw-bold mb-3">Kepala Sekolah <?php if ($isGuru): ?><small class="text-muted">(Admin)</small><?php endif; ?></h6>
                            <div class="mb-2">
                                <label class="form-label">Nama</label>
                                <?php if ($isGuru): ?>
                                <input type="text" class="form-control" value="<?= $s['principal_name'] ?? '' ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                                <?php else: ?>
                                <input type="text" name="principal_name" class="form-control" value="<?= $s['principal_name'] ?? '' ?>">
                                <?php endif; ?>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Jabatan</label>
                                <?php if ($isGuru): ?>
                                <input type="text" class="form-control" value="<?= $s['principal_title'] ?? 'Kepala Sekolah' ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                                <?php else: ?>
                                <input type="text" name="principal_title" class="form-control" value="<?= $s['principal_title'] ?? 'Kepala Sekolah' ?>">
                                <?php endif; ?>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">NIP</label>
                                <?php if ($isGuru): ?>
                                <input type="text" class="form-control" value="<?= $s['principal_nip'] ?? '' ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                                <?php else: ?>
                                <input type="text" name="principal_nip" class="form-control" value="<?= $s['principal_nip'] ?? '' ?>">
                                <?php endif; ?>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tanda Tangan (Gambar)</label>
                                <div class="mb-2">
                                    <?php if ($s['signature']): ?>
                                    <img src="../assets/img/ttd/<?= $s['signature'] ?>" alt="Preview" style="max-width:120px;height:60px;width:100%;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                                    <?php else: ?>
                                    <div style="width:120px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);"><small class="text-muted"><i class="fas fa-signature"></i></small></div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isGuru): ?>
                                <small class="text-muted"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</small>
                                <?php else: ?>
                                <input type="file" name="signature" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_signature')">
                                <div id="preview_signature" class="mt-1"></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($isGuru): ?>
                            <small class="text-muted"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="glass-card p-3">
                            <h6 class="fw-bold mb-3"><?= $isGuru ? 'Guru' : 'Admin Sistem' ?> <?php if ($isGuru): ?><small class="text-success">(Anda)</small><?php endif; ?></h6>
                            <div class="mb-2">
                                <label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="teacher_name" class="form-control" value="<?= $s['teacher_name'] ?? '' ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="teacher_title" class="form-control" value="<?= $s['teacher_title'] ?? ($isGuru ? 'Guru' : 'Admin Sistem') ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">NIP</label>
                                <input type="text" name="teacher_nip" class="form-control" value="<?= $s['teacher_nip'] ?? '' ?>">
                            </div>
                            <?php if (!$isGuru): ?>
                            <div class="mb-2">
                                <label class="form-label">Stempel (Gambar)</label>
                                <div class="mb-2">
                                    <?php if ($s['stamp']): ?>
                                    <img src="../assets/img/stamp/<?= $s['stamp'] ?>" alt="Preview" style="max-width:100px;max-height:100px;width:100%;height:auto;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                                    <?php else: ?>
                                    <div style="width:100px;height:100px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);"><small class="text-muted"><i class="fas fa-stamp"></i></small></div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="stamp" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_stamp')">
                                <div id="preview_stamp" class="mt-1"></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <label class="form-label">Tanda Tangan (Gambar)</label>
                                <div class="mb-2">
                                    <?php if ($s['signature']): ?>
                                    <img src="../assets/img/ttd/<?= $s['signature'] ?>" alt="Preview" style="max-width:120px;height:60px;width:100%;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                                    <?php else: ?>
                                    <div style="width:120px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);"><small class="text-muted"><i class="fas fa-signature"></i></small></div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="signature" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_signature')">
                                <div id="preview_signature" class="mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Posisi Tanda Tangan</label>
                    <select name="signature_position" class="form-select">
                        <option value="bottom" <?= $s['signature_position'] === 'bottom' ? 'selected' : '' ?>>Bawah (Kiri & Kanan)</option>
                        <option value="left" <?= $s['signature_position'] === 'left' ? 'selected' : '' ?>>Kiri Semua</option>
                        <option value="right" <?= $s['signature_position'] === 'right' ? 'selected' : '' ?>>Kanan Semua</option>
                    </select>
                </div>

                <button type="submit" name="simpan_ttd" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i><?= $isGuru ? 'Simpan Data Guru' : 'Simpan Penandatangan' ?>
                </button>
            </form>
        </div>

        <!-- Tab 3: Tampilan -->
        <div class="tab-pane fade <?= $tab === 'tampilan' ? 'show active' : '' ?>" id="tampilan" role="tabpanel">
            <?php if (!$isGuru): ?>
            <form method="POST">
            <?php endif; ?>
                <div class="row">
                    <?php
                    $color_fields = [
                        'header_color' => 'Warna Header',
                        'footer_color' => 'Warna Footer',
                        'table_color' => 'Warna Table Header',
                    ];
                    foreach ($color_fields as $key => $label):
                    ?>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><?= $label ?></label>
                        <div class="input-group">
                            <?php if ($isGuru): ?>
                            <input type="color" class="form-control form-control-color" value="<?= $s[$key] ?>" style="max-width:50px;cursor:not-allowed;" disabled>
                            <input type="text" class="form-control" value="<?= $s[$key] ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                            <?php else: ?>
                            <input type="color" class="form-control form-control-color" id="<?= $key ?>_picker" value="<?= $s[$key] ?>" onchange="document.getElementById('<?= $key ?>').value=this.value" style="max-width:50px;">
                            <input type="text" class="form-control" id="<?= $key ?>" name="<?= $key ?>" value="<?= $s[$key] ?>" oninput="document.getElementById('<?= $key ?>_picker').value=this.value">
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Font Family</label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['font_family'] ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <select name="font_family" class="form-select">
                            <option value="Inter, Arial, sans-serif" <?= $s['font_family'] === 'Inter, Arial, sans-serif' ? 'selected' : '' ?>>Inter / Arial</option>
                            <option value="Times New Roman, serif" <?= $s['font_family'] === 'Times New Roman, serif' ? 'selected' : '' ?>>Times New Roman</option>
                            <option value="Courier New, monospace" <?= $s['font_family'] === 'Courier New, monospace' ? 'selected' : '' ?>>Courier New</option>
                            <option value="Georgia, serif" <?= $s['font_family'] === 'Georgia, serif' ? 'selected' : '' ?>>Georgia</option>
                            <option value="Verdana, sans-serif" <?= $s['font_family'] === 'Verdana, sans-serif' ? 'selected' : '' ?>>Verdana</option>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ukuran Font (px)</label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= $s['font_size'] ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <input type="number" name="font_size" class="form-control" min="8" max="16" value="<?= $s['font_size'] ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Posisi Logo</label>
                        <?php if ($isGuru): ?>
                        <input type="text" class="form-control" value="<?= ucfirst($s['logo_position']) ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                        <?php else: ?>
                        <select name="logo_position" class="form-select">
                            <option value="left" <?= $s['logo_position'] === 'left' ? 'selected' : '' ?>>Kiri</option>
                            <option value="right" <?= $s['logo_position'] === 'right' ? 'selected' : '' ?>>Kanan</option>
                            <option value="center" <?= $s['logo_position'] === 'center' ? 'selected' : '' ?>>Tengah</option>
                        </select>
                        <?php endif; ?>
                    </div>
                </div>

            <?php if ($isGuru): ?>
            <div class="text-muted small"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</div>
            <?php else: ?>
                <button type="submit" name="simpan_tampilan" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Tampilan
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Tab 4: Lainnya -->
        <div class="tab-pane fade <?= $tab === 'lainnya' ? 'show active' : '' ?>" id="lainnya" role="tabpanel">
            <?php if (!$isGuru): ?>
            <form method="POST">
            <?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center mb-2">
                            <div class="col"><label class="form-label mb-0">Watermark</label></div>
                            <div class="col-auto">
                                <div class="form-check form-switch">
                                    <?php if ($isGuru): ?>
                                    <input type="checkbox" class="form-check-input" role="switch" <?= $s['watermark'] === 'Y' ? 'checked' : '' ?> disabled>
                                    <?php else: ?>
                                    <input type="hidden" name="watermark" value="N">
                                    <input type="checkbox" class="form-check-input" role="switch" name="watermark" value="Y" <?= $s['watermark'] === 'Y' ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Teks Watermark</label>
                            <?php if ($isGuru): ?>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($s['watermark_text'] ?? '') ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                            <?php else: ?>
                            <input type="text" name="watermark_text" class="form-control" value="<?= htmlspecialchars($s['watermark_text'] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center mb-2">
                            <div class="col"><label class="form-label mb-0">Tampilkan Statistik</label></div>
                            <div class="col-auto">
                                <div class="form-check form-switch">
                                    <?php if ($isGuru): ?>
                                    <input type="checkbox" class="form-check-input" role="switch" <?= $s['show_statistics'] === 'Y' ? 'checked' : '' ?> disabled>
                                    <?php else: ?>
                                    <input type="hidden" name="show_statistics" value="N">
                                    <input type="checkbox" class="form-check-input" role="switch" name="show_statistics" value="Y" <?= $s['show_statistics'] === 'Y' ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col"><label class="form-label mb-0">Nomor Halaman</label></div>
                            <div class="col-auto">
                                <div class="form-check form-switch">
                                    <?php if ($isGuru): ?>
                                    <input type="checkbox" class="form-check-input" role="switch" <?= $s['show_page_numbers'] === 'Y' ? 'checked' : '' ?> disabled>
                                    <?php else: ?>
                                    <input type="hidden" name="show_page_numbers" value="N">
                                    <input type="checkbox" class="form-check-input" role="switch" name="show_page_numbers" value="Y" <?= $s['show_page_numbers'] === 'Y' ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col"><label class="form-label mb-0">Tanggal Cetak</label></div>
                            <div class="col-auto">
                                <div class="form-check form-switch">
                                    <?php if ($isGuru): ?>
                                    <input type="checkbox" class="form-check-input" role="switch" <?= $s['show_date_printed'] === 'Y' ? 'checked' : '' ?> disabled>
                                    <?php else: ?>
                                    <input type="hidden" name="show_date_printed" value="N">
                                    <input type="checkbox" class="form-check-input" role="switch" name="show_date_printed" value="Y" <?= $s['show_date_printed'] === 'Y' ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-2">
                            <div class="col"><label class="form-label mb-0">Jam Cetak</label></div>
                            <div class="col-auto">
                                <div class="form-check form-switch">
                                    <?php if ($isGuru): ?>
                                    <input type="checkbox" class="form-check-input" role="switch" <?= $s['show_time_printed'] === 'Y' ? 'checked' : '' ?> disabled>
                                    <?php else: ?>
                                    <input type="hidden" name="show_time_printed" value="N">
                                    <input type="checkbox" class="form-check-input" role="switch" name="show_time_printed" value="Y" <?= $s['show_time_printed'] === 'Y' ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teks Copyright</label>
                    <?php if ($isGuru): ?>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($s['copyright_text'] ?? '') ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                    <?php else: ?>
                    <input type="text" name="copyright_text" class="form-control" value="<?= htmlspecialchars($s['copyright_text'] ?? '') ?>">
                    <small class="text-muted">Gunakan <code>%year%</code> untuk tahun otomatis.</small>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center mb-2">
                            <div class="col"><label class="form-label mb-0">QR Code</label></div>
                            <div class="col-auto">
                                <div class="form-check form-switch">
                                    <?php if ($isGuru): ?>
                                    <input type="checkbox" class="form-check-input" role="switch" <?= $s['qr_code'] === 'Y' ? 'checked' : '' ?> disabled>
                                    <?php else: ?>
                                    <input type="hidden" name="qr_code" value="N">
                                    <input type="checkbox" class="form-check-input" role="switch" name="qr_code" value="Y" <?= $s['qr_code'] === 'Y' ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Isi QR Code</label>
                            <?php if ($isGuru): ?>
                            <input type="text" class="form-control" value="<?= ucfirst($s['qr_content'] ?? 'url') ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                            <?php else: ?>
                            <select name="qr_content" class="form-select">
                                <option value="url" <?= $s['qr_content'] === 'url' ? 'selected' : '' ?>>URL</option>
                                <option value="nomor" <?= $s['qr_content'] === 'nomor' ? 'selected' : '' ?>>Nomor</option>
                                <option value="token" <?= $s['qr_content'] === 'token' ? 'selected' : '' ?>>Token</option>
                            </select>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php if ($isGuru): ?>
            <div class="text-muted small"><i class="fas fa-lock me-1"></i>Hanya Admin yang dapat mengakses fitur ini.</div>
            <?php else: ?>
                <button type="submit" name="simpan_lainnya" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan
                </button>
            </form>
            <?php endif; ?>
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
</script>
