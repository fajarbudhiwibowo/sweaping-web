<?php if (!isset($_SESSION['user_id'])): 
    notif('gagal','Silakan login untuk mendaftarkan objek.');
    redirect('index.php?page=login');
endif; 

// Ambil daftar area pantau milik user
$my_areas = [];
if (isset($_SESSION['user_id'])) {
    $stmt_area = mysqli_prepare($conn, "SELECT a.id, a.kode, a.nama_area, a.geojson_polygon FROM area_pantau a INNER JOIN organisasi_anggota oa ON oa.organisasi_id = a.organisasi_id WHERE oa.user_id = ? AND a.deleted_at IS NULL ORDER BY a.nama_area");
    mysqli_stmt_bind_param($stmt_area, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_area);
    $res_area = mysqli_stmt_get_result($stmt_area);
    while ($a = mysqli_fetch_assoc($res_area)) $my_areas[] = $a;
    mysqli_stmt_close($stmt_area);
}
?>

<main class="max-w-4xl mx-auto px-4 pt-28 pb-16">
    <div class="mb-6">
        <a href="index.php?page=objek" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Daftar Objek
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
            <i data-lucide="file-plus" class="w-7 h-7 text-emerald-700"></i> Daftar Objek Baru
        </h1>
    </div>

    <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi_daftar_objek" value="1">

        <!-- BAGIAN 1: VISUAL & IDENTITAS -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold">1</div>
                <div class="font-bold text-emerald-800 text-lg">Visual & Identitas Dasar</div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Jenis Objek</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="cursor-pointer group"><input type="radio" name="jenis_objek" value="pohon" onchange="togglePohonFields()" class="peer hidden" checked>
                            <div class="border-2 border-slate-200 peer-checked:border-emerald-700 peer-checked:bg-emerald-50 rounded-xl p-3 text-center transition"><i data-lucide="tree-pine" class="w-6 h-6 mx-auto mb-1 text-emerald-700"></i><div class="text-xs font-bold">Pohon</div></div>
                        </label>
                        <label class="cursor-pointer group"><input type="radio" name="jenis_objek" value="sungai" onchange="togglePohonFields()" class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-blue-700 peer-checked:bg-blue-50 rounded-xl p-3 text-center transition"><i data-lucide="waves" class="w-6 h-6 mx-auto mb-1 text-blue-700"></i><div class="text-xs font-bold">Sungai</div></div>
                        </label>
                        <label class="cursor-pointer group"><input type="radio" name="jenis_objek" value="taman" onchange="togglePohonFields()" class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-lime-700 peer-checked:bg-lime-50 rounded-xl p-3 text-center transition"><i data-lucide="flower-2" class="w-6 h-6 mx-auto mb-1 text-lime-700"></i><div class="text-xs font-bold">Taman</div></div>
                        </label>
                        <label class="cursor-pointer group"><input type="radio" name="jenis_objek" value="komposting" onchange="togglePohonFields()" class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-amber-700 peer-checked:bg-amber-50 rounded-xl p-3 text-center transition"><i data-lucide="recycle" class="w-6 h-6 mx-auto mb-1 text-amber-700"></i><div class="text-xs font-bold">Kompos</div></div>
                        </label>
                    </div>
                </div>
                <div>
                                    <div>
                    <label class="block text-sm font-semibold mb-2">Foto / Video Utama (opsional)</label>
                    
                    <!-- Input tersembunyi: Kamera (Foto) -->
                    <input type="file" name="foto" id="input_foto_kamera" accept="image/*" capture="environment" class="hidden" onchange="pilihMedia(this, 'foto')">
                    <!-- Input tersembunyi: Galeri (Foto) -->
                    <input type="file" id="input_foto_galeri" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="pilihMedia(this, 'foto')">
                    <!-- Input tersembunyi: Video -->
                    <input type="file" id="input_video" accept="video/mp4,video/x-m4v,video/*" class="hidden" onchange="pilihMedia(this, 'video')">
                    
                    <!-- Tombol -->
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <button type="button" onclick="document.getElementById('input_foto_kamera').click()" class="bg-emerald-700 text-white font-bold py-2 rounded-xl hover:bg-emerald-800 transition flex items-center justify-center gap-1 text-xs md:text-sm">
                            <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                        </button>
                        <button type="button" onclick="document.getElementById('input_foto_galeri').click()" class="bg-blue-600 text-white font-bold py-2 rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-1 text-xs md:text-sm">
                            <i data-lucide="image" class="w-4 h-4"></i> Galeri
                        </button>
                        <button type="button" onclick="document.getElementById('input_video').click()" class="bg-amber-600 text-white font-bold py-2 rounded-xl hover:bg-amber-700 transition flex items-center justify-center gap-1 text-xs md:text-sm">
                            <i data-lucide="video" class="w-4 h-4"></i> Video
                        </button>
                    </div>

                    <!-- Preview Area -->
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center bg-slate-50">
                        <img id="preview_foto" src="" alt="" class="hidden max-h-48 mx-auto rounded-xl mb-2">
                        <video id="preview_video" src="" controls class="hidden max-h-48 mx-auto rounded-xl mb-2"></video>
                        <p id="info_media" class="text-xs text-slate-500">Belum ada file dipilih</p>
                        <p id="status_kompresi" class="text-xs text-emerald-600 font-bold mt-1 hidden"></p>
                    </div>
                </div>
                </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Nama Objek</label>
                    <input type="text" name="nama_objek" required maxlength="150" placeholder="Contoh: Manggis Manis / Sungai Citarum" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: SPASIAL (PETA & AREA) -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold">2</div>
                <div class="font-bold text-emerald-800 text-lg">Bagian Spasial</div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Lokasi / Wilayah</label>
                    <input type="text" name="lokasi" maxlength="255" placeholder="Contoh: Taman Kota Blok C" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl">
                </div>
                
                <?php if(!empty($my_areas)): ?>
                <div>
                    <label class="block text-sm font-semibold mb-2">Pilih Area Pantau (opsional)</label>
                    <select name="area_id" id="select_area" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white">
                        <option value="">-- Tidak dalam area tertentu --</option>
                        <?php foreach($my_areas as $a): ?>
                            <option value="<?= (int)$a['id'] ?>" data-geojson='<?= e($a['geojson_polygon']) ?>'>
                                <?= e($a['kode'] . ' — ' . $a['nama_area']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold mb-2">Koordinat GPS</label>
                    <input type="text" name="koordinat" id="input_koordinat" maxlength="100" placeholder="-6.917, 107.619" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl">
                </div>

                <!-- PETA LEAFLET -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Peta Interaktif (Geser pin untuk titik persis)</label>
                    <div id="map" class="w-full h-64 md:h-80 rounded-xl border-2 border-slate-200 z-0"></div>
                </div>
            </div>
        </div>

        <!-- BAGIAN 3: BIOLOGIS / KARAKTERISTIK -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold">3</div>
                <div class="font-bold text-emerald-800 text-lg">Data Biologis / Karakteristik</div>
            </div>
            
            <!-- ================= FORM POHON ================= -->
            <div id="box_pohon_fields" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Nama Latin</label><input type="text" name="nama_latin" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Famili</label><input type="text" name="famili" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Asal Daerah</label><input type="text" name="asal_daerah" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Nama Daerah Lain</label><input type="text" name="nama_daerah_lain" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Status Pohon</label><select name="status_pohon" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><option value="">-- Pilih --</option><option value="liar">Liar</option><option value="ditanam">Ditanam</option><option value="dibudidayakan">Dibudidayakan</option></select></div>
                    <div><label class="block text-xs font-semibold mb-1">Kategori IAS</label><select name="kategori_ias" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><option value="">-- Pilih --</option><option value="IAS">IAS</option><option value="Non-IAS">Non-IAS</option></select></div>
                </div>
                <div><label class="block text-xs font-semibold mb-1">Potensi Pemanfaatan</label><textarea name="potensi_pemanfaatan" rows="2" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></textarea></div>
                
                <div class="mt-4">
                    <label class="block text-xs font-semibold mb-2">Karakteristik Fisik</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['berbiji'=>'Berbiji','berbuah'=>'Berbuah','berbunga'=>'Berbunga','akar_tunggang'=>'Akar Tunggang','akar_serabut'=>'Akar Serabut','berkayu'=>'Berkayu','getah'=>'Memiliki Getah','duri'=>'Memiliki Duri','evergreen'=>'Evergreen','menggugurkan_daun'=>'Menggugurkan Daun'] as $k=>$v): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="karakteristik[]" value="<?= $k ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:font-semibold text-center"><?= $v ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold mb-2">Manfaat</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['ekologis'=>'Ekologis','ekonomi'=>'Ekonomi','pangan'=>'Pangan','obat'=>'Obat','budaya'=>'Budaya','lainnya'=>'Lainnya'] as $k=>$v): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="manfaat[]" value="<?= $k ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold text-center"><?= $v ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 pt-4 border-t-2 border-emerald-100">
                    <div><label class="block text-xs font-semibold mb-1">Tanggal Tanam</label><input type="date" name="tanggal_tanam" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Sumber Dana</label><select name="sumber_dana" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"><option value="">-- Pilih --</option><option value="APBN">APBN</option><option value="APBD">APBD</option><option value="CSR">CSR</option><option value="Swadaya">Swadaya</option></select></div>
                    <div><label class="block text-xs font-semibold mb-1">Penyelenggara</label><input type="text" name="penyelenggara" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Pelaksana</label><input type="text" name="pelaksana" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Jenis Bibit</label><input type="text" name="jenis_bibit" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Asal Bibit</label><input type="text" name="asal_bibit" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                <div><label class="block text-xs font-semibold mb-1 mt-2">Alasan Penanaman</label><textarea name="alasan_penanaman" rows="2" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></textarea></div>
            </div>

            <!-- ================= FORM SUNGAI ================= -->
            <div id="box_sungai_fields" class="space-y-4" style="display:none;">
                <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-1 mt-2">A. Identitas Sungai</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Nama Lain Sungai</label><input type="text" name="nama_lain_sungai" maxlength="150" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Sumber Mata Air</label><input type="text" name="sumber_mata_air" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Panjang Sungai (km)</label><input type="number" name="panjang_sungai" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Lebar Rata-rata (m)</label><input type="number" name="lebar_rata_rata" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Kedalaman Rata-rata (m)</label><input type="number" name="kedalaman_rata_rata" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Daerah Hulu</label><input type="text" name="daerah_hulu" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Daerah Hilir</label><input type="text" name="daerah_hilir" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Muara</label><input type="text" name="muara" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>

                <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-1 mt-4 pt-4 border-t-2 border-blue-100">B. Karakteristik Sungai</div>
                
                <div>
                    <label class="block text-xs font-semibold mb-2">Jenis Sungai</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <?php foreach(['Alami','Buatan','Anak Sungai','Saluran'] as $js): ?>
                            <label class="cursor-pointer"><input type="radio" name="jenis_sungai" value="<?= $js ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition"><?= $js ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Dasar Sungai <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Berbatu','Berlumpur','Berpasir'] as $ds): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="dasar_sungai[]" value="<?= $ds ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition"><?= $ds ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-semibold mb-2">Ada Vegetasi?</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer"><input type="radio" name="ada_vegetasi" value="1" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Ya</div></label>
                            <label class="cursor-pointer"><input type="radio" name="ada_vegetasi" value="0" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Tidak</div></label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-2">Ada Sempadan?</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer"><input type="radio" name="ada_sempadan" value="1" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Ya</div></label>
                            <label class="cursor-pointer"><input type="radio" name="ada_sempadan" value="0" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 text-center transition">Tidak</div></label>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Pemanfaatan Sungai <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Air baku','Irigasi','Pertanian','Perikanan','Peternakan','Rekreasi','Kebutuhan rumah tangga','Tidak dimanfaatkan','Lainnya'] as $pm): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="pemanfaatan_sungai[]" value="<?= $pm ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition"><?= $pm ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ================= FORM TAMAN ================= -->
            <div id="box_taman_fields" class="space-y-4" style="display:none;">
                <div class="text-xs font-bold text-lime-700 uppercase tracking-wide mb-1 mt-2">A. Identitas Taman</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Pengelola</label><input type="text" name="taman_pengelola" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Luas Area (m²)</label><input type="number" name="taman_luas_area" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Tahun Pembangunan</label><input type="number" name="taman_tahun_pembangunan" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Tahun Revitalisasi</label><input type="number" name="taman_tahun_revitalisasi" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-semibold mb-1">Status Kepemilikan</label><input type="text" name="taman_status_kepemilikan" maxlength="100" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>

                <div class="text-xs font-bold text-lime-700 uppercase tracking-wide mb-1 mt-4 pt-4 border-t-2 border-lime-100">B. Vegetasi Taman</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Jenis Pohon Dominan</label><input type="text" name="taman_vegetasi_pohon" maxlength="300" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Jenis Tanaman Lain</label><input type="text" name="taman_vegetasi_tanaman" maxlength="300" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>
                
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Kategori Tanaman <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Tanaman Obat','Tanaman Pangan','Tanaman Hias','Tanaman Lokal','Tanaman Endemik','Vegetasi Penutup Tanah'] as $kt): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="taman_kategori_tanaman[]" value="<?= $kt ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold text-center transition"><?= $kt ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="text-xs font-bold text-lime-700 uppercase tracking-wide mb-1 mt-4 pt-4 border-t-2 border-lime-100">C. Pemanfaatan Taman</div>
                <div>
                    <label class="block text-xs font-semibold mb-2">Fungsi Pemanfaatan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Ruang publik','Rekreasi','Olahraga','Edukasi','Bermain anak','Interaksi sosial','Konservasi','Kegiatan budaya','Kegiatan komunitas','Lainnya'] as $fg): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="taman_pemanfaatan[]" value="<?= $fg ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold text-center transition"><?= $fg ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-1">Intensitas Pemanfaatan</label>
                    <select name="taman_intensitas" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Sangat tinggi">Sangat Tinggi</option>
                        <option value="Tinggi">Tinggi</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Rendah">Rendah</option>
                        <option value="Tidak dimanfaatkan">Tidak Dimanfaatkan</option>
                    </select>
                </div>

                <div class="text-xs font-bold text-lime-700 uppercase tracking-wide mb-1 mt-4 pt-4 border-t-2 border-lime-100">D. Fasilitas Taman</div>
                <div>
                    <label class="block text-xs font-semibold mb-2">Fasilitas yang Tersedia <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Bangku','Tempat sampah','Lampu penerangan','Toilet','Gazebo','Jalur pejalan kaki','Jalur difabel','Playground','Fasilitas olahraga','Papan informasi','Area parkir','Drainase','Sistem penyiraman','Fasilitas lainnya'] as $fs): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="taman_fasilitas[]" value="<?= $fs ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold text-center transition"><?= $fs ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ================= FORM KOMPOS ================= -->
            <div id="box_komposting_fields" class="space-y-4" style="display:none;">
                <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1 mt-2">A. Identitas Komposting</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs font-semibold mb-1">Pengelola</label><input type="text" name="kompos_pengelola" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Metode Komposting</label>
                        <select name="kompos_metode" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg bg-white">
                            <option value="">-- Pilih --</option>
                            <option value="Aerob">Aerob</option>
                            <option value="Anaerob">Anaerob</option>
                            <option value="Takakura">Takakura</option>
                            <option value="Bokashi">Bokashi</option>
                            <option value="Vermikomposting">Vermikomposting</option>
                            <option value="Windrow">Windrow</option>
                            <option value="Biopori">Biopori</option>
                            <option value="Drum">Drum</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-semibold mb-1">Kapasitas (kg)</label><input type="number" name="kompos_kapasitas" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Luas Area (m²)</label><input type="number" name="kompos_luas_area" step="0.01" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Tahun Mulai</label><input type="number" name="kompos_tahun_mulai" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div><label class="block text-xs font-semibold mb-1">Jumlah Komposter</label><input type="number" name="kompos_jumlah_komposter" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                    <div class="md:col-span-2"><label class="block text-xs font-semibold mb-1">Kelompok / Komunitas Pengelola</label><input type="text" name="kompos_kelompok" maxlength="200" class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg"></div>
                </div>

                <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1 mt-4 pt-4 border-t-2 border-amber-100">B. Bahan Organik</div>
                <div>
                    <label class="block text-xs font-semibold mb-2">Jenis Bahan yang Dikompos <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <?php foreach(['Daun','Rumput','Ranting','Sisa makanan','Sampah dapur','Limbah pertanian','Limbah pasar','Kotoran ternak','Bahan organik lainnya'] as $bh): ?>
                            <label class="cursor-pointer"><input type="checkbox" name="kompos_bahan_organik_arr[]" value="<?= $bh ?>" class="peer hidden"><div class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg text-xs text-slate-600 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold text-center transition"><?= $bh ?></div></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div> <!-- END BAGIAN 3 -->

        <!-- BAGIAN 4: ADMINISTRASI -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6">
            <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 mb-5">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold">4</div>
                <div class="font-bold text-emerald-800 text-lg">Administrasi & Penutup</div>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-sm font-semibold mb-2">Nama PIC Monev</label><input type="text" name="pic_monev_nama" value="<?= e($_SESSION['nama'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl"></div>
                    <div><label class="block text-sm font-semibold mb-2">Peran PIC</label><select name="pic_monev_peran" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl"><option value="">-- Pilih --</option><option value="Koordinator">Koordinator</option><option value="Peneliti">Peneliti</option><option value="Pengelola">Pengelola</option></select></div>
                </div>
                <div><label class="block text-sm font-semibold mb-2">Catatan Tambahan</label><textarea name="catatan" rows="3" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl"></textarea></div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Visibilitas</label>
                    <select name="visibility" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl">
                        <option value="publik">Publik (Siapa saja)</option>
                        <option value="teman">Teman (Mutual)</option>
                        <option value="privat">Privat (Hanya Saya)</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-4 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 text-lg shadow-lg">Simpan Objek</button>
            </div>
        </div>
    </form>
</main>

<!-- LEAFLET CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function pilihFoto(input) {
    var file = input.files[0];
    var info = document.getElementById('info_foto');
    var preview = document.getElementById('preview_foto');
    var inputKamera = document.getElementById('input_foto_kamera');

    if (!file) return;

    // Cek ukuran file (Maks 8 MB)
    if (file.size > 8 * 1024 * 1024) {
        info.innerText = '❌ Ukuran melebihi 8 MB';
        info.className = 'text-xs text-rose-600 mt-2 font-semibold';
        input.value = '';
        return;
    }

    // Tampilkan preview
    var reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        info.innerText = '✔ ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
        info.className = 'text-xs text-emerald-600 mt-2 font-semibold';
    };
    reader.readAsDataURL(file);

    // Sinkronisasi: Jika user memilih dari Galeri, salin filenya ke input Kamera agar bisa dikirim ke server
    if (input.id === 'input_foto_galeri') {
        var dt = new DataTransfer();
        dt.items.add(file);
        inputKamera.files = dt.files;
    }
}
function togglePohonFields() {
    var checked = document.querySelector('input[name="jenis_objek"]:checked');
    if (!checked) return;
    var val = checked.value;

    // Sembunyikan semua
    document.getElementById('box_pohon_fields').style.display = 'none';
    document.getElementById('box_sungai_fields').style.display = 'none';
    document.getElementById('box_taman_fields').style.display = 'none';
    document.getElementById('box_komposting_fields').style.display = 'none';

    // Munculkan yang dipilih
    if (val === 'pohon') document.getElementById('box_pohon_fields').style.display = 'block';
    else if (val === 'sungai') document.getElementById('box_sungai_fields').style.display = 'block';
    else if (val === 'taman') document.getElementById('box_taman_fields').style.display = 'block';
    else if (val === 'komposting') document.getElementById('box_komposting_fields').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    // Init Peta
    var map = L.map('map').setView([-2.5, 118], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    
    var marker = L.marker([-2.5, 118], {draggable: true}).addTo(map);
    
    function updateKoordinat(lat, lng) {
        document.getElementById('input_koordinat').value = lat.toFixed(6) + ', ' + lng.toFixed(6);
    }
    
    marker.on('dragend', function(e) {
        var pos = marker.getLatLng();
        updateKoordinat(pos.lat, pos.lng);
    });
    
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateKoordinat(e.latlng.lat, e.latlng.lng);
    });

    // Cek jika user input koordinat manual
    document.getElementById('input_koordinat').addEventListener('change', function() {
        var parts = this.value.split(',');
        if (parts.length === 2) {
            var lat = parseFloat(parts[0]);
            var lng = parseFloat(parts[1]);
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 15);
            }
        }
    });

    // Handle Area Pantau (Dropdown)
    var selectArea = document.getElementById('select_area');
    if (selectArea) {
        selectArea.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            var geoStr = opt.getAttribute('data-geojson');
            if (geoStr) {
                try {
                    var geo = JSON.parse(geoStr);
                    var layer = L.geoJSON(geo, {style: {color: '#1a5c2e', weight: 2, fillOpacity: 0.2}}).addTo(map);
                    map.fitBounds(layer.getBounds());
                    
                    // Pindahkan marker ke tengah area
                    var center = layer.getBounds().getCenter();
                    marker.setLatLng(center);
                    updateKoordinat(center.lat, center.lng);
                } catch(e) { console.error('Format GeoJSON tidak valid', e); }
            }
        });
    }

    // Init form default
    togglePohonFields();
});
</script>