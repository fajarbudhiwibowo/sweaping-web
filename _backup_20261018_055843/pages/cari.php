<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$q = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'user';
if (!in_array($tab, ['user', 'objek', 'organisasi'])) $tab = 'user';

$my_id = (int)$_SESSION['user_id'];
$hasil_user = []; $hasil_objek = []; $hasil_org = [];

// Ambil status follow saya
$my_follows = [];
$stmt_f = mysqli_prepare($conn, "SELECT following_id, status FROM follows WHERE follower_id = ?");
mysqli_stmt_bind_param($stmt_f, 'i', $my_id);
mysqli_stmt_execute($stmt_f);
$res_f = mysqli_stmt_get_result($stmt_f);
while ($row_f = mysqli_fetch_assoc($res_f)) $my_follows[$row_f['following_id']] = $row_f['status'];
mysqli_stmt_close($stmt_f);

// Ambil status keanggotaan organisasi saya
$my_orgs = [];
$stmt_org = mysqli_prepare($conn, "SELECT organisasi_id, role FROM organisasi_anggota WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_org, 'i', $my_id);
mysqli_stmt_execute($stmt_org);
$res_org = mysqli_stmt_get_result($stmt_org);
while ($row_o = mysqli_fetch_assoc($res_org)) $my_orgs[$row_o['organisasi_id']] = $row_o['role'];
mysqli_stmt_close($stmt_org);

// Ambil status pending request saya
$my_pending_org = [];
$stmt_p_org = mysqli_prepare($conn, "SELECT organisasi_id FROM organisasi_permintaan WHERE user_id = ? AND status = 'pending'");
mysqli_stmt_bind_param($stmt_p_org, 'i', $my_id);
mysqli_stmt_execute($stmt_p_org);
$res_p_org = mysqli_stmt_get_result($stmt_p_org);
while ($row_p = mysqli_fetch_assoc($res_p_org)) $my_pending_org[] = $row_p['organisasi_id'];
mysqli_stmt_close($stmt_p_org);

if ($q !== '') {
    $cari_like = "%$q%";
    if ($tab === 'user') {
        $stmt_u = mysqli_prepare($conn, "SELECT id, nama_lengkap, organisasi, role, avatar FROM users WHERE id != ? AND deleted_at IS NULL AND aktif = 1 AND (nama_lengkap LIKE ? OR organisasi LIKE ?) LIMIT 50");
        mysqli_stmt_bind_param($stmt_u, 'iss', $my_id, $cari_like, $cari_like);
        mysqli_stmt_execute($stmt_u); $res_u = mysqli_stmt_get_result($stmt_u);
        while ($u = mysqli_fetch_assoc($res_u)) $hasil_user[] = $u;
        mysqli_stmt_close($stmt_u);
    } elseif ($tab === 'objek') {
        $stmt_o = mysqli_prepare($conn, "SELECT id, kode_objek, nama_objek, jenis_objek, lokasi FROM objek WHERE deleted_at IS NULL AND status = 'aktif' AND (visibility = 'publik' OR user_id = ?) AND (nama_objek LIKE ? OR kode_objek LIKE ? OR lokasi LIKE ?) LIMIT 50");
        mysqli_stmt_bind_param($stmt_o, 'isss', $my_id, $cari_like, $cari_like, $cari_like);
        mysqli_stmt_execute($stmt_o); $res_o = mysqli_stmt_get_result($stmt_o);
        while ($o = mysqli_fetch_assoc($res_o)) $hasil_objek[] = $o;
        mysqli_stmt_close($stmt_o);
    } elseif ($tab === 'organisasi') {
        $stmt_g = mysqli_prepare($conn, "SELECT o.id, o.nama, o.kode_unik, o.deskripsi, u.nama_lengkap as pic_nama, (SELECT COUNT(*) FROM organisasi_anggota WHERE organisasi_id = o.id) as total_anggota FROM organisasi o LEFT JOIN users u ON o.pic_user_id = u.id WHERE (o.nama LIKE ? OR o.kode_unik LIKE ?) AND o.status = 'aktif' AND o.deleted_at IS NULL LIMIT 50");
        mysqli_stmt_bind_param($stmt_g, 'ss', $cari_like, $cari_like);
        mysqli_stmt_execute($stmt_g); $res_g = mysqli_stmt_get_result($stmt_g);
        while ($g = mysqli_fetch_assoc($res_g)) $hasil_org[] = $g;
        mysqli_stmt_close($stmt_g);
    }
}
?>

<main class="max-w-4xl mx-auto px-4 pt-24 pb-16">
    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <form method="GET" action="index.php" class="mb-8 relative">
        <input type="hidden" name="page" value="cari">
        <input type="hidden" name="tab" value="<?= $tab ?>">
        <div class="relative">
            <i data-lucide="search" class="w-6 h-6 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama user, organisasi, atau objek..." autofocus class="w-full pl-12 pr-4 py-4 text-lg border-2 border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none shadow-sm transition-all">
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition">Cari</button>
        </div>
    </form>

    <div class="flex gap-2 mb-6 border-b border-slate-200 overflow-x-auto">
        <a href="index.php?page=cari&q=<?= urlencode($q) ?>&tab=user" class="px-6 py-3 font-bold text-sm <?= $tab=='user' ? 'text-emerald-600 border-b-2 border-emerald-600' : 'text-slate-500 hover:text-slate-800' ?> whitespace-nowrap transition flex items-center gap-2"><i data-lucide="users" class="w-4 h-4"></i> Pengguna</a>
        <a href="index.php?page=cari&q=<?= urlencode($q) ?>&tab=organisasi" class="px-6 py-3 font-bold text-sm <?= $tab=='organisasi' ? 'text-emerald-600 border-b-2 border-emerald-600' : 'text-slate-500 hover:text-slate-800' ?> whitespace-nowrap transition flex items-center gap-2"><i data-lucide="building-2" class="w-4 h-4"></i> Organisasi</a>
        <a href="index.php?page=cari&q=<?= urlencode($q) ?>&tab=objek" class="px-6 py-3 font-bold text-sm <?= $tab=='objek' ? 'text-emerald-600 border-b-2 border-emerald-600' : 'text-slate-500 hover:text-slate-800' ?> whitespace-nowrap transition flex items-center gap-2"><i data-lucide="package" class="w-4 h-4"></i> Objek</a>
    </div>

    <?php if($q === ''): ?>
        <div class="text-center py-16"><i data-lucide="search" class="w-16 h-16 text-slate-200 mx-auto mb-2"></i><p class="text-slate-500">Mulai pencarian Anda.</p></div>
    <?php else: ?>
        <?php if($tab === 'user'): ?>
            <div class="space-y-3">
                <?php if(empty($hasil_user)): ?><p class="text-center text-slate-500 py-8">Tidak ada pengguna yang cocok.</p><?php else: foreach($hasil_user as $u): 
                    $status = $my_follows[$u['id']] ?? 'none';
                ?>
                    <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center justify-between gap-4 hover:shadow-md transition">
                        <div class="flex items-center gap-4 min-w-0">
                            <a href="index.php?page=user_profil&id=<?= $u['id'] ?>"><?= tampil_avatar($u, 'w-12 h-12', 'text-lg') ?></a>
                            <div class="min-w-0">
                                <a href="index.php?page=user_profil&id=<?= $u['id'] ?>" class="font-bold text-slate-800 hover:text-emerald-600 flex items-center gap-1 truncate"><?= e($u['nama_lengkap']) ?> <?= tampil_badge_verified($u['role']) ?></a>
                                <div class="text-xs text-slate-500 truncate"><?= e($u['organisasi'] ?: 'Individu') ?></div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <?php if($status === 'accepted'): ?><button disabled class="px-4 py-2 bg-slate-100 text-slate-500 font-bold text-xs rounded-lg cursor-not-allowed">Mengikuti</button>
                            <?php elseif($status === 'pending'): ?><button disabled class="px-4 py-2 bg-amber-50 text-amber-600 font-bold text-xs rounded-lg cursor-not-allowed">Menunggu</button>
                            <?php else: ?><a href="index.php?page=user_profil&id=<?= $u['id'] ?>" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition inline-block">Lihat Profil</a><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

        <?php elseif($tab === 'organisasi'): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if(empty($hasil_org)): ?><p class="col-span-2 text-center text-slate-500 py-8">Tidak ada organisasi yang cocok.</p><?php else: foreach($hasil_org as $g): 
                    $is_member = isset($my_orgs[$g['id']]);
                    $is_pending = in_array($g['id'], $my_pending_org);
                ?>
                    <div class="bg-white rounded-3xl border border-slate-100 p-6 flex flex-col hover:shadow-md transition">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-3"><i data-lucide="building" class="w-6 h-6"></i></div>
                        <h3 class="font-bold text-slate-800 text-lg mb-1"><?= e($g['nama']) ?></h3>
                        <div class="text-[10px] font-mono text-slate-400 mb-2"><?= e($g['kode_unik']) ?></div>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-4 flex-1"><?= e($g['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                        <div class="text-xs text-slate-600 mb-4"><i data-lucide="users" class="w-3 h-3 inline"></i> <b><?= $g['total_anggota'] ?></b> Anggota &middot; PIC: <b><?= e($g['pic_nama']) ?></b></div>
                        
                        <?php if($is_member): ?>
                            <a href="index.php?page=organisasi_detail&id=<?= $g['id'] ?>" class="w-full py-2 bg-slate-100 text-slate-600 font-bold text-center rounded-xl text-sm hover:bg-slate-200">Buka Dashboard</a>
                        <?php elseif($is_pending): ?>
                            <button disabled class="w-full py-2 bg-amber-50 text-amber-600 font-bold text-center rounded-xl text-sm cursor-not-allowed">Menunggu Persetujuan</button>
                        <?php else: ?>
                            <form method="POST" action="index.php?page=cari&tab=organisasi&q=<?= urlencode($q) ?>">
                                <input type="hidden" name="aksi_gabung" value="1">
                                <input type="hidden" name="organisasi_id" value="<?= $g['id'] ?>">
                                <input type="hidden" name="q" value="<?= e($q) ?>">
                                <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold rounded-xl text-sm hover:bg-emerald-700 transition">Ajukan Gabung</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>

        <?php elseif($tab === 'objek'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php if(empty($hasil_objek)): ?><p class="col-span-2 text-center text-slate-500 py-8">Tidak ada objek yang cocok.</p><?php else: foreach($hasil_objek as $o): 
                    $info = ['pohon'=>['bg'=>'bg-emerald-500','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-500','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-500','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-500','ic'=>'recycle']][$o['jenis_objek']] ?? ['bg'=>'bg-slate-500','ic'=>'package'];
                ?>
                    <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-4 hover:shadow-md transition group">
                        <div class="w-14 h-14 rounded-xl <?= $info['bg'] ?> bg-opacity-10 flex items-center justify-center flex-shrink-0"><i data-lucide="<?= $info['ic'] ?>" class="w-7 h-7 <?= str_replace('bg-', 'text-', $info['bg']) ?>"></i></div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-mono text-slate-400 mb-0.5"><?= e($o['kode_objek']) ?></div>
                            <div class="font-bold text-slate-800 truncate group-hover:text-emerald-600 transition"><?= e($o['nama_objek']) ?></div>
                            <div class="text-xs text-slate-500 truncate"><?= e($o['lokasi'] ?: '-') ?></div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
