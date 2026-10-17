<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$my_id = (int)$_SESSION['user_id'];
// Cek Organisasi User (untuk peringatan UI)
$stmt_org = mysqli_prepare($conn, "SELECT organisasi_id FROM organisasi_anggota WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_org, 'i', $my_id);
mysqli_stmt_execute($stmt_org);
$res_org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_org));
$my_org_id = $res_org ? (int)$res_org['organisasi_id'] : null;
mysqli_stmt_close($stmt_org);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<main class="max-w-5xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="map" class="w-8 h-8 text-emerald-600"></i> Tambah Area Pantau
        </h1>
        <p class="text-slate-500 mt-2">Gambar batas wilayah (poligon) pada peta. Luas akan dihitung otomatis.</p>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200">
            <?= e($_SESSION['notif']['pesan']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <?php if(!$my_org_id): ?>
        <div class="bg-amber-50 text-amber-800 p-4 rounded-xl mb-6 font-bold border border-amber-200 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i> Anda harus tergabung dalam Organisasi terlebih dahulu untuk membuat Area Pantau.
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Area</label>
                <input type="text" name="nama_area" required placeholder="Contoh: Taman Kota Blok C" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Area</label>
                <select name="jenis" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="taman">Taman</option>
                    <option value="rth">Ruang Terbuka Hijau (RTH)</option>
                    <option value="das">Daerah Aliran Sungai (DAS)</option>
                    <option value="sungai">Sungai Panjang</option>
                    <option value="hutan_kota">Hutan Kota</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Singkat</label>
            <textarea name="deskripsi" rows="2" placeholder="Deskripsi area..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Gambar Poligon Area</label>
            
            <!-- Fitur Pencarian & Lokasi Saat Ini -->
            <div class="flex flex-col md:flex-row gap-2 mb-3">
                <div class="relative flex-1">
                    <input type="text" id="search_alamat" placeholder="Ketik nama tempat/alamat (misal: Monas Jakarta)..." class="w-full pl-10 pr-4 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
                <button type="button" onclick="cariAlamat()" class="px-5 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition">Cari</button>
                <button type="button" onclick="lokasiSaya()" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i data-lucide="locate-fixed" class="w-4 h-4"></i> Lokasi Saya
                </button>
            </div>

            <div id="map" class="w-full h-96 rounded-2xl border-2 border-slate-300 relative z-0"></div>
            <p class="text-xs text-slate-500 mt-2">Klik ikon <b>Polygon (bentuk segi banyak)</b> di pojok kiri atas peta, lalu tap beberapa titik untuk menggambar batas area. Tap dua kali di titik terakhir untuk menyelesaikan.</p>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-sm font-bold text-emerald-800">Luas Terhitung:</span>
                <div id="tampil_luas" class="text-2xl font-extrabold text-emerald-600">0 m² (0.00 Ha)</div>
            </div>
            <input type="hidden" name="geojson_polygon" id="input_geojson" required>
            <input type="hidden" name="luas_m2" id="input_luas_m2" required>
        </div>

        <button type="submit" name="aksi_simpan_area" <?= !$my_org_id ? 'disabled' : '' ?> class="w-full py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-emerald-700 transition flex justify-center items-center gap-2 <?= !$my_org_id ? 'opacity-50 cursor-not-allowed' : '' ?>">
            <i data-lucide="save" class="w-5 h-5"></i> Simpan Area Pantau
        </button>
    </form>
</main>

<script>
var map;
var currentLocMarker = null;

document.addEventListener('DOMContentLoaded', function() {
    map = L.map('map').setView([-2.548926, 118.0148634], 5); // Default: Indonesia
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

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

        map.eachLayer(function(l) {
            if (l instanceof L.Polygon && l !== layer) { map.removeLayer(l); }
        });
    });
});

function cariAlamat() {
    var q = document.getElementById('search_alamat').value;
    if (q === '') return;

    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q))
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                var lat = data[0].lat;
                var lon = data[0].lon;
                map.setView([lat, lon], 16);
            } else {
                alert('Lokasi tidak ditemukan. Coba kata kunci lain.');
            }
        })
        .catch(err => alert('Gagal mencari lokasi: ' + err.message));
}

function lokasiSaya() {
    if (navigator.geolocation) {
        alert('Mengambil lokasi Anda...');
        navigator.geolocation.getCurrentPosition(
            (position) => {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                map.setView([lat, lng], 17);
                
                if (currentLocMarker) { map.removeLayer(currentLocMarker); }
                currentLocMarker = L.marker([lat, lng]).addTo(map)
                    .bindPopup('<b>Lokasi Anda Saat Ini</b><br>Akurasi: ±' + Math.round(position.coords.accuracy) + ' meter')
                    .openPopup();
            },
            (error) => {
                alert('Gagal mengambil lokasi. Pastikan GPS aktif dan izin browser diberikan. Error: ' + error.message);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        alert('Browser Anda tidak mendukung Geolocation.');
    }
}
</script>
