<?php
if (!is_logged_in() || !is_admin()) { redirect('index.php?page=beranda'); }

$q_blok = mysqli_query($conn, "SELECT * FROM home_sections ORDER BY urutan ASC");
$blok_list = [];
while($b = mysqli_fetch_assoc($q_blok)) $blok_list[] = $b;

// Helper untuk Ikon Blok
function get_block_icon($tipe) {
    $icons = [
        'hero' => 'star',
        'stats' => 'bar-chart-3',
        'challenges' => 'target',
        'live_feed' => 'activity',
        'leaderboard' => 'crown',
        'text_image' => 'image',
        'carousel' => 'gallery-horizontal',
        'video' => 'youtube',
        'faq' => 'help-circle',
        'testimonial' => 'message-square-quote',
        'cta' => 'megaphone'
    ];
    return $icons[$tipe] ?? 'box';
}
?>

<!-- Wrapper Konten (Karena sidebar dan header sudah diurus oleh header.php) -->
<div class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="layout-template" class="w-7 h-7 text-indigo-600"></i> Super Page Builder
            </h1>
            <p class="text-sm text-slate-500 mt-1">Atur susunan blok, warna, dan konten di halaman beranda.</p>
        </div>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200">
            <?= $_SESSION['notif']['pesan'] ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Kolom Kiri: Daftar Blok (Desktop: 7/12, Mobile: Full) -->
        <div class="lg:col-span-7 space-y-3">
            <h2 class="font-bold text-slate-800 mb-2 flex items-center gap-2"><i data-lucide="list" class="w-5 h-5"></i> Susunan Beranda</h2>
            
            <?php if(empty($blok_list)): ?>
                <div class="text-center p-8 bg-white border border-slate-200 rounded-2xl text-slate-500">
                    Belum ada blok. Silakan tambah blok baru di form sebelah kanan.
                </div>
            <?php endif; ?>

            <?php foreach($blok_list as $b): ?>
            <div class="bg-white p-4 rounded-2xl border <?= $b['aktif'] ? 'border-slate-200' : 'border-rose-200 opacity-60' ?> flex flex-col sm:flex-row justify-between items-start sm:items-center shadow-sm gap-4 transition hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="<?= get_block_icon($b['tipe']) ?>" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            Urutan <?= $b['urutan'] ?> • <?= $b['tipe'] ?> 
                            <?= !$b['aktif'] ? '<span class="text-rose-500 ml-1">(Disembunyikan)</span>' : '' ?>
                        </div>
                        <div class="text-base font-bold text-slate-800 leading-tight"><?= e($b['judul'] ?: 'Tanpa Judul') ?></div>
                        <div class="text-xs text-slate-500 line-clamp-1 mt-1"><?= e(substr(strip_tags($b['konten']), 0, 60)) ?>...</div>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto justify-end">
                    <button type="button" data-blok="<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>" onclick="editBlok(this)" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition" title="Edit">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <form method="POST" onsubmit="return confirm('Hapus blok ini secara permanen?');" class="inline">
                        <input type="hidden" name="aksi_hapus_blok" value="1">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                        <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Kolom Kanan: Form Tambah/Edit Blok (Desktop: 5/12, Mobile: Full) -->
        <div class="lg:col-span-5">
            <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200 shadow-lg sticky top-24">
                <h2 class="font-bold text-lg mb-5 text-indigo-700 flex items-center gap-2" id="form_title">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i> Tambah Blok Baru
                </h2>
                
                <form method="POST" action="index.php" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="aksi_simpan_blok" value="1">
                    <input type="hidden" name="id" id="f_id" value="0">
                    <input type="hidden" name="gambar_lama" id="f_gambar_lama" value="">

                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Pilih Tipe Widget</label>
                        <select name="tipe" id="f_tipe" class="w-full border-2 border-slate-200 p-2.5 rounded-xl font-semibold text-slate-700 bg-slate-50 focus:border-indigo-500 focus:outline-none" required>
                            <option value="hero">Hero (Banner Utama)</option>
                            <option value="stats">Statistik (Angka Berjalan)</option>
                            <option value="challenges">Eco-Challenges (Gamifikasi)</option>
                            <option value="live_feed">Live Feed (Aktivitas User)</option>
                            <option value="leaderboard">Top Leaderboard (Peringkat)</option>
                            <option value="text_image">Teks & Gambar (Kiri/Kanan)</option>
                            <option value="carousel">Carousel (Slider Gambar)</option>
                            <option value="video">Video Embed (YouTube)</option>
                            <option value="faq">FAQ (Tanya Jawab)</option>
                            <option value="testimonial">Testimoni Pengguna</option>
                            <option value="cta">CTA Banner (Ajakan Bertindak)</option>
                        </select>
                    </div>

                    <!-- Dynamic Helper Text -->
                    <div id="helper_text" class="bg-blue-50 text-blue-800 text-xs p-3 rounded-lg border border-blue-100 hidden leading-relaxed"></div>

                    <div id="box_upload_single">
                        <label class="block text-xs font-bold mb-1 text-slate-600">Gambar Utama (Background/Foto)</label>
                        <input type="file" name="gambar" id="input_gambar_single" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <div id="box_upload_multiple" class="hidden">
                        <label class="block text-xs font-bold mb-1 text-slate-600">Pilih Banyak Gambar (Carousel)</label>
                        <input type="file" name="gambar_carousel[]" id="input_gambar_carousel" multiple accept="image/*" class="w-full text-sm border-2 border-dashed border-indigo-300 p-3 rounded-xl bg-indigo-50">
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Judul Utama Blok</label>
                        <input type="text" name="judul" id="f_judul" class="w-full border-2 border-slate-200 p-2.5 rounded-xl focus:border-indigo-500 focus:outline-none" placeholder="Cth: Hulu Jaga Hilir..." required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Subjudul / Teks Kecil</label>
                        <input type="text" name="subjudul" id="f_subjudul" class="w-full border-2 border-slate-200 p-2.5 rounded-xl focus:border-indigo-500 focus:outline-none" placeholder="Cth: Merawat Alam Indonesia">
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600" id="label_konten">Isi Konten / Deskripsi</label>
                        <textarea name="konten" id="f_konten" rows="4" class="w-full border-2 border-slate-200 p-2.5 rounded-xl focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1 text-slate-600">Warna Background (Opsional)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="warna_bg" id="f_warna_bg" class="w-12 h-10 border-2 border-slate-200 rounded-lg cursor-pointer p-1">
                            <span class="text-xs text-slate-500">Biarkan putih jika tidak yakin.</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-bold mb-1 text-slate-600">Urutan Tampil</label>
                            <input type="number" name="urutan" id="f_urutan" class="w-full border-2 border-slate-200 p-2.5 rounded-xl font-bold" value="1">
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 w-full">
                                <input type="checkbox" name="aktif" id="f_aktif" checked class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100 flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 text-white font-bold py-3 rounded-xl hover:bg-indigo-700 transition shadow-md">Simpan Blok</button>
                        <button type="button" onclick="resetForm()" class="bg-slate-100 text-slate-600 font-bold py-3 px-4 rounded-xl hover:bg-slate-200 transition">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// FUNGSI EDIT
