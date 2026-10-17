<?php
$q_peta = mysqli_query($conn, "SELECT nama_objek, koordinat, jenis_objek, status_kondisi FROM laporan_aksi WHERE deleted_at IS NULL AND koordinat IS NOT NULL AND koordinat != ''");
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<main class="max-w-7xl mx-auto px-4 pt-24 pb-16 h-[calc(100vh-80px)] flex flex-col">
    <div class="mb-4">
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="map" class="w-8 h-8 text-emerald-600"></i> Peta Sebaran Aksi
        </h1>
        <p class="text-slate-500 mt-1">Visualisasi titik pemantauan lingkungan dari seluruh Indonesia.</p>
    </div>
    
    <div class="flex-1 rounded-3xl overflow-hidden shadow-xl border-2 border-slate-100 relative z-0">
        <div id="map" class="w-full h-full min-h-[500px]"></div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([-2.548926, 118.0148634], 5);
    
    // MENGGUNAKAN OPENSTREETMAP MURNI (100% GRATIS TANPA API KEY)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    <?php
    if (mysqli_num_rows($q_peta) > 0) {
        while ($p = mysqli_fetch_assoc($q_peta)) {
            $parts = array_map('trim', explode(',', $p['koordinat']));
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $lat = $parts[0];
                $lng = $parts[1];
                $nama = addslashes(htmlspecialchars($p['nama_objek']));
                $status = addslashes(htmlspecialchars($p['status_kondisi']));
                $jenis = $p['jenis_objek'];
                
                $warna = '#10b981'; // emerald
                if ($jenis == 'sungai') $warna = '#3b82f6'; // blue
                if ($jenis == 'taman') $warna = '#84cc16'; // lime
                if ($jenis == 'komposting') $warna = '#f59e0b'; // amber
                
                echo "L.circleMarker([$lat, $lng], {radius: 8, color: 'white', weight: 2, fillColor: '$warna', fillOpacity: 1}).addTo(map).bindPopup('<div class=\"text-center\"><b class=\"text-sm\">$nama</b><br><span class=\"text-xs text-gray-500\">$status</span></div>');\n";
            }
        }
    }
    ?>
});
</script>
