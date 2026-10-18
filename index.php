<?php
require_once 'config/database.php';
require_once 'core/functions.php';

// Fungsi Audit Log (Jika belum ada di functions.php)
if (!function_exists('audit_log')) {
    function audit_log($conn, $aksi, $target_id = null, $detail = null) {
        $uid = $_SESSION['user_id'] ?? null; 
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        $stmt = mysqli_prepare($conn, "INSERT INTO audit_log (user_id,aksi,target_id,detail,ip_address,user_agent) VALUES (?,?,?,?,?,?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'isssss', $uid, $aksi, $target_id, $detail, $ip, $ua);
            mysqli_stmt_execute($stmt); 
            mysqli_stmt_close($stmt);
        }
    }
}

// ==============================================================
// 1. HANDLE SIMPAN AREA PANTAU
// ==============================================================
if (isset($_POST['aksi_simpan_area'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $my_id = (int)$_SESSION['user_id'];
    $stmt_org = mysqli_prepare($conn, "SELECT organisasi_id FROM organisasi_anggota WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_org, 'i', $my_id);
    mysqli_stmt_execute($stmt_org);
    $res_org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_org));
    $my_org_id = $res_org ? (int)$res_org['organisasi_id'] : null;
    mysqli_stmt_close($stmt_org);

    if (!$my_org_id) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda harus tergabung dalam organisasi untuk membuat area pantau.'];
        header("Location: index.php?page=area_tambah"); exit;
    }

    $nama_area = trim($_POST['nama_area'] ?? '');
    $jenis = $_POST['jenis'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $geojson = $_POST['geojson_polygon'] ?? '';
    $luas_m2 = (float)($_POST['luas_m2'] ?? 0);

    if ($nama_area === '' || $geojson === '' || $luas_m2 <= 0) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Nama area dan gambar poligon wajib diisi.'];
        header("Location: index.php?page=area_tambah"); exit;
    }

    $q_last = mysqli_query($conn, "SELECT kode FROM area_pantau ORDER BY id DESC LIMIT 1");
    $last = mysqli_fetch_assoc($q_last);
    $next_num = $last ? ((int)substr($last['kode'], -3) + 1) : 1;
    $kode = 'AREA-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    $stmt_ins = mysqli_prepare($conn, "INSERT INTO area_pantau (kode, organisasi_id, nama_area, jenis, geojson_polygon, luas_m2, deskripsi, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_ins, 'sisssdsi', $kode, $my_org_id, $nama_area, $jenis, $geojson, $luas_m2, $deskripsi, $my_id);
    
    if (mysqli_stmt_execute($stmt_ins)) {
        mysqli_stmt_close($stmt_ins);
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Area pantau berhasil disimpan.'];
        cek_dan_update_misi($conn, $my_id, 'gambar_area');
        header("Location: index.php?page=area"); exit;
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt_ins)];
        header("Location: index.php?page=area_tambah"); exit;
    }
}

// ==============================================================
// 2. HANDLE AJUKAN GABUNG ORGANISASI
// ==============================================================
if (isset($_POST['aksi_gabung'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $org_id = (int)($_POST['organisasi_id'] ?? 0);
    $my_id = (int)$_SESSION['user_id'];
    $q_kembali = urlencode($_POST['q'] ?? '');

    if ($org_id > 0) {
        $stmt_cek = mysqli_prepare($conn, "SELECT 1 FROM organisasi_anggota WHERE organisasi_id = ? AND user_id = ? LIMIT 1");
        if ($stmt_cek) {
            mysqli_stmt_bind_param($stmt_cek, 'ii', $org_id, $my_id);
            mysqli_stmt_execute($stmt_cek);
            mysqli_stmt_store_result($stmt_cek);
            
            if (mysqli_stmt_num_rows($stmt_cek) == 0) {
                $stmt_ins = mysqli_prepare($conn, "INSERT INTO organisasi_permintaan (organisasi_id, user_id, status) VALUES (?, ?, 'pending') ON DUPLICATE KEY UPDATE status = 'pending'");
                if ($stmt_ins) {
                    mysqli_stmt_bind_param($stmt_ins, 'ii', $org_id, $my_id);
                    if (mysqli_stmt_execute($stmt_ins)) {
                        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Permintaan gabung berhasil dikirim. Menunggu persetujuan PIC.'];
                    } else {
                        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt_ins)];
                    }
                    mysqli_stmt_close($stmt_ins);
                }
            } else {
                $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda sudah menjadi anggota organisasi ini.'];
            }
            mysqli_stmt_close($stmt_cek);
        }
    }
    header("Location: index.php?page=cari&tab=organisasi&q=$q_kembali");
    exit;
}

// ==============================================================
// 3. HANDLE TERIMA/TOLAK GABUNG (AJAX JSON)
// ==============================================================
if (isset($_GET['page']) && $_GET['page'] === 'aksi_respond_org' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!is_logged_in()) { echo json_encode(['status'=>'error', 'message'=>'Not logged in']); exit; }
    
    $req_id = (int)($_POST['req_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $my_id = (int)$_SESSION['user_id'];
    
    if ($req_id <= 0 || !in_array($action, ['accepted', 'rejected'])) { echo json_encode(['status'=>'error', 'message'=>'Invalid param']); exit; }
    
    $stmt = mysqli_prepare($conn, "SELECT r.organisasi_id, r.user_id, o.pic_user_id FROM organisasi_permintaan r JOIN organisasi o ON r.organisasi_id = o.id WHERE r.id = ? AND r.status = 'pending' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $req_id);
    mysqli_stmt_execute($stmt);
    $req = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$req) { echo json_encode(['status'=>'error', 'message'=>'Request not found']); exit; }
    if ((int)$req['pic_user_id'] !== $my_id && !is_admin()) { echo json_encode(['status'=>'error', 'message'=>'Unauthorized']); exit; }
    
    mysqli_begin_transaction($conn);
    try {
        $stmt_u = mysqli_prepare($conn, "UPDATE organisasi_permintaan SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_u, 'si', $action, $req_id);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);
        
        if ($action === 'accepted') {
            $stmt_a = mysqli_prepare($conn, "INSERT INTO organisasi_anggota (organisasi_id, user_id, role) VALUES (?, ?, 'anggota') ON DUPLICATE KEY UPDATE role = 'anggota'");
            mysqli_stmt_bind_param($stmt_a, 'ii', $req['organisasi_id'], $req['user_id']);
            mysqli_stmt_execute($stmt_a);
            mysqli_stmt_close($stmt_a);
        }
        mysqli_commit($conn);
        echo json_encode(['status'=>'ok']);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]);
    }
    exit;
}

