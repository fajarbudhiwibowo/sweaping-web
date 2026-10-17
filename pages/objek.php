<?php
// Logika Filter & Pencarian
$where = ["status = 'aktif'", "deleted_at IS NULL"];
$params = []; $types = '';

if (!empty($_GET['f_jenis']) && in_array($_GET['f_jenis'], ['sungai','pohon','taman','komposting'])) {
    $where[] = "jenis_objek = ?"; $params[] = $_GET['f_jenis']; $types .= 's';
}
if (!empty($_GET['f_cari'])) {
    $where[] = "(nama_objek LIKE ? OR kode_objek LIKE ? OR lokasi LIKE ?)";
    $cari = '%' . $_GET['f_cari'] . '%';
    $params[] = $cari; $params[] = $cari; $params[] = $cari; $types .= 'sss';
}

$sql = "SELECT * FROM objek WHERE " . implode(' AND ', $where) . " ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res_objek = mysqli_stmt_get_result($stmt);
$data_objek = [];
while ($row = mysqli_fetch_assoc($res_objek)) $data_objek[] = $row;
?>

<main class="max-w-7xl mx-auto px-4 pt-24 pb-16">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="package" class="w-8 h-8 text-emerald-600"></i> Daftar Objek
            </h1>
            <p class="text-slate-500 mt-2">Master data pemantauan lingkungan (Pohon, Sungai, Taman, Kompos).</p>
        </div>
        <?php if(is_logged_in()): ?>
            <a href="index.php?page=objek_daftar" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 transition">
                <i data-lucide="plus" class="w-5 h-5"></i> Tambah Objek Baru
            </a>
        <?php endif; ?>
    </div>

    <!-- Form Filter & Search -->
    <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-8 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="hidden" name="page" value="objek">
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="f_cari" value="<?= e($_GET['f_cari'] ?? '') ?>" placeholder="Cari nama, kode, atau lokasi..." class="w-full pl-9 pr-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none">
        </div>
        <select name="f_jenis" class="px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            <option value="">-- Semua Jenis --</option>
            <option value="pohon" <?= ($_GET['f_jenis']??'')=='pohon'?'selected':'' ?>>🌳 Pohon</option>
            <option value="sungai" <?= ($_GET['f_jenis']??'')=='sungai'?'selected':'' ?>>🌊 Sungai</option>
            <option value="taman" <?= ($_GET['f_jenis']??'')=='taman'?'selected':'' ?>>🌷 Taman</option>
            <option value="komposting" <?= ($_GET['f_jenis']??'')=='komposting'?'selected':'' ?>>♻️ Komposting</option>
        </select>
        <div class="flex gap-2 md:col-span-2">
            <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition">Terapkan Filter</button>
            <a href="index.php?page=objek" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
        </div>
    </form>

    <!-- Grid Objek -->
    <?php if(empty($data_objek)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200">
            <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-bold text-slate-700">Belum Ada Objek</h3>
            <p class="text-slate-500 mt-1">Objek yang Anda daftarkan akan muncul di sini.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($data_objek as $o): 
                $info = ['pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-500','ic'=>'recycle']][$o['jenis_objek']] ?? ['bg'=>'bg-slate-500','ic'=>'package'];
            ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col">
                <div class="relative h-48 bg-slate-100 overflow-hidden">
                    <?php if(!empty($o['foto_pertama']) || !empty($o['telegram_file_id'])): ?>
                        <img src="<?= e(url_foto($o, 'foto_pertama', 'thumb')) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Foto Objek" onerror="this.style.display='none';">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center <?= $info['bg'] ?> bg-opacity-10">
                            <i data-lucide="<?= $info['ic'] ?>" class="w-16 h-16 <?= str_replace('bg-', 'text-', $info['bg']) ?> opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-4 left-4 <?= $info['bg'] ?> text-white text-xs font-bold px-3 py-1 rounded-full uppercase shadow-md flex items-center gap-1">
                        <i data-lucide="<?= $info['ic'] ?>" class="w-3 h-3"></i> <?= e($o['jenis_objek']) ?>
                    </div>
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <div class="text-xs font-mono text-slate-400 mb-1"><?= e($o['kode_objek']) ?></div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight group-hover:text-emerald-600 transition-colors"><?= e($o['nama_objek']) ?></h3>
                    <p class="text-sm text-slate-500 flex items-start gap-1.5 mb-4 line-clamp-2">
                        <i data-lucide="map-pin" class="w-4 h-4 flex-shrink-0 mt-0.5"></i> <?= e($o['lokasi'] ?: 'Lokasi tidak spesifik') ?>
                    </p>
                    <div class="mt-auto pt-4 border-t border-slate-100 flex gap-2">
                        <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="flex-1 text-center py-2 bg-slate-50 text-slate-700 font-bold rounded-lg hover:bg-slate-100 transition text-sm">Lihat Profil</a>
                        <?php if(is_logged_in() && ($o['user_id'] == $_SESSION['user_id'] || is_admin())): ?>
                            <a href="index.php?page=objek_edit&id=<?= $o['id'] ?>" class="px-4 py-2 bg-blue-50 text-blue-700 font-bold rounded-lg hover:bg-blue-100 transition text-sm">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
