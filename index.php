<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
// HANDLER: PROXY GAMBAR TELEGRAM (V5 Edition - Dengan Cache & Kompresi)
// ==============================================================
if (isset($_GET['telegram_photo'])) {
    $file_id = $_GET['telegram_photo'] ?? '';
    if (!$file_id || strlen($file_id) > 255) { http_response_code(404); exit; }

    // ============ CACHE SETUP ============
    $cache_dir = __DIR__ . '/uploads/.tg_cache/';
    if (!is_dir($cache_dir)) @mkdir($cache_dir, 0755, true);
    
    $cache_key  = md5($file_id);
    $cache_file = $cache_dir . $cache_key . '.cache';
    $cache_ttl  = 30 * 24 * 3600; // 30 hari
    
    // ============ SERVE FROM CACHE ============
    if (is_file($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
        $data = @file_get_contents($cache_file);
        if ($data !== false && $data !== '') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_buffer($finfo, $data);
            finfo_close($finfo);
            
            header('Content-Type: ' . ($mime ?: 'image/jpeg'));
            header('Cache-Control: public, max-age=86400');
            header('X-Content-Type-Options: nosniff');
            header('X-Cache-Status: HIT');
            echo $data;
            exit;
        }
    }
    
    // ============ FETCH FRESH FROM TELEGRAM ============
    $token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : (defined('BOT_TOKEN') ? BOT_TOKEN : '');
    if (empty($token)) {
        http_response_code(404); exit;
    }

    $url_api = "https://api.telegram.org/bot{$token}/getFile?file_id=" . urlencode($file_id);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url_api,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false // Diperlukan di beberapa hosting
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data || empty($data['ok'])) { 
        // Jika Telegram menolak (file_id salah/hilang), kirim gambar placeholder
        http_response_code(404); 
        exit; 
    }

    $file_path = $data['result']['file_path'] ?? '';
    if (!$file_path) { http_response_code(404); exit; }

    $url_file = "https://api.telegram.org/file/bot{$token}/{$file_path}";
    
    $ch2 = curl_init($url_file);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $img_data = curl_exec($ch2);
    $mime = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
    curl_close($ch2);

    if (!$img_data) { http_response_code(404); exit; }
    
    // ============ KOMPRES SEBELUM SAVE (Opsional) ============
    $gd_tersedia = function_exists('imagecreatefromstring');
    if ($gd_tersedia && strlen($img_data) > 50 * 1024) { // Kompres jika > 50KB
        $img = @imagecreatefromstring($img_data);
        if ($img) {
            $w = imagesx($img);
            $h = imagesy($img);
            $max_dim = 1200;
            $scale = min($max_dim / $w, $max_dim / $h, 1);
            $new_w = (int)($w * $scale);
            $new_h = (int)($h * $scale);
            
            $new_img = imagecreatetruecolor($new_w, $new_h);
            // Preserve transparency for PNG
            $is_png = (strpos($mime, 'png') !== false);
            if ($is_png) {
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);
            }
            imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            
            ob_start();
            imagejpeg($new_img, null, 78);
            $compressed = ob_get_clean();
            
            imagedestroy($img);
            imagedestroy($new_img);
            
            if ($compressed && strlen($compressed) < strlen($img_data)) {
                $img_data = $compressed;
                $mime = 'image/jpeg';
            }
        }
    }
    
    // ============ SAVE TO CACHE ============
    @file_put_contents($cache_file, $img_data);
    
    header('Content-Type: ' . ($mime ?: 'image/jpeg'));
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    header('X-Cache-Status: MISS');
    echo $img_data;
    exit;
}

// ==============================================================
// 0. HANDLE PAGE BUILDER (CMS)
// ==============================================================
if (isset($_POST['aksi_simpan_blok'])) {
    if (!is_logged_in() || !is_admin()) { header("Location: index.php?page=login"); exit; }
    
    $id = (int)($_POST['id'] ?? 0);
    $tipe = $_POST['tipe'] ?? 'hero';
    $judul = trim($_POST['judul'] ?? '');
    $subjudul = trim($_POST['subjudul'] ?? '');
    $konten = trim($_POST['konten'] ?? '');
    $warna_bg = trim($_POST['warna_bg'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 0);
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    
        // Upload Gambar (Single atau Multiple)
    $gambar = $_POST['gambar_lama'] ?? null;
    
    if ($tipe === 'carousel' && isset($_FILES['gambar_carousel'])) {
        $files = $_FILES['gambar_carousel'];
        $uploaded_files = [];
        if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $nama_file = 'slide_' . time() . '_' . $i . '.' . $ext;
                if (move_uploaded_file($files['tmp_name'][$i], UPLOAD_DIR . $nama_file)) {
                    $uploaded_files[] = $nama_file;
                }
            }
        }
        if (!empty($uploaded_files)) {
            $gambar = json_encode($uploaded_files); // Simpan sebagai JSON
        }
    } elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        // Upload Single (Hero / Text Image)
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = 'blok_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], UPLOAD_DIR . $gambar);
    }

    // SIMPAN KE DATABASE (Pastikan ini ada!)
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE home_sections SET tipe=?, judul=?, subjudul=?, konten=?, gambar=?, warna_bg=?, urutan=?, aktif=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssiii', $tipe, $judul, $subjudul, $konten, $gambar, $warna_bg, $urutan, $aktif, $id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO home_sections (tipe, judul, subjudul, konten, gambar, warna_bg, urutan, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssssii', $tipe, $judul, $subjudul, $konten, $gambar, $warna_bg, $urutan, $aktif);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Blok beranda berhasil disimpan.'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt)];
    }
    mysqli_stmt_close($stmt);
    
    header("Location: index.php?page=dashboard_cms"); exit;
} // <--- KURUNG KURAWAL UNTUK MENUTUP if (isset($_POST['aksi_simpan_blok']))

