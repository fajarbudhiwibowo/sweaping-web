<?php
if (!is_logged_in() || !is_admin()) { 
    $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Akses ditolak. Halaman khusus Admin.'];
    redirect('index.php?page=beranda'); 
}

// Ambil Statistik
$total_objek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM objek WHERE deleted_at IS NULL"))['total'];
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_aksi WHERE deleted_at IS NULL"))['total'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_area = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM area_pantau WHERE deleted_at IS NULL"))['total'];
?>

<main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
    
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="layout-dashboard" class="w-6 h-6 text-emerald-600"></i> Dashboard Admin
            </h1>
            <p class="text-slate-500 text-sm mt-1">Pusat kendali aplikasi Sweaping.</p>
        </div>
        <!-- Tombol Backup -->
        <button onclick="alert('Fitur backup akan diimplementasikan di tahap selanjutnya.')" class="bg-slate-800 text-white font-bold py-2 px-4 rounded-xl hover:bg-slate-900 transition text-sm flex items-center gap-2">
            <i data-lucide="hard-drive-download" class="w-4 h-4"></i> Backup ke Telegram
        </button>
    </div>

    <!-- Kartu Statistik -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase mb-1">Total Objek</div>
            <div class="text-3xl font-extrabold text-slate-800"><?= $total_objek ?></div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase mb-1">Total Laporan</div>
            <div class="text-3xl font-extrabold text-emerald-600"><?= $total_laporan ?></div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase mb-1">Total Area</div>
            <div class="text-3xl font-extrabold text-blue-600"><?= $total_area ?></div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase mb-1">Pengguna</div>
            <div class="text-3xl font-extrabold text-purple-600"><?= $total_user ?></div>
        </div>
    </div>

    <!-- Menu Navigasi Cepat -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- CMS / Page Builder -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-4">
                <i data-lucide="layout-template" class="w-6 h-6"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 mb-2">Kelola Beranda (Page Builder)</h2>
            <p class="text-slate-500 text-sm mb-4">Atur blok, teks, dan gambar di halaman depan tanpa coding.</p>
            <a href="index.php?page=dashboard_cms" class="inline-block bg-emerald-50 text-emerald-700 font-bold py-2 px-4 rounded-lg hover:bg-emerald-100 transition text-sm">Buka Page Builder</a>
        </div>

        <!-- Manajemen User -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-4">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 mb-2">Manajemen Pengguna</h2>
            <p class="text-slate-500 text-sm mb-4">Kelola hak akses, aktivis, dan admin sistem.</p>
            <a href="index.php?page=kelola_user" class="inline-block bg-blue-50 text-blue-700 font-bold py-2 px-4 rounded-lg hover:bg-blue-100 transition text-sm">Kelola User</a>
        </div>
    </div>

</main>