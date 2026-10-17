<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$error = '';
$sukses = '';

// Ambil daftar objek untuk dropdown
$q_objek = mysqli_query($conn, "SELECT id, kode_objek, nama_objek, jenis_objek FROM objek WHERE deleted_at IS NULL AND status = 'aktif' ORDER BY nama_objek ASC");

if (isset($_POST['aksi_simpan_laporan'])) {
    $objek_id = (int)$_POST['objek_id'];
    $status_kondisi = trim($_POST['status_kondisi'] ?? 'Aman / Bersih / Sehat');
    $catatan = trim($_POST['catatan'] ?? '');
    $visibility = $_POST['visibility'] ?? 'publik';
    $uid = (int)$_SESSION['user_id'];

    // Ambil data master objek untuk sinkronisasi
    $stmt_o = mysqli_prepare($conn, "SELECT jenis_objek, nama_objek, koordinat FROM objek WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_o, 'i', $objek_id);
    mysqli_stmt_execute($stmt_o);
    $objek_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_o));
    mysqli_stmt_close($stmt_o);

    if (!$objek_data) {
        $error = 'Objek tidak valid.';
    } elseif ($catatan === '') {
        $error = 'Catatan lapangan wajib diisi.';
    } else {
        $jenis_objek = $objek_data['jenis_objek'];
        $nama_objek = $objek_data['nama_objek'];
        $koordinat = $objek_data['koordinat'];
        
        // Logika Penghitungan Karbon Sederhana (Chave 2005) untuk v6 ini
        $serapan_karbon = 0;
        if ($jenis_objek === 'pohon') {
            $dbh = (float)($_POST['diameter_batang'] ?? 0);
            $tinggi = (float)($_POST['tinggi_pohon'] ?? 0);
            if ($dbh > 0 && $tinggi > 0) {
                $agb = 0.0509 * 0.6 * pow($dbh, 2) * $tinggi;
                $karbon = ($agb * 1.2) * 0.47;
                $serapan_karbon = round($karbon * 3.667, 2);
            }
        } elseif ($jenis_objek === 'komposting') {
            $volume = (float)($_POST['volume_sampah'] ?? 0);
            $serapan_karbon = round($volume * 0.7, 2);
        }

        // Insert Laporan
        $stmt = mysqli_prepare($conn, "INSERT INTO laporan_aksi (objek_id, user_id, jenis_objek, nama_objek, koordinat, status_kondisi, nama_kontributor, peran_kontributor, catatan, visibility, serapan_karbon) VALUES (?, ?, ?, ?, ?, ?, ?, 'User', ?, ?, ?)");
        $nama_kontributor = $_SESSION['nama'];
        
        mysqli_stmt_bind_param($stmt, 'iisssssssd', $objek_id, $uid, $jenis_objek, $nama_objek, $koordinat, $status_kondisi, $nama_kontributor, $catatan, $visibility, $serapan_karbon);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('index.php?page=arsip');
        } else {
            $error = 'Gagal menyimpan laporan: ' . mysqli_stmt_error($stmt);
        }
    }
}
?>

