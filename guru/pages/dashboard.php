<?php
$totalPengaduan   = hitung('pengaduan');
$menunggu         = hitung('pengaduan', "status='menunggu'");
$diproses         = hitung('pengaduan', "status='diproses'");
$selesai          = hitung('pengaduan', "status='selesai'");
$totalSiswa       = hitung('users', "role='user'");
$totalGuru        = hitung('users', "role='guru'");

$bulanList = query("SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total FROM pengaduan GROUP BY bulan ORDER BY bulan ASC LIMIT 12");
$bulanLabels = [];
$bulanData = [];
while ($b = fetch($bulanList)) {
    $bulanLabels[] = $b['bulan'];
    $bulanData[] = (int)$b['total'];
}

$statusList = query("SELECT status, COUNT(*) as total FROM pengaduan GROUP BY status");
$statusLabels = [];
$statusData = [];
$statusColors = ['menunggu' => '#ffc107', 'diproses' => '#0dcaf0', 'selesai' => '#198754', 'ditolak' => '#dc3545'];
$statusColorsHex = [];
while ($s = fetch($statusList)) {
    $statusLabels[] = ucfirst($s['status']);
    $statusData[] = (int)$s['total'];
    $statusColorsHex[] = $statusColors[$s['status']] ?? '#6c757d';
}
?>
<div class="page-header">
    <h2>Dashboard Guru</h2>
    <p class="text-muted">Ringkasan data pengaduan sarana sekolah</p>
</div>

<div class="row g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-primary text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Total Pengaduan</h6>
                    <h2 class="mb-0"><?= $totalPengaduan ?></h2>
                </div>
                <div class="stat-icon fs-1 opacity-50">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-warning text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Menunggu</h6>
                    <h2 class="mb-0"><?= $menunggu ?></h2>
                </div>
                <div class="stat-icon fs-1 opacity-50">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-info text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Diproses</h6>
                    <h2 class="mb-0"><?= $diproses ?></h2>
                </div>
                <div class="stat-icon fs-1 opacity-50">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-success text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Selesai</h6>
                    <h2 class="mb-0"><?= $selesai ?></h2>
                </div>
                <div class="stat-icon fs-1 opacity-50">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $kontakTotal = @hitung('kontak_messages'); $kontakPending = @hitung('kontak_messages', "status='pending'"); ?>
<div class="row g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-primary text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Pesan Kontak</h6>
                    <h2 class="mb-0"><?= $kontakTotal ?></h2>
                </div>
                <div class="stat-icon fs-1 opacity-50">
                    <i class="fas fa-headset"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-warning text-white p-3 rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Kontak Baru</h6>
                    <h2 class="mb-0"><?= $kontakPending ?></h2>
                </div>
                <div class="stat-icon fs-1 opacity-50">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Tren Pengaduan</h5>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Status Pengaduan</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: <?= json_encode($bulanLabels) ?>,
            datasets: [{
                label: 'Pengaduan',
                data: <?= json_encode($bulanData) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    var ctxPie = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusData) ?>,
                backgroundColor: <?= json_encode($statusColorsHex) ?>,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: '65%'
        }
    });
});
</script>
