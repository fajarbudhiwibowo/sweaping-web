<?php
require_once 'config/database.php';
require_once 'core/functions.php';

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
if (isset($_POST['aksi_simpan_laporan'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $objek_id = (int)$_POST['objek_id'];
    $catatan = trim($_POST['catatan']);
    $status_kondisi = $_POST['status_kondisi'];
    $nama_kontributor = trim($_POST['nama_kontributor']);
    $peran_kontributor = trim($_POST['peran_kontributor']);
    $visibility = $_POST['visibility'];
    $tanggal_monitoring = $_POST['tanggal_monitoring'];

    $stmt_o = mysqli_prepare($conn, "SELECT jenis_objek, nama_objek, koordinat FROM objek WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_o, 'i', $objek_id);
    mysqli_stmt_execute($stmt_o);
    $obj = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_o));
    mysqli_stmt_close($stmt_o);

    if (!$obj) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Objek tidak ditemukan.'];
        header("Location: index.php?page=lapor"); exit;
    }

    $jenis = $obj['jenis_objek'];
    $nama_objek = $obj['nama_objek'];
    $koordinat = $obj['koordinat'];
    
    $serapan_karbon = 0; $serapan_ilmiah = 0; $metode = null;

    if ($jenis === 'pohon') {
        $dbh = (float)($_POST['diameter_batang'] ?? 0); $tinggi = (float)($_POST['tinggi_pohon'] ?? 0);
        if ($dbh > 0 && $tinggi > 0) {
            $agb = 0.0509 * 0.6 * pow($dbh, 2) * $tinggi;
            $karbon = ($agb * 1.2) * 0.47;
            $serapan_karbon = round($karbon * 3.667, 2);
            $serapan_ilmiah = $serapan_karbon; $metode = 'chave_2005';
        }
    } elseif ($jenis === 'komposting') {
        $vol = (float)($_POST['volume_sampah'] ?? 0);
        $serapan_karbon = round($vol * 0.7, 2);
        $serapan_ilmiah = $serapan_karbon; $metode = 'ipcc_kompos';
    }

    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = 'lapor_' . time() . '_' . rand(100,999) . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto);
    }

    $insert_cols = ['objek_id', 'user_id', 'jenis_objek', 'nama_objek', 'koordinat', 'status_kondisi', 'nama_kontributor', 'peran_kontributor', 'catatan', 'visibility', 'tanggal_monitoring', 'serapan_karbon', 'serapan_karbon_ilmiah', 'metode_hitung', 'foto'];
    $insert_vals = [$objek_id, $uid, $jenis, $nama_objek, $koordinat, $status_kondisi, $nama_kontributor, $peran_kontributor, $catatan, $visibility, $tanggal_monitoring, $serapan_karbon, $serapan_ilmiah, $metode, $foto];
    $insert_types = "iisssssssssddss";

    $cols = [
        'diameter_batang' => 'd', 'tinggi_pohon' => 'd', 'penilaian_kesehatan' => 's', 'kondisi_batang' => 's',
        'debit_air_normal' => 'd', 'kedalaman_air' => 'd', 'lebar_sungai_monitor' => 'd', 'kondisi_air_visual' => 's', 'sampah_sungai' => 's',
        'kondisi_vegetasi' => 's', 'kebersihan_taman' => 's', 'volume_sampah' => 'i', 'hasil_kompos' => 'i'
    ];

    foreach ($cols as $col => $type) {
        if (isset($_POST[$col]) && $_POST[$col] !== '') {
            $insert_cols[] = $col;
            $insert_vals[] = ($type === 'd') ? (float)$_POST[$col] : (($type === 'i') ? (int)$_POST[$col] : $_POST[$col]);
            $insert_types .= $type;
        }
    }

    $sql = "INSERT INTO laporan_aksi (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', array_fill(0, count($insert_cols), '?')) . ")";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $insert_types, ...$insert_vals);
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            $stmt_log = mysqli_prepare($conn, "INSERT INTO audit_log (user_id, aksi, target_id, detail, ip_address) VALUES (?, 'buat_laporan', ?, ?, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            mysqli_stmt_bind_param($stmt_log, 'iiss', $uid, $new_id, $nama_objek, $ip);
            mysqli_stmt_execute($stmt_log); mysqli_stmt_close($stmt_log);

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
