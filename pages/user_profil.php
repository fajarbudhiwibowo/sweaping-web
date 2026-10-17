<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$profil_id = (int)($_GET['id'] ?? $_SESSION['user_id']);
$my_id = (int)$_SESSION['user_id'];

// Ambil data user
$stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, organisasi, role, avatar, tanggal_daftar FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) { echo "<div class='pt-32 text-center'>User tidak ditemukan.</div>"; exit; }

$is_self = ($profil_id === $my_id);

// Statistik
$stmt_stat = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM objek WHERE user_id = ? AND deleted_at IS NULL AND visibility = 'publik'");
mysqli_stmt_bind_param($stmt_stat, 'i', $profil_id);
mysqli_stmt_execute($stmt_stat);
$total_objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stat))['total'];
mysqli_stmt_close($stmt_stat);

$stmt_fol = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM follows WHERE following_id = ? AND status = 'accepted'");
mysqli_stmt_bind_param($stmt_fol, 'i', $profil_id);
mysqli_stmt_execute($stmt_fol);
$total_pengikut = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_fol))['total'];
mysqli_stmt_close($stmt_fol);

// Status follow saya ke dia
$status_follow = 'none';
if (!$is_self) {
    $stmt_cek = mysqli_prepare($conn, "SELECT status FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_cek, 'ii', $my_id, $profil_id);
    mysqli_stmt_execute($stmt_cek);
    $r_cek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
    if ($r_cek) $status_follow = $r_cek['status'];
    mysqli_stmt_close($stmt_cek);
}

// Ambil objek publik user ini
$stmt_o = mysqli_prepare($conn, "SELECT * FROM objek WHERE user_id = ? AND deleted_at IS NULL AND visibility = 'publik' ORDER BY id DESC LIMIT 6");
mysqli_stmt_bind_param($stmt_o, 'i', $profil_id);
mysqli_stmt_execute($stmt_o);
$q_objek = mysqli_stmt_get_result($stmt_o);
?>

<main class="max-w-5xl mx-auto px-4 pt-24 pb-16">
    <!-- Header Profil -->
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 mb-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-r from-emerald-500 to-emerald-700"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-end gap-6 mt-12 md:mt-16">
            <?= tampil_avatar($user, 'w-32 h-32 border-4 border-white shadow-lg', 'text-5xl') ?>
            
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-3xl font-extrabold text-slate-900 flex items-center justify-center md:justify-start gap-2">
                    <?= e($user['nama_lengkap']) ?> <?= tampil_badge_verified($user['role']) ?>
                </h1>
                <div class="text-slate-500 font-semibold mt-1">
                    <?= e($user['organisasi'] ?: 'Individu') ?> · Bergabung <?= date('M Y', strtotime($user['tanggal_daftar'])) ?>
                </div>
            </div>

            <div class="flex-shrink-0 mt-4 md:mt-0">
                <?php if($is_self): ?>
                    <a href="index.php?page=kelola_user" class="px-6 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition text-sm">Edit Profil</a>
                <?php else: ?>
                    <form method="POST" action="index.php?page=aksi_follow">
                        <input type="hidden" name="target_id" value="<?= $profil_id ?>">
                        <?php if($status_follow === 'accepted'): ?>
                            <button type="submit" name="aksi" value="unfollow" class="px-6 py-2.5 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-rose-100 hover:text-rose-700 transition text-sm">Mengikuti</button>
                        <?php elseif($status_follow === 'pending'): ?>
                            <button type="button" disabled class="px-6 py-2.5 bg-amber-100 text-amber-700 font-bold rounded-xl text-sm cursor-not-allowed">Menunggu Konfirmasi</button>
                        <?php else: ?>
                            <button type="submit" name="aksi" value="follow" class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 transition text-sm flex items-center gap-2">
                                <i data-lucide="user-plus" class="w-4 h-4"></i> Ikuti
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-slate-100">
            <div class="text-center">
                <div class="text-2xl font-black text-slate-800"><?= number_format($total_objek) ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase">Objek Publik</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-black text-slate-800"><?= number_format($total_pengikut) ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase">Pengikut</div>
            </div>
        </div>
    </div>

    <!-- Daftar Objek Publik -->
    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="package" class="w-5 h-5 text-emerald-600"></i> Objek Publik</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <?php if(mysqli_num_rows($q_objek) > 0): while($o = mysqli_fetch_assoc($q_objek)): 
            $info = ['pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-500','ic'=>'recycle']][$o['jenis_objek']] ?? ['bg'=>'bg-slate-500','ic'=>'package'];
        ?>
        <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="bg-white rounded-2xl border border-slate-100 p-4 flex gap-3 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-xl <?= $info['bg'] ?> bg-opacity-10 flex items-center justify-center flex-shrink-0">
                <i data-lucide="<?= $info['ic'] ?>" class="w-6 h-6 <?= str_replace('bg-', 'text-', $info['bg']) ?>"></i>
            </div>
            <div class="min-w-0">
                <div class="font-bold text-slate-800 truncate group-hover:text-emerald-600 transition"><?= e($o['nama_objek']) ?></div>
                <div class="text-[10px] text-slate-500 uppercase font-bold"><?= e($o['jenis_objek']) ?></div>
            </div>
        </a>
        <?php endwhile; else: echo '<div class="col-span-3 text-center py-8 text-slate-500">Belum ada objek publik.</div>'; endif; ?>
    </div>
</main>
