<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$objek_id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT o.*, u.nama_lengkap as pemilik, org.nama as nama_org FROM objek o LEFT JOIN users u ON o.user_id = u.id LEFT JOIN organisasi org ON o.organisasi_id = org.id WHERE o.id = ? AND o.deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $objek_id);
mysqli_stmt_execute($stmt);
$objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$objek) { echo "<div class='pt-32 text-center text-slate-500'>Objek tidak ditemukan.</div>"; exit; }

$info = ['pohon'=>['bg'=>'bg-emerald-600','ic'=>'tree-pine'], 'sungai'=>['bg'=>'bg-blue-600','ic'=>'waves'], 'taman'=>['bg'=>'bg-lime-600','ic'=>'flower-2'], 'komposting'=>['bg'=>'bg-amber-600','ic'=>'recycle']][$objek['jenis_objek']] ?? ['bg'=>'bg-slate-600','ic'=>'package'];

// Helper JSON
$to_arr = function($json) { return !empty($json) ? json_decode($json, true) : []; };
$karakteristik = $to_arr($objek['karakteristik']);
$manfaat = $to_arr($objek['manfaat']);

// Ambil Riwayat Laporan
$stmt_l = mysqli_prepare($conn, "SELECT * FROM laporan_aksi WHERE objek_id = ? AND deleted_at IS NULL ORDER BY tanggal_buat DESC");
mysqli_stmt_bind_param($stmt_l, 'i', $objek_id);
mysqli_stmt_execute($stmt_l);
$q_laporan = mysqli_stmt_get_result($stmt_l);

// QR Code
$qr_url = 'https://sweaping.lsmkompas.id/index.php?page=objek_profil&id=' . $objek_id;
$qr_api = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qr_url);
?>

