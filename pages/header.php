<?php
// Ambil XP User untuk ditampilkan di Header
$user_xp_header = 0;
if (is_logged_in()) {
    $stmt_xp_h = mysqli_prepare($conn, "SELECT eco_points FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_xp_h, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_xp_h);
    $res_xp_h = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_xp_h));
    $user_xp_header = $res_xp_h ? (int)$res_xp_h['eco_points'] : 0;
    mysqli_stmt_close($stmt_xp_h);
}

// Ambil Notifikasi Permintaan Follow
$notif_count = 0;
if (is_logged_in()) {
    $stmt_n = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM follows WHERE following_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt_n, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_n);
    $notif_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_n))['c'];
    mysqli_stmt_close($stmt_n);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SWEAPING v6</title>
    
    <!-- Favicon (Menggunakan file V5) -->
    <link rel="icon" type="image/png" href="/assets/logo-bulat.png">
    <link rel="apple-touch-icon" href="/assets/icon_192x192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; padding-bottom: 80px; }
        @media (min-width: 768px) { body { padding-bottom: 0; } }
        /* Logo Header */
        .header-logo { height: 40px; width: auto; object-fit: contain; }
        @media (min-width: 768px) { .header-logo { height: 48px; } }
    </style>
</head>
<body class="text-slate-800 antialiased">

<header class="bg-white/90 backdrop-blur-md border-b border-slate-200 fixed top-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
        
        <!-- LOGO V5 -->
        <a href="index.php?page=beranda" class="flex items-center flex-shrink-0">
            <img src="/assets/logo-horizontal.png" alt="SWEAPING" class="header-logo mix-blend-multiply" 
                 onerror="this.onerror=null; this.src='/assets/logo-bulat.png';">
        </a>

        <!-- MENU NAVIGASI -->
        <nav class="hidden lg:flex items-center gap-6">
            <a href="index.php?page=beranda" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition whitespace-nowrap">Beranda</a>
            <a href="index.php?page=objek" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition whitespace-nowrap">Objek</a>
            <a href="index.php?page=area" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition whitespace-nowrap">Area Pantau</a>
            <a href="index.php?page=peta" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition whitespace-nowrap">Peta</a>
            <a href="index.php?page=arsip" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition whitespace-nowrap">Arsip</a>
            <a href="index.php?page=organisasi" class="text-sm font-bold text-slate-600 hover:text-emerald-600 transition whitespace-nowrap">Organisasi</a>
        </nav>

        <!-- MENU KANAN -->
        <div class="flex items-center gap-3 flex-shrink-0">
            
            <?php if(is_logged_in()): ?>
                <a href="index.php?page=lapor" class="hidden xl:flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-sm shadow-md hover:bg-emerald-700 transition whitespace-nowrap">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Pilih Objek
                </a>
            <?php endif; ?>

            <a href="index.php?page=cari" class="p-2 text-slate-500 hover:text-emerald-600 transition" title="Cari"><i data-lucide="search" class="w-5 h-5"></i></a>

            <?php if(is_logged_in()): ?>
                <a href="index.php?page=misi_saya" class="hidden md:flex items-center gap-1 bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1.5 rounded-full text-xs font-bold hover:bg-amber-100 transition whitespace-nowrap" title="Eco-Points">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i> <?= number_format($user_xp_header) ?> XP
                </a>

                <a href="index.php?page=permintaan_follow" class="relative p-2 text-slate-500 hover:text-emerald-600 transition" title="Notifikasi">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <?php if($notif_count > 0): ?>
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
                    <?php endif; ?>
                </a>

                <div class="relative group ml-1">
                    <button class="flex items-center gap-2 focus:outline-none">
                        <div class="text-right hidden md:block">
                            <div class="text-sm font-bold text-slate-800 flex items-center justify-end">
                                <?= e($_SESSION['nama'] ?? 'User') ?> 
                                <?= tampil_badge_verified($_SESSION['role'] ?? 'user') ?>
                            </div>
                            <div class="text-[10px] text-slate-500 uppercase tracking-wide"><?= e($_SESSION['role'] ?? 'user') ?></div>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold shadow-sm">
                            <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden md:block"></i>
                    </button>
                    
                    <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 mb-1">
                            <div class="text-sm font-bold text-slate-800 truncate"><?= e($_SESSION['nama'] ?? 'User') ?></div>
                            <div class="text-xs text-emerald-600 font-bold"><?= number_format($user_xp_header) ?> Eco-Points</div>
                        </div>
                        <a href="index.php?page=user_profil" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                        </a>
                        <a href="index.php?page=misi_saya" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <i data-lucide="target" class="w-4 h-4"></i> Misi Saya
                        </a>
                        
                        <?php if(is_admin()): ?>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="index.php?page=kelola_user" class="flex items-center gap-2 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50 font-semibold transition">
                                <i data-lucide="users" class="w-4 h-4"></i> Kelola User
                            </a>
                            <a href="index.php?page=kelola_organisasi" class="flex items-center gap-2 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 font-semibold transition">
                                <i data-lucide="building-2" class="w-4 h-4"></i> Manajemen Organisasi
                            </a>
                            <a href="index.php?page=audit" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">
                                <i data-lucide="clipboard-list" class="w-4 h-4"></i> Audit Log
                            </a>
                        <?php endif; ?>
                        
                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="index.php?page=logout" class="flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-semibold transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php?page=login" class="px-4 py-2 text-sm font-bold text-emerald-700 hover:text-emerald-800 transition">Masuk</a>
                <a href="index.php?page=register" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-md hover:bg-emerald-700 transition">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="h-16"></div>
