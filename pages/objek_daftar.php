<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$uid = (int)$_SESSION['user_id'];
$error = '';

// Ambil Organisasi User
$stmt_org = mysqli_prepare($conn, "SELECT organisasi_id FROM organisasi_anggota WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_org, 'i', $uid);
mysqli_stmt_execute($stmt_org);
$res_org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_org));
$my_org_id = $res_org ? (int)$res_org['organisasi_id'] : null;
mysqli_stmt_close($stmt_org);

// Ambil Data Area untuk Auto-Detect Peta
$q_areas = mysqli_query($conn, "SELECT id, nama_area, geojson_polygon FROM area_pantau WHERE deleted_at IS NULL AND geojson_polygon IS NOT NULL");
$areas_json = [];
while ($row = mysqli_fetch_assoc($q_areas)) {
    $areas_json[] = $row;
}

// Proses Simpan Objek
if (isset($_POST['aksi_daftar_objek'])) {
    $jenis_objek = $_POST['jenis_objek'] ?? '';
    $nama_objek = trim($_POST['nama_objek'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $koordinat = trim($_POST['koordinat'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $visibility = $_POST['visibility'] ?? 'publik';
    $area_id = (int)($_POST['area_id'] ?? 0) ?: null;

    if (!in_array($jenis_objek, ['pohon','sungai','komposting'])) {
        $error = 'Jenis objek tidak valid.';
    } elseif ($nama_objek === '') {
        $error = 'Nama objek wajib diisi.';
    } else {
        $prefix_map = ['pohon'=>'TRB', 'sungai'=>'SNG', 'komposting'=>'KMP'];
        $prefix = $prefix_map[$jenis_objek];
        $q_last = mysqli_query($conn, "SELECT kode_objek FROM objek WHERE kode_objek LIKE '$prefix%' ORDER BY id DESC LIMIT 1");
        $last = mysqli_fetch_assoc($q_last);
        $next_num = $last ? ((int)substr($last['kode_objek'], -5) + 1) : 1;
        $kode_objek = $prefix . '-' . date('Y') . '-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);

        $to_json = function($post_key) {
            $arr = $_POST[$post_key] ?? [];
            return !empty($arr) ? json_encode(array_values($arr)) : null;
        };

        $nama_latin = trim($_POST['nama_latin'] ?? '') ?: null;
        $famili = trim($_POST['famili'] ?? '') ?: null;
        $asal_daerah = trim($_POST['asal_daerah'] ?? '') ?: null;
        $nama_daerah_lain = trim($_POST['nama_daerah_lain'] ?? '') ?: null;
        $status_pohon = trim($_POST['status_pohon'] ?? '') ?: null;
        $kategori_ias = trim($_POST['kategori_ias'] ?? '') ?: null;
        $potensi_pemanfaatan = trim($_POST['potensi_pemanfaatan'] ?? '') ?: null;
        $karakteristik = $to_json('karakteristik');
        $manfaat = $to_json('manfaat');
        $tanggal_tanam = trim($_POST['tanggal_tanam'] ?? '') ?: null;
        $alasan_penanaman = trim($_POST['alasan_penanaman'] ?? '') ?: null;
        $penyelenggara = trim($_POST['penyelenggara'] ?? '') ?: null;
        $pelaksana = trim($_POST['pelaksana'] ?? '') ?: null;
        $sumber_dana = trim($_POST['sumber_dana'] ?? '') ?: null;
        $jenis_bibit = trim($_POST['jenis_bibit'] ?? '') ?: null;
        $asal_bibit = trim($_POST['asal_bibit'] ?? '') ?: null;
        $pic_monev_nama = trim($_POST['pic_monev_nama'] ?? '') ?: null;
        $pic_monev_peran = trim($_POST['pic_monev_peran'] ?? '') ?: null;
        
        $nama_lain_sungai = trim($_POST['nama_lain_sungai'] ?? '') ?: null;
        $sumber_mata_air = trim($_POST['sumber_mata_air'] ?? '') ?: null;
        $panjang_sungai = (float)($_POST['panjang_sungai'] ?? 0) ?: null;
        $lebar_rata_rata = (float)($_POST['lebar_rata_rata'] ?? 0) ?: null;
        $kedalaman_rata_rata = (float)($_POST['kedalaman_rata_rata'] ?? 0) ?: null;
        $daerah_hulu = trim($_POST['daerah_hulu'] ?? '') ?: null;
        $daerah_hilir = trim($_POST['daerah_hilir'] ?? '') ?: null;
        $muara = trim($_POST['muara'] ?? '') ?: null;
        $jenis_sungai = trim($_POST['jenis_sungai'] ?? '') ?: null;
        $dasar_sungai = $to_json('dasar_sungai');
        $ada_vegetasi = isset($_POST['ada_vegetasi']) ? (int)$_POST['ada_vegetasi'] : null;
        $ada_sempadan = isset($_POST['ada_sempadan']) ? (int)$_POST['ada_sempadan'] : null;
        $pemanfaatan_sungai = $to_json('pemanfaatan_sungai');

        $kompos_pengelola = trim($_POST['kompos_pengelola'] ?? '') ?: null;
        $kompos_metode = trim($_POST['kompos_metode'] ?? '') ?: null;
        $kompos_kapasitas = (float)($_POST['kompos_kapasitas'] ?? 0) ?: null;
        $kompos_luas_area = (float)($_POST['kompos_luas_area'] ?? 0) ?: null;
        $kompos_tahun_mulai = (int)($_POST['kompos_tahun_mulai'] ?? 0) ?: null;
        $kompos_jumlah_komposter = (int)($_POST['kompos_jumlah_komposter'] ?? 0) ?: null;
        $kompos_kelompok = trim($_POST['kompos_kelompok'] ?? '') ?: null;
        $kompos_bahan_organik = $to_json('kompos_bahan_organik_arr');

        $nama_foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nama_foto = 'objek_' . $kode_objek . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $nama_foto);
        }

        $sql = "INSERT INTO objek (
            kode_objek, jenis_objek, nama_objek, lokasi, koordinat, catatan, user_id, organisasi_id, area_id, visibility, foto_pertama,
            nama_latin, famili, asal_daerah, nama_daerah_lain, status_pohon, kategori_ias, potensi_pemanfaatan, karakteristik, manfaat,
            tanggal_tanam, alasan_penanaman, penyelenggara, pelaksana, sumber_dana, jenis_bibit, asal_bibit, pic_monev_nama, pic_monev_peran,
            nama_lain_sungai, sumber_mata_air, panjang_sungai, lebar_rata_rata, kedalaman_rata_rata, daerah_hulu, daerah_hilir, muara, jenis_sungai, dasar_sungai, ada_vegetasi, ada_sempadan, pemanfaatan_sungai,
            kompos_pengelola, kompos_metode, kompos_kapasitas, kompos_luas_area, kompos_tahun_mulai, kompos_jumlah_komposter, kompos_kelompok, kompos_bahan_organik
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            $error = "Query Prepare Gagal: " . mysqli_error($conn);
        } else {
            $params = [
                $kode_objek, $jenis_objek, $nama_objek, $lokasi, $koordinat, $catatan, $uid, $my_org_id, $area_id, $visibility, $nama_foto,
                $nama_latin, $famili, $asal_daerah, $nama_daerah_lain, $status_pohon, $kategori_ias, $potensi_pemanfaatan, $karakteristik, $manfaat,
                $tanggal_tanam, $alasan_penanaman, $penyelenggara, $pelaksana, $sumber_dana, $jenis_bibit, $asal_bibit, $pic_monev_nama, $pic_monev_peran,
                $nama_lain_sungai, $sumber_mata_air, $panjang_sungai, $lebar_rata_rata, $kedalaman_rata_rata, $daerah_hulu, $daerah_hilir, $muara, $jenis_sungai, $dasar_sungai, $ada_vegetasi, $ada_sempadan, $pemanfaatan_sungai,
                $kompos_pengelola, $kompos_metode, $kompos_kapasitas, $kompos_luas_area, $kompos_tahun_mulai, $kompos_jumlah_komposter, $kompos_kelompok, $kompos_bahan_organik
            ];
            
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p)) $types .= 'd';
                else $types .= 's';
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                if ($jenis_objek === 'pohon') cek_dan_update_misi($conn, $uid, 'daftar_objek_pohon');
                redirect('index.php?page=objek');
            } else {
                $error = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
            }
        }
    }
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<main class="max-w-4xl mx-auto px-4 pt-24 pb-16">
    <div class="mb-8">
        <a href="index.php?page=objek" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Objek
        </a>
        <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
            <i data-lucide="plus-circle" class="w-8 h-8 text-emerald-600"></i> Daftarkan Objek Baru
        </h1>
    </div>

    <?php if($error): ?>
        <div class="bg-rose-50 text-rose-700 p-4 rounded-xl mb-6 text-sm font-bold border border-rose-200"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=objek_daftar" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        
        <!-- JENIS OBJEK -->
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Jenis Objek</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <?php 
                $jenis_opts = [
                    'pohon' => ['icon' => 'tree-pine', 'label' => 'Pohon'],
                    'sungai' => ['icon' => 'waves', 'label' => 'Sungai'],
                    'komposting' => ['icon' => 'recycle', 'label' => 'Komposting']
                ];
                foreach($jenis_opts as $val => $opt): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis_objek" value="<?= $val ?>" class="peer hidden" onchange="toggleForm(this.value)" required>
                        <div class="p-4 border-2 border-slate-200 rounded-xl text-center peer-checked:border-emerald-600 peer-checked:bg-emerald-50 hover:bg-slate-50 transition">
                            <i data-lucide="<?= $opt['icon'] ?>" class="w-6 h-6 mx-auto mb-2 text-slate-500 peer-checked:text-emerald-600"></i>
                            <div class="text-sm font-bold text-slate-700"><?= $opt['label'] ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Objek</label>
                <input type="text" name="nama_objek" required placeholder="Contoh: Pohon Mangga Depan Rumah" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Area Pantau <span class="text-slate-400 font-normal">(Otomatis / Opsional)</span></label>
                <select name="area_id" id="select_area" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Tanpa Area --</option>
                    <?php foreach($areas_json as $ar): ?>
                        <option value="<?= $ar['id'] ?>"><?= e($ar['nama_area']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p id="info_area" class="text-xs text-emerald-600 font-bold mt-1 hidden">✔ Terdeteksi di dalam area ini!</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi (Wilayah)</label>
            <input type="text" name="lokasi" placeholder="Contoh: Kelurahan Sukamaju, Bandung" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Koordinat GPS</label>
                <div class="flex gap-2">
                    <input type="text" name="koordinat" id="input_koordinat" required placeholder="-6.123, 106.456" class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <button type="button" onclick="lokasiSaya()" class="px-4 py-3 bg-emerald-100 text-emerald-700 rounded-xl hover:bg-emerald-200 transition" title="Lokasi Saya"><i data-lucide="locate-fixed" class="w-5 h-5"></i></button>
                </div>
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

        <!-- MINI MAP -->
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Geser Pin atau Pilih Area untuk Zoom Otomatis</label>
            <div id="mini_map" class="w-full h-64 rounded-2xl border-2 border-slate-300 relative z-0"></div>
        </div>

        <!-- FORM POHON -->
        <div id="form_pohon" class="hidden bg-emerald-50 rounded-2xl p-5 border border-emerald-100 space-y-4">
            <h3 class="text-sm font-bold text-emerald-800 border-b border-emerald-200 pb-2">Data Pohon</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nama_latin" placeholder="Nama Latin" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="famili" placeholder="Famili" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="asal_daerah" placeholder="Asal Daerah" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="nama_daerah_lain" placeholder="Nama Daerah Lain" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <select name="status_pohon" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Status Pohon --</option>
                    <option value="liar">Liar</option>
                    <option value="ditanam">Ditanam</option>
                </select>
                <select name="kategori_ias" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Kategori IAS --</option>
                    <option value="IAS">IAS</option>
                    <option value="Non-IAS">Non-IAS</option>
                </select>
            </div>
            <textarea name="potensi_pemanfaatan" rows="2" placeholder="Potensi Pemanfaatan..." class="w-full px-3 py-2 border border-slate-200 rounded-lg mt-2"></textarea>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <input type="date" name="tanggal_tanam" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <select name="sumber_dana" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Sumber Dana --</option>
                    <option value="Swadaya">Swadaya</option>
                    <option value="APBD">APBD</option>
                </select>
            </div>
        </div>

        <!-- FORM SUNGAI -->
        <div id="form_sungai" class="hidden bg-blue-50 rounded-2xl p-5 border border-blue-100 space-y-4">
            <h3 class="text-sm font-bold text-blue-800 border-b border-blue-200 pb-2">Data Sungai</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="nama_lain_sungai" placeholder="Nama Lain Sungai" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="sumber_mata_air" placeholder="Sumber Mata Air" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="panjang_sungai" placeholder="Panjang (km)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" step="0.01" name="lebar_rata_rata" placeholder="Lebar Rata-rata (m)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <input type="text" name="daerah_hulu" placeholder="Daerah Hulu" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="text" name="daerah_hilir" placeholder="Daerah Hilir" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
        </div>

        <!-- FORM KOMPOSTING -->
        <div id="form_komposting" class="hidden bg-amber-50 rounded-2xl p-5 border border-amber-100 space-y-4">
            <h3 class="text-sm font-bold text-amber-800 border-b border-amber-200 pb-2">Data Komposting</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="kompos_pengelola" placeholder="Pengelola" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <select name="kompos_metode" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white">
                    <option value="">-- Metode --</option>
                    <option value="Aerob">Aerob</option>
                    <option value="Takakura">Takakura</option>
                </select>
                <input type="number" step="0.01" name="kompos_kapasitas" placeholder="Kapasitas (kg)" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                <input type="number" name="kompos_jumlah_komposter" placeholder="Jumlah Komposter" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
            </div>
        </div>

        <!-- MONEV & FOTO -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama PIC Monev</label>
                <input type="text" name="pic_monev_nama" value="<?= e($_SESSION['nama']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Peran PIC Monev</label>
                <select name="pic_monev_peran" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Pilih --</option>
                    <option value="Koordinator">Koordinator</option>
                    <option value="Peneliti">Peneliti</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Lapangan</label>
            <textarea name="catatan" rows="3" placeholder="Deskripsi singkat objek..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Foto Objek (Opsional)</label>
            <input type="file" name="foto" accept="image/*" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white">
        </div>

        <button type="submit" name="aksi_daftar_objek" class="w-full py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-emerald-700 transition flex justify-center items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Simpan Objek
        </button>
    </form>
</main>

<script>
var areasData = <?= json_encode($areas_json) ?>;
var miniMap, marker;
var areaLayers = {}; // Simpan referensi layer poligon

document.addEventListener('DOMContentLoaded', function() {
    // Init Map
    miniMap = L.map('mini_map').setView([-2.548926, 118.0148634], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(miniMap);

    // Gambar semua polygon area
    areasData.forEach(function(area) {
        if (area.geojson_polygon) {
            try {
                var poly = JSON.parse(area.geojson_polygon);
                var layer = L.geoJSON(poly, {
                    style: { color: '#3b82f6', weight: 2, fillOpacity: 0.1 },
                    onEachFeature: function (feature, layer) {
                        layer.bindTooltip(area.nama_area, {permanent: false, direction: 'center'});
                    }
                }).addTo(miniMap);
                areaLayers[area.id] = layer; // Simpan layer untuk zoom nanti
            } catch(e) {}
        }
    });

    // Init Marker
    marker = L.marker([-2.548926, 118.0148634], {draggable: true}).addTo(miniMap);
    
    // Event saat marker digeser
    marker.on('dragend', function(e) {
        updateLocation(marker.getLatLng());
    });

    // Event saat peta diklik
    miniMap.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateLocation(e.latlng);
    });

    // EVENT: Saat user memilih Area dari Dropdown
    document.getElementById('select_area').addEventListener('change', function() {
        var selectedId = this.value;
        
        if (selectedId && areaLayers[selectedId]) {
            // Zoom ke area yang dipilih
            var layer = areaLayers[selectedId];
            var bounds = layer.getBounds();
            miniMap.fitBounds(bounds);
            
            // Pindahkan Marker ke tengah area
            var center = bounds.getCenter();
            marker.setLatLng(center);
            
            // Update input koordinat
            document.getElementById('input_koordinat').value = center.lat.toFixed(6) + ', ' + center.lng.toFixed(6);
            
            // Sembunyikan info "Terdeteksi"
            document.getElementById('info_area').classList.add('hidden');
        } else if (!selectedId) {
            // Jika user pilih "Tanpa Area", kembalikan view ke Indonesia
            miniMap.setView([-2.548926, 118.0148634], 5);
        }
    });
});

