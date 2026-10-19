<?php
if (!is_logged_in() || (!is_admin() && !is_pic_organisasi($conn, $_SESSION['user_id']))) { 
    redirect('index.php?page=beranda'); 
}

$my_id = (int)$_SESSION['user_id'];
$is_super = is_admin();

// Cek Organisasi jika user adalah PIC
$my_org_id = null;
$nama_org = '';
if (!$is_super) {
    $q_org = mysqli_query($conn, "SELECT id, nama FROM organisasi WHERE pic_user_id = $my_id LIMIT 1");
    $data_org = mysqli_fetch_assoc($q_org);
    if($data_org) {
        $my_org_id = $data_org['id'];
        $nama_org = $data_org['nama'];
    }
}

// Ambil data (Admin melihat semua, PIC hanya milik org-nya)
$sql_info = $is_super 
    ? "SELECT r.*, o.nama as nama_org FROM ruang_informasi r LEFT JOIN organisasi o ON r.organisasi_id = o.id ORDER BY r.id DESC"
    : "SELECT r.*, o.nama as nama_org FROM ruang_informasi r LEFT JOIN organisasi o ON r.organisasi_id = o.id WHERE r.organisasi_id = $my_org_id ORDER BY r.id DESC";
$q_info = mysqli_query($conn, $sql_info);
?>

<div class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="megaphone" class="w-7 h-7 md:w-8 md:h-8 text-rose-600"></i> 
                <?= $is_super ? 'Ruang Informasi' : 'Broadcast Tim' ?>
            </h1>
            <p class="text-sm md:text-base text-slate-500 mt-1">
                <?= $is_super ? 'Kirim pengumuman ke seluruh pengguna platform.' : 'Kirim pengumuman khusus ke anggota tim <b>'.e($nama_org).'</b>.' ?>
            </p>
        </div>
        
        <?php if($is_super): ?>
        <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-xl border border-blue-200 text-xs font-bold flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4"></i> Mode: Super Admin (Global)
        </div>
        <?php else: ?>
        <div class="bg-purple-50 text-purple-700 px-4 py-2 rounded-xl border border-purple-200 text-xs font-bold flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4"></i> Mode: PIC Organisasi
        </div>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200 flex items-center gap-2">
            <i data-lucide="<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5"></i>
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Kolom Kiri: Daftar Informasi (Desktop: 7/12) -->
        <div class="lg:col-span-7 space-y-4">
            <h2 class="font-bold text-slate-800 mb-2 flex items-center gap-2"><i data-lucide="inbox" class="w-5 h-5"></i> Daftar Pengumuman</h2>
            
            <?php if(mysqli_num_rows($q_info) > 0): while($info = mysqli_fetch_assoc($q_info)): ?>
                <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 shadow-sm relative group flex flex-col sm:flex-row gap-4 hover:shadow-md transition">
                    
                    <!-- Gambar (Jika ada) -->
                    <?php if(!empty($info['gambar']) && file_exists('uploads/info/' . $info['gambar'])): ?>
                        <img src="uploads/info/<?= e($info['gambar']) ?>" class="w-full sm:w-32 h-48 sm:h-32 object-cover rounded-xl border border-slate-200 flex-shrink-0">
                    <?php endif; ?>

                    <div class="flex-1 flex flex-col">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-2 mb-2">
                            <h3 class="text-lg font-bold text-slate-800 leading-tight"><?= e($info['judul']) ?></h3>
                            
                            <!-- Badge Target -->
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md whitespace-nowrap
                                <?= $info['organisasi_id'] ? 'bg-purple-50 text-purple-700 border border-purple-100' : ($info['target_audiens'] == 'semua' ? 'bg-blue-50 text-blue-700 border border-blue-100' : ($info['target_audiens'] == 'individu' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100')) ?>">
                                <?= $info['organisasi_id'] ? 'Tim: '.$info['nama_org'] : 'Target: '.$info['target_audiens'] ?>
                            </span>
                        </div>
                        
                        <p class="text-sm text-slate-600 mb-3 line-clamp-2"><?= nl2br(e($info['konten'])) ?></p>
                        
                        <?php if($info['link']): ?>
                            <div class="text-xs text-blue-600 font-bold mb-3 flex items-center gap-1 truncate"><i data-lucide="link" class="w-3 h-3 flex-shrink-0"></i> <a href="<?= e($info['link']) ?>" target="_blank" class="hover:underline"><?= e($info['link']) ?></a></div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between items-center border-t border-slate-100 pt-3 mt-auto">
                            <span class="text-[10px] md:text-xs text-slate-400 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> <?= date('d M Y H:i', strtotime($info['created_at'])) ?></span>
                            <div class="flex gap-2">
                                <button onclick="editInfo(<?= htmlspecialchars(json_encode($info), ENT_QUOTES, 'UTF-8') ?>)" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition" title="Edit"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                <form method="POST" onsubmit="return confirm('Hapus informasi ini secara permanen?');" class="inline">
                                    <input type="hidden" name="aksi_hapus_info" value="1">
                                    <input type="hidden" name="id" value="<?= $info['id'] ?>">
                                    <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="text-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">Belum Ada Informasi</h3>
                    <p class="text-slate-500 text-sm mt-1">Gunakan form di samping untuk mengirim pengumuman pertama Anda.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Form Tambah/Edit (Desktop: 5/12) -->
        <div class="lg:col-span-5">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-100 shadow-lg sticky top-24">
                <h2 class="font-bold text-lg text-slate-800 mb-5 flex items-center gap-2" id="form_title">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-rose-600"></i> Buat Informasi Baru
                </h2>
                
                <form id="form_info" method="POST" action="index.php?page=dashboard_info" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="aksi_simpan_info" value="1">
                    <input type="hidden" name="id" id="info_id" value="0">
                    <input type="hidden" name="gambar_lama" id="info_gambar_lama" value="">
                    
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Judul Informasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" id="info_judul" required class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:outline-none font-semibold" placeholder="Cth: Pendaftaran Relawan Dibuka">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Isi Konten <span class="text-rose-500">*</span></label>
                        <textarea name="konten" id="info_konten" rows="4" required class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:outline-none text-sm" placeholder="Tuliskan detail pengumuman di sini..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Upload Gambar (Opsional)</label>
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-3 bg-slate-50 text-center hover:bg-slate-100 transition relative">
                            <input type="file" name="gambar" id="info_gambar" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="text-sm text-slate-500 font-semibold flex flex-col items-center gap-1">
                                <i data-lucide="image-plus" class="w-6 h-6 text-slate-400"></i>
                                <span id="label_gambar">Klik untuk pilih gambar</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Tautan URL (Opsional)</label>
                        <input type="url" name="link" id="info_link" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-rose-500 focus:outline-none text-sm" placeholder="https://...">
                    </div>
                    
                    <?php if($is_super): ?>
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Target Audiens</label>
                        <select name="target_audiens" id="info_target" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:border-rose-500 focus:outline-none font-semibold text-sm">
                            <option value="semua">Semua Pengguna</option>
                            <option value="individu">Hanya Individu</option>
                            <option value="institusi">Hanya Institusi</option>
                        </select>
                    </div>
                    <?php else: ?>
                        <!-- Jika PIC, kunci target ke organisasi sendiri -->
                        <input type="hidden" name="target_audiens" value="anggota">
                        <div class="bg-purple-50 text-purple-700 p-3 rounded-xl border border-purple-200 text-xs font-bold flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 mt-0.5 flex-shrink-0"></i> 
                            <p>Sebagai PIC, pesan ini otomatis hanya akan muncul di profil anggota tim <b><?= e($nama_org) ?></b>.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="flex-1 py-3 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition shadow-md">Simpan Informasi</button>
                        <button type="button" onclick="resetForm()" class="px-4 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Tampilkan nama file saat dipilih
document.getElementById('info_gambar').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Klik untuk pilih gambar';
    document.getElementById('label_gambar').innerText = fileName;
});