// ==============================================================
// HANDLE RUANG INFORMASI (BROADCAST)
// ==============================================================
if (isset($_POST['aksi_simpan_info'])) {
    if (!is_logged_in() || (!is_admin() && !is_pic_organisasi($conn, $_SESSION['user_id']))) { 
        header("Location: index.php?page=login"); exit; 
    }
    
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    $link = trim($_POST['link'] ?? '');
    $target = $_POST['target_audiens'] ?? 'semua';
    $my_id = (int)$_SESSION['user_id'];
    
    // Cek apakah yang input adalah PIC Organisasi
    $org_id = null;
    if (!is_admin()) {
        $q_org = mysqli_query($conn, "SELECT id FROM organisasi WHERE pic_user_id = $my_id LIMIT 1");
        $data_org = mysqli_fetch_assoc($q_org);
        $org_id = $data_org ? (int)$data_org['id'] : null;
        $target = 'anggota'; // Paksa target jadi anggota
    }

    if ($judul === '' || $konten === '') {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Judul dan Konten wajib diisi.'];
        header("Location: index.php?page=dashboard_info"); exit;
    }

    // Handle Upload Gambar (Sama seperti sebelumnya)
    $nama_gambar = $_POST['gambar_lama'] ?? null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $new_img = 'info_' . time() . '.' . $ext;
        $upload_dir = 'uploads/info/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $new_img)) {
            if ($nama_gambar && file_exists($upload_dir . $nama_gambar)) @unlink($upload_dir . $nama_gambar);
            $nama_gambar = $new_img;
        }
    }

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE ruang_informasi SET judul=?, konten=?, link=?, target_audiens=?, gambar=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssi', $judul, $konten, $link, $target, $nama_gambar, $id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO ruang_informasi (judul, konten, link, target_audiens, organisasi_id, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssis', $judul, $konten, $link, $target, $org_id, $nama_gambar);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Informasi berhasil disimpan!'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt)];
    }
    mysqli_stmt_close($stmt);
    header("Location: index.php?page=dashboard_info"); exit;
}

// ==============================================================
// HANDLE GAME / MISI (CHALLENGES)
// ==============================================================
if (isset($_POST['aksi_simpan_misi'])) {
    if (!is_logged_in() || !is_admin()) { header("Location: index.php?page=login"); exit; }
    
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $target_aksi = $_POST['target_aksi'];
    $target_jumlah = (int)$_POST['target_jumlah'];
    $hadiah_xp = (int)$_POST['hadiah_xp'];
    $is_aktif = isset($_POST['is_aktif']) ? 1 : 0;

    if ($judul === '' || $target_aksi === '' || $target_jumlah <= 0) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Data misi tidak valid. Pastikan semua kolom terisi.'];
        header("Location: index.php?page=kelola_game"); exit;
    }

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE challenges SET judul=?, deskripsi=?, target_aksi=?, target_jumlah=?, hadiah_xp=?, is_aktif=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssiiii', $judul, $deskripsi, $target_aksi, $target_jumlah, $hadiah_xp, $is_aktif, $id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO challenges (judul, deskripsi, target_aksi, target_jumlah, hadiah_xp, is_aktif) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssiii', $judul, $deskripsi, $target_aksi, $target_jumlah, $hadiah_xp, $is_aktif);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Misi berhasil disimpan!'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt)];
    }
    mysqli_stmt_close($stmt);
    header("Location: index.php?page=kelola_game"); exit;
}

if (isset($_POST['aksi_hapus_misi'])) {
    if (!is_logged_in() || !is_admin()) { header("Location: index.php?page=login"); exit; }
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM challenges WHERE id = $id");
    $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Misi berhasil dihapus.'];
    header("Location: index.php?page=kelola_game"); exit;
}

// ==============================================================
// HANDLE AKSI KELOLA USER (BAN/UNBAN/HAPUS)
// ==============================================================
if (isset($_GET['page']) && $_GET['page'] === 'aksi_kelola_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in() || !is_admin()) { header("Location: index.php?page=beranda"); exit; }
    
    $target_id = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['aksi'] ?? '';
    $my_id = (int)$_SESSION['user_id'];
    
    if ($target_id > 0 && $target_id !== $my_id) {
        if ($action === 'toggle_aktif') {
            mysqli_query($conn, "UPDATE users SET aktif = NOT aktif WHERE id = $target_id");
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Status user berhasil diubah.'];
        } elseif ($action === 'hapus') {
            // Soft delete
            mysqli_query($conn, "UPDATE users SET deleted_at = NOW() WHERE id = $target_id");
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'User berhasil dihapus.'];
        }
    }
    header("Location: index.php?page=kelola_user"); exit;
}

// ==============================================================
// HANDLER DEV HUB (BACKUP & AJAX SYSTEM STATS)
// ==============================================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'system_stats') {
    header('Content-Type: application/json');
    if (!is_logged_in() || !is_super_admin()) { echo json_encode(['error' => 'Unauthorized']); exit; }

    // Cek Disk Space
    $disk_free = @disk_free_space("/");
    $disk_total = @disk_total_space("/");
    $disk_used_persen = $disk_total > 0 ? round((($disk_total - $disk_free) / $disk_total) * 100, 1) : 0;
    
    // Cek RAM (Jika terminal bisa)
    $ram_usage = 'N/A';
    if (function_exists('shell_exec')) {
        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        if (isset($free_arr[1])) {
            $mem = explode(" ", preg_replace('/\s+/', ' ', $free_arr[1]));
            if (isset($mem[2]) && isset($mem[1]) && $mem[1] > 0) {
                $ram_usage = round(($mem[2] / $mem[1]) * 100) . '%';
            }
        }
    }

    echo json_encode([
        'status' => 'ok',
        'disk_persen' => $disk_used_persen,
        'disk_free_gb' => round($disk_free / 1073741824, 2),
        'ram_persen' => $ram_usage,
        'php_version' => phpversion(),
        'mysql_version' => mysqli_get_server_info($conn)
    ]);
    exit;
}

