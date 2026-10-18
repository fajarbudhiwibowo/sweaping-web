<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

// Ambil objek_id dari URL (jika diakses dari tombol Lapor)
$selected_objek_id = (int)($_GET['objek_id'] ?? 0);

// Ambil daftar objek
$q_objek = mysqli_query($conn, "SELECT id, kode_objek, nama_objek, jenis_objek FROM objek WHERE deleted_at IS NULL AND status = 'aktif' ORDER BY nama_objek ASC");
?>

<main class="max-w-3xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-8 text-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="file-plus" class="w-8 h-8 text-emerald-600"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900">Buat Laporan Monitoring</h1>
        <p class="text-slate-500 mt-2">Dokumentasikan temuan dan perhitungan karbon di lapangan.</p>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        <input type="hidden" name="aksi_simpan_laporan" value="1">

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Objek yang Dipantau</label>
            <select name="objek_id" id="select_objek" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">-- Pilih Objek --</option>
                <?php while($o = mysqli_fetch_assoc($q_objek)): ?>
                    <option value="<?= $o['id'] ?>" data-jenis="<?= $o['jenis_objek'] ?>" <?= $o['id'] == $selected_objek_id ? 'selected' : '' ?>>
                        [<?= e($o['kode_objek']) ?>] <?= e($o['nama_objek']) ?> (<?= ucfirst($o['jenis_objek']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Sisa form sama dengan v5 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Monitoring</label>
                <input type="date" name="tanggal_monitoring" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Kondisi Umum</label>
                <select name="status_kondisi" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="Aman / Bersih / Sehat">✅ Aman / Bersih / Sehat</option>
                    <option value="Waspada / Perlu Perawatan">⚠️ Waspada / Perlu Perawatan</option>
                    <option value="Tercemar / Rusak">❌ Tercemar / Rusak</option>
                </select>
            </div>
        </div>

        <!-- FORM POHON -->
        <div id="form_pohon" class="hidden bg-emerald-50 rounded-2xl p-5 border border-emerald-100 space-y-4">
            <h3 class="text-sm font-bold text-emerald-800 border-b border-emerald-200 pb-2">Data Pohon (Kalkulasi Karbon Otomatis)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Diameter Batang (cm)</label>
                    <input type="number" step="0.1" name="diameter_batang" id="dbh_pohon" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tinggi Pohon (m)</label>
                    <input type="number" step="0.1" name="tinggi_pohon" id="tinggi_pohon" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
            </div>
            <div class="bg-white p-3 rounded-lg text-center border border-emerald-200">
                <span class="text-xs text-slate-500">Estimasi Serapan Karbon:</span>
                <span id="karbon_pohon" class="font-bold text-amber-600 text-lg ml-1">0.00</span> <span class="text-xs font-bold text-slate-500">kg CO₂</span>
            </div>
        </div>

        <!-- FORM SUNGAI -->
        <div id="form_sungai" class="hidden bg-blue-50 rounded-2xl p-5 border border-blue-100 space-y-4">
            <h3 class="text-sm font-bold text-blue-800 border-b border-blue-200 pb-2">Data Sungai</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="number" step="0.01" name="debit_air_normal" placeholder="Debit Normal (m³/s)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="kedalaman_air" placeholder="Kedalaman (m)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="lebar_sungai_monitor" placeholder="Lebar (m)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
        </div>

        <!-- FORM KOMPOSTING -->
        <div id="form_komposting" class="hidden bg-amber-50 rounded-2xl p-5 border border-amber-100 space-y-4">
            <h3 class="text-sm font-bold text-amber-800 border-b border-amber-200 pb-2">Data Komposting</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Volume Sampah (kg/bln)</label>
                    <input type="number" name="volume_sampah" id="vol_kompos" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Hasil Kompos (kg/bln)</label>
                    <input type="number" name="hasil_kompos" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
            </div>
            <div class="bg-white p-3 rounded-lg text-center border border-amber-200">
                <span class="text-xs text-slate-500">Estimasi CO₂ Dicegah:</span>
                <span id="karbon_kompos" class="font-bold text-amber-600 text-lg ml-1">0.00</span> <span class="text-xs font-bold text-slate-500">kg CO₂</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kontributor</label>
                <input type="text" name="nama_kontributor" value="<?= e($_SESSION['nama']) ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Peran</label>
                <input type="text" name="peran_kontributor" placeholder="Contoh: Patroli Rutin" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Lapangan</label>
            <textarea name="catatan" rows="3" required placeholder="Deskripsi detail kondisi objek..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Foto Kondisi (Opsional)</label>
                <input type="file" name="foto" accept="image/*" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Visibilitas</label>
                <select name="visibility" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="publik">🌍 Publik</option>
                    <option value="teman">👥 Teman</option>
                    <option value="privat">🔒 Privat</option>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-emerald-700 transition flex justify-center items-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i> Kirim Laporan
        </button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectObjek = document.getElementById('select_objek');
    const boxPohon = document.getElementById('form_pohon');
    const boxSungai = document.getElementById('form_sungai');
    const boxKompos = document.getElementById('form_komposting');
    
    function updateFormDisplay() {
        const opt = selectObjek.options[selectObjek.selectedIndex];
        const jenis = opt ? opt.getAttribute('data-jenis') : '';
        
        boxPohon.classList.add('hidden'); boxSungai.classList.add('hidden'); boxKompos.classList.add('hidden');
        
        if (jenis === 'pohon') boxPohon.classList.remove('hidden');
        if (jenis === 'sungai') boxSungai.classList.remove('hidden');
        if (jenis === 'komposting') boxKompos.classList.remove('hidden');
    }

    // Jalankan saat pertama kali load (untuk handle URL objek_id)
    updateFormDisplay();

    // Jalankan saat user ganti dropdown
    selectObjek.addEventListener('change', updateFormDisplay);

    // Live Calc Pohon
    const dbh = document.getElementById('dbh_pohon');
    const tinggi = document.getElementById('tinggi_pohon');
    const hasilPohon = document.getElementById('karbon_pohon');
    function hitungPohon() {
        let d = parseFloat(dbh.value) || 0, t = parseFloat(tinggi.value) || 0;
        if (d > 0 && t > 0) {
            let agb = 0.0509 * 0.6 * Math.pow(d, 2) * t;
            let karbon = (agb * 1.2) * 0.47;
            hasilPohon.textContent = (karbon * 3.667).toFixed(2);
        } else { hasilPohon.textContent = '0.00'; }
    }
    if(dbh) { dbh.addEventListener('input', hitungPohon); tinggi.addEventListener('input', hitungPohon); }

    // Live Calc Kompos
    const volKompos = document.getElementById('vol_kompos');
    const hasilKompos = document.getElementById('karbon_kompos');
    if(volKompos) {
        volKompos.addEventListener('input', function() {
            hasilKompos.textContent = ((parseFloat(this.value) || 0) * 0.7).toFixed(2);
        });
    }
});
</script>
