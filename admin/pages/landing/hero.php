<?php
$hero = fetch(query("SELECT * FROM landing_hero WHERE id = 1"));
if (!$hero) {
    execute("INSERT INTO landing_hero (id) VALUES (1)");
    $hero = fetch(query("SELECT * FROM landing_hero WHERE id = 1"));
}

if (isset($_POST['simpan'])) {
    $judul = bersihkan($_POST['judul']);
    $subtitle = bersihkan($_POST['subtitle']);
    $button1_teks = bersihkan($_POST['button1_teks']);
    $button1_link = bersihkan($_POST['button1_link']);
    $button2_teks = bersihkan($_POST['button2_teks']);
    $button2_link = bersihkan($_POST['button2_link']);
    $status = bersihkan($_POST['status']);

    $bg_image = $_POST['bg_image_lama'] ?? '';
    $logo = $_POST['logo_lama'] ?? '';
    $ilustrasi = $_POST['ilustrasi_lama'] ?? '';

    if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['bg_image'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($bg_image && file_exists('../assets/img/landing/' . $bg_image)) {
                unlink('../assets/img/landing/' . $bg_image);
            }
            $bg_image = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['logo'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($logo && file_exists('../assets/img/landing/' . $logo)) {
                unlink('../assets/img/landing/' . $logo);
            }
            $logo = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    if (isset($_FILES['ilustrasi']) && $_FILES['ilustrasi']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['ilustrasi'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($ilustrasi && file_exists('../assets/img/landing/' . $ilustrasi)) {
                unlink('../assets/img/landing/' . $ilustrasi);
            }
            $ilustrasi = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    execute("UPDATE landing_hero SET judul=?, subtitle=?, button1_teks=?, button1_link=?, button2_teks=?, button2_link=?, bg_image=?, logo=?, ilustrasi=?, status=? WHERE id=1", [$judul, $subtitle, $button1_teks, $button1_link, $button2_teks, $button2_link, $bg_image, $logo, $ilustrasi, $status]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate hero section");
    alert('success', 'Hero section berhasil diperbarui!');
    redirect('index.php?page=landing-hero');
}

$hero = fetch(query("SELECT * FROM landing_hero WHERE id = 1"));
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Hero Section</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Hero Section</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="badge <?= $hero['status'] === 'tampil' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:12px">
            <i class="fas <?= $hero['status'] === 'tampil' ? 'fa-eye' : 'fa-eye-slash' ?> me-1"></i>
            <?= ucfirst($hero['status']) ?>
        </span>
    </div>
</div>

<div class="glass-card">
    <h6 class="fw-bold mb-4"><i class="fas fa-home me-2 text-primary"></i>Edit Hero Section</h6>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="bg_image_lama" value="<?= $hero['bg_image'] ?>">
        <input type="hidden" name="logo_lama" value="<?= $hero['logo'] ?>">
        <input type="hidden" name="ilustrasi_lama" value="<?= $hero['ilustrasi'] ?>">

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Judul <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control" value="<?= $hero['judul'] ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="tampil" <?= $hero['status'] === 'tampil' ? 'selected' : '' ?>>Tampil</option>
                    <option value="sembunyi" <?= $hero['status'] === 'sembunyi' ? 'selected' : '' ?>>Sembunyi</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Subtitle</label>
            <textarea name="subtitle" class="form-control" rows="3"><?= $hero['subtitle'] ?? '' ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Button 1 Teks</label>
                <input type="text" name="button1_teks" class="form-control" value="<?= $hero['button1_teks'] ?? '' ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Button 1 Link</label>
                <input type="text" name="button1_link" class="form-control" value="<?= $hero['button1_link'] ?? '' ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Button 2 Teks</label>
                <input type="text" name="button2_teks" class="form-control" value="<?= $hero['button2_teks'] ?? '' ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Button 2 Link</label>
                <input type="text" name="button2_link" class="form-control" value="<?= $hero['button2_link'] ?? '' ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Background Image</label>
                <div class="mb-2">
                    <?php if ($hero['bg_image']): ?>
                    <img src="../assets/img/landing/<?= $hero['bg_image'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:100px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="bg_image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_bg')">
                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                <div id="preview_bg" class="mt-1"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Logo</label>
                <div class="mb-2">
                    <?php if ($hero['logo']): ?>
                    <img src="../assets/img/landing/<?= $hero['logo'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:100px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="logo" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_logo')">
                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                <div id="preview_logo" class="mt-1"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Ilustrasi</label>
                <div class="mb-2">
                    <?php if ($hero['ilustrasi']): ?>
                    <img src="../assets/img/landing/<?= $hero['ilustrasi'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:100px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="ilustrasi" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_ilustrasi')">
                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                <div id="preview_ilustrasi" class="mt-1"></div>
            </div>
        </div>

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
            document.getElementById(targetId).innerHTML = '<img src="' + e.target.result + '" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">';
        };
        reader.readAsDataURL(file);
    }
}
</script>