if (isset($_GET['aksi_dev']) && $_GET['aksi_dev'] === 'backup_db') {
    if (!is_logged_in() || !is_super_admin()) { header("Location: index.php?page=login"); exit; }
    
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) { $tables[] = $row[0]; }

    $sqlScript = "-- SWEAPING DB BACKUP\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW CREATE TABLE $table");
        $row = mysqli_fetch_row($result);
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";

        $result = mysqli_query($conn, "SELECT * FROM $table");
        $columnCount = mysqli_num_fields($result);
        while ($row = mysqli_fetch_row($result)) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $columnCount; $j++) {
                $row[$j] = $row[$j] ? addslashes($row[$j]) : 'NULL';
                if (isset($row[$j])) { $sqlScript .= '"' . $row[$j] . '"'; } else { $sqlScript .= '""'; }
                if ($j < ($columnCount - 1)) { $sqlScript .= ','; }
            }
            $sqlScript .= ");\n";
        }
    }
    $sqlScript .= "\n\n-- End of Backup";
    
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="sweaping_backup_' . date('Ymd_His') . '.sql"');
    echo $sqlScript;
    exit;
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
    // Tipe data: s (string), i (int), s, s, s, d (double), s, i -> 'sissssdi' (8 parameter untuk 8 kolom)
    mysqli_stmt_bind_param($stmt_ins, 'sisssdsi', $kode, $my_org_id, $nama_area, $jenis, $geojson, $luas_m2, $deskripsi, $my_id);
    
    if (mysqli_stmt_execute($stmt_ins)) {
        mysqli_stmt_close($stmt_ins);
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Area pantau berhasil disimpan.'];
        if (function_exists('cek_dan_update_misi')) {
            cek_dan_update_misi($conn, $my_id, 'gambar_area');
        }
        header("Location: index.php?page=area"); exit;
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt_ins)];
        header("Location: index.php?page=area_tambah"); exit;
    }
}


// ==============================================================
// HANDLE REGISTER INDIVIDU
// ==============================================================
if (isset($_POST['aksi_register_individu'])) {
    $nama = trim($_POST['nama_lengkap']);
    $panggilan = trim($_POST['nama_panggilan']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);
    
    $prov = $_POST['provinsi']; $kota = $_POST['kota']; $kec = $_POST['kecamatan']; $kel = $_POST['kelurahan'];
    $wilayah = $kel . ', ' . $kec . ', ' . $kota . ', ' . $prov;
    $alamat_lengkap = $alamat . ' (' . $wilayah . ')';
    
    $password = $_POST['password'];
    $confirm = $_POST['password_confirm'];

    if ($password !== $confirm) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Konfirmasi password tidak cocok.'];
        header("Location: index.php?page=register_individu"); exit;
    }

    $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt_cek, 's', $email);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Email sudah terdaftar.'];
        header("Location: index.php?page=register_individu"); exit;
    }
    mysqli_stmt_close($stmt_cek);

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap, nama_panggilan, email, password, role, telepon, organisasi, tipe_akun, aktif) VALUES (?, ?, ?, ?, 'user', ?, ?, 'individu', 1)");
    mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $panggilan, $email, $hash, $telepon, $alamat_lengkap);
    
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $new_id;
        $_SESSION['nama'] = $nama;
        $_SESSION['role'] = 'user';
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Pendaftaran berhasil! Selamat datang di SWEAPING.'];
        header("Location: index.php?page=user_profil"); exit;
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal mendaftar: ' . mysqli_stmt_error($stmt)];
        header("Location: index.php?page=register_individu"); exit;
    }
} // <--- KURUNG KURAWAL PENUTUP INDIVIDU (WAJIB ADA)

// ==============================================================
// HANDLE REGISTER INSTITUSI
// ==============================================================
if (isset($_POST['aksi_register_institusi'])) {
    $nama_instansi = trim($_POST['nama_institusi']);
    $jenis_instansi = trim($_POST['jenis_institusi']);
    $ketua = trim($_POST['nama_ketua']);
    $sekretaris = trim($_POST['nama_sekretaris']);
    $bendahara = trim($_POST['nama_bendahara']);
    $email_lembaga = trim($_POST['email_lembaga']);
    $telepon_pic = trim($_POST['telepon_pic']);
    $email_login = trim($_POST['email_login']);
    
    $prov = $_POST['provinsi']; $kota = $_POST['kota']; $kec = $_POST['kecamatan']; $kel = $_POST['kelurahan'];
    
    $password = $_POST['password'];
    $confirm = $_POST['password_confirm'];

    if ($password !== $confirm) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Konfirmasi password tidak cocok.'];
        header("Location: index.php?page=register_institusi"); exit;
    }

    $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt_cek, 's', $email_login);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    if (mysqli_stmt_num_rows($stmt_cek) > 0) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Email Login PIC sudah terdaftar.'];
        header("Location: index.php?page=register_institusi"); exit;
    }
    mysqli_stmt_close($stmt_cek);

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt_user = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap, email, password, role, telepon, organisasi, tipe_akun, aktif) VALUES (?, ?, ?, 'user', ?, ?, 'institusi', 1)");
    mysqli_stmt_bind_param($stmt_user, 'sssss', $nama_instansi, $email_login, $hash, $telepon_pic, $nama_instansi);
    
    if (mysqli_stmt_execute($stmt_user)) {
        $pic_user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_user);

        $kode_unik = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $nama_instansi), 0, 10)) . '-' . rand(10,99);
        
        $stmt_org = mysqli_prepare($conn, "INSERT INTO organisasi (nama, kode_unik, pic_user_id, jenis_institusi, nama_ketua, nama_sekretaris, nama_bendahara, email_lembaga, telepon_pic, provinsi, kota, kecamatan, kelurahan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");
        mysqli_stmt_bind_param($stmt_org, 'ssissssssssss', $nama_instansi, $kode_unik, $pic_user_id, $jenis_instansi, $ketua, $sekretaris, $bendahara, $email_lembaga, $telepon_pic, $prov, $kota, $kec, $kel);
        
        if (mysqli_stmt_execute($stmt_org)) {
            $org_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_org);

            $stmt_anggota = mysqli_prepare($conn, "INSERT INTO organisasi_anggota (organisasi_id, user_id, role) VALUES (?, ?, 'pic')");
            mysqli_stmt_bind_param($stmt_anggota, 'ii', $org_id, $pic_user_id);
            mysqli_stmt_execute($stmt_anggota);
            mysqli_stmt_close($stmt_anggota);

            $_SESSION['user_id'] = $pic_user_id;
            $_SESSION['nama'] = $nama_instansi;
            $_SESSION['role'] = 'user';
            
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Institusi berhasil didaftarkan! Anda sekarang login sebagai Admin PIC.'];
            header("Location: index.php?page=user_profil"); exit;
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal membuat data institusi: ' . mysqli_stmt_error($stmt_org)];
            header("Location: index.php?page=register_institusi"); exit;
        }
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal membuat akun PIC: ' . mysqli_stmt_error($stmt_user)];
        header("Location: index.php?page=register_institusi"); exit;
    }
} // <--- KURUNG KURAWAL PENUTUP INSTITUSI (WAJIB ADA)

