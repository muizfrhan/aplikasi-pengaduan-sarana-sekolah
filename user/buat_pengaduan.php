<?php
// ============================================================
// Buat / Edit Pengaduan - User
// Aplikasi Pengaduan Sarana Sekolah
// ============================================================

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

cek_user();

$title = 'Buat Pengaduan';

$kategoriList = fetchAll(query("SELECT * FROM kategori ORDER BY nama_kategori"));
$ruanganList = fetchAll(query("SELECT * FROM ruangan ORDER BY nama_ruangan"));

$isEdit = isset($_GET['edit']) && $_GET['edit'] > 0;
$editData = null;
$editId = 0;

$title = $isEdit ? 'Edit Pengaduan' : 'Buat Pengaduan';
$breadcrumb = $isEdit ? 'Edit Pengaduan' : 'Buat Pengaduan';

if ($isEdit) {
    $editId = (int)$_GET['edit'];
    $editData = fetch(query("SELECT * FROM pengaduan WHERE id = ? AND user_id = ? AND status = 'menunggu'", [$editId, $_SESSION['user_id']]));
    if (!$editData) {
        alert('warning', 'Pengaduan sudah diproses atau tidak ditemukan.');
        redirect('riwayat.php');
    }
}

$statusSubmit = '';
$messageSubmit = '';
$statusTitle = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editId = (int)($_POST['id'] ?? 0);
    $nama_pelapor = bersihkan($_POST['nama_pelapor']);
    $nis = bersihkan($_POST['nis']);
    $kelas = bersihkan($_POST['kelas']);
    $no_hp = bersihkan($_POST['no_hp']);
    $kategori_id = (int)$_POST['kategori_id'];
    $ruangan_id = (int)$_POST['ruangan_id'];
    $judul = bersihkan($_POST['judul']);
    $deskripsi = bersihkan($_POST['deskripsi']);
    $tgl_kejadian = bersihkan($_POST['tgl_kejadian']);

    // Validate required fields
    $errors = [];
    if (empty($nama_pelapor)) $errors[] = 'Nama';
    if (empty($nis)) $errors[] = 'NIS';
    if (empty($kelas)) $errors[] = 'Kelas';
    if (empty($no_hp)) $errors[] = 'No HP';
    if (empty($kategori_id)) $errors[] = 'Kategori';
    if (empty($ruangan_id)) $errors[] = 'Ruangan';
    if (empty($judul)) $errors[] = 'Judul';
    if (empty($deskripsi)) $errors[] = 'Deskripsi';
    if ($editId == 0 && empty($_FILES['foto']['name'])) $errors[] = 'Foto';
    if (!empty($errors)) {
        $statusSubmit = 'warning';
        $statusTitle = 'Data Belum Lengkap';
        $messageSubmit = 'Silakan lengkapi data: ' . implode(', ', $errors);
    } else {
        $foto = null;

        if (!empty($_FILES['foto']['name'])) {
            $upload = upload_foto($_FILES['foto']);
            if ($upload['status']) {
                $foto = $upload['file'];
                if ($editId > 0 && $editData && $editData['foto']) {
                    $oldFile = '../assets/upload/' . $editData['foto'];
                    if (file_exists($oldFile)) unlink($oldFile);
                }
            } else {
                $statusSubmit = 'warning';
                $statusTitle = 'Upload Gagal';
                $messageSubmit = $upload['message'];
            }
        } elseif ($editId > 0) {
            $foto = $editData['foto'];
        }

        if (empty($statusSubmit)) {
            if ($editId > 0) {
                $sql = "UPDATE pengaduan SET nama_pelapor=?, nis=?, kelas=?, no_hp=?, kategori_id=?, ruangan_id=?, judul=?, deskripsi=?, tgl_kejadian=?";
                $params = [$nama_pelapor, $nis, $kelas, $no_hp, $kategori_id, $ruangan_id, $judul, $deskripsi, $tgl_kejadian];
                if ($foto) { $sql .= ", foto=?"; $params[] = $foto; }
                $sql .= " WHERE id=? AND user_id=? AND status='menunggu'";
                $params[] = $editId; $params[] = $_SESSION['user_id'];
                $result = execute($sql, $params);
                if ($result > 0) {
                    catat_aktivitas($_SESSION['user_id'], "Mengubah pengaduan", "ID: $editId - $judul");
                    $statusSubmit = 'success';
                    $statusTitle = 'Berhasil';
                    $messageSubmit = 'Data berhasil diperbarui.';
                } else {
                    $statusSubmit = 'error';
                    $statusTitle = 'Gagal';
                    $messageSubmit = 'Data gagal diperbarui.';
                }
            } else {
                $kode = generate_kode();
                $sql = "INSERT INTO pengaduan (kode_pengaduan, user_id, nama_pelapor, nis, kelas, no_hp, kategori_id, ruangan_id, judul, deskripsi, foto, tgl_kejadian) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $result = execute($sql, [$kode, $_SESSION['user_id'], $nama_pelapor, $nis, $kelas, $no_hp, $kategori_id, $ruangan_id, $judul, $deskripsi, $foto, $tgl_kejadian]);
                if ($result > 0) {
                    catat_aktivitas($_SESSION['user_id'], "Membuat pengaduan", "Kode: $kode - $judul");
                    buat_notifikasi(0, "Pengaduan Baru", "$nama_pelapor mengirim pengaduan baru: $judul", "../admin/index.php?page=pengaduan", "pengaduan_baru");
                    $statusSubmit = 'success';
                    $statusTitle = 'Berhasil';
                    $messageSubmit = 'Pengaduan berhasil dikirim.';
                } else {
                    $statusSubmit = 'error';
                    $statusTitle = 'Gagal';
                    $messageSubmit = 'Pengaduan gagal disimpan.';
                }
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>
    
    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1"><?= $isEdit ? 'Edit Pengaduan' : 'Buat Pengaduan Baru' ?></h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <?php if ($isEdit): ?>
                        <li class="breadcrumb-item"><a href="riwayat.php">Riwayat</a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active"><?= $isEdit ? 'Edit Pengaduan' : 'Buat Pengaduan' ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4"><i class="fas fa-edit me-2 text-primary"></i><?= $isEdit ? 'Edit Pengaduan' : 'Form Pengaduan' ?></h6>
                    
                    <?php if ($isEdit): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-info-circle me-1"></i> Anda hanya dapat mengedit pengaduan yang berstatus <strong>Menunggu</strong>.
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="formPengaduan">
                        <input type="hidden" name="id" value="<?= $editId ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pelapor" class="form-control" value="<?= $editData['nama_pelapor'] ?? $_SESSION['nama_lengkap'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" class="form-control" value="<?= $editData['nis'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="kelas" class="form-control" placeholder="Contoh: XII RPL 1" value="<?= $editData['kelas'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" value="<?= $editData['no_hp'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategoriList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ($editData['kategori_id'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kategori'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                                <select name="ruangan_id" class="form-select" required>
                                    <option value="">-- Pilih Ruangan --</option>
                                    <?php foreach ($ruanganList as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($editData['ruangan_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= $r['nama_ruangan'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_kejadian" class="form-control" value="<?= $editData['tgl_kejadian'] ?? date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Judul Pengaduan <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Bangku Rusak di Kelas RPL 1" value="<?= $editData['judul'] ?? '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" class="form-control" rows="5" placeholder="Jelaskan secara detail kerusakan yang terjadi..." required><?= $editData['deskripsi'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Foto <span class="text-danger">*</span></label>
                            
                            <?php if ($isEdit && $editData['foto']): ?>
                            <div class="mb-2">
                                <small class="text-muted">Foto saat ini:</small><br>
                                <img src="../assets/upload/<?= $editData['foto'] ?>" alt="Current" style="max-height: 100px;" class="rounded mt-1">
                            </div>
                            <?php endif; ?>
                            
                            <div class="upload-area" id="uploadArea" style="<?= $isEdit && $editData['foto'] ? 'display:none;' : '' ?>">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                <p class="mb-1 text-muted">Seret foto ke sini atau klik untuk upload</p>
                                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP | Maks: 2 MB</small>
                                <input type="file" name="foto" id="fotoInput" class="d-none" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                            <div id="previewFoto" class="mt-2 <?= ($isEdit && $editData['foto']) || (isset($_FILES['foto']) && !empty($_FILES['foto']['name'])) ? '' : 'd-none' ?>">
                                <img id="previewImage" src="<?= $isEdit && $editData['foto'] ? '../assets/upload/' . $editData['foto'] : '' ?>" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                                <button type="button" class="btn btn-sm btn-danger mt-1" onclick="hapusPreview()">
                                    <i class="fas fa-times me-1"></i>Hapus
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" name="simpan" class="btn btn-primary btn-lg px-5" id="btnSubmit">
                            <i class="fas fa-paper-plane me-2"></i><?= $isEdit ? 'Simpan Perubahan' : 'Kirim Pengaduan' ?>
                        </button>
                        
                        <?php if ($isEdit): ?>
                        <a href="riwayat.php" class="btn btn-outline-secondary btn-lg px-4 ms-2">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="glass-card mb-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Panduan</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Isi semua data dengan benar
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Jelaskan kerusakan secara detail
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Upload foto untuk bukti
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Pantau status melalui riwayat
                        </li>
                    </ul>
                </div>
                
                <div class="glass-card">
                    <h6 class="fw-bold mb-3"><i class="fas fa-clock me-2 text-primary"></i>Estimasi Proses</h6>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clock text-dark" style="font-size: 12px;"></i>
                        </div>
                        <div>
                            <small class="fw-bold d-block">1. Menunggu</small>
                            <small class="text-muted">Admin akan memeriksa laporan</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-spinner text-white" style="font-size: 12px;"></i>
                        </div>
                        <div>
                            <small class="fw-bold d-block">2. Diproses</small>
                            <small class="text-muted">Sedang dalam penanganan</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle text-white" style="font-size: 12px;"></i>
                        </div>
                        <div>
                            <small class="fw-bold d-block">3. Selesai</small>
                            <small class="text-muted">Kerusakan telah diperbaiki</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($statusSubmit && $statusTitle): ?>
<script>
Swal.fire({
    icon: '<?= $statusSubmit ?>',
    title: '<?= $statusTitle ?>',
    text: '<?= $messageSubmit ?>',
    allowOutsideClick: false
})<?php if ($statusSubmit === 'success'): ?>.then(() => {
    window.location = 'riwayat.php';
})<?php endif; ?>;
</script>
<?php endif; ?>

<script>
// Upload area click
document.getElementById('uploadArea').addEventListener('click', function() {
    document.getElementById('fotoInput').click();
});

// Drag and drop
const uploadArea = document.getElementById('uploadArea');
uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = '#2563EB';
    this.style.background = 'rgba(37, 99, 235, 0.05)';
});
uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = '#E2E8F0';
    this.style.background = 'transparent';
});
uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '#E2E8F0';
    this.style.background = 'transparent';
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('fotoInput').files = files;
        previewFile(files[0]);
    }
});

// Preview
document.getElementById('fotoInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        previewFile(this.files[0]);
    }
});

function previewFile(file) {
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        Swal.fire({icon: 'warning', title: 'Format tidak didukung', text: 'Hanya JPG, JPEG, PNG, WEBP'});
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({icon: 'warning', title: 'File terlalu besar', text: 'Maksimal 2 MB'});
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImage').src = e.target.result;
        document.getElementById('previewFoto').classList.remove('d-none');
        document.getElementById('uploadArea').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function hapusPreview() {
    document.getElementById('fotoInput').value = '';
    document.getElementById('previewFoto').classList.add('d-none');
    document.getElementById('uploadArea').style.display = '';
}

// Form validation before submit
document.getElementById('formPengaduan').addEventListener('submit', function(e) {
    const fields = [
        { name: 'nama_pelapor', label: 'Nama' },
        { name: 'nis', label: 'NIS' },
        { name: 'kelas', label: 'Kelas' },
        { name: 'no_hp', label: 'No HP' },
        { name: 'kategori_id', label: 'Kategori' },
        { name: 'ruangan_id', label: 'Ruangan' },
        { name: 'judul', label: 'Judul' },
        { name: 'deskripsi', label: 'Deskripsi' }
    ];
    
    let valid = true;
    for (let f of fields) {
        const el = this.querySelector('[name="' + f.name + '"]');
        if (!el.value || el.value.trim() === '') {
            valid = false;
            break;
        }
    }
    
    
    <?php if (!$isEdit): ?>
    const fotoInput2 = document.getElementById('fotoInput');
    if (!fotoInput2.files || fotoInput2.files.length === 0) {
        valid = false;
    }
    <?php endif; ?>

    if (!valid) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap',
            text: 'Silakan lengkapi seluruh data termasuk foto.',
            allowOutsideClick: false
        });
        return;
    }
    
    // Disable button on submit
    document.getElementById('btnSubmit').disabled = true;
    document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
});
</script>

<?php include '../includes/footer.php'; ?>