<main class="max-w-3xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-8 text-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="file-plus" class="w-8 h-8 text-emerald-600"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900">Buat Laporan Aksi</h1>
        <p class="text-slate-500 mt-2">Dokumentasikan temuan Anda di lapangan.</p>
    </div>

    <?php if($error): ?>
        <div class="bg-rose-50 text-rose-700 p-4 rounded-xl mb-6 text-sm font-bold border border-rose-200"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=lapor" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Objek yang Dipantau</label>
            <select name="objek_id" id="select_objek" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">-- Pilih Objek --</option>
                <?php while($o = mysqli_fetch_assoc($q_objek)): ?>
                    <option value="<?= $o['id'] ?>" data-jenis="<?= $o['jenis_objek'] ?>">
                        [<?= e($o['kode_objek']) ?>] <?= e($o['nama_objek']) ?> (<?= ucfirst($o['jenis_objek']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Form Dinamis: Hanya muncul jika POHON dipilih -->
        <div id="box_pohon" class="hidden bg-emerald-50 rounded-2xl p-5 border border-emerald-100">
            <h3 class="text-sm font-bold text-emerald-800 mb-4 flex items-center gap-2"><i data-lucide="tree-pine" class="w-4 h-4"></i> Pengukuran Pohon (Otomatis Hitung Karbon)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Diameter Batang (cm)</label>
                    <input type="number" step="0.1" name="diameter_batang" id="dbh" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tinggi Pohon (m)</label>
                    <input type="number" step="0.1" name="tinggi_pohon" id="tinggi" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-emerald-500">
                </div>
            </div>
            <div class="mt-3 bg-white p-3 rounded-lg text-center border border-emerald-200">
                <span class="text-xs text-slate-500">Estimasi Serapan Karbon:</span>
                <span id="hasil_karbon" class="font-bold text-amber-600 text-lg ml-1">0.00</span> <span class="text-xs font-bold text-slate-500">kg CO₂</span>
            </div>
        </div>

        <!-- Form Dinamis: Hanya muncul jika KOMPOSTING dipilih -->
        <div id="box_kompos" class="hidden bg-amber-50 rounded-2xl p-5 border border-amber-100">
            <h3 class="text-sm font-bold text-amber-800 mb-4 flex items-center gap-2"><i data-lucide="recycle" class="w-4 h-4"></i> Data Komposting</h3>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Volume Sampah Organik (kg/bulan)</label>
                <input type="number" step="1" name="volume_sampah" id="vol_kompos" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-amber-500">
            </div>
            <div class="mt-3 bg-white p-3 rounded-lg text-center border border-amber-200">
                <span class="text-xs text-slate-500">Estimasi CO₂ Dicegah:</span>
                <span id="hasil_kompos" class="font-bold text-amber-600 text-lg ml-1">0.00</span> <span class="text-xs font-bold text-slate-500">kg CO₂</span>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Kondisi Objek</label>
            <select name="status_kondisi" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="Aman / Bersih / Sehat">✅ Aman / Bersih / Sehat</option>
                <option value="Waspada / Perlu Perawatan">⚠️ Waspada / Perlu Perawatan</option>
                <option value="Tercemar / Rusak">❌ Tercemar / Rusak</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Lapangan</label>
            <textarea name="catatan" required rows="4" placeholder="Jelaskan kondisi detail, temuan, atau tindakan yang dilakukan..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Visibilitas</label>
            <select name="visibility" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="publik">🌍 Publik</option>
                <option value="teman">👥 Teman</option>
                <option value="privat">🔒 Privat</option>
            </select>
        </div>

        <button type="submit" name="aksi_simpan_laporan" class="w-full py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-emerald-700 transition flex justify-center items-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i> Kirim Laporan
        </button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectObjek = document.getElementById('select_objek');
    const boxPohon = document.getElementById('box_pohon');
    const boxKompos = document.getElementById('box_kompos');
    
    // Tampilkan form dinamis saat objek dipilih
    selectObjek.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const jenis = selectedOption.getAttribute('data-jenis');
        
        boxPohon.classList.add('hidden');
        boxKompos.classList.add('hidden');
        
        if (jenis === 'pohon') boxPohon.classList.remove('hidden');
        if (jenis === 'komposting') boxKompos.classList.remove('hidden');
    });

    // Live Calculator Karbon Pohon (Chave 2005)
    const dbhInput = document.getElementById('dbh');
    const tinggiInput = document.getElementById('tinggi');
    const hasilKarbon = document.getElementById('hasil_karbon');
    
    function hitungKarbon() {
        let dbh = parseFloat(dbhInput.value) || 0;
        let tinggi = parseFloat(tinggiInput.value) || 0;
        if (dbh > 0 && tinggi > 0) {
            let agb = 0.0509 * 0.6 * Math.pow(dbh, 2) * tinggi;
            let karbon = (agb * 1.2) * 0.47;
            let co2 = karbon * 3.667;
            hasilKarbon.textContent = co2.toFixed(2);
        } else {
            hasilKarbon.textContent = '0.00';
        }
    }
    dbhInput.addEventListener('input', hitungKarbon);
    tinggiInput.addEventListener('input', hitungKarbon);

    // Live Calculator Kompos (IPCC)
    const volKompos = document.getElementById('vol_kompos');
    const hasilKompos = document.getElementById('hasil_kompos');
    
    volKompos.addEventListener('input', function() {
        let vol = parseFloat(this.value) || 0;
        let co2 = vol * 0.7;
        hasilKompos.textContent = co2.toFixed(2);
    });
});
</script>