function editInfo(data) {
    document.getElementById('form_title').innerHTML = '<i data-lucide="edit-3" class="w-5 h-5 text-blue-600"></i> Edit Informasi';
    document.getElementById('info_id').value = data.id;
    document.getElementById('info_judul').value = data.judul;
    document.getElementById('info_konten').value = data.konten;
    document.getElementById('info_link').value = data.link || '';
    document.getElementById('info_gambar_lama').value = data.gambar || '';
    
    // Tampilkan nama gambar lama di label
    if (data.gambar) {
        document.getElementById('label_gambar').innerText = 'Gambar saat ini: ' + data.gambar + ' (Pilih baru untuk ganti)';
    } else {
        document.getElementById('label_gambar').innerText = 'Klik untuk pilih gambar';
    }

    if(document.getElementById('info_target')) {
        document.getElementById('info_target').value = data.target_audiens;
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
    lucide.createIcons();
}

function resetForm() {
    document.getElementById('form_title').innerHTML = '<i data-lucide="plus-circle" class="w-5 h-5 text-rose-600"></i> Buat Informasi Baru';
    document.getElementById('info_id').value = 0;
    document.getElementById('info_gambar_lama').value = '';
    document.getElementById('form_info').reset();
    document.getElementById('label_gambar').innerText = 'Klik untuk pilih gambar';
    lucide.createIcons();
}
</script>