<?php
// Auto-detect current page from script name
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

// Map page keys to display info
$pageInfo = [
    'index'     => ['key' => 'dashboard',         'icon' => 'fa-home',              'title' => 'Dashboard'],
    'pengaduan' => ['key' => 'pengaduan',          'icon' => 'fa-clipboard-list',    'title' => 'Data Pengaduan'],
    'kategori'  => ['key' => 'kategori',           'icon' => 'fa-tags',              'title' => 'Kategori'],
    'ruangan'   => ['key' => 'ruangan',            'icon' => 'fa-door-open',         'title' => 'Ruangan'],
    'user'      => ['key' => 'user',               'icon' => 'fa-users',             'title' => 'Data User'],
    'laporan'   => ['key' => 'laporan',            'icon' => 'fa-file-alt',          'title' => 'Laporan'],
    'profil'    => ['key' => 'profil',             'icon' => 'fa-user-cog',          'title' => 'Profil'],
    'pengaturan'=> ['key' => 'pengaturan',          'icon' => 'fa-cog',               'title' => 'Pengaturan'],
];
$currentInfo = $pageInfo[$currentPage] ?? ['key' => 'dashboard', 'icon' => 'fa-home', 'title' => 'Dashboard'];

// Override with $page from router if available
if (isset($page) && is_string($page)) {
    $allPages = $pageInfo + [
        'dashboard'         => ['icon' => 'fa-home',            'title' => 'Dashboard'],
        'landing-hero'      => ['icon' => 'fa-images',       'title' => 'Hero Section'],
        'landing-about'     => ['icon' => 'fa-info-circle',  'title' => 'About Section'],
        'landing-steps'     => ['icon' => 'fa-list-ol',      'title' => 'Cara Pengaduan'],
        'landing-statistik' => ['icon' => 'fa-chart-line',   'title' => 'Statistik'],
        'landing-faq'       => ['icon' => 'fa-question-circle', 'title' => 'FAQ'],
        'landing-footer'    => ['icon' => 'fa-shoe-prints',  'title' => 'Footer'],
        'landing-setting'   => ['icon' => 'fa-sliders-h',    'title' => 'Pengaturan Landing'],
        'landing-branding'  => ['icon' => 'fa-paint-brush',  'title' => 'Branding Website'],
        'pdf-template'      => ['icon' => 'fa-file-pdf',     'title' => 'Kelola Template Laporan'],
        'export-template'   => ['icon' => 'fa-file-export',  'title' => 'Pengaturan Template Export'],
        'testimoni'         => ['icon' => 'fa-comment-dots', 'title' => 'Kelola Testimoni'],
        'password-reset'    => ['icon' => 'fa-key',          'title' => 'Permintaan Reset Password'],
        'password-messages' => ['icon' => 'fa-envelope',     'title' => 'Riwayat Pesan Password'],
        'pesan-kontak'      => ['icon' => 'fa-headset',      'title' => 'Pesan Kontak'],
    ];
    if (isset($allPages[$page])) {
        $currentInfo = array_merge(['key' => $page], $allPages[$page]);
    }
}

function page_icon_html(array $info): string {
    return '<i class="fas ' . $info['icon'] . '"></i>';
}

$notifIconMap = [
    'pengaduan_baru' => ['icon' => 'fa-file-alt', 'color' => 'bg-warning'],
    'pengaduan_diproses' => ['icon' => 'fa-spinner', 'color' => 'bg-info'],
    'pengaduan_selesai' => ['icon' => 'fa-check-circle', 'color' => 'bg-success'],
    'pengaduan_ditolak' => ['icon' => 'fa-times-circle', 'color' => 'bg-danger'],
    'testimoni_baru' => ['icon' => 'fa-comment-dots', 'color' => 'bg-info'],
    'testimoni_approved' => ['icon' => 'fa-check-circle', 'color' => 'bg-success'],
    'testimoni_rejected' => ['icon' => 'fa-times-circle', 'color' => 'bg-danger'],
    'pesan_password' => ['icon' => 'fa-envelope', 'color' => 'bg-primary'],
    'pesan_baru' => ['icon' => 'fa-envelope', 'color' => 'bg-primary'],
    'reset_password' => ['icon' => 'fa-key', 'color' => 'bg-secondary'],
    'password_baru' => ['icon' => 'fa-lock', 'color' => 'bg-success'],
];
function notif_icon_data(string $jenis): array {
    global $notifIconMap;
    return $notifIconMap[$jenis] ?? ['icon' => 'fa-circle', 'color' => 'bg-secondary'];
}

