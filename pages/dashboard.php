<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

// 1. Ambil Statistik
$total_objek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM objek WHERE deleted_at IS NULL"))['c'];
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM laporan_aksi WHERE deleted_at IS NULL"))['c'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL"))['c'];
$total_karbon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(serapan_karbon),0) as c FROM laporan_aksi WHERE deleted_at IS NULL"))['c'];

// 2. Data Chart Laporan
$chart_labels = []; $chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $chart_labels[] = date('M Y', strtotime("-$i months"));
    $q = mysqli_query($conn, "SELECT COUNT(*) as c FROM laporan_aksi WHERE DATE_FORMAT(tanggal_buat, '%Y-%m') = '$bulan' AND deleted_at IS NULL");
    $chart_data[] = mysqli_fetch_assoc($q)['c'];
}

// 3. Data Chart Objek
$q_dist = mysqli_query($conn, "SELECT jenis_objek, COUNT(*) as c FROM objek WHERE deleted_at IS NULL GROUP BY jenis_objek");
$dist_labels = []; $dist_data = [];
while($d = mysqli_fetch_assoc($q_dist)) {
    $dist_labels[] = ucfirst($d['jenis_objek']);
    $dist_data[] = $d['c'];
}

// 4. User Terbaru
$q_users = mysqli_query($conn, "SELECT * FROM users WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Konten Utama (Padding disesuaikan dengan Topbar Admin) -->
<div class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="layout-dashboard" class="w-7 h-7 md:w-8 md:h-8 text-emerald-600"></i> Command Center
            </h1>
            <p class="text-slate-500 mt-1">Selamat datang kembali, <b><?= e($_SESSION['nama']) ?></b>. Berikut ringkasan ekosistem hari ini.</p>
        </div>
    </div>

    <!-- Baris 1: Statistik -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 opacity-20 group-hover:scale-110 transition-transform"><i data-lucide="tree-pine" class="w-20 h-20 md:w-24 md:h-24"></i></div>
            <div class="relative z-10">
                <div class="text-emerald-100 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Total Objek</div>
                <div class="text-3xl md:text-4xl font-black"><?= number_format($total_objek) ?></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 opacity-20 group-hover:scale-110 transition-transform"><i data-lucide="clipboard-list" class="w-20 h-20 md:w-24 md:h-24"></i></div>
            <div class="relative z-10">
                <div class="text-blue-100 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Total Laporan</div>
                <div class="text-3xl md:text-4xl font-black"><?= number_format($total_laporan) ?></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-2xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 opacity-20 group-hover:scale-110 transition-transform"><i data-lucide="wind" class="w-20 h-20 md:w-24 md:h-24"></i></div>
            <div class="relative z-10">
                <div class="text-amber-100 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Karbon (Kg)</div>
                <div class="text-3xl md:text-4xl font-black"><?= number_format($total_karbon, 0) ?></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-4 md:p-5 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 opacity-20 group-hover:scale-110 transition-transform"><i data-lucide="users" class="w-20 h-20 md:w-24 md:h-24"></i></div>
            <div class="relative z-10">
                <div class="text-purple-100 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Pengguna</div>
                <div class="text-3xl md:text-4xl font-black"><?= number_format($total_user) ?></div>
            </div>
        </div>
    </div>

    <!-- Baris 2: Grafik -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-4 md:p-6">
            <div class="flex justify-between items-center mb-4 md:mb-6">
                <h2 class="font-bold text-slate-800 text-sm md:text-lg">Tren Laporan 6 Bulan</h2>
                <span class="bg-emerald-50 text-emerald-600 text-[10px] md:text-xs font-bold px-2 md:px-3 py-1 rounded-full">Real-time</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="lineChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-4 md:p-6">
            <h2 class="font-bold text-slate-800 text-sm md:text-lg mb-4 md:mb-6">Distribusi Objek</h2>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="doughnutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Baris 3: Tabel & Aksi Cepat -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-4 md:p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800 text-sm md:text-lg">Pendaftar Terbaru</h2>
                <a href="index.php?page=kelola_user" class="text-xs md:text-sm font-bold text-emerald-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[400px]">
                    <thead>
                        <tr class="text-[10px] md:text-xs font-bold text-slate-400 uppercase border-b border-slate-100">
                            <th class="pb-3 pr-4">Nama</th>
                            <th class="pb-3 pr-4">Tipe</th>
                            <th class="pb-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs md:text-sm">
                        <?php while($u = mysqli_fetch_assoc($q_users)): ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                            <td class="py-3 pr-4 font-bold text-slate-700"><?= e($u['nama_lengkap']) ?></td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-0.5 rounded text-[9px] md:text-[10px] font-bold uppercase <?= $u['tipe_akun'] === 'institusi' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                    <?= e($u['tipe_akun']) ?>
                                </span>
                            </td>
                            <td class="py-3 text-slate-500 text-[10px] md:text-xs"><?= date('d M Y', strtotime($u['tanggal_daftar'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel Aksi Cepat -->
        <div class="bg-slate-900 rounded-3xl shadow-lg p-5 md:p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl"></div>
            <h2 class="font-bold text-sm md:text-lg mb-4 relative z-10">Aksi Cepat</h2>
            <div class="space-y-2.5 relative z-10">
                <a href="index.php?page=dashboard_cms" class="w-full flex items-center justify-between p-3 bg-white/10 rounded-xl hover:bg-white/20 transition border border-white/10 text-xs md:text-sm">
                    <span class="font-bold">Kelola Beranda</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="index.php?page=dashboard_info" class="w-full flex items-center justify-between p-3 bg-rose-500/20 rounded-xl hover:bg-rose-500/30 transition border border-rose-500/30 text-rose-100 text-xs md:text-sm">
                    <span class="font-bold flex items-center gap-2"><i data-lucide="megaphone" class="w-4 h-4"></i> Ruang Informasi</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="index.php?page=kelola_game" class="w-full flex items-center justify-between p-3 bg-emerald-500/20 rounded-xl hover:bg-emerald-500/30 transition border border-emerald-500/30 text-emerald-100 text-xs md:text-sm">
                    <span class="font-bold flex items-center gap-2"><i data-lucide="gamepad-2" class="w-4 h-4"></i> Game & Misi</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    var ctxLine = document.getElementById('lineChart').getContext('2d');
    var gradientLine = ctxLine.createLinearGradient(0, 0, 0, 400);
    gradientLine.addColorStop(0, 'rgba(16, 185, 129, 0.5)');   
    gradientLine.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Laporan', data: <?= json_encode($chart_data) ?>, borderColor: '#10b981', backgroundColor: gradientLine, borderWidth: 3, pointRadius: 3, fill: true, tension: 0.4 
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });

    var ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($dist_labels) ?>,
            datasets: [{ data: <?= json_encode($dist_data) ?>, backgroundColor: ['#10b981', '#3b82f6', '#84cc16', '#f59e0b', '#8b5cf6', '#ec4899'], borderWidth: 0, hoverOffset: 10 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } }
    });
});
</script>