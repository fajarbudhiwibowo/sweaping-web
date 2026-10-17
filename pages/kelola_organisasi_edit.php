<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/functions.php';

if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$org_id = (int)($_GET['id'] ?? 0);
if ($org_id <= 0) { redirect('index.php?page=kelola_organisasi'); }

$sukses = '';
$error = '';

if (isset($_POST['aksi_edit_org'])) {
    $nama = trim($_POST['nama'] ?? '');
    $kode_unik = trim($_POST['kode_unik'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $pic_user_id = (int)($_POST['pic_user_id'] ?? 0);
    
    if ($nama === '' || $kode_unik === '') {
        $error = 'Nama dan Kode Unik wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE organisasi SET nama = ?, kode_unik = ?, deskripsi = ?, pic_user_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'sssii', $nama, $kode_unik, $deskripsi, $pic_user_id, $org_id);
        if (mysqli_stmt_execute($stmt)) {
            $sukses = 'Data organisasi berhasil diperbarui.';
        } else {
            $error = 'Gagal update: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

$stmt = mysqli_prepare($conn, "SELECT * FROM organisasi WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $org_id);
mysqli_stmt_execute($stmt);
$o = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$o) { redirect('index.php?page=kelola_organisasi'); }

// Ambil daftar user untuk dropdown PIC
$q_users = mysqli_query($conn, "SELECT id, nama_lengkap FROM users WHERE deleted_at IS NULL ORDER BY nama_lengkap ASC");
?>

<main class="max-w-3xl mx-auto px-4 pt-24 pb-16">
    <a href="index.php?page=kelola_organisasi" class="text-sm font-bold text-indigo-600 hover:underline flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Manajemen Organisasi
    </a>
    
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
            <i data-lucide="building-2" class="w-6 h-6 text-indigo-600"></i> Edit Organisasi
        </h1>

        <?php if($error): ?><div class="bg-rose-50 text-rose-700 p-3 rounded-xl mb-4 text-sm font-bold"><?= e($error) ?></div><?php endif; ?>
        <?php if($sukses): ?><div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl mb-4 text-sm font-bold"><?= e($sukses) ?></div><?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Nama Organisasi</label>
                <input type="text" name="nama" value="<?= e($o['nama']) ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Kode Unik</label>
                <input type="text" name="kode_unik" value="<?= e($o['kode_unik']) ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">PIC (Penanggung Jawab)</label>
                <select name="pic_user_id" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="0">-- Pilih PIC --</option>
                    <?php while($u = mysqli_fetch_assoc($q_users)): ?>
                        <option value="<?= $u['id'] ?>" <?= $o['pic_user_id'] == $u['id'] ? 'selected' : '' ?>><?= e($u['nama_lengkap']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= e($o['deskripsi']) ?></textarea>
            </div>
            <button type="submit" name="aksi_edit_org" class="w-full py-3.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition">Simpan Perubahan</button>
        </form>
    </div>
</main>
