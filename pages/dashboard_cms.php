<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$q_blok = mysqli_query($conn, "SELECT * FROM home_sections ORDER BY urutan ASC");
$blok_list = [];
while($b = mysqli_fetch_assoc($q_blok)) $blok_list[] = $b;
?>

<main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Page Builder (Beranda)</h1>
            <p class="text-sm text-slate-500">Atur susunan blok di halaman depan Sweaping.</p>
        </div>
        <a href="index.php?page=dashboard" class="text-sm font-bold text-slate-600 hover:text-emerald-600">← Kembali ke Dashboard</a>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold border border-emerald-200"><?= $_SESSION['notif']['pesan'] ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Daftar Blok -->
        <div class="lg:col-span-2 space-y-3">
            <?php if(empty($blok_list)): ?>
                <div class="text-center p-8 bg-white border border-slate-200 rounded-xl text-slate-500">
                    Belum ada blok. Silakan tambah blok baru di samping kanan.
                </div>
            <?php endif; ?>

            <?php foreach($blok_list as $b): ?>
            <div class="bg-white p-4 rounded-xl border border-slate-200 flex justify-between items-center shadow-sm <?= $b['aktif'] ? '' : 'opacity-50' ?>">
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase">Urutan <?= $b['urutan'] ?> — <?= $b['tipe'] ?></div>
                    <div class="text-lg font-bold text-slate-800"><?= e($b['judul'] ?: 'Tanpa Judul') ?></div>
                    <div class="text-sm text-slate-500"><?= e(substr($b['konten'], 0, 50)) ?>...</div>
                </div>
                <div class="flex gap-2">
                    <!-- Tombol Edit dengan atribut data yang aman -->
                    <button type="button" data-blok="<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>" onclick="editBlok(this)" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" title="Edit">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    
                    <!-- Tombol Hapus -->
                    <form method="POST" onsubmit="return confirm('Hapus blok ini?');">
                        <input type="hidden" name="aksi_hapus_blok" value="1">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Kolom Kanan: Form Tambah/Edit Blok -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-lg h-fit sticky top-24">
            <h2 class="font-bold text-lg mb-4 text-emerald-700" id="form_title">Tambah Blok Baru</h2>
            
            <form method="POST" action="index.php" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="aksi_simpan_blok" value="1">
                <input type="hidden" name="id" id="f_id" value="0">
                <input type="hidden" name="gambar_lama" id="f_gambar_lama" value="">

                <div>
                    <label class="block text-xs font-bold mb-1">Tipe Blok</label>
                    <select name="tipe" id="f_tipe" class="w-full border-2 border-slate-200 p-2 rounded-lg" required>
                        <option value="hero">Hero (Banner Utama)</option>
                        <option value="stats">Statistik (Glassmorphism)</option>
                        <option value="challenges">Eco-Challenges (Gamifikasi)</option>
                        <option value="live_feed">Live Feed (Aktivitas Terbaru)</option>
                        <option value="leaderboard">Top Leaderboard (Bento Grid)</option>
                        <option value="text_image">Teks & Gambar</option>
                        <option value="carousel">Carousel (Slider Gambar)</option>
                    </select>
                </div>

                <div id="box_upload_single">
                    <label class="block text-xs font-bold mb-1">Gambar Utama (Single)</label>
                    <input type="file" name="gambar" id="input_gambar_single" class="w-full text-sm">
                </div>
                <div id="box_upload_multiple" class="hidden">
                    <label class="block text-xs font-bold mb-1">Pilih Banyak Gambar (Carousel)</label>
                    <input type="file" name="gambar_carousel[]" id="input_gambar_carousel" multiple accept="image/*" class="w-full text-sm border-2 border-dashed border-emerald-300 p-2 rounded-lg bg-emerald-50">
                </div>

                <div>
                    <label class="block text-xs font-bold mb-1">Judul Utama</label>
                    <input type="text" name="judul" id="f_judul" class="w-full border-2 border-slate-200 p-2 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Subjudul / Teks Kecil</label>
                    <input type="text" name="subjudul" id="f_subjudul" class="w-full border-2 border-slate-200 p-2 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Isi Konten / Deskripsi</label>
                    <textarea name="konten" id="f_konten" rows="3" class="w-full border-2 border-slate-200 p-2 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Warna Background (Opsional)</label>
                    <input type="color" name="warna_bg" id="f_warna_bg" class="w-full h-10 border-2 border-slate-200 rounded-lg cursor-pointer">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold mb-1">Urutan</label>
                        <input type="number" name="urutan" id="f_urutan" class="w-full border-2 border-slate-200 p-2 rounded-lg" value="1">
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="aktif" id="f_aktif" checked class="w-4 h-4 text-emerald-600 rounded">
                            <span class="text-sm font-bold">Tampilkan</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 transition">Simpan Blok</button>
                <button type="button" onclick="resetForm()" class="w-full bg-slate-100 text-slate-600 font-bold py-2 rounded-xl hover:bg-slate-200 transition text-sm">Batal Edit</button>
            </form>
        </div>
    </div>
</main>

<script>
// FUNGSI EDIT YANG LEBIH AMAN
function editBlok(btn) {
    // Ambil data JSON dari atribut
    var jsonStr = btn.getAttribute('data-blok');
    
    // Trik untuk mengubah teks HTML entity (&quot;) kembali menjadi JSON valid
    var txt = document.createElement('textarea');
    txt.innerHTML = jsonStr;
    
    try {
        var data = JSON.parse(txt.value);
        
        document.getElementById('form_title').innerText = 'Edit Blok';
        document.getElementById('f_id').value = data.id;
        document.getElementById('f_tipe').value = data.tipe;
        document.getElementById('f_judul').value = data.judul || '';
        document.getElementById('f_subjudul').value = data.subjudul || '';
        document.getElementById('f_konten').value = data.konten || '';
        document.getElementById('f_warna_bg').value = data.warna_bg || '#ffffff';
        document.getElementById('f_urutan').value = data.urutan;
        document.getElementById('f_aktif').checked = data.aktif == 1;
        document.getElementById('f_gambar_lama').value = data.gambar || '';
        
        // Panggil event change untuk menyesuaikan tampilan upload
        document.getElementById('f_tipe').dispatchEvent(new Event('change'));
        
        // Scroll ke form
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        console.error("Gagal membaca data blok:", error);
        alert("Terjadi kesalahan saat memuat data. Silakan refresh halaman.");
    }
}

function resetForm() {
    document.getElementById('form_title').innerText = 'Tambah Blok Baru';
    document.getElementById('f_id').value = 0;
    document.getElementById('f_gambar_lama').value = '';
    document.querySelector('form').reset();
    document.getElementById('f_tipe').dispatchEvent(new Event('change'));
}

// Event listener untuk upload gambar
document.getElementById('f_tipe').addEventListener('change', function() {
    if (this.value === 'carousel') {
        document.getElementById('box_upload_single').classList.add('hidden');
        document.getElementById('box_upload_multiple').classList.remove('hidden');
    } else {
        document.getElementById('box_upload_single').classList.remove('hidden');
        document.getElementById('box_upload_multiple').classList.add('hidden');
    }
});
</script>