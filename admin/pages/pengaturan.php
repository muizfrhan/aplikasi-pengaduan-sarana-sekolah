<?php
// ============================================================
// Pengaturan - Admin
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

$setting = get_setting();

if (isset($_POST['simpan'])) {
    $nama_aplikasi = bersihkan($_POST['nama_aplikasi']);
    $singkatan = bersihkan($_POST['singkatan']);
    $alamat = bersihkan($_POST['alamat']);
    $telepon = bersihkan($_POST['telepon']);
    $email = bersihkan($_POST['email']);
    $website = bersihkan($_POST['website']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    $tentang = bersihkan($_POST['tentang']);
    $footer = bersihkan($_POST['footer']);
    
    $sql = "UPDATE setting SET nama_aplikasi=?, singkatan=?, alamat=?, telepon=?, email=?, website=?, deskripsi=?, tentang=?, footer=? WHERE id=1";
    execute($sql, [$nama_aplikasi, $singkatan, $alamat, $telepon, $email, $website, $deskripsi, $tentang, $footer]);
    
    catat_aktivitas($_SESSION['user_id'], "Mengupdate pengaturan");
    alert('success', 'Pengaturan berhasil diperbarui!');
    redirect('index.php?page=pengaturan');
}

$setting = get_setting();
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Pengaturan Aplikasi</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="glass-card">
    <h6 class="fw-bold mb-4"><i class="fas fa-cog me-2 text-primary"></i>Pengaturan Aplikasi</h6>
    <form method="POST" action="index.php?page=pengaturan">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Aplikasi</label>
                <input type="text" name="nama_aplikasi" class="form-control" value="<?= $setting['nama_aplikasi'] ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Singkatan</label>
                <input type="text" name="singkatan" class="form-control" value="<?= $setting['singkatan'] ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= $setting['alamat'] ?? '' ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?= $setting['telepon'] ?? '' ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $setting['email'] ?? '' ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Website</label>
                <input type="url" name="website" class="form-control" value="<?= $setting['website'] ?? '' ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi Aplikasi</label>
            <textarea name="deskripsi" class="form-control" rows="3"><?= $setting['deskripsi'] ?? '' ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Tentang Aplikasi</label>
            <textarea name="tentang" class="form-control" rows="5"><?= $setting['tentang'] ?? '' ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Footer</label>
            <textarea name="footer" class="form-control" rows="2"><?= $setting['footer'] ?? '' ?></textarea>
        </div>
        <button type="submit" name="simpan" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Simpan Pengaturan
        </button>
    </form>
</div>