// ==============================================================
// 4. HANDLE HAPUS & UPDATE AREA PANTAU
// ==============================================================
if (isset($_POST['aksi_hapus_area'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $area_id = (int)$_POST['area_id'];
    $my_id = (int)$_SESSION['user_id'];

    $stmt_cek = mysqli_prepare($conn, "SELECT a.created_by, o.pic_user_id FROM area_pantau a JOIN organisasi o ON a.organisasi_id = o.id WHERE a.id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_cek, 'i', $area_id);
    mysqli_stmt_execute($stmt_cek);
    $area = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
    mysqli_stmt_close($stmt_cek);

    if ($area && (is_admin() || $area['pic_user_id'] == $my_id || $area['created_by'] == $my_id)) {
        $stmt = mysqli_prepare($conn, "UPDATE area_pantau SET deleted_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $area_id);
        mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Area pantau berhasil dihapus.'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda tidak berhak menghapus area ini.'];
    }
    header("Location: index.php?page=area"); exit;
}

if (isset($_POST['aksi_update_area'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $area_id = (int)$_POST['area_id'];
    $my_id = (int)$_SESSION['user_id'];

    $stmt_cek = mysqli_prepare($conn, "SELECT a.created_by, o.pic_user_id FROM area_pantau a JOIN organisasi o ON a.organisasi_id = o.id WHERE a.id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_cek, 'i', $area_id);
    mysqli_stmt_execute($stmt_cek);
    $area = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
    mysqli_stmt_close($stmt_cek);

    if ($area && (is_admin() || $area['pic_user_id'] == $my_id || $area['created_by'] == $my_id)) {
        $nama = trim($_POST['nama_area']);
        $jenis = $_POST['jenis'];
        $desk = trim($_POST['deskripsi']);
        $geojson = $_POST['geojson_polygon'];
        $luas = (float)$_POST['luas_m2'];

        $stmt = mysqli_prepare($conn, "UPDATE area_pantau SET nama_area = ?, jenis = ?, deskripsi = ?, geojson_polygon = ?, luas_m2 = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssssdi', $nama, $jenis, $desk, $geojson, $luas, $area_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Area pantau berhasil diperbarui.'];
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal update: ' . mysqli_stmt_error($stmt)];
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda tidak berhak mengedit area ini.'];
    }
    header("Location: index.php?page=area"); exit;
}

// ==============================================================
// 5. HANDLE SIMPAN LAPORAN
// ==============================================================

// ==============================================================
// 5. HANDLE SIMPAN LAPORAN (BARU - LENGKAP)
// ==============================================================
if (isset($_POST['aksi_simpan_laporan'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $objek_id = (int)($_POST['objek_id'] ?? 0);
    
    if ($objek_id <= 0) { 
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Objek tidak valid.']; 
        header("Location: index.php?page=lapor"); exit; 
    }
    
    $stmt_o = mysqli_prepare($conn, "SELECT nama_objek, koordinat, jenis_objek FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt_o, 'i', $objek_id);
    mysqli_stmt_execute($stmt_o);
    $obj = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_o));
    mysqli_stmt_close($stmt_o);
    
    if (!$obj) { 
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Objek tidak ditemukan.']; 
        header("Location: index.php?page=lapor"); exit; 
    }
    
    $jenis_objek = $obj['jenis_objek'];
    $nama_objek = $obj['nama_objek'];
    $koordinat = $obj['koordinat'];
    
    $status_kondisi      = trim($_POST['status_kondisi'] ?? 'Aman / Bersih / Sehat');
    if ($status_kondisi === '') $status_kondisi = 'Aman / Bersih / Sehat';
    $nama_kontributor    = trim($_POST['nama_kontributor'] ?? '');
    $peran_kontributor   = trim($_POST['peran_kontributor'] ?? '');
    $catatan             = trim($_POST['catatan'] ?? '');
    $visibility          = trim($_POST['visibility'] ?? 'publik');
    if (!in_array($visibility, ['publik','teman','privat'], true)) $visibility = 'publik';
    $tanggal_monitoring  = trim($_POST['tanggal_monitoring'] ?? '') ?: null;
    
    $lingkar_batang      = trim($_POST['lingkar_batang'] ?? ''); $lingkar_batang = $lingkar_batang === '' ? null : (float)$lingkar_batang;
    $penyakit            = trim($_POST['penyakit'] ?? '') ?: null;
    $status_pohon        = trim($_POST['status_pohon'] ?? '') ?: null;
    $mitigasi            = trim($_POST['mitigasi'] ?? '') ?: null;
    
    $kompos_volume_bahan_masuk   = trim($_POST['kompos_volume_bahan_masuk'] ?? ''); $kompos_volume_bahan_masuk = $kompos_volume_bahan_masuk === '' ? null : (float)$kompos_volume_bahan_masuk;
    $kompos_frekuensi_pemasukan  = trim($_POST['kompos_frekuensi_pemasukan'] ?? '') ?: null;
    $kompos_frekuensi_pengadukan = trim($_POST['kompos_frekuensi_pengadukan'] ?? '') ?: null;
    $kompos_lama_proses          = trim($_POST['kompos_lama_proses'] ?? ''); $kompos_lama_proses = $kompos_lama_proses === '' ? null : (int)$kompos_lama_proses;
    
    $diameter_batang        = trim($_POST['diameter_batang'] ?? ''); $diameter_batang = $diameter_batang === '' ? null : (float)$diameter_batang;
    $tinggi_pohon           = trim($_POST['tinggi_pohon'] ?? ''); $tinggi_pohon = $tinggi_pohon === '' ? null : (float)$tinggi_pohon;
    $lebar_kanopi           = trim($_POST['lebar_kanopi'] ?? ''); $lebar_kanopi = $lebar_kanopi === '' ? null : (float)$lebar_kanopi;
    $lingkungan_tumbuh      = trim($_POST['lingkungan_tumbuh'] ?? '') ?: null;
    $kondisi_pertumbuhan    = trim($_POST['kondisi_pertumbuhan'] ?? '') ?: null;
    $kondisi_batang         = trim($_POST['kondisi_batang'] ?? '') ?: null;
    $kondisi_kanopi         = trim($_POST['kondisi_kanopi'] ?? '') ?: null;
    $kondisi_akar           = trim($_POST['kondisi_akar'] ?? '') ?: null;
    $kondisi_cabang         = trim($_POST['kondisi_cabang'] ?? '') ?: null;
    $kondisi_daun           = trim($_POST['kondisi_daun'] ?? '') ?: null;
    $kondisi_buah           = trim($_POST['kondisi_buah'] ?? '') ?: null;
    $kondisi_bunga          = trim($_POST['kondisi_bunga'] ?? '') ?: null;
    $kondisi_lainnya        = trim($_POST['kondisi_lainnya'] ?? '') ?: null;
    $penilaian_kesehatan    = trim($_POST['penilaian_kesehatan'] ?? '') ?: null;
    $risiko                 = trim($_POST['risiko'] ?? '') ?: null;
    $rekomendasi            = trim($_POST['rekomendasi'] ?? '') ?: null;
    $pic_monev_nama         = trim($_POST['pic_monev_nama'] ?? '') ?: null;
    $pic_monev_peran        = trim($_POST['pic_monev_peran'] ?? '') ?: null;
    
    $debit_air_normal       = trim($_POST['debit_air_normal'] ?? ''); $debit_air_normal = $debit_air_normal === '' ? null : (float)$debit_air_normal;
    $debit_air_pasang       = trim($_POST['debit_air_pasang'] ?? ''); $debit_air_pasang = $debit_air_pasang === '' ? null : (float)$debit_air_pasang;
    $debit_air_surut        = trim($_POST['debit_air_surut'] ?? ''); $debit_air_surut = $debit_air_surut === '' ? null : (float)$debit_air_surut;
    $tinggi_muka_air        = trim($_POST['tinggi_muka_air'] ?? ''); $tinggi_muka_air = $tinggi_muka_air === '' ? null : (float)$tinggi_muka_air;
    $lebar_sungai_monitor   = trim($_POST['lebar_sungai_monitor'] ?? ''); $lebar_sungai_monitor = $lebar_sungai_monitor === '' ? null : (float)$lebar_sungai_monitor;
    $kedalaman_air          = trim($_POST['kedalaman_air'] ?? ''); $kedalaman_air = $kedalaman_air === '' ? null : (float)$kedalaman_air;
    $kondisi_debit          = trim($_POST['kondisi_debit'] ?? '') ?: null;
    $kondisi_air_visual     = trim($_POST['kondisi_air_visual'] ?? '') ?: null;
    $warna_air              = trim($_POST['warna_air'] ?? '') ?: null;
    $bau_air                = trim($_POST['bau_air'] ?? '') ?: null;
    $sampah_sungai          = trim($_POST['sampah_sungai'] ?? '') ?: null;
    $vegetasi_sempadan      = trim($_POST['vegetasi_sempadan'] ?? '') ?: null;
    $erosi_sungai           = trim($_POST['erosi_sungai'] ?? '') ?: null;
    $kualitas_ph            = trim($_POST['kualitas_ph'] ?? ''); $kualitas_ph = $kualitas_ph === '' ? null : (float)$kualitas_ph;
    $kualitas_suhu          = trim($_POST['kualitas_suhu'] ?? ''); $kualitas_suhu = $kualitas_suhu === '' ? null : (float)$kualitas_suhu;
    $kualitas_tds           = trim($_POST['kualitas_tds'] ?? ''); $kualitas_tds = $kualitas_tds === '' ? null : (float)$kualitas_tds;
    $kualitas_do            = trim($_POST['kualitas_do'] ?? ''); $kualitas_do = $kualitas_do === '' ? null : (float)$kualitas_do;
    $kualitas_kekeruhan     = trim($_POST['kualitas_kekeruhan'] ?? ''); $kualitas_kekeruhan = $kualitas_kekeruhan === '' ? null : (float)$kualitas_kekeruhan;
    $kondisi_vegetasi       = trim($_POST['kondisi_vegetasi'] ?? '') ?: null;
    $kondisi_fasilitas      = trim($_POST['kondisi_fasilitas'] ?? '') ?: null;
    $kebersihan_taman       = trim($_POST['kebersihan_taman'] ?? '') ?: null;
    $kondisi_lingkungan     = trim($_POST['kondisi_lingkungan'] ?? '') ?: null;
    $kondisi_drainase       = trim($_POST['kondisi_drainase'] ?? '') ?: null;
    
    $kompos_kelembapan           = trim($_POST['kompos_kelembapan'] ?? '') ?: null;
    $kompos_suhu                 = trim($_POST['kompos_suhu'] ?? ''); $kompos_suhu = $kompos_suhu === '' ? null : (float)$kompos_suhu;
    $kompos_aerasi               = trim($_POST['kompos_aerasi'] ?? '') ?: null;
    $kompos_bau                  = trim($_POST['kompos_bau'] ?? '') ?: null;
    $kompos_hama                 = trim($_POST['kompos_hama'] ?? '') ?: null;
    $kompos_kecepatan_penguraian = trim($_POST['kompos_kecepatan_penguraian'] ?? '') ?: null;
    $kompos_kondisi_wadah        = trim($_POST['kompos_kondisi_wadah'] ?? '') ?: null;
    $kompos_kebersihan_area      = trim($_POST['kompos_kebersihan_area'] ?? '') ?: null;
    $kompos_status               = trim($_POST['kompos_status'] ?? '') ?: null;
    $kompos_warna                = trim($_POST['kompos_warna'] ?? '') ?: null;
    $kompos_tekstur              = trim($_POST['kompos_tekstur'] ?? '') ?: null;
    $kompos_aroma                = trim($_POST['kompos_aroma'] ?? '') ?: null;
    $kompos_kematangan           = trim($_POST['kompos_kematangan'] ?? '') ?: null;
    $kompos_kebersihan_anorganik = trim($_POST['kompos_kebersihan_anorganik'] ?? '') ?: null;
    $kompos_volume_awal          = trim($_POST['kompos_volume_awal'] ?? ''); $kompos_volume_awal = $kompos_volume_awal === '' ? null : (float)$kompos_volume_awal;
    $kompos_volume_akhir         = trim($_POST['kompos_volume_akhir'] ?? ''); $kompos_volume_akhir = $kompos_volume_akhir === '' ? null : (float)$kompos_volume_akhir;
    $kompos_berat_kompos         = trim($_POST['kompos_berat_kompos'] ?? ''); $kompos_berat_kompos = $kompos_berat_kompos === '' ? null : (float)$kompos_berat_kompos;
    $kompos_pemanfaatan_arr      = $_POST['kompos_pemanfaatan_arr'] ?? []; if (!is_array($kompos_pemanfaatan_arr)) $kompos_pemanfaatan_arr = [];
    $kompos_pemanfaatan_json     = !empty($kompos_pemanfaatan_arr) ? json_encode(array_values($kompos_pemanfaatan_arr)) : null;
    $kompos_jumlah_dimanfaatkan  = trim($_POST['kompos_jumlah_dimanfaatkan'] ?? ''); $kompos_jumlah_dimanfaatkan = $kompos_jumlah_dimanfaatkan === '' ? null : (float)$kompos_jumlah_dimanfaatkan;
    $kompos_jumlah_dijual        = trim($_POST['kompos_jumlah_dijual'] ?? ''); $kompos_jumlah_dijual = $kompos_jumlah_dijual === '' ? null : (float)$kompos_jumlah_dijual;
    $kompos_nilai_penjualan      = trim($_POST['kompos_nilai_penjualan'] ?? ''); $kompos_nilai_penjualan = $kompos_nilai_penjualan === '' ? null : (float)$kompos_nilai_penjualan;
    $kompos_dampak               = trim($_POST['kompos_dampak'] ?? '') ?: null;
    $kompos_status_operasional   = trim($_POST['kompos_status_operasional'] ?? '') ?: null;
    $kompos_faktor_arr           = $_POST['kompos_faktor_arr'] ?? []; if (!is_array($kompos_faktor_arr)) $kompos_faktor_arr = [];
    $kompos_faktor_json          = !empty($kompos_faktor_arr) ? json_encode(array_values($kompos_faktor_arr)) : null;

    // ============ KALKULASI KARBON & UMUR ============
    $umur_hari_kolom = null; $umur_teks_kolom = null;
    $serapan_karbon = 0; $serapan_karbon_ilmiah = 0; $metode_hitung = null; $umur_bulan = 0;
    
    if ($jenis_objek === 'pohon') {
        $stmt_tgl = mysqli_prepare($conn, "SELECT tanggal_tanam FROM objek WHERE id=?");
        mysqli_stmt_bind_param($stmt_tgl, 'i', $objek_id);
        mysqli_stmt_execute($stmt_tgl);
        $res_tgl = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_tgl));
        mysqli_stmt_close($stmt_tgl);
        
        $tgl_tanam_objek = $res_tgl['tanggal_tanam'] ?? null;
        $tgl_monitoring_val = $tanggal_monitoring ?: date('Y-m-d');
        $hasil_umur = hitung_umur($tgl_tanam_objek, $tgl_monitoring_val);
        if ($hasil_umur) {
            $umur_hari_kolom = $hasil_umur['total_hari'];
            $umur_teks_kolom = $hasil_umur['teks_full'];
            $umur_bulan = (int)($hasil_umur['total_hari'] / 30);
        }
        $co2_chave = chave_2005($diameter_batang, $tinggi_pohon, 0.6);
        if ($co2_chave !== null) {
            $serapan_karbon = $co2_chave;
            $serapan_karbon_ilmiah = $co2_chave;
            $metode_hitung = 'chave_2005';
        } else if ($umur_bulan > 0) {
            $serapan_karbon = $umur_bulan * 2.5;
            $metode_hitung = 'umur_fallback';
        }
    } elseif ($jenis_objek === 'komposting') {
        $vol = (float)($_POST['volume_sampah'] ?? $kompos_volume_bahan_masuk ?? 0);
        $serapan_karbon = round($vol * 0.7, 2);
        $serapan_karbon_ilmiah = $serapan_karbon;
        $metode_hitung = 'ipcc_kompos';
    }

    $volume_sampah = (int)($_POST['volume_sampah'] ?? 0);
    $hasil_kompos = (int)($_POST['hasil_kompos'] ?? 0);
    
    // ============ UPLOAD FOTO ============
    $nama_foto = null;
    $telegram_file_id = null; $telegram_message_id = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        if ($file['size'] <= MAX_FILE_SIZE) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); 
            $mime = finfo_file($finfo, $file['tmp_name']); 
            finfo_close($finfo);
            if (in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
                if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
                if ($gd_aktif) {
                    $img = null;
                    if ($mime === 'image/jpeg') $img = @imagecreatefromjpeg($file['tmp_name']);
                    elseif ($mime === 'image/png') $img = @imagecreatefrompng($file['tmp_name']);
                    elseif ($mime === 'image/webp') $img = @imagecreatefromwebp($file['tmp_name']);
                    if ($img) {
                        $w = imagesx($img); $h = imagesy($img);
                        $scale = min(MAX_DIMENSION / $w, MAX_DIMENSION / $h, 1);
                        $new_w = (int)($w * $scale); $new_h = (int)($h * $scale);
                        $new_img = imagecreatetruecolor($new_w, $new_h);
                        if ($mime === 'image/png' || $mime === 'image/webp') { imagealphablending($new_img, false); imagesavealpha($new_img, true); }
                        imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                        $nama_foto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
                        imagejpeg($new_img, UPLOAD_DIR . $nama_foto, JPEG_QUALITY);
                        imagedestroy($img); imagedestroy($new_img);
                    }
                } else { 
                    $nama_foto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg'; 
                    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_foto); 
                }
            }
        }
    }

    if ($nama_foto && file_exists(UPLOAD_DIR . $nama_foto)) {
        $caption = "📷 " . strtoupper($jenis_objek) . " - " . $nama_objek . "\n📍 " . $koordinat . "\n📝 " . mb_substr($catatan, 0, 200);
        $tg_result = upload_ke_telegram(UPLOAD_DIR . $nama_foto, $jenis_objek, $caption);
        if ($tg_result) {
            $telegram_file_id = $tg_result['file_id'];
            $telegram_message_id = $tg_result['message_id'];
            @unlink(UPLOAD_DIR . $nama_foto); 
            $nama_foto = null;
        }
    }

    // ============ SIMPAN KE DATABASE (ARRAY DINAMIS) ============
    $insert_data = [
        'objek_id' => $objek_id, 'user_id' => $uid, 'jenis_objek' => $jenis_objek,
        'nama_objek' => $nama_objek, 'koordinat' => $koordinat, 'status_kondisi' => $status_kondisi,
        'nama_kontributor' => $nama_kontributor, 'peran_kontributor' => $peran_kontributor,
        'catatan' => $catatan, 'visibility' => $visibility, 'tanggal_monitoring' => $tanggal_monitoring,
        'umur_bulan' => $umur_bulan, 'serapan_karbon' => $serapan_karbon,
        'umur_hari' => $umur_hari_kolom, 'umur_teks' => $umur_teks_kolom,
        'serapan_karbon_ilmiah' => $serapan_karbon_ilmiah, 'metode_hitung' => $metode_hitung,
        'volume_sampah' => $volume_sampah, 'hasil_kompos' => $hasil_kompos,
        'foto' => $nama_foto, 'telegram_file_id' => $telegram_file_id, 'telegram_message_id' => $telegram_message_id,
        'diameter_batang' => $diameter_batang, 'tinggi_pohon' => $tinggi_pohon, 'lingkar_batang' => $lingkar_batang, 'lebar_kanopi' => $lebar_kanopi,
        'lingkungan_tumbuh' => $lingkungan_tumbuh, 'kondisi_pertumbuhan' => $kondisi_pertumbuhan, 'kondisi_batang' => $kondisi_batang,
        'kondisi_kanopi' => $kondisi_kanopi, 'kondisi_akar' => $kondisi_akar, 'kondisi_cabang' => $kondisi_cabang,
        'kondisi_daun' => $kondisi_daun, 'kondisi_buah' => $kondisi_buah, 'kondisi_bunga' => $kondisi_bunga,
        'kondisi_lainnya' => $kondisi_lainnya, 'penilaian_kesehatan' => $penilaian_kesehatan,
        'penyakit' => $penyakit, 'status_pohon' => $status_pohon, 'risiko' => $risiko, 'mitigasi' => $mitigasi, 'rekomendasi' => $rekomendasi,
        'pic_monev_nama' => $pic_monev_nama, 'pic_monev_peran' => $pic_monev_peran,
        'debit_air_normal' => $debit_air_normal, 'debit_air_pasang' => $debit_air_pasang, 'debit_air_surut' => $debit_air_surut,
        'tinggi_muka_air' => $tinggi_muka_air, 'lebar_sungai_monitor' => $lebar_sungai_monitor, 'kedalaman_air' => $kedalaman_air,
        'kondisi_debit' => $kondisi_debit, 'kondisi_air_visual' => $kondisi_air_visual, 'warna_air' => $warna_air,
        'bau_air' => $bau_air, 'sampah_sungai' => $sampah_sungai, 'vegetasi_sempadan' => $vegetasi_sempadan, 'erosi_sungai' => $erosi_sungai,
        'kualitas_ph' => $kualitas_ph, 'kualitas_suhu' => $kualitas_suhu, 'kualitas_tds' => $kualitas_tds,
        'kualitas_do' => $kualitas_do, 'kualitas_kekeruhan' => $kualitas_kekeruhan,
        'kondisi_vegetasi' => $kondisi_vegetasi, 'kondisi_fasilitas' => $kondisi_fasilitas, 'kebersihan_taman' => $kebersihan_taman,
        'kondisi_lingkungan' => $kondisi_lingkungan, 'kondisi_drainase' => $kondisi_drainase,
        'kompos_volume_bahan_masuk' => $kompos_volume_bahan_masuk, 'kompos_frekuensi_pemasukan' => $kompos_frekuensi_pemasukan,
        'kompos_frekuensi_pengadukan' => $kompos_frekuensi_pengadukan, 'kompos_lama_proses' => $kompos_lama_proses,
        'kompos_kelembapan' => $kompos_kelembapan, 'kompos_suhu' => $kompos_suhu, 'kompos_aerasi' => $kompos_aerasi,
        'kompos_bau' => $kompos_bau, 'kompos_hama' => $kompos_hama, 'kompos_kecepatan_penguraian' => $kompos_kecepatan_penguraian,
        'kompos_kondisi_wadah' => $kompos_kondisi_wadah, 'kompos_kebersihan_area' => $kompos_kebersihan_area,
        'kompos_status' => $kompos_status, 'kompos_warna' => $kompos_warna, 'kompos_tekstur' => $kompos_tekstur,
        'kompos_aroma' => $kompos_aroma, 'kompos_kematangan' => $kompos_kematangan, 'kompos_kebersihan_anorganik' => $kompos_kebersihan_anorganik,
        'kompos_volume_awal' => $kompos_volume_awal, 'kompos_volume_akhir' => $kompos_volume_akhir, 'kompos_berat_kompos' => $kompos_berat_kompos,
        'kompos_pemanfaatan' => $kompos_pemanfaatan_json, 'kompos_jumlah_dimanfaatkan' => $kompos_jumlah_dimanfaatkan,
        'kompos_jumlah_dijual' => $kompos_jumlah_dijual, 'kompos_nilai_penjualan' => $kompos_nilai_penjualan,
        'kompos_dampak' => $kompos_dampak, 'kompos_status_operasional' => $kompos_status_operasional,
        'kompos_faktor_keberlanjutan' => $kompos_faktor_json
    ];
    
    $cols = []; $placeholders = []; $types = ''; $vals = [];
    foreach ($insert_data as $col => $val) {
        $cols[] = $col; $placeholders[] = '?';
        if (is_int($val)) $types .= 'i';
        elseif (is_float($val)) $types .= 'd';
        else $types .= 's';
        $vals[] = $val;
    }
    
    $sql = "INSERT INTO laporan_aksi (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$vals);
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            audit_log($conn, 'buat_laporan', $new_id, "Objek: $nama_objek");
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Laporan berhasil direkam!'];
            header("Location: index.php?page=arsip"); exit;
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt)];
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal prepare: ' . mysqli_error($conn)];
    }
    header("Location: index.php?page=lapor"); exit;
}

