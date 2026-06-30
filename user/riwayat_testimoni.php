<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
cek_user();

$title = 'Riwayat Testimoni';
$userId = $_SESSION['user_id'];

$items = query("SELECT * FROM testimonials WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar_user.php'; ?>

<div class="main-content" id="mainContent">
    <?php include '../includes/navbar_user.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Riwayat Testimoni</h5>
                <p class="text-muted mb-0">Daftar testimoni yang pernah Anda kirim</p>
            </div>
            <a href="buat_testimoni.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Buat Testimoni
            </a>
        </div>

        <?php if (mysqli_num_rows($items) > 0): ?>
        <div class="row g-4">
            <?php while ($t = fetch($items)): ?>
            <div class="col-lg-4 col-md-6">
                <div class="glass-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="testimoni-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i <= $t['rating'] ? ' text-warning' : ' text-muted' ?>" style="font-size:14px"></i>
                            <?php endfor; ?>
                        </div>
                        <div>
                            <?php if ($t['status'] === 'approved'): ?>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Disetujui</span>
                            <?php elseif ($t['status'] === 'rejected'): ?>
                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Ditolak</span>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-2"><?= htmlspecialchars($t['judul']) ?></h6>
                    <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($t['isi'])) ?></p>
                    <?php if ($t['alasan_tolak']): ?>
                    <div class="p-2 bg-danger-subtle rounded small mb-2">
                        <strong>Alasan ditolak:</strong> <?= htmlspecialchars($t['alasan_tolak']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($t['foto_testimoni']): ?>
                    <img src="../assets/img/testimoni/foto/<?= $t['foto_testimoni'] ?>" class="img-fluid rounded mb-2" style="max-height:100px;object-fit:cover">
                    <?php endif; ?>
                    <div class="text-muted small mt-2">
                        <i class="far fa-calendar-alt me-1"></i><?= tgl_indonesia($t['created_at']) ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-comment-dots text-muted" style="font-size:64px;opacity:0.3;"></i>
            <h5 class="mt-3 text-muted">Belum ada testimoni</h5>
            <p class="text-muted">Anda belum pernah mengirim testimoni. Bagikan pengalaman Anda!</p>
            <a href="buat_testimoni.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Buat Testimoni
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
