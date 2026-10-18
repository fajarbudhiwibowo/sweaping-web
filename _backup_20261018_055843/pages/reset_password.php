<?php
$token = trim($_GET['token'] ?? '');
$error_msg = '';
$user_data = null;

if (empty($token) || strlen($token) !== 32) {
    $error_msg = 'Link tidak valid.';
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, reset_expires FROM users WHERE reset_token = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$user_data) $error_msg = 'Link tidak valid atau sudah dipakai.';
    elseif (strtotime($user_data['reset_expires']) < time()) {
        $error_msg = 'Link sudah kadaluarsa.';
        $user_data = null;
    }
}

// Proses simpan password baru
$sukses = '';
if (isset($_POST['aksi_simpan_password']) && $user_data) {
    $pass = $_POST['password_baru'] ?? '';
    $konf = $_POST['password_konfirmasi'] ?? '';
    
    if (strlen($pass) < 6) $error_msg = 'Password minimal 6 karakter.';
    elseif ($pass !== $konf) $error_msg = 'Konfirmasi tidak sama.';
    else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $user_data['id']);
        mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        $sukses = 'Password berhasil diubah! Silakan login.';
        $user_data = null;
    }
}
?>

<main class="max-w-md mx-auto px-4 py-24">
    <div class="bg-white rounded-3xl shadow-xl p-8 border border-slate-100 text-center">
        <?php if($sukses): ?>
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4"><i data-lucide="check-circle" class="w-8 h-8"></i></div>
            <h2 class="text-xl font-bold mb-2">Berhasil!</h2>
            <p class="text-sm text-slate-500 mb-6"><?= $sukses ?></p>
            <a href="index.php?page=login" class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl block">Login Sekarang</a>
            
        <?php elseif($error_msg): ?>
            <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4"><i data-lucide="x-circle" class="w-8 h-8"></i></div>
            <h2 class="text-xl font-bold mb-2">Gagal</h2>
            <p class="text-sm text-slate-500 mb-6"><?= e($error_msg) ?></p>
            <a href="index.php?page=login" class="w-full py-3 bg-slate-200 text-slate-700 font-bold rounded-xl block">Kembali ke Login</a>
            
        <?php else: ?>
            <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4"><i data-lucide="lock" class="w-8 h-8"></i></div>
            <h2 class="text-xl font-bold mb-1">Buat Password Baru</h2>
            <p class="text-xs text-slate-500 mb-6">Untuk: <b><?= e($user_data['nama_lengkap']) ?></b></p>
            
            <form method="POST" class="space-y-4 text-left">
                <input type="hidden" name="aksi_simpan_password" value="1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru</label>
                    <input type="password" name="password_baru" required minlength="6" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_konfirmasi" required minlength="6" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full py-3.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition">Simpan Password</button>
            </form>
        <?php endif; ?>
    </div>
</main>
