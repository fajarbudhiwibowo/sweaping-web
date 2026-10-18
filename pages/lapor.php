<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$objek_id = (int)($_GET['id'] ?? $_GET['objek_id'] ?? 0);
if ($objek_id <= 0) {
    $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Silakan pilih objek dari daftar terlebih dahulu.'];
    redirect('index.php?page=objek');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $objek_id);
mysqli_stmt_execute($stmt);
$objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$objek) { 
    $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Objek tidak ditemukan.'];
    redirect('index.php?page=objek'); 
}

$karakteristik_arr = !empty($objek['karakteristik']) ? json_decode($objek['karakteristik'], true) : [];
$manfaat_arr = !empty($objek['manfaat']) ? json_decode($objek['manfaat'], true) : [];
$tanggal_tanam_js = $objek['tanggal_tanam'] ? $objek['tanggal_tanam'] : '';
?>

<main class="max-w-4xl mx-auto px-3 sm:px-4 pt-24 pb-32">
    <!-- Header -->
    <div class="mb-6">
        <a href="index.php?page=objek" class="text-sm font-bold text-emerald-700 hover:underline flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 mb-1 leading-tight">
            Monitoring: <?= e($objek['nama_objek']) ?>
        </h1>
        <p class="text-xs font-mono text-slate-500"><?= e($objek['kode_objek']) ?></p>
    </div>

    <!-- ACCORDION DATA BASELINE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
        <button type="button" onclick="toggleBaseline()" class="w-full px-4 py-4 bg-slate-50 flex justify-between items-center hover:bg-slate-100 transition active:bg-slate-200">
            <div class="font-bold text-sm text-slate-800 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-emerald-600"></i> Lihat Data Awal (Baseline)
            </div>
            <i data-lucide="chevron-down" id="icon_baseline" class="w-5 h-5 text-slate-500 transition-transform duration-300"></i>
        </button>
        <div id="baseline_content" class="p-4 border-t border-slate-100 hidden text-sm text-slate-600 bg-white">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <div><span class="text-[11px] font-bold uppercase text-slate-400 block">Nama Latin</span> <span class="font-semibold text-slate-800 italic"><?= e($objek['nama_latin'] ?: '-') ?></span></div>
                <div><span class="text-[11px] font-bold uppercase text-slate-400 block">Tanggal Tanam</span> <span class="font-semibold text-slate-800"><?= $objek['tanggal_tanam'] ? date('d M Y', strtotime($objek['tanggal_tanam'])) : '-' ?></span></div>
            </div>
        </div>
    </div>

    <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="aksi_simpan_laporan" value="1">
        <input type="hidden" name="objek_id" value="<?= $objek_id ?>">
        <input type="hidden" name="jenis_objek" value="<?= e($objek['jenis_objek']) ?>">
        <input type="hidden" id="tanggal_tanam_baseline" value="<?= $tanggal_tanam_js ?>">
        
        <!-- Hidden inputs untuk mesin kalkulasi -->
        <input type="hidden" name="skor_fhm" id="in_skor_fhm">
        <input type="hidden" name="kategori_fhm" id="in_kategori_fhm">
        <input type="hidden" name="incidence_percent" id="in_incidence_percent">

        <!-- Toggle Mode -->
        <div class="flex flex-col items-center justify-center bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
            <label class="flex items-center cursor-pointer gap-4 p-2">
                <span id="label_umum" class="text-sm font-bold text-emerald-600">Aktivis / Umum</span>
                <div class="relative">
                    <input type="checkbox" id="toggle_mode" class="sr-only" onchange="toggleMode()">
                    <div class="block bg-slate-300 w-16 h-8 rounded-full transition-colors"></div>
                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform shadow-md"></div>
                </div>
                <span id="label_peneliti" class="text-sm font-bold text-slate-400">Peneliti</span>
            </label>
        </div>

        <!-- SECTION 1: Waktu & Dimensi -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-4 sm:p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-4">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">1</div>
                <div class="font-bold text-slate-800 text-sm sm:text-base">Waktu & Dimensi Pohon</div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 pb-4 border-b border-slate-100">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Tanggal Monitoring</label>
                    <input type="date" name="tanggal_monitoring" id="input_tgl_monitoring" onchange="hitungUsia()" class="w-full px-3 py-2.5 text-base border-2 border-slate-200 rounded-xl" required>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl border border-blue-200">
                    <label class="block text-xs sm:text-xs font-bold text-blue-800 mb-1">Usia Pohon</label>
                    <div id="tampil_usia" class="text-base sm:text-lg font-extrabold text-blue-700">-</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Keliling Batang (cm)</label>
                    <input type="number" inputmode="decimal" name="lingkar_batang" id="input_keliling" oninput="hitungDBH()" class="w-full px-3 py-2.5 text-base border-2 border-slate-200 rounded-xl" placeholder="Cth: 157">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Tinggi Pohon (m)</label>
                    <input type="number" inputmode="decimal" step="0.1" name="tinggi_pohon" id="input_tinggi" oninput="hitungDBH()" class="w-full px-3 py-2.5 text-base border-2 border-slate-200 rounded-xl" placeholder="Cth: 10.5">
                </div>
                <div class="sm:col-span-2 bg-emerald-50 p-3 rounded-xl border border-emerald-200 flex justify-between items-center">
                    <span class="text-xs sm:text-sm font-bold text-emerald-800">Estimasi DBH:</span>
                    <span id="tampil_dbh" class="text-base sm:text-lg font-extrabold text-emerald-600">0 cm</span>
                    <input type="hidden" name="diameter_batang" id="input_dbh">
                </div>
            </div>
        </div>

        <!-- SECTION 2: Observasi Visual (Dasar FHM) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-4 sm:p-6 space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-4">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">2</div>
                <div class="font-bold text-slate-800 text-sm sm:text-base">Observasi Lapangan & Kerusakan</div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Tajuk</label>
                    <select name="kondisi_kanopi" id="sel_kanopi" onchange="hitungFHM()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        <option value="Sangat Rimbun">Sangat Rimbun</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Jarang / Botak">Jarang / Botak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Batang</label>
                    <select name="kondisi_batang" id="sel_batang" onchange="hitungFHM()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        <option value="Baik">Baik / Mulus</option>
                        <option value="Luka">Ada Luka / Rongga</option>
                        <option value="Jamur">Ada Jamur / Kanker</option>
                        <option value="Retak">Batang Retak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Akar</label>
                    <select name="kondisi_akar" id="sel_akar" onchange="hitungFHM()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        <option value="Tertutup Tanah">Tertutup Tanah</option>
                        <option value="Terbuka">Akar Terbuka</option>
                        <option value="Busuk">Akar Busuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Daun</label>
                    <select name="kondisi_daun" id="sel_daun" onchange="hitungFHM()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        <option value="Hijau Segar">Hijau Segar</option>
                        <option value="Menguning">Menguning</option>
                        <option value="Rontok">Banyak Rontok</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gangguan Hama/Penyakit</label>
                    <select name="penyakit" id="sel_hama" onchange="hitungFHM()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        <option value="Tidak Ada">Tidak Ada</option>
                        <option value="Serangga">Serangan Serangga</option>
                        <option value="Liana">Ditumbuhi Liana / Epifit</option>
                        <option value="Jamur">Infeksi Jamur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lingkungan Tumbuh</label>
                    <select name="lingkungan_tumbuh" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        <option value="Tanah Normal">Tanah Normal</option>
                        <option value="Pemadatan">Tanah Padat / Tertutup Semen</option>
                        <option value="Sampah">Banyak Sampah</option>
                        <option value="Genangan">Sering Tergenang</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 3: PENELITI (FHM, ISA, & INSIDENSI) -->
        <div class="peneliti-fields hidden bg-white rounded-3xl shadow-sm border border-teal-100 p-4 sm:p-6 space-y-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-teal-100 mb-4">
                <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-sm">3</div>
                <div class="font-bold text-teal-800 text-sm sm:text-base">Analisis Ilmiah (FHM & ISA)</div>
            </div>
            
            <!-- FHM SCORING -->
            <div>
                <label class="block text-[11px] sm:text-xs font-bold text-teal-700 uppercase mb-2">Parameter FHM (%)</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <input type="number" inputmode="decimal" name="kerapatan_tajuk" id="in_kerapatan" oninput="hitungFHM()" placeholder="Kerapatan" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                    <input type="number" inputmode="decimal" name="transparansi_tajuk" id="in_transparansi" oninput="hitungFHM()" placeholder="Transparansi" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                    <input type="number" inputmode="decimal" name="rasio_tajuk_hidup" id="in_rasio" oninput="hitungFHM()" placeholder="Rasio Hidup" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                    <input type="number" inputmode="decimal" name="mati_pucuk" id="in_mati_pucuk" oninput="hitungFHM()" placeholder="Mati Pucuk" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                </div>
                <div class="mt-2 bg-teal-50 p-3 rounded-lg border border-teal-200 flex justify-between items-center">
                    <span class="text-xs font-bold text-teal-800">Skor FHM (0-100):</span>
                    <span id="tampil_fhm" class="text-lg font-extrabold text-teal-700">-</span>
                </div>
            </div>

            <!-- ISA MATRIX -->
            <div class="border-t border-teal-100 pt-4">
                <label class="block text-[11px] sm:text-xs font-bold text-teal-700 uppercase mb-2">Matriks Risiko ISA (Pohon)</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold mb-1 text-slate-600">Probabilitas Gagal</label>
                        <select name="isa_probability" id="isa_prob" onchange="hitungISA()" class="w-full px-2 py-2 text-sm border-2 border-slate-200 rounded-lg">
                            <option value="1">Sangat Kecil</option>
                            <option value="2">Mungkin</option>
                            <option value="3">Sangat Mungkin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold mb-1 text-slate-600">Dampak ke Target</label>
                        <select name="isa_impact" id="isa_imp" onchange="hitungISA()" class="w-full px-2 py-2 text-sm border-2 border-slate-200 rounded-lg">
                            <option value="1">Kecil (Taman)</option>
                            <option value="2">Sedang (Jalan)</option>
                            <option value="3">Besar (Bangunan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold mb-1 text-slate-600">Konsekuensi</label>
                        <select name="isa_consequence" id="isa_cons" onchange="hitungISA()" class="w-full px-2 py-2 text-sm border-2 border-slate-200 rounded-lg">
                            <option value="1">Minor</option>
                            <option value="2">Signifikan</option>
                            <option value="3">Severe</option>
                        </select>
                    </div>
                </div>
                <div class="mt-2 bg-rose-50 p-3 rounded-lg border border-rose-200 flex justify-between items-center">
                    <span class="text-xs font-bold text-rose-800">Kategori Risiko ISA:</span>
                    <span id="tampil_isa" class="text-lg font-extrabold text-rose-700">Rendah</span>
                    <input type="hidden" name="kategori_risiko_isa" id="in_kategori_isa" value="Rendah">
                </div>
            </div>

            <!-- INSIDENSI KERUSAKAN -->
            <div class="border-t border-teal-100 pt-4">
                <label class="block text-[11px] sm:text-xs font-bold text-teal-700 uppercase mb-2">Insidensi Kerusakan Plot (I = nd/N x 100%)</label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold mb-1">Jumlah Pohon Rusak (nd)</label>
                        <input type="number" name="incidence_nd" id="in_nd" oninput="hitungInsidensi()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg" placeholder="Cth: 5">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold mb-1">Total Pohon Diperiksa (N)</label>
                        <input type="number" name="incidence_n" id="in_n" oninput="hitungInsidensi()" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg" placeholder="Cth: 50">
                    </div>
                </div>
                <div class="mt-2 bg-slate-100 p-3 rounded-lg border border-slate-300 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-700">Persentase Insidensi:</span>
                    <span id="tampil_insidensi" class="text-lg font-extrabold text-slate-800">0%</span>
                </div>
            </div>

            <!-- KARBON -->
            <div class="border-t border-teal-100 pt-4">
                <div class="bg-amber-50 rounded-xl border-2 border-amber-200 p-3 sm:p-4">
                    <div class="text-[11px] sm:text-xs font-bold text-amber-700 uppercase mb-2">Kalkulasi Karbon (Chave 2005)</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold mb-1">Massa Jenis Kayu</label>
                            <input type="number" inputmode="decimal" step="0.01" name="massa_jenis_kayu" id="input_massa_jenis" oninput="hitungKarbon()" value="0.6" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-lg">
                        </div>
                        <div class="flex flex-col justify-center bg-white p-2 rounded-lg mt-2 sm:mt-0">
                            <span class="text-[11px] font-bold text-amber-700">Serapan Karbon:</span>
                            <span id="tampil_karbon" class="text-base font-extrabold text-amber-800">0.00 kg CO2e</span>
                            <input type="hidden" name="serapan_karbon_ilmiah" id="input_karbon">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Dokumentasi -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-4 sm:p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-4">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">4</div>
                <div class="font-bold text-slate-800 text-sm sm:text-base">Dokumentasi</div>
            </div>
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-2">Foto Dokumentasi</label>
                <input type="file" name="foto" id="input_foto_kamera" accept="image/*" capture="environment" class="hidden" onchange="pilihMedia(this, 'foto')">
                <input type="file" id="input_foto_galeri" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="pilihMedia(this, 'foto')">
                
                <div id="upload_box" class="relative w-full h-40 md:h-56 border-4 border-dashed border-slate-200 rounded-2xl bg-slate-50 flex flex-col items-center justify-center overflow-hidden">
                    <img id="preview_foto" src="" alt="" class="absolute inset-0 w-full h-full object-cover hidden z-10">
                    <div id="upload_placeholder" class="text-center z-0 p-4">
                        <div class="flex justify-center gap-3 mb-2">
                            <button type="button" onclick="document.getElementById('input_foto_kamera').click()" class="bg-white border-2 border-slate-200 text-slate-700 font-bold py-2 px-4 rounded-xl hover:bg-slate-100 transition flex items-center justify-center gap-2 text-sm shadow-sm">
                                <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                            </button>
                            <button type="button" onclick="document.getElementById('input_foto_galeri').click()" class="bg-white border-2 border-slate-200 text-slate-700 font-bold py-2 px-4 rounded-xl hover:bg-slate-100 transition flex items-center justify-center gap-2 text-sm shadow-sm">
                                <i data-lucide="image" class="w-4 h-4"></i> Galeri
                            </button>
                        </div>
                        <p class="text-xs text-slate-500">Format: JPG/PNG. Maks 8MB.</p>
                    </div>
                    <button type="button" id="btn_hapus_foto" onclick="hapusFoto()" class="absolute top-3 right-3 bg-rose-500 text-white p-2 rounded-full hidden z-20 hover:bg-rose-600 transition shadow-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                <textarea name="catatan" rows="3" class="w-full px-3 py-2 text-base border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none" placeholder="Tuliskan temuan detail di lapangan..."></textarea>
            </div>
        </div>

        <!-- Tombol Simpan Sticky -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-slate-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50 sm:static sm:bg-transparent sm:border-0 sm:shadow-none sm:p-0">
            <button type="submit" class="w-full py-3.5 sm:py-4 bg-emerald-700 text-white font-bold text-base sm:text-lg rounded-xl hover:bg-emerald-800 shadow-lg active:scale-95 transition-transform">
                Simpan Laporan
            </button>
        </div>
    </form>
</main>

<script>
// Set tanggal hari ini
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('input_tgl_monitoring').value = today;
    hitungUsia(); 
    hitungFHM(); // Panggil kalkulasi awal
    hitungISA();
});

