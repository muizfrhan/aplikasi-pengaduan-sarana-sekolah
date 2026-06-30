<?php
$footer = fetch(query("SELECT * FROM landing_footer WHERE id = 1"));
if (!$footer) {
    execute("INSERT INTO landing_footer (id) VALUES (1)");
    $footer = fetch(query("SELECT * FROM landing_footer WHERE id = 1"));
}

if (isset($_POST['simpan'])) {
    $nama_sekolah = bersihkan($_POST['nama_sekolah']);
    $alamat = bersihkan($_POST['alamat']);
    $no_telepon = bersihkan($_POST['no_telepon']);
    $email = bersihkan($_POST['email']);
    $instagram = bersihkan($_POST['instagram']);
    $facebook = bersihkan($_POST['facebook']);
    $youtube = bersihkan($_POST['youtube']);
    $copyright = bersihkan($_POST['copyright']);
    $logo_footer = $_POST['logo_footer_lama'] ?? '';

    if (isset($_FILES['logo_footer']) && $_FILES['logo_footer']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_foto($_FILES['logo_footer'], '../assets/img/landing/');
        if ($upload['status']) {
            if ($logo_footer && file_exists('../assets/img/landing/' . $logo_footer)) {
                unlink('../assets/img/landing/' . $logo_footer);
            }
            $logo_footer = $upload['file'];
        } else {
            alert('error', $upload['message']);
        }
    }

    execute("UPDATE landing_footer SET nama_sekolah=?, alamat=?, no_telepon=?, email=?, instagram=?, facebook=?, youtube=?, copyright=?, logo_footer=? WHERE id=1", [$nama_sekolah, $alamat, $no_telepon, $email, $instagram, $facebook, $youtube, $copyright, $logo_footer]);

    catat_aktivitas($_SESSION['user_id'], "Mengupdate footer");
    alert('success', 'Footer berhasil diperbarui!');
    redirect('index.php?page=landing-footer');
}

$footer = fetch(query("SELECT * FROM landing_footer WHERE id = 1"));
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Footer Settings</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Footer</li>
            </ol>
        </nav>
    </div>
</div>

<div class="glass-card">
    <h6 class="fw-bold mb-4"><i class="fas fa-copyright me-2 text-primary"></i>Edit Footer</h6>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="logo_footer_lama" value="<?= $footer['logo_footer'] ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Sekolah</label>
                <input type="text" name="nama_sekolah" class="form-control" value="<?= $footer['nama_sekolah'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="no_telepon" class="form-control" value="<?= $footer['no_telepon'] ?? '' ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"><?= $footer['alamat'] ?? '' ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $footer['email'] ?? '' ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Instagram</label>
                <input type="text" name="instagram" class="form-control" value="<?= $footer['instagram'] ?? '' ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Facebook</label>
                <input type="text" name="facebook" class="form-control" value="<?= $footer['facebook'] ?? '' ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Youtube</label>
                <input type="text" name="youtube" class="form-control" value="<?= $footer['youtube'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Logo Footer</label>
                <div class="mb-2">
                    <?php if ($footer['logo_footer']): ?>
                    <img src="../assets/img/landing/<?= $footer['logo_footer'] ?>" alt="Preview" style="width:100px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:100px;height:60px;border-radius:8px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;background:var(--bg);">
                        <small class="text-muted"><i class="fas fa-image"></i></small>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="logo_footer" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewFile(this, 'preview_logo')">
                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                <div id="preview_logo" class="mt-1"></div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Copyright</label>
            <textarea name="copyright" class="form-control" rows="2"><?= $footer['copyright'] ?? '' ?></textarea>
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