$unreadCount = (int)fetch(query("SELECT COUNT(*) as c FROM notifications WHERE (user_id = 0 OR user_id = ?) AND is_read = 0", [$_SESSION['user_id']]))['c'];
$notifList = query("SELECT id, judul, pesan, link, jenis, created_at, is_read FROM notifications WHERE (user_id = 0 OR user_id = ?) ORDER BY id DESC LIMIT 15", [$_SESSION['user_id']]);
$maxNotifId = 0;
$notifItems = [];
while ($n = fetch($notifList)) {
    $id = (int)$n['id'];
    if ($id > $maxNotifId) $maxNotifId = $id;
    $notifItems[] = $n;
}
$allCount = (int)fetch(query("SELECT COUNT(*) as c FROM notifications WHERE (user_id = 0 OR user_id = ?)", [$_SESSION['user_id']]))['c'];
$csrfToken = generate_csrf();
?>
<nav class="top-navbar glass-card">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-indicator d-none d-md-flex align-items-center gap-2">
                    <span class="page-indicator-icon"><?= page_icon_html($currentInfo) ?></span>
                    <span class="page-indicator-text"><?= $currentInfo['title'] ?></span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="notif-wrapper">
                    <button class="bell-btn" id="bellBtn" aria-label="Notifikasi" aria-expanded="false">
                        <i class="fas fa-bell" id="bellIcon"></i>
                        <span class="notif-badge" id="notifBadge"<?= $unreadCount === 0 ? ' style="display:none"' : '' ?>><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                    </button>
                    <div class="notif-panel" id="notifPanel" role="dialog" aria-label="Notifikasi" aria-hidden="true">
                        <div class="notif-panel-header">
                            <div class="notif-panel-title">
                                <h5><i class="fas fa-bell"></i> Notifikasi</h5>
                                <span class="notif-panel-badge" id="notifPanelBadge"<?= $unreadCount === 0 ? ' style="display:none"' : '' ?>><?= $unreadCount ?></span>
                            </div>
                            <div class="notif-panel-actions">
                                <button class="notif-action-btn" id="notifMarkRead" title="Tandai sudah dibaca"<?= $unreadCount === 0 ? ' disabled style="opacity:0.4"' : '' ?>>
                                    <i class="fas fa-check-double"></i>
                                </button>
                                <button class="notif-action-btn" id="notifRefresh" title="Muat ulang">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="notif-action-btn notif-close-btn" id="notifClose" title="Tutup">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="notif-search">
                            <i class="fas fa-search"></i>
                            <input type="text" id="notifSearchInput" placeholder="Cari notifikasi..." autocomplete="off">
                        </div>
                        <div class="notif-tabs">
                            <button class="notif-tab active" data-filter="all">Semua</button>
                            <button class="notif-tab" data-filter="pengaduan">Pengaduan</button>
                            <button class="notif-tab" data-filter="testimoni">Testimoni</button>
                            <button class="notif-tab" data-filter="pesan">Pesan</button>
                            <button class="notif-tab" data-filter="password">Password</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <?php if (count($notifItems) > 0): ?>
                                <?php foreach ($notifItems as $i => $n):
                                    $iconData = notif_icon_data($n['jenis']);
                                    $isUnread = !(int)$n['is_read'];
                                ?>
                                <a class="notif-item<?= $isUnread ? ' unread' : '' ?>" href="#"
                                   data-id="<?= (int)$n['id'] ?>"
                                   data-link="<?= htmlspecialchars($n['link'] ?? '') ?>"
                                   data-jenis="<?= htmlspecialchars($n['jenis']) ?>"
                                   data-title="<?= htmlspecialchars($n['judul']) ?>"
                                   data-msg="<?= htmlspecialchars($n['pesan'] ?? '') ?>"
                                   style="--i: <?= $i ?>">
                                    <div class="notif-item-icon <?= $iconData['color'] ?>">
                                        <i class="fas <?= $iconData['icon'] ?>"></i>
                                    </div>
                                    <div class="notif-item-content">
                                        <div class="notif-item-title"><?= htmlspecialchars($n['judul']) ?></div>
                                        <div class="notif-item-msg"><?= htmlspecialchars($n['pesan'] ?? '') ?></div>
                                        <div class="notif-item-meta">
                                            <span class="notif-item-time"><?= waktu_relatif($n['created_at']) ?></span>
                                            <?php if ($isUnread): ?>
                                            <span class="notif-item-badge bg-primary">Baru</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="notif-item-chevron"><i class="fas fa-chevron-right"></i></div>
                                </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="notif-empty" id="notifEmpty">
                                    <div class="notif-empty-icon"><i class="fas fa-bell-slash"></i></div>
                                    <h6>Belum ada notifikasi</h6>
                                    <p>Notifikasi baru akan muncul di sini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="notif-panel-footer" id="notifFooter">
                            <span class="text-muted"><?= $allCount ?> notifikasi</span>
                        </div>
                    </div>
                </div>

                <div class="dropdown profile-dropdown">
                    <button class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown" aria-label="Menu profil">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <img src="../assets/img/<?= $_SESSION['foto'] ?? 'default.png' ?>" alt="Foto profil" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                            </div>
                            <div class="d-none d-md-block text-start">
                                <small class="fw-bold d-block"><?= $_SESSION['nama_lengkap'] ?></small>
                                <small class="text-muted"><?= ucfirst($_SESSION['role']) ?></small>
                            </div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-profile-header">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dropdown-profile-avatar">
                                        <img src="../assets/img/<?= $_SESSION['foto'] ?? 'default.png' ?>" alt="">
                                    </div>
                                    <div class="dropdown-profile-info">
                                        <div class="dropdown-profile-name"><?= $_SESSION['nama_lengkap'] ?></div>
                                        <div class="dropdown-profile-role"><?= ucfirst($_SESSION['role']) === 'Guru' ? 'Guru' : 'Administrator' ?></div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profil.php"><span class="dropdown-icon bg-primary-subtle"><i class="fas fa-user-cog"></i></span>Profil Saya</a></li>
                        <li><a class="dropdown-item" href="pengaturan.php"><span class="dropdown-icon bg-info-subtle"><i class="fas fa-cog"></i></span>Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><span class="dropdown-icon bg-danger-subtle"><i class="fas fa-sign-out-alt"></i></span>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