// ==============================================================
// HANDLE UPDATE PROFIL (AVATAR, COVER, BIO, SOSMED)
// ==============================================================
if (isset($_POST['aksi_update_profil'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $nama_input = trim($_POST['nama_lengkap']);
    $nama_pendek = trim($_POST['nama_pendek'] ?? '');
    $telepon = trim($_POST['telepon']);
    $organisasi = trim($_POST['organisasi']);
    $tipe_akun = $_POST['tipe_akun'];
    $bio = trim($_POST['bio_singkat'] ?? '');
    $ig = trim($_POST['sosmed_instagram'] ?? '');
    $li = trim($_POST['sosmed_linkedin'] ?? '');
    $web = trim($_POST['sosmed_website'] ?? '');
    
    // 1. Ambil data user lama (Cek avatar & cover lama)
    $stmt_old = mysqli_prepare($conn, "SELECT avatar, cover_foto FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_old, 'i', $uid);
    mysqli_stmt_execute($stmt_old);
    $old_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_old));
    mysqli_stmt_close($stmt_old);
    
    $avatar_filename = $old_data['avatar'] ?? null;
    $cover_filename = $old_data['cover_foto'] ?? null;
    
    // 2. Proses Upload Avatar/Logo
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        if ($file['size'] <= 2 * 1024 * 1024) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_avatar = 'user_' . $uid . '_' . time() . '.' . $ext;
                $upload_dir = 'uploads/avatar/';
                
                if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_avatar)) {
                    if ($old_data['avatar'] && file_exists($upload_dir . $old_data['avatar'])) {
                        @unlink($upload_dir . $old_data['avatar']);
                    }
                    $avatar_filename = $new_avatar;
                }
            }
        }
    }

    // 3. Proses Upload Cover Foto
    if (isset($_FILES['cover_foto']) && $_FILES['cover_foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_foto'];
        if ($file['size'] <= 3 * 1024 * 1024) { // Maks 3MB
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_cover = 'cover_' . $uid . '_' . time() . '.' . $ext;
                $upload_dir = 'uploads/covers/';
                
                if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_cover)) {
                    if ($old_data['cover_foto'] && file_exists($upload_dir . $old_data['cover_foto'])) {
                        @unlink($upload_dir . $old_data['cover_foto']);
                    }
                    $cover_filename = $new_cover;
                }
            }
        }
    }

    // 4. UPDATE TABEL USERS
    if ($tipe_akun === 'individu') {
        $stmt_upd = mysqli_prepare($conn, "UPDATE users SET nama_lengkap=?, telepon=?, organisasi=?, avatar=?, cover_foto=?, bio_singkat=?, sosmed_instagram=?, sosmed_linkedin=?, sosmed_website=? WHERE id=?");
        // Tipe: sssssssssi (9 string, 1 integer)
        mysqli_stmt_bind_param($stmt_upd, 'sssssssssi', $nama_input, $telepon, $organisasi, $avatar_filename, $cover_filename, $bio, $ig, $li, $web, $uid);
    } else {
        $stmt_upd = mysqli_prepare($conn, "UPDATE users SET telepon=?, organisasi=?, avatar=?, cover_foto=?, bio_singkat=?, sosmed_instagram=?, sosmed_linkedin=?, sosmed_website=? WHERE id=?");
        // Tipe: ssssssssi (8 string, 1 integer)
        mysqli_stmt_bind_param($stmt_upd, 'ssssssssi', $telepon, $organisasi, $avatar_filename, $cover_filename, $bio, $ig, $li, $web, $uid);
    }
    
    if (!mysqli_stmt_execute($stmt_upd)) {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal update user: ' . mysqli_stmt_error($stmt_upd)];
        header("Location: index.php?page=user_profil_edit"); exit;
    }
    mysqli_stmt_close($stmt_upd);

    // 5. JIKA INSTITUSI, UPDATE TABEL ORGANISASI
    if ($tipe_akun === 'institusi') {
        $stmt_org = mysqli_prepare($conn, "UPDATE organisasi SET nama = ?, nama_pendek = ? WHERE pic_user_id = ?");
        mysqli_stmt_bind_param($stmt_org, 'ssi', $nama_input, $nama_pendek, $uid);
        
        if (mysqli_stmt_execute($stmt_org)) {
            $_SESSION['nama'] = $nama_pendek; 
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Profil Lembaga berhasil diperbarui!'];
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal update lembaga: ' . mysqli_stmt_error($stmt_org)];
        }
        mysqli_stmt_close($stmt_org);
    } else {
        $_SESSION['nama'] = $nama_input;
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Profil berhasil diperbarui!'];
    }

    header("Location: index.php?page=user_profil"); exit;
}

