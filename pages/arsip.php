<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
// Query dasar, nanti akan disempurnakan
$q_arsip = mysqli_query($conn, "SELECT * FROM laporan_aksi WHERE user_id = {$_SESSION['user_id']} AND deleted_at IS NULL ORDER BY tanggal_buat DESC");
?>
<main class="max-w-5xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="folder-open" class="w-8 h-8 text-emerald-600"></i> Laporan Saya
        </h1>
        <p class="text-slate-500 mt-2">Rekapitulasi aksi lingkungan yang Anda laporkan.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php if(mysqli_num_rows($q_arsip) > 0): while($row = mysqli_fetch_assoc($q_arsip)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex gap-4 hover:shadow-md transition">
                <div class="w-16 h-16 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="file-text" class="w-8 h-8 text-emerald-600"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-emerald-600 mb-1"><?= date('d M Y', strtotime($row['tanggal_buat'])) ?></div>
                    <h3 class="font-bold text-slate-800 text-lg mb-1"><?= e($row['nama_objek']) ?></h3>
                    <p class="text-sm text-slate-500 line-clamp-2"><?= e($row['catatan']) ?></p>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="col-span-2 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                <i data-lucide="inbox" class="w-12 h-12 text-slate-300 mx-auto mb-2"></i>
                <p class="text-slate-500">Belum ada laporan.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
