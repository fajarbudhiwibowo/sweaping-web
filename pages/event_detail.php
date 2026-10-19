<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
$id = (int)$_GET['id'];
$uid = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT e.*, u.nama_lengkap FROM events e JOIN users u ON e.user_id = u.id WHERE e.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$ev = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$ev) { echo "<div class='pt-32 text-center text-slate-500'>Event tidak ditemukan.</div>"; exit; }
?>

<main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
    <a href="javascript:history.back()" class="text-sm font-bold text-emerald-700 hover:underline mb-4 inline-block">← Kembali</a>
    
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
        <?php if($ev['gambar']): ?>
            <img src="uploads/events/<?= e($ev['gambar']) ?>" class="w-full h-64 md:h-96 object-cover">
        <?php endif; ?>
        
        <div class="p-6 md:p-8">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full uppercase"><?= e($ev['status']) ?></span>
                    <h1 class="text-3xl font-extrabold text-slate-900 mt-3"><?= e($ev['judul']) ?></h1>
                </div>
                <span class="text-xs font-bold text-slate-400 flex items-center gap-1">
                    <i data-lucide="<?= $ev['visibility'] == 'publik' ? 'globe' : ($ev['visibility'] == 'teman' ? 'users' : 'lock') ?>" class="w-4 h-4"></i> 
                    <?= ucfirst($ev['visibility']) ?>
                </span>
            </div>

            <div class="flex flex-wrap gap-4 text-sm font-bold text-slate-600 mb-6 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-emerald-500"></i> <?= date('d F Y', strtotime($ev['tanggal_mulai'])) ?></div>
                <div class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-emerald-500"></i> <?= e($ev['lokasi']) ?></div>
                <div class="flex items-center gap-2"><i data-lucide="user" class="w-4 h-4 text-emerald-500"></i> Oleh: <?= e($ev['nama_lengkap']) ?></div>
            </div>

            <div class="prose max-w-none text-slate-700 whitespace-pre-line mb-8">
                <?= e($ev['deskripsi']) ?>
            </div>

            <?php if($ev['link']): ?>
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center">
                    <p class="text-sm font-bold text-blue-800 mb-2">Tautan Terkait Event</p>
                    <a href="<?= e($ev['link']) ?>" target="_blank" class="inline-block bg-blue-600 text-white font-bold px-6 py-2 rounded-lg hover:bg-blue-700 transition">Buka Tautan</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>