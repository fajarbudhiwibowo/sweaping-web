<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Pastikan koneksi database dan fungsi sudah dipanggil di index.php

// Ambil XP User
$user_xp_header = 0;
if (is_logged_in()) {
    $stmt_xp_h = mysqli_prepare($conn, "SELECT eco_points FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_xp_h, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_xp_h);
    $res_xp_h = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_xp_h));
    $user_xp_header = $res_xp_h ? (int)$res_xp_h['eco_points'] : 0;
    mysqli_stmt_close($stmt_xp_h);
}

// Ambil Notifikasi
$notif_count = 0;
if (is_logged_in()) {
    $stmt_n = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM follows WHERE following_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt_n, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_n);
    $notif_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_n))['c'];
    mysqli_stmt_close($stmt_n);
}

// Deteksi Area Admin
$admin_pages = ['dashboard', 'dashboard_cms', 'dashboard_info', 'dashboard_theme', 'kelola_user', 'kelola_organisasi', 'dev_hub', 'kelola_game'];
$current_page = $_GET['page'] ?? 'beranda';
$is_admin_area = is_logged_in() && is_admin() && in_array($current_page, $admin_pages);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SWEAPING v6</title>
    <link rel="icon" type="image/png" href="/assets/logo-bulat.png">
    
    <!-- Tailwind CSS (WAJIB ADA) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; padding-bottom: 80px; }
        @media (min-width: 768px) { body { padding-bottom: 0; } }
        .header-logo { height: 40px; width: auto; object-fit: contain; }
        @media (min-width: 768px) { .header-logo { height: 48px; } }
        
        /* Custom Scrollbar untuk Sidebar */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden">

