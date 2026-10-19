<?php
// Pastikan koneksi database tersedia
if (!isset($conn)) {
    require_once 'config/database.php'; 
}

// 1. Hitung Statistik & Karbon (Metode Manual Anti-Error)
$stats_lap = ['total_aksi' => 0, 'total_karbon' => 0, 'total_kontributor' => 0];
$q_lap = mysqli_query($conn, "SELECT serapan_karbon, nama_kontributor FROM laporan_aksi WHERE deleted_at IS NULL");
if ($q_lap) {
    $kontributor_arr = [];
    while($row = mysqli_fetch_assoc($q_lap)) {
        $stats_lap['total_aksi']++;
        $stats_lap['total_karbon'] += (float)$row['serapan_karbon'];
        if (!empty($row['nama_kontributor'])) {
            $kontributor_arr[$row['nama_kontributor']] = true;
        }
    }
    $stats_lap['total_kontributor'] = count($kontributor_arr);
}

// 2. Hitung Jumlah per Modul (Metode Manual Anti-Error)
$objek_counts = ['pohon'=>0, 'sungai'=>0, 'taman'=>0, 'komposting'=>0, 'hutan'=>0, 'lingkungan'=>0];
$q_obj = mysqli_query($conn, "SELECT jenis_objek FROM objek WHERE deleted_at IS NULL");
if ($q_obj) {
    while($row = mysqli_fetch_assoc($q_obj)) {
        $j = strtolower(trim($row['jenis_objek']));
        if (isset($objek_counts[$j])) {
            $objek_counts[$j]++;
        }
    }
}

// 3. Ambil Data untuk Grafik Radar
$grafik_labels = ['Pohon', 'Sungai', 'Taman', 'Kompos', 'Hutan', 'Lingkungan'];
$grafik_data = [
    $objek_counts['pohon'], $objek_counts['sungai'], $objek_counts['taman'], 
    $objek_counts['komposting'], $objek_counts['hutan'], $objek_counts['lingkungan']
];

// 4. Ambil Data Feed
$res_feed = mysqli_query($conn, "SELECT l.*, u.nama_lengkap, u.avatar FROM laporan_aksi l LEFT JOIN users u ON l.user_id = u.id WHERE l.deleted_at IS NULL ORDER BY l.tanggal_buat DESC LIMIT 3");

