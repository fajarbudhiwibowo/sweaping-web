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

<main class="max-w-4xl mx-auto px-4 pt-28 pb-16">
    <div class="mb-6">
        <a href="index.php?page=objek_profil&id=<?= $objek_id ?>" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Profil Objek
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
            <i data-lucide="edit" class="w-7 h-7 text-emerald-700"></i> Edit Objek: <?= e($objek['nama_objek']) ?>
        </h1>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
        <div class="bg-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-50 text-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-700 p-4 rounded-xl mb-6 font-bold border border-<?= $_SESSION['notif']['tipe'] == 'sukses' ? 'emerald' : 'rose' ?>-200"><?= e($_SESSION['notif']['pesan']) ?></div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi_update_objek" value="1">
        <input type="hidden" name="objek_id" value="<?= $objek_id ?>">
        <input type="hidden" name="jenis_objek" value="<?= e($objek['jenis_objek']) ?>">

           <!-- BAGIAN 1: VISUAL & IDENTITAS -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold">1</div>
                <div class="font-bold text-slate-800 text-lg">Identitas & Visual Objek</div>
            </div>
            
            <div class="space-y-6">
                <!-- Hidden input untuk menyimpan jenis objek yang sedang diedit -->
                <input type="hidden" name="jenis_objek" value="<?= e($objek['jenis_objek']) ?>">

                <!-- Nama Objek -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Objek</label>
                    <input type="text" name="nama_objek" required maxlength="150" value="<?= e($objek['nama_objek']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 transition-colors">
                </div>

                <!-- Upload Foto -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Foto Utama (Biarkan kosong jika tidak diganti)</label>
                    
                    <input type="file" name="foto" id="input_foto_kamera" accept="image/*" capture="environment" class="hidden" onchange="pilihMedia(this, 'foto')">
                    <input type="file" id="input_foto_galeri" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="pilihMedia(this, 'foto')">
                    
                    <div id="upload_box" class="relative w-full h-48 md:h-64 border-4 border-dashed border-slate-200 rounded-2xl bg-slate-50 flex flex-col items-center justify-center overflow-hidden">
                        
                        <!-- Preview Existing / New Image -->
                        <img id="preview_foto" src="<?= !empty($objek['foto_pertama']) ? 'uploads/'.$objek['foto_pertama'] : '' ?>" alt="" class="<?= !empty($objek['foto_pertama']) ? '' : 'hidden' ?> absolute inset-0 w-full h-full object-cover z-10">
                        
                        <!-- Placeholder Text & Buttons -->
                        <div id="upload_placeholder" class="<?= !empty($objek['foto_pertama']) ? 'hidden' : '' ?> text-center z-0 p-4">
                            <div class="flex justify-center gap-3 mb-3">
                                <button type="button" onclick="document.getElementById('input_foto_kamera').click()" class="bg-white border-2 border-slate-200 text-slate-700 font-bold py-2 px-4 rounded-xl hover:bg-slate-100 transition flex items-center justify-center gap-2 text-sm shadow-sm">
                                    <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                                </button>
                                <button type="button" onclick="document.getElementById('input_foto_galeri').click()" class="bg-white border-2 border-slate-200 text-slate-700 font-bold py-2 px-4 rounded-xl hover:bg-slate-100 transition flex items-center justify-center gap-2 text-sm shadow-sm">
                                    <i data-lucide="image" class="w-4 h-4"></i> Galeri
                                </button>
                            </div>
                            <p class="text-xs text-slate-500">Format: JPG/PNG/WEBP. Maks 8MB.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: SPASIAL (PETA & AREA) -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold">2</div>
                <div class="font-bold text-slate-800 text-lg">Bagian Spasial</div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Lokasi / Wilayah</label>
                    <input type="text" name="lokasi" maxlength="255" value="<?= e($objek['lokasi']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Pilih Area Pantau (opsional)</label>
                    <select name="area_id" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white">
                        <option value="">-- Tidak dalam area tertentu --</option>
                        <?php foreach($areas as $a): ?>
                            <option value="<?= (int)$a['id'] ?>" <?= $a['id'] == $objek['area_id'] ? 'selected' : '' ?>>
                                <?= e($a['nama_area']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Koordinat GPS</label>
                    <input type="text" name="koordinat" id="input_koordinat" maxlength="100" value="<?= e($objek['koordinat']) ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Peta Interaktif (Geser pin untuk titik persis)</label>
                    <div id="map" class="w-full h-64 md:h-80 rounded-xl border-2 border-slate-200 z-0"></div>
                </div>
            </div>
        </div>

        <!-- BAGIAN 3: BIOLOGIS / KARAKTERISTIK -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold">3</div>
                <div class="font-bold text-slate-800 text-lg">Data Karakteristik</div>
            </div>
            
            <!-- 1. FORM POHON -->
            <?php if($objek['jenis_objek'] === 'pohon'): ?>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Nama Latin</label><input type="text" name="nama_latin" value="<?= e($objek['nama_latin']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Famili</label><input type="text" name="famili" value="<?= e($objek['famili']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Asal Daerah</label><input type="text" name="asal_daerah" value="<?= e($objek['asal_daerah']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Nama Daerah Lain</label><input type="text" name="nama_daerah_lain" value="<?= e($objek['nama_daerah_lain']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Status Pohon</label><select name="status_pohon" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><option value="">-- Pilih --</option><option value="liar" <?= $objek['status_pohon']=='liar'?'selected':'' ?>>Liar</option><option value="ditanam" <?= $objek['status_pohon']=='ditanam'?'selected':'' ?>>Ditanam</option><option value="dibudidayakan" <?= $objek['status_pohon']=='dibudidayakan'?'selected':'' ?>>Dibudidayakan</option></select></div>
                    <div><label class="block text-xs font-semibold mb-1">Kategori IAS</label><select name="kategori_ias" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><option value="">-- Pilih --</option><option value="IAS" <?= $objek['kategori_ias']=='IAS'?'selected':'' ?>>IAS</option><option value="Non-IAS" <?= $objek['kategori_ias']=='Non-IAS'?'selected':'' ?>>Non-IAS</option></select></div>
                </div>
                <div><label class="block text-xs font-semibold mb-1">Potensi Pemanfaatan</label><textarea name="potensi_pemanfaatan" rows="2" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><?= e($objek['potensi_pemanfaatan']) ?></textarea></div>
                
                <div class="mt-4">
                    <label class="block text-xs font-semibold mb-2">Karakteristik Fisik</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['berbiji'=>'Berbiji','berbuah'=>'Berbuah','berbunga'=>'Berbunga','akar_tunggang'=>'Akar Tunggang','akar_serabut'=>'Akar Serabut','berkayu'=>'Berkayu','getah'=>'Memiliki Getah','duri'=>'Memiliki Duri','evergreen'=>'Evergreen','menggugurkan_daun'=>'Menggugurkan Daun'] as $k=>$v): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="karakteristik[]" value="<?= $k ?>" <?= in_array($k, $karakteristik)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:font-semibold text-center"><?= $v ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold mb-2">Manfaat</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['ekologis'=>'Ekologis','ekonomi'=>'Ekonomi','pangan'=>'Pangan','obat'=>'Obat','budaya'=>'Budaya','lainnya'=>'Lainnya'] as $k=>$v): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="manfaat[]" value="<?= $k ?>" <?= in_array($k, $manfaat)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold text-center"><?= $v ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 pt-4 border-t-2 border-emerald-100">
                    <div><label class="block text-xs font-semibold mb-1">Tanggal Tanam</label><input type="date" name="tanggal_tanam" value="<?= e($objek['tanggal_tanam']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Sumber Dana</label><select name="sumber_dana" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><option value="">-- Pilih --</option><option value="APBN" <?= $objek['sumber_dana']=='APBN'?'selected':'' ?>>APBN</option><option value="APBD" <?= $objek['sumber_dana']=='APBD'?'selected':'' ?>>APBD</option><option value="CSR" <?= $objek['sumber_dana']=='CSR'?'selected':'' ?>>CSR</option><option value="Swadaya" <?= $objek['sumber_dana']=='Swadaya'?'selected':'' ?>>Swadaya</option></select></div>
                    <div><label class="block text-xs font-semibold mb-1">Penyelenggara</label><input type="text" name="penyelenggara" value="<?= e($objek['penyelenggara']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Pelaksana</label><input type="text" name="pelaksana" value="<?= e($objek['pelaksana']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Jenis Bibit</label><input type="text" name="jenis_bibit" value="<?= e($objek['jenis_bibit']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Asal Bibit</label><input type="text" name="asal_bibit" value="<?= e($objek['asal_bibit']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div><label class="block text-xs font-semibold mb-1 mt-2">Alasan Penanaman</label><textarea name="alasan_penanaman" rows="2" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><?= e($objek['alasan_penanaman']) ?></textarea></div>
            </div>
            
            <!-- 2. FORM SUNGAI -->
            <?php elseif($objek['jenis_objek'] === 'sungai'): ?>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Nama Lain Sungai</label><input type="text" name="nama_lain_sungai" value="<?= e($objek['nama_lain_sungai']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Sumber Mata Air</label><input type="text" name="sumber_mata_air" value="<?= e($objek['sumber_mata_air']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Panjang Sungai (km)</label><input type="number" name="panjang_sungai" value="<?= e($objek['panjang_sungai']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Lebar Rata-rata (m)</label><input type="number" name="lebar_rata_rata" value="<?= e($objek['lebar_rata_rata']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kedalaman Rata-rata (m)</label><input type="number" name="kedalaman_rata_rata" value="<?= e($objek['kedalaman_rata_rata']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Daerah Hulu</label><input type="text" name="daerah_hulu" value="<?= e($objek['daerah_hulu']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Daerah Hilir</label><input type="text" name="daerah_hilir" value="<?= e($objek['daerah_hilir']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Muara</label><input type="text" name="muara" value="<?= e($objek['muara']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Jenis Sungai</label>
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-2">
                        <?php foreach(['Alami','Buatan','Anak Sungai','Saluran'] as $js): ?>
                            <label class="cursor-pointer"><input type="radio" name="jenis_sungai" value="<?= $js ?>" <?= $objek['jenis_sungai']==$js?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition"><?= $js ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Dasar Sungai <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Berbatu','Berlumpur','Berpasir'] as $ds): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="dasar_sungai[]" value="<?= $ds ?>" <?= in_array($ds, $dasar_sungai)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition"><?= $ds ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold mb-2">Ada Vegetasi?</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer"><input type="radio" name="ada_vegetasi" value="1" <?= $objek['ada_vegetasi']=='1'?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Ya</div></label>
                            <label class="cursor-pointer"><input type="radio" name="ada_vegetasi" value="0" <?= $objek['ada_vegetasi']=='0'?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Tidak</div></label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-2">Ada Sempadan?</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer"><input type="radio" name="ada_sempadan" value="1" <?= $objek['ada_sempadan']=='1'?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Ya</div></label>
                            <label class="cursor-pointer"><input type="radio" name="ada_sempadan" value="0" <?= $objek['ada_sempadan']=='0'?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Tidak</div></label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Pemanfaatan Sungai <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Air baku','Irigasi','Pertanian','Perikanan','Peternakan','Rekreasi','Kebutuhan rumah tangga','Tidak dimanfaatkan','Lainnya'] as $pm): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="pemanfaatan_sungai[]" value="<?= $pm ?>" <?= in_array($pm, $pemanfaatan_sungai)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition"><?= $pm ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 3. FORM TAMAN -->
            <?php elseif($objek['jenis_objek'] === 'taman'): ?>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Pengelola</label><input type="text" name="taman_pengelola" value="<?= e($objek['taman_pengelola']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Luas Area (m²)</label><input type="number" name="taman_luas_area" value="<?= e($objek['taman_luas_area']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Tahun Pembangunan</label><input type="number" name="taman_tahun_pembangunan" value="<?= e($objek['taman_tahun_pembangunan']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Tahun Revitalisasi</label><input type="number" name="taman_tahun_revitalisasi" value="<?= e($objek['taman_tahun_revitalisasi']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-semibold mb-1">Status Kepemilikan</label><input type="text" name="taman_status_kepemilikan" value="<?= e($objek['taman_status_kepemilikan']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 pt-4 border-t-2 border-lime-100">
                    <div><label class="block text-xs font-semibold mb-1">Jenis Pohon Dominan</label><input type="text" name="taman_vegetasi_pohon" value="<?= e($objek['taman_vegetasi_pohon']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Jenis Tanaman Lain</label><input type="text" name="taman_vegetasi_tanaman" value="<?= e($objek['taman_vegetasi_tanaman']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Kategori Tanaman <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Tanaman Obat','Tanaman Pangan','Tanaman Hias','Tanaman Lokal','Tanaman Endemik','Vegetasi Penutup Tanah'] as $kt): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="taman_kategori_tanaman[]" value="<?= $kt ?>" <?= in_array($kt, $taman_kategori)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold text-center transition"><?= $kt ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Fungsi Pemanfaatan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Ruang publik','Rekreasi','Olahraga','Edukasi','Bermain anak','Interaksi sosial','Konservasi','Kegiatan budaya','Kegiatan komunitas','Lainnya'] as $fg): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="taman_pemanfaatan[]" value="<?= $fg ?>" <?= in_array($fg, $taman_pemanfaatan)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold text-center transition"><?= $fg ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-1">Intensitas Pemanfaatan</label>
                    <select name="taman_intensitas" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Sangat tinggi" <?= $objek['taman_intensitas']=='Sangat tinggi'?'selected':'' ?>>Sangat Tinggi</option>
                        <option value="Tinggi" <?= $objek['taman_intensitas']=='Tinggi'?'selected':'' ?>>Tinggi</option>
                        <option value="Sedang" <?= $objek['taman_intensitas']=='Sedang'?'selected':'' ?>>Sedang</option>
                        <option value="Rendah" <?= $objek['taman_intensitas']=='Rendah'?'selected':'' ?>>Rendah</option>
                        <option value="Tidak dimanfaatkan" <?= $objek['taman_intensitas']=='Tidak dimanfaatkan'?'selected':'' ?>>Tidak Dimanfaatkan</option>
                    </select>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Fasilitas yang Tersedia <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Bangku','Tempat sampah','Lampu penerangan','Toilet','Gazebo','Jalur pejalan kaki','Jalur difabel','Playground','Fasilitas olahraga','Papan informasi','Area parkir','Drainase','Sistem penyirama','Fasilitas lainnya'] as $fs): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="taman_fasilitas[]" value="<?= $fs ?>" <?= in_array($fs, $taman_fasilitas)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold text-center transition"><?= $fs ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 4. FORM KOMPOS -->
            <?php elseif($objek['jenis_objek'] === 'komposting'): ?>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Pengelola</label><input type="text" name="kompos_pengelola" value="<?= e($objek['kompos_pengelola']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Metode Komposting</label>
                        <select name="kompos_metode" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach(['Aerob','Anaerob','Takakura','Bokashi','Vermikomposting','Windrow','Biopori','Drum','Lainnya'] as $s): ?>
                                <option value="<?= $s ?>" <?= $objek['kompos_metode']===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-semibold mb-1">Kapasitas (kg)</label><input type="number" name="kompos_kapasitas" value="<?= e($objek['kompos_kapasitas']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Luas Area (m²)</label><input type="number" name="kompos_luas_area" value="<?= e($objek['kompos_luas_area']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Tahun Mulai</label><input type="number" name="kompos_tahun_mulai" value="<?= e($objek['kompos_tahun_mulai']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Jumlah Komposter</label><input type="number" name="kompos_jumlah_komposter" value="<?= e($objek['kompos_jumlah_komposter']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-semibold mb-1">Kelompok / Komunitas Pengelola</label><input type="text" name="kompos_kelompok" value="<?= e($objek['kompos_kelompok']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold mb-2">Jenis Bahan yang Dikompos <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Daun','Rumput','Ranting','Sisa makanan','Sampah dapur','Limbah pertanian','Limbah pasar','Kotoran ternak','Bahan organik lainnya'] as $bh): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="kompos_bahan_organik_arr[]" value="<?= $bh ?>" <?= in_array($bh, $kompos_bahan)?'checked':'' ?> class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold text-center transition"><?= $bh ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 5. FORM HUTAN -->
            <?php elseif($objek['jenis_objek'] === 'hutan'): ?>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Nama Lain Hutan</label><input type="text" name="hutan_nama_lain" value="<?= e($objek['hutan_nama_lain']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Pengelola</label><input type="text" name="hutan_pengelola" value="<?= e($objek['hutan_pengelola']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Luas (Ha)</label><input type="number" name="hutan_luas" value="<?= e($objek['hutan_luas']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tipe Hutan</label>
                        <select name="hutan_tipe" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="">-- Pilih --</option><option value="Lindung" <?= $objek['hutan_tipe']=='Lindung'?'selected':'' ?>>Hutan Lindung</option><option value="Produksi" <?= $objek['hutan_tipe']=='Produksi'?'selected':'' ?>>Hutan Produksi</option><option value="Konservasi" <?= $objek['hutan_tipe']=='Konservasi'?'selected':'' ?>>Hutan Konservasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Kondisi Tajuk</label>
                        <select name="hutan_kondisi_tajuk" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="Rapat" <?= $objek['hutan_kondisi_tajuk']=='Rapat'?'selected':'' ?>>Rapat / Hijau</option><option value="Sedang" <?= $objek['hutan_kondisi_tajuk']=='Sedang'?'selected':'' ?>>Sedang</option><option value="Jarang" <?= $objek['hutan_kondisi_tajuk']=='Jarang'?'selected':'' ?>>Jarang / Gundul</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Regenerasi (Anakan)</label>
                        <select name="hutan_regenerasi" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="Baik" <?= $objek['hutan_regenerasi']=='Baik'?'selected':'' ?>>Banyak Anakan / Baik</option><option value="Buruk" <?= $objek['hutan_regenerasi']=='Buruk'?'selected':'' ?>>Sangat Sedikit / Buruk</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 pt-4 border-t-2 border-teal-100">
                    <div><label class="block text-xs font-semibold mb-1">Kanopi (%)</label><input type="number" name="hutan_kanopi" value="<?= e($objek['hutan_kanopi']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kerapatan (ph/Ha)</label><input type="number" name="hutan_kerapatan" value="<?= e($objek['hutan_kerapatan']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Pohon Mati (Snags)</label><input type="number" name="hutan_pohon_mati" value="<?= e($objek['hutan_pohon_mati']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">pH Tanah</label><input type="number" name="hutan_ph_tanah" value="<?= e($objek['hutan_ph_tanah']) ?>" step="0.1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">KTK (cmol/kg)</label><input type="number" name="hutan_ktk" value="<?= e($objek['hutan_ktk']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Karbon Tanah (ton/ha)</label><input type="number" name="hutan_karbon_tanah" value="<?= e($objek['hutan_karbon_tanah']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kedalaman Solum (cm)</label><input type="number" name="hutan_kedalaman_solum" value="<?= e($objek['hutan_kedalaman_solum']) ?>" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Ada Spesies IAS?</label>
                        <select name="hutan_ada_ias" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="0" <?= $objek['hutan_ada_ias']=='0'?'selected':'' ?>>Tidak Ada</option><option value="1" <?= $objek['hutan_ada_ias']=='1'?'selected':'' ?>>Ada (Invasif)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4 pt-4 border-t-2 border-teal-100">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Jejak Manusia</label>
                        <select name="hutan_jejak_manusia" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="Tidak Ada" <?= $objek['hutan_jejak_manusia']=='Tidak Ada'?'selected':'' ?>>Tidak Ada</option><option value="Penebangan" <?= $objek['hutan_jejak_manusia']=='Penebangan'?'selected':'' ?>>Ada Penebangan</option><option value="Kebakaran" <?= $objek['hutan_jejak_manusia']=='Kebakaran'?'selected':'' ?>>Ada Kebakaran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tingkat Gangguan</label>
                        <select name="hutan_tingkat_gangguan" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="Rendah" <?= $objek['hutan_tingkat_gangguan']=='Rendah'?'selected':'' ?>>Rendah</option><option value="Sedang" <?= $objek['hutan_tingkat_gangguan']=='Sedang'?'selected':'' ?>>Sedang</option><option value="Tinggi" <?= $objek['hutan_tingkat_gangguan']=='Tinggi'?'selected':'' ?>>Tinggi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Keberadaan Sampah</label>
                        <select name="hutan_sampah" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="Tidak Ada" <?= $objek['hutan_sampah']=='Tidak Ada'?'selected':'' ?>>Tidak Ada</option><option value="Sedikit" <?= $objek['hutan_sampah']=='Sedikit'?'selected':'' ?>>Sedikit</option><option value="Banyak" <?= $objek['hutan_sampah']=='Banyak'?'selected':'' ?>>Banyak</option>
                        </select>
                    </div>
                    <div class="md:col-span-3"><label class="block text-xs font-semibold mb-1">Jenis Gangguan (Detail)</label><input type="text" name="hutan_jenis_gangguan" value="<?= e($objek['hutan_jenis_gangguan']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div class="bg-purple-50 rounded-xl border-2 border-purple-200 p-4 mt-4">
                    <div class="text-xs font-bold text-purple-700 uppercase mb-3">Parameter Biodiversitas (Tabel Spesies)</div>
                    <label class="text-xs font-bold block mb-2">Tabel Spesies (Auto Shannon)</label>
                    <table class="w-full text-sm mb-2" id="tabel_spesies">
                        <tbody>
                            <!-- (Opsional) Load existing species here if you have a dedicated table, or just show current index -->
                            <tr><td class="pr-2"><input type="text" class="w-full border-2 p-1" placeholder="Nama Spesies (Meranti)"></td><td class="pr-2"><input type="number" class="w-full border-2 p-1" oninput="calcShannon()" placeholder="Jumlah Individu"></td><td><button type="button" onclick="hapusBaris(this)">❌</button></td></tr>
                        </tbody>
                    </table>
                    <button type="button" onclick="tambahSpesies()" class="text-purple-700 font-bold text-sm mb-3">+ Tambah Spesies</button>
                    <div class="bg-white p-2 rounded border-2 border-purple-200 flex justify-between">
                        <span class="text-xs font-bold text-purple-700">Indeks Shannon (H'):</span><span id="shannon_hasil" class="font-bold text-purple-900"><?= e($objek['hutan_shannon'] ?: '0.00') ?></span>
                    </div>
                    <input type="hidden" name="hutan_shannon" id="in_hutan_shannon" value="<?= e($objek['hutan_shannon'] ?: '0.00') ?>">
                </div>
            </div>

            <!-- 6. FORM LINGKUNGAN -->
            <?php elseif($objek['jenis_objek'] === 'lingkungan'): ?>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1">Tipe Lingkungan</label>
                        <select name="lingkungan_tipe" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="Rumah" <?= $objek['lingkungan_tipe']=='Rumah'?'selected':'' ?>>Rumah</option>
                            <option value="Sekolah" <?= $objek['lingkungan_tipe']=='Sekolah'?'selected':'' ?>>Sekolah</option>
                            <option value="Kantor" <?= $objek['lingkungan_tipe']=='Kantor'?'selected':'' ?>>Kantor</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-semibold mb-1">Nama PIC / Penanggung Jawab</label><input type="text" name="lingkungan_pic" value="<?= e($objek['lingkungan_pic']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-4 pt-4 border-t-2 border-purple-100">
                    <div><label class="block text-xs font-semibold mb-1">Suhu Udara (°C)</label><input type="number" name="lingkungan_suhu" value="<?= e($objek['lingkungan_suhu']) ?>" step="0.1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kelembaban (%)</label><input type="number" name="lingkungan_kelembaban" value="<?= e($objek['lingkungan_kelembaban']) ?>" step="0.1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Cahaya (Lux)</label><input type="number" name="lingkungan_lux" value="<?= e($objek['lingkungan_lux']) ?>" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kebisingan (dB)</label><input type="number" name="lingkungan_kebisingan" value="<?= e($objek['lingkungan_kebisingan']) ?>" step="0.1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Partikulat PM2.5 (µg/m³)</label><input type="number" name="lingkungan_pm25" value="<?= e($objek['lingkungan_pm25']) ?>" step="0.1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kadar CO2 (ppm)</label><input type="number" name="lingkungan_co2" value="<?= e($objek['lingkungan_co2']) ?>" step="0.1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
            </div>
            <?php endif; ?>
        </div> <!-- END BAGIAN 3 -->

        <!-- BAGIAN 4: ADMINISTRASI -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold">4</div>
                <div class="font-bold text-slate-800 text-lg">Administrasi & Penutup</div>
            </div>
            <div class="space-y-4">
                <div><label class="block text-sm font-semibold mb-2">Catatan Tambahan</label><textarea name="catatan" rows="3" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl"><?= e($objek['catatan']) ?></textarea></div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Visibilitas</label>
                    <select name="visibility" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl">
                        <option value="publik" <?= $objek['visibility']=='publik'?'selected':'' ?>>Publik (Siapa saja)</option>
                        <option value="teman" <?= $objek['visibility']=='teman'?'selected':'' ?>>Teman (Mutual)</option>
                        <option value="privat" <?= $objek['visibility']=='privat'?'selected':'' ?>>Privat (Hanya Saya)</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-4 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 text-lg shadow-lg">Selesai Edit & Simpan</button>
            </div>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Init Peta
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

    // Fungsi Upload Foto (Preview)
    window.pilihMedia = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_foto').src = e.target.result;
                document.getElementById('preview_foto').classList.remove('hidden');
                document.getElementById('upload_placeholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Fungsi Tabel Spesies (Hutan)
    window.tambahSpesies = function() {
        var tr = document.createElement('tr');
        tr.innerHTML = '<td class="pr-2"><input type="text" class="w-full border-2 p-1" placeholder="Spesies"></td><td class="pr-2"><input type="number" class="w-full border-2 p-1" oninput="calcShannon()"></td><td><button type="button" onclick="hapusBaris(this)">❌</button></td>';
        document.querySelector('#tabel_spesies tbody').appendChild(tr);
    }
    window.hapusBaris = function(btn) { btn.closest('tr').remove(); calcShannon(); }
    window.calcShannon = function() {
        var inputs = document.querySelectorAll('#tabel_spesies tbody input[type="number"]');
        var total = 0;
        inputs.forEach(inp => total += parseFloat(inp.value) || 0);
        if (total === 0) { document.getElementById('shannon_hasil').innerText = "0.00"; document.getElementById('in_hutan_shannon').value = "0.00"; return; }
        var h = 0;
        inputs.forEach(inp => {
            var val = parseFloat(inp.value) || 0;
            if (val > 0) { var p = val / total; h -= p * Math.log(p); }
        });
        document.getElementById('shannon_hasil').innerText = h.toFixed(2);
        document.getElementById('in_hutan_shannon').value = h.toFixed(2);
    }
});
</script>