<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$org_id = (int)($_GET['id'] ?? 0);
if ($org_id <= 0) { redirect('index.php?page=organisasi'); }

$uid = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM organisasi WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $org_id);
mysqli_stmt_execute($stmt);
$org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$org) { echo "<div class='pt-32 text-center text-slate-500'>Organisasi tidak ditemukan.</div>"; exit; }

$is_pic = ((int)$org['pic_user_id'] === $uid);

$stmt_cek = mysqli_prepare($conn, "SELECT role FROM organisasi_anggota WHERE organisasi_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_cek, 'ii', $org_id, $uid);
mysqli_stmt_execute($stmt_cek);
$r_cek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
mysqli_stmt_close($stmt_cek);
$my_role = $r_cek ? $r_cek['role'] : 'bukan_anggota';

$stmt_anggota = mysqli_prepare($conn, "SELECT u.id, u.nama_lengkap, u.email, oa.role FROM organisasi_anggota oa INNER JOIN users u ON oa.user_id = u.id WHERE oa.organisasi_id = ? ORDER BY oa.role ASC, u.nama_lengkap ASC");
mysqli_stmt_bind_param($stmt_anggota, 'i', $org_id);
mysqli_stmt_execute($stmt_anggota);
$q_anggota = mysqli_stmt_get_result($stmt_anggota);

$stmt_objek = mysqli_prepare($conn, "SELECT * FROM objek WHERE organisasi_id = ? AND deleted_at IS NULL ORDER BY id DESC");
mysqli_stmt_bind_param($stmt_objek, 'i', $org_id);
mysqli_stmt_execute($stmt_objek);
$q_objek = mysqli_stmt_get_result($stmt_objek);

$q_req = null;
if($is_pic || is_admin()) {
    $stmt_req = mysqli_prepare($conn, "SELECT r.id as req_id, u.nama_lengkap, u.email, u.avatar FROM organisasi_permintaan r JOIN users u ON r.user_id = u.id WHERE r.organisasi_id = ? AND r.status = 'pending'");
    mysqli_stmt_bind_param($stmt_req, 'i', $org_id);
    mysqli_stmt_execute($stmt_req);
    $q_req = mysqli_stmt_get_result($stmt_req);
}
?>

<main class="max-w-6xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-6">
        <a href="index.php?page=organisasi" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Organisasi
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 mb-8 flex flex-col md:flex-row items-center gap-6">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl flex items-center justify-center shadow-lg flex-shrink-0">
            <i data-lucide="building-2" class="w-12 h-12 text-white"></i>
        </div>
        <div class="text-center md:text-left flex-1">
            <h1 class="text-3xl font-extrabold text-slate-900 mb-1"><?= e($org['nama']) ?></h1>
            <div class="text-sm font-mono text-slate-500 mb-2">Kode: <?= e($org['kode_unik']) ?></div>
            <p class="text-slate-600 text-sm"><?= e($org['deskripsi'] ?: 'Belum ada deskripsi.') ?></p>
        </div>
        <div class="bg-slate-50 px-6 py-4 rounded-2xl border border-slate-200 text-center">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Status Anda</div>
            <div class="font-extrabold text-lg text-emerald-600 uppercase"><?= str_replace('_', ' ', $my_role) ?></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center"><i data-lucide="users" class="w-5 h-5"></i></div>
                    <div class="font-bold text-slate-800">Total Anggota</div>
                </div>
                <div class="text-3xl font-extrabold text-slate-900"><?= mysqli_num_rows($q_anggota) ?></div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center"><i data-lucide="package" class="w-5 h-5"></i></div>
                    <div class="font-bold text-slate-800">Total Objek</div>
                </div>
                <div class="text-3xl font-extrabold text-slate-900"><?= mysqli_num_rows($q_objek) ?></div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <?php if($q_req && mysqli_num_rows($q_req) > 0): ?>
            <div class="bg-white rounded-3xl shadow-sm border-2 border-amber-200 overflow-hidden">
                <div class="p-5 border-b border-amber-100 bg-amber-50 flex justify-between items-center">
                    <h2 class="font-bold text-amber-800 flex items-center gap-2"><i data-lucide="user-plus" class="w-5 h-5"></i> Permintaan Gabung</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php while($r = mysqli_fetch_assoc($q_req)): ?>
                        <div class="p-4 flex items-center justify-between gap-4" id="req-org-<?= $r['req_id'] ?>">
                            <div class="flex items-center gap-3 min-w-0">
                                <?= tampil_avatar($r, 'w-10 h-10', 'text-sm') ?>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 truncate"><?= e($r['nama_lengkap']) ?></div>
                                    <div class="text-xs text-slate-500 truncate"><?= e($r['email']) ?></div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="respondOrg(<?= $r['req_id'] ?>, 'rejected')" class="px-3 py-1.5 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg hover:bg-rose-100 hover:text-rose-700">Tolak</button>
                                <button type="button" onclick="respondOrg(<?= $r['req_id'] ?>, 'accepted')" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700">Terima</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="users" class="w-5 h-5 text-emerald-600"></i> Anggota Organisasi</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if(mysqli_num_rows($q_anggota) > 0): while($a = mysqli_fetch_assoc($q_anggota)): ?>
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold flex-shrink-0"><?= strtoupper(substr($a['nama_lengkap'],0,1)) ?></div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 truncate"><?= e($a['nama_lengkap']) ?></div>
                                    <div class="text-xs text-slate-500 truncate"><?= e($a['email']) ?></div>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full <?= $a['role']=='pic' ? 'bg-amber-100 text-amber-700' : ($a['role']=='admin_org' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') ?>"><?= $a['role'] ?></span>
                        </div>
                    <?php endwhile; else: echo '<div class="p-6 text-center text-sm text-slate-500">Belum ada anggota.</div>'; endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="package" class="w-5 h-5 text-blue-600"></i> Objek Lingkungan</h2>
                    <a href="index.php?page=objek_daftar" class="text-xs font-bold text-emerald-600 hover:underline">+ Tambah Objek</a>
                </div>
                <div class="p-4">
                    <?php if(mysqli_num_rows($q_objek) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php while($o = mysqli_fetch_assoc($q_objek)): ?>
                                <div class="border border-slate-200 rounded-2xl p-4 flex gap-3 hover:shadow-md transition">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="<?= $o['jenis_objek']=='pohon'?'tree-pine':($o['jenis_objek']=='sungai'?'waves':($o['jenis_objek']=='taman'?'flower-2':'recycle')) ?>" class="w-6 h-6 text-emerald-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate"><?= e($o['nama_objek']) ?></div>
                                        <div class="text-[10px] text-slate-500 uppercase font-bold"><?= e($o['jenis_objek']) ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-sm text-slate-500 py-6">Belum ada objek yang didaftarkan atas nama organisasi ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function respondOrg(reqId, action) {
    const formData = new FormData();
    formData.append('req_id', reqId);
    formData.append('action', action);
    fetch('index.php?page=aksi_respond_org', { method: 'POST', body: formData })
    .then(res => res.json()).then(data => {
        if(data.status === 'ok') {
            const el = document.getElementById('req-org-' + reqId);
            el.style.opacity = '0'; setTimeout(() => el.remove(), 300);
            if(action === 'accepted') setTimeout(() => location.reload(), 500);
        } else alert('Gagal memproses: ' + (data.message || 'Unknown error'));
    }).catch(err => alert('Error: ' + err.message));
}
</script>