var NOTIF_LAST_ID = <?= $maxNotifId ?>;
var NOTIF_POLL_URL = 'ajax/notifikasi.php';
var NOTIF_ICONS = <?= json_encode($notifIconMap) ?>;
var CSRF_TOKEN = '<?= $csrfToken ?>';
var NOTIF_FILTER = '';
var NOTIF_QUERY = '';

document.addEventListener('DOMContentLoaded', function() {
    var bellBtn = document.getElementById('bellBtn');
    var panel = document.getElementById('notifPanel');
    var closeBtn = document.getElementById('notifClose');
    var searchInput = document.getElementById('notifSearchInput');
    var list = document.getElementById('notifList');
    var tabBtns = document.querySelectorAll('.notif-tab');

    bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleNotifPanel();
    });

    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        closeNotifPanel();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && panel.classList.contains('active')) {
            closeNotifPanel();
        }
    });

    document.addEventListener('click', function(e) {
        var wrapper = document.querySelector('.notif-wrapper');
        if (panel.classList.contains('active') && wrapper && !wrapper.contains(e.target)) {
            closeNotifPanel();
        }
    });

    searchInput.addEventListener('input', function() {
        NOTIF_QUERY = this.value.toLowerCase().trim();
        applyFilters();
    });

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            tabBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            NOTIF_FILTER = this.getAttribute('data-filter');
            applyFilters();
        });
    });

    document.getElementById('notifMarkRead').addEventListener('click', function() {
        markAllRead();
    });

    document.getElementById('notifRefresh').addEventListener('click', function() {
        pollNotif(true);
    });

    list.addEventListener('click', function(e) {
        var item = e.target.closest('.notif-item');
        if (!item) return;
        e.preventDefault();
        var id = item.getAttribute('data-id');
        var link = item.getAttribute('data-link');
        if (!id) return;
        var form = new FormData();
        form.append('mark_read', '1');
        form.append('id', id);
        fetch(NOTIF_POLL_URL, { method: 'POST', body: form })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.link) window.location.href = d.link;
                else if (link) window.location.href = link;
            })
            .catch(function() {
                if (link) window.location.href = link;
            });
    });

    panel.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            var focusable = panel.querySelectorAll('button:not([disabled]), [href], input, [tabindex]:not([tabindex="-1"])');
            if (focusable.length === 0) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }
    });

    setInterval(pollNotif, 5000);
});