function updateLocation(latlng) {
    var lat = latlng.lat;
    var lng = latlng.lng;
    
    // Update input
    document.getElementById('input_koordinat').value = lat.toFixed(6) + ', ' + lng.toFixed(6);
    
    // Cek Area Otomatis
    var point = turf.point([lng, lat]); // Turf pakai [lng, lat]
    var foundAreaId = '';
    
    areasData.forEach(function(area) {
        if (area.geojson_polygon) {
            try {
                var poly = JSON.parse(area.geojson_polygon);
                if (turf.booleanPointInPolygon(point, poly)) {
                    foundAreaId = area.id;
                }
            } catch(e) {}
        }
    });

    var select = document.getElementById('select_area');
    var info = document.getElementById('info_area');
    
    if (foundAreaId) {
        // Jika pin masuk ke area, update dropdown (tanpa memicu event change agar tidak loop)
        select.value = foundAreaId;
        info.classList.remove('hidden');
        select.classList.add('ring-2', 'ring-emerald-500');
    } else {
        select.value = '';
        info.classList.add('hidden');
        select.classList.remove('ring-2', 'ring-emerald-500');
    }
}

function toggleForm(jenis) {
    document.getElementById('form_pohon').classList.add('hidden');
    document.getElementById('form_sungai').classList.add('hidden');
    document.getElementById('form_komposting').classList.add('hidden');
    
    if (jenis === 'pohon') document.getElementById('form_pohon').classList.remove('hidden');
    if (jenis === 'sungai') document.getElementById('form_sungai').classList.remove('hidden');
    if (jenis === 'komposting') document.getElementById('form_komposting').classList.remove('hidden');
}

function lokasiSaya() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;
            miniMap.setView([lat, lng], 16);
            marker.setLatLng([lat, lng]);
            updateLocation({lat: lat, lng: lng});
        }, err => alert('Gagal: ' + err.message), { enableHighAccuracy: true });
    } else {
        alert('Browser tidak mendukung Geolocation.');
    }
}
</script>
