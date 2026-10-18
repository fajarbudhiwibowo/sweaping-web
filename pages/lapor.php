<?php if (!isset($_SESSION['user_id'])) { redirect('index.php?page=login'); }
$lapor_objek_id = (int)($_GET['objek_id'] ?? 0);
$stmt_lo = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt_lo, 'i', $lapor_objek_id);
mysqli_stmt_execute($stmt_lo);
$lapor_objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_lo));
mysqli_stmt_close($stmt_lo);
if (!$lapor_objek) { notif('gagal', 'Objek tidak ditemukan.'); redirect('index.php?page=objek'); }
$jenis_aktif = $lapor_objek['jenis_objek'];
?>
<main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
    <h1 class="text-2xl font-extrabold mb-6">Formulir Laporan: <?= e($lapor_objek['nama_objek']) ?></h1>
    <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi_simpan_laporan" value="1">
        <input type="hidden" name="objek_id" value="<?= $lapor_objek_id ?>">
        <input type="hidden" name="jenis_objek" value="<?= $jenis_aktif ?>">

        <!-- DATA OBJEK (READONLY) -->
        <div class="bg-slate-50 p-4 rounded-xl border">
            <div class="font-bold"><?= e($lapor_objek['nama_objek']) ?></div>
            <div class="text-sm text-slate-500"><?= e($lapor_objek['koordinat']) ?></div>
        </div>

        <!-- MONITORING POHON -->
        <div id="box_pohon" style="<?= $jenis_aktif==='pohon'?'':'display:none' ?>" class="bg-white p-6 rounded-3xl shadow-xl space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs font-bold">Diameter Batang (cm)</label><input type="number" name="diameter_batang" id="m_dbh" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Tinggi Pohon (m)</label><input type="number" name="tinggi_pohon" id="m_tinggi" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Lingkar Batang (cm)</label><input type="number" name="lingkar_batang" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Penyakit</label><input type="text" name="penyakit" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Status Pohon</label><select name="status_pohon" class="w-full border-2 p-2 rounded"><option value="Sehat">Sehat</option><option value="Sakit">Sakit</option></select></div>
            </div>
            <div class="bg-emerald-50 p-3 rounded text-emerald-800 text-sm font-bold">Estimasi Karbon: <span id="co2_calc">0.00</span> kg CO2</div>
        </div>

        <!-- MONITORING SUNGAI -->
        <div id="box_sungai" style="<?= $jenis_aktif==='sungai'?'':'display:none' ?>" class="bg-white p-6 rounded-3xl shadow-xl space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs font-bold">Debit Air (m3/s)</label><input type="number" name="debit_air_normal" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Kedalaman Air (m)</label><input type="number" name="kedalaman_air" class="w-full border-2 p-2 rounded"></div>
            </div>
        </div>

        <!-- MONITORING KOMPOS -->
        <div id="box_kompos" style="<?= $jenis_aktif==='komposting'?'':'display:none' ?>" class="bg-white p-6 rounded-3xl shadow-xl space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs font-bold">Volume Bahan Masuk (kg)</label><input type="number" name="kompos_volume_bahan_masuk" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Frekuensi Pemasukan</label><input type="text" name="kompos_frekuensi_pemasukan" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Frekuensi Pengadukan</label><input type="text" name="kompos_frekuensi_pengadukan" class="w-full border-2 p-2 rounded"></div>
                <div><label class="text-xs font-bold">Lama Proses (hari)</label><input type="number" name="kompos_lama_proses" class="w-full border-2 p-2 rounded"></div>
            </div>
        </div>

        <!-- UMUM -->
        <div class="bg-white p-6 rounded-3xl shadow-xl space-y-4">
            <div><label class="block text-sm font-bold">Mitigasi / Tindakan Preventif</label><textarea name="mitigasi" rows="2" class="w-full border-2 p-2 rounded"></textarea></div>
            <div><label class="block text-sm font-bold">Catatan</label><textarea name="catatan" rows="3" required class="w-full border-2 p-2 rounded"></textarea></div>
            <div><label class="block text-sm font-bold">Foto</label><input type="file" name="foto" class="w-full border-2 p-2 rounded"></div>
        </div>

        <button type="submit" class="w-full py-4 bg-emerald-700 text-white font-bold rounded-xl">Simpan Laporan</button>
    </form>
</main>
<script>
document.getElementById('m_dbh')?.addEventListener('input', hitung);
document.getElementById('m_tinggi')?.addEventListener('input', hitung);
function hitung() {
    var dbh = parseFloat(document.getElementById('m_dbh').value) || 0;
    var tinggi = parseFloat(document.getElementById('m_tinggi').value) || 0;
    if (dbh > 0 && tinggi > 0) {
        var agb = 0.0509 * 0.6 * Math.pow(dbh, 2) * tinggi;
        var co2 = (agb * 1.2) * 0.47 * 3.667;
        document.getElementById('co2_calc').innerText = co2.toFixed(2);
    }
}
</script>