<main class="max-w-6xl mx-auto px-4 pt-24 pb-16">
    <a href="index.php?page=objek" class="text-sm font-bold text-emerald-600 hover:underline flex items-center gap-1 mb-6">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar Objek
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- KIRI: Foto, QR, Info Dasar -->
        <div class="space-y-6">
                            <?php 
                $foto_url = '';
                $has_foto = false;
                if (!empty($objek['telegram_file_id'])) {
                    $foto_url = url_foto($objek, 'foto_pertama', 'full');
                    $has_foto = true;
                } elseif (!empty($objek['foto_pertama']) && file_exists('uploads/' . $objek['foto_pertama'])) {
                    $foto_url = 'uploads/' . $objek['foto_pertama'];
                    $has_foto = true;
                }
                ?>
                
                <?php if($has_foto): ?>
                    <img src="<?= e($foto_url) ?>" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');" alt="Foto" class="w-full h-64 object-cover">
                    <div class="hidden w-full h-64 bg-slate-100 flex items-center justify-center border-b border-slate-200">
                        <i data-lucide="<?= $info['ic'] ?>" class="w-20 h-20 text-slate-300"></i>
                    </div>
                <?php else: ?>
                    <div class="w-full h-64 bg-slate-100 flex items-center justify-center border-b border-slate-200">
                        <i data-lucide="<?= $info['ic'] ?>" class="w-20 h-20 text-slate-300"></i>
                    </div>
                <?php endif; ?>
                <div class="p-5">
                    <div class="text-xs font-mono text-slate-400 mb-1"><?= e($objek['kode_objek']) ?></div>
                    <h1 class="text-2xl font-extrabold text-slate-900 leading-tight mb-2"><?= e($objek['nama_objek']) ?></h1>
                    <p class="text-sm text-slate-500 flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4"></i> <?= e($objek['lokasi'] ?: '-') ?></p>
                    <p class="text-xs font-mono text-slate-500 mt-1 flex items-center gap-1.5"><i data-lucide="globe" class="w-3.5 h-3.5"></i> <?= e($objek['koordinat'] ?: '-') ?></p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-5 text-center">
                <img src="<?= $qr_api ?>" alt="QR Code" class="w-40 h-40 mx-auto mb-3 rounded-xl shadow-sm">
                <a href="<?= $qr_api ?>" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:underline"><i data-lucide="download" class="w-3.5 h-3.5"></i> Download QR Code</a>
            </div>
        </div>

        <!-- KANAN: Data Lengkap & Riwayat -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Data Spesifik -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
                <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i data-lucide="info" class="w-5 h-5 text-emerald-600"></i> Informasi Detail
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                        <div class="text-xs font-bold text-slate-500 uppercase">Pemilik</div>
                        <div class="font-bold text-slate-800"><?= e($objek['pemilik'] ?: '-') ?></div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                        <div class="text-xs font-bold text-slate-500 uppercase">Organisasi</div>
                        <div class="font-bold text-slate-800"><?= e($objek['nama_org'] ?: 'Individu') ?></div>
                    </div>
                </div>

                <?php if($objek['jenis_objek'] === 'pohon'): ?>
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Nama Latin</span><span class="text-sm font-semibold italic text-slate-800"><?= e($objek['nama_latin'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Famili</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['famili'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Asal Daerah</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['asal_daerah'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Status Pohon</span><span class="text-sm font-semibold text-slate-800 capitalize"><?= e($objek['status_pohon'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Tanggal Tanam</span><span class="text-sm font-semibold text-slate-800"><?= $objek['tanggal_tanam'] ? date('d F Y', strtotime($objek['tanggal_tanam'])) : '-' ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Kategori IAS</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['kategori_ias'] ?: '-') ?></span></div>
                    </div>
                    <?php if(!empty($karakteristik)): ?>
                    <div class="mt-4"><span class="text-xs text-slate-500 font-bold uppercase block mb-2">Karakteristik</span><div class="flex flex-wrap gap-2"><?php foreach($karakteristik as $k): ?><span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold"><?= e($k) ?></span><?php endforeach; ?></div></div>
                    <?php endif; ?>
                    <?php if(!empty($manfaat)): ?>
                    <div class="mt-4"><span class="text-xs text-slate-500 font-bold uppercase block mb-2">Manfaat</span><div class="flex flex-wrap gap-2"><?php foreach($manfaat as $m): ?><span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-semibold"><?= e($m) ?></span><?php endforeach; ?></div></div>
                    <?php endif; ?>
                <?php elseif($objek['jenis_objek'] === 'sungai'): ?>
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Nama Lain Sungai</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['nama_lain_sungai'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Sumber Mata Air</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['sumber_mata_air'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Panjang Sungai</span><span class="text-sm font-semibold text-slate-800"><?= $objek['panjang_sungai'] ? number_format($objek['panjang_sungai'], 2) . ' km' : '-' ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Lebar Rata-rata</span><span class="text-sm font-semibold text-slate-800"><?= $objek['lebar_rata_rata'] ? number_format($objek['lebar_rata_rata'], 2) . ' m' : '-' ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Daerah Hulu</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['daerah_hulu'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Daerah Hilir</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['daerah_hilir'] ?: '-') ?></span></div>
                    </div>
                <?php elseif($objek['jenis_objek'] === 'komposting'): ?>
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Pengelola</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_pengelola'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Metode</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_metode'] ?: '-') ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Kapasitas</span><span class="text-sm font-semibold text-slate-800"><?= $objek['kompos_kapasitas'] ? number_format($objek['kompos_kapasitas'], 2) . ' kg' : '-' ?></span></div>
                        <div><span class="text-xs text-slate-500 font-bold uppercase block mb-1">Jumlah Komposter</span><span class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_jumlah_komposter'] ?: '-') ?></span></div>
                    </div>
                <?php endif; ?>
                
                <div class="mt-6 bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <div class="text-xs font-bold text-slate-500 uppercase mb-1">Catatan Pendaftaran</div>
                    <p class="text-sm text-slate-700"><?= nl2br(e($objek['catatan'] ?: 'Tidak ada catatan.')) ?></p>
                </div>
            </div>

            <!-- Riwayat Monitoring -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
                <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-3">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2"><i data-lucide="activity" class="w-5 h-5 text-emerald-600"></i> Riwayat Monitoring</h2>
                    <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-sm font-bold"><?= mysqli_num_rows($q_laporan) ?> Laporan</span>
                </div>
                
                <div class="space-y-4">
                    <?php if(mysqli_num_rows($q_laporan) > 0): while($l = mysqli_fetch_assoc($q_laporan)): ?>
                        <div class="border border-slate-200 rounded-2xl p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="text-sm font-bold text-slate-800"><?= date('d F Y, H:i', strtotime($l['tanggal_buat'])) ?></div>
                                    <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5"><i data-lucide="user" class="w-3 h-3"></i> <?= e($l['nama_kontributor']) ?> (<?= e($l['peran_kontributor']) ?>)</div>
                                </div>
                                <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase"><?= e($l['status_kondisi']) ?></span>
                            </div>
                            <p class="text-sm text-slate-600 mb-3"><?= nl2br(e($l['catatan'])) ?></p>
                            
                            <div class="flex flex-wrap gap-2">
                                <?php if($l['serapan_karbon_ilmiah'] > 0): ?>
                                    <div class="bg-amber-50 border border-amber-100 px-3 py-1.5 rounded-lg text-xs font-bold text-amber-700 flex items-center gap-1">
                                        <i data-lucide="wind" class="w-3.5 h-3.5"></i> <?= number_format($l['serapan_karbon_ilmiah'], 2) ?> kg CO₂
                                    </div>
                                <?php endif; ?>
                                <?php if($l['foto']): ?>
                                    <a href="uploads/<?= e($l['foto']) ?>" target="_blank" class="bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold text-blue-700 flex items-center gap-1 hover:bg-blue-100">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i> Foto
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="text-center py-8 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                            <p class="text-slate-500 font-bold">Belum ada riwayat monitoring.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