// ==============================================================
// 6. HANDLE UPDATE LAPORAN (EDIT)
// ==============================================================
if (isset($_POST['aksi_update_laporan'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'ID laporan tidak valid.'];
        header("Location: index.php?page=arsip"); exit;
    }
    
    $stmt_old = mysqli_prepare($conn, "SELECT user_id, jenis_objek, objek_id FROM laporan_aksi WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt_old, 'i', $id);
    mysqli_stmt_execute($stmt_old);
    $old_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_old));
    mysqli_stmt_close($stmt_old);
    
    if (!$old_data) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Laporan tidak ditemukan.'];
        header("Location: index.php?page=arsip"); exit;
    }
    if ($old_data['user_id'] != $uid && !is_admin()) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Tidak berhak mengedit laporan ini.'];
        header("Location: index.php?page=arsip"); exit;
    }
    
    $jenis_objek = $old_data['jenis_objek'];
    $objek_id = $old_data['objek_id'];
    
    // Ambil data POST
    $status_kondisi      = trim($_POST['status_kondisi'] ?? 'Aman / Bersih / Sehat');
    if ($status_kondisi === '') $status_kondisi = 'Aman / Bersih / Sehat';
    $nama_kontributor    = trim($_POST['nama_kontributor'] ?? '');
    $peran_kontributor   = trim($_POST['peran_kontributor'] ?? '');
    $catatan             = trim($_POST['catatan'] ?? '');
    $visibility          = trim($_POST['visibility'] ?? 'publik');
    if (!in_array($visibility, ['publik','teman','privat'], true)) $visibility = 'publik';
    $tanggal_monitoring  = trim($_POST['tanggal_monitoring'] ?? '') ?: null;
    
    $lingkar_batang      = trim($_POST['lingkar_batang'] ?? ''); $lingkar_batang = $lingkar_batang === '' ? null : (float)$lingkar_batang;
    $penyakit            = trim($_POST['penyakit'] ?? '') ?: null;
    $status_pohon        = trim($_POST['status_pohon'] ?? '') ?: null;
    $mitigasi            = trim($_POST['mitigasi'] ?? '') ?: null;
    
    $kompos_volume_bahan_masuk   = trim($_POST['kompos_volume_bahan_masuk'] ?? ''); $kompos_volume_bahan_masuk = $kompos_volume_bahan_masuk === '' ? null : (float)$kompos_volume_bahan_masuk;
    $kompos_frekuensi_pemasukan  = trim($_POST['kompos_frekuensi_pemasukan'] ?? '') ?: null;
    $kompos_frekuensi_pengadukan = trim($_POST['kompos_frekuensi_pengadukan'] ?? '') ?: null;
    $kompos_lama_proses          = trim($_POST['kompos_lama_proses'] ?? ''); $kompos_lama_proses = $kompos_lama_proses === '' ? null : (int)$kompos_lama_proses;
    
    $diameter_batang        = trim($_POST['diameter_batang'] ?? ''); $diameter_batang = $diameter_batang === '' ? null : (float)$diameter_batang;
    $tinggi_pohon           = trim($_POST['tinggi_pohon'] ?? ''); $tinggi_pohon = $tinggi_pohon === '' ? null : (float)$tinggi_pohon;
    $lebar_kanopi           = trim($_POST['lebar_kanopi'] ?? ''); $lebar_kanopi = $lebar_kanopi === '' ? null : (float)$lebar_kanopi;
    $lingkungan_tumbuh      = trim($_POST['lingkungan_tumbuh'] ?? '') ?: null;
    $kondisi_pertumbuhan    = trim($_POST['kondisi_pertumbuhan'] ?? '') ?: null;
    $kondisi_batang         = trim($_POST['kondisi_batang'] ?? '') ?: null;
    $kondisi_kanopi         = trim($_POST['kondisi_kanopi'] ?? '') ?: null;
    $kondisi_akar           = trim($_POST['kondisi_akar'] ?? '') ?: null;
    $kondisi_cabang         = trim($_POST['kondisi_cabang'] ?? '') ?: null;
    $kondisi_daun           = trim($_POST['kondisi_daun'] ?? '') ?: null;
    $kondisi_buah           = trim($_POST['kondisi_buah'] ?? '') ?: null;
    $kondisi_bunga          = trim($_POST['kondisi_bunga'] ?? '') ?: null;
    $kondisi_lainnya        = trim($_POST['kondisi_lainnya'] ?? '') ?: null;
    $penilaian_kesehatan    = trim($_POST['penilaian_kesehatan'] ?? '') ?: null;
    $risiko                 = trim($_POST['risiko'] ?? '') ?: null;
    $rekomendasi            = trim($_POST['rekomendasi'] ?? '') ?: null;
    $pic_monev_nama         = trim($_POST['pic_monev_nama'] ?? '') ?: null;
    $pic_monev_peran        = trim($_POST['pic_monev_peran'] ?? '') ?: null;
    
    $debit_air_normal       = trim($_POST['debit_air_normal'] ?? ''); $debit_air_normal = $debit_air_normal === '' ? null : (float)$debit_air_normal;
    $debit_air_pasang       = trim($_POST['debit_air_pasang'] ?? ''); $debit_air_pasang = $debit_air_pasang === '' ? null : (float)$debit_air_pasang;
    $debit_air_surut        = trim($_POST['debit_air_surut'] ?? ''); $debit_air_surut = $debit_air_surut === '' ? null : (float)$debit_air_surut;
    $tinggi_muka_air        = trim($_POST['tinggi_muka_air'] ?? ''); $tinggi_muka_air = $tinggi_muka_air === '' ? null : (float)$tinggi_muka_air;
    $lebar_sungai_monitor   = trim($_POST['lebar_sungai_monitor'] ?? ''); $lebar_sungai_monitor = $lebar_sungai_monitor === '' ? null : (float)$lebar_sungai_monitor;
    $kedalaman_air          = trim($_POST['kedalaman_air'] ?? ''); $kedalaman_air = $kedalaman_air === '' ? null : (float)$kedalaman_air;
    $kondisi_debit          = trim($_POST['kondisi_debit'] ?? '') ?: null;
    $kondisi_air_visual     = trim($_POST['kondisi_air_visual'] ?? '') ?: null;
    $warna_air              = trim($_POST['warna_air'] ?? '') ?: null;
    $bau_air                = trim($_POST['bau_air'] ?? '') ?: null;
    $sampah_sungai          = trim($_POST['sampah_sungai'] ?? '') ?: null;
    $vegetasi_sempadan      = trim($_POST['vegetasi_sempadan'] ?? '') ?: null;
    $erosi_sungai           = trim($_POST['erosi_sungai'] ?? '') ?: null;
    $kualitas_ph            = trim($_POST['kualitas_ph'] ?? ''); $kualitas_ph = $kualitas_ph === '' ? null : (float)$kualitas_ph;
    $kualitas_suhu          = trim($_POST['kualitas_suhu'] ?? ''); $kualitas_suhu = $kualitas_suhu === '' ? null : (float)$kualitas_suhu;
    $kualitas_tds           = trim($_POST['kualitas_tds'] ?? ''); $kualitas_tds = $kualitas_tds === '' ? null : (float)$kualitas_tds;
    $kualitas_do            = trim($_POST['kualitas_do'] ?? ''); $kualitas_do = $kualitas_do === '' ? null : (float)$kualitas_do;
    $kualitas_kekeruhan     = trim($_POST['kualitas_kekeruhan'] ?? ''); $kualitas_kekeruhan = $kualitas_kekeruhan === '' ? null : (float)$kualitas_kekeruhan;
    $kondisi_vegetasi       = trim($_POST['kondisi_vegetasi'] ?? '') ?: null;
    $kondisi_fasilitas      = trim($_POST['kondisi_fasilitas'] ?? '') ?: null;
    $kebersihan_taman       = trim($_POST['kebersihan_taman'] ?? '') ?: null;
    $kondisi_lingkungan     = trim($_POST['kondisi_lingkungan'] ?? '') ?: null;
    $kondisi_drainase       = trim($_POST['kondisi_drainase'] ?? '') ?: null;
    
    $kompos_kelembapan           = trim($_POST['kompos_kelembapan'] ?? '') ?: null;
    $kompos_suhu                 = trim($_POST['kompos_suhu'] ?? ''); $kompos_suhu = $kompos_suhu === '' ? null : (float)$kompos_suhu;
    $kompos_aerasi               = trim($_POST['kompos_aerasi'] ?? '') ?: null;
    $kompos_bau                  = trim($_POST['kompos_bau'] ?? '') ?: null;
    $kompos_hama                 = trim($_POST['kompos_hama'] ?? '') ?: null;
    $kompos_kecepatan_penguraian = trim($_POST['kompos_kecepatan_penguraian'] ?? '') ?: null;
    $kompos_kondisi_wadah        = trim($_POST['kompos_kondisi_wadah'] ?? '') ?: null;
    $kompos_kebersihan_area      = trim($_POST['kompos_kebersihan_area'] ?? '') ?: null;
    $kompos_status               = trim($_POST['kompos_status'] ?? '') ?: null;
    $kompos_warna                = trim($_POST['kompos_warna'] ?? '') ?: null;
    $kompos_tekstur              = trim($_POST['kompos_tekstur'] ?? '') ?: null;
    $kompos_aroma                = trim($_POST['kompos_aroma'] ?? '') ?: null;
    $kompos_kematangan           = trim($_POST['kompos_kematangan'] ?? '') ?: null;
    $kompos_kebersihan_anorganik = trim($_POST['kompos_kebersihan_anorganik'] ?? '') ?: null;
    $kompos_volume_awal          = trim($_POST['kompos_volume_awal'] ?? ''); $kompos_volume_awal = $kompos_volume_awal === '' ? null : (float)$kompos_volume_awal;
    $kompos_volume_akhir         = trim($_POST['kompos_volume_akhir'] ?? ''); $kompos_volume_akhir = $kompos_volume_akhir === '' ? null : (float)$kompos_volume_akhir;
    $kompos_berat_kompos         = trim($_POST['kompos_berat_kompos'] ?? ''); $kompos_berat_kompos = $kompos_berat_kompos === '' ? null : (float)$kompos_berat_kompos;
    $kompos_pemanfaatan_arr      = $_POST['kompos_pemanfaatan_arr'] ?? []; if (!is_array($kompos_pemanfaatan_arr)) $kompos_pemanfaatan_arr = [];
    $kompos_pemanfaatan_json     = !empty($kompos_pemanfaatan_arr) ? json_encode(array_values($kompos_pemanfaatan_arr)) : null;
    $kompos_jumlah_dimanfaatkan  = trim($_POST['kompos_jumlah_dimanfaatkan'] ?? ''); $kompos_jumlah_dimanfaatkan = $kompos_jumlah_dimanfaatkan === '' ? null : (float)$kompos_jumlah_dimanfaatkan;
    $kompos_jumlah_dijual        = trim($_POST['kompos_jumlah_dijual'] ?? ''); $kompos_jumlah_dijual = $kompos_jumlah_dijual === '' ? null : (float)$kompos_jumlah_dijual;
    $kompos_nilai_penjualan      = trim($_POST['kompos_nilai_penjualan'] ?? ''); $kompos_nilai_penjualan = $kompos_nilai_penjualan === '' ? null : (float)$kompos_nilai_penjualan;
    $kompos_dampak               = trim($_POST['kompos_dampak'] ?? '') ?: null;
    $kompos_status_operasional   = trim($_POST['kompos_status_operasional'] ?? '') ?: null;
    $kompos_faktor_arr           = $_POST['kompos_faktor_arr'] ?? []; if (!is_array($kompos_faktor_arr)) $kompos_faktor_arr = [];
    $kompos_faktor_json          = !empty($kompos_faktor_arr) ? json_encode(array_values($kompos_faktor_arr)) : null;

    // Karbon & Umur
    $umur_hari_kolom = null; $umur_teks_kolom = null;
    $serapan_karbon = 0; $serapan_karbon_ilmiah = 0; $metode_hitung = null; $umur_bulan = 0;
    
    if ($jenis_objek === 'pohon') {
        $stmt_tgl = mysqli_prepare($conn, "SELECT tanggal_tanam FROM objek WHERE id=?");
        mysqli_stmt_bind_param($stmt_tgl, 'i', $objek_id);
        mysqli_stmt_execute($stmt_tgl);
        $res_tgl = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_tgl));
        mysqli_stmt_close($stmt_tgl);
        
        $tgl_tanam_objek = $res_tgl['tanggal_tanam'] ?? null;
        $tgl_monitoring_val = $tanggal_monitoring ?: date('Y-m-d');
        $hasil_umur = hitung_umur($tgl_tanam_objek, $tgl_monitoring_val);
        if ($hasil_umur) {
            $umur_hari_kolom = $hasil_umur['total_hari'];
            $umur_teks_kolom = $hasil_umur['teks_full'];
            $umur_bulan = (int)($hasil_umur['total_hari'] / 30);
        }
        $co2_chave = chave_2005($diameter_batang, $tinggi_pohon, 0.6);
        if ($co2_chave !== null) {
            $serapan_karbon = $co2_chave;
            $serapan_karbon_ilmiah = $co2_chave;
            $metode_hitung = 'chave_2005';
        } else if ($umur_bulan > 0) {
            $serapan_karbon = $umur_bulan * 2.5;
            $metode_hitung = 'umur_fallback';
        }
    } elseif ($jenis_objek === 'komposting') {
        $vol = (float)($_POST['volume_sampah'] ?? $kompos_volume_bahan_masuk ?? 0);
        $serapan_karbon = round($vol * 0.7, 2);
        $serapan_karbon_ilmiah = $serapan_karbon;
        $metode_hitung = 'ipcc_kompos';
    }

    $volume_sampah = (int)($_POST['volume_sampah'] ?? 0);
    $hasil_kompos = (int)($_POST['hasil_kompos'] ?? 0);
    
    // Upload Foto (optional)
    $stmt_f = mysqli_prepare($conn, "SELECT foto, telegram_file_id FROM laporan_aksi WHERE id = ?");
    mysqli_stmt_bind_param($stmt_f, 'i', $id);
    mysqli_stmt_execute($stmt_f);
    $old_f = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_f));
    mysqli_stmt_close($stmt_f);
    
    $nama_foto = $old_f['foto'] ?? null;
    $telegram_file_id = $old_f['telegram_file_id'] ?? null;
    $telegram_message_id = null; // Reset if new photo
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        if ($file['size'] <= MAX_FILE_SIZE) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); 
            $mime = finfo_file($finfo, $file['tmp_name']); 
            finfo_close($finfo);
            if (in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
                if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
                if ($gd_aktif) {
                    $img = null;
                    if ($mime === 'image/jpeg') $img = @imagecreatefromjpeg($file['tmp_name']);
                    elseif ($mime === 'image/png') $img = @imagecreatefrompng($file['tmp_name']);
                    elseif ($mime === 'image/webp') $img = @imagecreatefromwebp($file['tmp_name']);
                    if ($img) {
                        $w = imagesx($img); $h = imagesy($img);
                        $scale = min(MAX_DIMENSION / $w, MAX_DIMENSION / $h, 1);
                        $new_w = (int)($w * $scale); $new_h = (int)($h * $scale);
                        $new_img = imagecreatetruecolor($new_w, $new_h);
                        if ($mime === 'image/png' || $mime === 'image/webp') { imagealphablending($new_img, false); imagesavealpha($new_img, true); }
                        imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                        $nama_foto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
                        imagejpeg($new_img, UPLOAD_DIR . $nama_foto, JPEG_QUALITY);
                        imagedestroy($img); imagedestroy($new_img);
                    }
                } else { 
                    $nama_foto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg'; 
                    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_foto); 
                }
                if ($nama_foto) {
                    if ($old_f['foto']) @unlink(UPLOAD_DIR . $old_f['foto']);
                    $telegram_file_id = null; // trigger re-upload
                }
            }
        }
    }

    if ($nama_foto && !$telegram_file_id && file_exists(UPLOAD_DIR . $nama_foto)) {
        $caption = "📷 EDIT " . strtoupper($jenis_objek) . " - " . $_POST['nama_objek'] . "\n📍 " . $_POST['koordinat'];
        $tg_result = upload_ke_telegram(UPLOAD_DIR . $nama_foto, $jenis_objek, $caption);
        if ($tg_result) {
            $telegram_file_id = $tg_result['file_id'];
            $telegram_message_id = $tg_result['message_id'];
            @unlink(UPLOAD_DIR . $nama_foto); 
            $nama_foto = null;
        }
    }

    // Build UPDATE query dynamically
    $update_data = [
        'status_kondisi' => $status_kondisi, 'nama_kontributor' => $nama_kontributor,
        'peran_kontributor' => $peran_kontributor, 'catatan' => $catatan,
        'visibility' => $visibility, 'tanggal_monitoring' => $tanggal_monitoring,
        'umur_bulan' => $umur_bulan, 'serapan_karbon' => $serapan_karbon,
        'umur_hari' => $umur_hari_kolom, 'umur_teks' => $umur_teks_kolom,
        'serapan_karbon_ilmiah' => $serapan_karbon_ilmiah, 'metode_hitung' => $metode_hitung,
        'volume_sampah' => $volume_sampah, 'hasil_kompos' => $hasil_kompos,
        'foto' => $nama_foto, 'telegram_file_id' => $telegram_file_id, 'telegram_message_id' => $telegram_message_id,
        'diameter_batang' => $diameter_batang, 'tinggi_pohon' => $tinggi_pohon, 'lingkar_batang' => $lingkar_batang, 'lebar_kanopi' => $lebar_kanopi,
        'lingkungan_tumbuh' => $lingkungan_tumbuh, 'kondisi_pertumbuhan' => $kondisi_pertumbuhan, 'kondisi_batang' => $kondisi_batang,
        'kondisi_kanopi' => $kondisi_kanopi, 'kondisi_akar' => $kondisi_akar, 'kondisi_cabang' => $kondisi_cabang,
        'kondisi_daun' => $kondisi_daun, 'kondisi_buah' => $kondisi_buah, 'kondisi_bunga' => $kondisi_bunga,
        'kondisi_lainnya' => $kondisi_lainnya, 'penilaian_kesehatan' => $penilaian_kesehatan,
        'penyakit' => $penyakit, 'status_pohon' => $status_pohon, 'risiko' => $risiko, 'mitigasi' => $mitigasi, 'rekomendasi' => $rekomendasi,
        'pic_monev_nama' => $pic_monev_nama, 'pic_monev_peran' => $pic_monev_peran,
        'debit_air_normal' => $debit_air_normal, 'debit_air_pasang' => $debit_air_pasang, 'debit_air_surut' => $debit_air_surut,
        'tinggi_muka_air' => $tinggi_muka_air, 'lebar_sungai_monitor' => $lebar_sungai_monitor, 'kedalaman_air' => $kedalaman_air,
        'kondisi_debit' => $kondisi_debit, 'kondisi_air_visual' => $kondisi_air_visual, 'warna_air' => $warna_air,
        'bau_air' => $bau_air, 'sampah_sungai' => $sampah_sungai, 'vegetasi_sempadan' => $vegetasi_sempadan, 'erosi_sungai' => $erosi_sungai,
        'kualitas_ph' => $kualitas_ph, 'kualitas_suhu' => $kualitas_suhu, 'kualitas_tds' => $kualitas_tds,
        'kualitas_do' => $kualitas_do, 'kualitas_kekeruhan' => $kualitas_kekeruhan,
        'kondisi_vegetasi' => $kondisi_vegetasi, 'kondisi_fasilitas' => $kondisi_fasilitas, 'kebersihan_taman' => $kebersihan_taman,
        'kondisi_lingkungan' => $kondisi_lingkungan, 'kondisi_drainase' => $kondisi_drainase,
        'kompos_volume_bahan_masuk' => $kompos_volume_bahan_masuk, 'kompos_frekuensi_pemasukan' => $kompos_frekuensi_pemasukan,
        'kompos_frekuensi_pengadukan' => $kompos_frekuensi_pengadukan, 'kompos_lama_proses' => $kompos_lama_proses,
        'kompos_kelembapan' => $kompos_kelembapan, 'kompos_suhu' => $kompos_suhu, 'kompos_aerasi' => $kompos_aerasi,
        'kompos_bau' => $kompos_bau, 'kompos_hama' => $kompos_hama, 'kompos_kecepatan_penguraian' => $kompos_kecepatan_penguraian,
        'kompos_kondisi_wadah' => $kompos_kondisi_wadah, 'kompos_kebersihan_area' => $kompos_kebersihan_area,
        'kompos_status' => $kompos_status, 'kompos_warna' => $kompos_warna, 'kompos_tekstur' => $kompos_tekstur,
        'kompos_aroma' => $kompos_aroma, 'kompos_kematangan' => $kompos_kematangan, 'kompos_kebersihan_anorganik' => $kompos_kebersihan_anorganik,
        'kompos_volume_awal' => $kompos_volume_awal, 'kompos_volume_akhir' => $kompos_volume_akhir, 'kompos_berat_kompos' => $kompos_berat_kompos,
        'kompos_pemanfaatan' => $kompos_pemanfaatan_json, 'kompos_jumlah_dimanfaatkan' => $kompos_jumlah_dimanfaatkan,
        'kompos_jumlah_dijual' => $kompos_jumlah_dijual, 'kompos_nilai_penjualan' => $kompos_nilai_penjualan,
        'kompos_dampak' => $kompos_dampak, 'kompos_status_operasional' => $kompos_status_operasional,
        'kompos_faktor_keberlanjutan' => $kompos_faktor_json
    ];
    
    $set_parts = []; $types = ''; $vals = [];
    foreach ($update_data as $col => $val) {
        $set_parts[] = "$col = ?";
        if (is_int($val)) $types .= 'i';
        elseif (is_float($val)) $types .= 'd';
        else $types .= 's';
        $vals[] = $val;
    }
    $types .= 'i'; $vals[] = $id;
    
    $sql = "UPDATE laporan_aksi SET " . implode(', ', $set_parts) . " WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$vals);
        if (mysqli_stmt_execute($stmt)) {
            audit_log($conn, 'edit_laporan', $id, "Objek: " . ($_POST['nama_objek'] ?? ''));
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Laporan berhasil diperbarui!'];
            header("Location: index.php?page=arsip"); exit;
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal update: ' . mysqli_stmt_error($stmt)];
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal prepare: ' . mysqli_error($conn)];
    }
    header("Location: index.php?page=edit&id=$id"); exit;
}