// ==============================================================
// HANDLE TAMBAH EVENT / RENCANA AKSI
// ==============================================================
if (isset($_POST['aksi_tambah_event'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    
    $uid = (int)$_SESSION['user_id'];
    $id = (int)($_POST['event_id'] ?? 0);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tanggal = $_POST['tanggal_mulai'];
    $lokasi = trim($_POST['lokasi']);
    $link = trim($_POST['link'] ?? '');
    $visibility = $_POST['visibility'] ?? 'publik';
    $gambar_lama = $_POST['gambar_lama'] ?? '';
    
    // Upload Gambar
    $nama_gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $new_gambar = 'event_' . time() . '.' . $ext;
        $upload_dir = 'uploads/events/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $new_gambar)) {
            if ($gambar_lama && file_exists($upload_dir . $gambar_lama)) @unlink($upload_dir . $gambar_lama);
            $nama_gambar = $new_gambar;
        }
    }

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE events SET judul=?, deskripsi=?, tanggal_mulai=?, lokasi=?, link=?, visibility=?, gambar=? WHERE id=? AND user_id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssii', $judul, $deskripsi, $tanggal, $lokasi, $link, $visibility, $nama_gambar, $id, $uid);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO events (user_id, judul, deskripsi, tanggal_mulai, lokasi, link, visibility, gambar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'rencana')");
        mysqli_stmt_bind_param($stmt, 'isssssss', $uid, $judul, $deskripsi, $tanggal, $lokasi, $link, $visibility, $nama_gambar);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Event berhasil disimpan!'];
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan event.'];
    }
    mysqli_stmt_close($stmt);
    header("Location: index.php?page=user_profil"); exit;
}

if (isset($_POST['aksi_hapus_event'])) {
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $uid = (int)$_SESSION['user_id'];
    $id = (int)$_POST['event_id'];
    
    $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $uid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => 'Event berhasil dihapus.'];
    header("Location: index.php?page=user_profil"); exit;
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
    } // <--- TAMBAHKAN SATU KURUNG KURAWAL INI (Untuk menutup if baris 180)

