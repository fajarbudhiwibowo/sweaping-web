<?php 
if (!isset($_SESSION['user_id'])) { redirect('index.php?page=login'); }
$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM laporan_aksi WHERE id=? AND deleted_at IS NULL");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$data) { notif('gagal', 'Data tidak ditemukan'); redirect('index.php?page=arsip'); }
$jenis_aktif = $data['jenis_objek'];
?>
<main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
    <h1 class="text-2xl font-extrabold mb-6">Edit Laporan: <?= e($data['nama_objek']) ?></h1>
    <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi_update_laporan" value="1">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <div class="bg-white p-6 rounded-3xl shadow-xl space-y-4">
            <div><label class="block text-sm font-bold">Nama Objek</label><input type="text" name="nama_objek" value="<?= e($data['nama_objek']) ?>" class="w-full border-2 p-2 rounded"></div>
            <div><label class="block text-sm font-bold">Catatan</label><textarea name="catatan" rows="3" class="w-full border-2 p-2 rounded"><?= e($data['catatan']) ?></textarea></div>
            
            <!-- POHON -->
            <div style="<?= $jenis_aktif==='pohon'?'':'display:none' ?>">
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div><label class="text-xs font-bold">Lingkar Batang (cm)</label><input type="number" name="lingkar_batang" value="<?= e($data['lingkar_batang'] ?? '') ?>" class="w-full border-2 p-2 rounded"></div>
                    <div><label class="text-xs font-bold">Penyakit</label><input type="text" name="penyakit" value="<?= e($data['penyakit'] ?? '') ?>" class="w-full border-2 p-2 rounded"></div>
                </div>
            </div>

            <!-- KOMPOS -->
            <div style="<?= $jenis_aktif==='komposting'?'':'display:none' ?>">
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div><label class="text-xs font-bold">Volume Masuk (kg)</label><input type="number" name="kompos_volume_bahan_masuk" value="<?= e($data['kompos_volume_bahan_masuk'] ?? '') ?>" class="w-full border-2 p-2 rounded"></div>
                    <div><label class="text-xs font-bold">Lama Proses (hari)</label><input type="number" name="kompos_lama_proses" value="<?= e($data['kompos_lama_proses'] ?? '') ?>" class="w-full border-2 p-2 rounded"></div>
                </div>
            </div>

            <div><label class="block text-sm font-bold mt-3">Mitigasi</label><textarea name="mitigasi" rows="2" class="w-full border-2 p-2 rounded"><?= e($data['mitigasi'] ?? '') ?></textarea></div>
        </div>
        <button type="submit" class="w-full py-4 bg-emerald-700 text-white font-bold rounded-xl">Simpan Perubahan</button>
    </form>
</main>