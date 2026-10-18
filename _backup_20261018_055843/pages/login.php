<?php
// Logika Proses Login
if (isset($_POST['aksi_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, password, role FROM users WHERE email = ? AND aktif = 1 AND deleted_at IS NULL LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            // Login Sukses
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Catat ke audit log (jika tabelnya ada)
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $stmt_log = mysqli_prepare($conn, "INSERT INTO audit_log (user_id, aksi, detail, ip_address) VALUES (?, 'login', 'Login berhasil', ?)");
            if ($stmt_log) {
                mysqli_stmt_bind_param($stmt_log, 'is', $user['id'], $ip);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
            }
            
            redirect('index.php?page=beranda');
        } else {
            $error = 'Email atau password salah, atau akun tidak aktif.';
        }
    }
}
?>

<main class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white rounded-3xl shadow-xl p-8 border border-slate-100">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="log-in" class="w-8 h-8 text-emerald-600"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Masuk ke SWEAPING</h1>
            <p class="text-sm text-slate-500 mt-2">Platform Konservasi & Akuntansi Karbon</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=login" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
            </div>
            
            <button type="submit" name="aksi_login" class="w-full py-3.5 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition mt-2">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Belum punya akun? <a href="index.php?page=register" class="font-bold text-emerald-700 hover:underline">Daftar Sekarang</a>
        </div>
    </div>
</main>
