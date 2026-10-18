<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
$my_id = (int)$_SESSION['user_id'];

// Ambil Tab Aktif
$tab = $_GET['tab'] ?? 'semua';
$allowed_tabs = ['semua', 'pohon', 'sungai', 'taman', 'komposting'];
if (!in_array($tab, $allowed_tabs)) $tab = 'semua';

// Filter & Pencarian
$where = ["status = 'aktif'", "deleted_at IS NULL"]; $params = []; $types = '';

if ($tab !== 'semua') {
    $where[] = "jenis_objek = ?"; $params[] = $tab; $types .= 's';
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
            <p class="text-slate-500 mt-2">Master data pemantauan lingkungan.</p>
        </div>
        <a href="index.php?page=objek_daftar" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 transition">
            <i data-lucide="plus" class="w-5 h-5"></i> Tambah Objek Baru
        </a>
    </div>

    <!-- TABS KLASIFIKASI -->
    <div class="flex border-b border-slate-200 mb-6 overflow-x-auto">
        <?php 
        $tabs = [
            'semua' => ['icon' => 'layers', 'label' => 'Semua'],
            'pohon' => ['icon' => 'tree-pine', 'label' => 'Pohon'],
            'sungai' => ['icon' => 'waves', 'label' => 'Sungai'],
            'taman' => ['icon' => 'flower-2', 'label' => 'Taman'],
            'komposting' => ['icon' => 'recycle', 'label' => 'Komposting']
        ];
        foreach($tabs as $key => $t): 
            $is_active = ($tab === $key);
            $class = $is_active ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300';
        ?>
            <a href="index.php?page=objek&tab=<?= $key ?><?= !empty($_GET['f_cari']) ? '&f_cari='.urlencode($_GET['f_cari']) : '' ?>" 
               class="flex items-center gap-2 px-6 py-3 font-bold text-sm border-b-2 whitespace-nowrap transition <?= $class ?>">
                <i data-lucide="<?= $t['icon'] ?>" class="w-4 h-4"></i> <?= $t['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Form Filter Pencarian -->
    <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-8 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="hidden" name="page" value="objek">
        <input type="hidden" name="tab" value="<?= $tab ?>">
        <div class="relative md:col-span-2">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="f_cari" value="<?= e($_GET['f_cari'] ?? '') ?>" placeholder="Cari nama, kode, atau lokasi..." class="w-full pl-9 pr-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition">Cari</button>
            <a href="index.php?page=objek&tab=<?= $tab ?>" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
        </div>
    </form>

    <!-- Grid Objek -->
    <?php if(empty($data_objek)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200">
            <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-bold text-slate-700">Belum Ada Objek di Kategori Ini</h3>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($data_objek as $o): 
                $info = ['pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-500','ic'=>'recycle']][$o['jenis_objek']] ?? ['bg'=>'bg-slate-500','ic'=>'package'];
                $can_edit = is_admin() || ($o['user_id'] == $my_id);
            ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col">
                <div class="relative h-48 bg-slate-100 overflow-hidden">
                    <?php if(!empty($o['foto_pertama'])): ?>
                        <img src="uploads/<?= e($o['foto_pertama']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Foto Objek">
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
                    
                    <!-- TOMBOL AKSI -->
                    <div class="mt-auto pt-4 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="col-span-2 flex items-center justify-center gap-1 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200 transition text-sm">
                            <i data-lucide="eye" class="w-4 h-4"></i> Lihat Profil
                        </a>
                        
                        <a href="index.php?page=lapor&objek_id=<?= $o['id'] ?>" class="flex items-center justify-center gap-1 py-2 bg-emerald-50 text-emerald-700 font-bold rounded-lg hover:bg-emerald-100 transition text-sm">
                            <i data-lucide="file-plus" class="w-4 h-4"></i> Lapor
                        </a>
                        
                        <?php if($can_edit): ?>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="index.php?page=objek_edit&id=<?= $o['id'] ?>" class="flex items-center justify-center gap-1 py-2 bg-blue-50 text-blue-700 font-bold rounded-lg hover:bg-blue-100 transition text-sm" title="Edit Objek">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                                </a>
                                <form method="POST" action="index.php" onsubmit="return confirm('Yakin ingin menghapus objek ini?');" class="flex">
                                    <input type="hidden" name="aksi_hapus_objek" value="1">
                                    <input type="hidden" name="objek_id" value="<?= $o['id'] ?>">
                                    <button type="submit" class="w-full flex items-center justify-center gap-1 py-2 bg-rose-50 text-rose-600 font-bold rounded-lg hover:bg-rose-100 transition text-sm" title="Hapus Objek">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-center py-2 bg-slate-50 text-slate-400 text-xs font-bold rounded-lg border border-slate-100">
                                Hanya Lihat
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
