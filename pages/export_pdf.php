<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$jenis_objek = $_GET['jenis'] ?? 'semua';
$where = $jenis_objek !== 'semua' ? "AND l.jenis_objek = '" . mysqli_real_escape_string($conn, $jenis_objek) . "'" : "";

$sql = "SELECT o.kode_objek, o.nama_objek, l.tanggal_monitoring, l.nama_kontributor, 
               l.diameter_batang, l.serapan_karbon_ilmiah, l.skor_fhm, l.kategori_risiko_isa
        FROM laporan_aksi l 
        JOIN objek o ON l.objek_id = o.id 
        WHERE l.deleted_at IS NULL $where
        ORDER BY l.tanggal_buat DESC";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Sweaping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { margin: 1cm; }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-white p-8">

    <div class="no-print mb-6 flex justify-between items-center bg-slate-100 p-4 rounded-xl">
        <div>
            <h1 class="text-xl font-bold">Pratinjau Cetak PDF</h1>
            <p class="text-sm text-slate-500">Gunakan fitur "Save as PDF" pada dialog print browser Anda.</p>
        </div>
        <button onclick="window.print()" class="bg-emerald-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-emerald-700">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold">Laporan Data Monitoring</h2>
        <h3 class="text-lg font-bold text-slate-700">Aplikasi SWEAPING - LSM KOMPAS</h3>
        <p class="text-sm text-slate-500">Dicetak pada: <?= date('d F Y') ?></p>
    </div>

    <table class="w-full text-sm border-collapse border border-slate-300">
        <thead>
            <tr class="bg-slate-100">
                <th class="border border-slate-300 p-2 text-left">No</th>
                <th class="border border-slate-300 p-2 text-left">Kode</th>
                <th class="border border-slate-300 p-2 text-left">Nama Objek</th>
                <th class="border border-slate-300 p-2 text-left">Tgl</th>
                <th class="border border-slate-300 p-2 text-left">DBH (cm)</th>
                <th class="border border-slate-300 p-2 text-left">Karbon (kg)</th>
                <th class="border border-slate-300 p-2 text-left">FHM</th>
                <th class="border border-slate-300 p-2 text-left">ISA</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td class="border border-slate-300 p-2"><?= $no++ ?></td>
                <td class="border border-slate-300 p-2"><?= e($row['kode_objek']) ?></td>
                <td class="border border-slate-300 p-2 font-semibold"><?= e($row['nama_objek']) ?></td>
                <td class="border border-slate-300 p-2"><?= date('d/m/Y', strtotime($row['tanggal_monitoring'])) ?></td>
                <td class="border border-slate-300 p-2"><?= $row['diameter_batang'] ?? '-' ?></td>
                <td class="border border-slate-300 p-2"><?= $row['serapan_karbon_ilmiah'] ?? '-' ?></td>
                <td class="border border-slate-300 p-2"><?= $row['skor_fhm'] ?? '-' ?></td>
                <td class="border border-slate-300 p-2 font-bold <?= $row['kategori_risiko_isa']=='Tinggi'?'text-rose-600':'' ?>"><?= $row['kategori_risiko_isa'] ?? '-' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>