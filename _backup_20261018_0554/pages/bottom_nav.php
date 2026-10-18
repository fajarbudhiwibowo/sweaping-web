<?php
$current_page = $_GET['page'] ?? 'beranda';
?>
<nav class="md:hidden fixed bottom-0 w-full bg-white border-t border-slate-200 z-50 pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
    <div class="flex justify-around items-center h-16">
        <a href="index.php?page=beranda" class="flex flex-col items-center gap-1 <?= $current_page == 'beranda' ? 'text-emerald-600' : 'text-slate-400' ?> hover:text-emerald-600 transition">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span class="text-[10px] font-bold">Beranda</span>
        </a>
        <a href="index.php?page=objek" class="flex flex-col items-center gap-1 <?= in_array($current_page, ['objek', 'objek_profil', 'objek_daftar']) ? 'text-emerald-600' : 'text-slate-400' ?> hover:text-emerald-600 transition">
            <i data-lucide="package" class="w-5 h-5"></i>
            <span class="text-[10px] font-bold">Objek</span>
        </a>
        
        <!-- Tombol Tambah / Lapor -->
        <a href="index.php?page=lapor" class="flex items-center justify-center w-14 h-14 bg-emerald-600 text-white rounded-full shadow-lg -mt-8 border-4 border-white hover:bg-emerald-700 transition">
            <i data-lucide="plus" class="w-6 h-6"></i>
        </a>
        
        <a href="index.php?page=peta" class="flex flex-col items-center gap-1 <?= $current_page == 'peta' ? 'text-emerald-600' : 'text-slate-400' ?> hover:text-emerald-600 transition">
            <i data-lucide="map" class="w-5 h-5"></i>
            <span class="text-[10px] font-bold">Peta</span>
        </a>
        <a href="index.php?page=arsip" class="flex flex-col items-center gap-1 <?= $current_page == 'arsip' ? 'text-emerald-600' : 'text-slate-400' ?> hover:text-emerald-600 transition">
            <i data-lucide="folder" class="w-5 h-5"></i>
            <span class="text-[10px] font-bold">Arsip</span>
        </a>
    </div>
</nav>
