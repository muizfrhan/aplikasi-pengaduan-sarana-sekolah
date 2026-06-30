<?php
// ============================================================
// Profil - Admin
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

$user = fetch(query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]));

if (isset($_POST['simpan'])) {
    $nama_lengkap = bersihkan($_POST['nama_lengkap']);
    $email = bersihkan($_POST['email']);
    $no_hp = bersihkan($_POST['no_hp']);
    
    execute("UPDATE users SET nama_lengkap=?, email=?, no_hp=? WHERE id=?", 
        [$nama_lengkap, $email, $no_hp, $_SESSION['user_id']]);
    
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        execute("UPDATE users SET password=? WHERE id=?", [$hash, $_SESSION['user_id']]);
    }
    
    if (!empty($_FILES['foto']['name'])) {
        $upload = upload_foto($_FILES['foto'], '../assets/img/');
        if ($upload['status']) {
            if ($user['foto'] !== 'default.png' && file_exists("../assets/img/" . $user['foto'])) {
                unlink("../assets/img/" . $user['foto']);
            }
            execute("UPDATE users SET foto=? WHERE id=?", [$upload['file'], $_SESSION['user_id']]);
            $_SESSION['foto'] = $upload['file'];
        }
    }
    
    $_SESSION['nama_lengkap'] = $nama_lengkap;
    catat_aktivitas($_SESSION['user_id'], "Mengupdate profil");
    alert('success', 'Profil berhasil diperbarui!');
    redirect('index.php?page=profil');
}

// Refresh data
$user = fetch(query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]));
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Profil Saya</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="glass-card text-center">
            <div class="profile-avatar mb-3">
                <img src="../assets/img/<?= $user['foto'] ?? 'default.png' ?>" alt="" class="rounded-circle" width="120" height="120" style="object-fit: cover; border: 4px solid rgba(37,99,235,0.2);">
            </div>
            <h5 class="fw-bold"><?= $user['nama_lengkap'] ?></h5>
            <span class="badge bg-primary mb-3"><?= ucfirst($user['role']) ?></span>
            <p class="text-muted small">
                <i class="fas fa-envelope me-1"></i><?= $user['email'] ?? '-' ?><br>
                <i class="fas fa-phone me-1"></i><?= $user['no_hp'] ?? '-' ?>
            </p>
            <div class="text-start">
                <div class="mb-2">
                    <small class="text-muted">Username</small>
                    <p class="fw-semibold mb-0"><?= $user['username'] ?></p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Bergabung</small>
                    <p class="fw-semibold mb-0"><?= tgl_indonesia($user['created_at'], true) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="glass-card">
            <h6 class="fw-bold mb-4"><i class="fas fa-edit me-2 text-primary"></i>Edit Profil</h6>
            <form method="POST" enctype="multipart/form-data" action="index.php?page=profil">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= $user['nama_lengkap'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= $user['username'] ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $user['email'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= $user['no_hp'] ?? '' ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Foto Profil</label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Maksimal 2MB. Format: JPG, JPEG, PNG, WEBP</small>
                </div>
                <hr>
                <h6 class="fw-bold mb-3">Ganti Password</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password2" class="form-control" placeholder="Ulangi password baru" oninput="if(this.value != this.form.password.value) this.setCustomValidity('Password tidak cocok'); else this.setCustomValidity('');">
                    </div>
                </div>
                <button type="submit" name="simpan" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>