function editBlok(btn) {
    var jsonStr = btn.getAttribute('data-blok');
    var txt = document.createElement('textarea');
    txt.innerHTML = jsonStr;
    
    try {
        var data = JSON.parse(txt.value);
        
        document.getElementById('form_title').innerHTML = '<i data-lucide="edit" class="w-5 h-5"></i> Edit Blok: ' + data.tipe.toUpperCase();
        document.getElementById('f_id').value = data.id;
        document.getElementById('f_tipe').value = data.tipe;
        document.getElementById('f_judul').value = data.judul || '';
        document.getElementById('f_subjudul').value = data.subjudul || '';
        document.getElementById('f_konten').value = data.konten || '';
        document.getElementById('f_warna_bg').value = data.warna_bg || '#ffffff';
        document.getElementById('f_urutan').value = data.urutan;
        document.getElementById('f_aktif').checked = data.aktif == 1;
        document.getElementById('f_gambar_lama').value = data.gambar || '';
        
        // Trigger event change untuk update UI (upload & helper text)
        document.getElementById('f_tipe').dispatchEvent(new Event('change'));
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        lucide.createIcons();
    } catch (error) {
        console.error("Gagal membaca data blok:", error);
        alert("Terjadi kesalahan saat memuat data. Silakan refresh halaman.");
    }
}

