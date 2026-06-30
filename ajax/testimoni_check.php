<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$lastCount = isset($_GET["count"]) ? (int)$_GET["count"] : 0;

$total = hitung("testimonials", "status='approved'");
$fallback = hitung("testimoni", "status='tampil'");
$currentCount = $total + $fallback;

if ($currentCount !== $lastCount) {
    $testimonials = fetchAll(query("SELECT * FROM testimonials WHERE status='approved' ORDER BY created_at DESC"));
    if (count($testimonials) === 0) {
        $testimonials = fetchAll(query("SELECT * FROM testimoni WHERE status='tampil' ORDER BY created_at DESC"));
    }
    ob_start();
    if ($testimonials):
        foreach ($testimonials as $t):
            $nama = $t["nama"] ?? ($t["nama_lengkap"] ?? "");
            $kelas = $t["kelas"] ?? ($t["jabatan"] ?? "");
            $isi = $t["isi"] ?? ($t["isi_testimoni"] ?? "");
            $judul = $t["judul"] ?? "";
            $rating = (int)($t["rating"] ?? 5);
            $foto = $t["foto"] ?? "";
            $foto_testimoni = $t["foto_testimoni"] ?? "";
            $created = $t["created_at"] ?? "";
            $fotoPath = "../assets/img/testimoni/foto/" . $foto_testimoni;
            $fotoExists = $foto_testimoni && file_exists($fotoPath);
            $avatarPath = "";
            if ($foto && file_exists("../assets/img/" . $foto)) {
                $avatarPath = "../assets/img/" . $foto;
            }
?>
    <div class="swiper-slide" data-rating="<?= $rating ?>" data-date="<?= $created ?>">
        <div class="testimoni-card-modern">
            <div class="testimoni-card-inner">
                <div class="testimoni-card-glow"></div>
                <div class="testimoni-card-content">
<?php if ($fotoExists): ?>
                    <div class="testimoni-featured-img mb-3">
                        <img src="<?= $fotoPath ?>" alt="Foto testimoni" class="img-fluid" loading="lazy">
                    </div>
<?php endif; ?>
                    <div class="testimoni-rating mb-2">
<?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?= $i <= $rating ? " active" : "" ?>"></i>
<?php endfor; ?>
                    </div>
<?php if ($judul): ?>
                    <h5 class="testimoni-judul"><?= htmlspecialchars($judul) ?></h5>
<?php endif; ?>
                    <p class="testimoni-isi"><?= htmlspecialchars($isi) ?></p>
                    <div class="testimoni-footer">
                        <div class="testimoni-author-row">
                            <div class="testimoni-avatar-wrap">
<?php if ($avatarPath): ?>
                                <img src="<?= $avatarPath ?>" alt="<?= $nama ?>" class="testimoni-avatar-img" loading="lazy">
<?php else: ?>
                                <div class="testimoni-avatar-placeholder"><?= strtoupper(substr($nama, 0, 1)) ?></div>
<?php endif; ?>
                                <div class="testimoni-verified-badge"><i class="fas fa-check"></i></div>
                            </div>
                            <div class="testimoni-author-info">
                                <h6 class="testimoni-author-name"><?= htmlspecialchars($nama) ?></h6>
                                <span class="testimoni-author-role"><?= htmlspecialchars($kelas ?: "Siswa") ?></span>
                            </div>
                        </div>
                        <div class="testimoni-date">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?= date("d M Y", strtotime($created)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
        endforeach;
    endif;
    $html = ob_get_clean();
    echo json_encode(["changed" => true, "html" => $html, "count" => $currentCount]);
} else {
    echo json_encode(["changed" => false, "count" => $currentCount]);
}