// ==============================================================
// 5.5 HANDLE SIMPAN DAFTAR OBJEK BARU
// ==============================================================
if (isset($_POST['aksi_daftar_objek'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $jenis_objek = $_POST['jenis_objek'] ?? '';
    $nama_objek = trim($_POST['nama_objek'] ?? '');
    
    if ($nama_objek === '' || !in_array($jenis_objek, ['pohon','sungai','taman','kompos','hutan','lingkungan'], true)) { 
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Data tidak valid.']; 
        header("Location: index.php?page=objek_daftar"); exit; 
    }

    $to_f = fn($v) => ($v === '' || $v === null) ? null : (float)$v;
    $to_i = fn($v) => ($v === '' || $v === null) ? null : (int)$v;
    $to_s = fn($v) => ($v === '' || $v === null) ? null : trim($v);

    // Generate Kode
    $prefix_map = ['pohon'=>'TRB', 'sungai'=>'SNG', 'taman'=>'TMN', 'kompos'=>'KMP', 'hutan'=>'HTN', 'lingkungan'=>'LNG'];
    $prefix = $prefix_map[$jenis_objek] . '-' . date('Y') . '-';
    $q_last = mysqli_query($conn, "SELECT kode_objek FROM objek WHERE kode_objek LIKE '$prefix%' ORDER BY id DESC LIMIT 1");
    $last = mysqli_fetch_assoc($q_last);
    $next_num = $last ? ((int)substr($last['kode_objek'], -5) + 1) : 1;
    $kode_objek = $prefix . str_pad($next_num, 5, '0', STR_PAD_LEFT);

    // Upload Foto
    $foto_pertama = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_pertama = 'objek_' . $kode_objek . '_' . time() . '.' . $ext;
        if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
        move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $foto_pertama);
    }

    // Data
    $insert_data = [
        'kode_objek' => $kode_objek, 'jenis_objek' => $jenis_objek, 'nama_objek' => $nama_objek,
        'lokasi' => $to_s($_POST['lokasi'] ?? null), 'koordinat' => $to_s($_POST['koordinat'] ?? null),
        'user_id' => $uid, 'foto_pertama' => $foto_pertama, 'mode_input' => $_POST['mode_input'] ?? 'umum',
        'geojson_polygon' => $_POST['geojson_polygon'] ?? null,
        'luas_m2' => $to_f($_POST['luas_m2'] ?? null),
        'panjang_m' => $to_f($_POST['panjang_m'] ?? null),
        'kondisi_umum' => $to_s($_POST['kondisi_umum'] ?? null),
        
        // Ilmiah
        'metode_sampling' => $to_s($_POST['metode_sampling'] ?? null),
        'pohon_dbh' => $to_f($_POST['pohon_dbh'] ?? null),
        'pohon_carbon_stock' => $to_f($_POST['pohon_carbon_stock'] ?? null),
        'hutan_tipe' => $to_s($_POST['hutan_tipe'] ?? null),
        'hutan_shannon' => $to_f($_POST['out_shannon'] ?? null), // Ambil dari JS
        'sungai_ph' => $to_f($_POST['sungai_ph'] ?? null),
        'sungai_suhu' => $to_f($_POST['sungai_suhu'] ?? null)
    ];

    $cols = []; $placeholders = []; $types = ''; $vals = [];
    foreach ($insert_data as $col => $val) {
        $cols[] = $col; $placeholders[] = '?';
        if (is_int($val)) $types .= 'i';
        elseif (is_float($val)) $types .= 'd';
        else $types .= 's';
        $vals[] = $val;
    }
    
    $sql = "INSERT INTO objek (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$vals);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => "Objek $kode_objek berhasil didaftarkan!"];
            header("Location: index.php?page=objek"); exit;
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal: ' . mysqli_stmt_error($stmt)];
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: index.php?page=objek_daftar"); exit;
}
// ============ END SIMPAN DAFTAR OBJEK BARU ============

