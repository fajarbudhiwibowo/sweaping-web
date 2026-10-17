<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SWEAPING v6</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; padding-bottom: 80px; }
        @media (min-width: 768px) { body { padding-bottom: 0; } }
    </style>
</head>
<body class="text-slate-800 antialiased">

<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 fixed top-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <!-- Logo -->
        <a href="index.php?page=beranda" class="flex items-center gap-2 flex-shrink-0">
            <img src="assets/logo-bulat.png" alt="Logo" class="h-8 w-8" onerror="this.outerHTML='<span class=\'text-xl font-extrabold text-emerald-700\'>SWEAPING</span>'">
            <span class="hidden md:block font-extrabold text-emerald-800 tracking-tight">SWEAPING <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full ml-1">v6</span></span>
        </a>

        <!-- Menu Navigasi Desktop (Tengah) -->
        <nav class="hidden md:flex items-center gap-6">
            <a href="index.php?page=beranda" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition">Beranda</a>
            <a href="index.php?page=objek" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition">Objek</a>
            <a href="index.php?page=area" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition">Area Pantau</a>
            <a href="index.php?page=peta" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition">Peta</a>
            <a href="index.php?page=arsip" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition">Arsip</a>
            <a href="index.php?page=organisasi" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition">Organisasi</a>
            
            <?php if(is_logged_in()): ?>
                <a href="index.php?page=lapor" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-sm shadow-md hover:bg-emerald-700 transition flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Buat Laporan
                </a>
            <?php endif; ?>
        </nav>

        <!-- Menu Kanan (Profil) -->
        <div class="flex items-center gap-4">
            <?php if(is_logged_in()): 
                $stmt_n = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM follows WHERE following_id = ? AND status = 'pending'");
                mysqli_stmt_bind_param($stmt_n, 'i', $_SESSION['user_id']);
                mysqli_stmt_execute($stmt_n);
                $notif_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_n))['c'];
                mysqli_stmt_close($stmt_n);
            ?>
            <?php
// Ambil XP dari session atau database (jika tersedia)
$user_xp = 0;
if (is_logged_in()) {
    $stmt_xp = mysqli_prepare($conn, "SELECT eco_points FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_xp, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_xp);
    $res_xp = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_xp));
    $user_xp = $res_xp ? (int)$res_xp['eco_points'] : 0;
    mysqli_stmt_close($stmt_xp);
}
?>
<div class="text-[10px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-full mt-1 inline-block">
    ⚡ <?= number_format($user_xp) ?> XP
</div>
                
                <a href="index.php?page=cari" class="p-2 text-slate-600 hover:text-emerald-600 transition" title="Pencarian Global">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </a><a href="index.php?page=permintaan_follow" class="relative p-2 text-slate-600 hover:text-emerald-600 transition" title="Permintaan Follow">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <?php if($notif_count > 0): ?>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if(is_logged_in()): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 focus:outline-none">
                        <div class="text-right hidden md:block">
                            <div class="text-sm font-bold flex items-center justify-end">
                                <?= e($_SESSION['nama'] ?? 'User') ?> 
                                <?= tampil_badge_verified($_SESSION['role'] ?? 'user') ?>
                            </div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wide"><?= e($_SESSION['role'] ?? 'user') ?></div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">
                            <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden md:block"></i>
                    </button>
                    
                    <!-- Isi Dropdown -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <a href="index.php?page=user_profil" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                        </a>
                        
                <a href="index.php?page=cari" class="p-2 text-slate-600 hover:text-emerald-600 transition" title="Pencarian Global">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </a><a href="index.php?page=permintaan_follow" class="flex items-center gap-2 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">
                            <i data-lucide="user-plus" class="w-4 h-4"></i> Permintaan Follow
                            <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                        </a>
                        
                        <?php if(is_admin()): ?>
                            <div class="border-t border-slate-100 my-1"></div>
                            
                            <a href="index.php?page=kelola_organisasi" class="flex items-center gap-2 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 font-semibold">
                                <i data-lucide="building-2" class="w-4 h-4"></i> Manajemen Organisasi
                            </a>\n                            <a href="index.php?page=kelola_user" class="flex items-center gap-2 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50 font-semibold">
                                <i data-lucide="users" class="w-4 h-4"></i> Kelola User
                            </a>
                            <a href="index.php?page=audit" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i data-lucide="clipboard-list" class="w-4 h-4"></i> Audit Log
                            </a>
                        <?php endif; ?>
                        
                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="index.php?page=logout" class="flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-semibold">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php?page=login" class="text-sm font-bold text-emerald-700 hover:text-emerald-800">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="h-16"></div>
