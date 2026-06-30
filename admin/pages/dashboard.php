<?php
$totalPengaduan = hitung('pengaduan');
$pengaduanBaru = hitung('pengaduan', "status='menunggu'");
$diproses = hitung('pengaduan', "status='diproses'");
$selesai = hitung('pengaduan', "status='selesai'");
$ditolak = hitung('pengaduan', "status='ditolak'");
$totalUser = hitung('users', "role='user'");
$totalPesan = hitung('password_messages');
$pesanDibaca = hitung('password_messages', "status_baca='Sudah Dibaca'");
$pesanBelumDibaca = hitung('password_messages', "status_baca='Belum Dibaca'");
$totalKontak = @hitung('kontak_messages');
$kontakPending = @hitung('kontak_messages', "status='pending'");

$tahunResult = query("SELECT DISTINCT YEAR(created_at) as tahun FROM pengaduan ORDER BY tahun DESC");
$availableYears = [];
while ($row = fetch($tahunResult)) {
    $availableYears[] = (int)$row['tahun'];
}
if (!in_array((int)date('Y'), $availableYears)) {
    $availableYears[] = (int)date('Y');
    rsort($availableYears);
}
$tahunAktif = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
if ($tahunAktif < 2000 || $tahunAktif > 2100) {
    $tahunAktif = (int)date('Y');
}

$bulanData = array_fill(0, 12, 0);
$bulanQuery = query("SELECT MONTH(created_at) as bulan, COUNT(*) as total FROM pengaduan WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at) ORDER BY bulan", [$tahunAktif]);
while ($row = fetch($bulanQuery)) {
    $bulanData[(int)$row['bulan'] - 1] = (int)$row['total'];
}

