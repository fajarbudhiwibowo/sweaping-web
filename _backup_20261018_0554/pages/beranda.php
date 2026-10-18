<?php
// Ambil data statistik (Query yang sudah dioptimalkan)
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) as total,
    SUM(CASE WHEN jenis_objek='pohon' THEN 1 ELSE 0 END) as total_pohon,
    COALESCE(SUM(serapan_karbon),0) as total_karbon,
    COUNT(DISTINCT nama_kontributor) as total_kontributor
FROM laporan_aksi WHERE deleted_at IS NULL AND visibility = 'publik'
"));

$res_feed = mysqli_query($conn, "SELECT l.*, u.nama_lengkap, u.avatar FROM laporan_aksi l LEFT JOIN users u ON l.user_id = u.id WHERE l.deleted_at IS NULL AND l.visibility = 'publik' ORDER BY l.tanggal_buat DESC LIMIT 3");

$res_lb = mysqli_query($conn, "SELECT u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar, COUNT(l.id) as total_laporan FROM users u INNER JOIN laporan_aksi l ON l.user_id = u.id WHERE l.deleted_at IS NULL GROUP BY u.id ORDER BY total_laporan DESC LIMIT 5");
$leaderboard = []; while ($lb = mysqli_fetch_assoc($res_lb)) $leaderboard[] = $lb;
?>

