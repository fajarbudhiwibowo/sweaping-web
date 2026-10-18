<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

// 1. Ambil Data Area Pantau (Poligon)
$q_area = mysqli_query($conn, "SELECT id, nama_area, jenis, geojson_polygon, luas_m2 FROM area_pantau WHERE deleted_at IS NULL AND geojson_polygon IS NOT NULL");
$areas_data = [];
while ($row = mysqli_fetch_assoc($q_area)) {
    $areas_data[] = $row;
}

// 2. Ambil Data Objek (Titik)
// Hanya menampilkan objek publik atau milik user sendiri
$uid = (int)$_SESSION['user_id'];
$stmt_o = mysqli_prepare($conn, "SELECT id, kode_objek, nama_objek, jenis_objek, koordinat, status FROM objek WHERE deleted_at IS NULL AND status='aktif' AND (visibility='publik' OR user_id=?)");
mysqli_stmt_bind_param($stmt_o, 'i', $uid);
mysqli_stmt_execute($stmt_o);
$q_objek = mysqli_stmt_get_result($stmt_o);
$objek_data = [];
while ($row = mysqli_fetch_assoc($q_objek)) {
    $objek_data[] = $row;
}
mysqli_stmt_close($stmt_o);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<main class="max-w-7xl mx-auto px-4 pt-24 pb-16 h-[calc(100vh-80px)] flex flex-col">
    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="map" class="w-8 h-8 text-emerald-600"></i> Peta Sebaran Terpadu
            </h1>
            <p class="text-slate-500 mt-1">Visualisasi titik objek dan batas wilayah area pantau.</p>
        </div>
        <div class="flex gap-2 text-xs font-bold text-slate-600 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Pohon</div>
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Sungai</div>
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Kompos</div>
            <div class="flex items-center gap-1 ml-2"><span class="w-3 h-3 rounded bg-emerald-500 opacity-30 border border-emerald-500"></span> Area</div>
        </div>
    </div>
    
    <div class="flex-1 rounded-3xl overflow-hidden shadow-xl border-2 border-slate-100 relative z-0">
        <div id="map" class="w-full h-full min-h-[500px]"></div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Init Map
    var map = L.map('map').setView([-2.548926, 118.0148634], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Layer Groups
    var objekLayer = L.layerGroup().addTo(map);
    var areaLayer = L.layerGroup().addTo(map);

    // ==============================
    // 1. GAMBAR TITIK OBJEK (MARKER)
    // ==============================
    var objekData = <?= json_encode($objek_data) ?>;
    
    objekData.forEach(function(o) {
        if (o.koordinat && o.koordinat !== '') {
            var parts = o.koordinat.split(',');
            if (parts.length === 2) {
                var lat = parseFloat(parts[0]);
                var lng = parseFloat(parts[1]);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    // Warna berdasarkan jenis
                    var color = '#10b981'; // emerald
                    if (o.jenis_objek === 'sungai') color = '#3b82f6';
                    if (o.jenis_objek === 'komposting') color = '#f59e0b';
                    
                    var marker = L.circleMarker([lat, lng], {
                        radius: 8, color: 'white', weight: 2, fillColor: color, fillOpacity: 1
                    }).addTo(objekLayer);

                    var popupContent = `
                        <div class="text-center">
                            <b class="text-sm">${o.nama_objek}</b><br>
                            <span class="text-xs text-gray-500 uppercase">${o.kode_objek}</span><br>
                            <a href="index.php?page=objek_profil&id=${o.id}" class="inline-block mt-2 px-3 py-1 bg-emerald-600 text-white text-xs font-bold rounded">Lihat Profil</a>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                }
            }
        }
    });

    // ==============================
    // 2. GAMBAR AREA PANTAU (POLIGON)
    // ==============================
    var areaData = <?= json_encode($areas_data) ?>;
    
    areaData.forEach(function(a) {
        try {
            var geojson = JSON.parse(a.geojson_polygon);
            
            // Warna berdasarkan jenis area
            var fillColor = '#10b981'; // Taman/RTH (emerald)
            if (a.jenis === 'sungai' || a.jenis === 'das') fillColor = '#3b82f6'; // Biru
            if (a.jenis === 'hutan_kota') fillColor = '#047857'; // Hijau gelap
            
            var layer = L.geoJSON(geojson, {
                style: {
                    color: fillColor,
                    weight: 2,
                    fillColor: fillColor,
                    fillOpacity: 0.2
                }
            }).addTo(areaLayer);

            // Tambahkan Label di tengah area
            var center = layer.getBounds().getCenter();
            var popupContent = `
                <div class="text-center">
                    <b class="text-sm">${a.nama_area}</b><br>
                    <span class="text-xs text-gray-500">${parseInt(a.luas_m2).toLocaleString('id-ID')} m²</span><br>
                    <a href="index.php?page=area_detail&id=${a.id}" class="inline-block mt-2 px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded">Lihat Detail Area</a>
                </div>
            `;
            layer.bindPopup(popupContent);
            
            // Tambahkan Tooltip permanen (nama area) agar terlihat jelas
            layer.bindTooltip(a.nama_area, {permanent: true, direction: 'center', className: 'bg-white/80 border-none shadow-none text-xs font-bold text-slate-700'});

        } catch(e) {
            console.error('Gagal load area', a.id, e);
        }
    });

    // ==============================
    // 3. LAYER CONTROL (TOGGLE)
    // ==============================
    var overlays = {
        "📍 Titik Objek": objekLayer,
        "🗺️ Area Pantau": areaLayer
    };
    L.control.layers(null, overlays, {position: 'topright'}).addTo(map);
});
</script>
