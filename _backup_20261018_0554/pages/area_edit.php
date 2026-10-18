<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }
$area_id = (int)($_GET['id'] ?? 0);
$my_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT a.*, o.pic_user_id FROM area_pantau a LEFT JOIN organisasi o ON a.organisasi_id = o.id WHERE a.id = ? AND a.deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $area_id);
mysqli_stmt_execute($stmt);
$area = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$area) { redirect('index.php?page=area'); }

$can_edit = is_admin() || ($area['pic_user_id'] == $my_id) || ($area['created_by'] == $my_id);
if (!$can_edit) { 
    $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda tidak berhak mengedit area ini.'];
    redirect('index.php?page=area'); 
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<main class="max-w-5xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-6">
        <a href="index.php?page=area" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Area
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="edit-3" class="w-8 h-8 text-blue-600"></i> Edit Area Pantau
        </h1>
    </div>

    <form method="POST" action="index.php" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        <input type="hidden" name="aksi_update_area" value="1">
        <input type="hidden" name="area_id" value="<?= $area['id'] ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Area</label>
                <input type="text" name="nama_area" required value="<?= e($area['nama_area']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Area</label>
                <select name="jenis" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <?php 
                    $jenis_list = ['taman'=>'Taman', 'rth'=>'RTH', 'das'=>'DAS', 'sungai'=>'Sungai Panjang', 'hutan_kota'=>'Hutan Kota', 'lainnya'=>'Lainnya'];
                    foreach($jenis_list as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $area['jenis'] == $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Singkat</label>
            <textarea name="deskripsi" rows="2" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= e($area['deskripsi']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Gambar Ulang Poligon Area</label>
            <div class="flex flex-col md:flex-row gap-2 mb-3">
                <div class="relative flex-1">
                    <input type="text" id="search_alamat" placeholder="Ketik nama tempat..." class="w-full pl-10 pr-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
                <button type="button" onclick="cariAlamat()" class="px-5 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition">Cari</button>
                <button type="button" onclick="lokasiSaya()" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2"><i data-lucide="locate-fixed" class="w-4 h-4"></i> Lokasi Saya</button>
            </div>
            <div id="map" class="w-full h-96 rounded-2xl border-2 border-slate-300 relative z-0"></div>
            <p class="text-xs text-slate-500 mt-2">Hapus poligon lama (ikon tempat sampah) dan gambar baru jika ingin mengubah batas area.</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-sm font-bold text-blue-800">Luas Saat Ini:</span>
                <div id="tampil_luas" class="text-2xl font-extrabold text-blue-600"><?= number_format($area['luas_m2'], 0, ',', '.') ?> m² (<?= number_format($area['luas_m2']/10000, 2) ?> Ha)</div>
            </div>
            <input type="hidden" name="geojson_polygon" id="input_geojson" value="<?= e($area['geojson_polygon']) ?>" required>
            <input type="hidden" name="luas_m2" id="input_luas_m2" value="<?= $area['luas_m2'] ?>" required>
        </div>

        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-blue-700 transition flex justify-center items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Simpan Perubahan Area
        </button>
    </form>
</main>

<script>
var map;
var currentLocMarker = null;
var existingPolygon = null;

document.addEventListener('DOMContentLoaded', function() {
    map = L.map('map').setView([-2.548926, 118.0148634], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Muat Poligon Lama jika ada
    var geojsonStr = document.getElementById('input_geojson').value;
    if (geojsonStr && geojsonStr !== '') {
        try {
            var geojsonData = JSON.parse(geojsonStr);
            existingPolygon = L.geoJSON(geojsonData, { style: { color: 'blue', fillOpacity: 0.3 } }).addTo(map);
            map.fitBounds(existingPolygon.getBounds());
        } catch(e) { console.log('Error loading GeoJSON', e); }
    }

    map.pm.addControls({
        position: 'topleft',
        drawMarker: false, drawCircleMarker: false, drawPolyline: false, drawRectangle: false, drawCircle: false,
        drawPolygon: true, editMode: true, dragMode: false, cutPolygon: false, removalMode: true,
    });

    map.on('pm:create', function(e) {
        var layer = e.layer;
        var geojson = layer.toGeoJSON();
        var areaM2 = turf.area(geojson);
        var areaHa = areaM2 / 10000;

        document.getElementById('input_geojson').value = JSON.stringify(geojson);
        document.getElementById('input_luas_m2').value = areaM2.toFixed(2);
        document.getElementById('tampil_luas').innerText = Math.round(areaM2).toLocaleString('id-ID') + ' m² (' + areaHa.toFixed(2) + ' Ha)';

        // Hapus polygon lama
        if (existingPolygon) { map.removeLayer(existingPolygon); existingPolygon = null; }
        map.eachLayer(function(l) {
            if (l instanceof L.Polygon && l !== layer) { map.removeLayer(l); }
        });
    });
});

function cariAlamat() {
    var q = document.getElementById('search_alamat').value;
    if (q === '') return;
    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q))
        .then(r => r.json()).then(data => {
            if (data.length > 0) map.setView([data[0].lat, data[0].lon], 16);
            else alert('Lokasi tidak ditemukan.');
        });
}

function lokasiSaya() {
    if (navigator.geolocation) {
        alert('Mengambil lokasi Anda...');
        navigator.geolocation.getCurrentPosition(pos => {
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            map.setView([lat, lng], 17);
            if (currentLocMarker) map.removeLayer(currentLocMarker);
            currentLocMarker = L.marker([lat, lng]).addTo(map).bindPopup('Lokasi Anda').openPopup();
        }, err => alert('Gagal: ' + err.message), { enableHighAccuracy: true });
    }
}
</script>