// 5. Ambil Data Leaderboard (Fix Query)
$res_lb = mysqli_query($conn, "
    SELECT u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar, COUNT(l.id) as total_laporan 
    FROM users u INNER JOIN laporan_aksi l ON l.user_id = u.id 
    WHERE l.deleted_at IS NULL AND u.deleted_at IS NULL
    GROUP BY u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar 
    ORDER BY total_laporan DESC LIMIT 5
");
$leaderboard = []; while ($lb = mysqli_fetch_assoc($res_lb)) $leaderboard[] = $lb;

// 6. Ambil Info Terkini
$res_info = mysqli_query($conn, "SELECT * FROM ruang_informasi WHERE target_audiens='semua' ORDER BY id DESC LIMIT 3");
$info_list = []; while($info = mysqli_fetch_assoc($res_info)) $info_list[] = $info;

// 7. Ambil Blok Page Builder
$q_sections = mysqli_query($conn, "SELECT * FROM home_sections WHERE aktif = 1 ORDER BY urutan ASC");
?>

<!-- Load Chart.js & CountUp.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/countup.js@2.0.8/dist/countUp.umd.js"></script>

<style>
    @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-15px); } 100% { transform: translateY(0px); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
    
    .animate-float { animation: float 5s ease-in-out infinite; }
    .animate-fade-up { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07); }
    .hover-lift { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .hover-lift:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); }
    .bg-grid-pattern { background-image: radial-gradient(#10b981 1px, transparent 1px); background-size: 24px 24px; opacity: 0.15; }
</style>

<main class="pt-0 overflow-hidden">
    <?php while($sec = mysqli_fetch_assoc($q_sections)): ?>
        
        <!-- 1. HERO SECTION -->
        <?php if($sec['tipe'] === 'hero'): ?>
        <section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden bg-slate-900 pt-20 pb-16">
            <div class="absolute inset-0 z-0">
                <?php if($sec['gambar']): ?>
                    <img src="<?= UPLOAD_DIR . e($sec['gambar']) ?>" class="w-full h-full object-cover opacity-60 scale-105 animate-float" style="animation-duration: 20s;" alt="Hero BG">
                <?php else: ?>
                    <img src="assets/hero-bg.jpg" class="w-full h-full object-cover opacity-60 scale-105 animate-float" style="animation-duration: 20s;" alt="Hero BG" onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900/30 via-slate-900/60 to-slate-900"></div>
                <div class="absolute inset-0 bg-grid-pattern"></div>
            </div>

            <div class="relative z-10 max-w-6xl mx-auto px-4 text-center mt-[-5vh]">
                <div class="animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/10 border border-white/20 text-emerald-300 text-xs md:text-sm font-bold mb-8 backdrop-blur-md shadow-lg">
                        <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span></span>
                        SWEAPING v6.0 Enterprise Edition
                    </div>
                </div>
                
                <h1 class="text-4xl md:text-7xl font-extrabold text-white leading-tight mb-6 tracking-tight drop-shadow-2xl animate-fade-up" style="animation-delay: 0.2s;">
                    <?= e($sec['judul']) ?><br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-amber-300 text-glow"><?= e($sec['subjudul']) ?></span>
                </h1>
                
                <p class="text-base md:text-xl text-slate-200 max-w-2xl mx-auto mb-10 leading-relaxed px-2 animate-fade-up drop-shadow-md" style="animation-delay: 0.3s;">
                    <?= nl2br(e($sec['konten'])) ?>
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4 animate-fade-up" style="animation-delay: 0.4s;">
                    <?php if(is_logged_in()): ?>
                        <a href="index.php?page=lapor" class="w-full sm:w-auto px-8 py-4 bg-amber-500 text-slate-900 font-extrabold rounded-2xl shadow-[0_0_30px_rgba(245,158,11,0.4)] hover:bg-amber-400 hover:scale-105 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i> Buat Laporan Baru
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=mulai_aksi" class="w-full sm:w-auto px-8 py-4 bg-emerald-500 text-white font-extrabold rounded-2xl shadow-[0_0_30px_rgba(16,185,129,0.4)] hover:bg-emerald-400 hover:scale-105 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="rocket" class="w-5 h-5"></i> Mulai Aksi Sekarang
                        </a>
                    <?php endif; ?>
                    <a href="#modul" class="w-full sm:w-auto px-8 py-4 bg-white/10 text-white font-bold rounded-2xl border border-white/20 hover:bg-white/20 transition-all flex items-center justify-center gap-2 backdrop-blur-md">
                        <i data-lucide="compass" class="w-5 h-5"></i> Jelajahi Fitur
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 2. NEWS TICKER -->
        <?php if($sec['tipe'] === 'hero' && !empty($info_list)): ?>
        <div class="relative z-50 bg-white border-b border-slate-200 shadow-sm overflow-hidden">
            <div class="max-w-7xl mx-auto flex items-center h-12">
                <div class="bg-rose-600 text-white text-[10px] md:text-xs font-bold px-4 h-full flex items-center gap-2 flex-shrink-0 z-10 relative shadow-md">
                    <i data-lucide="megaphone" class="w-3 h-3 md:w-4 md:h-4 animate-pulse"></i> INFO TERKINI
                </div>
                <div class="flex-1 overflow-hidden relative h-full flex items-center px-4">
                    <div class="animate-[marquee_25s_linear_infinite] whitespace-nowrap flex gap-8 text-xs md:text-sm font-medium text-slate-600">
                        <?php foreach($info_list as $inf): ?>
                            <span class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <b class="text-slate-800"><?= e($inf['judul']) ?>:</b> <?= e(substr($inf['konten'], 0, 100)) ?>...
                                <a href="<?= e($inf['link'] ?: '#') ?>" class="text-emerald-600 hover:underline font-bold">Baca</a>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

                <!-- ========================================== -->
        <!-- 3. FLOATING STATS (Animasi Berhitung)      -->
        <!-- ========================================== -->
        <?php if($sec['tipe'] === 'stats'): ?>
        <section id="modul" class="relative z-40 max-w-7xl mx-auto px-4 pt-16 pb-8 mb-8">
            
            <!-- Judul dengan Spasi yang Tepat -->
            <div class="text-center mb-12">
                <span class="text-emerald-600 font-bold tracking-widest uppercase text-xs md:text-sm mb-3 block animate-fade-up">Ekosistem Terpadu</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 animate-fade-up" style="animation-delay: 0.1s;">6 Modul Monitoring Lingkungan</h2>
                <p class="text-slate-500 mt-4 max-w-2xl mx-auto text-sm md:text-base animate-fade-up" style="animation-delay: 0.2s;">Pantau berbagai aspek lingkungan di sekitar Anda dengan standar ilmiah dan kalkulasi karbon yang akurat.</p>
            </div>

            <!-- Baris 1: 6 Modul Monitoring -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-6">
                <!-- Pohon -->
                <div class="glass-panel rounded-2xl p-4 text-center hover-lift border-b-4 border-emerald-500 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-emerald-50 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    <div class="relative z-10">
                        <i data-lucide="tree-pine" class="w-6 h-6 mx-auto text-emerald-500 mb-2 group-hover:scale-110 transition"></i>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 anim-count" data-target="<?= (int)$objek_counts['pohon'] ?>">0</div>
                        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Pohon</div>
                    </div>
                </div>
                <!-- Sungai -->
                <div class="glass-panel rounded-2xl p-4 text-center hover-lift border-b-4 border-blue-500 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-blue-50 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    <div class="relative z-10">
                        <i data-lucide="waves" class="w-6 h-6 mx-auto text-blue-500 mb-2 group-hover:scale-110 transition"></i>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 anim-count" data-target="<?= (int)$objek_counts['sungai'] ?>">0</div>
                        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Sungai</div>
                    </div>
                </div>
                <!-- Taman -->
                <div class="glass-panel rounded-2xl p-4 text-center hover-lift border-b-4 border-lime-500 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-lime-50 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    <div class="relative z-10">
                        <i data-lucide="flower-2" class="w-6 h-6 mx-auto text-lime-500 mb-2 group-hover:scale-110 transition"></i>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 anim-count" data-target="<?= (int)$objek_counts['taman'] ?>">0</div>
                        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Taman</div>
                    </div>
                </div>
                <!-- Kompos -->
                <div class="glass-panel rounded-2xl p-4 text-center hover-lift border-b-4 border-amber-500 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-amber-50 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    <div class="relative z-10">
                        <i data-lucide="recycle" class="w-6 h-6 mx-auto text-amber-500 mb-2 group-hover:scale-110 transition"></i>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 anim-count" data-target="<?= (int)$objek_counts['komposting'] ?>">0</div>
                        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Kompos</div>
                    </div>
                </div>
                <!-- Hutan -->
                <div class="glass-panel rounded-2xl p-4 text-center hover-lift border-b-4 border-teal-500 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-teal-50 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    <div class="relative z-10">
                        <i data-lucide="trees" class="w-6 h-6 mx-auto text-teal-500 mb-2 group-hover:scale-110 transition"></i>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 anim-count" data-target="<?= (int)$objek_counts['hutan'] ?>">0</div>
                        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Hutan</div>
                    </div>
                </div>
                <!-- Lingkungan -->
                <div class="glass-panel rounded-2xl p-4 text-center hover-lift border-b-4 border-purple-500 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-purple-50 opacity-0 group-hover:opacity-100 transition duration-300"></div>
                    <div class="relative z-10">
                        <i data-lucide="home" class="w-6 h-6 mx-auto text-purple-500 mb-2 group-hover:scale-110 transition"></i>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 anim-count" data-target="<?= (int)$objek_counts['lingkungan'] ?>">0</div>
                        <div class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Lingkungan</div>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Karbon & Kontributor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl md:rounded-3xl p-5 md:p-6 text-white shadow-xl flex items-center justify-between relative overflow-hidden group">
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition duration-300"></div>
                    <div class="relative z-10">
                        <div class="text-xs font-bold text-amber-100 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="wind" class="w-3 h-3"></i> Serapan Karbon</div>
                        <div class="text-3xl md:text-5xl font-black flex items-baseline gap-1">
                            <span class="anim-count" data-target="<?= (float)$stats_lap['total_karbon'] ?>">0</span> 
                            <span class="text-sm md:text-lg font-bold text-amber-100">Kg CO₂</span>
                        </div>
                    </div>
                    <i data-lucide="leaf" class="w-20 h-20 md:w-28 md:h-28 text-white opacity-20 absolute -right-4 -bottom-4 group-hover:rotate-12 transition duration-500"></i>
                </div>

                <div class="bg-gradient-to-br from-indigo-500 to-purple-700 rounded-2xl md:rounded-3xl p-5 md:p-6 text-white shadow-xl flex items-center justify-between relative overflow-hidden group">
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition duration-300"></div>
                    <div class="relative z-10">
                        <div class="text-xs font-bold text-indigo-100 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="users" class="w-3 h-3"></i> Total Kontributor</div>
                        <div class="text-3xl md:text-5xl font-black flex items-baseline gap-1">
                            <span class="anim-count" data-target="<?= (int)$stats_lap['total_kontributor'] ?>">0</span> 
                            <span class="text-sm md:text-lg font-bold text-indigo-100">Orang</span>
                        </div>
                    </div>
                    <i data-lucide="award" class="w-20 h-20 md:w-28 md:h-28 text-white opacity-20 absolute -right-4 -bottom-4 group-hover:-rotate-12 transition duration-500"></i>
                </div>
            </div>
        </section>
        <?php endif; ?>

       <!-- 4. 6 MODUL MONITORING & GRAFIK ANIMASI -->
        <?php if($sec['tipe'] === 'challenges' && !isset($_GET['dummy'])): ?>
        <section id="modul" class="max-w-7xl mx-auto px-4 py-16 bg-slate-50/50">
            <div class="text-center mb-12">
                <span class="text-emerald-600 font-bold tracking-widest uppercase text-xs md:text-sm mb-2 block animate-fade-up">Swadaya, Edukasi & Aksi Peduli Lingkungan</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 animate-fade-up" style="animation-delay: 0.1s;">SWEAPING</h2>
                <p class="text-slate-500 mt-3 max-w-2xl mx-auto text-sm md:text-base animate-fade-up" style="animation-delay: 0.2s;">Bergerak di 6 sektor monitoring untuk keberlanjutan dan keberlangsungan ekosistem.</p>
            </div>
            
            <!-- Grid 6 Modul -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-16">
                <!-- Modul Cards (Sama seperti sebelumnya) -->
                <div class="bg-white p-5 md:p-8 rounded-3xl shadow-sm border border-slate-100 hover-lift group text-center relative overflow-hidden"><div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div><div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-emerald-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="tree-pine" class="w-8 h-8 md:w-10 md:h-10 text-emerald-600"></i></div><h3 class="font-bold text-lg md:text-xl text-slate-800 relative z-10">Pohon</h3><p class="text-xs md:text-sm text-slate-500 mt-2 relative z-10">Kalkulasi DBH, Serapan Karbon, dan Skor Kesehatan (FHM).</p></div>
                <div class="bg-white p-5 md:p-8 rounded-3xl shadow-sm border border-slate-100 hover-lift group text-center relative overflow-hidden"><div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div><div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-blue-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="waves" class="w-8 h-8 md:w-10 md:h-10 text-blue-600"></i></div><h3 class="font-bold text-lg md:text-xl text-slate-800 relative z-10">Sungai</h3><p class="text-xs md:text-sm text-slate-500 mt-2 relative z-10">Pantau debit air, kualitas pH, TDS, dan tingkat pencemaran sungai.</p></div>
                <div class="bg-white p-5 md:p-8 rounded-3xl shadow-sm border border-slate-100 hover-lift group text-center relative overflow-hidden"><div class="absolute inset-0 bg-gradient-to-br from-lime-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div><div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-lime-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="flower-2" class="w-8 h-8 md:w-10 md:h-10 text-lime-600"></i></div><h3 class="font-bold text-lg md:text-xl text-slate-800 relative z-10">Taman</h3><p class="text-xs md:text-sm text-slate-500 mt-2 relative z-10">Analisis tutupan kanopi, indeks keanekaragaman, dan fasilitas RTH.</p></div>
                <div class="bg-white p-5 md:p-8 rounded-3xl shadow-sm border border-slate-100 hover-lift group text-center relative overflow-hidden"><div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div><div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-amber-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="recycle" class="w-8 h-8 md:w-10 md:h-10 text-amber-600"></i></div><h3 class="font-bold text-lg md:text-xl text-slate-800 relative z-10">Kompos</h3><p class="text-xs md:text-sm text-slate-500 mt-2 relative z-10">Hitung volume sampah organik dan estimasi pengurangan emisi (IPCC).</p></div>
                <div class="bg-white p-5 md:p-8 rounded-3xl shadow-sm border border-slate-100 hover-lift group text-center relative overflow-hidden"><div class="absolute inset-0 bg-gradient-to-br from-teal-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div><div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-teal-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="trees" class="w-8 h-8 md:w-10 md:h-10 text-teal-600"></i></div><h3 class="font-bold text-lg md:text-xl text-slate-800 relative z-10">Hutan</h3><p class="text-xs md:text-sm text-slate-500 mt-2 relative z-10">Evaluasi kerapatan tajuk, cadangan karbon tanah, dan indeks Shannon.</p></div>
                <div class="bg-white p-5 md:p-8 rounded-3xl shadow-sm border border-slate-100 hover-lift group text-center relative overflow-hidden"><div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div><div class="w-16 h-16 md:w-20 md:h-20 mx-auto bg-purple-100 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="home" class="w-8 h-8 md:w-10 md:h-10 text-purple-600"></i></div><h3 class="font-bold text-lg md:text-xl text-slate-800 relative z-10">Lingkungan</h3><p class="text-xs md:text-sm text-slate-500 mt-2 relative z-10">Ukur suhu, kelembaban, kebisingan, dan kualitas udara (PM2.5).</p></div>
            </div>

                        <!-- Grafik Animasi Distribusi Modul -->
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 p-6 md:p-10 flex flex-col md:flex-row items-center gap-10 max-w-5xl mx-auto relative overflow-hidden animate-fade-up" style="animation-delay: 0.2s;">
                <div class="absolute -right-20 -top-20 opacity-5"><i data-lucide="pie-chart" class="w-96 h-96"></i></div>
                
                <!-- PERHATIKAN: Container diberi tinggi pasti (h-[350px]) agar chart tidak hilang -->
                <div class="w-full md:w-1/2 relative h-[350px] md:h-[400px] z-10">
                    <canvas id="modulChart"></canvas>
                </div>
                
                <div class="w-full md:w-1/2 text-center md:text-left z-10">
                    <span class="inline-block bg-emerald-100 text-emerald-700 font-bold px-3 py-1 rounded-full text-xs mb-3"><i data-lucide="activity" class="w-3 h-3 inline mr-1"></i> Analitik Real-time</span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-4">Distribusi Data Objek</h3>
                    <p class="text-slate-500 text-sm md:text-base leading-relaxed mb-6">Grafik ini menunjukkan sebaran objek yang telah didaftarkan dan dipantau oleh komunitas SWEAPING di seluruh Indonesia.</p>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 inline-block">
                        <div class="text-xs text-slate-500 font-bold uppercase">Total Keseluruhan</div>
                        <div class="text-3xl font-black text-emerald-600"><?= array_sum($grafik_data) ?> <span class="text-sm text-slate-500 font-medium">Objek</span></div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- 5. CAROUSEL / SLIDER GAMBAR                -->
        <!-- ========================================== -->
        <?php if($sec['tipe'] === 'carousel'): ?>
        <section class="py-16 bg-slate-50 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 relative z-10">
                <div class="text-center mb-10">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800"><?= e($sec['judul']) ?></h2>
                    <p class="text-slate-500 mt-2"><?= e($sec['subjudul']) ?></p>
                </div>
                
                <div class="swiper mySwiper rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                    <div class="swiper-wrapper">
                        <?php 
                        $gambars = json_decode($sec['gambar'] ?? '[]', true);
                        if(!empty($gambars) && is_array($gambars)): 
                            foreach($gambars as $img): ?>
                            <div class="swiper-slide">
                                <img src="<?= UPLOAD_DIR . e($img) ?>" class="w-full h-[300px] md:h-[500px] object-cover" alt="Slide">
                            </div>
                            <?php endforeach; 
                        else: ?>
                            <div class="swiper-slide bg-slate-200 h-[300px] flex items-center justify-center text-slate-500">Belum ada gambar.</div>
                        <?php endif; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next text-white drop-shadow-lg bg-black/20 w-10 h-10 rounded-full hover:bg-black/40"></div>
                    <div class="swiper-button-prev text-white drop-shadow-lg bg-black/20 w-10 h-10 rounded-full hover:bg-black/40"></div>
                </div>
            </div>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            <script>
                if(document.querySelector('.mySwiper')) {
                    new Swiper(".mySwiper", {
                        loop: true,
                        autoplay: { delay: 3500, disableOnInteraction: false },
                        effect: 'slide',
                        speed: 800,
                        pagination: { el: ".swiper-pagination", clickable: true },
                        navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }
                    });
                }
            </script>
        </section>
        <?php endif; ?>

        <!-- 6. ECO-CHALLENGES -->
        <?php if($sec['tipe'] === 'challenges'): ?>
        <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 rounded-[2.5rem] p-6 md:p-16 relative overflow-hidden flex flex-col md:flex-row items-center justify-between shadow-2xl border border-emerald-500/20 group">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-30"></div>
                <div class="relative z-10 md:w-3/5 mb-10 md:mb-0 text-center md:text-left">
                    <span class="inline-flex items-center gap-1 text-amber-400 text-xs font-bold uppercase tracking-widest mb-4 bg-amber-400/10 px-4 py-1.5 rounded-full border border-amber-400/20"><i data-lucide="zap" class="w-4 h-4"></i> Fitur Gamifikasi</span>
                    <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-5 leading-tight"><?= e($sec['judul']) ?><br class="hidden md:block"><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300"><?= e($sec['subjudul']) ?></span></h2>
                    <p class="text-slate-300 mb-8 text-sm md:text-lg leading-relaxed max-w-xl"><?= nl2br(e($sec['konten'])) ?></p>
                    <div class="flex flex-wrap justify-center md:justify-start gap-3">
                        <div class="flex items-center gap-2 text-white font-bold text-xs md:text-sm bg-white/10 px-4 py-2.5 rounded-xl border border-white/10 backdrop-blur-sm"><i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i> Leaderboard</div>
                        <div class="flex items-center gap-2 text-white font-bold text-xs md:text-sm bg-white/10 px-4 py-2.5 rounded-xl border border-white/10 backdrop-blur-sm"><i data-lucide="target" class="w-4 h-4 text-emerald-400"></i> Eco-Missions</div>
                    </div>
                </div>
                <div class="relative z-10 md:w-2/5 flex justify-center">
                    <div class="relative w-48 h-48 md:w-64 md:h-64 animate-float">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-orange-600 rounded-full blur-2xl opacity-40 animate-pulse"></div>
                        <div class="relative w-full h-full bg-gradient-to-br from-amber-300 to-amber-500 rounded-full flex items-center justify-center shadow-2xl border-8 border-white/10">
                            <i data-lucide="gamepad-2" class="w-24 h-24 md:w-32 md:h-32 text-white drop-shadow-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 7. LIVE FEED -->
        <?php if($sec['tipe'] === 'live_feed'): ?>
        <section class="bg-slate-50 py-20 relative">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-4">
                    <div class="text-center md:text-left">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 flex items-center justify-center md:justify-start gap-3"><i data-lucide="activity" class="w-8 h-8 text-emerald-600"></i> <?= e($sec['judul']) ?></h2>
                        <p class="text-slate-500 mt-2 text-sm md:text-base"><?= e($sec['subjudul']) ?></p>
                    </div>
                    <a href="index.php?page=arsip" class="flex px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-100 transition shadow-sm items-center gap-2 text-sm hover:scale-105">
                        Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                    <?php if(mysqli_num_rows($res_feed) > 0): while($row = mysqli_fetch_assoc($res_feed)): 
                        $jenis_info = ['sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-600','ic'=>'recycle']];
                        $info = $jenis_info[$row['jenis_objek']] ?? $jenis_info['pohon'];
                    ?>
                    <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 hover-lift flex flex-col h-full group">
                        <?php if(!empty($row['foto']) || !empty($row['telegram_file_id'])): ?>
                            <div class="h-56 overflow-hidden relative">
                                <img src="<?= e(url_foto($row, 'foto', 'thumb')) ?>" onerror="this.src='assets/placeholder.jpg'; this.onerror=null;" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="Foto">
                                <div class="absolute top-4 left-4 <?= $info['bg'] ?> text-white text-[10px] md:text-xs font-bold px-3 py-1.5 rounded-full uppercase flex items-center gap-1.5 shadow-lg backdrop-blur-md bg-opacity-90"><i data-lucide="<?= $info['ic'] ?>" class="w-3.5 h-3.5"></i> <?= e($row['jenis_objek']) ?></div>
                            </div>
                        <?php else: ?>
                            <div class="h-56 <?= $info['bg'] ?> bg-opacity-10 flex flex-col items-center justify-center relative">
                                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center shadow-sm mb-3">
                                    <i data-lucide="<?= $info['ic'] ?>" class="w-10 h-10 <?= str_replace('bg-', 'text-', $info['bg']) ?>"></i>
                                </div>
                                <div class="absolute top-4 left-4 <?= $info['bg'] ?> text-white text-[10px] md:text-xs font-bold px-3 py-1.5 rounded-full uppercase flex items-center gap-1.5 shadow-lg"><i data-lucide="<?= $info['ic'] ?>" class="w-3.5 h-3.5"></i> <?= e($row['jenis_objek']) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="text-[10px] md:text-xs text-slate-400 mb-2 font-bold uppercase tracking-wider flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?= date('d M Y', strtotime($row['tanggal_buat'])) ?></div>
                            <h3 class="font-bold text-lg md:text-xl text-slate-800 mb-2 leading-tight group-hover:text-emerald-600 transition-colors"><?= e($row['nama_objek']) ?></h3>
                            <p class="text-slate-500 text-sm mb-5 line-clamp-3 flex-1"><?= e($row['catatan']) ?></p>
                            
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                                <div class="flex items-center gap-2">
                                    <?= tampil_avatar($row, 'w-8 h-8', 'text-xs') ?>
                                    <a href="index.php?page=user_profil&id=<?= $row['user_id'] ?>" class="text-xs md:text-sm font-bold text-slate-700 hover:text-emerald-600 transition line-clamp-1"><?= e($row['nama_lengkap'] ?? 'Anonim') ?></a>
                                </div>
                                <a href="index.php?page=objek_profil&id=<?= $row['objek_id'] ?>" class="text-[10px] md:text-xs font-bold text-emerald-600 hover:text-white hover:bg-emerald-600 bg-emerald-50 px-4 py-2 rounded-xl transition-all shadow-sm">Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: echo '<div class="col-span-3 text-center py-10 text-slate-500">Belum ada aktivitas publik.</div>'; endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 8. TOP LEADERBOARD -->
        <?php if($sec['tipe'] === 'leaderboard'): ?>
        <section class="max-w-6xl mx-auto px-4 py-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-800 flex items-center justify-center gap-3"><i data-lucide="crown" class="w-8 h-8 md:w-10 md:h-10 text-amber-500"></i> <?= e($sec['judul']) ?></h2>
                <p class="text-slate-500 mt-3 text-sm md:text-lg"><?= e($sec['subjudul']) ?></p>
            </div>

            <?php if(empty($leaderboard)): ?>
    <div class="text-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm animate-fade-up">
        <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="crown" class="w-12 h-12 text-amber-300"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800">Belum Ada Pahlawan Lingkungan</h3>
        <p class="text-slate-500 mt-2 max-w-md mx-auto">Jadilah yang pertama mendaftarkan objek dan buat laporan untuk mengisi papan peringkat ini!</p>
        <a href="index.php?page=mulai_aksi" class="inline-block mt-6 px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-md">Mulai Aksi Sekarang</a>
    </div>
