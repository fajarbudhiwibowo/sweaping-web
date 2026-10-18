<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$area_id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT a.*, o.nama as nama_org FROM area_pantau a LEFT JOIN organisasi o ON a.organisasi_id = o.id WHERE a.id = ? AND a.deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $area_id);
mysqli_stmt_execute($stmt);
$area = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$area) { echo "<div class='pt-32 text-center text-slate-500'>Area tidak ditemukan.</div>"; exit; }

// 1. Hitung Total Serapan Karbon dari SEMUA objek di area ini (berdasarkan laporan terakhir)
$q_karbon = "
    SELECT COALESCE(SUM(COALESCE(l.serapan_karbon_ilmiah, l.serapan_karbon, 0)), 0) as total_karbon
    FROM laporan_aksi l
    INNER JOIN objek o ON l.objek_id = o.id
    WHERE o.area_id = ? AND o.deleted_at IS NULL AND l.deleted_at IS NULL
    AND l.id = (SELECT MAX(id) FROM laporan_aksi WHERE objek_id = l.objek_id AND deleted_at IS NULL)
";
$stmt_k = mysqli_prepare($conn, $q_karbon);
mysqli_stmt_bind_param($stmt_k, 'i', $area_id);
mysqli_stmt_execute($stmt_k);
$res_k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_k));
$total_karbon_area = (float)($res_k['total_karbon'] ?? 0);
mysqli_stmt_close($stmt_k);

// 2. Ambil Objek Publik di dalam Area ini
$stmt_o = mysqli_prepare($conn, "SELECT o.*, u.nama_lengkap FROM objek o LEFT JOIN users u ON o.user_id = u.id WHERE o.area_id = ? AND o.deleted_at IS NULL AND o.visibility = 'publik' ORDER BY o.id DESC");
mysqli_stmt_bind_param($stmt_o, 'i', $area_id);
mysqli_stmt_execute($stmt_o);
$q_objek = mysqli_stmt_get_result($stmt_o);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<main class="max-w-5xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-6">
        <a href="index.php?page=area" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Area
        </a>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                    <i data-lucide="map" class="w-8 h-8 text-emerald-600"></i> <?= e($area['nama_area']) ?>
                </h1>
                <div class="text-sm font-mono text-slate-500 mt-1">Kode: <?= e($area['kode']) ?></div>
            </div>
            
            <!-- Kotak Statistik: Karbon & Luas -->
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-6 py-3 text-center flex-1 md:flex-none">
                    <div class="text-xs font-bold text-amber-600 uppercase tracking-wide flex items-center justify-center gap-1">
                        <i data-lucide="wind" class="w-4 h-4"></i> Serapan CO₂
                    </div>
                    <div class="text-xl font-extrabold text-amber-800"><?= number_format($total_karbon_area, 2, ',', '.') ?> kg</div>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-6 py-3 text-center flex-1 md:flex-none">
                    <div class="text-xs font-bold text-emerald-600 uppercase tracking-wide flex items-center justify-center gap-1">
                        <i data-lucide="expand" class="w-4 h-4"></i> Luas Area
                    </div>
                    <div class="text-xl font-extrabold text-emerald-800"><?= number_format($area['luas_m2'], 0, ',', '.') ?> m²</div>
                    <div class="text-xs text-emerald-600">(<?= number_format($area['luas_m2']/10000, 2) ?> Ha)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Kolom Kiri: Info -->
        <div class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="text-xs font-bold text-slate-500 uppercase mb-1">Jenis Area</div>
                <div class="font-bold text-slate-800 text-lg uppercase"><?= e($area['jenis']) ?></div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="text-xs font-bold text-slate-500 uppercase mb-1">Pengelola</div>
                <div class="font-bold text-slate-800"><?= e($area['nama_org'] ?: 'Individu') ?></div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi</div>
                <div class="text-sm text-slate-600 leading-relaxed"><?= nl2br(e($area['deskripsi'] ?: 'Tidak ada deskripsi.')) ?></div>
            </div>
        </div>

        <!-- Kolom Kanan: Peta Poligon -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-4 h-full flex flex-col">
                <div class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-emerald-600"></i> Batas Wilayah Area Pantau</div>
                <div id="map" class="w-full flex-1 min-h-[350px] rounded-2xl border border-slate-200 relative z-0"></div>
            </div>
        </div>
    </div>

    <!-- DAFTAR OBJEK DI DALAM AREA -->
    <div class="border-t-4 border-slate-100 pt-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="package" class="w-6 h-6 text-emerald-600"></i> Objek Terdaftar di Area Ini
            </h2>
            <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-sm font-bold"><?= mysqli_num_rows($q_objek) ?> Objek</span>
        </div>

        <?php if(mysqli_num_rows($q_objek) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php while($o = mysqli_fetch_assoc($q_objek)): 
                    $info = ['pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-500','ic'=>'recycle']][$o['jenis_objek']] ?? ['bg'=>'bg-slate-500','ic'=>'package'];
                ?>
                    <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="bg-white rounded-2xl border border-slate-100 p-4 flex flex-col hover:shadow-md transition group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl <?= $info['bg'] ?> bg-opacity-10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="<?= $info['ic'] ?>" class="w-6 h-6 <?= str_replace('bg-', 'text-', $info['bg']) ?>"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-mono text-slate-400"><?= e($o['kode_objek']) ?></div>
                                <div class="font-bold text-slate-800 truncate group-hover:text-emerald-600 transition"><?= e($o['nama_objek']) ?></div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 truncate mb-2"><i data-lucide="map-pin" class="w-3 h-3 inline"></i> <?= e($o['lokasi'] ?: '-') ?></div>
                        <div class="mt-auto pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 uppercase font-bold"><?= e($o['jenis_objek']) ?></span>
                            <span class="text-xs font-semibold text-slate-600"><?= e($o['nama_lengkap'] ?? 'Anonim') ?></span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mx-auto mb-2"></i>
                <p class="text-slate-500 font-bold">Belum ada objek publik di area ini.</p>
                <p class="text-xs text-slate-400 mt-1">Saat Anda mendaftarkan objek baru, pilih Area Pantau ini agar muncul di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([-2.548926, 118.0148634], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    var geojsonStr = `<?= $area['geojson_polygon'] ?>`;
    if (geojsonStr && geojsonStr !== '') {
        try {
            var geojsonData = JSON.parse(geojsonStr);
            var polygonLayer = L.geoJSON(geojsonData, {
                style: { color: '#10b981', weight: 3, fillColor: '#10b981', fillOpacity: 0.3 }
            }).addTo(map);
            map.fitBounds(polygonLayer.getBounds());
        } catch(e) { console.error('Gagal memuat peta area:', e); }
    }
});
</script>