// ==============================================================
// 5. HANDLE SIMPAN LAPORAN
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
    $tanggal_monitoring  = trim($_POST['tanggal_monitoring'] ?? '') ?: date('Y-m-d');
    
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

    // VARIABEL PENELITI POHON
    $kerapatan_tajuk        = trim($_POST['kerapatan_tajuk'] ?? ''); $kerapatan_tajuk = $kerapatan_tajuk === '' ? null : (float)$kerapatan_tajuk;
    $transparansi_tajuk     = trim($_POST['transparansi_tajuk'] ?? ''); $transparansi_tajuk = $transparansi_tajuk === '' ? null : (float)$transparansi_tajuk;
    $rasio_tajuk_hidup      = trim($_POST['rasio_tajuk_hidup'] ?? ''); $rasio_tajuk_hidup = $rasio_tajuk_hidup === '' ? null : (float)$rasio_tajuk_hidup;
    $mati_pucuk             = trim($_POST['mati_pucuk'] ?? ''); $mati_pucuk = $mati_pucuk === '' ? null : (float)$mati_pucuk;
    $massa_jenis_kayu       = trim($_POST['massa_jenis_kayu'] ?? ''); $massa_jenis_kayu = $massa_jenis_kayu === '' ? 0.6 : (float)$massa_jenis_kayu;
    $target_sasaran         = trim($_POST['target_sasaran'] ?? '') ?: null;
    $kategori_risiko_isa    = trim($_POST['kategori_risiko_isa'] ?? '') ?: null;

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

       $skor_fhm            = trim($_POST['skor_fhm'] ?? ''); $skor_fhm = $skor_fhm === '' ? null : (float)$skor_fhm;
    $kategori_fhm        = trim($_POST['kategori_fhm'] ?? '') ?: null;
    $isa_probability     = trim($_POST['isa_probability'] ?? '') ?: null;
    $isa_impact          = trim($_POST['isa_impact'] ?? '') ?: null;
    $isa_consequence     = trim($_POST['isa_consequence'] ?? '') ?: null;
    $incidence_nd        = trim($_POST['incidence_nd'] ?? ''); $incidence_nd = $incidence_nd === '' ? null : (int)$incidence_nd;
    $incidence_n         = trim($_POST['incidence_n'] ?? ''); $incidence_n = $incidence_n === '' ? null : (int)$incidence_n;
    $incidence_percent   = trim($_POST['incidence_percent'] ?? ''); $incidence_percent = $incidence_percent === '' ? null : (float)$incidence_percent;
   
    // KALKULASI KARBON & UMUR
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
        $co2_chave = chave_2005($diameter_batang, $tinggi_pohon, $massa_jenis_kayu);
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
    
    // UPLOAD FOTO
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

    // SIMPAN KE DATABASE
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
        'kompos_faktor_keberlanjutan' => $kompos_faktor_json,
                'kerapatan_tajuk' => $kerapatan_tajuk,
        'transparansi_tajuk' => $transparansi_tajuk,
        'rasio_tajuk_hidup' => $rasio_tajuk_hidup,
        'mati_pucuk' => $mati_pucuk,
        'massa_jenis_kayu' => $massa_jenis_kayu,
        'target_sasaran' => $target_sasaran,
               
                // --- HASIL HITUNGAN MESIN FHM & ISA ---
        'skor_fhm' => $skor_fhm,
        'kategori_fhm' => $kategori_fhm,
        'isa_probability' => $isa_probability,
        'isa_impact' => $isa_impact,
        'isa_consequence' => $isa_consequence,
        'incidence_nd' => $incidence_nd,
        'incidence_n' => $incidence_n,
        'incidence_percent' => $incidence_percent
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
    
    if ($nama_objek === '' || !in_array($jenis_objek, ['pohon','sungai','taman','komposting','hutan','lingkungan'], true)) { 
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Data tidak valid.']; 
        header("Location: index.php?page=objek_daftar"); exit; 
    }

    $to_f = fn($v) => ($v === '' || $v === null) ? null : (float)$v;
    $to_i = fn($v) => ($v === '' || $v === null) ? null : (int)$v;
    $to_s = fn($v) => ($v === '' || $v === null) ? null : trim($v);

    // Generate Kode
    $prefix_map = ['pohon'=>'TRB', 'sungai'=>'SNG', 'taman'=>'TMN', 'komposting'=>'KMP', 'hutan'=>'HTN', 'lingkungan'=>'LNG'];
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

       // Siapkan Data (Array Utuh & Rapi - SINKRONISASI DATABASE SWEAPING)
    $insert_data = [
        'kode_objek' => $kode_objek, 
        'jenis_objek' => $jenis_objek, 
        'nama_objek' => $nama_objek,
        'lokasi' => $to_s($_POST['lokasi'] ?? null), 
        'koordinat' => $to_s($_POST['koordinat'] ?? null),
        'user_id' => $uid, 
        'area_id' => $to_i($_POST['area_id'] ?? null),
        'foto_pertama' => $foto_pertama, 
        'mode_input' => $_POST['mode_input'] ?? 'umum',
        'catatan' => $to_s($_POST['catatan'] ?? null),
        'visibility' => $_POST['visibility'] ?? 'publik',
        'pic_monev_nama' => $to_s($_POST['pic_monev_nama'] ?? null),
        'pic_monev_peran' => $to_s($_POST['pic_monev_peran'] ?? null),

        // --- DATA POHON ---
        'nama_latin' => $to_s($_POST['nama_latin'] ?? null),
        'famili' => $to_s($_POST['famili'] ?? null),
        'asal_daerah' => $to_s($_POST['asal_daerah'] ?? null),
        'nama_daerah_lain' => $to_s($_POST['nama_daerah_lain'] ?? null),
        'status_pohon' => $to_s($_POST['status_pohon'] ?? null),
        'kategori_ias' => $to_s($_POST['kategori_ias'] ?? null),
        'potensi_pemanfaatan' => $to_s($_POST['potensi_pemanfaatan'] ?? null),
        'karakteristik' => isset($_POST['karakteristik']) ? json_encode($_POST['karakteristik']) : null,
        'manfaat' => isset($_POST['manfaat']) ? json_encode($_POST['manfaat']) : null,
        'tanggal_tanam' => $to_s($_POST['tanggal_tanam'] ?? null),
        'alasan_penanaman' => $to_s($_POST['alasan_penanaman'] ?? null),
        'penyelenggara' => $to_s($_POST['penyelenggara'] ?? null),
        'pelaksana' => $to_s($_POST['pelaksana'] ?? null),
        'sumber_dana' => $to_s($_POST['sumber_dana'] ?? null),
        'jenis_bibit' => $to_s($_POST['jenis_bibit'] ?? null),
        'asal_bibit' => $to_s($_POST['asal_bibit'] ?? null),

        // --- DATA SUNGAI ---
        'nama_lain_sungai' => $to_s($_POST['nama_lain_sungai'] ?? null),
        'sumber_mata_air' => $to_s($_POST['sumber_mata_air'] ?? null),
        'panjang_sungai' => $to_f($_POST['panjang_sungai'] ?? null),
        'lebar_rata_rata' => $to_f($_POST['lebar_rata_rata'] ?? null),
        'kedalaman_rata_rata' => $to_f($_POST['kedalaman_rata_rata'] ?? null),
        'daerah_hulu' => $to_s($_POST['daerah_hulu'] ?? null),
        'daerah_hilir' => $to_s($_POST['daerah_hilir'] ?? null),
        'muara' => $to_s($_POST['muara'] ?? null),
        'jenis_sungai' => $to_s($_POST['jenis_sungai'] ?? null),
        'dasar_sungai' => isset($_POST['dasar_sungai']) ? json_encode($_POST['dasar_sungai']) : null,
        'ada_vegetasi' => $to_i($_POST['ada_vegetasi'] ?? null),
        'ada_sempadan' => $to_i($_POST['ada_sempadan'] ?? null),
        'pemanfaatan_sungai' => isset($_POST['pemanfaatan_sungai']) ? json_encode($_POST['pemanfaatan_sungai']) : null,

        // --- DATA TAMAN ---
        'taman_pengelola' => $to_s($_POST['taman_pengelola'] ?? null),
        'taman_luas_area' => $to_f($_POST['taman_luas_area'] ?? null),
        'taman_tahun_pembangunan' => $to_s($_POST['taman_tahun_pembangunan'] ?? null),
        'taman_tahun_revitalisasi' => $to_s($_POST['taman_tahun_revitalisasi'] ?? null),
        'taman_status_kepemilikan' => $to_s($_POST['taman_status_kepemilikan'] ?? null),
        'taman_vegetasi_pohon' => $to_s($_POST['taman_vegetasi_pohon'] ?? null),
        'taman_vegetasi_tanaman' => $to_s($_POST['taman_vegetasi_tanaman'] ?? null),
        'taman_kategori_tanaman' => isset($_POST['taman_kategori_tanaman']) ? json_encode($_POST['taman_kategori_tanaman']) : null,
        'taman_pemanfaatan' => isset($_POST['taman_pemanfaatan']) ? json_encode($_POST['taman_pemanfaatan']) : null,
        'taman_intensitas' => $to_s($_POST['taman_intensitas'] ?? null),
        'taman_fasilitas' => isset($_POST['taman_fasilitas']) ? json_encode($_POST['taman_fasilitas']) : null,

        // --- DATA KOMPOS ---
        'kompos_pengelola' => $to_s($_POST['kompos_pengelola'] ?? null),
        'kompos_metode' => $to_s($_POST['kompos_metode'] ?? null),
        'kompos_kapasitas' => $to_f($_POST['kompos_kapasitas'] ?? null),
        'kompos_luas_area' => $to_f($_POST['kompos_luas_area'] ?? null),
        'kompos_tahun_mulai' => $to_s($_POST['kompos_tahun_mulai'] ?? null),
        'kompos_jumlah_komposter' => $to_i($_POST['kompos_jumlah_komposter'] ?? null),
        'kompos_kelompok' => $to_s($_POST['kompos_kelompok'] ?? null),
        'kompos_bahan_organik' => isset($_POST['kompos_bahan_organik_arr']) ? json_encode($_POST['kompos_bahan_organik_arr']) : null,

        // --- DATA HUTAN ---
        'hutan_nama_lain' => $to_s($_POST['hutan_nama_lain'] ?? null),
        'hutan_pengelola' => $to_s($_POST['hutan_pengelola'] ?? null),
        'hutan_luas' => $to_f($_POST['hutan_luas'] ?? null),
        'hutan_tipe' => $to_s($_POST['hutan_tipe'] ?? null),
        'hutan_kondisi_tajuk' => $to_s($_POST['hutan_kondisi_tajuk'] ?? null),
        'hutan_regenerasi' => $to_s($_POST['hutan_regenerasi'] ?? null),
        'hutan_kanopi' => $to_f($_POST['hutan_kanopi'] ?? null),
        'hutan_kerapatan' => $to_f($_POST['hutan_kerapatan'] ?? null),
        'hutan_pohon_mati' => $to_i($_POST['hutan_pohon_mati'] ?? null),
        'hutan_ph_tanah' => $to_f($_POST['hutan_ph_tanah'] ?? null),
        'hutan_ktk' => $to_f($_POST['hutan_ktk'] ?? null),
        'hutan_karbon_tanah' => $to_f($_POST['hutan_karbon_tanah'] ?? null),
        'hutan_kedalaman_solum' => $to_f($_POST['hutan_kedalaman_solum'] ?? null),
        'hutan_ada_ias' => $to_i($_POST['hutan_ada_ias'] ?? null),
        'hutan_jejak_manusia' => $to_s($_POST['hutan_jejak_manusia'] ?? null),
        'hutan_tingkat_gangguan' => $to_s($_POST['hutan_tingkat_gangguan'] ?? null),
        'hutan_sampah' => $to_s($_POST['hutan_sampah'] ?? null),
        'hutan_jenis_gangguan' => $to_s($_POST['hutan_jenis_gangguan'] ?? null),
        'hutan_shannon' => $to_f($_POST['hutan_shannon'] ?? null),

        // --- DATA LINGKUNGAN ---
        'lingkungan_tipe' => $to_s($_POST['lingkungan_tipe'] ?? null),
        'lingkungan_pic' => $to_s($_POST['lingkungan_pic'] ?? null),
        'lingkungan_suhu' => $to_f($_POST['lingkungan_suhu'] ?? null),
        'lingkungan_kelembaban' => $to_f($_POST['lingkungan_kelembaban'] ?? null),
        'lingkungan_lux' => $to_f($_POST['lingkungan_lux'] ?? null),
        'lingkungan_kebisingan' => $to_f($_POST['lingkungan_kebisingan'] ?? null),
        'lingkungan_pm25' => $to_f($_POST['lingkungan_pm25'] ?? null),
        'lingkungan_co2' => $to_f($_POST['lingkungan_co2'] ?? null)
    ];//

    $cols = []; $placeholders = []; $types = ''; $vals = [];
    foreach ($insert_data as $col => $val) {
        $cols[] = $col; 
        $placeholders[] = '?';
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
// HANDLE AKSI FOLLOW / UNFOLLOW (AJAX)
// ==============================================================
if (isset($_GET['page']) && $_GET['page'] === 'aksi_follow' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!is_logged_in()) { echo json_encode(['status'=>'error', 'message'=>'Not logged in']); exit; }
    
    $my_id = (int)$_SESSION['user_id'];
    $target_id = (int)($_POST['target_id'] ?? 0);
    $action = $_POST['action'] ?? 'follow';
    
    if ($target_id <= 0 || $target_id === $my_id) {
        echo json_encode(['status'=>'error', 'message'=>'Invalid target']); exit;
    }
    
    if ($action === 'follow') {
        // Cek apakah sudah follow
        $stmt = mysqli_prepare($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $my_id, $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 0) {
            mysqli_stmt_close($stmt);
            $stmt_ins = mysqli_prepare($conn, "INSERT INTO follows (follower_id, following_id, status) VALUES (?, ?, 'pending')");
            mysqli_stmt_bind_param($stmt_ins, 'ii', $my_id, $target_id);
            mysqli_stmt_execute($stmt_ins);
            mysqli_stmt_close($stmt_ins);
        } else {
            mysqli_stmt_close($stmt);
        }
        echo json_encode(['status'=>'ok', 'action'=>'follow']);
    } else {
        // Unfollow
        $stmt_del = mysqli_prepare($conn, "DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        mysqli_stmt_bind_param($stmt_del, 'ii', $my_id, $target_id);
        mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
        echo json_encode(['status'=>'ok', 'action'=>'unfollow']);
    }
    exit;
}

// ==============================================================
// HANDLE RESPON PERTEMANAN (TERIMA/TOLAK)
// ==============================================================
if (isset($_GET['page']) && $_GET['page'] === 'aksi_respond_follow' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!is_logged_in()) { echo json_encode(['status'=>'error', 'message'=>'Not logged in']); exit; }
    
    $follow_id = (int)($_POST['follow_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $my_id = (int)$_SESSION['user_id'];
    
    if ($follow_id <= 0 || !in_array($action, ['accepted', 'rejected'])) { 
        echo json_encode(['status'=>'error', 'message'=>'Invalid param']); exit; 
    }

    // Pastikan yang menerima request adalah user yang sedang login (following_id)
    $stmt = mysqli_prepare($conn, "UPDATE follows SET status = ? WHERE id = ? AND following_id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $action, $follow_id, $my_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'message'=>'Gagal update database']);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ==============================================================
// HANDLE RESPON GABUNG ORGANISASI (TERIMA/TOLAK)
// ==============================================================
if (isset($_GET['page']) && $_GET['page'] === 'aksi_respond_org' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!is_logged_in()) { echo json_encode(['status'=>'error', 'message'=>'Not logged in']); exit; }
    
    $req_id = (int)($_POST['req_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $my_id = (int)$_SESSION['user_id'];
    
    if ($req_id <= 0 || !in_array($action, ['accepted', 'rejected'])) { 
        echo json_encode(['status'=>'error', 'message'=>'Invalid param']); exit; 
    }
    
    // Ambil data request dan cek apakah user ini adalah PIC organisasi tersebut
    $stmt = mysqli_prepare($conn, "SELECT r.organisasi_id, r.user_id, o.pic_user_id FROM organisasi_permintaan r JOIN organisasi o ON r.organisasi_id = o.id WHERE r.id = ? AND r.status = 'pending' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $req_id);
    mysqli_stmt_execute($stmt);
    $req = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$req) { echo json_encode(['status'=>'error', 'message'=>'Request not found']); exit; }
    // Hanya PIC atau Admin yang boleh approve
    if ((int)$req['pic_user_id'] !== $my_id && !is_admin()) { 
        echo json_encode(['status'=>'error', 'message'=>'Unauthorized']); exit; 
    }
    
    mysqli_begin_transaction($conn);
    try {
        // 1. Update status permintaan
        $stmt_u = mysqli_prepare($conn, "UPDATE organisasi_permintaan SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_u, 'si', $action, $req_id);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);
        
        // 2. Jika diterima, masukkan ke anggota organisasi
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
// 9. ROUTER HALAMAN NORMAL (Daftar Halaman yang Diperbolehkan)
// ==============================================================
$halaman = $_GET['page'] ?? 'beranda';

$allowed_pages = [
    'beranda', 'login', 'register', 'peta', 'objek', 'objek_daftar', 
    'misi_saya', 'user_profil', 'logout', 'kelola_user', 'kelola_user_edit', 
    'kelola_user_reset', 'reset_password', 'permintaan_follow', 'cari', 
    'organisasi', 'organisasi_detail', 'kelola_organisasi', 'kelola_organisasi_edit',
    'area', 'area_tambah', 'area_edit', 'area_detail',
    'objek_profil', 'lapor', 'objek_edit', 'edit', 'export_pdf', 'tentang', 'kontak', 'dashboard', 'dashboard_cms', 'dashboard_info', 'dashboard_theme', 'dev_hub', 'kelola_game',
    'mulai_aksi', 'register_individu', 'register_institusi', 'user_profil', 'user_profil_edit' // <--- TAMBAHKAN INI
];

if (!in_array($halaman, $allowed_pages)) {
    $halaman = 'beranda';
}

if (in_array($halaman, ['objek', 'area', 'peta', 'organisasi']) && !is_logged_in()) {
    redirect('index.php?page=login');
}
require_once 'pages/header.php';
require_once 'pages/bottom_nav.php';

$file_konten = __DIR__ . '/pages/' . $halaman . '.php';
if (file_exists($file_konten)) {
    require_once $file_konten;
} else {
    echo "<div class='p-10 text-center'><h1 class='text-2xl font-bold text-slate-400'>Halaman belum dibuat.</h1></div>";
} // <--- TAMBAHKAN KURUNG KURAWAL INI

if (isset($_GET['page']) && $_GET['page'] === 'aksi_gabung_org' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!is_logged_in()) { echo json_encode(['status'=>'error', 'message'=>'Login dulu']); exit; }
    
    $org_id = (int)$_POST['organisasi_id'];
    $my_id = (int)$_SESSION['user_id'];
    
    // Cek apakah sudah jadi anggota
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM organisasi_anggota WHERE organisasi_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $org_id, $my_id);
    mysqli_stmt_execute($stmt); mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) { echo json_encode(['status'=>'error', 'message'=>'Anda sudah menjadi anggota.']); exit; }
    mysqli_stmt_close($stmt);

    // Insert ke permintaan
    $stmt_ins = mysqli_prepare($conn, "INSERT INTO organisasi_permintaan (organisasi_id, user_id, status) VALUES (?, ?, 'pending') ON DUPLICATE KEY UPDATE status = 'pending'");
    mysqli_stmt_bind_param($stmt_ins, 'ii', $org_id, $my_id);
    if (mysqli_stmt_execute($stmt_ins)) {
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'message'=>'Gagal terkirim.']);
    }
    exit;
}

require_once 'pages/footer.php';
?>
