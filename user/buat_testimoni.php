<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
cek_user();

$title = 'Kirim Testimoni';
$userId = $_SESSION['user_id'];
$user = fetch(query("SELECT * FROM users WHERE id = ?", [$userId]));

if (isset($_POST['simpan'])) {
    $judul = bersihkan($_POST['judul']);
    $isi = bersihkan($_POST['isi']);
    $rating = (int)$_POST['rating'];
    $setuju = $_POST['setuju'] ?? '';

    // Validasi
    $errors = [];
    if (empty($judul)) $errors[] = 'Judul testimoni wajib diisi.';
    if (empty($isi)) $errors[] = 'Isi testimoni wajib diisi.';
    if (strlen($isi) > 500) $errors[] = 'Isi testimoni maksimal 500 karakter.';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating harus antara 1-5.';
    if ($setuju !== '1') $errors[] = 'Anda harus menyetujui pernyataan bahwa testimoni ini benar.';

    if (count($errors) > 0) {
        echo '<script>Swal.fire({icon:"error",title:"Validasi Gagal",html:"' . implode('<br>', array_map('esc_js', $errors)) . '"});</script>';
    } else {
        $foto_testimoni = '';
        if (isset($_FILES['foto_testimoni']) && $_FILES['foto_testimoni']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_foto($_FILES['foto_testimoni'], '../assets/img/testimoni/foto/');
            if ($upload['status']) {
                $foto_testimoni = $upload['file'];
            }
        }

        $id = insert("INSERT INTO testimonials (user_id, nama, kelas, foto, rating, judul, isi, foto_testimoni, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')", [
            $userId,
            $user['nama_lengkap'],
            $user['kelas'] ?? '',
            $user['foto'] ?? 'default.png',
            $rating,
            $judul,
            $isi,
            $foto_testimoni
        ]);

        if ($id > 0) {
            // Notify admins and gurus
            $adminList = query("SELECT id FROM users WHERE (role='admin' OR role='guru') AND is_active='Y'");
            while ($a = fetch($adminList)) {
                buat_notifikasi((int)$a['id'], "Testimoni Baru", "Siswa telah mengirim testimoni dan menunggu persetujuan.", "../admin/index.php?page=testimoni", "testimoni_baru");
            }

            catat_aktivitas($userId, "Mengirim testimoni", $judul);
            echo '<script>Swal.fire({icon:"success",title:"Berhasil!",text:"Testimoni berhasil dikirim dan menunggu persetujuan Admin."}).then(function(){window.location.href="index.php";});</script>';
            exit;
        } else {
            echo '<script>Swal.fire({icon:"error",title:"Gagal",text:"Terjadi kesalahan saat menyimpan testimoni."});</script>';
        }
    }
}

function esc_js($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Kirim Testimoni</h5>
                <p class="text-muted mb-0">Bagikan pengalaman Anda menggunakan aplikasi pengaduan sarana sekolah</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4"><i class="fas fa-comment-dots me-2 text-primary"></i>Form Testimoni</h6>
                    <form method="POST" enctype="multipart/form-data" id="formTestimoni">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['kelas'] ?? '-') ?>" readonly style="cursor:not-allowed;opacity:0.85;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Judul Testimoni <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Aplikasi yang sangat membantu" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <div class="rating-input d-flex gap-1" id="ratingInput">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="rating-star" data-value="<?= $i ?>" style="cursor:pointer;font-size:36px;color:#ddd;transition:all 0.2s;">
                                    <i class="fas fa-star"></i>
                                </div>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="0">
                            <small class="text-muted">Klik bintang untuk memberi rating</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Testimoni <span class="text-danger">*</span></label>
                            <textarea name="isi" class="form-control" rows="5" placeholder="Tulis pengalaman Anda menggunakan aplikasi ini..." required maxlength="500" id="isiTestimoni"></textarea>
                            <small class="text-muted"><span id="charCount">0</span>/500 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Foto <small class="text-muted">(opsional)</small></label>
                            <input type="file" name="foto_testimoni" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="setuju" id="setuju" value="1" required>
                                <label class="form-check-label" for="setuju">
                                    Saya menyatakan testimoni ini benar dan sesuai dengan pengalaman saya.
                                </label>
                            </div>
                        </div>

                        <button type="submit" name="simpan" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Testimoni
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Testimoni akan melalui proses moderasi</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Admin akan menyetujui atau menolak testimoni</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Testimoni yang disetujui akan tampil di halaman utama</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Gunakan bahasa yang sopan dan santun</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Testimoni tidak boleh mengandung SARA atau ujaran kebencian</li>
                    </ul>
                </div>

                <div class="glass-card mt-3 text-center">
                    <div class="mb-2">
                        <?php if ($user['foto'] && $user['foto'] !== 'default.png'): ?>
                        <img src="../assets/img/<?= $user['foto'] ?>" class="rounded-circle" width="80" height="80" style="object-fit:cover;border:3px solid rgba(37,99,235,0.2)">
                        <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:32px;font-weight:700"><?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($user['nama_lengkap']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($user['kelas'] ?? 'Siswa') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Rating stars
document.querySelectorAll('.rating-star').forEach(function(el) {
    el.addEventListener('click', function() {
        var val = parseInt(this.dataset.value);
        document.getElementById('ratingValue').value = val;
        document.querySelectorAll('.rating-star').forEach(function(s) {
            s.style.color = parseInt(s.dataset.value) <= val ? '#f59e0b' : '#ddd';
        });
    });
    el.addEventListener('mouseenter', function() {
        var val = parseInt(this.dataset.value);
        document.querySelectorAll('.rating-star').forEach(function(s) {
            if (parseInt(s.dataset.value) <= val) s.style.color = '#fbbf24';
        });
    });
    el.addEventListener('mouseleave', function() {
        var selected = parseInt(document.getElementById('ratingValue').value) || 0;
        document.querySelectorAll('.rating-star').forEach(function(s) {
            s.style.color = parseInt(s.dataset.value) <= selected ? '#f59e0b' : '#ddd';
        });
    });
});

// Character count
document.getElementById('isiTestimoni').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Form validation with SweetAlert
document.getElementById('formTestimoni').addEventListener('submit', function(e) {
    var rating = parseInt(document.getElementById('ratingValue').value);
    var isi = document.getElementById('isiTestimoni').value;
    var judul = document.querySelector('[name="judul"]').value;
    var setuju = document.getElementById('setuju').checked;

    if (!judul.trim()) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Judul testimoni wajib diisi.' });
        return;
    }
    if (rating < 1) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Silakan pilih rating minimal 1 bintang.' });
        return;
    }
    if (!isi.trim()) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Isi testimoni wajib diisi.' });
        return;
    }
    if (isi.length > 500) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Isi testimoni maksimal 500 karakter.' });
        return;
    }
    if (!setuju) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Anda harus menyetujui pernyataan bahwa testimoni ini benar.' });
        return;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