<?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 items-end">
                    <!-- Peringkat 2 (Kiri) -->
                    <?php if(isset($leaderboard[1])): $lb2 = $leaderboard[1]; ?>
                    <div class="md:col-span-1 bg-gradient-to-t from-slate-200 to-white rounded-3xl p-6 flex flex-col items-center text-center border-2 border-slate-300 shadow-xl relative order-2 md:order-1">
                        <div class="absolute -top-6 w-12 h-12 bg-slate-300 rounded-full flex items-center justify-center text-2xl border-4 border-white shadow-md">🥈</div>
                        <div class="mt-6 text-xl font-bold text-slate-800 truncate w-full flex items-center justify-center gap-1"><?= e($lb2['nama_lengkap']) ?> <?= tampil_badge_verified($lb2['role']) ?></div>
                        <div class="text-xs font-bold text-slate-500 mb-4 truncate w-full"><?= e($lb2['organisasi'] ?: 'Individu') ?></div>
                        <div class="bg-white/80 px-6 py-3 rounded-2xl w-full border border-slate-200">
                            <span class="font-black text-3xl text-slate-700"><?= number_format($lb2['total_laporan']) ?></span> <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Laporan</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Peringkat 1 (Tengah) -->
                    <?php if(isset($leaderboard[0])): $top1 = $leaderboard[0]; ?>
                    <div class="md:col-span-1 bg-gradient-to-t from-amber-200 to-amber-50 rounded-[2.5rem] p-8 flex flex-col items-center text-center border-4 border-amber-400 shadow-2xl relative order-1 md:order-2 transform md:-translate-y-6">
                        <div class="absolute -top-10 w-20 h-20 bg-amber-300 rounded-full flex items-center justify-center text-4xl border-4 border-white shadow-xl animate-bounce" style="animation-duration: 2s;">🥇</div>
                        <div class="mt-6 text-xl font-bold text-slate-800 truncate w-full flex items-center justify-center gap-1"><?= e($top1['nama_lengkap']) ?> <?= tampil_badge_verified($top1['role']) ?></div>
                        <div class="text-sm font-bold text-amber-500 mb-6 truncate w-full"><?= e($top1['organisasi'] ?: 'Individu') ?></div>
                        <div class="bg-white px-8 py-4 rounded-2xl w-full shadow-inner border border-amber-200">
                            <span class="font-black text-5xl text-amber-700"><?= number_format($top1['total_laporan']) ?></span> <span class="text-xs font-bold text-slate-500 uppercase tracking-widest block mt-1">Total Laporan</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Peringkat 3 (Kanan) -->
                    <?php if(isset($leaderboard[2])): $lb3 = $leaderboard[2]; ?>
                    <div class="md:col-span-1 bg-gradient-to-t from-orange-200 to-orange-50 rounded-3xl p-6 flex flex-col items-center text-center border-2 border-orange-300 shadow-xl relative order-3 md:order-3">
                        <div class="absolute -top-6 w-12 h-12 bg-orange-300 rounded-full flex items-center justify-center text-2xl border-4 border-white shadow-md">🥉</div>
                        <div class="mt-6 text-xl font-bold text-slate-800 truncate w-full flex items-center justify-center gap-1"><?= e($lb3['nama_lengkap']) ?> <?= tampil_badge_verified($lb3['role']) ?></div>
                        <div class="text-xs font-bold text-slate-500 mb-4 truncate w-full"><?= e($lb3['organisasi'] ?: 'Individu') ?></div>
                        <div class="bg-white/80 px-6 py-3 rounded-2xl w-full border border-orange-200">
                            <span class="font-black text-3xl text-orange-700"><?= number_format($lb3['total_laporan']) ?></span> <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Laporan</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- 9. TEXT & IMAGE -->
        <?php if($sec['tipe'] === 'text_image'): ?>
        <section class="py-20" style="background-color: <?= e($sec['warna_bg'] ?: '#ffffff') ?>;">
            <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center gap-12">
                <div class="flex-1 text-center md:text-left">
                    <span class="text-emerald-600 font-bold tracking-widest uppercase text-xs mb-2 block">Kenali Kami Lebih Dekat</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6"><?= e($sec['judul']) ?></h2>
                    <p class="text-slate-600 leading-relaxed mb-8 text-base"><?= nl2br(e($sec['konten'])) ?></p>
                    <a href="index.php?page=mulai_aksi" class="inline-block px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-lg">Pelajari Selengkapnya</a>
                </div>
                <?php if($sec['gambar']): ?>
                <div class="flex-1 w-full relative group">
                    <div class="absolute inset-0 bg-emerald-600 rounded-3xl rotate-3 group-hover:rotate-6 transition-transform duration-300"></div>
                    <img src="<?= UPLOAD_DIR . e($sec['gambar']) ?>" class="relative w-full rounded-3xl shadow-xl z-10 border-4 border-white" alt="<?= e($sec['judul']) ?>">
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- 10. WIDGET TAMBAHAN (VIDEO, FAQ, TESTI, CTA)-->
        <?php if($sec['tipe'] === 'video'): ?>
        <section class="py-20 bg-slate-50">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h2 class="text-3xl font-extrabold text-slate-800 mb-2"><?= e($sec['judul']) ?></h2>
                <p class="text-slate-500 mb-8"><?= e($sec['subjudul']) ?></p>
                <div class="relative pb-[56.25%] h-0 rounded-3xl overflow-hidden shadow-2xl border-8 border-white bg-slate-200">
                    <iframe class="absolute top-0 left-0 w-full h-full" src="<?= e($sec['konten']) ?>" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if($sec['tipe'] === 'faq'): ?>
        <section class="py-20 bg-white">
            <div class="max-w-3xl mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold text-slate-800"><?= e($sec['judul']) ?></h2>
                </div>
                <div class="space-y-4">
                    <?php 
                    $faqs = explode("\n", $sec['konten']); 
                    foreach($faqs as $faq): 
                        if(empty(trim($faq))) continue;
                        list($q, $a) = array_pad(explode('|', $faq, 2), 2, '');
                    ?>
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                        <details class="group">
                            <summary class="flex justify-between items-center p-5 cursor-pointer list-none font-bold text-slate-800 hover:bg-slate-100 transition">
                                <span class="flex items-center gap-3"><i data-lucide="help-circle" class="w-5 h-5 text-emerald-600"></i> <?= e(trim($q)) ?></span>
                                <span class="transition group-open:rotate-180"><i data-lucide="chevron-down" class="w-5 h-5"></i></span>
                            </summary>
                            <div class="p-5 pt-0 text-slate-600 text-sm leading-relaxed border-t border-slate-100 mt-2"><?= nl2br(e(trim($a))) ?></div>
                        </details>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if($sec['tipe'] === 'testimonial'): ?>
        <section class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-extrabold text-slate-800"><?= e($sec['judul']) ?></h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php 
                    $testis = explode("\n", $sec['konten']); 
                    foreach($testis as $testi): 
                        if(empty(trim($testi))) continue;
                        list($nama, $peran, $teks) = array_pad(explode('|', $testi, 3), 3, '');
                    ?>
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-lg hover-lift relative">
                        <i data-lucide="quote" class="w-10 h-10 text-emerald-100 absolute top-6 right-6"></i>
                        <p class="text-slate-700 italic mb-8 relative z-10 text-sm leading-relaxed">"<?= e(trim($teks)) ?>"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center font-bold text-xl shadow-md"><?= strtoupper(substr(trim($nama), 0, 1)) ?></div>
                            <div>
                                <div class="font-bold text-slate-900"><?= e(trim($nama)) ?></div>
                                <div class="text-xs font-semibold text-emerald-600"><?= e(trim($peran)) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if($sec['tipe'] === 'cta'): ?>
        <section class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-[3rem] p-10 md:p-20 text-center relative overflow-hidden shadow-2xl">
                    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    <h2 class="text-3xl md:text-5xl font-black text-white mb-6 relative z-10 leading-tight"><?= e($sec['judul']) ?></h2>
                    <p class="text-emerald-100 text-base md:text-xl mb-10 relative z-10 max-w-2xl mx-auto"><?= e($sec['subjudul']) ?></p>
                    <div class="relative z-10">
                        <a href="<?= e($sec['konten'] ?: 'index.php?page=mulai_aksi') ?>" class="inline-flex items-center gap-2 bg-white text-emerald-800 font-black px-10 py-4 rounded-2xl hover:bg-amber-400 hover:text-slate-900 hover:scale-105 transition-all shadow-xl text-base md:text-lg">
                            Mulai Aksi Sekarang <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
    <?php endwhile; ?> 
