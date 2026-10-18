<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$uid = (int)$_SESSION['user_id'];
$is_admin = is_admin();

// Query Laporan
$sql = "SELECT l.*, u.nama_lengkap FROM laporan_aksi l LEFT JOIN users u ON l.user_id = u.id WHERE l.deleted_at IS NULL";
if (!$is_admin) {
    $sql .= " AND (l.user_id = ? OR l.visibility = 'publik')";
}
$sql .= " ORDER BY l.tanggal_buat DESC LIMIT 100";

$stmt = mysqli_prepare($conn, $sql);
if (!$is_admin) {
    mysqli_stmt_bind_param($stmt, 'i', $uid);
}
mysqli_stmt_execute($stmt);
$res_lapor = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-5xl mx-auto px-4 pt-24 pb-16">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="folder" class="w-8 h-8 text-emerald-600"></i> Arsip Laporan
            </h1>
            <p class="text-slate-500 mt-1">Rekapitulasi aksi monitoring dan perhitungan karbon.</p>
        </div>
        <a href="index.php?page=lapor" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 transition flex items-center gap-2">
            <i data-lucide="plus" class="w-5 h-5"></i> Buat Laporan Baru
        </a>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="space-y-4">
        <?php if(mysqli_num_rows($res_lapor) > 0): while($r = mysqli_fetch_assoc($res_lapor)): 
            $can_edit = $is_admin || ($r['user_id'] == $uid);
            $info = ['pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-500','ic'=>'recycle']][$r['jenis_objek']] ?? ['bg'=>'bg-slate-500','ic'=>'package'];
        ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col md:flex-row gap-6 hover:shadow-md transition">
                <?php if($r['foto']): ?>
                    <div class="w-full md:w-48 h-32 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100">
                        <img src="uploads/<?= e($r['foto']) ?>" class="w-full h-full object-cover" alt="Foto Laporan">
                    </div>
                <?php else: ?>
                    <div class="w-full md:w-48 h-32 flex-shrink-0 rounded-xl bg-slate-50 flex items-center justify-center border border-slate-200">
                        <i data-lucide="<?= $info['ic'] ?>" class="w-10 h-10 text-slate-300"></i>
                    </div>
                <?php endif; ?>

                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="<?= $info['bg'] ?> text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= e($r['jenis_objek']) ?></span>
                            <span class="text-xs text-slate-400"><?= date('d M Y', strtotime($r['tanggal_buat'])) ?></span>
                            <?php if($r['visibility'] == 'privat'): ?><i data-lucide="lock" class="w-3 h-3 text-slate-400"></i><?php endif; ?>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg"><?= e($r['nama_objek']) ?></h3>
                        <p class="text-sm text-slate-500 line-clamp-2 mt-1"><?= e($r['catatan']) ?></p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="text-xs text-slate-500 font-semibold flex items-center gap-1"><i data-lucide="user" class="w-3 h-3"></i> <?= e($r['nama_kontributor']) ?></div>
                            <?php if($r['serapan_karbon_ilmiah'] > 0): ?>
                                <div class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-md flex items-center gap-1">
                                    <i data-lucide="wind" class="w-3 h-3"></i> <?= number_format($r['serapan_karbon_ilmiah'], 2) ?> kg CO₂
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if($can_edit): ?>
                        <div class="flex gap-2">
                            <a href="index.php?page=edit&id=<?= $r['id'] ?>" class="px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg hover:bg-blue-100 transition">Edit</a>
                            <form method="POST" action="index.php" onsubmit="return confirm('Yakin ingin menghapus laporan ini?');">
                                <input type="hidden" name="aksi_hapus_laporan" value="1">
                                <input type="hidden" name="laporan_id" value="<?= $r['id'] ?>">
                                <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-600 text-xs font-bold rounded-lg hover:bg-rose-100 transition">Hapus</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-500 font-bold">Belum ada laporan.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
