<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$target_id = (int)($_GET['id'] ?? 0);
if ($target_id <= 0) { redirect('index.php?page=kelola_user'); }

$sukses = '';
$error = '';

if (isset($_POST['aksi_edit_user'])) {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $organisasi = trim($_POST['organisasi'] ?? '');
    
    if ($nama === '' || $email === '') {
        $error = 'Nama dan Email wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET nama_lengkap = ?, email = ?, role = ?, organisasi = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $role, $organisasi, $target_id);
        if (mysqli_stmt_execute($stmt)) {
            $sukses = 'Data user berhasil diperbarui.';
        } else {
            $error = 'Gagal update: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $target_id);
mysqli_stmt_execute($stmt);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$u) { redirect('index.php?page=kelola_user'); }
?>

<main class="max-w-3xl mx-auto px-4 pt-24 pb-16">
    <a href="index.php?page=kelola_user" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Kelola User
    </a>
    
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
            <i data-lucide="user-cog" class="w-6 h-6 text-indigo-600"></i> Edit User: <?= e($u['nama_lengkap']) ?>
        </h1>

        <?php if($error): ?><div class="bg-rose-50 text-rose-700 p-3 rounded-xl mb-4 text-sm font-bold"><?= e($error) ?></div><?php endif; ?>
        <?php if($sukses): ?><div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl mb-4 text-sm font-bold"><?= e($sukses) ?></div><?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?= e($u['nama_lengkap']) ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="<?= e($u['email']) ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Organisasi</label>
                <input type="text" name="organisasi" value="<?= e($u['organisasi'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Role</label>
                <select name="role" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin Website</option>
                    <option value="super_admin" <?= $u['role']=='super_admin'?'selected':'' ?>>Super Admin</option>
                </select>
            </div>
            <button type="submit" name="aksi_edit_user" class="w-full py-3.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition">Simpan Perubahan</button>
        </form>
    </div>
</main>