function resetForm() {
    document.getElementById('form_title').innerHTML = '<i data-lucide="plus-circle" class="w-5 h-5"></i> Tambah Blok Baru';
    document.getElementById('f_id').value = 0;
    document.getElementById('f_gambar_lama').value = '';
    document.getElementById('f_warna_bg').value = '#ffffff';
    document.getElementById('f_aktif').checked = true;
    document.querySelector('form').reset();
    document.getElementById('f_tipe').dispatchEvent(new Event('change'));
    lucide.createIcons();
}

// FUNGSI DINAMIS SAAT TIPE WIDGET DIGANTI
document.getElementById('f_tipe').addEventListener('change', function() {
    const tipe = this.value;
    const boxSingle = document.getElementById('box_upload_single');
    const boxMultiple = document.getElementById('box_upload_multiple');
    const helper = document.getElementById('helper_text');
    const labelKonten = document.getElementById('label_konten');
    const inputKonten = document.getElementById('f_konten');

    // Reset state
    boxSingle.classList.remove('hidden');
    boxMultiple.classList.add('hidden');
    helper.classList.add('hidden');
    labelKonten.innerText = 'Isi Konten / Deskripsi';
    inputKonten.placeholder = 'Tuliskan deskripsi blok di sini...';
    inputKonten.rows = 4;

    // Logika per tipe
    if (tipe === 'carousel') {
        boxSingle.classList.add('hidden');
        boxMultiple.classList.remove('hidden');
    } else if (tipe === 'video') {
        helper.classList.remove('hidden');
        helper.innerHTML = '<b>Panduan Video:</b> Masukkan URL Embed YouTube di kolom Konten. <br>Contoh: <code>https://www.youtube.com/embed/dQw4w9WgXcQ</code>';
        labelKonten.innerText = 'URL Embed YouTube';
        inputKonten.placeholder = 'https://www.youtube.com/embed/...';
        inputKonten.rows = 2;
    } else if (tipe === 'faq') {
        helper.classList.remove('hidden');
        helper.innerHTML = '<b>Panduan FAQ:</b> Tulis Pertanyaan dan Jawaban, pisahkan dengan tanda <b>|</b> (pipa). Tekan Enter untuk baris baru.<br>Contoh:<br>Apakah ini gratis? | Ya, 100% gratis.<br>Bagaimana cara daftar? | Klik menu Mulai Aksi.';
        labelKonten.innerText = 'Daftar FAQ (Pertanyaan | Jawaban)';
        inputKonten.placeholder = 'Pertanyaan 1 | Jawaban 1\nPertanyaan 2 | Jawaban 2';
        inputKonten.rows = 6;
    } else if (tipe === 'testimonial') {
        helper.classList.remove('hidden');
        helper.innerHTML = '<b>Panduan Testimoni:</b> Format: <b>Nama | Jabatan/Peran | Teks Testimoni</b>. Tekan Enter untuk orang baru.<br>Contoh:<br>Fajar | Admin | Website ini luar biasa!<br>Rifqi | Relawan | Sangat mudah digunakan.';
        labelKonten.innerText = 'Daftar Testimoni (Nama | Peran | Teks)';
        inputKonten.placeholder = 'Nama 1 | Peran 1 | Teks panjang...\nNama 2 | Peran 2 | Teks panjang...';
        inputKonten.rows = 6;
    } else if (tipe === 'cta') {
        helper.classList.remove('hidden');
        helper.innerHTML = '<b>Panduan CTA:</b> Masukkan Link/URL tujuan tombol di kolom Konten.<br>Contoh: <code>index.php?page=mulai_aksi</code>';
        labelKonten.innerText = 'Link Tujuan Tombol (URL)';
        inputKonten.placeholder = 'index.php?page=...';
        inputKonten.rows = 2;
    }
});
</script>