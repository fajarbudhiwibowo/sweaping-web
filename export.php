<?php
require_once 'config/database.php';
require_once 'core/functions.php';

if (!is_logged_in()) { 
    header("Location: index.php?page=login"); 
    exit; 
}

$format = $_GET['format'] ?? 'csv';
$jenis_objek = $_GET['jenis'] ?? 'semua';

// Query Data (Join Laporan & Objek)
$sql = "SELECT o.kode_objek, o.nama_objek, o.nama_latin, l.tanggal_monitoring, 
               l.nama_kontributor, l.diameter_batang, l.tinggi_pohon, 
               l.serapan_karbon_ilmiah, l.skor_fhm, l.kategori_risiko_isa, 
               l.catatan 
        FROM laporan_aksi l 
        JOIN objek o ON l.objek_id = o.id 
        WHERE l.deleted_at IS NULL";

if ($jenis_objek !== 'semua') {
    $sql .= " AND l.jenis_objek = '" . mysqli_real_escape_string($conn, $jenis_objek) . "'";
}
$sql .= " ORDER BY l.tanggal_buat DESC";

$result = mysqli_query($conn, $sql);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$filename = "Data_Monitoring_Sweaping_" . date('Ymd_His');

// ==========================================
// 1. EXPORT CSV
// ==========================================
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    // Header Kolom
    fputcsv($output, ['Kode Objek', 'Nama Objek', 'Nama Latin', 'Tanggal Monitor', 'Kontributor', 'DBH (cm)', 'Tinggi (m)', 'Karbon (kg CO2e)', 'Skor FHM', 'Risiko ISA', 'Catatan']);
    
    foreach ($data as $row) {
        fputcsv($output, [
            $row['kode_objek'],
            $row['nama_objek'],
            $row['nama_latin'],
            $row['tanggal_monitoring'],
            $row['nama_kontributor'],
            $row['diameter_batang'],
            $row['tinggi_pohon'],
            $row['serapan_karbon_ilmiah'],
            $row['skor_fhm'],
            $row['kategori_risiko_isa'],
            $row['catatan']
        ]);
    }
    fclose($output);
    exit;
}

// ==========================================
// 2. EXPORT EXCEL (.xls) VIA HTML TABLE
// ==========================================
if ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head><body>';
    echo '<table border="1">';
    echo '<tr>
            <th style="background-color: #059669; color: white;">Kode Objek</th>
            <th style="background-color: #059669; color: white;">Nama Objek</th>
            <th style="background-color: #059669; color: white;">Tanggal</th>
            <th style="background-color: #059669; color: white;">Kontributor</th>
            <th style="background-color: #059669; color: white;">DBH (cm)</th>
            <th style="background-color: #059669; color: white;">Tinggi (m)</th>
            <th style="background-color: #059669; color: white;">Karbon (kg)</th>
            <th style="background-color: #059669; color: white;">Skor FHM</th>
            <th style="background-color: #059669; color: white;">Risiko ISA</th>
          </tr>';
          
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $row['kode_objek'] . '</td>';
        echo '<td>' . $row['nama_objek'] . '</td>';
        echo '<td>' . $row['tanggal_monitoring'] . '</td>';
        echo '<td>' . $row['nama_kontributor'] . '</td>';
        echo '<td>' . $row['diameter_batang'] . '</td>';
        echo '<td>' . $row['tinggi_pohon'] . '</td>';
        echo '<td>' . $row['serapan_karbon_ilmiah'] . '</td>';
        echo '<td>' . $row['skor_fhm'] . '</td>';
        echo '<td>' . $row['kategori_risiko_isa'] . '</td>';
        echo '</tr>';
    }
    echo '</table></body></html>';
    exit;
}

// ==========================================
// 3. EXPORT PDF (Redirect ke halaman Print)
// ==========================================
if ($format === 'pdf') {
    header("Location: index.php?page=export_pdf&jenis=" . $jenis_objek);
    exit;
}
?>