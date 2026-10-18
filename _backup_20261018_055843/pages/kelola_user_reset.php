<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$target_id = (int)($_GET['id'] ?? 0);
if ($target_id <= 0) { redirect('index.php?page=kelola_user'); }

$stmt = mysqli_prepare($conn, "SELECT nama_lengkap, email FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $target_id);
mysqli_stmt_execute($stmt);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$u) { redirect('index.php?page=kelola_user'); }

$sukses = '';
$error = '';
$link_generated = '';
$pass_generated = '';

if (isset($_POST['aksi_reset'])) {
    $mode = $_POST['mode'] ?? '';
    
    if ($mode === 'manual') {
        $pass = trim($_POST['password_baru'] ?? '');
        if (strlen($pass) < 6) { $error = 'Password minimal 6 karakter.'; } 
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $hash, $target_id);
            mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
            $sukses = 'Password berhasil diubah secara manual.';
        }
    } elseif ($mode === 'generate') {
        $pass = 'swp' . rand(100000, 999999);
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $target_id);
        mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        $sukses = 'Password berhasil di-generate.';
        $pass_generated = $pass;
    } elseif ($mode === 'link') {
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 jam
        $stmt = mysqli_prepare($conn, "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $token, $expires, $target_id);
        mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        $sukses = 'Link reset berhasil dibuat.';
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $link_generated = $base_url . '/index.php?page=reset_password&token=' . $token;
    }
}
?>

<main class="max-w-2xl mx-auto px-4 pt-24 pb-16">
    <a href="index.php?page=kelola_user" class="text-sm font-bold text-indigo-600 hover:underline flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Kelola User
    </a>
    
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="key-round" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Reset Password</h1>
            <p class="text-sm text-slate-500 mt-1">Untuk: <b><?= e($u['nama_lengkap']) ?></b> (<?= e($u['email']) ?>)</p>
        </div>

        <?php if($error): ?><div class="bg-rose-50 text-rose-700 p-3 rounded-xl mb-4 text-sm font-bold"><?= e($error) ?></div><?php endif; ?>
        <?php if($sukses): ?><div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl mb-4 text-sm font-bold"><?= e($sukses) ?></div><?php endif; ?>

        <?php if($pass_generated): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-center">
                <p class="text-sm text-amber-800 mb-1">Password Baru (Catat & kirim ke user):</p>
                <div class="text-2xl font-mono font-bold text-amber-900 tracking-wider select-all"><?= $pass_generated ?></div>
            </div>
        <?php endif; ?>

        <?php if($link_generated): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-amber-800 mb-1">Link Reset (Berlaku 1 jam):</p>
                <textarea readonly class="w-full text-xs font-mono bg-white border border-amber-300 rounded-lg p-2 mb-2" rows="3"><?= $link_generated ?></textarea>
                <a href="https://wa.me/?text=<?= urlencode('Halo ' . $u['nama_lengkap'] . ', ini link reset password SWEAPING Anda: ' . $link_generated . ' (Berlaku 1 jam)') ?>" target="_blank" class="inline-flex w-full py-2.5 bg-[#25D366] text-white font-bold rounded-lg hover:bg-[#1faa52] justify-center items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Kirim via WhatsApp
                </a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Manual -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                <h3 class="font-bold text-slate-800 text-sm mb-2 text-center">Set Manual</h3>
                <form method="POST" class="space-y-2">
                    <input type="hidden" name="aksi_reset" value="1"><input type="hidden" name="mode" value="manual">
                    <input type="text" name="password_baru" required minlength="6" placeholder="Password baru..." class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500">
                    <button type="submit" class="w-full py-2 bg-indigo-600 text-white font-bold rounded-lg text-xs hover:bg-indigo-700">Simpan Manual</button>
                </form>
            </div>

            <!-- Generate -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm mb-2 text-center">Generate Acak</h3>
                    <p class="text-xs text-slate-500 text-center mb-3">Buat password acak (swp+6 angka) secara instan.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="aksi_reset" value="1"><input type="hidden" name="mode" value="generate">
                    <button type="submit" class="w-full py-2 bg-emerald-600 text-white font-bold rounded-lg text-xs hover:bg-emerald-700">Generate Password</button>
                </form>
            </div>

            <!-- Link WA -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm mb-2 text-center">Link WA</h3>
                    <p class="text-xs text-slate-500 text-center mb-3">Buat link reset (berlaku 1 jam) untuk dikirim ke WA user.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="aksi_reset" value="1"><input type="hidden" name="mode" value="link">
                    <button type="submit" class="w-full py-2 bg-amber-500 text-white font-bold rounded-lg text-xs hover:bg-amber-600">Buat Link WA</button>
                </form>
            </div>
        </div>
    </div>
</main>
