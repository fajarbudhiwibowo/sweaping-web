<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
$uid = (int)$_SESSION['user_id'];

// 1. Ambil Data Area Pantau dari Database
// (Kita ambil semua area yang belum dihapus)
$q_area = mysqli_query($conn, "SELECT * FROM area_pantau WHERE deleted_at IS NULL ORDER BY id DESC");

// 2. Masukkan ke dalam array $areas
$areas = [];
while ($a = mysqli_fetch_assoc($q_area)) {
    $areas[] = $a;
}
?>

<main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
    
    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="map" class="w-7 h-7 text-emerald-600"></i> Area Pantau
            </h1>
            <p class="text-sm text-slate-500 mt-1">Daftar kawasan konservasi yang dipantau oleh komunitas.</p>
        </div>
        <a href="index.php?page=area_tambah" class="bg-emerald-600 text-white font-bold py-2.5 px-5 rounded-xl hover:bg-emerald-700 transition shadow-md flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Gambar Area Baru
        </a>
    </div>

    <!-- Notifikasi -->
    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200">
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <!-- Grid Kartu Area -->
    <?php if(empty($areas)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200">
            <i data-lucide="map-pin" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-bold text-slate-700">Belum Ada Area Pantau</h3>
            <p class="text-slate-500 mt-1">Silakan klik "Gambar Area Baru" untuk memulai.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($areas as $a): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col h-full group hover:shadow-md transition-shadow">
                
                <!-- Bagian Atas (Ikon & Judul) -->
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded uppercase">
                            <?= e($a['jenis'] ?? 'Area') ?>
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-800 mb-1"><?= e($a['nama_area']) ?></h3>
                    <p class="text-xs font-mono text-slate-400 mb-2"><?= e($a['kode']) ?></p>
                    <p class="text-sm text-slate-500 line-clamp-2 mb-4"><?= e($a['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                    
                    <!-- Informasi Luas / Panjang -->
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <?= ($a['mode_gambar'] ?? 'poligon') === 'tracking' ? 'Panjang Jalur' : 'Luas Area' ?>
                            </span>
                            <div class="text-lg font-extrabold text-emerald-600 mt-1">
                                <?php if(($a['mode_gambar'] ?? 'poligon') === 'tracking'): ?>
                                    <?= number_format($a['panjang_m'] ?? 0, 0, ',', '.') ?> meter
                                <?php else: ?>
                                    <?= number_format($a['luas_m2'] ?? 0, 0, ',', '.') ?> m²
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Bawah (Tombol Aksi) -->
                <div class="p-5 pt-0 mt-auto">
                    <a href="index.php?page=area_detail&id=<?= $a['id'] ?>" class="w-full flex items-center justify-center gap-2 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition text-sm mb-2">
                        <i data-lucide="eye" class="w-4 h-4"></i> Lihat Peta & Detail Area
                    </a>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="index.php?page=area_edit&id=<?= $a['id'] ?>" class="flex items-center justify-center gap-1 py-2 bg-blue-50 text-blue-700 font-bold rounded-lg hover:bg-blue-100 transition text-sm">
                            <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                        </a>
                        <form method="POST" action="index.php" onsubmit="return confirm('Yakin ingin menghapus area ini?');" class="w-full">
                            <input type="hidden" name="aksi_hapus_area" value="1">
                            <input type="hidden" name="area_id" value="<?= $a['id'] ?>">
                            <button type="submit" class="w-full flex items-center justify-center gap-1 py-2 bg-rose-50 text-rose-600 font-bold rounded-lg hover:bg-rose-100 transition text-sm">
                                <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>