$statusCounts = ['menunggu' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];
$statusQuery = query("SELECT status, COUNT(*) as total FROM pengaduan GROUP BY status");
while ($row = fetch($statusQuery)) {
    if (isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']] = (int)$row['total'];
    }
}
$namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Dashboard</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><i class="fas fa-home me-1"></i>Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="analog-clock-wrapper">
        <div class="analog-clock">
            <div class="clock-face">
                <?php $clockNums = ['12','1','2','3','4','5','6','7','8','9','10','11']; foreach ($clockNums as $i => $num): ?>
                <div class="clock-number" style="--i:<?= $i ?>"><span><?= $num ?></span></div>
                <?php endforeach; ?>
                <div class="clock-hand hour" id="hourHand"></div>
                <div class="clock-hand minute" id="minuteHand"></div>
                <div class="clock-hand second" id="secondHand"></div>
                <div class="clock-center"></div>
            </div>
        </div>
        <div class="clock-info">
            <div class="clock-day" id="clockDay"></div>
            <div class="clock-date" id="clockDate"></div>
            <div class="clock-digital" id="clockDigital"></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="0">
            <div class="stats-icon bg-primary">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-stat="total" data-target="<?= $totalPengaduan ?>">0</h3>
                <p>Total Pengaduan</p>
            </div>
            <div class="stats-trend text-primary">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="100">
            <div class="stats-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-stat="menunggu" data-target="<?= $pengaduanBaru ?>">0</h3>
                <p>Pengaduan Baru</p>
            </div>
            <div class="stats-trend text-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="200">
            <div class="stats-icon bg-info">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-stat="diproses" data-target="<?= $diproses ?>">0</h3>
                <p>Diproses</p>
            </div>
            <div class="stats-trend text-info">
                <i class="fas fa-spinner"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="300">
            <div class="stats-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-stat="selesai" data-target="<?= $selesai ?>">0</h3>
                <p>Selesai</p>
            </div>
            <div class="stats-trend text-success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="400">
            <div class="stats-icon bg-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-stat="ditolak" data-target="<?= $ditolak ?>">0</h3>
                <p>Ditolak</p>
            </div>
            <div class="stats-trend text-danger">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
    <?php $testimoniPending = hitung('testimonials', "status='pending'"); ?>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="500">
            <div class="stats-icon bg-secondary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $totalUser ?>">0</h3>
                <p>Total User</p>
            </div>
            <div class="stats-trend text-secondary">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="550">
            <div class="stats-icon bg-primary">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= hitung('testimonials') ?>">0</h3>
                <p>Total Testimoni</p>
            </div>
            <div class="stats-trend text-primary">
                <i class="fas fa-comment-dots"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="600">
            <div class="stats-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $testimoniPending ?>">0</h3>
                <p>Testimoni Pending</p>
            </div>
            <div class="stats-trend text-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="650">
            <div class="stats-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= hitung('testimonials', "status='approved'") ?>">0</h3>
                <p>Testimoni Disetujui</p>
            </div>
            <div class="stats-trend text-success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="600">
            <div class="stats-icon bg-info">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $totalPesan ?>">0</h3>
                <p>Total Pesan Password</p>
            </div>
            <div class="stats-trend text-info">
                <i class="fas fa-envelope"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="700">
            <div class="stats-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $pesanDibaca ?>">0</h3>
                <p>Pesan Sudah Dibaca</p>
            </div>
            <div class="stats-trend text-success">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="800">
            <div class="stats-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $pesanBelumDibaca ?>">0</h3>
                <p>Pesan Belum Dibaca</p>
            </div>
            <div class="stats-trend text-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="850">
            <div class="stats-icon bg-primary">
                <i class="fas fa-headset"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $totalKontak ?>">0</h3>
                <p>Total Pesan Kontak</p>
            </div>
            <div class="stats-trend text-primary">
                <i class="fas fa-headset"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stats-card glass-card" data-aos="fade-up" data-aos-delay="900">
            <div class="stats-icon bg-warning">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stats-info">
                <h3 class="counter" data-target="<?= $kontakPending ?>">0</h3>
                <p>Pesan Kontak Pending</p>
            </div>
            <div class="stats-trend text-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="glass-card chart-card" data-aos="fade-up">
            <div class="card-header-custom d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0" style="font-size:1.125rem"><i class="fas fa-chart-bar me-2 text-primary"></i>Grafik Pengaduan Bulanan</h6>
                <select id="filterTahun" class="form-select form-select-sm" style="width:auto;font-size:0.8rem;border-radius:8px;">
                    <?php foreach ($availableYears as $thn): ?>
                    <option value="<?= $thn ?>" <?= $thn === $tahunAktif ? 'selected' : '' ?>><?= $thn ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="chart-container">
                <canvas id="chartBulanan"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="glass-card chart-card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-header-custom d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0" style="font-size:1.125rem"><i class="fas fa-chart-pie me-2 text-primary"></i>Status Pengaduan</h6>
            </div>
            <div class="chart-container">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="glass-card" data-aos="fade-up">
            <div class="card-header-custom d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold"><i class="fas fa-history me-2 text-primary"></i>Aktivitas Terbaru</h6>
                <a href="index.php?page=pengaduan" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="activity-list">
                <?php
                $aktivitas = query("SELECT a.*, u.nama_lengkap, u.foto 
                                   FROM aktivitas a 
                                   LEFT JOIN users u ON a.user_id = u.id 
                                   ORDER BY a.created_at DESC LIMIT 10");
                if (mysqli_num_rows($aktivitas) > 0):
                    while ($act = fetch($aktivitas)):
                ?>
                <div class="activity-item">
                    <div class="activity-avatar">
                        <img src="../assets/img/<?= $act['foto'] ?? 'default.png' ?>" alt="">
                    </div>
                    <div class="activity-content">
                        <p class="mb-0">
                            <strong><?= $act['nama_lengkap'] ?? 'Sistem' ?></strong>
                            <?= $act['aksi'] ?>
                        </p>
                        <?php if ($act['keterangan']): ?>
                        <small class="text-muted"><?= $act['keterangan'] ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="activity-time">
                        <small class="text-muted"><?= tgl_indonesia($act['created_at'], true) ?></small>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0">Belum ada aktivitas</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="glass-card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-header-custom d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold"><i class="fas fa-bell me-2 text-primary"></i>Notifikasi</h6>
                <span class="badge bg-danger"><?= $pengaduanBaru ?> Baru</span>
            </div>
            <div class="notification-list">
                <?php
                $notif = query("SELECT p.*, k.nama_kategori, r.nama_ruangan 
                               FROM pengaduan p 
                               LEFT JOIN kategori k ON p.kategori_id = k.id 
                               LEFT JOIN ruangan r ON p.ruangan_id = r.id 
                               WHERE p.status = 'menunggu' 
                               ORDER BY p.created_at DESC LIMIT 5");
                if (mysqli_num_rows($notif) > 0):
                    while ($n = fetch($notif)):
                ?>
                <div class="notification-item">
                    <div class="notification-icon bg-warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="notification-content">
                        <p class="mb-0">
                            <strong><?= potong_teks($n['judul'], 30) ?></strong>
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-tag me-1"></i><?= $n['nama_kategori'] ?? '-' ?> 
                            <i class="fas fa-door-open ms-2 me-1"></i><?= $n['nama_ruangan'] ?? '-' ?>
                        </small>
                    </div>
                    <div class="notification-time">
                        <small class="text-muted"><?= tgl_indonesia($n['created_at']) ?></small>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <p class="mb-0">Tidak ada notifikasi baru</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// Data Awal (di-render dari PHP)
// ============================================================
const chartLabels = <?= json_encode($namaBulan) ?>;
const statusLabels = ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'];
const statusColors = ['#F59E0B', '#38BDF8', '#10B981', '#EF4444'];
const statusBorderColors = ['#D97706', '#0284C7', '#059669', '#DC2626'];

let monthlyData = <?= json_encode($bulanData) ?>;
let statusData = <?= json_encode(array_values($statusCounts)) ?>;

let chartBulanan = null;
let chartStatus = null;

// ============================================================
// Inisialisasi Grafik (dijalankan setelah semua library loaded)
// ============================================================
function initCharts() {
    const ctxBulanan = document.getElementById('chartBulanan').getContext('2d');
    const ctxStatus = document.getElementById('chartStatus').getContext('2d');

    if (typeof Chart === 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memuat Grafik',
            text: 'Library Chart.js tidak ditemukan. Periksa kembali loading script.'
        });
        return;
    }

    chartBulanan = new Chart(ctxBulanan, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Jumlah Pengaduan',
                data: monthlyData,
                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                borderColor: '#2563EB',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(37, 99, 235, 0.6)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.y + ' Pengaduan';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            },
            hover: {
                mode: 'index',
                intersect: false
            }
        }
    });

    chartStatus = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                borderColor: statusBorderColors,
                borderWidth: 2,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            animation: {
                animateRotate: true,
                duration: 800,
                easing: 'easeInOutQuart'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        font: { size: 12 },
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var value = ctx.parsed;
                            var pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return ctx.label + ': ' + value + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}

