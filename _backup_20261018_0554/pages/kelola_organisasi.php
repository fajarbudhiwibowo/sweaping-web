<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/functions.php';

if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$cari = trim($_GET['cari'] ?? '');
$where = "o.deleted_at IS NULL";
$params = []; $types = '';

if ($cari !== '') {
    $where .= " AND (o.nama LIKE ? OR o.kode_unik LIKE ?)";
    $cari_like = "%$cari%";
    $params[] = $cari_like; $params[] = $cari_like; $types .= 'ss';
}

$sql = "SELECT o.*, u.nama_lengkap as pic_nama, (SELECT COUNT(*) FROM organisasi_anggota WHERE organisasi_id = o.id) as total_anggota FROM organisasi o LEFT JOIN users u ON o.pic_user_id = u.id WHERE $where ORDER BY o.id DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$q_org = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-6xl mx-auto px-4 pt-24 pb-16">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="building-2" class="w-7 h-7 text-indigo-600"></i> Manajemen Organisasi
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kelola entitas organisasi/instansi.</p>
        </div>
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="hidden" name="page" value="kelola_organisasi">
            <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama/kode..." class="w-full md:w-64 px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition">Cari</button>
        </form>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200">
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                        <th class="px-4 py-4">Organisasi</th>
                        <th class="px-4 py-4">PIC (Admin)</th>
                        <th class="px-4 py-4 text-center">Anggota</th>
                        <th class="px-4 py-4 text-center">Status</th>
                        <th class="px-4 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php while($o = mysqli_fetch_assoc($q_org)): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800"><?= e($o['nama']) ?></div>
                            <div class="text-[10px] font-mono text-slate-400 mt-0.5"><?= e($o['kode_unik']) ?></div>
                        </td>
                        <td class="px-4 py-3 text-slate-600"><?= e($o['pic_nama'] ?: '-') ?></td>
                        <td class="px-4 py-3 text-center font-bold text-slate-700"><?= number_format($o['total_anggota']) ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if(($o['status'] ?? 'aktif') === 'aktif'): ?>
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-bold text-xs"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif</span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-rose-600 font-bold text-xs"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Banned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="index.php?page=organisasi_detail&id=<?= $o['id'] ?>" class="p-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-200" title="Lihat Dashboard"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="index.php?page=kelola_organisasi_edit&id=<?= $o['id'] ?>" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" title="Edit Organisasi"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                
                                <form method="POST" action="index.php?page=aksi_kelola_organisasi" class="inline">
                                    <input type="hidden" name="org_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="aksi" value="toggle_status">
                                    <button type="submit" class="p-2 <?= ($o['status'] ?? 'aktif') === 'aktif' ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' ?> rounded-lg" title="<?= ($o['status'] ?? 'aktif') === 'aktif' ? 'Ban Organisasi' : 'Aktifkan Organisasi' ?>">
                                        <i data-lucide="<?= ($o['status'] ?? 'aktif') === 'aktif' ? 'shield-ban' : 'shield-check' ?>" class="w-4 h-4"></i>
                                    </button>
                                </form>

                                <form method="POST" action="index.php?page=aksi_kelola_organisasi" class="inline" onsubmit="return confirm('Hapus organisasi ini?');">
                                    <input type="hidden" name="org_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <button type="submit" class="p-2 bg-slate-800 text-white rounded-lg hover:bg-rose-700" title="Hapus Organisasi"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
