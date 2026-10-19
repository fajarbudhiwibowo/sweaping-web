<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
$uid = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$org = null;
if ($user['tipe_akun'] === 'institusi') {
    $stmt_org = mysqli_prepare($conn, "SELECT * FROM organisasi WHERE pic_user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_org, 'i', $uid);
    mysqli_stmt_execute($stmt_org);
    $org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_org));
    mysqli_stmt_close($stmt_org);
}
?>

<main class="max-w-2xl mx-auto px-4 pt-28 pb-16">
    <div class="mb-6">
        <a href="index.php?page=user_profil" class="text-sm font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Profil
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900">Edit Profil</h1>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200">
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

       <form action="index.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-5">
        <input type="hidden" name="aksi_update_profil" value="1">
        <input type="hidden" name="tipe_akun" value="<?= e($user['tipe_akun']) ?>">
        
                <!-- Upload Cover Photo -->
        <div class="mb-6">
            <label class="block text-sm font-bold text-slate-700 mb-2">Foto Sampul (Cover)</label>
            <input type="file" name="cover_foto" accept="image/*" class="w-full text-sm border-2 border-dashed border-slate-300 p-2 rounded-xl bg-slate-50">
            <p class="text-[10px] text-slate-400 mt-1">Rekomendasi ukuran 1200x400px. Maks 3MB.</p>
        </div>
        
        <!-- Upload Avatar / Logo -->
        <div class="flex flex-col items-center pb-6 border-b border-slate-100">
            <div class="w-24 h-24 rounded-full border-4 border-slate-200 overflow-hidden bg-slate-100 flex items-center justify-center mb-4 relative group">
                <?php if(!empty($user['avatar']) && file_exists('uploads/avatar/' . $user['avatar'])): ?>
                    <img src="uploads/avatar/<?= e($user['avatar']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <i data-lucide="image" class="w-8 h-8 text-slate-400"></i>
                <?php endif; ?>
            </div>
            <label class="cursor-pointer bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-xl text-sm transition">
                <i data-lucide="upload" class="w-4 h-4 inline mr-1"></i> 
                <?= $user['tipe_akun'] === 'institusi' ? 'Ganti Logo Lembaga' : 'Ganti Foto Profil' ?>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
            </label>
            <p class="text-[10px] text-slate-400 mt-2">Maks 2MB (JPG/PNG)</p>
        </div>

        <!-- Informasi Dasar -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- NAMA LENGKAP / NAMA LEMBAGA -->
            <div class="<?= $user['tipe_akun'] === 'institusi' ? 'md:col-span-2' : '' ?>">
                <label class="block text-sm font-bold text-slate-700 mb-1">
                    <?= $user['tipe_akun'] === 'institusi' ? 'Nama Panjang Lembaga' : 'Nama Lengkap' ?>
                </label>
                <input type="text" name="nama_lengkap" required 
                       value="<?= e($user['tipe_akun'] === 'institusi' ? ($org['nama'] ?? '') : $user['nama_lengkap']) ?>" 
                       class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
            </div>

            <!-- NAMA SINGKAT (KHUSUS INSTITUSI) -->
            <?php if($user['tipe_akun'] === 'institusi'): ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Nama Singkat Lembaga</label>
                <input type="text" name="nama_pendek" value="<?= e($org['nama_pendek'] ?? '') ?>" 
                       placeholder="Cth: LSM KOMPAS" 
                       class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
            </div>
            <?php endif; ?>

            <!-- EMAIL (READONLY) -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Email (Tidak dapat diubah)</label>
                <input type="email" readonly value="<?= e($user['email']) ?>" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed">
            </div>
            
            <!-- TELEPON -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                <input type="tel" name="telepon" value="<?= e($user['telepon']) ?>" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
            </div>
            
            <!-- ALAMAT -->
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Alamat / Wilayah</label>
                <input type="text" name="organisasi" value="<?= e($user['organisasi']) ?>" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
            </div>
        </div>

        <div>
    <label class="block text-sm font-bold text-slate-700 mb-1">Bio Singkat (Tentang Anda)</label>
    <textarea name="bio_singkat" rows="3" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none"><?= e($user['bio_singkat']) ?></textarea>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-bold text-slate-700 mb-1">Instagram (Username)</label>
        <input type="text" name="sosmed_instagram" value="<?= e($user['sosmed_instagram']) ?>" placeholder="tanpa @" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-sm font-bold text-slate-700 mb-1">LinkedIn (URL)</label>
        <input type="url" name="sosmed_linkedin" value="<?= e($user['sosmed_linkedin']) ?>" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
    </div>
    <div>
        <label class="block text-sm font-bold text-slate-700 mb-1">Website (URL)</label>
        <input type="url" name="sosmed_website" value="<?= e($user['sosmed_website']) ?>" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
    </div>
</div>

  <button type="submit" class="w-full py-3.5 bg-emerald-700 text-white font-bold text-lg rounded-xl hover:bg-emerald-800 shadow-lg transition mt-4">Simpan Perubahan Profil</button>
      
    </form>
</main>