function toggleNotifPanel() {
    var panel = document.getElementById('notifPanel');
    var bellBtn = document.getElementById('bellBtn');
    if (panel.classList.contains('active')) {
        closeNotifPanel();
    } else {
        panel.style.display = 'flex';
        panel.offsetHeight;
        panel.classList.add('active');
        bellBtn.setAttribute('aria-expanded', 'true');
        setTimeout(function() {
            var first = panel.querySelector('button:not([disabled]), [href], input, [tabindex]:not([tabindex="-1"])');
            if (first) first.focus();
        }, 100);
    }
}

function closeNotifPanel() {
    var panel = document.getElementById('notifPanel');
    var bellBtn = document.getElementById('bellBtn');
    panel.classList.remove('active');
    bellBtn.setAttribute('aria-expanded', 'false');
    bellBtn.focus();
    setTimeout(function() {
        if (!panel.classList.contains('active')) {
            panel.style.display = 'none';
        }
    }, 300);
}

function applyFilters() {
    var items = document.querySelectorAll('.notif-item');
    var empty = document.getElementById('notifEmpty');
    var hasVisible = false;
    items.forEach(function(item) {
        var jenis = item.getAttribute('data-jenis') || '';
        var title = (item.getAttribute('data-title') || '').toLowerCase();
        var msg = (item.getAttribute('data-msg') || '').toLowerCase();
        var matchJenis = !NOTIF_FILTER || NOTIF_FILTER === 'all' || jenis.indexOf(NOTIF_FILTER) !== -1;
        var matchQuery = !NOTIF_QUERY || title.indexOf(NOTIF_QUERY) !== -1 || msg.indexOf(NOTIF_QUERY) !== -1;
        item.style.display = (matchJenis && matchQuery) ? '' : 'none';
        if (matchJenis && matchQuery) hasVisible = true;
    });
    if (empty) {
        empty.style.display = hasVisible ? 'none' : '';
    }
}

function markAllRead() {
    var btn = document.getElementById('notifMarkRead');
    if (btn.getAttribute('disabled') !== null) return;
    btn.disabled = true;
    btn.style.opacity = '0.4';
    fetch(NOTIF_POLL_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'mark_all_read=1&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            document.querySelectorAll('.notif-item.unread').forEach(function(el) {
                el.classList.remove('unread');
                var badge = el.querySelector('.notif-item-badge');
                if (badge) badge.remove();
            });
            updateBadge(0);
            applyFilters();
        }
        btn.disabled = false;
        btn.style.opacity = '';
    })
    .catch(function() {
        btn.disabled = false;
        btn.style.opacity = '';
    });
}

