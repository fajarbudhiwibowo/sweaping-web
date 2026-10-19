<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$cari = trim($_GET['cari'] ?? '');
$where = "deleted_at IS NULL"; $params = []; $types = '';
if ($cari !== '') {
    $where .= " AND (nama_lengkap LIKE ? OR email LIKE ?)";
    $cari_like = "%$cari%"; $params[] = $cari_like; $params[] = $cari_like; $types .= 'ss';
}
$sql = "SELECT * FROM users WHERE $where ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$q_users = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-7xl mx-auto px-4 pt-24 pb-16">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2"><i data-lucide="users" class="w-7 h-7 text-indigo-600"></i> Manajemen User</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola akses, reset password, dan status pengguna.</p>
        </div>
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="hidden" name="page" value="kelola_user">
            <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama/email..." class="w-full md:w-64 px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition">Cari</button>
        </form>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead><tr class="bg-slate-50 text-xs uppercase font-bold text-slate-500"><th class="px-4 py-4">User</th><th class="px-4 py-4">Role</th><th class="px-4 py-4">Status</th><th class="px-4 py-4 text-center">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php while($u = mysqli_fetch_assoc($q_users)): $is_self = ($u['id'] == $_SESSION['user_id']); ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold flex-shrink-0"><?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?></div>
                                <div>
                                    <div class="font-bold text-slate-800 flex flex-wrap items-center gap-1">
    <?= e($u['nama_lengkap']) ?> 
    <?= tampil_badge_verified($u['role']) ?> 
    
    <!-- BADGE TIPE AKUN -->
    <?php if(isset($u['tipe_akun']) && $u['tipe_akun'] === 'institusi'): ?>
        <span class="text-[10px] bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full border border-purple-200">Institusi</span>
    <?php else: ?>
        <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200">Individu</span>
    <?php endif; ?>

    <?php if($is_self): ?>
        <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-1">Anda</span>
    <?php endif; ?>
</div>
                            </div>
                            </div>
                        </td>
                        <td class="px-4 py-3"><span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase <?= $u['role']=='super_admin' ? 'bg-purple-100 text-purple-700' : ($u['role']=='admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600') ?>"><?= e($u['role']) ?></span></td>
                        <td class="px-4 py-3"><?php if($u['aktif']): ?><span class="text-emerald-600 font-bold text-xs flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif</span><?php else: ?><span class="text-rose-600 font-bold text-xs flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Banned</span><?php endif; ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="index.php?page=kelola_user_edit&id=<?= $u['id'] ?>" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" title="Edit Profil"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                <?php if(!$is_self && (is_super_admin() || $u['role'] !== 'super_admin')): ?>
                                    <a href="index.php?page=kelola_user_reset&id=<?= $u['id'] ?>" class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100" title="Reset Password"><i data-lucide="key" class="w-4 h-4"></i></a>
                                    <form method="POST" action="index.php?page=aksi_kelola_user" class="inline">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="aksi" value="toggle_aktif">
                                        <button type="submit" class="p-2 <?= $u['aktif'] ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' ?> rounded-lg" title="<?= $u['aktif'] ? 'Ban User' : 'Aktifkan User' ?>"><i data-lucide="<?= $u['aktif'] ? 'user-x' : 'user-check' ?>" class="w-4 h-4"></i></button>
                                    </form>
                                    <form method="POST" action="index.php?page=aksi_kelola_user" class="inline" onsubmit="return confirm('Hapus user ini?');">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="aksi" value="hapus">
                                        <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-rose-700" title="Hapus User"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