function toggleBaseline() {
    const content = document.getElementById('baseline_content');
    const icon = document.getElementById('icon_baseline');
    content.classList.toggle('hidden');
    icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
}

function toggleMode() {
    const isPeneliti = document.getElementById('toggle_mode').checked;
    const penelitiFields = document.querySelectorAll('.peneliti-fields');
    const labelUmum = document.getElementById('label_umum');
    const labelPeneliti = document.getElementById('label_peneliti');
    const track = document.querySelector('#toggle_mode + div');
    const dot = document.querySelector('#toggle_mode + div + .dot');
    
    if(isPeneliti) {
        penelitiFields.forEach(el => el.classList.remove('hidden'));
        labelUmum.classList.replace('text-emerald-600', 'text-slate-400');
        labelPeneliti.classList.replace('text-slate-400', 'text-teal-600');
        track.classList.replace('bg-slate-300', 'bg-teal-500');
        dot.style.transform = 'translateX(100%)';
    } else {
        penelitiFields.forEach(el => el.classList.add('hidden'));
        labelUmum.classList.replace('text-slate-400', 'text-emerald-600');
        labelPeneliti.classList.replace('text-teal-600', 'text-slate-400');
        track.classList.replace('bg-teal-500', 'bg-slate-300');
        dot.style.transform = 'translateX(0)';
    }
}