<?php if ($is_admin_area): ?>
    <!-- ========================================== -->
    <!-- LAYOUT ADMIN (SIDEBAR + TOPBAR)            -->
    <!-- ========================================== -->
    
    <!-- Overlay Mobile -->
    <div id="admin_overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity" onclick="toggleAdminSidebar()"></div>

    <!-- Sidebar Desktop & Mobile Drawer -->
    <aside id="admin_sidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-white z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 flex flex-col shadow-2xl">
        <div class="h-16 flex items-center justify-between px-6 border-b border-white/10 flex-shrink-0">
            <a href="index.php?page=beranda" class="flex items-center gap-2">
                <img src="/assets/logo-bulat.png" alt="Logo" class="h-8">
                <span class="font-extrabold text-lg tracking-wider">SWEAPING</span>
            </a>
            <button onclick="toggleAdminSidebar()" class="md:hidden text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 hide-scrollbar">
            <div class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 mt-2">Utama</div>
            <a href="index.php?page=dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'dashboard' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> <span class="font-bold text-sm">Command Center</span>
            </a>
            
            <div class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 mt-6">Konten & Tampilan</div>
            <a href="index.php?page=dashboard_cms" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'dashboard_cms' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="layout-template" class="w-5 h-5"></i> <span class="font-bold text-sm">Page Builder</span>
            </a>
            <a href="index.php?page=dashboard_info" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'dashboard_info' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="megaphone" class="w-5 h-5"></i> <span class="font-bold text-sm">Ruang Informasi</span>
            </a>
            <a href="index.php?page=dashboard_theme" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'dashboard_theme' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="palette" class="w-5 h-5"></i> <span class="font-bold text-sm">Tema & Desain</span>
            </a>

            <div class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 mt-6">Data & Gamifikasi</div>
            <a href="index.php?page=kelola_user" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'kelola_user' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="users" class="w-5 h-5"></i> <span class="font-bold text-sm">Pengguna</span>
            </a>
            <a href="index.php?page=kelola_organisasi" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'kelola_organisasi' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
                <i data-lucide="building-2" class="w-5 h-5"></i> <span class="font-bold text-sm">Organisasi</span>
            <a href="index.php?page=kelola_game" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'kelola_game' ? 'bg-amber-500 text-white shadow-md' : 'text-amber-400 hover:bg-white/10' ?>">
                <i data-lucide="gamepad-2" class="w-5 h-5"></i> <span class="font-bold text-sm">Game Center</span>
            </a>

            <!-- TAMBAHKAN INI: MENU DEV HUB -->
            <?php if(is_super_admin()): ?>
            <div class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 mt-6">Sistem & Developer</div>
            <a href="index.php?page=dev_hub" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $current_page == 'dev_hub' ? 'bg-rose-600 text-white shadow-md' : 'text-rose-400 hover:bg-white/10' ?>">
                <i data-lucide="terminal" class="w-5 h-5"></i> <span class="font-bold text-sm">Developer Hub</span>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="p-4 border-t border-white/10 flex-shrink-0">
            <a href="index.php?page=logout" class="flex items-center justify-center gap-2 w-full py-2.5 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 rounded-xl transition text-sm font-bold">
                <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- Area Konten Admin -->
    <div class="md:ml-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 md:px-6 sticky top-0 z-30">
                        <!-- MENU KANAN (User Avatar & Dropdown) -->
            <div class="flex items-center gap-3">
                <?php if(is_logged_in()): ?>
                    
                    <!-- Dropdown Container (Hover di Desktop, Klik di Mobile) -->
                    <div class="relative group">
                        <button type="button" class="flex items-center gap-2 focus:outline-none">
                            <div class="text-right hidden md:block">
                                <div class="text-sm font-bold text-slate-800 flex items-center justify-end">
                                    <?= e($_SESSION['nama'] ?? 'User') ?> 
                                </div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wide font-semibold">
                                    <?= e($_SESSION['role'] ?? 'user') ?>
                                </div>
                            </div>
                            
                            <!-- Avatar Circle -->
                            <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold shadow-md hover:ring-4 hover:ring-emerald-100 transition-all">
                                <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden md:block group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>
                        
                        <!-- Isi Dropdown -->
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 transform origin-top-right group-hover:scale-100 scale-95">
                            
                            <!-- Info User -->
                            <div class="px-4 py-3 border-b border-slate-100 mb-1">
                                <div class="text-sm font-bold text-slate-800 truncate"><?= e($_SESSION['nama'] ?? 'User') ?></div>
                                <div class="text-xs text-emerald-600 font-bold"><?= e($_SESSION['email'] ?? '') ?></div>
                            </div>

                                                        <!-- Link ke Profil -->
                            <a href="index.php?page=user_profil" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 font-semibold transition">
                                <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                            </a>

                            <!-- Link ke Dashboard Admin (HANYA MUNCUL JIKA ROLE ADMIN/SUPER_ADMIN) -->
                            <?php if(is_admin()): ?>
                            <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-2.5 text-sm text-indigo-600 bg-indigo-50/50 hover:bg-indigo-50 font-bold transition border-l-4 border-indigo-500">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard Admin
                            </a>
                            <?php endif; ?>

                            <!-- TAMBAHKAN INI: Link Broadcast Tim (HANYA MUNCUL JIKA USER ADALAH PIC ORGANISASI) -->
                            <?php if(!is_admin() && is_pic_organisasi($conn, $_SESSION['user_id'])): ?>
                            <a href="index.php?page=dashboard_info" class="flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 bg-rose-50/50 hover:bg-rose-50 font-bold transition border-l-4 border-rose-500">
                                <i data-lucide="megaphone" class="w-4 h-4"></i> Broadcast Tim
                            </a>
                            <?php endif; ?>

                            <!-- Link Tambahan -->
                            <a href="index.php?page=misi_saya" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 font-semibold transition">
                                <i data-lucide="target" class="w-4 h-4"></i> Misi Saya
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <!-- Link Logout -->
                            <a href="index.php?logout=true" class="flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-bold transition">
                                <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="index.php?page=login" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-xl text-sm transition shadow-md flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i> Masuk
                    </a>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="flex-1 bg-slate-50">
            <!-- KONTEN DIMULAI DI SINI (Oleh file halaman) -->

