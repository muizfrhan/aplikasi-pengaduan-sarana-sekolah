<?php
$path = "C:\\xampp\\htdocs\\PSK\\index.php";
$content = file_get_contents($path);

// 1. Add testimoni query
$search1 = '$footerData = fetch(query("SELECT * FROM landing_footer WHERE id = 1"));
$brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));';

$replace1 = '$footerData = fetch(query("SELECT * FROM landing_footer WHERE id = 1"));
$brand = fetch(query("SELECT * FROM website_branding WHERE id = 1"));
$testimonials = fetchAll(query("SELECT * FROM testimoni WHERE status=' . "'" . 'tampil' . "'" . ' ORDER BY created_at DESC"));';

$content = str_replace($search1, $replace1, $content);
echo "1. testimoni query added\n";

// 2. Add Testimoni to desktop nav
$search2 = '<a class="nav-link" href="#faq">FAQ</a>';
$replace2 = '<a class="nav-link" href="#faq">FAQ</a>
                <a class="nav-link" href="#testimoni">Testimoni</a>';
$content = str_replace($search2, $replace2, $content);
echo "2. desktop nav updated\n";

// 3. Add Testimoni to mobile menu
$search3 = '<a class="menu-item" href="#faq" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-question-circle"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">FAQ</span>
                        <span class="menu-item-desc">Pertanyaan yang sering diajukan</span>
                    </span>
                </a>';
$replace3 = '<a class="menu-item" href="#faq" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-question-circle"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">FAQ</span>
                        <span class="menu-item-desc">Pertanyaan yang sering diajukan</span>
                    </span>
                </a>
                <a class="menu-item" href="#testimoni" data-nav>
                    <span class="menu-item-icon"><i class="fas fa-comment-dots"></i></span>
                    <span class="menu-item-text">
                        <span class="menu-item-title">Testimoni</span>
                        <span class="menu-item-desc">Apa kata mereka tentang APSS</span>
                    </span>
                </a>';
$content = str_replace($search3, $replace3, $content);
echo "3. mobile menu updated\n";

// 4. Add testimoni to footer quick links
$search4 = '<a href="#faq">FAQ</a>
                        <?php if (!isset($_SESSION[' . "'user_id'" . '])): ?>
                        <a href="login.php">Login</a>';
$replace4 = '<a href="#faq">FAQ</a>
                        <a href="#testimoni">Testimoni</a>
                        <?php if (!isset($_SESSION[' . "'user_id'" . '])): ?>
                        <a href="login.php">Login</a>';
$content = str_replace($search4, $replace4, $content);
echo "4. footer links updated\n";

// 5. Add testimoni section between FAQ and Footer
$search5 = '    <!-- Footer -->
    <footer class="footer-section">';
$replace5 = '    <!-- Testimoni Section -->
    <?php if ($testimonials): ?>
    <section id="testimoni" class="section-padding" style="background: #F8FAFC;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">Apa Kata <span>Mereka</span></h2>
                <p class="section-subtitle">Testimoni dari pengguna aplikasi pengaduan sarana sekolah</p>
            </div>
            <div class="row g-4">
                <?php foreach ($testimonials as $t): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="testimoni-card h-100">
                        <div class="testimoni-card-body">
                            <div class="testimoni-stars mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= (int)$t[' . "'rating'" . ']): ?>
                                    <i class="fas fa-star text-warning"></i>
                                    <?php else: ?>
                                    <i class="far fa-star text-muted"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <p class="testimoni-text mb-4">
                                <i class="fas fa-quote-left text-primary opacity-25 me-1" style="font-size:12px;"></i>
                                <?= htmlspecialchars($t[' . "'isi_testimoni'" . ']) ?>
                                <i class="fas fa-quote-right text-primary opacity-25 ms-1" style="font-size:12px;"></i>
                            </p>
                            <div class="testimoni-author d-flex align-items-center gap-3">
                                <?php if ($t[' . "'foto'" . '] && file_exists("assets/img/testimoni/" . $t[' . "'foto'" . '])): ?>
                                <img src="assets/img/testimoni/<?= $t[' . "'foto'" . '] ?>" alt="<?= $t[' . "'nama'" . '] ?>" class="testimoni-avatar-img rounded-circle">
                                <?php else: ?>
                                <div class="testimoni-avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                    <?= strtoupper(substr($t[' . "'nama'" . '], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($t[' . "'nama'" . ']) ?></h6>
                                    <?php if ($t[' . "'jabatan'" . ']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($t[' . "'jabatan'" . ']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer-section">';
$content = str_replace($search5, $replace5, $content);
echo "5. testimoni section added\n";

// Write the modified content back
file_put_contents($path, $content);
echo "All landing page changes saved successfully\n";