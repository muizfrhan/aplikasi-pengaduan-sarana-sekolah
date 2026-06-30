<?php
$setting = fetch(query("SELECT * FROM landing_setting WHERE id = 1"));
if (!$setting) {
    execute("INSERT INTO landing_setting (id) VALUES (1)");
    $setting = fetch(query("SELECT * FROM landing_setting WHERE id = 1"));
}

if (isset($_POST['simpan'])) {
    $nama_website = bersihkan($_POST['nama_website']);
    $primary_color = bersihkan($_POST['primary_color']);
    $secondary_color = bersihkan($_POST['secondary_color']);
    $bg_color = bersihkan($_POST['bg_color']);
    $logo = $_POST['logo_lama'] ?? '';
    $favicon = $_POST['favicon_lama'] ?? '';
    $bg_image = $_POST['bg_image_lama'] ?? '';
    $footer_image = $_POST['footer_image_lama'] ?? '';

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

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['favicon'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($favicon && file_exists('../assets/img/landing/' . $favicon)) {
                unlink('../assets/img/landing/' . $favicon);
            }
            $favicon = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

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

    if (isset($_FILES['footer_image']) && $_FILES['footer_image']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['footer_image'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($footer_image && file_exists('../assets/img/landing/' . $footer_image)) {
                unlink('../assets/img/landing/' . $footer_image);
            }
            $footer_image = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    execute("UPDATE landing_setting SET nama_website=?, logo=?, favicon=?, primary_color=?, secondary_color=?, bg_color=?, bg_image=?, footer_image=? WHERE id=1", [$nama_website, $logo, $favicon, $primary_color, $secondary_color, $bg_color, $bg_image, $footer_image]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate landing settings");
    alert('success', 'Landing settings berhasil diperbarui!');
    redirect('index.php?page=landing-setting');
}

$setting = fetch(query("SELECT * FROM landing_setting WHERE id = 1"));
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Landing Settings</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Landing Settings</li>
            </ol>
        </nav>
    </div>
</div>

<div class="glass-card">
    <h6 class="fw-bold mb-4"><i class="fas fa-palette me-2 text-primary"></i>Edit Landing Settings</h6>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="logo_lama" value="<?= $setting['logo'] ?>">
        <input type="hidden" name="favicon_lama" value="<?= $setting['favicon'] ?>">
        <input type="hidden" name="bg_image_lama" value="<?= $setting['bg_image'] ?>">
        <input type="hidden" name="footer_image_lama" value="<?= $setting['footer_image'] ?>">

        <div class="mb-3">
            <label class="form-label">Nama Website <span class="text-danger">*</span></label>
            <input type="text" name="nama_website" class="form-control" value="<?= $setting['nama_website'] ?>" required>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Primary Color</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="primary_color" class="form-control form-control-color" value="<?= $setting['primary_color'] ?>" style="width:50px;height:38px;padding:2px">
                    <input type="text" class="form-control" value="<?= $setting['primary_color'] ?>" readonly style="width:100px">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Secondary Color</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= $setting['secondary_color'] ?>" style="width:50px;height:38px;padding:2px">
                    <input type="text" class="form-control" value="<?= $setting['secondary_color'] ?>" readonly style="width:100px">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Background Color</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="bg_color" class="form-control form-control-color" value="<?= $setting['bg_color'] ?>" style="width:50px;height:38px;padding:2px">
                    <input type="text" class="form-control" value="<?= $setting['bg_color'] ?>" readonly style="width:100px">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Logo</label>
                <div class="mb-2">
                    <?php if ($setting['logo']): ?>
                    <img src="../assets/img/landing/<?= $setting['logo'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
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
            <div class="col-md-6 mb-3">
                <label class="form-label">Favicon</label>
                <div class="mb-2">
                    <?php if ($setting['favicon']): ?>
                    <img src="../assets/img/landing/<?= $setting['favicon'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:100px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="favicon" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_favicon')">
                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                <div id="preview_favicon" class="mt-1"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Background Image</label>
                <div class="mb-2">
                    <?php if ($setting['bg_image']): ?>
                    <img src="../assets/img/landing/<?= $setting['bg_image'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
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
            <div class="col-md-6 mb-3">
                <label class="form-label">Footer Image</label>
                <div class="mb-2">
                    <?php if ($setting['footer_image']): ?>
                    <img src="../assets/img/landing/<?= $setting['footer_image'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:100px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="footer_image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_footer')">
                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                <div id="preview_footer" class="mt-1"></div>
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
