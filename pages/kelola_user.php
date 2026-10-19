<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$tab = $_GET['tab'] ?? 'semua';
$cari = trim($_GET['cari'] ?? '');

// 1. Ambil Data User (Berdasarkan Filter)
$where = "deleted_at IS NULL"; $params = []; $types = '';
if ($cari !== '') {
    $where .= " AND (nama_lengkap LIKE ? OR email LIKE ?)";
    $cari_like = "%$cari%"; $params[] = $cari_like; $params[] = $cari_like; $types .= 'ss';
}
if ($tab === 'individu') { $where .= " AND tipe_akun = 'individu'"; }
if ($tab === 'institusi') { $where .= " AND tipe_akun = 'institusi'"; }

$sql = "SELECT * FROM users WHERE $where ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$q_users = mysqli_stmt_get_result($stmt);

// 2. Ambil Data Antrian Persetujuan Gabung Organisasi
$q_approvals = mysqli_query($conn, "
    SELECT r.id as req_id, r.created_at, u.nama_lengkap as nama_user, u.email, u.avatar, o.nama as nama_org 
    FROM organisasi_permintaan r 
    JOIN users u ON r.user_id = u.id 
    JOIN organisasi o ON r.organisasi_id = o.id 
    WHERE r.status = 'pending' 
    ORDER BY r.id DESC
");
$total_approvals = mysqli_num_rows($q_approvals);
?>

<!-- Wrapper Konten (Karena sidebar dan header sudah diurus oleh header.php) -->
<div class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <!-- Header Halaman & Pencarian -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="users" class="w-7 h-7 md:w-8 md:h-8 text-indigo-600"></i> Manajemen Pengguna
            </h1>
            <p class="text-sm md:text-base text-slate-500 mt-1">Kelola akses individu, lembaga, dan persetujuan anggota.</p>
        </div>
        
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="hidden" name="page" value="kelola_user">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <div class="relative w-full md:w-72">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama atau email..." class="w-full pl-9 pr-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm font-semibold">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition shadow-md whitespace-nowrap">Cari</button>
        </form>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200 flex items-center gap-2">
            <i data-lucide="<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5"></i>
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <!-- Navigasi Tab (Mobile Scrollable) -->
    <div class="flex overflow-x-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-1 mb-6 hide-scrollbar gap-1">
        <a href="index.php?page=kelola_user&tab=semua" class="flex-1 py-2.5 px-4 text-xs md:text-sm font-bold rounded-xl text-center transition whitespace-nowrap <?= $tab === 'semua' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50' ?>">Semua User</a>
        <a href="index.php?page=kelola_user&tab=individu" class="flex-1 py-2.5 px-4 text-xs md:text-sm font-bold rounded-xl text-center transition whitespace-nowrap <?= $tab === 'individu' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50' ?>">Individu</a>
        <a href="index.php?page=kelola_user&tab=institusi" class="flex-1 py-2.5 px-4 text-xs md:text-sm font-bold rounded-xl text-center transition whitespace-nowrap <?= $tab === 'institusi' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50' ?>">Institusi</a>
        <a href="index.php?page=kelola_user&tab=approval" class="flex-1 py-2.5 px-4 text-xs md:text-sm font-bold rounded-xl text-center transition relative whitespace-nowrap <?= $tab === 'approval' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50' ?>">
            Persetujuan
            <?php if($total_approvals > 0): ?>
                <span class="absolute top-1.5 right-1.5 md:top-2 md:right-2 flex h-4 w-4 md:h-5 md:w-5 items-center justify-center rounded-full bg-white text-rose-600 text-[9px] md:text-[10px] font-black"><?= $total_approvals ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- KONTEN TAB: APPROVAL -->
    <?php if($tab === 'approval'): ?>
        <div class="space-y-4">
            <?php if($total_approvals > 0): while($ap = mysqli_fetch_assoc($q_approvals)): ?>
                <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:shadow-md transition">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-12 h-12 md:w-14 md:h-14 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-500 overflow-hidden flex-shrink-0 border-2 border-white shadow-sm">
                            <?php if($ap['avatar']): ?><img src="uploads/avatar/<?= e($ap['avatar']) ?>" class="w-full h-full object-cover"><?php else: ?><?= strtoupper(substr($ap['nama_user'], 0, 1)) ?><?php endif; ?>
                        </div>
                        <div>
                            <div class="font-bold text-slate-800 text-base md:text-lg"><?= e($ap['nama_user']) ?></div>
                            <div class="text-xs md:text-sm text-slate-500">Ingin bergabung dengan <b class="text-indigo-600"><?= e($ap['nama_org']) ?></b></div>
                            <div class="text-[10px] md:text-xs text-slate-400 mt-1 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> <?= date('d M Y H:i', strtotime($ap['created_at'])) ?></div>
                        </div>
                    </div>
                    <div class="flex gap-2 w-full md:w-auto mt-2 md:mt-0">
                        <button onclick="respondOrg(<?= $ap['req_id'] ?>, 'accepted')" class="flex-1 md:flex-none px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-md flex items-center justify-center gap-1 text-sm"><i data-lucide="check" class="w-4 h-4"></i> Terima</button>
                        <button onclick="respondOrg(<?= $ap['req_id'] ?>, 'rejected')" class="flex-1 md:flex-none px-6 py-2.5 bg-rose-100 text-rose-600 font-bold rounded-xl hover:bg-rose-200 transition flex items-center justify-center gap-1 text-sm"><i data-lucide="x" class="w-4 h-4"></i> Tolak</button>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="text-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">Tidak Ada Antrian</h3>
                    <p class="text-slate-500 mt-1 text-sm">Belum ada permintaan bergabung yang menunggu persetujuan.</p>
                </div>
            <?php endif; ?>
        </div>

    <!-- KONTEN TAB: SEMUA USER / INDIVIDU / INSTITUSI -->
    <?php else: ?>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] md:text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                            <th class="px-4 md:px-6 py-4">User</th>
                            <th class="px-4 md:px-6 py-4">Tipe Akun</th>
                            <th class="px-4 md:px-6 py-4">Role</th>
                            <th class="px-4 md:px-6 py-4">Status</th>
                            <th class="px-4 md:px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs md:text-sm">
                        <?php if(mysqli_num_rows($q_users) > 0): while($u = mysqli_fetch_assoc($q_users)): $is_self = ($u['id'] == $_SESSION['user_id']); ?>
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold flex-shrink-0 overflow-hidden border border-slate-200">
                                        <?php if(!empty($u['avatar']) && file_exists('uploads/avatar/' . $u['avatar'])): ?>
                                            <img src="uploads/avatar/<?= e($u['avatar']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 flex items-center gap-1 whitespace-nowrap">
                                            <?= e($u['nama_lengkap']) ?> 
                                            <?php if($is_self): ?><span class="text-[9px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full ml-1 border border-blue-200">Anda</span><?php endif; ?>
                                        </div>
                                        <div class="text-[10px] md:text-xs text-slate-500 truncate max-w-[150px] md:max-w-xs"><?= e($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                <?php if(isset($u['tipe_akun']) && $u['tipe_akun'] === 'institusi'): ?>
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded-md text-[9px] md:text-[10px] font-bold uppercase border border-purple-100 flex items-center gap-1 w-fit"><i data-lucide="building-2" class="w-3 h-3"></i> Institusi</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-md text-[9px] md:text-[10px] font-bold uppercase border border-emerald-100 flex items-center gap-1 w-fit"><i data-lucide="user" class="w-3 h-3"></i> Individu</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-md text-[9px] md:text-[10px] font-bold uppercase border <?= $u['role']=='super_admin' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : ($u['role']=='admin' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-slate-50 text-slate-600 border-slate-200') ?>">
                                    <?= str_replace('_', ' ', e($u['role'])) ?>
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                <?php if($u['aktif']): ?>
                                    <span class="text-emerald-600 font-bold text-[10px] md:text-xs flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Aktif</span>
                                <?php else: ?>
                                    <span class="text-rose-600 font-bold text-[10px] md:text-xs flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Banned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4">
                                <div class="flex items-center justify-center gap-1.5 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="index.php?page=kelola_user_edit&id=<?= $u['id'] ?>" class="p-1.5 md:p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition" title="Edit Profil"><i data-lucide="edit-3" class="w-3.5 h-3.5 md:w-4 md:h-4"></i></a>
                                    
                                    <?php if(!$is_self && (is_super_admin() || $u['role'] !== 'super_admin')): ?>
                                        <a href="index.php?page=kelola_user_reset&id=<?= $u['id'] ?>" class="p-1.5 md:p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition" title="Reset Password"><i data-lucide="key" class="w-3.5 h-3.5 md:w-4 md:h-4"></i></a>
                                        
                                        <form method="POST" action="index.php?page=aksi_kelola_user" class="inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="aksi" value="toggle_aktif">
                                            <button type="submit" class="p-1.5 md:p-2 <?= $u['aktif'] ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' ?> rounded-lg transition" title="<?= $u['aktif'] ? 'Ban User' : 'Aktifkan User' ?>"><i data-lucide="<?= $u['aktif'] ? 'user-x' : 'user-check' ?>" class="w-3.5 h-3.5 md:w-4 md:h-4"></i></button>
                                        </form>
                                        
                                        <form method="POST" action="index.php?page=aksi_kelola_user" class="inline" onsubmit="return confirm('Hapus user ini secara permanen?');">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="aksi" value="hapus">
                                            <button type="submit" class="p-1.5 md:p-2 bg-slate-800 text-white rounded-lg hover:bg-rose-700 transition" title="Hapus User"><i data-lucide="trash-2" class="w-3.5 h-3.5 md:w-4 md:h-4"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-16">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i data-lucide="users" class="w-10 h-10 text-slate-300"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-700">Tidak Ada Data</h3>
                                    <p class="text-slate-500 text-sm mt-1">Tidak ada user yang sesuai dengan filter atau pencarian Anda.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function respondOrg(reqId, action) {
    if(!confirm('Yakin ingin memproses permintaan ini?')) return;
    
    // Optional: Ubah tombol jadi state loading (bisa ditambahkan nanti)
    
    fetch('index.php?page=aksi_respond_org', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `req_id=${reqId}&action=${action}`
    }).then(res => res.json()).then(data => {
        if(data.status === 'ok') {
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan sistem.');
        }
    }).catch(err => {
        alert('Gagal terhubung ke server.');
    });
}
</script>