// ==============================================================
// 6. HANDLE HAPUS LAPORAN (SOFT DELETE)
// ==============================================================
if (isset($_POST['aksi_hapus_laporan'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $uid = (int)$_SESSION['user_id'];
    $laporan_id = (int)$_POST['laporan_id'];

    $stmt = mysqli_prepare($conn, "SELECT user_id, nama_objek FROM laporan_aksi WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $laporan_id);
    mysqli_stmt_execute($stmt);
    $lapor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($lapor && (is_admin() || $lapor['user_id'] == $uid)) {
        $stmt_del = mysqli_prepare($conn, "UPDATE laporan_aksi SET deleted_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, 'i', $laporan_id);
        mysqli_stmt_execute($stmt_del); mysqli_stmt_close($stmt_del);

        $stmt_log = mysqli_prepare($conn, "INSERT INTO audit_log (user_id, aksi, target_id, detail, ip_address) VALUES (?, 'hapus_laporan', ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $detail = "Hapus Laporan: " . $lapor['nama_objek'];
        mysqli_stmt_bind_param($stmt_log, 'iiss', $uid, $laporan_id, $detail, $ip);
        mysqli_stmt_execute($stmt_log); mysqli_stmt_close($stmt_log);

        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Laporan berhasil dihapus.'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda tidak berhak menghapus laporan ini.'];
    }
    header("Location: index.php?page=arsip"); exit;
}

// ==============================================================
// 7. HANDLE HAPUS OBJEK (SOFT DELETE)
// ==============================================================
if (isset($_POST['aksi_hapus_objek'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $uid = (int)$_SESSION['user_id'];
    $objek_id = (int)$_POST['objek_id'];

    $stmt = mysqli_prepare($conn, "SELECT user_id, nama_objek FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $objek_id);
    mysqli_stmt_execute($stmt);
    $objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($objek && (is_admin() || $objek['user_id'] == $uid)) {
        $stmt_del = mysqli_prepare($conn, "UPDATE objek SET deleted_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, 'i', $objek_id);
        mysqli_stmt_execute($stmt_del); mysqli_stmt_close($stmt_del);

        $stmt_log = mysqli_prepare($conn, "INSERT INTO audit_log (user_id, aksi, target_id, detail, ip_address) VALUES (?, 'hapus_objek', ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $detail = "Hapus Objek: " . $objek['nama_objek'];
        mysqli_stmt_bind_param($stmt_log, 'iiss', $uid, $objek_id, $detail, $ip);
        mysqli_stmt_execute($stmt_log); mysqli_stmt_close($stmt_log);

        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Objek berhasil dihapus.'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda tidak berhak menghapus objek ini.'];
    }
    header("Location: index.php?page=objek"); exit;
}

// ==============================================================
// 8. HANDLE UPDATE OBJEK (Hanya 1 Versi yang Digunakan)
// ==============================================================
if (isset($_POST['aksi_update_objek'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $objek_id = (int)$_POST['objek_id'];
    $nama_objek = trim($_POST['nama_objek']);
    $lokasi = trim($_POST['lokasi']);
    $koordinat = trim($_POST['koordinat']);
    $catatan = trim($_POST['catatan']);
    $area_id = (int)($_POST['area_id'] ?? 0) ?: null;
    $visibility = $_POST['visibility'];

    $stmt_cek = mysqli_prepare($conn, "SELECT user_id, foto_pertama FROM objek WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_cek, 'i', $objek_id);
    mysqli_stmt_execute($stmt_cek);
    $res_cek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
    mysqli_stmt_close($stmt_cek);

    if ($res_cek && (is_admin() || $res_cek['user_id'] == $uid)) {
        $foto = $res_cek['foto_pertama'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'objek_' . $objek_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto);
        }

        $stmt_upd = mysqli_prepare($conn, "UPDATE objek SET nama_objek=?, lokasi=?, koordinat=?, catatan=?, area_id=?, visibility=?, foto_pertama=? WHERE id=?");
        mysqli_stmt_bind_param($stmt_upd, 'ssssissi', $nama_objek, $lokasi, $koordinat, $catatan, $area_id, $visibility, $foto, $objek_id);
        mysqli_stmt_execute($stmt_upd);
        mysqli_stmt_close($stmt_upd);

        $stmt_log = mysqli_prepare($conn, "INSERT INTO audit_log (user_id, aksi, target_id, detail, ip_address) VALUES (?, 'edit_objek', ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $detail = "Edit Objek: " . $nama_objek;
        mysqli_stmt_bind_param($stmt_log, 'iiss', $uid, $objek_id, $detail, $ip);
        mysqli_stmt_execute($stmt_log); mysqli_stmt_close($stmt_log);

        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Objek berhasil diperbarui.'];
        header("Location: index.php?page=objek_profil&id=$objek_id"); exit;
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Anda tidak berhak mengedit objek ini.'];
        header("Location: index.php?page=objek"); exit;
    }
}

// ==============================================================
// 9. ROUTER HALAMAN NORMAL (Daftar Halaman yang Diperbolehkan)
// ==============================================================
$halaman = $_GET['page'] ?? 'beranda';

$allowed_pages = [
    'beranda', 'login', 'register', 'peta', 'objek', 'objek_daftar', 
    'misi_saya', 'user_profil', 'logout', 'kelola_user', 'kelola_user_edit', 
    'kelola_user_reset', 'reset_password', 'permintaan_follow', 'cari', 
    'organisasi', 'organisasi_detail', 'kelola_organisasi', 'kelola_organisasi_edit',
    'area', 'area_tambah', 'area_edit', 'area_detail',
    'objek_profil', 'lapor', 'objek_edit', 'edit' // <--- RUTE BARU
];

if (!in_array($halaman, $allowed_pages)) {
    $halaman = 'beranda';
}

require_once 'pages/header.php';
require_once 'pages/bottom_nav.php';

$file_konten = __DIR__ . '/pages/' . $halaman . '.php';
if (file_exists($file_konten)) {
    require_once $file_konten;
} else {
    echo "<div class='p-10 text-center'><h1 class='text-2xl font-bold text-slate-400'>Halaman belum dibuat.</h1></div>";
}

require_once 'pages/footer.php';
?>