function hitungUsia() {
    const tglTanamStr = document.getElementById('tanggal_tanam_baseline').value;
    const tglMonitorStr = document.getElementById('input_tgl_monitoring').value;
    const tampil = document.getElementById('tampil_usia');

    if (!tglTanamStr) {
        tampil.innerHTML = "<span class='text-xs text-slate-400 italic'>Tgl tanam belum diisi</span>";
        return;
    }
    if (!tglMonitorStr) { tampil.innerText = "-"; return; }

    const tglTanam = new Date(tglTanamStr);
    const tglMonitor = new Date(tglMonitorStr);

    if (tglMonitor < tglTanam) {
        tampil.innerHTML = "<span class='text-xs text-rose-600 font-bold'>Tgl monitor < tgl tanam!</span>";
        return;
    }

    let years = tglMonitor.getFullYear() - tglTanam.getFullYear();
    let months = tglMonitor.getMonth() - tglTanam.getMonth();
    let days = tglMonitor.getDate() - tglTanam.getDate();

    if (days < 0) {
        months--;
        const prevMonth = new Date(tglMonitor.getFullYear(), tglMonitor.getMonth(), 0);
        days += prevMonth.getDate();
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    let hasil = [];
    if (years > 0) hasil.push(years + " Tahun");
    if (months > 0) hasil.push(months + " Bulan");
    if (days > 0 || hasil.length === 0) hasil.push(days + " Hari");

    tampil.innerText = hasil.join(', ');
}

function hitungDBH() {
    const keliling = parseFloat(document.getElementById('input_keliling').value) || 0;
    const dbh = keliling > 0 ? (keliling / Math.PI).toFixed(2) : 0;
    document.getElementById('tampil_dbh').innerText = dbh + ' cm';
    document.getElementById('input_dbh').value = dbh;
    hitungKarbon();
}

function hitungKarbon() {
    const dbh_cm = parseFloat(document.getElementById('input_dbh').value) || 0;
    const tinggi = parseFloat(document.getElementById('input_tinggi').value) || 0;
    const massa_jenis = parseFloat(document.getElementById('input_massa_jenis').value) || 0.6;
    
    let karbon = 0;
    if (dbh_cm > 0 && tinggi > 0) {
        const dbh_m = dbh_cm / 100;
        const agb = 0.0673 * Math.pow((massa_jenis * Math.pow(dbh_m, 2) * tinggi), 0.976);
        const biomassa_kg = agb * 1000; 
        const karbon_kg = biomassa_kg * 0.5; 
        const co2e_kg = karbon_kg * 3.67; 
        karbon = co2e_kg.toFixed(2);
    }
    document.getElementById('tampil_karbon').innerText = karbon + ' kg CO2e';
    document.getElementById('input_karbon').value = karbon;
}

// MESIN SKOR FHM
function hitungFHM() {
    const isPeneliti = document.getElementById('toggle_mode').checked;
    if(!isPeneliti) return; // Hanya hitung jika mode peneliti

    // 1. Nilai Tajuk (Persentase)
    const kerapatan = parseFloat(document.getElementById('in_kerapatan').value) || 0;
    const transparansi = parseFloat(document.getElementById('in_transparansi').value) || 0;
    const rasio = parseFloat(document.getElementById('in_rasio').value) || 0;
    const matiPucuk = parseFloat(document.getElementById('in_mati_pucuk').value) || 0;
    
    // Rata-rata tajuk (makin tinggi transparansi/mati pucuk, makin rendah skor)
    const skorTajuk = (kerapatan + (100 - transparansi) + rasio + (100 - matiPucuk)) / 4;

    // 2. Nilai Kondisi Fisik (dari dropdown)
    const mapBatang = {'Baik': 100, 'Luka': 50, 'Jamur': 20, 'Retak': 50};
    const mapAkar = {'Tertutup Tanah': 100, 'Terbuka': 50, 'Busuk': 10};
    const mapDaun = {'Hijau Segar': 100, 'Menguning': 50, 'Rontok': 20};
    const mapHama = {'Tidak Ada': 100, 'Serangga': 50, 'Liana': 50, 'Jamur': 50};

    const valBatang = mapBatang[document.getElementById('sel_batang').value] || 100;
    const valAkar = mapAkar[document.getElementById('sel_akar').value] || 100;
    const valDaun = mapDaun[document.getElementById('sel_daun').value] || 100;
    const valHama = mapHama[document.getElementById('sel_hama').value] || 100;

    const skorFisik = (valBatang + valAkar + valDaun + valHama) / 4;

    // 3. Skor Akhir (Bobot: Tajuk 60%, Fisik 40%)
    let totalSkor = (skorTajuk * 0.6) + (skorFisik * 0.4);
    totalSkor = totalSkor.toFixed(2);

    // Kategori
    let kategori = "Sehat";
    if(totalSkor < 50) kategori = "Rusak Berat";
    else if (totalSkor < 75) kategori = "Kurang Sehat";

    document.getElementById('tampil_fhm').innerText = totalSkor + ' (' + kategori + ')';
    document.getElementById('in_skor_fhm').value = totalSkor;
    document.getElementById('in_kategori_fhm').value = kategori;
}

// MESIN MATRIKS ISA
function hitungISA() {
    const isPeneliti = document.getElementById('toggle_mode').checked;
    if(!isPeneliti) return;

    const prob = parseInt(document.getElementById('isa_prob').value) || 1;
    const imp = parseInt(document.getElementById('isa_imp').value) || 1;
    const cons = parseInt(document.getElementById('isa_cons').value) || 1;

    // Matriks ISA sederhana: Probability x Impact x Consequence
    const score = prob * imp * cons;
    
    let kategori = "Rendah";
    if(score >= 18) kategori = "Tinggi";
    else if (score >= 8) kategori = "Sedang";

    document.getElementById('tampil_isa').innerText = kategori;
    document.getElementById('in_kategori_isa').value = kategori;
}

// MESIN INSIDENSI KERUSAKAN
function hitungInsidensi() {
    const isPeneliti = document.getElementById('toggle_mode').checked;
    if(!isPeneliti) return;

    const nd = parseFloat(document.getElementById('in_nd').value) || 0;
    const n = parseFloat(document.getElementById('in_n').value) || 0;
    
    let percent = 0;
    if(n > 0) {
        percent = (nd / n) * 100;
    }

    document.getElementById('tampil_insidensi').innerText = percent.toFixed(2) + '%';
    document.getElementById('in_incidence_percent').value = percent.toFixed(2);
}

// UPLOAD FOTO
function pilihMedia(input, tipe) {
    var file = input.files[0];
    var preview = document.getElementById('preview_foto');
    var placeholder = document.getElementById('upload_placeholder');
    var btnHapus = document.getElementById('btn_hapus_foto');
    var inputKamera = document.getElementById('input_foto_kamera');
    if (!file) return;
    if (file.size > 8 * 1024 * 1024) { alert('❌ Maks 8MB'); input.value = ''; return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        btnHapus.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
    if (input.id === 'input_foto_galeri') {
        var dt = new DataTransfer();
        dt.items.add(file);
        inputKamera.files = dt.files;
    }
}

function hapusFoto() {
    document.getElementById('input_foto_kamera').value = '';
    document.getElementById('input_foto_galeri').value = '';
    document.getElementById('preview_foto').classList.add('hidden');
    document.getElementById('preview_foto').src = '';
    document.getElementById('upload_placeholder').classList.remove('hidden');
    document.getElementById('btn_hapus_foto').classList.add('hidden');
}
</script>