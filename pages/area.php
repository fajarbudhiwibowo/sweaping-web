<?php
$my_id = (int)$_SESSION['user_id'];

$q_area = mysqli_query($conn, "SELECT a.*, o.nama as nama_org, o.pic_user_id FROM area_pantau a LEFT JOIN organisasi o ON a.organisasi_id = o.id WHERE a.deleted_at IS NULL ORDER BY a.id DESC");
?>

<main class="max-w-6xl mx-auto px-4 pt-24 pb-16">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="map" class="w-8 h-8 text-emerald-600"></i> Area Pantau
            </h1>
            <p class="text-slate-500 mt-2">Daftar kawasan konservasi yang dipantau oleh komunitas.</p>
        </div>
        <?php if(is_logged_in()): ?>
            <a href="index.php?page=area_tambah" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 transition flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i> Gambar Area Baru
            </a>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(mysqli_num_rows($q_area) > 0): while($a = mysqli_fetch_assoc($q_area)): 
            $can_edit = is_admin() || ($a['pic_user_id'] == $my_id) || ($a['created_by'] == $my_id);
        ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center"><i data-lucide="map-pin" class="w-6 h-6"></i></div>
                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase"><?= e($a['jenis']) ?></span>
                </div>
                <h3 class="font-bold text-slate-800 text-xl mb-1"><?= e($a['nama_area']) ?></h3>
                <div class="text-xs font-mono text-slate-400 mb-3"><?= e($a['kode']) ?></div>
                <p class="text-sm text-slate-500 line-clamp-2 mb-4 flex-1"><?= e($a['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                
                <div class="border-t border-slate-100 pt-4 flex justify-between items-center mb-4">
                    <div class="text-sm font-bold text-emerald-600"><?= number_format($a['luas_m2'], 0, ',', '.') ?> m²</div>
                    <div class="text-xs text-slate-500">oleh <b><?= e($a['nama_org'] ?: 'Individu') ?></b></div>
                </div>

                <!-- TOMBOL LIHAT (Semua User) -->
                <a href="index.php?page=area_detail&id=<?= $a['id'] ?>" class="w-full text-center py-2.5 bg-emerald-600 text-white font-bold text-sm rounded-xl hover:bg-emerald-700 transition mb-2 flex justify-center items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4"></i> Lihat Peta & Detail Area
                </a>

                <!-- TOMBOL EDIT & HAPUS (Khusus Admin/PIC/Pembuat) -->
                <?php if($can_edit): ?>
                <div class="flex gap-2">
                    <a href="index.php?page=area_edit&id=<?= $a['id'] ?>" class="flex-1 text-center py-2 bg-blue-50 text-blue-600 font-bold text-xs rounded-lg hover:bg-blue-100 transition">Edit</a>
                    <form method="POST" action="index.php" onsubmit="return confirm('Yakin ingin menghapus area ini?');" class="flex-1">
                        <input type="hidden" name="aksi_hapus_area" value="1">
                        <input type="hidden" name="area_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="w-full py-2 bg-rose-50 text-rose-600 font-bold text-xs rounded-lg hover:bg-rose-100 transition">Hapus</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; else: ?>
            <div class="col-span-3 text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <i data-lucide="map" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-500 font-bold">Belum ada area pantau.</p>
                <a href="index.php?page=area_tambah" class="text-emerald-600 text-sm font-bold hover:underline mt-2 block">Buat area pertama Anda &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
</main>