</main>

<!-- SCRIPT ANIMASI ANGKA (MURNI JAVASCRIPT - TANPA LIBRARY) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Beri jeda sedikit agar layout selesai dirender
    setTimeout(() => {
        const counters = document.querySelectorAll('.anim-count');
        
        counters.forEach(counter => {
            const target = parseFloat(counter.getAttribute('data-target')) || 0;
            
            // Jika target 0, langsung set 0
            if (target === 0) {
                counter.innerText = '0';
                return;
            }

            let start = 0;
            const duration = 2000; // 2 detik
            const startTime = performance.now();

            function updateCount(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Fungsi Easing (agar melambat di akhir)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const currentVal = start + (target - start) * easeOut;
                
                // Format ribuan (misal: 3.426)
                counter.innerText = Math.floor(currentVal).toLocaleString('id-ID');
                
                if (progress < 1) {
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target.toLocaleString('id-ID');
                }
            }
            
            requestAnimationFrame(updateCount);
        });
    }, 300); // Jeda 300ms
});

// 2. Script Grafik Radar (Diperbaiki & Animasi Lebih Kentara)
    document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('modulChart');
    if(canvas) {
        let chartInstance = null;
        
        // TRIK: Gunakan Intersection Observer agar animasi mulai saat di-scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Jika grafik sudah terlihat di layar dan belum pernah digambar
                if (entry.isIntersecting && !chartInstance) {
                    const ctx = canvas.getContext('2d');
                    
                    chartInstance = new Chart(ctx, {
                        type: 'radar',
                        data: {
                            labels: <?= json_encode($grafik_labels) ?>,
                            datasets: [{
                                label: 'Jumlah Objek',
                                data: <?= json_encode($grafik_data) ?>,
                                backgroundColor: 'rgba(16, 185, 129, 0.25)', // Sedikit lebih tebal
                                borderColor: '#10b981',
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#10b981',
                                pointBorderWidth: 3, // Titik lebih tebal
                                pointRadius: 6,      // Titik lebih besar
                                borderWidth: 3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            // ANIMASI LEBIH KENTARA
                            animation: {
                                duration: 2,
                                easing: 'easeOutElastic', // Efek memantul saat muncul
                                animateScale: true,       // Membesar dari tengah (Sangat kentara di Radar)
                                animateRotate: true       // Berputar saat muncul
                            },
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                r: {
                                    angleLines: { color: 'rgba(0, 0, 0, 0.08)' },
                                    grid: { color: 'rgba(0, 0, 0, 0.08)' },
                                    pointLabels: {
                                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: 'bold' },
                                        color: '#475569'
                                    },
                                    ticks: { display: false, beginAtZero: true }
                                }
                            }
                        }
                    });
                    
                    // Hentikan pengamatan setelah grafik digambar
                    observer.unobserve(canvas);
                }
            });
        }, { threshold: 0.2 }); // Mulai animasi saat 20% grafik terlihat
        
        // Mulai amati canvas grafik
        observer.observe(canvas);
    }
});

</script>