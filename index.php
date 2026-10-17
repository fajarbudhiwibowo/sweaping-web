<?php
require_once 'config/database.php';
require_once 'core/functions.php';

// ==============================================================
// 1. HANDLE SIMPAN AREA PANTAU (DIPINDAH KE ATAS)
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
// 3.5 HANDLE HAPUS & UPDATE AREA PANTAU
// ==============================================================
if (isset($_POST['aksi_hapus_area'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $area_id = (int)$_POST['area_id'];
    $my_id = (int)$_SESSION['user_id'];

    // Cek Hak Akses
    $stmt_cek = mysqli_prepare($conn, "SELECT a.created_by, o.pic_user_id FROM area_pantau a JOIN organisasi o ON a.organisasi_id = o.id WHERE a.id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_cek, 'i', $area_id);
    mysqli_stmt_execute($stmt_cek);
    $area = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));
    mysqli_stmt_close($stmt_cek);

    if ($area && (is_admin() || $area['pic_user_id'] == $my_id || $area['created_by'] == $my_id)) {
        $stmt = mysqli_prepare($conn, "UPDATE area_pantau SET deleted_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $area_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
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

// ==============================================================
// 4. ROUTER HALAMAN NORMAL
// ==============================================================
$halaman = $_GET['page'] ?? 'beranda';

$allowed_pages = [
    'beranda', 'login', 'register', 'peta', 'objek', 'objek_daftar', 
    'misi_saya', 'user_profil', 'logout', 'kelola_user', 'kelola_user_edit', 
    'kelola_user_reset', 'reset_password', 'permintaan_follow', 'cari', 
    'organisasi', 'organisasi_detail', 'kelola_organisasi', 'kelola_organisasi_edit',
    'area', 'area_tambah', 'area_edit', 'area_detail'
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
