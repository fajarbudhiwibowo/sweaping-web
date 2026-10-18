<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
$my_id = (int)$_SESSION['user_id'];

// Ambil daftar request yang pending
$stmt = mysqli_prepare($conn, "SELECT f.id as follow_id, f.created_at, u.id as user_id, u.nama_lengkap, u.organisasi, u.avatar, u.role FROM follows f JOIN users u ON f.follower_id = u.id WHERE f.following_id = ? AND f.status = 'pending' ORDER BY f.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $my_id);
mysqli_stmt_execute($stmt);
$q_requests = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-3xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-6">
        <a href="index.php?page=beranda" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Beranda
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="user-plus" class="w-8 h-8 text-amber-500"></i> Permintaan Follow
        </h1>
        <p class="text-slate-500 mt-2">Setujui permintaan untuk saling berteman dan melihat aktivitas privat.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="divide-y divide-slate-100">
            <?php if(mysqli_num_rows($q_requests) > 0): while($r = mysqli_fetch_assoc($q_requests)): ?>
                <div class="p-5 flex flex-col sm:flex-row items-center justify-between gap-4" id="req-<?= $r['follow_id'] ?>">
                    <div class="flex items-center gap-4 w-full">
                        <?= tampil_avatar($r, 'w-14 h-14', 'text-xl') ?>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-800 text-lg truncate flex items-center gap-1">
                                <a href="index.php?page=user_profil&id=<?= $r['user_id'] ?>" class="hover:text-emerald-600 transition"><?= e($r['nama_lengkap']) ?></a>
                                <?= tampil_badge_verified($r['role']) ?>
                            </div>
                            <div class="text-xs text-slate-500 truncate"><?= e($r['organisasi'] ?: 'Individu') ?></div>
                            <div class="text-[10px] text-slate-400 mt-1"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full sm:w-auto flex-shrink-0">
                        <button type="button" onclick="respondFollow(<?= $r['follow_id'] ?>, 'rejected')" class="flex-1 sm:flex-none px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-rose-100 hover:text-rose-700 transition text-sm">Tolak</button>
                        <button type="button" onclick="respondFollow(<?= $r['follow_id'] ?>, 'accepted')" class="flex-1 sm:flex-none px-4 py-2 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition text-sm">Terima</button>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">Tidak Ada Permintaan</h3>
                    <p class="text-slate-500 text-sm mt-1">Belum ada user yang ingin mengikuti Anda saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function respondFollow(followId, action) {
    const formData = new FormData();
    formData.append('follow_id', followId);
    formData.append('action', action);

    fetch('index.php?page=aksi_respond_follow', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'ok') {
            const el = document.getElementById('req-' + followId);
            el.style.transition = 'all 0.3s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(() => el.remove(), 300);
        } else {
            alert('Gagal memproses.');
        }
    })
    .catch(err => alert('Error: ' + err.message));
}
</script>
