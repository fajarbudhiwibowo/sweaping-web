<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$q_misi = mysqli_query($conn, "SELECT * FROM challenges ORDER BY is_aktif DESC, id DESC");
$total_misi = mysqli_num_rows($q_misi);

// Statistik Misi
$q_selesai = mysqli_query($conn, "SELECT COUNT(*) as c FROM user_challenges WHERE status = 'selesai'");
$total_selesai = mysqli_fetch_assoc($q_selesai)['c'];
?>

<div class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="gamepad-2" class="w-7 h-7 md:w-8 md:h-8 text-emerald-600"></i> Game Center (Misi)
            </h1>
            <p class="text-sm md:text-base text-slate-500 mt-1">Buat tantangan untuk memicu semangat pengguna dalam berkontribusi.</p>
        </div>
        
        <div class="flex gap-3 w-full md:w-auto">
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2 flex-1 md:flex-none text-center">
                <div class="text-[10px] font-bold text-emerald-600 uppercase">Total Misi</div>
                <div class="text-xl font-black text-emerald-800"><?= $total_misi ?></div>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2 flex-1 md:flex-none text-center">
                <div class="text-[10px] font-bold text-amber-600 uppercase">Misi Selesai (User)</div>
                <div class="text-xl font-black text-amber-800"><?= $total_selesai ?></div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200 flex items-center gap-2">
            <i data-lucide="<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5"></i>
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Kolom Kiri: Daftar Misi -->
        <div class="lg:col-span-7 space-y-4">
            <h2 class="font-bold text-slate-800 mb-2 flex items-center gap-2"><i data-lucide="list" class="w-5 h-5"></i> Daftar Misi</h2>
            
            <?php if($total_misi > 0): while($m = mysqli_fetch_assoc($q_misi)): ?>
                <div class="bg-white p-4 md:p-5 rounded-2xl border <?= $m['is_aktif'] ? 'border-slate-200' : 'border-rose-200 opacity-60' ?> shadow-sm hover:shadow-md transition flex flex-col gap-3">
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="target" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-base md:text-lg leading-tight"><?= e($m['judul']) ?></h3>
                                <div class="text-xs text-slate-500 mt-1 line-clamp-2"><?= e($m['deskripsi']) ?></div>
                            </div>
                        </div>
                        <div class="flex gap-1.5 flex-shrink-0">
                            <button onclick="editMisi(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)" class="p-1.5 md:p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                            <form method="POST" onsubmit="return confirm('Hapus misi ini?');" class="inline">
                                <input type="hidden" name="aksi_hapus_misi" value="1">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="p-1.5 md:p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 mt-2 pt-3 border-t border-slate-100">
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold uppercase tracking-wide flex items-center gap-1"><i data-lucide="activity" class="w-3 h-3"></i> <?= e($m['target_aksi']) ?></span>
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-100 rounded-md text-[10px] font-bold uppercase tracking-wide flex items-center gap-1"><i data-lucide="zap" class="w-3 h-3"></i> <?= number_format($m['hadiah_xp']) ?> XP</span>
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-md text-[10px] font-bold uppercase tracking-wide flex items-center gap-1"><i data-lucide="crosshair" class="w-3 h-3"></i> Target: <?= number_format($m['target_jumlah']) ?>x</span>
                        
                        <span class="ml-auto text-[10px] font-bold uppercase <?= $m['is_aktif'] ? 'text-emerald-600' : 'text-rose-500' ?>">
                            <?= $m['is_aktif'] ? '● Aktif' : '○ Nonaktif' ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="text-center py-12 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <i data-lucide="gamepad" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-700">Belum Ada Misi</h3>
                    <p class="text-slate-500 text-sm mt-1">Buat misi pertama Anda di form sebelah kanan.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Form Tambah/Edit -->
        <div class="lg:col-span-5">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200 shadow-lg sticky top-24">
                <h2 class="font-bold text-lg mb-5 text-indigo-700 flex items-center gap-2" id="form_title">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Buat Misi Baru
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="aksi_simpan_misi" value="1">
                    <input type="hidden" name="id" id="misi_id" value="0">
                    
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Judul Misi <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul" id="misi_judul" required class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none font-semibold" placeholder="Cth: Pahlawan Penghijauan">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Deskripsi Singkat <span class="text-rose-500">*</span></label>
                        <textarea name="deskripsi" id="misi_deskripsi" rows="2" required class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none text-sm" placeholder="Cth: Daftarkan 1 pohon baru untuk mendapatkan XP."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Aksi yang Dipantau (Target)</label>
                        <select name="target_aksi" id="misi_aksi" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:border-emerald-500 focus:outline-none text-sm font-semibold">
                            <option value="daftar_objek_pohon">Mendaftarkan Objek Pohon</option>
                            <option value="daftar_objek_sungai">Mendaftarkan Objek Sungai</option>
                            <option value="buat_laporan">Membuat Laporan Monitoring</option>
                            <option value="gambar_area">Menggambar Area Pantau (Poligon)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold mb-1 text-slate-600">Target Jumlah</label>
                            <input type="number" name="target_jumlah" id="misi_target" required min="1" value="1" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none font-bold text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 text-slate-600">Hadiah XP</label>
                            <input type="number" name="hadiah_xp" id="misi_xp" required min="10" value="50" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-amber-500 focus:outline-none font-bold text-center text-amber-600">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <input type="checkbox" name="is_aktif" id="misi_aktif" checked class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500">
                            <span class="text-sm font-bold text-slate-700">Aktifkan Misi Ini</span>
                        </label>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100 flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 text-white font-bold py-3 rounded-xl hover:bg-indigo-700 transition shadow-md">Simpan Misi</button>
                        <button type="button" onclick="resetForm()" class="px-4 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editMisi(data) {
    document.getElementById('form_title').innerHTML = '<i data-lucide="edit-3" class="w-5 h-5 text-blue-600"></i> Edit Misi';
    document.getElementById('misi_id').value = data.id;
    document.getElementById('misi_judul').value = data.judul;
    document.getElementById('misi_deskripsi').value = data.deskripsi;
    document.getElementById('misi_aksi').value = data.target_aksi;
    document.getElementById('misi_target').value = data.target_jumlah;
    document.getElementById('misi_xp').value = data.hadiah_xp;
    document.getElementById('misi_aktif').checked = data.is_aktif == 1;
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
    lucide.createIcons();
}

function resetForm() {
    document.getElementById('form_title').innerHTML = '<i data-lucide="plus-circle" class="w-5 h-5 text-indigo-700"></i> Buat Misi Baru';
    document.getElementById('misi_id').value = 0;
    document.querySelector('form').reset();
    document.getElementById('misi_target').value = 1;
    document.getElementById('misi_xp').value = 50;
    document.getElementById('misi_aktif').checked = true;
    lucide.createIcons();
}
</script>