<?php else: ?>
        <!-- ========================================== -->
    <!-- LAYOUT USER BIASA (HEADER ATAS)            -->
    <!-- ========================================== -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 fixed top-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            
            <!-- Logo -->
            <a href="index.php?page=beranda" class="flex items-center flex-shrink-0">
                <img src="/assets/logo-horizontal.png" alt="SWEAPING" class="header-logo mix-blend-multiply">
            </a>

            <!-- Menu Tengah (Navigasi) -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="index.php?page=beranda" class="text-sm font-bold text-slate-600 hover:text-emerald-600">Beranda</a>
                <a href="index.php?page=objek" class="text-sm font-bold text-slate-600 hover:text-emerald-600">Objek</a>
                <a href="index.php?page=arsip" class="text-sm font-bold text-slate-600 hover:text-emerald-600">Arsip</a>
            </nav>

            <!-- MENU KANAN (Avatar & Dropdown) -->
            <div class="flex items-center gap-3">
                <?php if(is_logged_in()): ?>
                    
                    <!-- Wrapper Dropdown (relative group) -->
                    <div class="relative group">
                        
                        <!-- Tombol Avatar -->
                        <button type="button" class="flex items-center gap-2 focus:outline-none">
                            <!-- Nama & Role (Hanya Desktop) -->
                            <div class="text-right hidden md:block">
                                <div class="text-sm font-bold text-slate-800 leading-tight">
                                    <?= e($_SESSION['nama'] ?? 'User') ?>
                                </div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wide font-semibold">
                                    <?= e($_SESSION['role'] ?? 'user') ?>
                                </div>
                            </div>
                            
                            <!-- Avatar Circle -->
                            <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold shadow-md hover:ring-4 hover:ring-emerald-100 transition-all">
                                <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                            </div>
                            
                            <!-- Ikon Panah -->
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden md:block group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>

                        <!-- Isi Dropdown Menu (z-50 agar di atas segalanya) -->
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-[9999] transform origin-top-right group-hover:scale-100 scale-95">
                            
                            <!-- Info User -->
                            <div class="px-4 py-3 border-b border-slate-100 mb-1">
                                <div class="text-sm font-bold text-slate-800 truncate"><?= e($_SESSION['nama'] ?? 'User') ?></div>
                                <div class="text-xs text-emerald-600 font-bold"><?= e($_SESSION['email'] ?? '') ?></div>
                            </div>

                            <!-- Link ke Dashboard Admin (HANYA MUNCUL JIKA ADMIN) -->
                                                        <!-- Link ke Profil -->
                            <a href="index.php?page=user_profil" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 font-semibold transition">
                                <i data-lucide="user" class="w-4 h-4"></i> Profil Saya
                            </a>

                            <!-- Link ke Dashboard Admin (HANYA MUNCUL JIKA ROLE ADMIN/SUPER_ADMIN) -->
                            <?php if(is_admin()): ?>
                            <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-2.5 text-sm text-indigo-600 bg-indigo-50/50 hover:bg-indigo-50 font-bold transition border-l-4 border-indigo-500">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard Admin
                            </a>
                            <?php endif; ?>

                            <!-- TAMBAHKAN INI: Link Broadcast Tim (HANYA MUNCUL JIKA USER ADALAH PIC ORGANISASI) -->
                            <?php if(!is_admin() && is_pic_organisasi($conn, $_SESSION['user_id'])): ?>
                            <a href="index.php?page=dashboard_info" class="flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 bg-rose-50/50 hover:bg-rose-50 font-bold transition border-l-4 border-rose-500">
                                <i data-lucide="megaphone" class="w-4 h-4"></i> Broadcast Tim
                            </a>
                            <?php endif; ?>

                            <!-- Link Tambahan -->
                            <a href="index.php?page=misi_saya" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 font-semibold transition">
                                <i data-lucide="target" class="w-4 h-4"></i> Misi Saya
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <!-- Link Logout -->
                            <a href="index.php?logout=true" class="flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-bold transition">
                                <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Tombol Masuk (Jika Belum Login) -->
                    <a href="index.php?page=login" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-xl text-sm transition shadow-md flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i> Masuk
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </header>
    <div class="h-16"></div>
    <!-- KONTEN USER DIMULAI DI SINI -->
<?php endif; ?>