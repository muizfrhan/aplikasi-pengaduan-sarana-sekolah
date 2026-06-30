<?php
$brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
if (!$brand) {
    execute("INSERT INTO website_branding (id) VALUES (1)");
    $brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
}

if (isset($_POST['simpan'])) {
    $nama_website = bersihkan($_POST['nama_website']);
    $nama_lengkap = bersihkan($_POST['nama_lengkap']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    $versi = bersihkan($_POST['versi']);
    $nama_sekolah = bersihkan($_POST['nama_sekolah']);
    $tagline = bersihkan($_POST['tagline']);
    $primary_color = bersihkan($_POST['primary_color']);
    $secondary_color = bersihkan($_POST['secondary_color']);
    $status = bersihkan($_POST['status']);

    $logo = $_POST['logo_lama'] ?? '';
    $favicon = $_POST['favicon_lama'] ?? '';

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['logo'], '../assets/img/');
        if ($upload['status']) {
            if ($logo && file_exists('../assets/img/' . $logo)) {
                unlink('../assets/img/' . $logo);
            }
            $logo = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['favicon'], '../assets/img/');
        if ($upload['status']) {
            if ($favicon && file_exists('../assets/img/' . $favicon)) {
                unlink('../assets/img/' . $favicon);
            }
            $favicon = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    execute("UPDATE website_branding SET logo=?, favicon=?, nama_website=?, nama_lengkap=?, deskripsi=?, versi=?, nama_sekolah=?, tagline=?, primary_color=?, secondary_color=?, status=? WHERE id=1",
        [$logo, $favicon, $nama_website, $nama_lengkap, $deskripsi, $versi, $nama_sekolah, $tagline, $primary_color, $secondary_color, $status]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate branding website");
    alert('success', 'Branding website berhasil diperbarui!');
    redirect('index.php?page=landing-branding');
}

// Handle hapus logo
if (isset($_GET['hapus_logo'])) {
    $brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
    if ($brand && $brand['logo'] && file_exists('../assets/img/' . $brand['logo'])) {
        unlink('../assets/img/' . $brand['logo']);
    }
    execute("UPDATE website_branding SET logo = NULL WHERE id = 1");
    catat_aktivitas($_SESSION['user_id'], "Menghapus logo website");
    alert('success', 'Logo berhasil dihapus!');
    redirect('index.php?page=landing-branding');
}

// Handle hapus favicon
if (isset($_GET['hapus_favicon'])) {
    $brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
    if ($brand && $brand['favicon'] && file_exists('../assets/img/' . $brand['favicon'])) {
        unlink('../assets/img/' . $brand['favicon']);
    }
    execute("UPDATE website_branding SET favicon = NULL WHERE id = 1");
    catat_aktivitas($_SESSION['user_id'], "Menghapus favicon website");
    alert('success', 'Favicon berhasil dihapus!');
    redirect('index.php?page=landing-branding');
}

$brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Branding Website</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Branding Website</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="badge <?= $brand['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:12px">
            <i class="fas <?= $brand['status'] === 'aktif' ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
            <?= ucfirst($brand['status']) ?>
        </span>
    </div>
</div>

<div class="glass-card">
    <h6 class="fw-bold mb-4"><i class="fas fa-palette me-2 text-primary"></i>Identitas Website</h6>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="logo_lama" value="<?= $brand['logo'] ?>">
        <input type="hidden" name="favicon_lama" value="<?= $brand['favicon'] ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Logo Website</label>
                <div class="mb-2">
                    <?php if ($brand['logo']): ?>
                    <div class="d-flex align-items-center gap-2">
                        <img src="../assets/img/<?= $brand['logo'] ?>" alt="Logo" style="width:120px;height:70px;object-fit:contain;border-radius:12px;border:1px solid var(--border);background:#f8f9fa;">
                        <a href="?page=landing-branding&hapus_logo=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus logo?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <div style="width:120px;height:70px;border-radius:12px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image fa-2x"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="logo" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_logo')">
                <small class="text-muted">Format: PNG, JPG, JPEG, WEBP. Maks 2 MB.</small>
                <div id="preview_logo" class="mt-1"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Icon Website (Favicon)</label>
                <div class="mb-2">
                    <?php if ($brand['favicon']): ?>
                    <div class="d-flex align-items-center gap-2">
                        <img src="../assets/img/<?= $brand['favicon'] ?>" alt="Favicon" style="width:48px;height:48px;object-fit:cover;border-radius:10px;border:1px solid var(--border);background:#f8f9fa;">
                        <a href="?page=landing-branding&hapus_favicon=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus favicon?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <div style="width:48px;height:48px;border-radius:10px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="favicon" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp,image/x-icon" onchange="previewFile(this, 'preview_favicon')">
                <small class="text-muted">Format: PNG, JPG, JPEG, WEBP, ICO. Maks 2 MB.</small>
                <div id="preview_favicon" class="mt-1"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Website <span class="text-danger">*</span></label>
                <input type="text" name="nama_website" class="form-control" value="<?= $brand['nama_website'] ?>" required placeholder="APSS">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Versi Website</label>
                <input type="text" name="versi" class="form-control" value="<?= $brand['versi'] ?? '1.0.0' ?>" placeholder="1.0.0">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="aktif" <?= $brand['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $brand['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Lengkap Website</label>
            <input type="text" name="nama_lengkap" class="form-control" value="<?= $brand['nama_lengkap'] ?>" placeholder="Aplikasi Pengaduan Sarana Sekolah">
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi Singkat</label>
            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Sistem digital untuk melaporkan kerusakan sarana dan prasarana sekolah..."><?= $brand['deskripsi'] ?? '' ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Sekolah</label>
                <input type="text" name="nama_sekolah" class="form-control" value="<?= $brand['nama_sekolah'] ?>" placeholder="SMK Negeri 1 Contoh">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tagline</label>
                <input type="text" name="tagline" class="form-control" value="<?= $brand['tagline'] ?>" placeholder="Cepat • Mudah • Transparan">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Primary Color</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="color" name="primary_color" class="form-control form-control-color" value="<?= $brand['primary_color'] ?? '#2563EB' ?>" style="width:60px;height:40px;padding:3px;border-radius:8px;cursor:pointer;">
                    <input type="text" class="form-control" value="<?= $brand['primary_color'] ?? '#2563EB' ?>" style="width:120px;font-family:monospace;font-size:13px;" readonly>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Secondary Color</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= $brand['secondary_color'] ?? '#0F172A' ?>" style="width:60px;height:40px;padding:3px;border-radius:8px;cursor:pointer;">
                    <input type="text" class="form-control" value="<?= $brand['secondary_color'] ?? '#0F172A' ?>" style="width:120px;font-family:monospace;font-size:13px;" readonly>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <button type="submit" name="simpan" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Simpan
        </button>
    </form>
</div>

<script>
function previewFile(input, targetId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const target = document.getElementById(targetId);
            const isFavicon = targetId === 'preview_favicon';
            const w = isFavicon ? '48' : '120';
            const h = isFavicon ? '48' : '70';
            target.innerHTML = '<img src="' + e.target.result + '" style="width:' + w + 'px;height:' + h + 'px;object-fit:contain;border-radius:8px;border:1px solid var(--border);background:#f8f9fa;">';
        };
        reader.readAsDataURL(file);
    }
}
</script>