function pollNotif(force) {
    var url = NOTIF_POLL_URL + '?last_id=' + NOTIF_LAST_ID;
    if (force) url += '&force=1';
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.latest && data.latest.length > 0) {
                data.latest.forEach(function(item) { prependNotif(item); });
                updateBadge(data.unread);
                showNotifToast(data.latest[0]);
                playNotifSound();
                shakeBell();
                NOTIF_LAST_ID = data.max_id;
            }
        })
        .catch(function() {});
}

function prependNotif(item) {
    var list = document.getElementById('notifList');
    var empty = list.querySelector('.notif-empty');
    if (empty) empty.remove();

    var icons = NOTIF_ICONS[item.jenis] || { icon: 'fa-circle', color: 'bg-secondary' };
    var el = document.createElement('a');
    el.className = 'notif-item unread';
    el.href = '#';
    el.setAttribute('data-id', item.id);
    el.setAttribute('data-link', item.link || '');
    el.setAttribute('data-jenis', item.jenis || '');
    el.setAttribute('data-title', item.judul || '');
    el.setAttribute('data-msg', item.pesan || '');
    el.style.setProperty('--i', '0');
    el.innerHTML =
        '<div class="notif-item-icon ' + icons.color + '"><i class="fas ' + icons.icon + '"></i></div>' +
        '<div class="notif-item-content">' +
            '<div class="notif-item-title">' + escHtml(item.judul) + '</div>' +
            '<div class="notif-item-msg">' + escHtml(item.pesan || '') + '</div>' +
            '<div class="notif-item-meta">' +
                '<span class="notif-item-time">' + escHtml(item.waktu) + '</span>' +
                '<span class="notif-item-badge bg-primary">Baru</span>' +
            '</div>' +
        '</div>' +
        '<div class="notif-item-chevron"><i class="fas fa-chevron-right"></i></div>';
    list.insertBefore(el, list.firstChild);

    updateFooterCount();
    applyFilters();
}

function updateBadge(count) {
    var badge = document.getElementById('notifBadge');
    var pb = document.getElementById('notifPanelBadge');
    var mr = document.getElementById('notifMarkRead');
    var n = parseInt(count) || 0;
    var display = n > 0 ? n : 0;
    badge.textContent = display > 9 ? '9+' : display;
    badge.style.display = display > 0 ? '' : 'none';
    if (pb) {
        pb.textContent = display;
        pb.style.display = display > 0 ? '' : 'none';
    }
    if (mr) {
        mr.disabled = display === 0;
        mr.style.opacity = display === 0 ? '0.4' : '';
    }
    if (display > 0) {
        badge.classList.remove('bounce');
        void badge.offsetWidth;
        badge.classList.add('bounce');
    }
}

function updateFooterCount() {
    var footer = document.getElementById('notifFooter');
    if (footer) {
        var count = document.querySelectorAll('.notif-item').length;
        footer.innerHTML = '<span class="text-muted">' + count + ' notifikasi</span>';
    }
}

function showNotifToast(item) {
    var m = { pengaduan_baru:'info', pengaduan_diproses:'info', pengaduan_selesai:'success', pengaduan_ditolak:'error', pesan_password:'info', pesan_baru:'info', reset_password:'warning', password_baru:'success', kontak:'info' };
    Swal.fire({ icon: m[item.jenis]||'info', title: item.judul, text: item.pesan, toast: true, position: 'top-end', timer: 4000, timerProgressBar: true, showConfirmButton: false });
}

function playNotifSound() {
    try {
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.4);
    } catch(e) {}
}

function shakeBell() {
    var icon = document.getElementById('bellIcon');
    icon.classList.remove('bell-shake');
    void icon.offsetWidth;
    icon.classList.add('bell-shake');
    setTimeout(function() { icon.classList.remove('bell-shake'); }, 600);
}

function escHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>
