<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$error = '';
$uid = (int)$_SESSION['user_id'];

if (isset($_POST['aksi_daftar_objek'])) {
    $jenis_objek = $_POST['jenis_objek'] ?? '';
    $nama_objek = trim($_POST['nama_objek'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $koordinat = trim($_POST['koordinat'] ?? '');
    $area_id_form = (int)($_POST['area_id'] ?? 0);
    $area_id_form = $area_id_form > 0 ? $area_id_form : null;
    $catatan = trim($_POST['catatan'] ?? '');
    $visibility = $_POST['visibility'] ?? 'publik';

    if (!in_array($jenis_objek, ['pohon','sungai','taman','komposting'])) {
        $error = 'Jenis objek tidak valid.';
    } elseif ($nama_objek === '') {
        $error = 'Nama objek wajib diisi.';
    } else {
        $prefix_map = ['pohon'=>'TRB', 'sungai'=>'SNG', 'taman'=>'TMN', 'komposting'=>'KMP'];
        $prefix = $prefix_map[$jenis_objek];
        $kode_objek = $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        // CEK ORGANISASI USER
        $org_id = null;
        $stmt_org = mysqli_prepare($conn, "SELECT organisasi_id FROM organisasi_anggota WHERE user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_org, 'i', $uid);
        mysqli_stmt_execute($stmt_org);
        $res_org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_org));
        if($res_org) $org_id = (int)$res_org['organisasi_id'];
        mysqli_stmt_close($stmt_org);

        // INSERT DENGAN ORGANISASI_ID
        $stmt = mysqli_prepare($conn, "INSERT INTO objek (kode_objek, jenis_objek, nama_objek, lokasi, koordinat, catatan, user_id, visibility, organisasi_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssssisi', $kode_objek, $jenis_objek, $nama_objek, $lokasi, $koordinat, $catatan, $uid, $visibility, $org_id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('index.php?page=objek');
        } else {
            $error = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
        }
    }
}
?>

<main class="max-w-3xl mx-auto px-4 pt-24 pb-16">
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

    <form method="POST" action="index.php?page=objek_daftar" class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kolom Kiri -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Objek</label>
                    <select name="jenis_objek" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="pohon">🌳 Pohon</option>
                        <option value="sungai">🌊 Sungai</option>
                        <option value="taman">🌷 Taman</option>
                        <option value="komposting">♻️ Komposting</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Objek</label>
                    <input type="text" name="nama_objek" required placeholder="Contoh: Pohon Mangga Depan Rumah" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi (Wilayah)</label>
                    <input type="text" name="lokasi" placeholder="Contoh: Kelurahan Sukamaju, Bandung" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Koordinat GPS</label>
                    <div class="flex gap-2">
                        <input type="text" name="koordinat" id="input_koordinat" placeholder="-6.123, 106.456" class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <button type="button" onclick="ambilLokasi()" class="px-4 py-3 bg-emerald-100 text-emerald-700 rounded-xl hover:bg-emerald-200 transition" title="Ambil lokasi otomatis"><i data-lucide="map-pin" class="w-5 h-5"></i></button>
                    </div>
                    <p id="info_lokasi" class="text-xs text-slate-500 mt-2"></p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Visibilitas</label>
                    <select name="visibility" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="publik">🌍 Publik (Semua bisa lihat)</option>
                        <option value="teman">👥 Teman (Hanya mutual follow)</option>
                        <option value="privat">🔒 Privat (Hanya Anda)</option>
                    </select>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan Tambahan</label>
            <textarea name="catatan" rows="3" placeholder="Deskripsi singkat objek..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <button type="submit" name="aksi_daftar_objek" class="w-full py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg hover:bg-emerald-700 transition flex justify-center items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Simpan Objek
        </button>
    </form>
</main>

<script>
function ambilLokasi() {
    var info = document.getElementById('info_lokasi');
    var input = document.getElementById('input_koordinat');
    if (!navigator.geolocation) { info.textContent = 'GPS tidak didukung.'; return; }
    info.textContent = '⏳ Mengambil lokasi...';
    navigator.geolocation.getCurrentPosition(
        pos => {
            input.value = pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6);
            info.textContent = '✔ Akurasi ±' + Math.round(pos.coords.accuracy) + ' m';
            info.className = 'text-xs text-emerald-600 mt-2 font-semibold';
        },
        err => { info.textContent = '❌ Gagal: ' + err.message; info.className = 'text-xs text-rose-600 mt-2'; },
        { enableHighAccuracy: true }
    );
}
</script>
