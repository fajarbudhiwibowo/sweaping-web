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
            <div class="mb-4">
    <label class="block text-sm font-semibold mb-2">Pilih Metode Penggambaran</label>
    <div class="flex gap-4">
        <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 border-2 border-slate-200 rounded-lg hover:border-emerald-500 transition">
            <input type="radio" name="mode_gambar" value="poligon" checked onchange="ubahModeGambar()">
            <span class="text-sm font-bold text-slate-700">Poligon (Area Tertutup)</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 border-2 border-slate-200 rounded-lg hover:border-emerald-500 transition">
            <input type="radio" name="mode_gambar" value="tracking" onchange="ubahModeGambar()">
            <span class="text-sm font-bold text-slate-700">Tracking (Garis / Jalur)</span>
        </label>
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

        <div class="bg-emerald-50 rounded-xl p-4 border-2 border-emerald-200 mt-4 flex justify-between items-center">
    <div>
        <span class="text-sm font-bold text-emerald-800 block" id="label_hasil">Luas Terhitung</span>
        <span id="tampil_hasil" class="text-2xl font-extrabold text-emerald-600">0</span>
    </div>
    <button type="button" onclick="resetGambar()" class="text-xs bg-white text-rose-500 font-bold px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 transition">Hapus & Ulangi</button>
</div>

        <button type="submit" name="aksi_simpan_area" <?= !$my_org_id ? 'disabled' : '' ?> class="w-full py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-emerald-700 transition flex justify-center items-center gap-2 <?= !$my_org_id ? 'opacity-50 cursor-not-allowed' : '' ?>">
            <i data-lucide="save" class="w-5 h-5"></i> Simpan Area Pantau
        </button>
        <input type="hidden" name="luas_m2" id="in_luas_m2" value="0">
<input type="hidden" name="panjang_m" id="in_panjang_m" value="0">
<input type="hidden" name="geojson_polygon" id="in_geojson" value="">
    </form>
</main>

<script>
    var map = L.map('map').setView([-2.5, 118], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    
    var markers = [];
    var polyline = null;
    var polygon = null;
    var latlngs = [];

    map.on('click', function(e) {
        latlngs.push(e.latlng);
        
        // Tambah titik (marker)
        var marker = L.marker(e.latlng).addTo(map);
        markers.push(marker);

        // Hapus garis sebelumnya
        if (polyline) map.removeLayer(polyline);
        if (polygon) map.removeLayer(polygon);

        var mode = document.querySelector('input[name="mode_gambar"]:checked').value;

        if (latlngs.length > 1) {
            // Gambar garis
            polyline = L.polyline(latlngs, {color: 'blue'}).addTo(map);
            
            // Jika mode poligon dan titik > 2, gambar area tertutup
            if (mode === 'poligon' && latlngs.length > 2) {
                polygon = L.polygon(latlngs, {color: 'green', fillOpacity: 0.3}).addTo(map);
            }
        }
        
        hitungArea();
    });

    function ubahModeGambar() {
        resetGambar();
    }

    function resetGambar() {
        latlngs = [];
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        if (polyline) map.removeLayer(polyline);
        if (polygon) map.removeLayer(polygon);
        polyline = null; polygon = null;
        document.getElementById('in_luas_m2').value = 0;
        document.getElementById('in_panjang_m').value = 0;
        document.getElementById('in_geojson').value = "";
        hitungArea();
    }

    function hitungArea() {
        var mode = document.querySelector('input[name="mode_gambar"]:checked').value;
        var label = document.getElementById('label_hasil');
        var tampil = document.getElementById('tampil_hasil');

        if (latlngs.length < 2) {
            label.innerText = mode === 'poligon' ? 'Luas Terhitung' : 'Panjang Terhitung';
            tampil.innerText = '0';
            return;
        }

        if (mode === 'poligon') {
            // Hitung Luas (m2)
            if (latlngs.length < 3) return;
            var area = L.GeometryUtil ? L.GeometryUtil.geodesicArea(latlngs) : 0;
            document.getElementById('in_luas_m2').value = area.toFixed(2);
            document.getElementById('in_panjang_m').value = 0;
            label.innerText = 'Luas Area';
            tampil.innerText = area.toLocaleString('id-ID') + ' m² (' + (area/10000).toFixed(2) + ' Ha)';
            
            // Simpan GeoJSON (Polygon)
            var geojson = polygon.toGeoJSON();
            document.getElementById('in_geojson').value = JSON.stringify(geojson);
            
        } else {
            // Hitung Panjang (meter)
            var totalMeter = 0;
            for (var i = 0; i < latlngs.length - 1; i++) {
                totalMeter += map.distance(latlngs[i], latlngs[i+1]);
            }
            document.getElementById('in_luas_m2').value = 0;
            document.getElementById('in_panjang_m').value = totalMeter.toFixed(2);
            label.innerText = 'Panjang Jalur';
            tampil.innerText = totalMeter.toLocaleString('id-ID', {maximumFractionDigits: 2}) + ' meter';
            
            // Simpan GeoJSON (LineString)
            var geojson = polyline.toGeoJSON();
            document.getElementById('in_geojson').value = JSON.stringify(geojson);
        }
    }
</script>
