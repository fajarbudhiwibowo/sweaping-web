<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$objek_id = (int)($_GET['id'] ?? 0);
$uid = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $objek_id);
mysqli_stmt_execute($stmt);
$objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$objek) { redirect('index.php?page=objek'); }
if (!is_admin() && $objek['user_id'] != $uid) { $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Tidak berhak.']; redirect('index.php?page=objek'); }

// Helper JSON
$to_arr = function($json) { return !empty($json) ? json_decode($json, true) : []; };
$karakteristik = $to_arr($objek['karakteristik']);
$manfaat = $to_arr($objek['manfaat']);
$taman_kategori = $to_arr($objek['taman_kategori_tanaman']);
$taman_pemanfaatan = $to_arr($objek['taman_pemanfaatan']);
$taman_fasilitas = $to_arr($objek['taman_fasilitas']);
$kompos_bahan = $to_arr($objek['kompos_bahan_organik']);
$dasar_sungai = $to_arr($objek['dasar_sungai']);
$pemanfaatan_sungai = $to_arr($objek['pemanfaatan_sungai']);

// Ambil Area untuk Dropdown
$q_areas = mysqli_query($conn, "SELECT id, nama_area FROM area_pantau WHERE deleted_at IS NULL");
$areas = [];
while($a = mysqli_fetch_assoc($q_areas)) $areas[] = $a;
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<main class="max-w-4xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-8">
        <a href="index.php?page=objek" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Objek
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="edit-3" class="w-8 h-8 text-blue-600"></i> Edit Objek: <?= e($objek['nama_objek']) ?>
        </h1>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <form method="POST" action="index.php" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        <input type="hidden" name="aksi_update_objek" value="1">
        <input type="hidden" name="objek_id" value="<?= $objek['id'] ?>">
        <input type="hidden" name="jenis_objek" value="<?= $objek['jenis_objek'] ?>">

        <!-- IDENTITAS UTAMA -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Objek</label>
                <input type="text" name="nama_objek" required value="<?= e($objek['nama_objek']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi (Wilayah)</label>
                <input type="text" name="lokasi" value="<?= e($objek['lokasi']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Area Pantau</label>
                <select name="area_id" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Tanpa Area --</option>
                    <?php foreach($areas as $ar): ?>
                        <option value="<?= $ar['id'] ?>" <?= $ar['id'] == $objek['area_id'] ? 'selected' : '' ?>><?= e($ar['nama_area']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Visibilitas</label>
                <select name="visibility" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="publik" <?= $objek['visibility']=='publik'?'selected':'' ?>>🌍 Publik</option>
                    <option value="teman" <?= $objek['visibility']=='teman'?'selected':'' ?>>👥 Teman</option>
                    <option value="privat" <?= $objek['visibility']=='privat'?'selected':'' ?>>🔒 Privat</option>
                </select>
            </div>
        </div>

        <!-- KOORDINAT & PETA -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Koordinat GPS</label>
                <input type="text" name="koordinat" id="input_koordinat" value="<?= e($objek['koordinat']) ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Geser Pin untuk Mengubah Lokasi</label>
            <div id="map" class="w-full h-64 rounded-2xl border-2 border-slate-300 relative z-0"></div>
        </div>

        <!-- FIELD DINAMIS (BERDASARKAN JENIS) -->
        <?php if($objek['jenis_objek'] === 'pohon'): ?>
        <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100 space-y-4">
            <h3 class="text-sm font-bold text-emerald-800 border-b border-emerald-200 pb-2">Data Pohon</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nama_latin" value="<?= e($objek['nama_latin']) ?>" placeholder="Nama Latin" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="famili" value="<?= e($objek['famili']) ?>" placeholder="Famili" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="asal_daerah" value="<?= e($objek['asal_daerah']) ?>" placeholder="Asal Daerah" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="nama_daerah_lain" value="<?= e($objek['nama_daerah_lain']) ?>" placeholder="Nama Daerah Lain" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="date" name="tanggal_tanam" value="<?= e($objek['tanggal_tanam']) ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <select name="status_pohon" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Status Pohon --</option>
                    <?php foreach(['liar','ditanam','dibudidayakan','penghijauan','konservasi','lainnya'] as $s): ?>
                        <option value="<?= $s ?>" <?= $objek['status_pohon']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="kategori_ias" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Kategori IAS --</option>
                    <?php foreach(['IAS','Non-IAS','Belum Diketahui'] as $s): ?>
                        <option value="<?= $s ?>" <?= $objek['kategori_ias']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs mt-2">
                <?php $kar = ['berbiji'=>'Berbiji', 'berbuah'=>'Berbuah', 'berbunga'=>'Berbunga', 'akar_tunggang'=>'Akar Tunggang', 'berkayu'=>'Berkayu', 'evergreen'=>'Evergreen']; foreach($kar as $k=>$v): ?>
                    <label class="flex items-center gap-1"><input type="checkbox" name="karakteristik[]" value="<?= $k ?>" <?= in_array($k, $karakteristik)?'checked':'' ?>> <?= $v ?></label>
                <?php endforeach; ?>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs mt-2">
                <?php $man = ['ekologis'=>'Ekologis', 'ekonomi'=>'Ekonomi', 'pangan'=>'Pangan', 'obat'=>'Obat']; foreach($man as $k=>$v): ?>
                    <label class="flex items-center gap-1"><input type="checkbox" name="manfaat[]" value="<?= $k ?>" <?= in_array($k, $manfaat)?'checked':'' ?>> <?= $v ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif($objek['jenis_objek'] === 'sungai'): ?>
        <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100 space-y-4">
            <h3 class="text-sm font-bold text-blue-800 border-b border-blue-200 pb-2">Data Sungai</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nama_lain_sungai" value="<?= e($objek['nama_lain_sungai']) ?>" placeholder="Nama Lain Sungai" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="sumber_mata_air" value="<?= e($objek['sumber_mata_air']) ?>" placeholder="Sumber Mata Air" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="panjang_sungai" value="<?= e($objek['panjang_sungai']) ?>" placeholder="Panjang (km)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="lebar_rata_rata" value="<?= e($objek['lebar_rata_rata']) ?>" placeholder="Lebar (m)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="kedalaman_rata_rata" value="<?= e($objek['kedalaman_rata_rata']) ?>" placeholder="Kedalaman (m)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="daerah_hulu" value="<?= e($objek['daerah_hulu']) ?>" placeholder="Daerah Hulu" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="daerah_hilir" value="<?= e($objek['daerah_hilir']) ?>" placeholder="Daerah Hilir" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="muara" value="<?= e($objek['muara']) ?>" placeholder="Muara" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs mt-2">
                <?php $ds = ['Berbatu', 'Berlumpur', 'Berpasir']; foreach($ds as $d): ?>
                    <label class="flex items-center gap-1"><input type="checkbox" name="dasar_sungai[]" value="<?= $d ?>" <?= in_array($d, $dasar_sungai)?'checked':'' ?>> <?= $d ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif($objek['jenis_objek'] === 'komposting'): ?>
        <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100 space-y-4">
            <h3 class="text-sm font-bold text-amber-800 border-b border-amber-200 pb-2">Data Komposting</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="kompos_pengelola" value="<?= e($objek['kompos_pengelola']) ?>" placeholder="Pengelola" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <select name="kompos_metode" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Metode --</option>
                    <?php foreach(['Aerob','Anaerob','Takakura','Bokashi','Vermikomposting','Windrow','Biopori','Drum','Lainnya'] as $s): ?>
                        <option value="<?= $s ?>" <?= $objek['kompos_metode']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="0.01" name="kompos_kapasitas" value="<?= e($objek['kompos_kapasitas']) ?>" placeholder="Kapasitas (kg)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" name="kompos_jumlah_komposter" value="<?= e($objek['kompos_jumlah_komposter']) ?>" placeholder="Jumlah Komposter" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs mt-2">
                <?php $bh = ['Daun', 'Rumput', 'Sisa makanan', 'Sampah dapur', 'Limbah pertanian']; foreach($bh as $b): ?>
                    <label class="flex items-center gap-1"><input type="checkbox" name="kompos_bahan_organik_arr[]" value="<?= $b ?>" <?= in_array($b, $kompos_bahan)?'checked':'' ?>> <?= $b ?></label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan</label>
            <textarea name="catatan" rows="3" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= e($objek['catatan']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Ganti Foto (Opsional)</label>
            <?php if($objek['foto_pertama']): ?>
                <img src="uploads/<?= e($objek['foto_pertama']) ?>" class="h-32 rounded-lg mb-2 border border-slate-200">
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white">
        </div>

        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-blue-700 transition flex justify-center items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Simpan Perubahan
        </button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil koordinat awal dari input
    var coordStr = document.getElementById('input_koordinat').value;
    var lat = -2.548926, lng = 118.0148634; // Default Indonesia
    if (coordStr) {
        var parts = coordStr.split(',');
        if (parts.length === 2) {
            lat = parseFloat(parts[0].trim());
            lng = parseFloat(parts[1].trim());
        }
    }

    var map = L.map('map').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    marker.on('dragend', function(e) {
        var pos = marker.getLatLng();
        document.getElementById('input_koordinat').value = pos.lat.toFixed(6) + ', ' + pos.lng.toFixed(6);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('input_koordinat').value = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
    });
});
</script>