<style>
    .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.4); }
    .hover-lift { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s; }
    .hover-lift:hover { transform: translateY(-6px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); }
    .bg-grid-pattern { background-image: radial-gradient(#10b981 1px, transparent 1px); background-size: 20px 20px; opacity: 0.1; }
</style>

<!-- HERO SECTION ENTERPRISE -->
<section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden bg-slate-900 pt-20">
    <!-- Background Image & Gradient -->
    <div class="absolute inset-0 z-0">
        <img src="assets/hero-bg.jpg" class="w-full h-full object-cover opacity-40" alt="Hero BG" onerror="this.style.display='none'">
        <div class="absolute inset-0 bg-gradient-to-b from-emerald-900/60 via-slate-900/80 to-slate-900"></div>
        <div class="absolute inset-0 bg-grid-pattern"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 text-center mt-[-5vh]">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-sm font-bold mb-6 backdrop-blur-md">
            <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span></span>
            SWEAPING v6.0 Telah Hadir
        </div>
        
        <h1 class="text-4xl md:text-7xl font-extrabold text-white leading-tight mb-6 tracking-tight drop-shadow-2xl">
            Hulu Jaga Hilir,<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-amber-300">Merawat Alam Indonesia</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed">
            Platform konservasi & akuntansi karbon terpadu. Bergabunglah dengan ribuan komunitas dalam mendokumentasikan aksi nyata untuk bumi.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
            <?php if(is_logged_in()): ?>
                <a href="index.php?page=lapor" class="w-full sm:w-auto px-8 py-4 bg-amber-500 text-slate-900 font-extrabold rounded-2xl shadow-[0_0_20px_rgba(245,158,11,0.4)] hover:bg-amber-400 hover:scale-105 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Buat Laporan Baru
                </a>
            <?php else: ?>
                <a href="index.php?page=login" class="w-full sm:w-auto px-8 py-4 bg-emerald-600 text-white font-extrabold rounded-2xl shadow-[0_0_20px_rgba(16,185,129,0.4)] hover:bg-emerald-500 hover:scale-105 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-5 h-5"></i> Masuk ke Platform
                </a>
            <?php endif; ?>
            <a href="#fitur" class="w-full sm:w-auto px-8 py-4 bg-white/10 text-white font-bold rounded-2xl border border-white/20 hover:bg-white/20 transition-all flex items-center justify-center gap-2 backdrop-blur-md">
                Jelajahi Fitur
            </a>
        </div>
    </div>
</section>

<!-- FLOATING STATS (Glassmorphism) -->
<section class="relative z-20 max-w-5xl mx-auto px-4 -mt-16 mb-20">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="glass-panel rounded-3xl p-6 text-center hover-lift">
            <div class="text-3xl md:text-4xl font-black text-slate-800"><?= number_format($stats['total']) ?></div>
            <div class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider mt-2">Total Aksi</div>
        </div>
        <div class="glass-panel rounded-3xl p-6 text-center hover-lift">
            <div class="text-3xl md:text-4xl font-black text-emerald-600"><?= number_format($stats['total_pohon']) ?></div>
            <div class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider mt-2">Pohon Dipantau</div>
        </div>
        <div class="glass-panel rounded-3xl p-6 text-center hover-lift border-2 border-amber-300/50">
            <div class="text-3xl md:text-4xl font-black text-amber-600"><?= number_format($stats['total_karbon'], 0) ?></div>
            <div class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider mt-2">Kg CO₂ Terserap</div>
        </div>
        <div class="glass-panel rounded-3xl p-6 text-center hover-lift">
            <div class="text-3xl md:text-4xl font-black text-slate-800"><?= number_format($stats['total_kontributor']) ?></div>
            <div class="text-xs md:text-sm font-bold text-slate-500 uppercase tracking-wider mt-2">Kontributor</div>
        </div>
    </div>
</section>

<!-- ECO-CHALLENGES TEASER (V6 GAMIFICATION) -->
<section class="max-w-7xl mx-auto px-4 py-16">
    <div class="bg-gradient-to-r from-emerald-900 to-slate-900 rounded-[2.5rem] p-8 md:p-12 relative overflow-hidden flex flex-col md:flex-row items-center justify-between shadow-2xl">
        <div class="relative z-10 md:w-2/3 mb-8 md:mb-0">
            <span class="inline-flex items-center gap-1 text-amber-400 text-xs font-bold uppercase tracking-widest mb-3"><i data-lucide="zap" class="w-4 h-4"></i> Fitur Baru v6</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Selesaikan Misi,<br>Dapatkan Reward Eksklusif!</h2>
            <p class="text-slate-300 mb-6 text-sm md:text-base leading-relaxed">Dokumentasikan aksi lingkunganmu, kumpulkan Eco-Points, dan naikkan level akunmu. Buka fitur eksklusif seperti <b>Export Data</b> dan <b>Area Mapping</b>.</p>
            <div class="flex gap-4">
                <div class="flex items-center gap-2 text-white font-bold text-sm bg-white/10 px-4 py-2 rounded-xl border border-white/10"><i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i> Leaderboard</div>
                <div class="flex items-center gap-2 text-white font-bold text-sm bg-white/10 px-4 py-2 rounded-xl border border-white/10"><i data-lucide="target" class="w-4 h-4 text-emerald-400"></i> Eco-Missions</div>
            </div>
        </div>
        <div class="relative z-10 md:w-1/3 flex justify-center">
            <div class="w-32 h-32 md:w-48 md:h-48 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center shadow-[0_0_50px_rgba(245,158,11,0.5)] border-4 border-white/20">
                <i data-lucide="medal" class="w-16 h-16 md:w-24 md:h-24 text-white"></i>
            </div>
        </div>
        <!-- Decorative shapes -->
        <i data-lucide="leaf" class="absolute -top-4 -left-4 w-32 h-32 text-emerald-500 opacity-20 rotate-45"></i>
        <i data-lucide="sprout" class="absolute -bottom-8 right-32 w-40 h-40 text-emerald-500 opacity-10"></i>
    </div>
</section>

<!-- LIVE FEED SECTION -->
<section class="bg-slate-50 py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3"><i data-lucide="activity" class="w-8 h-8 text-emerald-600"></i> Aktivitas Terbaru</h2>
                <p class="text-slate-500 mt-2">Pantau aksi nyata dari komunitas di seluruh Indonesia.</p>
            </div>
            <a href="index.php?page=arsip" class="hidden md:flex px-6 py-2.5 bg-white text-emerald-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-100 transition shadow-sm items-center gap-2">Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if(mysqli_num_rows($res_feed) > 0): while($row = mysqli_fetch_assoc($res_feed)): 
                $jenis_info = ['sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-600','ic'=>'recycle']];
                $info = $jenis_info[$row['jenis_objek']] ?? $jenis_info['pohon'];
            ?>
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 hover-lift flex flex-col h-full">
                <?php if(!empty($row['foto']) || !empty($row['telegram_file_id'])): ?>
                    <div class="h-48 overflow-hidden relative group">
                        <img src="<?= e(url_foto($row, 'foto', 'thumb')) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="Foto">
                        <div class="absolute top-4 left-4 <?= $info['bg'] ?> text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase flex items-center gap-1.5 shadow-lg"><i data-lucide="<?= $info['ic'] ?>" class="w-3.5 h-3.5"></i> <?= e($row['jenis_objek']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="h-24 <?= $info['bg'] ?> flex items-center justify-center relative">
                        <i data-lucide="<?= $info['ic'] ?>" class="w-12 h-12 text-white/50"></i>
                    </div>
                <?php endif; ?>
                
                <div class="p-6 flex-1 flex flex-col">
                    <div class="text-xs text-slate-400 mb-2 font-semibold flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?= date('d M Y', strtotime($row['tanggal_buat'])) ?></div>
                    <h3 class="font-bold text-xl text-slate-800 mb-2 leading-tight"><?= e($row['nama_objek']) ?></h3>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-3 flex-1"><?= e($row['catatan']) ?></p>
                    
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                        <div class="flex items-center gap-2">
                            <?= tampil_avatar($row, 'w-8 h-8', 'text-xs') ?>
                            <a href="index.php?page=user_profil&id=<?= $row['user_id'] ?>" class="text-sm font-bold text-slate-700 hover:text-emerald-600 transition"><?= e($row['nama_lengkap'] ?? 'Anonim') ?></a>
                        </div>
                        <a href="index.php?page=objek_profil&id=<?= $row['objek_id'] ?>" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg transition">Detail</a>
                    </div>
                </div>
            </div>
            <?php endwhile; else: echo '<div class="col-span-3 text-center py-10 text-slate-500">Belum ada aktivitas publik.</div>'; endif; ?>
        </div>
        
        <div class="mt-8 text-center md:hidden">
            <a href="index.php?page=arsip" class="inline-flex px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-100 transition shadow-sm items-center gap-2">Lihat Semua Aktivitas</a>
        </div>
    </div>
</section>

<!-- TOP LEADERBOARD (Bento Grid Style) -->
<section class="max-w-7xl mx-auto px-4 py-20">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-extrabold text-slate-900 flex items-center justify-center gap-3"><i data-lucide="crown" class="w-8 h-8 text-amber-500"></i> Top Kontributor</h2>
        <p class="text-slate-500 mt-2">Apresiasi untuk pahlawan lingkungan paling aktif.</p>
    </div>

    <?php if(empty($leaderboard)): ?>
        <div class="text-center py-10 text-slate-500">Belum ada data kontributor.</div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Peringkat 1 (Besar) -->
            <?php if(isset($leaderboard[0])): $top1 = $leaderboard[0]; ?>
            <div class="md:col-span-3 bg-gradient-to-r from-amber-100 to-amber-50 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between border-2 border-amber-200 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -top-10 opacity-10"><i data-lucide="crown" class="w-64 h-64 text-amber-600"></i></div>
                <div class="flex items-center gap-6 relative z-10 w-full md:w-auto mb-6 md:mb-0">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-white shadow-lg flex items-center justify-center text-4xl md:text-5xl border-4 border-amber-400">🥇</div>
                    <div class="text-center md:text-left">
                        <div class="text-sm font-bold text-amber-700 uppercase tracking-wider mb-1">Peringkat 1</div>
                        <div class="text-2xl md:text-3xl font-black text-slate-800 flex items-center md:justify-start justify-center gap-2"><?= e($top1['nama_lengkap']) ?> <?= tampil_badge_verified($top1['role']) ?></div>
                        <div class="text-slate-600 font-semibold mt-1"><?= e($top1['organisasi'] ?: 'Individu') ?></div>
                    </div>
                </div>
                <div class="relative z-10 text-center md:text-right bg-white px-6 py-4 rounded-2xl shadow-sm border border-amber-200">
                    <div class="text-4xl font-black text-emerald-600"><?= number_format($top1['total_laporan']) ?></div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Total Laporan</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Peringkat 2 & 3 -->
            <?php for($i=1; $i<=2; $i++): if(isset($leaderboard[$i])): $lb = $leaderboard[$i]; $medal = $i==1 ? '🥈' : '🥉'; $bg = $i==1 ? 'bg-slate-100 border-slate-300' : 'bg-orange-100 border-orange-300'; ?>
            <div class="md:col-span-1 <?= $bg ?> rounded-3xl p-6 flex flex-col items-center text-center border-2 shadow-lg relative">
                <div class="w-16 h-16 rounded-full bg-white shadow-md flex items-center justify-center text-3xl mb-4 border-2 border-white"><?= $medal ?></div>
                <div class="text-xl font-bold text-slate-800 flex items-center gap-1 truncate w-full justify-center"><?= e($lb['nama_lengkap']) ?> <?= tampil_badge_verified($lb['role']) ?></div>
                <div class="text-sm font-semibold text-slate-500 mb-4 truncate w-full"><?= e($lb['organisasi'] ?: 'Individu') ?></div>
                <div class="mt-auto bg-white/60 px-4 py-2 rounded-xl w-full">
                    <span class="font-black text-2xl text-emerald-600"><?= number_format($lb['total_laporan']) ?></span> <span class="text-xs font-bold text-slate-500 uppercase">Laporan</span>
                </div>
            </div>
            <?php endif; endfor; ?>
            
            <!-- Peringkat 4 & 5 (Compact List) -->
            <div class="md:col-span-1 bg-white rounded-3xl p-6 border border-slate-200 shadow-md">
                <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-3 mb-3 flex items-center gap-2"><i data-lucide="list" class="w-4 h-4 text-slate-400"></i> Papan Peringkat</h4>
                <div class="space-y-3">
                    <?php for($i=3; $i<=4; $i++): if(isset($leaderboard[$i])): $lb = $leaderboard[$i]; ?>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500 text-sm shrink-0"><?= $i+1 ?></div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-800 truncate"><?= e($lb['nama_lengkap']) ?></div>
                                <div class="text-[10px] text-slate-500 truncate"><?= e($lb['organisasi'] ?: 'Individu') ?></div>
                            </div>
                        </div>
                        <div class="font-bold text-sm text-emerald-600 shrink-0"><?= number_format($lb['total_laporan']) ?></div>
                    </div>
                    <?php endif; endfor; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