// ============================================================
// Ambil Data Grafik via AJAX
// ============================================================
function fetchChartData(tahun) {
    fetch('ajax/chart_data.php?tahun=' + tahun)
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            if (data.error) {
                throw new Error(data.error);
            }
            updateCharts(data);
        })
        .catch(function(err) {
            console.error('Grafik Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Grafik',
                text: 'Terjadi kesalahan saat mengambil data grafik.',
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#2563EB'
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetchChartData(tahun);
                }
            });
        });
}

// ============================================================
// Update Grafik & Statistik
// ============================================================
function updateCharts(data) {
    monthlyData = data.monthly;
    statusData = data.status;

    if (chartBulanan) {
        chartBulanan.data.datasets[0].data = monthlyData;
        chartBulanan.update('default');
    }

    if (chartStatus) {
        chartStatus.data.datasets[0].data = statusData;
        chartStatus.update('default');
    }

    var stats = data.stats;
    var statSelectors = {
        'total': stats.total,
        'menunggu': stats.menunggu,
        'diproses': stats.diproses,
        'selesai': stats.selesai,
        'ditolak': stats.ditolak
    };

    Object.keys(statSelectors).forEach(function(key) {
        var els = document.querySelectorAll('[data-stat="' + key + '"]');
        els.forEach(function(el) {
            el.textContent = statSelectors[key];
        });
    });

    var notifBadge = document.querySelector('.badge.bg-danger');
    if (notifBadge) {
        notifBadge.textContent = stats.menunggu + ' Baru';
    }
}

// ============================================================
// Filter Tahun
// ============================================================
document.getElementById('filterTahun').addEventListener('change', function() {
    var tahun = this.value;
    fetchChartData(tahun);
});

// ============================================================
// Auto Refresh (30 detik)
// ============================================================
setInterval(function() {
    var tahun = document.getElementById('filterTahun').value;
    fetchChartData(tahun);
}, 30000);

// ============================================================
// Jalankan setelah semua resource (Chart.js) selesai dimuat
// ============================================================
window.addEventListener('load', initCharts);
</script>
