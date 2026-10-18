<?php
// Logika Proses Register
if (isset($_POST['aksi_register'])) {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $organisasi = trim($_POST['organisasi'] ?? '');

    if ($nama === '' || $email === '' || $password === '') {
        $error = 'Nama, Email, dan Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Cek apakah email sudah terdaftar
        $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_cek, 's', $email);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        
        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        } else {
            mysqli_stmt_close($stmt_cek);
            
            // Simpan ke database
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Default role
            
            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap, email, password, role, organisasi) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $hash, $role, $organisasi);
            
            if (mysqli_stmt_execute($stmt)) {
                $new_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                
                // Catat log
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $stmt_log = mysqli_prepare($conn, "INSERT INTO audit_log (user_id, aksi, detail, ip_address) VALUES (?, 'register', 'Registrasi akun baru', ?)");
                if ($stmt_log) {
                    mysqli_stmt_bind_param($stmt_log, 'is', $new_id, $ip);
                    mysqli_stmt_execute($stmt_log);
                    mysqli_stmt_close($stmt_log);
                }
                
                // Redirect ke login dengan pesan sukses (bisa pakai session flash jika ada)
                redirect('index.php?page=login');
            } else {
                $error = 'Gagal mendaftar: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<main class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white rounded-3xl shadow-xl p-8 border border-slate-100">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="user-plus" class="w-8 h-8 text-emerald-600"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Daftar Akun Baru</h1>
            <p class="text-sm text-slate-500 mt-2">Bergabung dengan komunitas peduli lingkungan</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=register" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Organisasi / Komunitas <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="text" name="organisasi" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password <span class="text-slate-400 font-normal">(Min. 6 Karakter)</span></label>
                <input type="password" name="password" required minlength="6" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
            </div>
            
            <button type="submit" name="aksi_register" class="w-full py-3.5 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition mt-2">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun? <a href="index.php?page=login" class="font-bold text-emerald-700 hover:underline">Masuk di sini</a>
        </div>
    </div>
</main>
