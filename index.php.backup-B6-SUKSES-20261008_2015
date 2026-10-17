<?php
// ============================================================
// SWEAPING v4.0 - MODERN EDITION
// Platform Konservasi & Akuntansi Karbon
// Dikembangkan oleh LSM KOMPAS
// ============================================================

// ============ KONFIGURASI - GANTI 4 BARIS INI ============
define('DB_HOST', 'localhost');
define('DB_USER', 'lsmw8652_sweaping_user');
define('DB_PASS', 'UNfF~*w+)BV7o5[6');
define('DB_NAME', 'lsmw8652_sweaping');
// =========================================================

// ============ KONFIGURASI LAIN ============
define('APP_DEBUG', false);
define('APP_NAME', 'SWEAPING');
define('APP_TAGLINE', 'Platform Konservasi & Akuntansi Karbon');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 8 * 1024 * 1024);   // naik dari 2 MB ke 8 MB
define('MAX_DIMENSION', 1600);               // naik dari 1280 ke 1600
define('JPEG_QUALITY', 80);                  // naik dikit, 78 → 80
define('THUMB_WIDTH', 400);
define('THUMB_QUALITY', 70);
define('RATE_LIMIT_PER_JAM', 30);
define('SESSION_LIFETIME', 3600 * 2);
define('CO2_PER_KG_KOMPOS', 0.7);  // kg CO2 dicegah per kg sampah organik dikompos
define('ORG_NAME', 'LSM KOMPAS');
define('ORG_EMAIL', 'info@lsmkompas.id');
define('ORG_WEBSITE', 'https://lsmkompas.id');
// =========================================

// ============ LOAD .env ============
function load_env($path) {
    if (!is_readable($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if ($k !== '' && !defined($k)) define($k, $v);
    }
}
load_env(__DIR__ . '/.env');
// ===================================

// ============ AUTO-SETUP FILES ============
function auto_setup_files() {
    $base = __DIR__ . '/';
    if (!is_dir($base . 'uploads')) @mkdir($base . 'uploads', 0755, true);
    if (!is_dir($base . 'assets')) @mkdir($base . 'assets', 0755, true);

    $f = $base . 'uploads/.htaccess';
    if (!file_exists($f)) @file_put_contents($f, "<FilesMatch \"\\.(php|php3|php4|php5|php7|phtml|pl|py|jsp|asp|sh|cgi|exe)$\">\nOrder Allow,Deny\nDeny from all\n</FilesMatch>\n<FilesMatch \"\\.(jpg|jpeg|png|webp|gif)$\">\nOrder Allow,Deny\nAllow from all\n</FilesMatch>\nOptions -Indexes\n");

    $f = $base . '.htaccess';
    if (!file_exists($f)) @file_put_contents($f, "Options -Indexes\nServerSignature Off\n<FilesMatch \"^(\\.env|\\.git|composer\\.(json|lock)|package\\.json|.*\\.log)$\">\nOrder Allow,Deny\nDeny from all\n</FilesMatch>\n<IfModule mod_headers.c>\nHeader set X-Frame-Options \"DENY\"\nHeader set X-Content-Type-Options \"nosniff\"\nHeader set Referrer-Policy \"strict-origin-when-cross-origin\"\n</IfModule>\n");

    $f = $base . 'manifest.json';
    if (!file_exists($f)) @file_put_contents($f, json_encode([
        'name' => 'SWEAPING',
        'short_name' => 'SWEAPING',
        'description' => 'Platform Konservasi & Akuntansi Karbon',
        'start_url' => './',
        'display' => 'standalone',
        'background_color' => '#0d3519',
        'theme_color' => '#1a5c2e',
        'orientation' => 'portrait',
        'icons' => [['src' => 'assets/logo-bulat.png','sizes' => '192x192','type' => 'image/png','purpose' => 'any maskable']]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $f = $base . 'sw.js';
    if (!file_exists($f)) @file_put_contents($f, "const CACHE_NAME='sweaping-v4';\nconst ASSETS=['./','./offline.html','./manifest.json'];\nself.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE_NAME).then(c=>Promise.all(ASSETS.map(u=>c.add(u).catch(()=>null)))));self.skipWaiting();});\nself.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(k=>Promise.all(k.filter(x=>x!==CACHE_NAME).map(x=>caches.delete(x)))));self.clients.claim();});\nself.addEventListener('fetch',e=>{if(e.request.method!=='GET')return;e.respondWith(fetch(e.request).then(res=>{const clone=res.clone();caches.open(CACHE_NAME).then(c=>c.put(e.request,clone));return res;}).catch(()=>caches.match(e.request).then(c=>c||caches.match('./offline.html'))));});\n");

    $f = $base . 'offline.html';
    if (!file_exists($f)) @file_put_contents($f, '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Offline</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-50 min-h-screen flex items-center justify-center p-4"><div class="max-w-md text-center"><div class="text-6xl mb-4">📡</div><h1 class="text-2xl font-bold text-slate-900 mb-2">Offline</h1><p class="text-slate-600 mb-6">Koneksi internet terputus.</p><button onclick="location.reload()" class="px-6 py-3 bg-emerald-700 text-white font-bold rounded-xl">Coba Lagi</button></div></body></html>');

    $f = $base . 'robots.txt';
    if (!file_exists($f)) @file_put_contents($f, "User-agent: *\nDisallow: /uploads/\nAllow: /\n");
}
auto_setup_files();

// ============ HTTPS ============
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
if (!$is_https && !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// ============ ERROR HANDLING ============
if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }
else { ini_set('display_errors', 0); ini_set('log_errors', 1); error_reporting(E_ALL); }

// ============ SESSION ============
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $is_https]);
    session_start();
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(self), camera=(self)");

// ============ KONEKSI DB ============
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("<div style='font-family:sans-serif;padding:2rem;max-width:600px;margin:auto;'><h1 style='color:#dc2626;'>❌ Koneksi Database Gagal</h1><p>Periksa 4 baris konfigurasi di baris 8-11 file <b>index.php</b>.</p><p style='background:#fee;padding:1rem;border-radius:8px;'><b>Error:</b> " . htmlspecialchars(mysqli_connect_error()) . "</p></div>");
}
mysqli_set_charset($conn, 'utf8mb4');

// ============ AUTO-CREATE TABEL ============
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS laporan_aksi (
    id INT AUTO_INCREMENT PRIMARY KEY, user_id INT DEFAULT NULL,
    jenis_objek ENUM('sungai','pohon','taman','komposting') NOT NULL,
    nama_objek VARCHAR(150) NOT NULL, koordinat VARCHAR(100) NOT NULL,
    status_kondisi VARCHAR(100) NOT NULL, nama_kontributor VARCHAR(150) NOT NULL,
    peran_kontributor VARCHAR(100) NOT NULL, catatan TEXT NOT NULL,
    umur_bulan INT DEFAULT 0, serapan_karbon DECIMAL(10,2) DEFAULT 0.00,
    foto VARCHAR(255) DEFAULT NULL, deleted_at TIMESTAMP NULL DEFAULT NULL,
    tanggal_buat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jenis (jenis_objek), INDEX idx_tanggal (tanggal_buat),
    INDEX idx_user (user_id), INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY, nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    role ENUM('super_admin','admin','user') DEFAULT 'user',
    aktif TINYINT(1) DEFAULT 1, tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, aksi VARCHAR(50),
    target_id INT, detail TEXT, ip_address VARCHAR(45), user_agent VARCHAR(255),
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id), INDEX idx_waktu (waktu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS pesan_kontak (
    id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL, subjek VARCHAR(200) NOT NULL, pesan TEXT NOT NULL,
    sudah_dibaca TINYINT(1) DEFAULT 0, ip_address VARCHAR(45),
    tanggal_kirim TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dibaca (sudah_dibaca), INDEX idx_tanggal (tanggal_kirim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN user_id INT DEFAULT NULL");
// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN umur_bulan INT DEFAULT 0");
// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN serapan_karbon DECIMAL(10,2) DEFAULT 0.00");
// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
// @mysqli_query($conn, "ALTER TABLE users ADD COLUMN aktif TINYINT(1) DEFAULT 1");
// @mysqli_query($conn, "ALTER TABLE users ADD COLUMN telepon VARCHAR(20) DEFAULT NULL");
// // Tambahan migrasi untuk komposting
// @mysqli_query($conn, "ALTER TABLE laporan_aksi MODIFY COLUMN jenis_objek ENUM('sungai','pohon','taman','komposting') NOT NULL");
// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN volume_sampah INT DEFAULT 0");
// @mysqli_query($conn, "ALTER TABLE laporan_aksi ADD COLUMN hasil_kompos INT DEFAULT 0");

$cek_admin = mysqli_query($conn, "SELECT id FROM users WHERE role='super_admin' LIMIT 1");
if (mysqli_num_rows($cek_admin) == 0) {
    $pass_default = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap,email,password,role) VALUES (?,?,?,'super_admin')");
    $nama = 'Super Admin'; $mail = 'admin@lsmkompas.id';
    mysqli_stmt_bind_param($stmt, 'sss', $nama, $mail, $pass_default);
    mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
}

$gd_aktif = function_exists('imagecreatefromjpeg');

// ============ CSRF ============
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function csrf_field() { return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf']) . '">'; }
function csrf_check() { if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) { http_response_code(419); die("Permintaan tidak valid (CSRF)."); } }

// ============ HELPERS ============
function notif($tipe, $pesan) { $_SESSION['notif'] = ['tipe' => $tipe, 'pesan' => $pesan]; }
function redirect($url) { header("Location: $url"); exit; }
function is_admin() { return isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin','admin'], true); }
function is_super_admin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'; }
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function get_ip() { return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function audit_log($conn, $aksi, $target_id = null, $detail = null) {
    $uid = $_SESSION['user_id'] ?? null; $ip = get_ip();
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_log (user_id,aksi,target_id,detail,ip_address,user_agent) VALUES (?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'isssss', $uid, $aksi, $target_id, $detail, $ip, $ua);
    mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
}
function rate_limit_cek($conn, $uid, $limit) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as jml FROM laporan_aksi WHERE user_id = ? AND tanggal_buat > NOW() - INTERVAL 1 HOUR");
    mysqli_stmt_bind_param($stmt, 'i', $uid); mysqli_stmt_execute($stmt);
    $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
    return $res['jml'] < $limit;
}
// ============ FUNGSI FOTO ============
function url_foto($row, $kolom_foto = 'foto', $tipe = 'thumb') {
    // Prioritas: Telegram file_id
    if (!empty($row['telegram_file_id'])) {
        return 'index.php?telegram_photo=' . urlencode($row['telegram_file_id']);
    }
    // Fallback: file lokal
    if (empty($row[$kolom_foto])) return '';
    $prefix = ($tipe === 'thumb') ? 'thumb_' : '';
    return UPLOAD_DIR . $prefix . $row[$kolom_foto];
}


// ============ FUNGSI AVATAR ============
function url_avatar($row) {
    if (empty($row['avatar'])) return '';
    $file = $row['avatar'];
    if (!file_exists(UPLOAD_DIR . 'avatar/' . $file)) return '';
    return UPLOAD_DIR . 'avatar/' . $file;
}

function tampil_avatar($row, $size_class = 'w-8 h-8', $font_class = 'text-xs') {
    $avatar_url = url_avatar($row);
    if ($avatar_url) {
        return '<img src="' . e($avatar_url) . '" alt="" class="' . e($size_class) . ' rounded-full object-cover flex-shrink-0">';
    }
    $initial = strtoupper(substr($row['nama_lengkap'] ?? '?', 0, 1));
    return '<div class="' . e($size_class) . ' rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center font-bold ' . e($font_class) . ' flex-shrink-0">' . e($initial) . '</div>';
}
// ============ END FUNGSI AVATAR ============

// ============ FUNGSI TELEGRAM ============
function telegram_token() {
    return defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
}

function telegram_chat_id($jenis_objek) {
    $map = [
        'pohon'      => defined('TELEGRAM_CHAT_POHON')      ? TELEGRAM_CHAT_POHON      : '',
        'sungai'     => defined('TELEGRAM_CHAT_SUNGAI')     ? TELEGRAM_CHAT_SUNGAI     : '',
        'taman'      => defined('TELEGRAM_CHAT_TAMAN')      ? TELEGRAM_CHAT_TAMAN      : '',
        'komposting' => defined('TELEGRAM_CHAT_KOMPOSTING') ? TELEGRAM_CHAT_KOMPOSTING : '',
    ];
    return $map[$jenis_objek] ?? '';
}

function upload_ke_telegram($file_path, $jenis_objek, $caption = '') {
    if (!file_exists($file_path)) return false;

    $token   = telegram_token();
    $chat_id = telegram_chat_id($jenis_objek);

    if ($token === '' || $chat_id === '') {
        error_log('[TELEGRAM] Token/chat_id kosong untuk: ' . $jenis_objek);
        return false;
    }

    $url = "https://api.telegram.org/bot{$token}/sendPhoto";

    $post = [
        'chat_id' => $chat_id,
        'caption' => mb_substr($caption, 0, 1024),
        'photo'   => new CURLFile($file_path),
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('[TELEGRAM] cURL error: ' . $curl_err);
        return false;
    }
    if ($http_code !== 200) {
        error_log('[TELEGRAM] HTTP ' . $http_code . ': ' . substr($response, 0, 300));
        return false;
    }

    $data = json_decode($response, true);
    if (!$data || empty($data['ok'])) {
        error_log('[TELEGRAM] API error: ' . substr($response, 0, 300));
        return false;
    }

    $photos = $data['result']['photo'] ?? [];
    if (empty($photos)) return false;

    $largest = end($photos);

    return [
        'file_id'    => $largest['file_id'],
        'message_id' => $data['result']['message_id'],
    ];
}

function get_telegram_file_url($file_id) {
    $token = telegram_token();
    if ($token === '') return false;

    $url = "https://api.telegram.org/bot{$token}/getFile?file_id=" . urlencode($file_id);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data || empty($data['ok'])) return false;

    $file_path = $data['result']['file_path'] ?? '';
    if (!$file_path) return false;

    return "https://api.telegram.org/file/bot{$token}/{$file_path}";
}

// ============ FUNGSI PERHITUNGAN UMUR POHON ============
function hitung_umur($tanggal_tanam, $tanggal_monitoring) {
    if (empty($tanggal_tanam) || empty($tanggal_monitoring)) return null;
    if ($tanggal_tanam === '0000-00-00' || $tanggal_monitoring === '0000-00-00') return null;
    
    try {
        $tgl_tanam   = new DateTime($tanggal_tanam);
        $tgl_monitor = new DateTime($tanggal_monitoring);
    } catch (Exception $e) {
        return null;
    }
    
    if ($tgl_monitor < $tgl_tanam) return null;
    
    $diff = $tgl_tanam->diff($tgl_monitor);
    $tahun = $diff->y;
    $bulan = $diff->m;
    $hari_sisa = $diff->d;
    $minggu = intdiv($hari_sisa, 7);
    $hari = $hari_sisa % 7;
    $total_hari = $diff->days;
    
    $parts = [];
    if ($tahun > 0) $parts[] = $tahun . ' tahun';
    if ($bulan > 0) $parts[] = $bulan . ' bulan';
    if ($minggu > 0) $parts[] = $minggu . ' minggu';
    if ($hari > 0) $parts[] = $hari . ' hari';
    if (empty($parts)) $parts[] = 'kurang dari 1 hari';
    $teks_full = implode(', ', $parts);
    
    return [
        'total_hari' => $total_hari,
        'tahun'      => $tahun,
        'bulan'      => $bulan,
        'minggu'     => $minggu,
        'hari'       => $hari,
        'teks_full'  => $teks_full,
    ];
}

// ============ FUNGSI CHAVE 2005 — SERAPAN KARBON POHON ============
function chave_2005($dbh, $tinggi, $density = 0.6) {
    if (empty($dbh) || empty($tinggi)) return null;
    $dbh = (float)$dbh;
    $tinggi = (float)$tinggi;
    if ($dbh <= 0 || $tinggi <= 0) return null;
    
    $agb = 0.0509 * $density * pow($dbh, 2) * $tinggi;
    $biomassa_total = $agb * 1.2;
    $karbon = $biomassa_total * 0.47;
    $co2 = $karbon * 3.667;
    
    return round($co2, 2);
}

    
// ============ FUNGSI SUNGAI CH4 ESTIMASI (IPCC 2019 Wetlands) ============
function sungai_ch4_estimasi($do, $sampah, $visual) {
    $do = ($do === null || $do === '') ? null : (float)$do;
    
    // Base emission by DO (mg/L)
    $base = 10.0; // default kalau DO kosong
    if ($do !== null) {
        if ($do >= 6)       $base = 5.0;
        elseif ($do >= 4)   $base = 10.0;
        elseif ($do >= 2)   $base = 20.0;
        else                $base = 40.0;
    }
    
    // Multiplier sampah
    $mult_sampah = 1.0;
    $map_sampah = [
        'Tidak ada'     => 0.5,
        'Sedikit'       => 1.0,
        'Sedang'        => 1.5,
        'Banyak'        => 2.5,
        'Sangat banyak' => 4.0,
    ];
    if ($sampah && isset($map_sampah[$sampah])) $mult_sampah = $map_sampah[$sampah];
    
    // Multiplier visual
    $mult_visual = 1.0;
    $map_visual = [
        'Jernih'       => 0.5,
        'Agak keruh'   => 1.0,
        'Keruh'        => 2.0,
        'Sangat keruh' => 3.0,
    ];
    if ($visual && isset($map_visual[$visual])) $mult_visual = $map_visual[$visual];
    
    $ch4 = round($base * $mult_sampah * $mult_visual, 2);
    $level = 'Rendah';
    if ($ch4 >= 30) $level = 'Tinggi';
    elseif ($ch4 >= 15) $level = 'Sedang';
    
    return ['value' => $ch4, 'level' => $level];
}
// ============ END FUNGSI SUNGAI CH4 ============


// ============ FUNGSI OBJEK ============
function generate_kode_objek($conn, $jenis) {
    // Prefix berdasarkan jenis
    $prefix_map = [
        'pohon'      => 'TRB',
        'sungai'     => 'SNG',
        'taman'      => 'TMN',
        'komposting' => 'KMP'
    ];
    
    $prefix = $prefix_map[$jenis] ?? 'OBJ';
    $tahun = date('Y');
    $prefix_tahun = $prefix . '-' . $tahun . '-';
    
    // Cari kode terakhir dengan prefix & tahun yang sama
    $stmt = mysqli_prepare($conn, "SELECT kode_objek FROM objek WHERE kode_objek LIKE ? ORDER BY id DESC LIMIT 1");
    $like = $prefix_tahun . '%';
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Hitung nomor urut berikutnya
    if ($row) {
        // Ambil 5 digit terakhir, increment
        $nomor_terakhir = (int)substr($row['kode_objek'], -5);
        $nomor_baru = $nomor_terakhir + 1;
    } else {
        $nomor_baru = 1;
    }
    
    // Format: TRB-2026-00001
    return $prefix_tahun . str_pad($nomor_baru, 5, '0', STR_PAD_LEFT);
}

// ============ FUNGSI EXPORT XLSX ============
function export_xlsx($data, $filename = 'export.xlsx') {
    if (empty($data)) return false;
    if (!class_exists('ZipArchive')) return false;
    
    $tmp_file = tempnam(sys_get_temp_dir(), 'xlsx_');
    if (!$tmp_file) return false;
    
    $zip = new ZipArchive();
    if ($zip->open($tmp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        @unlink($tmp_file);
        return false;
    }
    
    // Helper escape XML
    $esc = function($v) {
        $v = (string)$v;
        $v = str_replace(['&','<','>','"',"'"], ['&amp;','&lt;','&gt;','&quot;','&apos;'], $v);
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v);
        return $v;
    };
    
    // Build baris XML
    $rows_xml = '';
    $row_num = 1;
    foreach ($data as $row) {
        $cells_xml = '';
        $col_letter = 'A';
        foreach ($row as $cell) {
            $cells_xml .= '<c r="' . $col_letter . $row_num . '" t="inlineStr"><is><t>' . $esc($cell) . '</t></is></c>';
            $col_letter++;
        }
        $rows_xml .= '<row r="' . $row_num . '">' . $cells_xml . '</row>';
        $row_num++;
    }
    
    // 1. [Content_Types].xml
    $zip->addFromString('[Content_Types].xml', 
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
        '<Default Extension="xml" ContentType="application/xml"/>' .
        '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
        '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
        '</Types>'
    );
    
    // 2. _rels/.rels
    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
        '</Relationships>'
    );
    
    // 3. xl/workbook.xml
    $zip->addFromString('xl/workbook.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
        '<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets>' .
        '</workbook>'
    );
    
    // 4. xl/_rels/workbook.xml.rels
    $zip->addFromString('xl/_rels/workbook.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
        '</Relationships>'
    );
    
    // 5. xl/worksheets/sheet1.xml
    $zip->addFromString('xl/worksheets/sheet1.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
        '<sheetData>' . $rows_xml . '</sheetData>' .
        '</worksheet>'
    );
    
    $zip->close();
    
    // Serve file
    if (file_exists($tmp_file)) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($tmp_file));
        header('Cache-Control: max-age=0');
        readfile($tmp_file);
        @unlink($tmp_file);
        return true;
    }
    
    return false;
}
// ============ END FUNGSI EXPORT XLSX ============

// ============ HALAMAN ============
$halaman = $_GET['page'] ?? 'beranda';
$halaman_diizinkan = ['beranda','arsip','login','register','lapor','edit','kelola_user','audit','recycle','peta','diagnostik','tentang','kontak','pesan','objek','objek_daftar','objek_profil','objek_edit','sitemap','misi_saya','user_profil','pengikut','mengikuti','permintaan_follow','cari'];
if (!in_array($halaman, $halaman_diizinkan, true)) $halaman = 'beranda';
// ============ ENDPOINT: SITEMAP XML ============
if ($halaman === 'sitemap') {
    header('Content-Type: application/xml; charset=utf-8');
    $today = date('Y-m-d');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Halaman statis
    $statis = [
        ['beranda', '1.0', 'daily'],
        ['arsip',   '0.9', 'daily'],
        ['objek',   '0.8', 'weekly'],
        ['peta',    '0.8', 'weekly'],
        ['tentang', '0.7', 'monthly'],
        ['kontak',  '0.6', 'monthly'],
    ];

    foreach ($statis as $s) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($base_url . '/index.php?page=' . $s[0]) . "</loc>\n";
        echo "    <lastmod>{$today}</lastmod>\n";
        echo "    <changefreq>{$s[2]}</changefreq>\n";
        echo "    <priority>{$s[1]}</priority>\n";
        echo "  </url>\n";
    }

    // Halaman objek profil (dinamis)
    $res_sm = mysqli_query($conn, "SELECT id, tanggal_daftar FROM objek WHERE deleted_at IS NULL AND status = 'aktif' ORDER BY id DESC LIMIT 1000");
    if ($res_sm) {
        while ($o = mysqli_fetch_assoc($res_sm)) {
            $url = $base_url . '/index.php?page=objek_profil&id=' . (int)$o['id'];
            $lastmod = !empty($o['tanggal_daftar']) ? date('Y-m-d', strtotime($o['tanggal_daftar'])) : $today;
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
            echo "    <lastmod>{$lastmod}</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.6</priority>\n";
            echo "  </url>\n";
        }
    }

    echo '</urlset>';
    exit;
}

// ============ SEO META PER HALAMAN ============
$base_url = 'https://sweaping.lsmkompas.id';
$base_img = $base_url . '/assets/logo-bulat.png';

$meta_seo = [
    'beranda' => [
        'title' => 'SWEAPING — Platform Konservasi & Akuntansi Karbon',
        'desc'  => 'Platform pelaporan aksi lingkungan & akuntansi karbon. Dokumentasikan aksi Anda untuk lingkungan yang lebih baik.',
        'image' => $base_url . '/assets/hero-bg.jpg',
        'type'  => 'website',
        'index' => true,
    ],
    'arsip' => [
        'title' => 'Arsip Aksi Lingkungan — SWEAPING',
        'desc'  => 'Rekapitulasi aksi lingkungan: penanaman pohon, pemantauan sungai, taman, dan komposting dengan perhitungan serapan karbon.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => true,
    ],
    'objek' => [
        'title' => 'Daftar Objek Lingkungan — SWEAPING',
        'desc'  => 'Master data objek lingkungan: pohon, sungai, taman, dan komposting yang dipantau di platform SWEAPING.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => true,
    ],
    'peta' => [
        'title' => 'Peta Sebaran Aksi Lingkungan — SWEAPING',
        'desc'  => 'Visualisasi sebaran lokasi aksi lingkungan di Indonesia berdasarkan koordinat GPS yang dilaporkan.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => true,
    ],
    'tentang' => [
        'title' => 'Tentang SWEAPING — Platform Aksi Lingkungan LSM KOMPAS',
        'desc'  => 'SWEAPING adalah platform konservasi & akuntansi karbon untuk komunitas. Dikembangkan oleh LSM KOMPAS untuk mendokumentasikan, memantau, dan mengukur dampak aksi lingkungan.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => true,
    ],
    'kontak' => [
        'title' => 'Kontak — SWEAPING',
        'desc'  => 'Hubungi tim SWEAPING untuk pertanyaan, saran, atau kerja sama di bidang lingkungan.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => true,
    ],
    'login' => [
        'title' => 'Masuk — SWEAPING',
        'desc'  => 'Masuk ke akun SWEAPING Anda untuk melaporkan aksi lingkungan.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => false,
    ],
    'register' => [
        'title' => 'Daftar Akun — SWEAPING',
        'desc'  => 'Daftar akun SWEAPING gratis dan mulai dokumentasikan aksi lingkungan Anda.',
        'image' => $base_img,
        'type'  => 'website',
        'index' => false,
    ],
    'objek_profil' => [
        'title' => 'Profil Objek — SWEAPING',
        'desc'  => 'Profil objek lingkungan di SWEAPING. Lihat riwayat monitoring dan serapan karbon.',
        'image' => $base_img,
        'type'  => 'article',
        'index' => true,
    ],
];

// Halaman privat / auth — noindex
$halaman_noindex = ['login','register','lapor','edit','kelola_user','audit','recycle','diagnostik','pesan','objek_daftar','objek_edit'];

// Pre-fetch data objek untuk SEO halaman profil
if ($halaman === 'objek_profil' && !empty($_GET['id'])) {
    $seo_objek_id = (int)$_GET['id'];
    if ($seo_objek_id > 0) {
        $stmt_seo = mysqli_prepare($conn, "SELECT nama_objek, jenis_objek, lokasi, kode_objek FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        mysqli_stmt_bind_param($stmt_seo, 'i', $seo_objek_id);
        mysqli_stmt_execute($stmt_seo);
        $seo_objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_seo));
        mysqli_stmt_close($stmt_seo);

        if ($seo_objek) {
            $jenis_label = ucfirst($seo_objek['jenis_objek']);
            $meta_seo['objek_profil']['title'] = $seo_objek['nama_objek'] . ' (' . $seo_objek['kode_objek'] . ') — SWEAPING';
            $meta_seo['objek_profil']['desc']  = 'Profil ' . $jenis_label . ' "' . $seo_objek['nama_objek'] . '"'
                . ($seo_objek['lokasi'] ? ' di ' . $seo_objek['lokasi'] : '')
                . '. Lihat riwayat monitoring dan serapan karbon di SWEAPING.';
        }
    }
}

// Ambil meta sesuai halaman aktif
$seo = $meta_seo[$halaman] ?? $meta_seo['beranda'];
if (in_array($halaman, $halaman_noindex, true)) $seo['index'] = false;

// Canonical
$canonical_url = $base_url . '/index.php';
if ($halaman !== 'beranda') {
    $canonical_url .= '?page=' . $halaman;
    if ($halaman === 'objek_profil' && !empty($_GET['id'])) {
        $canonical_url .= '&id=' . (int)$_GET['id'];
    }
}

$notif_pesan = $_SESSION['notif'] ?? null;
unset($_SESSION['notif']);

// ============ ENDPOINT: PROXY FOTO TELEGRAM ============
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
    $url = get_telegram_file_url($file_id);
    if (!$url) { http_response_code(404); exit; }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($code !== 200 || !$data) { http_response_code(404); exit; }
    
    // ============ SAVE TO CACHE ============
    // ============ KOMPRES SEBELUM SAVE ============
    $gd_tersedia = function_exists('imagecreatefromstring');
    if ($gd_tersedia && strlen($data) > 50 * 1024) { // hanya kompres kalau > 50 KB
        $img = @imagecreatefromstring($data);
        if ($img) {
            $w = imagesx($img);
            $h = imagesy($img);
            
            // Resize kalau lebih besar dari 1200px
            $max_dim = 1200;
            $scale = min($max_dim / $w, $max_dim / $h, 1);
            $new_w = (int)($w * $scale);
            $new_h = (int)($h * $scale);
            
            $new_img = imagecreatetruecolor($new_w, $new_h);
            
            // Preserve transparency untuk PNG
            $is_png = (strpos($mime, 'png') !== false);
            if ($is_png) {
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);
            }
            
            imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            
            // Output ke buffer
            ob_start();
            imagejpeg($new_img, null, 78);
            $compressed = ob_get_clean();
            
            imagedestroy($img);
            imagedestroy($new_img);
            
            // Pakai hasil kompresi kalau lebih kecil
            if ($compressed && strlen($compressed) < strlen($data)) {
                $data = $compressed;
                $mime = 'image/jpeg'; // setelah kompres jadi JPEG
            }
        }
    }
    
    // ============ SAVE TO CACHE ============
    @file_put_contents($cache_file, $data);
    
    // ============ AUTO CLEANUP (jalan 1% request) ============
// ============ AUTO CLEANUP (jalan 1% request) ============
    if (random_int(1, 100) === 1) {
        $max_cache_size = 500 * 1024 * 1024; // 500 MB
        $max_age_days   = 30;
        
        $old_files = glob($cache_dir . '*.cache');
        if ($old_files) {
            $now = time();
            $total_size = 0;
            $file_data = [];
            
            foreach ($old_files as $f) {
                $size = @filesize($f);
                $mtime = @filemtime($f);
                if ($size === false || $mtime === false) continue;
                
                $age_days = ($now - $mtime) / 86400;
                
                // Hapus kalau > max_age
                if ($age_days > $max_age_days) {
                    @unlink($f);
                    continue;
                }
                
                $total_size += $size;
                $file_data[] = ['path' => $f, 'size' => $size, 'mtime' => $mtime];
            }
            
            // Kalau total > max_size, hapus TERLAMA
            if ($total_size > $max_cache_size) {
                usort($file_data, function($a, $b) {
                    return $a['mtime'] - $b['mtime'];
                });
                
                foreach ($file_data as $fd) {
                    if ($total_size <= $max_cache_size) break;
                    @unlink($fd['path']);
                    $total_size -= $fd['size'];
                }
            }
        }
    }

    header('Content-Type: ' . ($mime ?: 'image/jpeg'));
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    header('X-Cache-Status: MISS');
    echo $data;
    exit;
}
// ======================================================

// ============ ENDPOINT AJAX: DAFTAR OBJEK ============
if (isset($_GET['ajax']) && $_GET['ajax'] === 'objek_list') {
    header('Content-Type: application/json');
    
    $jenis = $_GET['jenis'] ?? '';
    if (!in_array($jenis, ['pohon','sungai','taman','komposting'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Jenis tidak valid']);
        exit;
    }
    
    $stmt = mysqli_prepare($conn, "SELECT id, kode_objek, nama_objek, lokasi FROM objek WHERE jenis_objek = ? AND status = 'aktif' AND deleted_at IS NULL ORDER BY nama_objek ASC");
    mysqli_stmt_bind_param($stmt, 's', $jenis);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) $data[] = $row;
    mysqli_stmt_close($stmt);
    
    echo json_encode(['status' => 'ok', 'data' => $data]);
    exit;
}

// ============ ENDPOINT AJAX: ESTIMASI CH4 SUNGAI ============
if (isset($_GET['ajax']) && $_GET['ajax'] === 'ch4_estimate') {
    header('Content-Type: application/json');
    
    $do     = $_GET['do'] ?? '';
    $sampah = $_GET['sampah'] ?? '';
    $visual = $_GET['visual'] ?? '';
    
    $hasil = sungai_ch4_estimasi($do, $sampah, $visual);
    
    echo json_encode([
        'status' => 'ok',
        'value'  => $hasil['value'],
        'level'  => $hasil['level']
    ]);
    exit;
}
// ============ END ENDPOINT CH4 ============


// ============ HANDLER AJAX: FOLLOW / UNFOLLOW / CANCEL ============
if (isset($_POST['aksi_follow'])) {
    header('Content-Type: application/json');
    csrf_check();
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Silakan login dulu.']);
        exit;
    }
    
    $target_id = (int)($_POST['target_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $me = (int)$_SESSION['user_id'];
    
    if ($target_id <= 0 || $target_id === $me) {
        echo json_encode(['status' => 'error', 'message' => 'Target tidak valid.']);
        exit;
    }
    
    $stmt = mysqli_prepare($conn, "SELECT id, is_primary_admin FROM users WHERE id = ? AND deleted_at IS NULL AND aktif = 1 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $target_id);
    mysqli_stmt_execute($stmt);
    $target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$target) {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
        exit;
    }
    
    // ---- FOLLOW (kirim request pending) ----
    if ($action === 'follow') {
        // Insert pending; kalau sudah ada, biarkan. Kalau status rejected → reset pending
        $stmt = mysqli_prepare($conn, "INSERT INTO follows (follower_id, following_id, status) VALUES (?, ?, 'pending') ON DUPLICATE KEY UPDATE status = IF(status = 'rejected', 'pending', status)");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Cek status aktual
        $stmt = mysqli_prepare($conn, "SELECT status FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        $new_state = $row['status'] ?? 'pending';
        
                // Kirim notifikasi HANYA kalau status pending DAN belum ada notif serupa yang belum dibaca
        if ($new_state === 'pending') {
            $stmt = mysqli_prepare($conn, "SELECT id FROM notifications WHERE user_id = ? AND tipe = 'follow_request' AND aktor_id = ? AND sudah_dibaca = 0 LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ii', $target_id, $me);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $sudah_ada = (mysqli_stmt_num_rows($stmt) > 0);
            mysqli_stmt_close($stmt);
            
            if (!$sudah_ada) {
                $pesan = $_SESSION['nama'] . ' ingin mengikuti Anda.';
                $stmt = mysqli_prepare($conn, "INSERT INTO notifications (user_id, tipe, aktor_id, pesan) VALUES (?, 'follow_request', ?, ?)");
                mysqli_stmt_bind_param($stmt, 'iis', $target_id, $me, $pesan);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        
        audit_log($conn, 'follow_user', $target_id, "Follow user id=$target_id (status=$new_state)");
        echo json_encode(['status' => 'ok', 'new_state' => $new_state]);
        exit;
    }
    
    // ---- UNFOLLOW (hapus baris) ----
    if ($action === 'unfollow') {
        // Cek status sebelumnya untuk guard primary admin
        $stmt = mysqli_prepare($conn, "SELECT status FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if ($row && $row['status'] === 'accepted' && (int)$target['is_primary_admin'] === 1) {
            echo json_encode(['status' => 'error', 'message' => 'Admin utama tidak dapat di-unfollow.']);
            exit;
        }
        
        $stmt = mysqli_prepare($conn, "DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Hapus notifikasi terkait (biar tidak basi)
        $stmt = mysqli_prepare($conn, "DELETE FROM notifications WHERE tipe = 'follow_request' AND aktor_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        audit_log($conn, 'unfollow_user', $target_id, "Unfollow user id=$target_id");
        echo json_encode(['status' => 'ok', 'new_state' => 'not-following']);
        exit;
    }
    
    // ---- CANCEL (batalkan pending request sendiri) ----
    if ($action === 'cancel') {
        $stmt = mysqli_prepare($conn, "DELETE FROM follows WHERE follower_id = ? AND following_id = ? AND status = 'pending'");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Hapus notifikasi terkait
        $stmt = mysqli_prepare($conn, "DELETE FROM notifications WHERE tipe = 'follow_request' AND aktor_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $me, $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        audit_log($conn, 'cancel_follow', $target_id, "Cancel follow request to $target_id");
        echo json_encode(['status' => 'ok', 'new_state' => 'not-following']);
        exit;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
    exit;
}
// ============ END HANDLER FOLLOW ============

// ============ HANDLER AJAX: RESPOND FOLLOW (accept/reject) ============
if (isset($_POST['aksi_respond_follow'])) {
    header('Content-Type: application/json');
    csrf_check();
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Silakan login dulu.']);
        exit;
    }
    
    $follow_id = (int)($_POST['follow_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $me = (int)$_SESSION['user_id'];
    
    if ($follow_id <= 0 || !in_array($action, ['accept', 'reject'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid.']);
        exit;
    }
    
    // Cek follow milik saya & status pending
    $stmt = mysqli_prepare($conn, "SELECT id, follower_id FROM follows WHERE id = ? AND following_id = ? AND status = 'pending' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $follow_id, $me);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak ditemukan atau sudah diproses.']);
        exit;
    }
    
    $follower_id = (int)$row['follower_id'];
    $new_status = ($action === 'accept') ? 'accepted' : 'rejected';
    
    $stmt = mysqli_prepare($conn, "UPDATE follows SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $follow_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Hapus notifikasi follow_request terkait
    $stmt = mysqli_prepare($conn, "DELETE FROM notifications WHERE tipe = 'follow_request' AND aktor_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $follower_id, $me);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Kirim notifikasi info ke follower
    $pesan = ($action === 'accept')
        ? $_SESSION['nama'] . ' menyetujui permintaan follow Anda.'
        : $_SESSION['nama'] . ' menolak permintaan follow Anda.';
    $tipe = ($action === 'accept') ? 'follow_accepted' : 'follow_rejected';
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (user_id, tipe, aktor_id, pesan) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isis', $follower_id, $tipe, $me, $pesan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    audit_log($conn, 'respond_follow', $follow_id, "Respond follow id=$follow_id -> $new_status");
    echo json_encode(['status' => 'ok', 'new_status' => $new_status]);
    exit;
}
// ============ END HANDLER RESPOND FOLLOW ============



// ============================================================
// PROSES AKSI
// ============================================================

if (isset($_POST['aksi_login'])) {
    csrf_check();
    $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    $stmt = mysqli_prepare($conn, "SELECT id,nama_lengkap,password,role,aktif FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email); mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
    if ($row && $row['aktif'] && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $row['id']; $_SESSION['nama'] = $row['nama_lengkap']; $_SESSION['role'] = $row['role'];
        audit_log($conn, 'login', $row['id'], 'Login berhasil');
        notif('sukses', 'Selamat datang, ' . $row['nama_lengkap'] . '!');
        redirect('index.php?page=beranda');
    }
    audit_log($conn, 'login_gagal', null, 'Email: ' . $email);
    notif('gagal', 'Login gagal! Email atau kata sandi salah.');
    redirect('index.php?page=login');
}

// ============ HANDLER: UPLOAD AVATAR ============
if (isset($_POST['aksi_upload_avatar'])) {
    csrf_check();
    
    if (!isset($_SESSION['user_id'])) {
        notif('gagal', 'Silakan login dulu.');
        redirect('index.php?page=login');
    }
    
    $uid = (int)$_SESSION['user_id'];
    
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        notif('gagal', 'Tidak ada file yang diupload.');
        redirect('index.php?page=user_profil&id=' . $uid);
    }
    
    $file = $_FILES['avatar'];
    $max_size = 2 * 1024 * 1024; // 2 MB
    
    if ($file['size'] > $max_size) {
        notif('gagal', 'Ukuran foto melebihi 2 MB.');
        redirect('index.php?page=user_profil&id=' . $uid);
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        notif('gagal', 'Format harus JPG, PNG, atau WEBP.');
        redirect('index.php?page=user_profil&id=' . $uid);
    }
    
    // Ambil avatar lama untuk dihapus
    $stmt = mysqli_prepare($conn, "SELECT avatar FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    $avatar_dir = UPLOAD_DIR . 'avatar/';
    if (!is_dir($avatar_dir)) @mkdir($avatar_dir, 0755, true);
    
    // Proses resize pakai GD
    $nama_avatar = null;
    
    if ($gd_aktif) {
        $img = null;
        switch ($mime) {
            case 'image/jpeg': $img = @imagecreatefromjpeg($file['tmp_name']); break;
            case 'image/png':  $img = @imagecreatefrompng($file['tmp_name']); break;
            case 'image/webp': $img = @imagecreatefromwebp($file['tmp_name']); break;
        }
        
        if ($img) {
            $w = imagesx($img);
            $h = imagesy($img);
            $size = 400;
            
            // Crop tengah jadi kotak dulu
            $min_dim = min($w, $h);
            $src_x = (int)(($w - $min_dim) / 2);
            $src_y = (int)(($h - $min_dim) / 2);
            
            $new_img = imagecreatetruecolor($size, $size);
            
            // Preserve transparency untuk PNG
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);
            }
            
            imagecopyresampled($new_img, $img, 0, 0, $src_x, $src_y, $size, $size, $min_dim, $min_dim);
            
            $nama_avatar = 'user_' . $uid . '_' . date('Ymd_His') . '.jpg';
            imagejpeg($new_img, $avatar_dir . $nama_avatar, 85);
            
            imagedestroy($img);
            imagedestroy($new_img);
        }
    }
    
    // Fallback: tanpa GD
    if (!$nama_avatar) {
        $ext = 'jpg';
        if ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/webp') $ext = 'webp';
        $nama_avatar = 'user_' . $uid . '_' . date('Ymd_His') . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $avatar_dir . $nama_avatar);
    }
    
    if (!$nama_avatar) {
        notif('gagal', 'Gagal memproses gambar.');
        redirect('index.php?page=user_profil&id=' . $uid);
    }
    
    // Hapus avatar lama
    if (!empty($old['avatar'])) {
        @unlink($avatar_dir . $old['avatar']);
    }
    
    // Update DB
    $stmt = mysqli_prepare($conn, "UPDATE users SET avatar = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $nama_avatar, $uid);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        audit_log($conn, 'upload_avatar', $uid, 'Update avatar');
        notif('sukses', 'Foto profil berhasil diperbarui!');
    } else {
        mysqli_stmt_close($stmt);
        notif('gagal', 'Gagal menyimpan: ' . mysqli_error($conn));
    }
    
    redirect('index.php?page=user_profil&id=' . $uid);
}
// ============ END HANDLER AVATAR ============


if (isset($_POST['aksi_register'])) {
    csrf_check();
    $nama    = trim($_POST['nama_lengkap'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $pwd     = $_POST['password'] ?? '';
        $organisasi = trim($_POST['organisasi'] ?? '');
    if ($organisasi === '') $organisasi = null;
    
    // Validasi
    if ($nama === '') { notif('gagal', 'Nama lengkap wajib diisi.'); redirect('index.php?page=register'); }
    if (strlen($pwd) < 6) { notif('gagal', 'Kata sandi minimal 6 karakter.'); redirect('index.php?page=register'); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { notif('gagal', 'Format email tidak valid.'); redirect('index.php?page=register'); }
    
    // Bersihkan & validasi nomor telepon
$telepon_bersih = preg_replace('/[^0-9]/', '', $telepon);

// Hapus angka 0 di depan kalau ada
if (substr($telepon_bersih, 0, 1) === '0') {
    $telepon_bersih = substr($telepon_bersih, 1);
}

// Tambah +62 di depan
$telepon_bersih = '+62' . $telepon_bersih;

// Validasi panjang (setelah +62, harus 10-14 digit)
if (strlen($telepon_bersih) < 12 || strlen($telepon_bersih) > 16) {
    notif('gagal', 'Nomor telepon tidak valid. Contoh: 81234567890');
    redirect('index.php?page=register');
}
    
    // Cek email sudah ada
    $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($cek, 's', $email); mysqli_stmt_execute($cek); mysqli_stmt_store_result($cek);
    if (mysqli_stmt_num_rows($cek) > 0) { 
        mysqli_stmt_close($cek); 
        notif('gagal', 'Email sudah terdaftar!'); 
        redirect('index.php?page=register'); 
    }
    mysqli_stmt_close($cek);
    
    // Simpan ke database
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap,organisasi,email,telepon,password,role) VALUES (?,?,?,?,?,'user')");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $organisasi, $email, $telepon_bersih, $hash);
    mysqli_stmt_execute($stmt);
    $new_id = mysqli_insert_id($conn); 
    mysqli_stmt_close($stmt);
    
    audit_log($conn, 'register', $new_id, "Registrasi: $email");
    notif('sukses', 'Registrasi berhasil! Silakan masuk.');
    redirect('index.php?page=login');
}

if (isset($_GET['logout'])) {
    audit_log($conn, 'logout', $_SESSION['user_id'] ?? null, 'Logout');
    session_destroy(); header("Location: index.php"); exit;
}

if (isset($_POST['aksi_kontak'])) {
    csrf_check();
    $nama = trim($_POST['nama'] ?? ''); $email = trim($_POST['email'] ?? '');
    $subjek = trim($_POST['subjek'] ?? ''); $pesan = trim($_POST['pesan'] ?? '');
    if ($nama === '' || $email === '' || $subjek === '' || $pesan === '') { notif('gagal', 'Semua kolom wajib diisi.'); redirect('index.php?page=kontak'); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { notif('gagal', 'Format email tidak valid.'); redirect('index.php?page=kontak'); }
    $ip = get_ip();
    $stmt = mysqli_prepare($conn, "INSERT INTO pesan_kontak (nama,email,subjek,pesan,ip_address) VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $subjek, $pesan, $ip);
    if (mysqli_stmt_execute($stmt)) { audit_log($conn, 'kirim_kontak', null, "Dari: $email"); notif('sukses', 'Pesan terkirim! Kami akan menghubungi Anda segera.'); }
    else { notif('gagal', 'Gagal mengirim pesan.'); }
    mysqli_stmt_close($stmt); redirect('index.php?page=kontak');
}

if (isset($_POST['aksi_simpan'])) {
    csrf_check();
    if (!isset($_SESSION['user_id'])) { notif('gagal', 'Silakan login dulu.'); redirect('index.php?page=login'); }
    $uid = (int)$_SESSION['user_id'];
    if (!rate_limit_cek($conn, $uid, RATE_LIMIT_PER_JAM)) { notif('gagal', 'Batas laporan/jam tercapai.'); redirect('index.php?page=lapor'); }
    // ============ AMBIL MASTER DATA OBJEK ============
    $objek_id = (int)($_POST['objek_id'] ?? 0);
    if ($objek_id <= 0) { 
        notif('gagal', 'Objek tidak valid. Silakan pilih objek dulu.'); 
        redirect('index.php?page=objek'); 
    }
    
    $stmt_lo = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt_lo, 'i', $objek_id);
    mysqli_stmt_execute($stmt_lo);
    $lapor_objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_lo));
    mysqli_stmt_close($stmt_lo);
    
    if (!$lapor_objek) { 
        notif('gagal', 'Objek tidak ditemukan.'); 
        redirect('index.php?page=objek'); 
    }
    
    // Ambil dari master data (bukan POST)
    $jenis_objek = $lapor_objek['jenis_objek'];
    $nama_objek  = $lapor_objek['nama_objek'];
    $koordinat   = $lapor_objek['koordinat'];
    
    // Ambil dari POST (yang masih user isi)
    $status_kondisi      = trim($_POST['penilaian_kesehatan'] ?? 'Aman / Bersih / Sehat');
    $nama_kontributor    = trim($_POST['nama_kontributor'] ?? '');
    $peran_kontributor   = trim($_POST['peran_kontributor'] ?? '');
    $catatan             = trim($_POST['catatan'] ?? '');
    
    // ============ VISIBILITY ============
    $visibility = trim($_POST['visibility'] ?? 'publik');
    if (!in_array($visibility, ['publik','teman','privat'], true)) {
        $visibility = 'publik';
    }    
    
    // Default kalau status_kondisi kosong
    if ($status_kondisi === '') $status_kondisi = 'Aman / Bersih / Sehat';
        // ============ AMBIL FIELD BAGIAN D (MONITORING) ============
    $tanggal_monitoring     = trim($_POST['tanggal_monitoring'] ?? '');
    if ($tanggal_monitoring === '') $tanggal_monitoring = null;
    
    $diameter_batang        = trim($_POST['diameter_batang'] ?? '');
$diameter_batang = $diameter_batang === '' ? null : (float)$diameter_batang;
    
    $tinggi_pohon           = trim($_POST['tinggi_pohon'] ?? '');
    $tinggi_pohon           = $tinggi_pohon === '' ? null : (float)$tinggi_pohon;
    
    $lebar_kanopi           = trim($_POST['lebar_kanopi'] ?? '');
    $lebar_kanopi           = $lebar_kanopi === '' ? null : (float)$lebar_kanopi;
    
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

    // ============ AMBIL PIC MONEV ============
        
        // FIX: kalau field tidak ada di POST (form lama), pakai nilai lama dari DB
    $pic_monev_nama  = isset($_POST['pic_monev_nama'])  ? (trim($_POST['pic_monev_nama'])  ?: null) : $r['pic_monev_nama'];
    $pic_monev_peran = isset($_POST['pic_monev_peran']) ? (trim($_POST['pic_monev_peran']) ?: null) : $r['pic_monev_peran'];
        
        // ============ AMBIL FIELD SUNGAI ============
    $debit_air_normal       = trim($_POST['debit_air_normal'] ?? '');
    $debit_air_normal       = $debit_air_normal === '' ? null : (float)$debit_air_normal;

    $debit_air_pasang       = trim($_POST['debit_air_pasang'] ?? '');
    $debit_air_pasang       = $debit_air_pasang === '' ? null : (float)$debit_air_pasang;

    $debit_air_surut        = trim($_POST['debit_air_surut'] ?? '');
    $debit_air_surut        = $debit_air_surut === '' ? null : (float)$debit_air_surut;

    $tinggi_muka_air        = trim($_POST['tinggi_muka_air'] ?? '');
    $tinggi_muka_air        = $tinggi_muka_air === '' ? null : (float)$tinggi_muka_air;

    $lebar_sungai_monitor   = trim($_POST['lebar_sungai_monitor'] ?? '');
    $lebar_sungai_monitor   = $lebar_sungai_monitor === '' ? null : (float)$lebar_sungai_monitor;

    $kedalaman_air          = trim($_POST['kedalaman_air'] ?? '');
    $kedalaman_air          = $kedalaman_air === '' ? null : (float)$kedalaman_air;

    $kondisi_debit          = trim($_POST['kondisi_debit'] ?? '') ?: null;
    $kondisi_air_visual     = trim($_POST['kondisi_air_visual'] ?? '') ?: null;
    $warna_air              = trim($_POST['warna_air'] ?? '') ?: null;
    $bau_air                = trim($_POST['bau_air'] ?? '') ?: null;
    $sampah_sungai          = trim($_POST['sampah_sungai'] ?? '') ?: null;
    $vegetasi_sempadan      = trim($_POST['vegetasi_sempadan'] ?? '') ?: null;
    $erosi_sungai           = trim($_POST['erosi_sungai'] ?? '') ?: null;

    $kualitas_ph            = trim($_POST['kualitas_ph'] ?? '');
    $kualitas_ph            = $kualitas_ph === '' ? null : (float)$kualitas_ph;

    $kualitas_suhu          = trim($_POST['kualitas_suhu'] ?? '');
    $kualitas_suhu          = $kualitas_suhu === '' ? null : (float)$kualitas_suhu;

    $kualitas_tds           = trim($_POST['kualitas_tds'] ?? '');
    $kualitas_tds           = $kualitas_tds === '' ? null : (float)$kualitas_tds;

    $kualitas_do            = trim($_POST['kualitas_do'] ?? '');
    $kualitas_do            = $kualitas_do === '' ? null : (float)$kualitas_do;

    $kualitas_kekeruhan     = trim($_POST['kualitas_kekeruhan'] ?? '');
    $kualitas_kekeruhan     = $kualitas_kekeruhan === '' ? null : (float)$kualitas_kekeruhan;
            // ============ AMBIL FIELD TAMAN ============
    $kondisi_vegetasi    = trim($_POST['kondisi_vegetasi'] ?? '') ?: null;
    $kondisi_fasilitas   = trim($_POST['kondisi_fasilitas'] ?? '') ?: null;
    $kebersihan_taman    = trim($_POST['kebersihan_taman'] ?? '') ?: null;
    $kondisi_lingkungan  = trim($_POST['kondisi_lingkungan'] ?? '') ?: null;
    $kondisi_drainase    = trim($_POST['kondisi_drainase'] ?? '') ?: null;
        // ============ AMBIL FIELD KOMPOSTING D-G ============
    // D. Monitoring Kondisi
    $kompos_kelembapan           = trim($_POST['kompos_kelembapan'] ?? '') ?: null;
    $kompos_suhu                 = trim($_POST['kompos_suhu'] ?? '');
    $kompos_suhu                 = $kompos_suhu === '' ? null : (float)$kompos_suhu;
    $kompos_aerasi               = trim($_POST['kompos_aerasi'] ?? '') ?: null;
    $kompos_bau                  = trim($_POST['kompos_bau'] ?? '') ?: null;
    $kompos_hama                 = trim($_POST['kompos_hama'] ?? '') ?: null;
    $kompos_kecepatan_penguraian = trim($_POST['kompos_kecepatan_penguraian'] ?? '') ?: null;
    $kompos_kondisi_wadah        = trim($_POST['kompos_kondisi_wadah'] ?? '') ?: null;
    $kompos_kebersihan_area      = trim($_POST['kompos_kebersihan_area'] ?? '') ?: null;

    // E. Hasil Komposting
    $kompos_status               = trim($_POST['kompos_status'] ?? '') ?: null;
    $kompos_warna                = trim($_POST['kompos_warna'] ?? '') ?: null;
    $kompos_tekstur              = trim($_POST['kompos_tekstur'] ?? '') ?: null;
    $kompos_aroma                = trim($_POST['kompos_aroma'] ?? '') ?: null;
    $kompos_kematangan           = trim($_POST['kompos_kematangan'] ?? '') ?: null;
    $kompos_kebersihan_anorganik = trim($_POST['kompos_kebersihan_anorganik'] ?? '') ?: null;
    
    $kompos_volume_awal          = trim($_POST['kompos_volume_awal'] ?? '');
    $kompos_volume_awal          = $kompos_volume_awal === '' ? null : (float)$kompos_volume_awal;
    $kompos_volume_akhir         = trim($_POST['kompos_volume_akhir'] ?? '');
    $kompos_volume_akhir         = $kompos_volume_akhir === '' ? null : (float)$kompos_volume_akhir;
    $kompos_berat_kompos         = trim($_POST['kompos_berat_kompos'] ?? '');
    $kompos_berat_kompos         = $kompos_berat_kompos === '' ? null : (float)$kompos_berat_kompos;

    // F. Pemanfaatan
    $kompos_pemanfaatan_arr = $_POST['kompos_pemanfaatan_arr'] ?? [];
    if (!is_array($kompos_pemanfaatan_arr)) $kompos_pemanfaatan_arr = [];
    $kompos_pemanfaatan_json = !empty($kompos_pemanfaatan_arr) ? json_encode(array_values($kompos_pemanfaatan_arr)) : null;
    
    $kompos_jumlah_dimanfaatkan = trim($_POST['kompos_jumlah_dimanfaatkan'] ?? '');
    $kompos_jumlah_dimanfaatkan = $kompos_jumlah_dimanfaatkan === '' ? null : (float)$kompos_jumlah_dimanfaatkan;
    $kompos_jumlah_dijual       = trim($_POST['kompos_jumlah_dijual'] ?? '');
    $kompos_jumlah_dijual       = $kompos_jumlah_dijual === '' ? null : (float)$kompos_jumlah_dijual;
    $kompos_nilai_penjualan     = trim($_POST['kompos_nilai_penjualan'] ?? '');
    $kompos_nilai_penjualan     = $kompos_nilai_penjualan === '' ? null : (float)$kompos_nilai_penjualan;

    // G. Dampak & Keberlanjutan
    $kompos_dampak              = trim($_POST['kompos_dampak'] ?? '') ?: null;
    $kompos_status_operasional  = trim($_POST['kompos_status_operasional'] ?? '') ?: null;
    
    $kompos_faktor_arr = $_POST['kompos_faktor_arr'] ?? [];
    if (!is_array($kompos_faktor_arr)) $kompos_faktor_arr = [];
    $kompos_faktor_json = !empty($kompos_faktor_arr) ? json_encode(array_values($kompos_faktor_arr)) : null;
   if (!in_array($jenis_objek, ['sungai','pohon','taman','komposting'], true)) { 
    notif('gagal', 'Jenis objek tidak valid.'); 
    redirect('index.php?page=lapor'); 
}
    if ($nama_objek === '' || $koordinat === '' || $catatan === '' || $nama_kontributor === '' || $peran_kontributor === '') { notif('gagal', 'Data belum lengkap.'); redirect('index.php?page=lapor'); }
    
    // ============ VARIABEL UMUR & KARBON ============
    $umur_hari_kolom       = null;
    $umur_teks_kolom       = null;
    $serapan_karbon_ilmiah = null;
    $metode_hitung_kolom   = null;
    
    // ============ TANGGAL MONITORING (untuk semua jenis) ============
    $tgl_monitoring = trim($_POST['tanggal_monitoring'] ?? '');
    if ($tgl_monitoring === '') $tgl_monitoring = date('Y-m-d');
    
    if ($jenis_objek === 'pohon') {
        // Ambil tanggal tanam dari objek master data
        $tgl_tanam_objek = $lapor_objek['tanggal_tanam'] ?? null;
        
        // 1. Hitung umur otomatis dari tanggal tanam
        $hasil_umur = hitung_umur($tgl_tanam_objek, $tgl_monitoring);
        if ($hasil_umur) {
            $umur_hari_kolom = $hasil_umur['total_hari'];
            $umur_teks_kolom = $hasil_umur['teks_full'];
            $umur_bulan = (int)($hasil_umur['total_hari'] / 30);
        }
        
        // 2. Hitung serapan karbon dengan Chave 2005
        $dbh_input    = trim($_POST['diameter_batang'] ?? '');
        $tinggi_input = trim($_POST['tinggi_pohon'] ?? '');
        
        $co2_chave = chave_2005($dbh_input, $tinggi_input, 0.6);
        
        if ($co2_chave !== null) {
            $serapan_karbon        = $co2_chave;
            $serapan_karbon_ilmiah = $co2_chave;
            $metode_hitung_kolom   = 'chave_2005';
        } else {
            // Fallback: rumus lama (umur_bulan × 2.5)
            if ($umur_bulan > 0) {
                $serapan_karbon      = $umur_bulan * 2.5;
                $metode_hitung_kolom = 'umur_fallback';
            }
        }
    }
    $nama_foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        if ($file['size'] > MAX_FILE_SIZE) { notif('gagal', 'Foto melebihi ' . (MAX_FILE_SIZE/1024/1024) . ' MB.'); redirect('index.php?page=lapor'); }
        $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) { notif('gagal', 'Format foto harus JPG, PNG, atau WEBP.'); redirect('index.php?page=lapor'); }
        if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
        if ($gd_aktif) {
            $img = null;
            switch ($mime) { case 'image/jpeg': $img = @imagecreatefromjpeg($file['tmp_name']); break; case 'image/png': $img = @imagecreatefrompng($file['tmp_name']); break; case 'image/webp': $img = @imagecreatefromwebp($file['tmp_name']); break; }
            if ($img) {
                $w = imagesx($img); $h = imagesy($img);
                $scale = min(MAX_DIMENSION / $w, MAX_DIMENSION / $h, 1);
                $new_w = (int)($w * $scale); $new_h = (int)($h * $scale);
                $new_img = imagecreatetruecolor($new_w, $new_h);
                if ($mime === 'image/png' || $mime === 'image/webp') { imagealphablending($new_img, false); imagesavealpha($new_img, true); }
                imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                $nama_foto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
                imagejpeg($new_img, UPLOAD_DIR . $nama_foto, JPEG_QUALITY);
                $thumb_h = (int)(THUMB_WIDTH * $new_h / $new_w);
                $thumb = imagecreatetruecolor(THUMB_WIDTH, $thumb_h);
                imagecopyresampled($thumb, $new_img, 0, 0, 0, 0, THUMB_WIDTH, $thumb_h, $new_w, $new_h);
                imagejpeg($thumb, UPLOAD_DIR . 'thumb_' . $nama_foto, THUMB_QUALITY);
                imagedestroy($img); imagedestroy($new_img); imagedestroy($thumb);
            }
        } else { $nama_foto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg'; move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_foto); }
    }
    // ============ UPLOAD KE TELEGRAM ============
$telegram_file_id = null;
$telegram_message_id = null;
if ($nama_foto && file_exists(UPLOAD_DIR . $nama_foto)) {
    $caption = "📷 " . strtoupper($jenis_objek) . " - " . $nama_objek . "\n";
    $caption .= "📍 " . $koordinat . "\n";
    $caption .= "📝 " . mb_substr($catatan, 0, 200);
    $tg_result = upload_ke_telegram(UPLOAD_DIR . $nama_foto, $jenis_objek, $caption);
    if ($tg_result) {
        $telegram_file_id = $tg_result['file_id'];
        $telegram_message_id = $tg_result['message_id'];
    }
}
// ============================================
    $objek_id = (int)($_POST['objek_id'] ?? 0);
if ($objek_id <= 0) $objek_id = 0;
             $stmt = mysqli_prepare($conn, "INSERT INTO laporan_aksi (
        objek_id, user_id, jenis_objek, nama_objek, koordinat,
        status_kondisi, nama_kontributor, peran_kontributor, catatan, visibility,
        umur_bulan, serapan_karbon, volume_sampah, hasil_kompos,
        foto, telegram_file_id, telegram_message_id,
        tanggal_monitoring, diameter_batang, tinggi_pohon, lebar_kanopi,
        lingkungan_tumbuh,
        kondisi_pertumbuhan, kondisi_batang, kondisi_kanopi, kondisi_akar,
        kondisi_cabang, kondisi_daun, kondisi_buah, kondisi_bunga,
        kondisi_lainnya, penilaian_kesehatan, risiko, rekomendasi,
        pic_monev_nama, pic_monev_peran,
        debit_air_normal, debit_air_pasang, debit_air_surut,
        tinggi_muka_air, lebar_sungai_monitor, kedalaman_air,
        kondisi_debit, kondisi_air_visual, warna_air, bau_air,
        sampah_sungai, vegetasi_sempadan, erosi_sungai,
        kualitas_ph, kualitas_suhu, kualitas_tds, kualitas_do, kualitas_kekeruhan,
        kondisi_vegetasi, kondisi_fasilitas, kebersihan_taman, kondisi_lingkungan, kondisi_drainase,
        kompos_kelembapan, kompos_suhu, kompos_aerasi, kompos_bau, kompos_hama,
        kompos_kecepatan_penguraian, kompos_kondisi_wadah, kompos_kebersihan_area,
        kompos_status, kompos_warna, kompos_tekstur, kompos_aroma,
        kompos_kematangan, kompos_kebersihan_anorganik,
        kompos_volume_awal, kompos_volume_akhir, kompos_berat_kompos,
        kompos_pemanfaatan, kompos_jumlah_dimanfaatkan, kompos_jumlah_dijual,
        kompos_nilai_penjualan, kompos_dampak, kompos_status_operasional, kompos_faktor_keberlanjutan, umur_hari, umur_teks, serapan_karbon_ilmiah, metode_hitung 
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        if (!$stmt) { die('<div style="background:#fecaca;color:#7f1d1d;padding:20px;font-family:monospace;font-size:16px;"><b>PREPARE GAGAL:</b><br>' . htmlspecialchars(mysqli_error($conn)) . '</div>'); }
        mysqli_stmt_bind_param($stmt,
        'iissssssssidiissisdddsssssssssssssssddddddsssssssdddddssssssdssssssssssssdddsdddsssisds',
        $objek_id, $uid, $jenis_objek, $nama_objek, $koordinat,
        $status_kondisi, $nama_kontributor, $peran_kontributor, $catatan, $visibility,
        $umur_bulan, $serapan_karbon, $volume_sampah, $hasil_kompos,
        $nama_foto, $telegram_file_id, $telegram_message_id,
        $tanggal_monitoring, $diameter_batang, $tinggi_pohon, $lebar_kanopi,
        $lingkungan_tumbuh,
        $kondisi_pertumbuhan, $kondisi_batang, $kondisi_kanopi, $kondisi_akar,
        $kondisi_cabang, $kondisi_daun, $kondisi_buah, $kondisi_bunga,
        $kondisi_lainnya, $penilaian_kesehatan, $risiko, $rekomendasi,
        $pic_monev_nama, $pic_monev_peran,
        $debit_air_normal, $debit_air_pasang, $debit_air_surut,
        $tinggi_muka_air, $lebar_sungai_monitor, $kedalaman_air,
        $kondisi_debit, $kondisi_air_visual, $warna_air, $bau_air,
        $sampah_sungai, $vegetasi_sempadan, $erosi_sungai,
        $kualitas_ph, $kualitas_suhu, $kualitas_tds, $kualitas_do, $kualitas_kekeruhan,
        $kondisi_vegetasi, $kondisi_fasilitas, $kebersihan_taman, $kondisi_lingkungan, $kondisi_drainase,
        $kompos_kelembapan, $kompos_suhu, $kompos_aerasi, $kompos_bau, $kompos_hama,
        $kompos_kecepatan_penguraian, $kompos_kondisi_wadah, $kompos_kebersihan_area,
        $kompos_status, $kompos_warna, $kompos_tekstur, $kompos_aroma,
        $kompos_kematangan, $kompos_kebersihan_anorganik,
        $kompos_volume_awal, $kompos_volume_akhir, $kompos_berat_kompos,
        $kompos_pemanfaatan_json, $kompos_jumlah_dimanfaatkan, $kompos_jumlah_dijual,
        $kompos_nilai_penjualan, $kompos_dampak, $kompos_status_operasional, $kompos_faktor_json, $umur_hari_kolom, $umur_teks_kolom, $serapan_karbon_ilmiah, $metode_hitung_kolom
    );

    if (mysqli_stmt_execute($stmt)) { $new_id = mysqli_insert_id($conn); audit_log($conn, 'buat_laporan', $new_id, "Objek: $nama_objek"); notif('sukses', 'Laporan berhasil direkam!'); }
else { 
        $err_detail = 'Gagal menyimpan. Error: ' . mysqli_stmt_error($stmt);
        $err_detail .= ' | SQL errno: ' . mysqli_stmt_errno($stmt);
        notif('gagal', $err_detail);
    }
    mysqli_stmt_close($stmt); redirect('index.php?page=arsip');
}
// ============ PROSES DAFTAR OBJEK BARU ============
if (isset($_POST['aksi_daftar_objek'])) {
    csrf_check();
    
    // Harus login
    if (!isset($_SESSION['user_id'])) { 
        notif('gagal', 'Silakan login dulu.'); 
        redirect('index.php?page=login'); 
    }
    
    $uid = (int)$_SESSION['user_id'];
    
    // Ambil data dari form (umum)
    $jenis_objek = $_POST['jenis_objek'] ?? '';
    $nama_objek  = trim($_POST['nama_objek'] ?? '');
    $lokasi      = trim($_POST['lokasi'] ?? '');
    $koordinat   = trim($_POST['koordinat'] ?? '');
    $catatan     = trim($_POST['catatan'] ?? '');
        $visibility  = trim($_POST['visibility'] ?? 'publik');
    if (!in_array($visibility, ['publik','teman','privat'], true)) $visibility = 'publik';

    // Ambil data field khusus POHON
    $nama_latin          = trim($_POST['nama_latin'] ?? '');
    $famili              = trim($_POST['famili'] ?? '');
    $asal_daerah         = trim($_POST['asal_daerah'] ?? '');
    $nama_daerah_lain    = trim($_POST['nama_daerah_lain'] ?? '');
    $status_pohon        = trim($_POST['status_pohon'] ?? '');
    $potensi_pemanfaatan = trim($_POST['potensi_pemanfaatan'] ?? '');
    $tanggal_tanam       = trim($_POST['tanggal_tanam'] ?? '');
    $alasan_penanaman    = trim($_POST['alasan_penanaman'] ?? '');
    $penyelenggara       = trim($_POST['penyelenggara'] ?? '');
    $pelaksana           = trim($_POST['pelaksana'] ?? '');
    $sumber_dana         = trim($_POST['sumber_dana'] ?? '');
    $jenis_bibit         = trim($_POST['jenis_bibit'] ?? '');
    $asal_bibit          = trim($_POST['asal_bibit'] ?? '');
    $kategori_ias        = trim($_POST['kategori_ias'] ?? '');

        // Ambil data field khusus SUNGAI
    $nama_lain_sungai     = trim($_POST['nama_lain_sungai'] ?? '');
    $sumber_mata_air      = trim($_POST['sumber_mata_air'] ?? '');
    $panjang_sungai       = trim($_POST['panjang_sungai'] ?? '');
    $lebar_rata_rata      = trim($_POST['lebar_rata_rata'] ?? '');
    $kedalaman_rata_rata  = trim($_POST['kedalaman_rata_rata'] ?? '');
    $daerah_hulu          = trim($_POST['daerah_hulu'] ?? '');
    $daerah_hilir         = trim($_POST['daerah_hilir'] ?? '');
    $muara                = trim($_POST['muara'] ?? '');
    $jenis_sungai         = trim($_POST['jenis_sungai'] ?? '');
    $ada_vegetasi         = isset($_POST['ada_vegetasi']) ? (int)$_POST['ada_vegetasi'] : null;
    $ada_sempadan         = isset($_POST['ada_sempadan']) ? (int)$_POST['ada_sempadan'] : null;

    // Dasar sungai & pemanfaatan (array dari checkbox) → JSON
    $dasar_arr = $_POST['dasar_sungai'] ?? [];
    $pemanfaatan_arr = $_POST['pemanfaatan_sungai'] ?? [];
    if (!is_array($dasar_arr)) $dasar_arr = [];
    if (!is_array($pemanfaatan_arr)) $pemanfaatan_arr = [];
    $dasar_sungai_json       = !empty($dasar_arr) ? json_encode(array_values($dasar_arr)) : null;
    $pemanfaatan_sungai_json = !empty($pemanfaatan_arr) ? json_encode(array_values($pemanfaatan_arr)) : null;

    // Kosongkan string kosong → NULL (Sungai)
    foreach ([
        'nama_lain_sungai','sumber_mata_air','daerah_hulu','daerah_hilir',
        'muara','jenis_sungai'
    ] as $var) {
        if ($$var === '') $$var = null;
    }
        // Ambil data field khusus TAMAN
    $taman_pengelola         = trim($_POST['taman_pengelola'] ?? '');
    $taman_luas_area         = trim($_POST['taman_luas_area'] ?? '');
    $taman_luas_area         = $taman_luas_area === '' ? null : (float)$taman_luas_area;
    $taman_tahun_pembangunan = trim($_POST['taman_tahun_pembangunan'] ?? '');
    $taman_tahun_pembangunan = $taman_tahun_pembangunan === '' ? null : (int)$taman_tahun_pembangunan;
    $taman_tahun_revitalisasi = trim($_POST['taman_tahun_revitalisasi'] ?? '');
    $taman_tahun_revitalisasi = $taman_tahun_revitalisasi === '' ? null : (int)$taman_tahun_revitalisasi;
    $taman_status_kepemilikan = trim($_POST['taman_status_kepemilikan'] ?? '');
    $taman_vegetasi_pohon    = trim($_POST['taman_vegetasi_pohon'] ?? '');
    $taman_vegetasi_tanaman  = trim($_POST['taman_vegetasi_tanaman'] ?? '');
    $taman_intensitas        = trim($_POST['taman_intensitas'] ?? '');

    // Array checkbox → JSON
    $taman_kategori_arr = $_POST['taman_kategori_tanaman'] ?? [];
    $taman_pemanfaatan_arr = $_POST['taman_pemanfaatan'] ?? [];
    $taman_fasilitas_arr = $_POST['taman_fasilitas'] ?? [];
    if (!is_array($taman_kategori_arr)) $taman_kategori_arr = [];
    if (!is_array($taman_pemanfaatan_arr)) $taman_pemanfaatan_arr = [];
    if (!is_array($taman_fasilitas_arr)) $taman_fasilitas_arr = [];

    $taman_kategori_json    = !empty($taman_kategori_arr) ? json_encode(array_values($taman_kategori_arr)) : null;
    $taman_pemanfaatan_json = !empty($taman_pemanfaatan_arr) ? json_encode(array_values($taman_pemanfaatan_arr)) : null;
    $taman_fasilitas_json   = !empty($taman_fasilitas_arr) ? json_encode(array_values($taman_fasilitas_arr)) : null;

    // Kosongkan string kosong → NULL (Taman)
    foreach ([
        'taman_pengelola','taman_status_kepemilikan',
        'taman_vegetasi_pohon','taman_vegetasi_tanaman','taman_intensitas'
    ] as $var) {
        if ($$var === '') $$var = null;
    }
        // Ambil data field khusus KOMPOSTING
    $kompos_pengelola     = trim($_POST['kompos_pengelola'] ?? '');
    $kompos_metode        = trim($_POST['kompos_metode'] ?? '');
    $kompos_kapasitas     = trim($_POST['kompos_kapasitas'] ?? '');
    $kompos_kapasitas     = $kompos_kapasitas === '' ? null : (float)$kompos_kapasitas;
    $kompos_luas_area     = trim($_POST['kompos_luas_area'] ?? '');
    $kompos_luas_area     = $kompos_luas_area === '' ? null : (float)$kompos_luas_area;
    $kompos_tahun_mulai   = trim($_POST['kompos_tahun_mulai'] ?? '');
    $kompos_tahun_mulai   = $kompos_tahun_mulai === '' ? null : (int)$kompos_tahun_mulai;
    $kompos_jumlah_komposter = trim($_POST['kompos_jumlah_komposter'] ?? '');
    $kompos_jumlah_komposter = $kompos_jumlah_komposter === '' ? null : (int)$kompos_jumlah_komposter;
    $kompos_kelompok      = trim($_POST['kompos_kelompok'] ?? '');

    // Bahan organik (checkbox array) → JSON
    $kompos_bahan_arr = $_POST['kompos_bahan_organik_arr'] ?? [];
    if (!is_array($kompos_bahan_arr)) $kompos_bahan_arr = [];
    $kompos_bahan_organik_json = !empty($kompos_bahan_arr) ? json_encode(array_values($kompos_bahan_arr)) : null;

    // Kosongkan string kosong → NULL (Komposting)
    foreach ([
        'kompos_pengelola','kompos_metode','kompos_kelompok'
    ] as $var) {
        if ($$var === '') $$var = null;
    }

    // Karakteristik & manfaat (array dari checkbox) → simpan sebagai JSON
    $karakteristik_arr = $_POST['karakteristik'] ?? [];
    $manfaat_arr       = $_POST['manfaat'] ?? [];
    if (!is_array($karakteristik_arr)) $karakteristik_arr = [];
    if (!is_array($manfaat_arr)) $manfaat_arr = [];
    $karakteristik_json = !empty($karakteristik_arr) ? json_encode(array_values($karakteristik_arr)) : null;
    $manfaat_json       = !empty($manfaat_arr) ? json_encode(array_values($manfaat_arr)) : null;

    // Ambil data Monev
    $pic_monev_nama  = trim($_POST['pic_monev_nama'] ?? '');
    $pic_monev_peran = trim($_POST['pic_monev_peran'] ?? '');

    // Kosongkan kalau string kosong (biar jadi NULL di DB)
    foreach ([
        'nama_latin','famili','asal_daerah','nama_daerah_lain','status_pohon',
        'potensi_pemanfaatan','tanggal_tanam','alasan_penanaman','penyelenggara',
        'pelaksana','sumber_dana','jenis_bibit','asal_bibit','kategori_ias',
        'pic_monev_nama','pic_monev_peran'
    ] as $var) {
        if ($$var === '') $$var = null;
    }
    
    // Validasi
    if (!in_array($jenis_objek, ['pohon','sungai','taman','komposting'], true)) { 
        notif('gagal', 'Jenis objek tidak valid.'); 
        redirect('index.php?page=objek_daftar'); 
    }
    
    if ($nama_objek === '') { 
        notif('gagal', 'Nama objek wajib diisi.'); 
        redirect('index.php?page=objek_daftar'); 
    }
    
    // Generate kode unik
    $kode_objek = generate_kode_objek($conn, $jenis_objek);
    
    // Proses upload foto (kalau ada)
    $nama_foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        
        if ($file['size'] > MAX_FILE_SIZE) { 
            notif('gagal', 'Foto melebihi ' . (MAX_FILE_SIZE/1024/1024) . ' MB.'); 
            redirect('index.php?page=objek_daftar'); 
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 
        $mime = finfo_file($finfo, $file['tmp_name']); 
        finfo_close($finfo);
        
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) { 
            notif('gagal', 'Format foto harus JPG, PNG, atau WEBP.'); 
            redirect('index.php?page=objek_daftar'); 
        }
        
        if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
        
        // Kompres foto pakai GD kalau tersedia
        if ($gd_aktif) {
            $img = null;
            switch ($mime) { 
                case 'image/jpeg': $img = @imagecreatefromjpeg($file['tmp_name']); break; 
                case 'image/png':  $img = @imagecreatefrompng($file['tmp_name']); break; 
                case 'image/webp': $img = @imagecreatefromwebp($file['tmp_name']); break; 
            }
            
            if ($img) {
                $w = imagesx($img); $h = imagesy($img);
                $scale = min(MAX_DIMENSION / $w, MAX_DIMENSION / $h, 1);
                $new_w = (int)($w * $scale); $new_h = (int)($h * $scale);
                $new_img = imagecreatetruecolor($new_w, $new_h);
                if ($mime === 'image/png' || $mime === 'image/webp') { 
                    imagealphablending($new_img, false); 
                    imagesavealpha($new_img, true); 
                }
                imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                $nama_foto = 'objek_' . $kode_objek . '_' . date('Ymd_His') . '.jpg';
                imagejpeg($new_img, UPLOAD_DIR . $nama_foto, JPEG_QUALITY);
                imagedestroy($img); 
                imagedestroy($new_img);
            }
        } else {
            // Tanpa GD: upload langsung
            $nama_foto = 'objek_' . $kode_objek . '_' . date('Ymd_His') . '.jpg';
            move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_foto);
        }
    }
// ============ UPLOAD KE TELEGRAM (OBJEK BARU) ============
$telegram_file_id = null;
$telegram_message_id = null;
if ($nama_foto && file_exists(UPLOAD_DIR . $nama_foto)) {
    $caption = "📷 OBJEK " . strtoupper($jenis_objek) . " - " . $nama_objek . "\n";
    $caption .= "📍 " . $lokasi;
    $tg_result = upload_ke_telegram(UPLOAD_DIR . $nama_foto, $jenis_objek, $caption);
    if ($tg_result) {
        $telegram_file_id = $tg_result['file_id'];
        $telegram_message_id = $tg_result['message_id'];
        
        // Hapus file lokal
        @unlink(UPLOAD_DIR . $nama_foto);
        $nama_foto = null;
    }
}
// ========================================================
    // Simpan ke database
          $stmt = mysqli_prepare($conn, "INSERT INTO objek (
        kode_objek, jenis_objek, nama_objek, lokasi, koordinat, user_id,
        foto_pertama, catatan, telegram_file_id, telegram_message_id,
        nama_latin, famili, asal_daerah, nama_daerah_lain, status_pohon,
        potensi_pemanfaatan, karakteristik, manfaat,
        tanggal_tanam, alasan_penanaman, penyelenggara, pelaksana,
        sumber_dana, jenis_bibit, asal_bibit, kategori_ias,
        pic_monev_nama, pic_monev_peran,
        nama_lain_sungai, sumber_mata_air, panjang_sungai, lebar_rata_rata,
        kedalaman_rata_rata, daerah_hulu, daerah_hilir, muara,
        jenis_sungai, dasar_sungai, ada_vegetasi, ada_sempadan, pemanfaatan_sungai,
        taman_pengelola, taman_luas_area, taman_tahun_pembangunan, taman_tahun_revitalisasi,
        taman_status_kepemilikan, taman_vegetasi_pohon, taman_vegetasi_tanaman,
        taman_kategori_tanaman, taman_pemanfaatan, taman_intensitas, taman_fasilitas,
        kompos_pengelola, kompos_metode, kompos_kapasitas, kompos_luas_area,
        kompos_tahun_mulai, kompos_jumlah_komposter, kompos_kelompok, kompos_bahan_organik, visibility
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        // ============ BIND PARAM DINAMIS (ANTI-MISMATCH) ============
    $bind_values = [
        $kode_objek, $jenis_objek, $nama_objek, $lokasi, $koordinat, $uid,
        $nama_foto, $catatan, $telegram_file_id, $telegram_message_id,
        $nama_latin, $famili, $asal_daerah, $nama_daerah_lain, $status_pohon,
        $potensi_pemanfaatan, $karakteristik_json, $manfaat_json,
        $tanggal_tanam, $alasan_penanaman, $penyelenggara, $pelaksana,
        $sumber_dana, $jenis_bibit, $asal_bibit, $kategori_ias,
        $pic_monev_nama, $pic_monev_peran,
        $nama_lain_sungai, $sumber_mata_air, $panjang_sungai, $lebar_rata_rata,
        $kedalaman_rata_rata, $daerah_hulu, $daerah_hilir, $muara,
        $jenis_sungai, $dasar_sungai_json, $ada_vegetasi, $ada_sempadan, $pemanfaatan_sungai_json,
        $taman_pengelola, $taman_luas_area, $taman_tahun_pembangunan, $taman_tahun_revitalisasi,
        $taman_status_kepemilikan, $taman_vegetasi_pohon, $taman_vegetasi_tanaman,
        $taman_kategori_json, $taman_pemanfaatan_json, $taman_intensitas, $taman_fasilitas_json,
        $kompos_pengelola, $kompos_metode, $kompos_kapasitas, $kompos_luas_area,
        $kompos_tahun_mulai, $kompos_jumlah_komposter, $kompos_kelompok, $kompos_bahan_organik_json,
        $visibility
    ];
    $type_string = '';
    foreach ($bind_values as $bv) {
        $type_string .= is_int($bv) ? 'i' : (is_float($bv) ? 'd' : 's');
    }
    mysqli_stmt_bind_param($stmt, $type_string, ...$bind_values);
    // ============ END BIND PARAM DINAMIS ============
    
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        audit_log($conn, 'daftar_objek', $new_id, "Kode: $kode_objek | Nama: $nama_objek");
        notif('sukses', "Objek $kode_objek berhasil didaftarkan!");
} else {
    notif('gagal', 'Gagal: ' . mysqli_stmt_error($stmt));
}
    mysqli_stmt_close($stmt);
    redirect('index.php?page=objek');
}

// ============ PROSES EDIT OBJEK (KOMPREHENSIF) ============
if (isset($_POST['aksi_update_objek'])) {
    csrf_check();
    
    if (!isset($_SESSION['user_id'])) { 
        notif('gagal', 'Silakan login dulu.'); 
        redirect('index.php?page=login'); 
    }
    
    $uid = (int)$_SESSION['user_id'];
    $id  = (int)($_POST['id'] ?? 0);
    
    if ($id <= 0) { 
        notif('gagal', 'ID objek tidak valid.'); 
        redirect('index.php?page=objek'); 
    }
    
    // Cek kepemilikan
    $stmt = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$r) { 
        notif('gagal', 'Objek tidak ditemukan.'); 
        redirect('index.php?page=objek'); 
    }
    if ($r['user_id'] != $uid && !is_admin()) { 
        notif('gagal', 'Tidak berhak mengedit objek ini.'); 
        redirect('index.php?page=objek'); 
    }
    
    // ===== FIELD UMUM =====
    $nama_objek = trim($_POST['nama_objek'] ?? '');
    $lokasi     = trim($_POST['lokasi'] ?? '');
    $koordinat  = trim($_POST['koordinat'] ?? '');
    $catatan    = trim($_POST['catatan'] ?? '');
    $status     = $_POST['status'] ?? 'aktif';
    if (!in_array($status, ['aktif','tidak_aktif'], true)) $status = 'aktif';
    
    $visibility = trim($_POST['visibility'] ?? 'publik');
    if (!in_array($visibility, ['publik','teman','privat'], true)) $visibility = 'publik';
    
    if ($nama_objek === '') { 
        notif('gagal', 'Nama objek wajib diisi.'); 
        redirect('index.php?page=objek_edit&id=' . $id); 
    }
    
    // ===== FIELD POHON =====
    $nama_latin          = trim($_POST['nama_latin'] ?? '') ?: null;
    $famili              = trim($_POST['famili'] ?? '') ?: null;
    $asal_daerah         = trim($_POST['asal_daerah'] ?? '') ?: null;
    $nama_daerah_lain    = trim($_POST['nama_daerah_lain'] ?? '') ?: null;
    $status_pohon        = trim($_POST['status_pohon'] ?? '') ?: null;
    $kategori_ias        = trim($_POST['kategori_ias'] ?? '') ?: null;
    $potensi_pemanfaatan = trim($_POST['potensi_pemanfaatan'] ?? '') ?: null;
    $tanggal_tanam       = trim($_POST['tanggal_tanam'] ?? '') ?: null;
    $alasan_penanaman    = trim($_POST['alasan_penanaman'] ?? '') ?: null;
    $penyelenggara       = trim($_POST['penyelenggara'] ?? '') ?: null;
    $pelaksana           = trim($_POST['pelaksana'] ?? '') ?: null;
    $sumber_dana         = trim($_POST['sumber_dana'] ?? '') ?: null;
    $jenis_bibit         = trim($_POST['jenis_bibit'] ?? '') ?: null;
    $asal_bibit          = trim($_POST['asal_bibit'] ?? '') ?: null;
    
    $karakteristik_arr = $_POST['karakteristik'] ?? [];
    if (!is_array($karakteristik_arr)) $karakteristik_arr = [];
    $karakteristik_json = !empty($karakteristik_arr) ? json_encode(array_values($karakteristik_arr)) : null;
    
    $manfaat_arr = $_POST['manfaat'] ?? [];
    if (!is_array($manfaat_arr)) $manfaat_arr = [];
    $manfaat_json = !empty($manfaat_arr) ? json_encode(array_values($manfaat_arr)) : null;
    
    // ===== FIELD SUNGAI =====
    $nama_lain_sungai    = trim($_POST['nama_lain_sungai'] ?? '') ?: null;
    $sumber_mata_air     = trim($_POST['sumber_mata_air'] ?? '') ?: null;
    $panjang_sungai      = trim($_POST['panjang_sungai'] ?? ''); $panjang_sungai = $panjang_sungai === '' ? null : (float)$panjang_sungai;
    $lebar_rata_rata     = trim($_POST['lebar_rata_rata'] ?? ''); $lebar_rata_rata = $lebar_rata_rata === '' ? null : (float)$lebar_rata_rata;
    $kedalaman_rata_rata = trim($_POST['kedalaman_rata_rata'] ?? ''); $kedalaman_rata_rata = $kedalaman_rata_rata === '' ? null : (float)$kedalaman_rata_rata;
    $daerah_hulu         = trim($_POST['daerah_hulu'] ?? '') ?: null;
    $daerah_hilir        = trim($_POST['daerah_hilir'] ?? '') ?: null;
    $muara               = trim($_POST['muara'] ?? '') ?: null;
    $jenis_sungai        = trim($_POST['jenis_sungai'] ?? '') ?: null;
    $ada_vegetasi        = isset($_POST['ada_vegetasi']) && $_POST['ada_vegetasi'] !== '' ? (int)$_POST['ada_vegetasi'] : null;
    $ada_sempadan        = isset($_POST['ada_sempadan']) && $_POST['ada_sempadan'] !== '' ? (int)$_POST['ada_sempadan'] : null;
    
    $dasar_arr = $_POST['dasar_sungai'] ?? [];
    if (!is_array($dasar_arr)) $dasar_arr = [];
    $dasar_sungai_json = !empty($dasar_arr) ? json_encode(array_values($dasar_arr)) : null;
    
    $pemanfaatan_sungai_arr = $_POST['pemanfaatan_sungai'] ?? [];
    if (!is_array($pemanfaatan_sungai_arr)) $pemanfaatan_sungai_arr = [];
    $pemanfaatan_sungai_json = !empty($pemanfaatan_sungai_arr) ? json_encode(array_values($pemanfaatan_sungai_arr)) : null;
    
    // ===== FIELD TAMAN =====
    $taman_pengelola          = trim($_POST['taman_pengelola'] ?? '') ?: null;
    $taman_luas_area          = trim($_POST['taman_luas_area'] ?? ''); $taman_luas_area = $taman_luas_area === '' ? null : (float)$taman_luas_area;
    $taman_tahun_pembangunan  = trim($_POST['taman_tahun_pembangunan'] ?? ''); $taman_tahun_pembangunan = $taman_tahun_pembangunan === '' ? null : (int)$taman_tahun_pembangunan;
    $taman_tahun_revitalisasi = trim($_POST['taman_tahun_revitalisasi'] ?? ''); $taman_tahun_revitalisasi = $taman_tahun_revitalisasi === '' ? null : (int)$taman_tahun_revitalisasi;
    $taman_status_kepemilikan = trim($_POST['taman_status_kepemilikan'] ?? '') ?: null;
    $taman_vegetasi_pohon     = trim($_POST['taman_vegetasi_pohon'] ?? '') ?: null;
    $taman_vegetasi_tanaman   = trim($_POST['taman_vegetasi_tanaman'] ?? '') ?: null;
    $taman_intensitas         = trim($_POST['taman_intensitas'] ?? '') ?: null;
    
    $taman_kategori_arr = $_POST['taman_kategori_tanaman'] ?? [];
    if (!is_array($taman_kategori_arr)) $taman_kategori_arr = [];
    $taman_kategori_json = !empty($taman_kategori_arr) ? json_encode(array_values($taman_kategori_arr)) : null;
    
    $taman_pemanfaatan_arr = $_POST['taman_pemanfaatan'] ?? [];
    if (!is_array($taman_pemanfaatan_arr)) $taman_pemanfaatan_arr = [];
    $taman_pemanfaatan_json = !empty($taman_pemanfaatan_arr) ? json_encode(array_values($taman_pemanfaatan_arr)) : null;
    
    $taman_fasilitas_arr = $_POST['taman_fasilitas'] ?? [];
    if (!is_array($taman_fasilitas_arr)) $taman_fasilitas_arr = [];
    $taman_fasilitas_json = !empty($taman_fasilitas_arr) ? json_encode(array_values($taman_fasilitas_arr)) : null;
    
    // ===== FIELD KOMPOSTING =====
    $kompos_pengelola        = trim($_POST['kompos_pengelola'] ?? '') ?: null;
    $kompos_metode           = trim($_POST['kompos_metode'] ?? '') ?: null;
    $kompos_kapasitas        = trim($_POST['kompos_kapasitas'] ?? ''); $kompos_kapasitas = $kompos_kapasitas === '' ? null : (float)$kompos_kapasitas;
    $kompos_luas_area        = trim($_POST['kompos_luas_area'] ?? ''); $kompos_luas_area = $kompos_luas_area === '' ? null : (float)$kompos_luas_area;
    $kompos_tahun_mulai      = trim($_POST['kompos_tahun_mulai'] ?? ''); $kompos_tahun_mulai = $kompos_tahun_mulai === '' ? null : (int)$kompos_tahun_mulai;
    $kompos_jumlah_komposter = trim($_POST['kompos_jumlah_komposter'] ?? ''); $kompos_jumlah_komposter = $kompos_jumlah_komposter === '' ? null : (int)$kompos_jumlah_komposter;
    $kompos_kelompok         = trim($_POST['kompos_kelompok'] ?? '') ?: null;
    
    $kompos_bahan_arr = $_POST['kompos_bahan_organik_arr'] ?? [];
    if (!is_array($kompos_bahan_arr)) $kompos_bahan_arr = [];
    $kompos_bahan_organik_json = !empty($kompos_bahan_arr) ? json_encode(array_values($kompos_bahan_arr)) : null;
    
    // ===== PIC MONEV =====
    $pic_monev_nama  = trim($_POST['pic_monev_nama'] ?? '') ?: null;
    $pic_monev_peran = trim($_POST['pic_monev_peran'] ?? '') ?: null;
    
    // ===== FOTO (opsional) =====
    $nama_foto = $r['foto_pertama'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        if ($file['size'] <= MAX_FILE_SIZE) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
                if ($r['foto_pertama']) {
                    @unlink(UPLOAD_DIR . $r['foto_pertama']);
                    @unlink(UPLOAD_DIR . 'thumb_' . $r['foto_pertama']);
                }
                if ($gd_aktif) {
                    $img = null;
                    switch ($mime) {
                        case 'image/jpeg': $img = @imagecreatefromjpeg($file['tmp_name']); break;
                        case 'image/png':  $img = @imagecreatefrompng($file['tmp_name']); break;
                        case 'image/webp': $img = @imagecreatefromwebp($file['tmp_name']); break;
                    }
                    if ($img) {
                        $w = imagesx($img); $h = imagesy($img);
                        $scale = min(MAX_DIMENSION / $w, MAX_DIMENSION / $h, 1);
                        $new_w = (int)($w * $scale); $new_h = (int)($h * $scale);
                        $new_img = imagecreatetruecolor($new_w, $new_h);
                        if ($mime === 'image/png' || $mime === 'image/webp') {
                            imagealphablending($new_img, false); imagesavealpha($new_img, true);
                        }
                        imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                        $nama_foto = 'objek_' . $id . '_' . date('Ymd_His') . '.jpg';
                        imagejpeg($new_img, UPLOAD_DIR . $nama_foto, JPEG_QUALITY);
                        imagedestroy($img); imagedestroy($new_img);
                    }
                } else {
                    $nama_foto = 'objek_' . $id . '_' . date('Ymd_His') . '.jpg';
                    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_foto);
                }
            }
        }
    }
    
    // ===== UPDATE DB (ANTI-MISMATCH) =====
    $sql_update = "UPDATE objek SET 
        nama_objek=?, lokasi=?, koordinat=?, catatan=?, status=?, visibility=?, foto_pertama=?,
        nama_latin=?, famili=?, asal_daerah=?, nama_daerah_lain=?, status_pohon=?, kategori_ias=?,
        potensi_pemanfaatan=?, karakteristik=?, manfaat=?,
        tanggal_tanam=?, alasan_penanaman=?, penyelenggara=?, pelaksana=?,
        sumber_dana=?, jenis_bibit=?, asal_bibit=?,
        pic_monev_nama=?, pic_monev_peran=?,
        nama_lain_sungai=?, sumber_mata_air=?, panjang_sungai=?, lebar_rata_rata=?, kedalaman_rata_rata=?,
        daerah_hulu=?, daerah_hilir=?, muara=?, jenis_sungai=?, dasar_sungai=?, ada_vegetasi=?, ada_sempadan=?, pemanfaatan_sungai=?,
        taman_pengelola=?, taman_luas_area=?, taman_tahun_pembangunan=?, taman_tahun_revitalisasi=?,
        taman_status_kepemilikan=?, taman_vegetasi_pohon=?, taman_vegetasi_tanaman=?,
        taman_kategori_tanaman=?, taman_pemanfaatan=?, taman_intensitas=?, taman_fasilitas=?,
        kompos_pengelola=?, kompos_metode=?, kompos_kapasitas=?, kompos_luas_area=?,
        kompos_tahun_mulai=?, kompos_jumlah_komposter=?, kompos_kelompok=?, kompos_bahan_organik=?
        WHERE id=?";
    
    $stmt = mysqli_prepare($conn, $sql_update);
    if (!$stmt) {
        notif('gagal', 'Gagal prepare: ' . mysqli_error($conn));
        redirect('index.php?page=objek_edit&id=' . $id);
    }
    
    $bind_values = [
        $nama_objek, $lokasi, $koordinat, $catatan, $status, $visibility, $nama_foto,
        $nama_latin, $famili, $asal_daerah, $nama_daerah_lain, $status_pohon, $kategori_ias,
        $potensi_pemanfaatan, $karakteristik_json, $manfaat_json,
        $tanggal_tanam, $alasan_penanaman, $penyelenggara, $pelaksana,
        $sumber_dana, $jenis_bibit, $asal_bibit,
        $pic_monev_nama, $pic_monev_peran,
        $nama_lain_sungai, $sumber_mata_air, $panjang_sungai, $lebar_rata_rata, $kedalaman_rata_rata,
        $daerah_hulu, $daerah_hilir, $muara, $jenis_sungai, $dasar_sungai_json, $ada_vegetasi, $ada_sempadan, $pemanfaatan_sungai_json,
        $taman_pengelola, $taman_luas_area, $taman_tahun_pembangunan, $taman_tahun_revitalisasi,
        $taman_status_kepemilikan, $taman_vegetasi_pohon, $taman_vegetasi_tanaman,
        $taman_kategori_json, $taman_pemanfaatan_json, $taman_intensitas, $taman_fasilitas_json,
        $kompos_pengelola, $kompos_metode, $kompos_kapasitas, $kompos_luas_area,
        $kompos_tahun_mulai, $kompos_jumlah_komposter, $kompos_kelompok, $kompos_bahan_organik_json,
        $id
    ];
    $type_string = '';
    foreach ($bind_values as $bv) {
        $type_string .= is_int($bv) ? 'i' : (is_float($bv) ? 'd' : 's');
    }
    mysqli_stmt_bind_param($stmt, $type_string, ...$bind_values);
    
    if (mysqli_stmt_execute($stmt)) {
        audit_log($conn, 'edit_objek', $id, "Nama: $nama_objek");
        notif('sukses', 'Objek berhasil diperbarui.');
    } else {
        notif('gagal', 'Gagal memperbarui objek: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    
    redirect('index.php?page=objek_profil&id=' . $id);
}
// ============ END PROSES EDIT OBJEK ============


// ============ PROSES HAPUS OBJEK (SOFT DELETE) ============
if (isset($_GET['hapus_objek'])) {
    if (!isset($_SESSION['user_id'])) { 
        notif('gagal', 'Silakan login dulu.'); 
        redirect('index.php?page=login'); 
    }
    
    $uid = (int)$_SESSION['user_id'];
    $id = (int)$_GET['hapus_objek'];
    
    if ($id <= 0) { 
        notif('gagal', 'ID objek tidak valid.'); 
        redirect('index.php?page=objek'); 
    }
    
    // Cek objek ada & hak akses
    $stmt = mysqli_prepare($conn, "SELECT user_id, nama_objek, kode_objek FROM objek WHERE id = ? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$r) { 
        notif('gagal', 'Objek tidak ditemukan.'); 
        redirect('index.php?page=objek'); 
    }
    
    if ($r['user_id'] != $uid && !is_admin()) { 
        notif('gagal', 'Tidak berhak menghapus objek ini.'); 
        redirect('index.php?page=objek'); 
    }
    
    // Soft delete
    $stmt = mysqli_prepare($conn, "UPDATE objek SET deleted_at = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        audit_log($conn, 'hapus_objek', $id, "Kode: " . $r['kode_objek'] . " | Nama: " . $r['nama_objek']);
        notif('sukses', 'Objek "' . $r['nama_objek'] . '" berhasil dihapus.');
    } else {
        notif('gagal', 'Gagal menghapus objek.');
    }
    mysqli_stmt_close($stmt);
    redirect('index.php?page=objek');
}

if (isset($_POST['aksi_update'])) {
    csrf_check();
    if (!isset($_SESSION['user_id'])) redirect('index.php?page=login');
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { notif('gagal','ID tidak valid'); redirect('index.php?page=arsip'); }
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM laporan_aksi WHERE id=? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt, 'i', $id); mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
    if (!$r) { notif('gagal','Data tidak ditemukan'); redirect('index.php?page=arsip'); }
    if ($r['user_id'] != $_SESSION['user_id'] && !is_admin()) { notif('gagal','Tidak berhak.'); redirect('index.php?page=arsip'); }
    $nama_objek = trim($_POST['nama_objek'] ?? ''); $koordinat = trim($_POST['koordinat'] ?? '');
    $status_kondisi = trim($_POST['status_kondisi'] ?? ''); $catatan = trim($_POST['catatan'] ?? '');
    $jenis_objek = $_POST['jenis_objek'] ?? '';
    if (!in_array($jenis_objek, ['sungai','pohon','taman','komposting'], true)) { notif('gagal','Jenis tidak valid.'); redirect('index.php?page=arsip'); }
    $umur_bulan = 0; $serapan_karbon = 0.00;
    if ($jenis_objek === 'pohon') { $umur_bulan = max(0, min(1200, (int)($_POST['umur_bulan'] ?? 0))); if ($umur_bulan > 0) $serapan_karbon = $umur_bulan * 2.5; }
    $stmt = mysqli_prepare($conn, "UPDATE laporan_aksi SET jenis_objek=?, nama_objek=?, koordinat=?, status_kondisi=?, catatan=?, umur_bulan=?, serapan_karbon=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'sssssidi', $jenis_objek, $nama_objek, $koordinat, $status_kondisi, $catatan, $umur_bulan, $serapan_karbon, $id);
    mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    audit_log($conn, 'edit_laporan', $id, "Objek: $nama_objek");
    notif('sukses', 'Laporan diperbarui.'); redirect('index.php?page=arsip');
}

if (isset($_POST['aksi_hapus'])) {
    csrf_check();
    if (!isset($_SESSION['user_id'])) { 
        notif('gagal', 'Silakan login.'); 
        redirect('index.php?page=login'); 
    }
    
    $id  = (int)($_POST['id'] ?? 0);
    $uid = (int)$_SESSION['user_id'];
    
    if ($id <= 0) { 
        notif('gagal', 'ID tidak valid.'); 
        redirect('index.php?page=arsip'); 
    }
    
    // Ambil data laporan untuk cek kepemilikan
    $stmt = mysqli_prepare($conn, "SELECT user_id, nama_objek FROM laporan_aksi WHERE id=? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$r) { 
        notif('gagal', 'Data tidak ditemukan.'); 
        redirect('index.php?page=arsip'); 
    }
    
    // Cek hak: harus owner atau admin
    if ($r['user_id'] != $uid && !is_admin()) {
        notif('gagal', 'Anda tidak berhak menghapus laporan milik orang lain.');
        redirect('index.php?page=arsip');
    }
    
    // Soft delete
    $stmt = mysqli_prepare($conn, "UPDATE laporan_aksi SET deleted_at=NOW() WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    audit_log($conn, 'hapus_laporan', $id, 'Soft delete: ' . $r['nama_objek']);
    notif('sukses', 'Laporan dipindah ke Recycle Bin.');
    redirect('index.php?page=arsip');
}

if (isset($_GET['restore']) && is_super_admin()) {
    $id = (int)$_GET['restore'];
    $stmt = mysqli_prepare($conn, "UPDATE laporan_aksi SET deleted_at=NULL WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    audit_log($conn, 'restore_laporan', $id, 'Restore');
    notif('sukses', 'Laporan dipulihkan.'); redirect('index.php?page=recycle');
}

if (isset($_GET['hapus_permanen']) && is_super_admin()) {
    $id = (int)$_GET['hapus_permanen'];
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "SELECT foto FROM laporan_aksi WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id); mysqli_stmt_execute($stmt);
        $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
        if ($r && $r['foto']) { @unlink(UPLOAD_DIR . $r['foto']); @unlink(UPLOAD_DIR . 'thumb_' . $r['foto']); }
        $del = mysqli_prepare($conn, "DELETE FROM laporan_aksi WHERE id=?");
        mysqli_stmt_bind_param($del, 'i', $id); mysqli_stmt_execute($del); mysqli_stmt_close($del);
        audit_log($conn, 'hapus_permanen', $id, 'Hard delete');
        notif('sukses', 'Laporan dihapus permanen.');
    }
    redirect('index.php?page=recycle');
}

if (isset($_POST['aksi_ubah_role']) && is_super_admin()) {
    csrf_check();
    $uid = (int)$_POST['user_id']; $role = $_POST['role'] ?? '';
    if (in_array($role, ['user','admin','super_admin'], true) && $uid != $_SESSION['user_id']) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET role=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'si', $role, $uid); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        audit_log($conn, 'ubah_role', $uid, "Role jadi: $role");
        notif('sukses', 'Role diubah.');
    }
    redirect('index.php?page=kelola_user');
}

if (isset($_GET['toggle_aktif']) && is_super_admin()) {
    $uid = (int)$_GET['toggle_aktif'];
    if ($uid != $_SESSION['user_id']) {
        mysqli_query($conn, "UPDATE users SET aktif = 1 - aktif WHERE id=$uid");
        audit_log($conn, 'toggle_aktif_user', $uid, 'Toggle status');
        notif('sukses', 'Status user diubah.');
    }
    redirect('index.php?page=kelola_user');
}

// ============ GENERATE RESET PASSWORD LINK ============
if (isset($_POST['aksi_generate_reset']) && is_super_admin()) {
    csrf_check();
    
    $target_id = (int)($_POST['user_id'] ?? 0);
    
    // Validasi ID
    if ($target_id <= 0) {
        notif('gagal', 'ID user tidak valid.');
        redirect('index.php?page=kelola_user');
    }
    
    // Tidak bisa reset akun sendiri
    if ($target_id == $_SESSION['user_id']) {
        notif('gagal', 'Tidak dapat reset password akun sendiri. Gunakan menu profil jika perlu.');
        redirect('index.php?page=kelola_user');
    }
    
    // Ambil data target
    $stmt = mysqli_prepare($conn, "SELECT nama_lengkap, email, role FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $target_id);
    mysqli_stmt_execute($stmt);
    $target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$target) {
        notif('gagal', 'User tidak ditemukan atau sudah dihapus.');
        redirect('index.php?page=kelola_user');
    }
    
    // Generate token unik (32 karakter hex)
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 jam
    
    // Simpan ke database
    $stmt = mysqli_prepare($conn, "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $token, $expires, $target_id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        
        // Build link reset
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $reset_link = $base_url . '/index.php?page=reset_password&token=' . $token;
        
        // Simpan di session untuk ditampilkan (bukan di URL, demi keamanan)
        $_SESSION['reset_link_tmp'] = $reset_link;
        $_SESSION['reset_link_nama'] = $target['nama_lengkap'];
        $_SESSION['reset_link_expires'] = $expires;
        
        audit_log($conn, 'generate_reset', $target_id, "Generate reset password untuk: {$target['nama_lengkap']} ({$target['email']})");
        notif('sukses', "Link reset untuk '{$target['nama_lengkap']}' berhasil dibuat. Lihat di bawah tombol Reset.");
    } else {
        mysqli_stmt_close($stmt);
        notif('gagal', 'Gagal membuat token reset: ' . mysqli_error($conn));
    }
    
    redirect('index.php?page=kelola_user');
}
// ============ END GENERATE RESET PASSWORD ============

// ============ SIMPAN PASSWORD BARU ============
if (isset($_POST['aksi_simpan_password'])) {
    csrf_check();
    
    $token      = trim($_POST['token'] ?? '');
    $password   = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['password_konfirmasi'] ?? '';
    
    // Validasi 1: Token format
    if (empty($token) || strlen($token) !== 32) {
        notif('gagal', 'Token tidak valid.');
        redirect('index.php?page=login');
    }
    
    // Validasi 2: Password minimal 6 karakter
    if (strlen($password) < 6) {
        notif('gagal', 'Password minimal 6 karakter.');
        redirect('index.php?page=reset_password&token=' . urlencode($token));
    }
    
    // Validasi 3: Konfirmasi harus sama
    if ($password !== $konfirmasi) {
        notif('gagal', 'Konfirmasi password tidak sama.');
        redirect('index.php?page=reset_password&token=' . urlencode($token));
    }
    
    // Cek token di DB
    $stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, email, reset_expires FROM users WHERE reset_token = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$user) {
        notif('gagal', 'Link tidak valid atau sudah pernah dipakai.');
        redirect('index.php?page=login');
    }
    
    // Cek expired
    if (strtotime($user['reset_expires']) < time()) {
        notif('gagal', 'Link sudah kadaluarsa. Minta admin buat link baru.');
        redirect('index.php?page=login');
    }
    
    // Hash password baru
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password + HAPUS token (sekali pakai)
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $user['id']);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        audit_log($conn, 'reset_password_sukses', $user['id'], "Reset password untuk: {$user['nama_lengkap']} ({$user['email']})");
        notif('sukses', 'Password berhasil diubah! Silakan login dengan password baru.');
        redirect('index.php?page=login');
    } else {
        mysqli_stmt_close($stmt);
        notif('gagal', 'Gagal menyimpan password: ' . mysqli_error($conn));
        redirect('index.php?page=reset_password&token=' . urlencode($token));
    }
}
// ============ END SIMPAN PASSWORD BARU ============

// ============ HAPUS USER (SOFT DELETE) ============
if (isset($_POST['aksi_hapus_user']) && is_super_admin()) {
    csrf_check();
    
    $target_id  = (int)($_POST['user_id'] ?? 0);
    $konfirmasi = trim($_POST['konfirmasi'] ?? '');
    
    // Validasi 1: ID harus valid
    if ($target_id <= 0) {
        notif('gagal', 'ID user tidak valid.');
        redirect('index.php?page=kelola_user');
    }
    
    // Validasi 2: Tidak bisa hapus diri sendiri
    if ($target_id == $_SESSION['user_id']) {
        notif('gagal', 'Tidak dapat menghapus akun Anda sendiri.');
        redirect('index.php?page=kelola_user');
    }
    
    // Ambil data target dari DB
    $stmt = mysqli_prepare($conn, "SELECT nama_lengkap, email, role FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $target_id);
    mysqli_stmt_execute($stmt);
    $target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    // Validasi 3: User harus ada
    if (!$target) {
        notif('gagal', 'User tidak ditemukan atau sudah dihapus.');
        redirect('index.php?page=kelola_user');
    }
    
    // Validasi 4: Tidak bisa hapus sesama super_admin
    if ($target['role'] === 'super_admin') {
        notif('gagal', 'Super Admin tidak dapat dihapus. Turunkan role-nya terlebih dahulu.');
        redirect('index.php?page=kelola_user');
    }
    
    // Validasi 5: Konfirmasi nama harus persis sama (case-insensitive)
    if (strcasecmp($konfirmasi, $target['nama_lengkap']) !== 0) {
        notif('gagal', 'Konfirmasi nama tidak sesuai. Penghapusan dibatalkan.');
        redirect('index.php?page=kelola_user');
    }
    
    // ✅ Semua validasi lolos → soft delete
    $stmt = mysqli_prepare($conn, "UPDATE users SET deleted_at = NOW(), aktif = 0 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $target_id);
    
    if (mysqli_stmt_execute($stmt)) {
        audit_log($conn, 'hapus_user', $target_id, 
            "Hapus user: {$target['nama_lengkap']} ({$target['email']}) - Role: {$target['role']}");
        notif('sukses', "User '{$target['nama_lengkap']}' berhasil dihapus.");
    } else {
        notif('gagal', 'Gagal menghapus user: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    
    redirect('index.php?page=kelola_user');
}
// ============ END HAPUS USER ============

if (isset($_GET['tandai_dibaca']) && is_super_admin()) {
    $id = (int)$_GET['tandai_dibaca'];
    mysqli_query($conn, "UPDATE pesan_kontak SET sudah_dibaca=1 WHERE id=$id");
    redirect('index.php?page=pesan');
}

// ============ EXPORT CSV (dengan filter role) ============
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $where = ["deleted_at IS NULL"];
    $params = []; $types = '';
    if (!is_admin()) {
        $where[] = "user_id = ?";
        $params[] = (int)$_SESSION['user_id'];
        $types .= 'i';
    }
    
    $sql = "SELECT * FROM laporan_aksi WHERE " . implode(' AND ', $where) . " ORDER BY tanggal_buat DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sweaping_laporan_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['Tanggal','Jenis','Nama Objek','Koordinat','Visibility','Status','Kontributor','Peran','Umur (bln)','Serapan CO2 (kg)','Catatan']);
    while ($r = mysqli_fetch_assoc($res)) {
        fputcsv($out, [
            $r['tanggal_buat'], $r['jenis_objek'], $r['nama_objek'], $r['koordinat'],
            $r['visibility'] ?? 'publik', $r['status_kondisi'],
            $r['nama_kontributor'], $r['peran_kontributor'],
            $r['umur_bulan'], $r['serapan_karbon'], $r['catatan']
        ]);
    }
    fclose($out);
    exit;
}

// ============ EXPORT XLSX (dengan filter role) ============
if (isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    $where = ["deleted_at IS NULL"];
    $params = []; $types = '';
    if (!is_admin()) {
        $where[] = "user_id = ?";
        $params[] = (int)$_SESSION['user_id'];
        $types .= 'i';
    }
    
    $sql = "SELECT * FROM laporan_aksi WHERE " . implode(' AND ', $where) . " ORDER BY tanggal_buat DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    $data = [];
    $data[] = ['Tanggal','Jenis','Nama Objek','Koordinat','Visibility','Status','Kontributor','Peran','Umur (bln)','Serapan CO2 (kg)','Catatan'];
    while ($r = mysqli_fetch_assoc($res)) {
        $data[] = [
            $r['tanggal_buat'], $r['jenis_objek'], $r['nama_objek'], $r['koordinat'],
            $r['visibility'] ?? 'publik', $r['status_kondisi'],
            $r['nama_kontributor'], $r['peran_kontributor'],
            $r['umur_bulan'], $r['serapan_karbon'], $r['catatan']
        ];
    }
    mysqli_stmt_close($stmt);
    
    $filename = 'sweaping_laporan_' . date('Ymd_His') . '.xlsx';
    if (export_xlsx($data, $filename)) {
        exit;
    } else {
        notif('gagal', 'Gagal generate XLSX. Coba lagi nanti.');
        redirect('index.php?page=arsip');
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($seo['title']) ?></title>
    <meta name="description" content="<?= e($seo['desc']) ?>">
    <?php if (empty($seo['index'])): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="robots" content="index, follow, max-image-preview:large">
    <?php endif; ?>
    <link rel="canonical" href="<?= e($canonical_url) ?>">

    <!-- Open Graph / WhatsApp Preview -->
    <meta property="og:type" content="<?= e($seo['type']) ?>">
    <meta property="og:url" content="<?= e($canonical_url) ?>">
    <meta property="og:title" content="<?= e($seo['title']) ?>">
    <meta property="og:description" content="<?= e($seo['desc']) ?>">
    <meta property="og:image" content="<?= e($seo['image']) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="SWEAPING">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seo['title']) ?>">
    <meta name="twitter:description" content="<?= e($seo['desc']) ?>">
    <meta name="twitter:image" content="<?= e($seo['image']) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SWEAPING">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1a5c2e">
    <meta name="format-detection" content="telephone=no">
    
<link rel="manifest" href="manifest.json?v=2">
    <link rel="icon" href="assets/logo-bulat.png" type="image/png">
    <link rel="apple-touch-icon" href="assets/logo-bulat.png">
    
    <!-- Structured Data: Organization + WebSite -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "<?= e($base_url) ?>/#organization",
          "name": "SWEAPING",
          "alternateName": "Swadaya, Edukasi, dan Aksi Peduli Lingkungan",
          "url": "<?= e($base_url) ?>/",
          "logo": "<?= e($base_img) ?>",
          "description": "Platform konservasi & akuntansi karbon komunitas yang dikembangkan oleh LSM KOMPAS.",
          "parentOrganization": {
            "@type": "NGO",
            "name": "LSM KOMPAS",
            "alternateName": "Koordinat Masyarakat Pejuang Aspirasi",
            "url": "https://lsmkompas.id"
          }
        },
        {
          "@type": "WebSite",
          "@id": "<?= e($base_url) ?>/#website",
          "url": "<?= e($base_url) ?>/",
          "name": "SWEAPING",
          "description": "Platform pelaporan aksi lingkungan dan akuntansi karbon.",
          "publisher": { "@id": "<?= e($base_url) ?>/#organization" },
          "inLanguage": "id-ID"
        }
      ]
    }
    </script>

    <?php if ($halaman === 'tentang'): ?>
    <!-- Structured Data: AboutPage -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AboutPage",
      "name": "Tentang SWEAPING",
      "description": "<?= e($seo['desc']) ?>",
      "url": "<?= e($canonical_url) ?>",
      "isPartOf": { "@id": "<?= e($base_url) ?>/#website" },
      "about": { "@id": "<?= e($base_url) ?>/#organization" },
      "inLanguage": "id-ID"
    }
    </script>
    <?php endif; ?>
    
    <!-- Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            screens: {
                'sm': '640px',
                'md': '768px',
                'lg': '1024px',
                'xl': '1280px',
            },
            extend: {
                fontSize: {
                    'xs':   ['0.8125rem', { lineHeight: '1.5' }],    // 13px
                    'sm':   ['0.9375rem', { lineHeight: '1.6' }],    // 15px
                    'base': ['1rem',      { lineHeight: '1.6' }],    // 16px
                    'lg':   ['1.125rem',  { lineHeight: '1.6' }],    // 18px
                    'xl':   ['1.25rem',   { lineHeight: '1.5' }],    // 20px
                },
                colors: {
                    emerald: {
                        50:'#f0f9f0',100:'#dcf0dd',200:'#bbd8bd',300:'#8fbf93',
                        400:'#5fa465',500:'#3d8b45',600:'#2d7a36',700:'#1a5c2e',
                        800:'#134a24',900:'#0d3519',950:'#061a0c',
                    },
                    amber: {
                        50:'#fff8e6',100:'#ffedb8',200:'#ffdb7a',300:'#ffc53d',
                        400:'#fab005',500:'#f9a825',600:'#e08b00',700:'#b86900',
                        800:'#8f4f00',900:'#663800',
                    }
                },
                fontFamily: { 
                    sans: ['-apple-system', 'BlinkMacSystemFont', '"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] 
                }
            }
        }
    }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        @media (max-width: 768px) { input, select, textarea { font-size: 16px !important; } }
        
        .hero-bg {
            background-image: 
                linear-gradient(to bottom, rgba(13,53,25,0) 0%, rgba(13,53,25,0.35) 70%, rgba(13,53,25,0.75) 100%),
                url('assets/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        @media (max-width: 768px) { .hero-bg { background-attachment: scroll; } }
        
        .glass { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .glass-dark { background: rgba(13,53,25,0.6); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        
        .card-hover { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(26,92,46,0.25); }
        
        #toTop { opacity:0; pointer-events:none; transition: all 0.3s; transform: translateY(20px); }
        #toTop.show { opacity:1; pointer-events:auto; transform: translateY(0); }
        
        #notif-bar { transform: translateY(-100%); transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1); }
        #notif-bar.show { transform: translateY(0); }
        
        .modal-backdrop { opacity:0; pointer-events:none; transition: opacity 0.25s; }
        .modal-backdrop.show { opacity:1; pointer-events:auto; }
        .modal-content { transform: scale(0.9) translateY(20px); transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1); }
        .modal-backdrop.show .modal-content { transform: scale(1) translateY(0); }
        
        .fade-up { animation: fadeUp 0.8s ease-out forwards; opacity: 0; }
        .fade-up-delay-1 { animation-delay: 0.15s; }
        .fade-up-delay-2 { animation-delay: 0.3s; }
        .fade-up-delay-3 { animation-delay: 0.45s; }
        
        .feature-icon { 
            width: 56px; height: 56px; 
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px;
        }
        .feature-icon svg { width: 28px; height: 28px; stroke-width: 2; }
        
        .nav-link { position: relative; }
        .nav-link::after {
            content: ''; position: absolute; bottom: -4px; left: 0; right: 0;
            height: 2px; background: #1a5c2e; transform: scaleX(0); transform-origin: right;
            transition: transform 0.3s;
        }
        /* ============ WHATSAPP FLOATING BUTTON ============ */
@keyframes wa-pulse {
    0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
    70% { box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
    100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
}
.wa-float-btn { animation: wa-pulse 2s infinite; }

/* Mobile: sesuaikan posisi */
@media (max-width: 768px) {
    #toTop { bottom: 5.5rem !important; }
}
        /* ============ CTA ANIMATIONS ============ */
@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.15; }
    50% { transform: translateY(-30px) rotate(8deg); opacity: 0.25; }
}
@keyframes float-delay {
    0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.12; }
    50% { transform: translateY(25px) rotate(-6deg); opacity: 0.22; }
}
@keyframes glow-pulse {
    0%, 100% { box-shadow: 0 10px 30px -5px rgba(251, 191, 36, 0.4), 0 0 0 0 rgba(251, 191, 36, 0.5); }
    50% { box-shadow: 0 15px 40px -5px rgba(251, 191, 36, 0.6), 0 0 0 12px rgba(251, 191, 36, 0); }
}
@keyframes shine {
    0% { left: -100%; }
    100% { left: 200%; }
}

.cta-icon-float { animation: float 6s ease-in-out infinite; }
.cta-icon-float-delay { animation: float-delay 7s ease-in-out infinite; }
.cta-icon-float-slow { animation: float 9s ease-in-out infinite; }

.cta-btn-glow {
    animation: glow-pulse 2.5s ease-in-out infinite;
    position: relative;
    overflow: hidden;
}
.cta-btn-glow::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shine 3s ease-in-out infinite;
}
.cta-btn-glow:hover {
    transform: scale(1.05) translateY(-2px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
       /* ============ MOBILE OPTIMIZATION - FINAL v3 ============ */
@media screen and (max-width: 768px) {
    /* Base font size */
    html { 
        font-size: 17px !important; 
        -webkit-text-size-adjust: 100%;
    }
    body { 
        line-height: 1.65 !important; 
    }
    
    /* Semua ukuran teks Tailwind */
    .text-\[10px\] { font-size: 14px !important; line-height: 1.4 !important; }
    .text-\[11px\] { font-size: 14px !important; line-height: 1.4 !important; }
    .text-xs { font-size: 14px !important; line-height: 1.5 !important; }
    .text-sm { font-size: 16px !important; line-height: 1.6 !important; }
    .text-base { font-size: 17px !important; line-height: 1.6 !important; }
    .text-lg { font-size: 19px !important; }
    .text-xl { font-size: 22px !important; }
    .text-2xl { font-size: 26px !important; }
    .text-3xl { font-size: 30px !important; }
    .text-4xl { font-size: 34px !important; }
    
    /* Judul */
    h1 { font-size: 28px !important; line-height: 1.2 !important; }
    h2 { font-size: 24px !important; line-height: 1.3 !important; }
    h3 { font-size: 20px !important; line-height: 1.4 !important; }
    h4 { font-size: 18px !important; }
    
    /* Paragraf & elemen umum (fallback) */
    p, li { font-size: 16px !important; }
    
    /* Angka besar (statistik) */
    .font-black, .font-extrabold {
        line-height: 1.15 !important;
    }
    
    /* Kartu statistik - label uppercase */
    .uppercase {
        letter-spacing: 0.4px !important;
    }
    
    /* Form input */
    input, select, textarea {
        font-size: 16px !important;
        min-height: 48px !important;
        padding: 12px 16px !important;
    }
    
    /* Tombol */
    button, .btn, a[class*="rounded-full"] {
        min-height: 48px !important;
    }
    
    /* Navbar */
    header > div > div {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }
    
    /* Ikon Lucide */
    .lucide, svg[class*="lucide"] {
        width: 22px !important;
        height: 22px !important;
    }
    header .lucide {
        width: 24px !important;
        height: 24px !important;
    }
    
    /* Section padding */
    section {
        padding-top: 2.5rem !important;
        padding-bottom: 2.5rem !important;
    }
    
    /* Card arsip mobile - pastikan teks terbaca */
    .text-\[11px\] {
        font-size: 14px !important;
    }
}

/* HP sangat kecil (lebar < 360px) */
@media screen and (max-width: 360px) {
    html { font-size: 16px !important; }
    h1 { font-size: 24px !important; }
    h2 { font-size: 20px !important; }
    .text-xs { font-size: 13px !important; }
}
@media screen and (max-width: 360px) {
    #stats > .grid {
        grid-template-columns: 1fr !important;
    }
}
/* ============ ONBOARDING TOUR ============ */
#tour-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.65);
    z-index: 9998; opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}
#tour-overlay.active { opacity: 1; pointer-events: auto; }

#tour-spotlight {
    position: fixed;
    border-radius: 12px;
    box-shadow: 0 0 0 9999px rgba(0,0,0,0.65), 0 0 0 3px #1a5c2e, 0 0 30px rgba(26,92,46,0.5);
    z-index: 9999; pointer-events: none;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
}
#tour-spotlight.active { opacity: 1; }

#tour-tooltip {
    position: fixed;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3), 0 0 0 1px rgba(26,92,46,0.12);
    z-index: 10000;
    max-width: 340px;
    width: calc(100vw - 32px);
    padding: 20px;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.3s, transform 0.3s;
    pointer-events: none;
}
#tour-tooltip.active { opacity: 1; transform: translateY(0); pointer-events: auto; }

#tour-tooltip .tour-arrow {
    position: absolute;
    width: 12px; height: 12px;
    background: #fff;
    transform: rotate(45deg);
    box-shadow: -1px -1px 2px rgba(0,0,0,0.05);
}
#tour-tooltip[data-pos="bottom"] .tour-arrow { top: -6px; }
#tour-tooltip[data-pos="top"] .tour-arrow { bottom: -6px; box-shadow: 1px 1px 2px rgba(0,0,0,0.05); }
#tour-tooltip[data-pos="center"] .tour-arrow { display: none; }

#tour-tooltip h4 {
    font-size: 15px; font-weight: 800;
    color: #0d3519; margin: 0 0 6px 0;
    padding-right: 26px;
}
#tour-tooltip p {
    font-size: 13px; color: #475569;
    margin: 0 0 16px 0; line-height: 1.55;
}

.tour-progress {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 14px; padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.tour-progress-bar {
    flex: 1; height: 4px; background: #f1f5f9;
    border-radius: 999px; overflow: hidden;
    margin-right: 10px;
}
.tour-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #1a5c2e, #2d7a36);
    border-radius: 999px; transition: width 0.35s ease; width: 0%;
}
.tour-step-counter {
    font-size: 11px; font-weight: 700;
    color: #1a5c2e; white-space: nowrap;
}

.tour-buttons { display: flex; gap: 8px; align-items: center; }
.tour-btn {
    padding: 8px 14px; border-radius: 999px;
    font-size: 12px; font-weight: 700;
    cursor: pointer; border: none;
    transition: all 0.2s; font-family: inherit;
}
.tour-btn-skip { background: transparent; color: #94a3b8; margin-right: auto; }
.tour-btn-skip:hover { color: #64748b; background: #f8fafc; }
.tour-btn-back {
    background: #fff; color: #1a5c2e;
    border: 2px solid #1a5c2e; padding: 6px 12px;
}
.tour-btn-back:hover { background: #f0f9f0; }
.tour-btn-next { background: #1a5c2e; color: #fff; padding: 8px 18px; }
.tour-btn-next:hover { background: #134a24; }

#tour-close {
    position: absolute; top: 12px; right: 12px;
    width: 26px; height: 26px; border-radius: 50%;
    background: #f1f5f9; border: none;
    cursor: pointer; display: flex;
    align-items: center; justify-content: center;
    color: #64748b; transition: all 0.2s; padding: 0;
}
#tour-close:hover { background: #e2e8f0; color: #0f172a; }

#tour-help-btn {
    position: fixed; bottom: 1.5rem; left: 1rem;
    z-index: 40; width: 44px; height: 44px;
    border-radius: 50%; background: #1a5c2e;
    color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 20px rgba(26,92,46,0.35);
    transition: all 0.25s; opacity: 0; pointer-events: none;
}
#tour-help-btn.show { opacity: 1; pointer-events: auto; }
#tour-help-btn:hover { transform: scale(1.08); background: #134a24; }

@media (max-width: 640px) {
    #tour-tooltip { max-width: calc(100vw - 24px); padding: 16px; }
    #tour-tooltip h4 { font-size: 14px; }
    #tour-tooltip p { font-size: 12.5px; }
    #tour-help-btn { bottom: 5rem; left: 0.5rem; width: 40px; height: 40px; }
}
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

<?php if(!$gd_aktif && APP_DEBUG): ?>
    <div class="bg-amber-500 text-slate-900 text-center py-2 text-xs font-bold">⚠️ Ekstensi GD tidak aktif</div>
<?php endif; ?>

<?php if($notif_pesan): 
    $warna = $notif_pesan['tipe'] === 'sukses' ? 'bg-emerald-600' : 'bg-rose-600';
    $ikon = $notif_pesan['tipe'] === 'sukses' ? 'check-circle' : 'alert-circle';
?>
    <div id="notif-bar" class="fixed top-0 left-0 right-0 <?= $warna ?> text-white py-3 px-4 text-sm font-bold z-[2000] shadow-lg">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
            <i data-lucide="<?= $ikon ?>" class="w-3.5 h-3.5"></i>
            <span><?= e($notif_pesan['pesan']) ?></span>
        </div>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const n = document.getElementById('notif-bar');
            if (n) { n.classList.add('show'); setTimeout(() => n.classList.remove('show'), 4000); }
        });
    </script>
<?php endif; ?>

<!-- ============ HEADER ============ -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="main-header">
    <div class="glass border-b border-emerald-100/50">
        <div class="max-w-7xl mx-auto px-4 py-2 flex justify-between items-center">
                            <form action="index.php" method="GET" class="flex items-center">
                    <input type="hidden" name="page" value="cari">
                    <div class="relative">
                        <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" 
                            placeholder="Cari objek/user..." 
                            class="w-40 xl:w-56 pl-8 pr-3 py-1.5 text-xs border-2 border-slate-200 rounded-full bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                    </div>
                </form>
            <a href="index.php?page=beranda" class="flex items-center gap-3">
                <img src="assets/logo-horizontal.png" alt="SWEAPING" class="h-16 md:h-28 w-auto bg-transparent mix-blend-multiply"
                     onerror="this.outerHTML='<span class=\'text-xl md:text-2xl font-extrabold text-emerald-800 tracking-tight\'>SWEAPING</span>'">
            </a>
            
            <nav class="hidden lg:flex items-center gap-7 font-medium text-sm text-slate-700">
                <a href="index.php?page=beranda" class="nav-link <?= $halaman=='beranda'?'active text-emerald-700':'' ?>">Beranda</a>
                <a href="index.php?page=arsip" class="nav-link <?= $halaman=='arsip'?'active text-emerald-700':'' ?>">Arsip</a>
                <a href="index.php?page=objek" class="nav-link <?= $halaman=='objek'?'active text-emerald-700':'' ?>">Objek</a>
                <a href="index.php?page=peta" class="nav-link <?= $halaman=='peta'?'active text-emerald-700':'' ?>">Peta</a>
                <a href="index.php?page=tentang" class="nav-link <?= $halaman=='tentang'?'active text-emerald-700':'' ?>">Tentang</a>
                <a href="index.php?page=kontak" class="nav-link <?= $halaman=='kontak'?'active text-emerald-700':'' ?>">Kontak</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                
                <?php if($halaman !== 'beranda'): ?>
                    <a href="index.php?page=objek_daftar" class="px-4 py-2 bg-emerald-700 text-white rounded-full font-semibold hover:bg-emerald-800 transition flex items-center gap-2">
    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Objek
</a>
<?php endif; ?>
                    <?php if(is_admin()): ?>
                        <a href="index.php?page=recycle" class="text-amber-600 hover:text-amber-700" title="Recycle Bin"><i data-lucide="recycle" class="w-3.5 h-3.5"></i></a>
                    <?php endif; ?>
                    <?php if(is_admin()): ?>
    <a href="index.php?page=kelola_user" class="text-indigo-600 hover:text-indigo-700" title="Kelola User">
        <i data-lucide="users" class="w-3.5 h-3.5"></i>
    </a>
    <a href="index.php?page=audit" class="text-slate-500 hover:text-slate-700" title="Audit Log">
        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
    </a>
<?php endif; ?>
<?php if(is_super_admin()): ?>
    <a href="index.php?page=pesan" class="text-blue-600 hover:text-blue-700 relative" title="Pesan Kontak">
        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
        <?php 
        $unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM pesan_kontak WHERE sudah_dibaca=0"));
        if($unread['c'] > 0): ?>
            <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold rounded-full w-3.5 h-3.5 flex items-center justify-center"><?= $unread['c'] ?></span>
        <?php endif; ?>
    </a>
<?php endif; ?>
                    <div class="relative group">
                        <button class="flex items-center gap-2 text-sm">
                            <?php
$me_row = ['nama_lengkap' => $_SESSION['nama'] ?? '?', 'avatar' => null];
$stmt_me = mysqli_prepare($conn, "SELECT avatar FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_me, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt_me);
$r_me = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_me));
mysqli_stmt_close($stmt_me);
if ($r_me) $me_row['avatar'] = $r_me['avatar'];
echo tampil_avatar($me_row, 'w-8 h-8', 'text-xs');
?>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                            <div class="px-4 py-2 border-b">
                                <div class="font-bold text-sm"><?= e($_SESSION['nama']) ?></div>
                                <div class="text-xs text-emerald-600 font-semibold uppercase"><?= e($_SESSION['role']) ?></div>
                            </div>
                                                        <a href="index.php?page=misi_saya" class="flex items-center gap-2 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">
                                <i data-lucide="target" class="w-3.5 h-3.5"></i> Misi Saya
                            </a>
                                                        <?php
                            $stmt_nf = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND tipe = 'follow_request' AND sudah_dibaca = 0");
                            mysqli_stmt_bind_param($stmt_nf, 'i', $_SESSION['user_id']);
                            mysqli_stmt_execute($stmt_nf);
                            $nf = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_nf));
                            mysqli_stmt_close($stmt_nf);
                            $nf_count = $nf ? (int)$nf['c'] : 0;
                            ?>
                            <a href="index.php?page=permintaan_follow" class="flex items-center gap-2 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">
                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Permintaan Follow
                                <?php if($nf_count > 0): ?>
                                    <span class="ml-auto bg-rose-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center"><?= $nf_count ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="index.php?logout=true" class="flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Keluar
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="index.php?page=login" class="text-emerald-700 hover:text-emerald-800 font-semibold">Masuk</a>
                    <a href="index.php?page=register" class="px-5 py-2.5 bg-emerald-700 text-white rounded-full font-semibold hover:bg-emerald-800 transition shadow-lg shadow-emerald-700/20">Daftar</a>
                <?php endif; ?>
            </nav>
            
            <button onclick="toggleNavMobile()" aria-label="Menu" class="lg:hidden p-2 text-slate-700">
                <i data-lucide="menu" id="icon-menu" class="w-6 h-6"></i>
                <i data-lucide="x" id="icon-close" class="w-6 h-6 hidden"></i>
            </button>
        </div>
    </div>
    
    <div id="nav-mobile" class="hidden lg:hidden glass border-t border-emerald-100/50 shadow-xl">
        <div class="px-4 py-4 space-y-1 text-sm">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="bg-emerald-50 rounded-2xl px-4 py-3 mb-3 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold">
                        <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-bold text-emerald-900"><?= e($_SESSION['nama']) ?></div>
                        <div class="text-xs text-emerald-600 font-semibold uppercase"><?= e($_SESSION['role']) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            
                        <form action="index.php" method="GET" class="py-1">
                <input type="hidden" name="page" value="cari">
                <div class="relative">
                    <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" 
                        placeholder="Cari objek/user..."
                        class="w-full pl-9 pr-3 py-2.5 text-sm border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </form>
            
            <a href="index.php?page=beranda" class="flex items-center gap-3 py-2 px-2.5 rounded-xl <?= $halaman=='beranda'?'bg-emerald-50 text-emerald-700 font-bold':'' ?>">
                <i data-lucide="home" class="w-3.5 h-3.5"></i> Beranda
            </a>
            <a href="index.php?page=arsip" class="flex items-center gap-3 py-2 px-2.5 rounded-xl <?= $halaman=='arsip'?'bg-emerald-50 text-emerald-700 font-bold':'' ?>">
                <i data-lucide="folder" class="w-3.5 h-3.5"></i> Arsip Global
            </a>
            <a href="index.php?page=objek" class="flex items-center gap-3 py-2 px-2.5 rounded-xl <?= $halaman=='objek'?'bg-emerald-50 text-emerald-700 font-bold':'' ?>">
    <i data-lucide="package" class="w-3.5 h-3.5"></i> Daftar Objek
</a>
            <a href="index.php?page=peta" class="flex items-center gap-3 py-2 px-2.5 rounded-xl <?= $halaman=='peta'?'bg-emerald-50 text-emerald-700 font-bold':'' ?>">
                <i data-lucide="map" class="w-3.5 h-3.5"></i> Peta Sebaran
            </a>
            <a href="index.php?page=tentang" class="flex items-center gap-3 py-2 px-2.5 rounded-xl <?= $halaman=='tentang'?'bg-emerald-50 text-emerald-700 font-bold':'' ?>">
                <i data-lucide="info" class="w-3.5 h-3.5"></i> Tentang
            </a>
            <a href="index.php?page=kontak" class="flex items-center gap-3 py-2 px-2.5 rounded-xl <?= $halaman=='kontak'?'bg-emerald-50 text-emerald-700 font-bold':'' ?>">
                <i data-lucide="mail" class="w-3.5 h-3.5"></i> Kontak
            </a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($halaman !== 'beranda'): ?>
                <a href="index.php?page=objek_daftar" class="flex items-center gap-3 py-2 px-2.5 rounded-xl bg-amber-50 text-amber-800 font-bold">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Tambah Objek Baru
                </a>
                <?php endif; ?>
                <?php if(is_admin()): ?>
                    <a href="index.php?page=recycle" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-amber-700">
                        <i data-lucide="recycle" class="w-3.5 h-3.5"></i> Recycle Bin
                    </a>
                <?php endif; ?>
                <?php if(is_admin()): ?>
    <a href="index.php?page=kelola_user" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-indigo-700">
        <i data-lucide="users" class="w-3.5 h-3.5"></i> Kelola User
    </a>
    <a href="index.php?page=audit" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-slate-700">
        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Audit Log
    </a>
<?php endif; ?>
<?php if(is_super_admin()): ?>
    <a href="index.php?page=pesan" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-blue-700">
        <i data-lucide="mail" class="w-3.5 h-3.5"></i> Pesan Kontak
    </a>
<?php endif; ?>
<a href="index.php?page=misi_saya" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-emerald-700">
    <i data-lucide="target" class="w-3.5 h-3.5"></i> Misi Saya
</a>
                <a href="index.php?page=permintaan_follow" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-amber-700">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Permintaan Follow
                    <?php if(isset($nf_count) && $nf_count > 0): ?>
                        <span class="ml-auto bg-rose-500 text-white text-[10px] font-bold rounded-full px-2 py-0.5"><?= $nf_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="index.php?logout=true" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-rose-600 font-bold border-t mt-2 pt-3">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Keluar
                </a>
            <?php else: ?>
                <a href="index.php?page=login" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-emerald-700 font-bold border-t mt-2 pt-3">
                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Masuk
                </a>
                <a href="index.php?page=register" class="flex items-center gap-3 py-2 px-2.5 rounded-xl text-emerald-700 font-bold">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ============ HALAMAN BERANDA ============ -->
<?php if ($halaman == 'beranda'): ?>
    
    <!-- HERO -->
    <section class="hero-bg min-h-[75vh] flex items-center justify-center text-white pt-24 pb-16 relative">
        <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
            <img src="assets/logo-vertikal.png" alt="SWEAPING" 
                 class="h-32 md:h-48 w-auto mx-auto mb-6 fade-up drop-shadow-2xl"
                 onerror="this.style.display='none'">
            
            <h1 class="text-3xl md:text-6xl font-extrabold mt-4 mb-4 leading-tight fade-up fade-up-delay-1 drop-shadow-lg">
                Hulu Jaga Hilir,<br>Merawat Alam Indonesia
            </h1>
            <p class="text-base md:text-xl text-emerald-50 max-w-2xl mx-auto mb-8 fade-up fade-up-delay-2 drop-shadow">
                Swadaya, Edukasi, dan Aksi Peduli Lingkungan untuk Indonesia yang lebih hijau.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-3 fade-up fade-up-delay-3">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=lapor" class="px-8 py-4 bg-amber-500 text-slate-900 font-bold rounded-full shadow-2xl hover:bg-amber-400 transition transform hover:scale-105 flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Buat Laporan
                    </a>
                <?php else: ?>
                    <a href="index.php?page=login" class="px-8 py-4 bg-amber-500 text-slate-900 font-bold rounded-full shadow-2xl hover:bg-amber-400 transition transform hover:scale-105 flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Masuk untuk Melapor
                    </a>
                <?php endif; ?>
                <a href="index.php?page=arsip" class="px-8 py-4 glass-dark border-2 border-white/40 text-white font-bold rounded-full hover:bg-white/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="folder" class="w-3.5 h-3.5"></i> Lihat Arsip
                </a>
            </div>
            
            <div class="mt-16 fade-up fade-up-delay-3">
                <a href="#stats" class="inline-flex flex-col items-center text-white/70 hover:text-white transition animate-float">
                    <span class="text-xs mb-1">Gulir ke bawah</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- STATISTIK -->
    <?php
    $stats = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) as total,
    SUM(CASE WHEN jenis_objek='pohon' THEN 1 ELSE 0 END) as total_pohon,
    SUM(CASE WHEN jenis_objek='sungai' THEN 1 ELSE 0 END) as total_sungai,
    SUM(CASE WHEN jenis_objek='taman' THEN 1 ELSE 0 END) as total_taman,
    COALESCE(SUM(serapan_karbon),0) as total_karbon,
    COUNT(DISTINCT nama_kontributor) as total_kontributor,
    COALESCE(SUM(CASE WHEN jenis_objek='pohon' AND metode_hitung='chave_2005' THEN serapan_karbon_ilmiah ELSE 0 END),0) as karbon_pohon,
    COALESCE(SUM(CASE WHEN jenis_objek='taman' AND metode_hitung='rth_konversi' THEN serapan_karbon_ilmiah ELSE 0 END),0) as karbon_taman,
    COALESCE(SUM(CASE WHEN jenis_objek='komposting' AND metode_hitung='ipcc_kompos' THEN serapan_karbon_ilmiah ELSE 0 END),0) as karbon_kompos,
    COALESCE(SUM(CASE WHEN jenis_objek='sungai' AND metode_hitung='ch4_estimasi' THEN serapan_karbon_ilmiah ELSE 0 END),0) as ch4_sungai
FROM laporan_aksi WHERE deleted_at IS NULL AND visibility = 'publik'
"));
    ?>
    <section class="bg-gradient-to-br from-emerald-800 to-emerald-950 text-white py-16 md:py-24 relative overflow-hidden group">
                <!-- Ikon Melayang (background) -->
        <i data-lucide="leaf" class="cta-icon-float absolute top-10 left-[10%] w-16 h-16 text-emerald-300 pointer-events-none"></i>
        <i data-lucide="tree-pine" class="cta-icon-float-delay absolute bottom-16 right-[12%] w-20 h-20 text-emerald-300 pointer-events-none"></i>
        <i data-lucide="sprout" class="cta-icon-float-slow absolute top-20 right-[20%] w-12 h-12 text-emerald-200 pointer-events-none"></i>
        <i data-lucide="flower-2" class="cta-icon-float absolute bottom-10 left-[18%] w-10 h-10 text-emerald-200 pointer-events-none"></i>
        <i data-lucide="leaf" class="cta-icon-float-delay absolute top-1/2 left-[5%] w-8 h-8 text-emerald-200 pointer-events-none"></i>
        <div class="absolute inset-0 opacity-10">
            <svg viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg" class="w-full h-full"><path d="M0,300 Q300,100 600,300 T1200,300 L1200,600 L0,600 Z" fill="white"/></svg>
        </div>
        <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-2xl md:text-4xl font-extrabold mb-4">Siap Beraksi untuk Lingkungan?</h2>
            <p class="text-emerald-100 mb-8 text-base md:text-lg">Bergabung dengan komunitas yang peduli. Setiap laporan adalah langkah nyata untuk Indonesia yang lebih hijau.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=objek_daftar" class="cta-btn-glow px-8 py-4 bg-amber-500 text-slate-900 font-bold rounded-full shadow-2xl hover:bg-amber-400 flex items-center justify-center gap-2 text-base relative z-10">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 relative z-10"></i> 
                        <span class="relative z-10">Tambah Objek Baru Sekarang</span>
                    </a>
                <?php else: ?>
                    <a href="index.php?page=register" class="cta-btn-glow px-8 py-4 bg-amber-500 text-slate-900 font-bold rounded-full shadow-2xl hover:bg-amber-400 flex items-center justify-center gap-2 relative z-10">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5 relative z-10"></i>
                        <span class="relative z-10">Daftar Gratis</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <section id="stats" class="max-w-7xl mx-auto px-4 mt-12 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-xl p-5 md:p-6 border border-slate-100 card-hover">
                <div class="feature-icon bg-emerald-100 text-emerald-700 mb-3">
                    <i data-lucide="bar-chart-3"></i>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-slate-900"><?= number_format($stats['total']) ?></div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-1 tracking-tight">Total Laporan</div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-5 md:p-6 border-2 border-emerald-100 card-hover">
                <div class="feature-icon bg-emerald-600 text-white mb-3">
                    <i data-lucide="tree-pine"></i>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-emerald-700"><?= number_format($stats['total_pohon']) ?></div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-1 tracking-tight">Pohon Dipantau</div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-5 md:p-6 border-2 border-amber-100 card-hover">
                <div class="feature-icon bg-amber-500 text-white mb-3">
                    <i data-lucide="leaf"></i>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-amber-700"><?= number_format($stats['total_karbon'], 0) ?></div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-1 tracking-tight">Kg CO₂ Terserap</div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl p-5 md:p-6 border border-slate-100 card-hover">
                <div class="feature-icon bg-blue-100 text-blue-700 mb-3">
                    <i data-lucide="users"></i>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-slate-900"><?= number_format($stats['total_kontributor']) ?></div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-1 tracking-tight">Kontributor</div>
            </div>
        </div>
        
        <!-- RINCIAN KARBON PER JENIS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        
        <!-- POHON -->
        <div class="bg-white rounded-2xl shadow-lg p-5 md:p-6 border-2 border-emerald-100 card-hover relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-100 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                        <i data-lucide="tree-pine" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full uppercase tracking-wide">Chave 2005</span>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-emerald-700 leading-tight">
                    <?= number_format($stats['karbon_pohon'], 0) ?>
                </div>
                <div class="text-[10px] text-emerald-600 font-bold">kg CO₂</div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-2 tracking-tight">Serapan Pohon</div>
            </div>
        </div>

        <!-- TAMAN -->
        <div class="bg-white rounded-2xl shadow-lg p-5 md:p-6 border-2 border-lime-100 card-hover relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-lime-100 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-lime-500 to-lime-700 text-white flex items-center justify-center shadow-md">
                        <i data-lucide="flower-2" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-lime-100 text-lime-700 rounded-full uppercase tracking-wide">RTH</span>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-lime-700 leading-tight">
                    <?= number_format($stats['karbon_taman'], 0) ?>
                </div>
                <div class="text-[10px] text-lime-600 font-bold">kg CO₂</div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-2 tracking-tight">Serapan Taman</div>
            </div>
        </div>

        <!-- KOMPOSTING -->
        <div class="bg-white rounded-2xl shadow-lg p-5 md:p-6 border-2 border-amber-100 card-hover relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-100 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-white flex items-center justify-center shadow-md">
                        <i data-lucide="recycle" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full uppercase tracking-wide">IPCC</span>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-amber-700 leading-tight">
                    <?= number_format($stats['karbon_kompos'], 0) ?>
                </div>
                <div class="text-[10px] text-amber-600 font-bold">kg CO₂</div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-2 tracking-tight">Dicegah Kompos</div>
            </div>
        </div>

        <!-- SUNGAI -->
        <div class="bg-white rounded-2xl shadow-lg p-5 md:p-6 border-2 border-blue-100 card-hover relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-blue-100 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center shadow-md">
                        <i data-lucide="waves" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full uppercase tracking-wide">IPCC</span>
                </div>
                <div class="text-2xl md:text-3xl font-extrabold text-blue-700 leading-tight">
                    <?= number_format($stats['ch4_sungai'], 0) ?>
                </div>
                <div class="text-[10px] text-blue-600 font-bold">kg CO₂e</div>
                <div class="text-xs md:text-sm text-slate-500 font-semibold mt-2 tracking-tight">Potensi CH₄</div>
            </div>
        </div>

    </div>
    </section>

    <!-- FITUR UNGGULAN -->
    <section class="max-w-7xl mx-auto px-4 py-16 md:py-24">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wide mb-3">Fitur Unggulan</span>
            <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mb-3">Semua yang Anda Butuhkan</h2>
            <p class="text-slate-600 max-w-2xl mx-auto">Alat lengkap untuk mendokumentasikan, memantau, dan mengukur dampak aksi lingkungan Anda.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <a href="index.php?page=arsip&f_jenis=pohon" class="bg-white rounded-2xl p-6 border border-slate-100 card-hover block group">
                <div class="feature-icon bg-emerald-100 text-emerald-700 mb-4 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300"><i data-lucide="tree-pine"></i></div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-emerald-700 transition">Monitoring Pohon</h3>
                <p class="text-sm text-slate-600">Pantau kesehatan & pertumbuhan pohon dengan foto & data terstruktur.</p>
                <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-emerald-600 opacity-0 group-hover:opacity-100 transition">
                    Lihat Arsip <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </div>
            </a>
            <a href="index.php?page=arsip&f_jenis=sungai" class="bg-white rounded-2xl p-6 border border-slate-100 card-hover block group">
                <div class="feature-icon bg-blue-100 text-blue-700 mb-4 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300"><i data-lucide="waves"></i></div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-blue-700 transition">Patroli Sungai</h3>
                <p class="text-sm text-slate-600">Dokumentasikan kondisi sungai, temuan sampah, & tindak lanjut.</p>
                <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-blue-600 opacity-0 group-hover:opacity-100 transition">
                    Lihat Arsip <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </div>
            </a>
            <a href="index.php?page=arsip" class="bg-white rounded-2xl p-6 border border-slate-100 card-hover block group">
                <div class="feature-icon bg-amber-100 text-amber-700 mb-4 group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300"><i data-lucide="calculator"></i></div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-amber-700 transition">Penghitung Karbon</h3>
                <p class="text-sm text-slate-600">Estimasi serapan CO₂ otomatis dari data pohon yang dilaporkan.</p>
                <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-amber-600 opacity-0 group-hover:opacity-100 transition">
                    Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </div>
            </a>
            <a href="index.php?page=arsip&f_jenis=taman" class="bg-white rounded-2xl p-6 border border-slate-100 card-hover block group">
                <div class="feature-icon bg-lime-100 text-lime-700 mb-4 group-hover:scale-110 group-hover:bg-lime-600 group-hover:text-white transition-all duration-300"><i data-lucide="map-pin"></i></div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-lime-700 transition">Monitoring Taman</h3>
                <p class="text-sm text-slate-600">Pantau kondisi taman kota, kebersihan, & keindahan lingkungan.</p>
                <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-lime-600 opacity-0 group-hover:opacity-100 transition">
                    Lihat Arsip <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </div>
            </a>
            <a href="index.php?page=arsip&f_jenis=komposting" class="bg-white rounded-2xl p-6 border border-slate-100 card-hover block group">
                <div class="feature-icon bg-amber-600 text-white mb-4 group-hover:scale-110 group-hover:bg-amber-700 transition-all duration-300"><i data-lucide="recycle"></i></div>
                <h3 class="font-bold text-lg mb-2 group-hover:text-amber-700 transition">Komposting</h3>
                <p class="text-sm text-slate-600">Catat volume sampah organik & hasil kompos. Hitung CO₂ dicegah otomatis.</p>
                <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-amber-700 opacity-0 group-hover:opacity-100 transition">
                    Lihat Arsip <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </div>
            </a>
        </div>
    </section>

    <!-- AKTIVITAS TERBARU -->
    <section class="bg-gradient-to-br from-emerald-50 to-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-end justify-between mb-8 flex-wrap gap-3">
                <div>
                    <span class="inline-block px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wide mb-2">Live Feed</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">Aktivitas Terbaru</h2>
                </div>
                <a href="index.php?page=arsip" class="text-emerald-700 font-semibold hover:text-emerald-800 flex items-center gap-1">
                    Lihat Semua <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $res_feed = mysqli_query($conn, "SELECT * FROM laporan_aksi WHERE deleted_at IS NULL AND visibility = 'publik' ORDER BY tanggal_buat DESC LIMIT 3");
                if (mysqli_num_rows($res_feed) > 0) {
                    while($row = mysqli_fetch_assoc($res_feed)) {
                        $jenis_info = [
    'sungai'     => ['badge' => 'from-blue-500 to-blue-600',       'ikon' => 'waves'],
    'pohon'      => ['badge' => 'from-emerald-600 to-emerald-700', 'ikon' => 'tree-pine'],
    'taman'      => ['badge' => 'from-lime-500 to-lime-600',       'ikon' => 'flower-2'],
    'komposting' => ['badge' => 'from-amber-600 to-amber-700',     'ikon' => 'recycle'],
];
$info = $jenis_info[$row['jenis_objek']] ?? $jenis_info['pohon'];
$badge = $info['badge'];
$ikon = $info['ikon'];
                        ?>
                        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 card-hover">
                            <div class="bg-gradient-to-r <?= $badge ?> text-white text-xs font-bold px-4 py-2 uppercase flex items-center gap-2">
                                <i data-lucide="<?= $ikon ?>" class="w-3.5 h-3.5"></i>
                                <?= e($row['jenis_objek']) ?>
                            </div>
                                                        <?php $link_objek = 'index.php?page=objek_profil&id=' . (int)$row['objek_id']; ?>
                            <?php if($row['foto'] || !empty($row['telegram_file_id'])): ?>
    <a href="<?= $link_objek ?>" class="block" title="Lihat profil objek">
        <img src="<?= e(url_foto($row, 'foto', 'thumb')) ?>" alt="Foto" class="w-full h-44 object-cover hover:opacity-90 transition-opacity" loading="lazy">
    </a>
<?php endif; ?>
                            <div class="p-5">
                                                                <a href="<?= $link_objek ?>" class="block font-bold text-lg mb-1 hover:text-emerald-700 transition-colors"><?= e($row['nama_objek']) ?></a>
                                <p class="text-xs text-emerald-600 font-semibold mb-2 flex items-center gap-1">
                                    <i data-lucide="circle-check" class="w-3 h-3"></i>
                                    <?= e($row['status_kondisi']) ?>
                                </p>
                                <?php if($row['jenis_objek'] == 'pohon'): ?>
                                    <p class="text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded-lg font-bold mb-3 inline-flex items-center gap-1">
                                        <i data-lucide="leaf" class="w-3 h-3"></i>
                                        <?= number_format($row['serapan_karbon'], 2) ?> kg CO₂
                                    </p>
                                <?php endif; ?>
                                <p class="text-slate-600 text-sm mb-3"><?= e(mb_strimwidth($row['catatan'], 0, 100, '...')) ?></p>
                                <div class="border-t pt-3 text-xs text-slate-500 flex items-center gap-1">
                                    <div class="border-t pt-3 flex items-center justify-between gap-2">
    <a href="<?= (int)$row['user_id'] > 0 ? 'index.php?page=user_profil&id=' . (int)$row['user_id'] : '#' ?>" 
   class="text-xs text-slate-500 hover:text-emerald-700 hover:underline flex items-center gap-1 min-w-0 truncate transition"
   title="Lihat profil <?= e($row['nama_kontributor']) ?>">
    <?php 
    $row_avatar_feed = ['nama_lengkap' => $row['nama_kontributor'], 'avatar' => null];
    $stmt_av = mysqli_prepare($conn, "SELECT avatar FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_av, 'i', $row['user_id']);
    mysqli_stmt_execute($stmt_av);
    $r_av = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_av));
    mysqli_stmt_close($stmt_av);
    if ($r_av) $row_avatar_feed['avatar'] = $r_av['avatar'];
    echo tampil_avatar($row_avatar_feed, 'w-5 h-5', 'text-[10px]');
    ?>
    <span class="truncate"><?= e($row['nama_kontributor']) ?></span>
</a>
    <?php if(isset($_SESSION['user_id']) && (int)($_SESSION['user_id']) !== (int)$row['user_id']): 
        
        // Cek apakah sudah follow
                           $stmt_ck = mysqli_prepare($conn, "SELECT f.id, f.status, u.is_primary_admin FROM follows f INNER JOIN users u ON u.id = f.following_id WHERE f.follower_id = ? AND f.following_id = ? LIMIT 1");
                    mysqli_stmt_bind_param($stmt_ck, 'ii', $_SESSION['user_id'], $row['user_id']);
                    mysqli_stmt_execute($stmt_ck);
                    $ck = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_ck));
                    mysqli_stmt_close($stmt_ck);
        $status_val = $ck ? $ck['status'] : 'not-following';
        $is_following = ($status_val === 'accepted');
        $is_pending   = ($status_val === 'pending');
        $is_primary   = $ck && (int)$ck['is_primary_admin'] === 1;
    ?>
                <?php
        $btn_state = 'not-following';
        if ($is_following) $btn_state = 'following';
        elseif ($is_pending) $btn_state = 'pending';
        
        $btn_class = 'bg-emerald-600 text-white hover:bg-emerald-700';
        if ($btn_state === 'following') $btn_class = 'bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700';
        elseif ($btn_state === 'pending') $btn_class = 'bg-amber-100 text-amber-700 hover:bg-amber-200';
        ?>
        <button type="button" 
            data-follow-inline="<?= (int)$row['user_id'] ?>"
            data-state="<?= $btn_state ?>"
            data-primary="<?= $is_primary ? '1' : '0' ?>"
            class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold transition <?= $btn_class ?>">
            <?php if($btn_state === 'following'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Mengikuti</span>
            <?php elseif($btn_state === 'pending'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Menunggu</span>
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Ikuti</span>
            <?php endif; ?>
        </button>
    <?php endif; ?>
</div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-span-3 text-center py-12 bg-white rounded-2xl border border-dashed border-slate-200">
                        <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                        <p class="text-slate-500">Belum ada aktivitas</p>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- LEADERBOARD -->
    <section class="bg-gradient-to-br from-amber-50 via-white to-amber-50 py-16 md:py-24">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-10">
                <span class="inline-block px-4 py-1.5 bg-amber-100 text-amber-700 rounded-full text-xs font-bold uppercase tracking-wide mb-3">Papan Peringkat</span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mb-2">🏆 Top Kontributor</h2>
                <p class="text-slate-600 max-w-xl mx-auto">Pahlawan lingkungan paling aktif di SWEAPING</p>
            </div>
            
            <?php
            $res_lb = mysqli_query($conn, "
                SELECT u.id, u.nama_lengkap, u.organisasi, u.role,
                       COUNT(l.id) as total_laporan,
                       COALESCE(SUM(l.serapan_karbon), 0) as total_karbon
                FROM users u
                INNER JOIN laporan_aksi l ON l.user_id = u.id
                WHERE l.deleted_at IS NULL AND u.deleted_at IS NULL
                GROUP BY u.id
                ORDER BY total_laporan DESC, total_karbon DESC
                LIMIT 50
            ");
            $leaderboard = [];
            while ($lb = mysqli_fetch_assoc($res_lb)) $leaderboard[] = $lb;
            ?>
            
            <?php if (empty($leaderboard)): ?>
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 text-center">
                    <i data-lucide="trophy" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                    <p class="text-slate-500">Belum ada kontributor. Jadilah yang pertama! 🌱</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden max-h-[600px] max-h-[calc(100vh-100px)] overflow-y-auto">
                    <?php foreach($leaderboard as $idx => $lb): 
                        $rank = $idx + 1;
                        $medal = '';
                        $bg_row = 'bg-white hover:bg-slate-50';
                        $badge_color = 'bg-slate-100 text-slate-700';
                        
                        if ($rank === 1) { $medal = '🥇'; $bg_row = 'bg-gradient-to-r from-amber-50 via-amber-50 to-white'; $badge_color = 'bg-gradient-to-br from-amber-400 to-amber-600 text-white'; }
                        elseif ($rank === 2) { $medal = '🥈'; $bg_row = 'bg-gradient-to-r from-slate-50 to-white'; $badge_color = 'bg-gradient-to-br from-slate-300 to-slate-500 text-white'; }
                        elseif ($rank === 3) { $medal = '🥉'; $bg_row = 'bg-gradient-to-r from-orange-50 to-white'; $badge_color = 'bg-gradient-to-br from-orange-400 to-orange-600 text-white'; }
                        else { $medal = $rank; }
                        
                        $role_icon = ['super_admin'=>'👑','admin'=>'🛡️','user'=>'👤'];
                        $ri = $role_icon[$lb['role']] ?? '👤';
                    ?>
                        <div class="<?= $bg_row ?> p-4 flex items-center gap-4 <?= $idx < count($leaderboard)-1 ? 'border-b border-slate-100' : '' ?> transition">
                            <!-- Rank Badge -->
                            <div class="w-12 h-12 rounded-full <?= $badge_color ?> flex items-center justify-center font-extrabold text-lg flex-shrink-0 shadow-sm">
                                <?= $rank <= 3 ? $medal : $rank ?>
                            </div>
                            
                            <!-- Nama + Organisasi -->
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-800 truncate flex items-center gap-1.5">
                                    <?= e($lb['nama_lengkap']) ?>
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-0.5 flex items-center gap-1">
                                    <?= $ri ?> 
                                    <?php if($lb['organisasi']): ?>
                                        <span class="font-semibold text-emerald-700"><?= e($lb['organisasi']) ?></span>
                                    <?php else: ?>
                                        <span><?= ucwords(str_replace('_',' ', $lb['role'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Stats -->
                            <div class="text-right flex-shrink-0">
                                <div class="font-extrabold text-slate-800 leading-tight"><?= number_format($lb['total_laporan']) ?></div>
                                <div class="text-[10px] text-slate-400 font-semibold">LAPORAN</div>
                                <div class="text-[10px] text-amber-600 font-bold mt-0.5"><?= number_format($lb['total_karbon'], 0) ?> kg CO₂</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p class="text-center text-xs text-slate-500 mt-4 italic">
                    💡 Daftarkan objek baru & catat monitoring untuk naik peringkat!
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- WIDGET KONTRIBUTOR AKTIF -->
    <section class="bg-gradient-to-br from-emerald-50 via-white to-amber-50 py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-8">
                <span class="inline-block px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wide mb-3">👥 Kontributor Aktif</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-2">Kontributor Paling Aktif</h2>
                <p class="text-slate-600 max-w-xl mx-auto text-sm md:text-base">Minggu ini — yuk follow mereka untuk melihat update terbaru!</p>
            </div>
            
            <?php
            $stmt_ka = mysqli_prepare($conn, "
                SELECT u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar,
                       COUNT(l.id) as total_laporan
                FROM users u
                INNER JOIN laporan_aksi l ON l.user_id = u.id
                WHERE l.deleted_at IS NULL 
                  AND l.visibility = 'publik'
                  AND l.tanggal_buat >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  AND u.deleted_at IS NULL
                  AND u.aktif = 1
                GROUP BY u.id
                ORDER BY total_laporan DESC, u.id ASC
                LIMIT 8
            ");
            mysqli_stmt_execute($stmt_ka);
            $res_ka = mysqli_stmt_get_result($stmt_ka);
            $kontributor_aktif = [];
            while ($ka = mysqli_fetch_assoc($res_ka)) $kontributor_aktif[] = $ka;
            mysqli_stmt_close($stmt_ka);
            ?>
            
            <?php if(empty($kontributor_aktif)): ?>
                <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-200 max-w-md mx-auto">
                    <i data-lucide="users" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                    <p class="text-sm text-slate-500">Belum ada aktivitas minggu ini.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                    <?php foreach($kontributor_aktif as $ka): 
                        $is_me_ka = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$ka['id'];
                        
                        $ka_state = 'not-following';
                        if (isset($_SESSION['user_id']) && !$is_me_ka) {
                            $stmt_f_ka = mysqli_prepare($conn, "SELECT status FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
                            mysqli_stmt_bind_param($stmt_f_ka, 'ii', $_SESSION['user_id'], $ka['id']);
                            mysqli_stmt_execute($stmt_f_ka);
                            $rf_ka = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_f_ka));
                            mysqli_stmt_close($stmt_f_ka);
                            if ($rf_ka) {
                                if ($rf_ka['status'] === 'accepted') $ka_state = 'following';
                                elseif ($rf_ka['status'] === 'pending') $ka_state = 'pending';
                            }
                        }
                        
                        $ka_btn_class = 'bg-emerald-600 text-white hover:bg-emerald-700';
                        if ($ka_state === 'following') $ka_btn_class = 'bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700';
                        elseif ($ka_state === 'pending') $ka_btn_class = 'bg-amber-100 text-amber-700 hover:bg-amber-200';
                        
                        $role_icon_ka = ['super_admin'=>'👑','admin'=>'🛡️','user'=>'👤'];
                        $ri_ka = $role_icon_ka[$ka['role']] ?? '👤';
                    ?>
                        <div class="bg-white rounded-2xl border border-slate-100 p-4 text-center hover:shadow-lg transition card-hover">
                            <a href="index.php?page=user_profil&id=<?= (int)$ka['id'] ?>" class="block">
                                <div class="flex justify-center mb-3">
                                    <?= tampil_avatar($ka, 'w-16 h-16 md:w-20 md:h-20', 'text-2xl') ?>
                                </div>
                                <div class="font-bold text-sm text-slate-800 truncate">
                                    <?= e($ka['nama_lengkap']) ?>
                                </div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5 mb-2">
                                    <?= $ri_ka ?> 
                                    <?php if($ka['organisasi']): ?>
                                        <?= e($ka['organisasi']) ?>
                                    <?php else: ?>
                                        <?= ucwords(str_replace('_',' ', $ka['role'])) ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                            
                            <div class="text-xs text-emerald-700 font-bold mb-3">
                                <?= number_format($ka['total_laporan']) ?> laporan minggu ini
                            </div>
                            
                            <?php if(isset($_SESSION['user_id']) && !$is_me_ka): ?>
                                <button type="button"
                                    data-follow-inline="<?= (int)$ka['id'] ?>"
                                    data-state="<?= $ka_state ?>"
                                    data-primary="0"
                                    class="w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-[11px] font-bold transition <?= $ka_btn_class ?>">
                                    <?php if($ka_state === 'following'): ?>
                                        <i data-lucide="check" class="w-3 h-3"></i> Mengikuti
                                    <?php elseif($ka_state === 'pending'): ?>
                                        <i data-lucide="clock" class="w-3 h-3"></i> Menunggu
                                    <?php else: ?>
                                        <i data-lucide="plus" class="w-3 h-3"></i> Ikuti
                                    <?php endif; ?>
                                </button>
                            <?php elseif($is_me_ka): ?>
                                <div class="text-[11px] text-slate-400 italic">Anda</div>
                            <?php else: ?>
                                <a href="index.php?page=login" class="w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                    <i data-lucide="log-in" class="w-3 h-3"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>


    <!-- LEADERBOARD ORGANISASI -->
    <section class="bg-gradient-to-br from-blue-50 via-white to-blue-50 py-16 md:py-24">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-10">
                <span class="inline-block px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase tracking-wide mb-3">Peringkat Lembaga</span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 mb-2">🏛️ Top Organisasi</h2>
                <p class="text-slate-600 max-w-xl mx-auto">Komunitas & lembaga paling aktif di SWEAPING</p>
            </div>
            
            <?php
            $res_org = mysqli_query($conn, "
                SELECT u.organisasi,
                       COUNT(DISTINCT u.id) as total_kontributor,
                       COUNT(l.id) as total_laporan,
                       COALESCE(SUM(l.serapan_karbon), 0) as total_karbon
                FROM users u
                INNER JOIN laporan_aksi l ON l.user_id = u.id
                WHERE u.organisasi IS NOT NULL 
                  AND u.organisasi != ''
                  AND l.deleted_at IS NULL 
                  AND u.deleted_at IS NULL
                GROUP BY u.organisasi
                ORDER BY total_laporan DESC, total_karbon DESC
                LIMIT 20
            ");
            $orgs = [];
            while ($o = mysqli_fetch_assoc($res_org)) $orgs[] = $o;
            ?>
            
            <?php if (empty($orgs)): ?>
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-12 text-center">
                    <i data-lucide="building-2" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                    <p class="text-slate-500">Belum ada organisasi terdaftar</p>
                    <p class="text-xs text-slate-400 mt-1">Isi nama organisasi di profil Anda untuk muncul di sini</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden max-h-[600px] max-h-[calc(100vh-100px)] overflow-y-auto">
                    <?php foreach($orgs as $idx => $o): 
                        $rank = $idx + 1;
                        $medal = '';
                        $bg_row = 'bg-white hover:bg-slate-50';
                        $badge_color = 'bg-slate-100 text-slate-700';
                        
                        if ($rank === 1) { $medal = '🥇'; $bg_row = 'bg-gradient-to-r from-blue-50 via-blue-50 to-white'; $badge_color = 'bg-gradient-to-br from-blue-400 to-blue-600 text-white'; }
                        elseif ($rank === 2) { $medal = '🥈'; $bg_row = 'bg-gradient-to-r from-slate-50 to-white'; $badge_color = 'bg-gradient-to-br from-slate-300 to-slate-500 text-white'; }
                        elseif ($rank === 3) { $medal = '🥉'; $bg_row = 'bg-gradient-to-r from-cyan-50 to-white'; $badge_color = 'bg-gradient-to-br from-cyan-400 to-cyan-600 text-white'; }
                        else { $medal = $rank; }
                    ?>
                        <div class="<?= $bg_row ?> p-4 flex items-center gap-4 <?= $idx < count($orgs)-1 ? 'border-b border-slate-100' : '' ?> transition">
                            <div class="w-12 h-12 rounded-full <?= $badge_color ?> flex items-center justify-center font-extrabold text-lg flex-shrink-0 shadow-sm">
                                <?= $rank <= 3 ? $medal : $rank ?>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-800 truncate">
                                    🏛️ <?= e($o['organisasi']) ?>
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-0.5">
                                    👥 <?= number_format($o['total_kontributor']) ?> kontributor
                                </div>
                            </div>
                            
                            <div class="text-right flex-shrink-0">
                                <div class="font-extrabold text-slate-800 leading-tight"><?= number_format($o['total_laporan']) ?></div>
                                <div class="text-[10px] text-slate-400 font-semibold">LAPORAN</div>
                                <div class="text-[10px] text-blue-600 font-bold mt-0.5"><?= number_format($o['total_karbon'], 0) ?> kg CO₂</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p class="text-center text-xs text-slate-500 mt-4 italic">
                    🏛️ Ajak komunitas Anda bergabung & raih peringkat teratas!
                </p>
            <?php endif; ?>
        </div>
    </section>

    <?php if(isset($_SESSION['user_id'])): 
        $uid_misi = (int)$_SESSION['user_id'];
        $bulan_ini = date('Y-m');
        $q_misi = mysqli_prepare($conn, "
            SELECT COUNT(*) as total 
            FROM objek 
            WHERE user_id = ? 
              AND deleted_at IS NULL 
              AND DATE_FORMAT(tanggal_daftar, '%Y-%m') = ?
        ");
        mysqli_stmt_bind_param($q_misi, 'is', $uid_misi, $bulan_ini);
        mysqli_stmt_execute($q_misi);
        $r_misi = mysqli_fetch_assoc(mysqli_stmt_get_result($q_misi));
        mysqli_stmt_close($q_misi);
        $total_misi = (int)($r_misi['total'] ?? 0);
        
        $levels = [
            ['nama' => 'Rookie',      'target' => 10,   'emoji' => '🥉'],
            ['nama' => 'Bronze',      'target' => 25,   'emoji' => '🥉'],
            ['nama' => 'Silver',      'target' => 50,   'emoji' => '🥈'],
            ['nama' => 'Gold',        'target' => 100,  'emoji' => '🥇'],
            ['nama' => 'Platinum',    'target' => 250,  'emoji' => '💎'],
            ['nama' => 'Diamond',     'target' => 500,  'emoji' => '🌟'],
            ['nama' => 'Legend',      'target' => 750,  'emoji' => '👑'],
            ['nama' => 'Master',      'target' => 1000, 'emoji' => '🔥'],
            ['nama' => 'Grandmaster', 'target' => 1500, 'emoji' => '💫'],
        ];
        
        $level_capaian = null;
        $level_next = $levels[0];
        foreach ($levels as $lv) {
            if ($total_misi >= $lv['target']) {
                $level_capaian = $lv;
            } else {
                $level_next = $lv;
                break;
            }
        }
        
        $progress_persen = 0;
        $butuh = 0;
        if ($level_capaian) {
            $base = $level_capaian['target'];
            $target_next = $level_next['target'];
            $progress_persen = min(100, round((($total_misi - $base) / ($target_next - $base)) * 100));
            $butuh = $target_next - $total_misi;
        } else {
            $progress_persen = min(100, round(($total_misi / $level_next['target']) * 100));
            $butuh = $level_next['target'] - $total_misi;
        }
        
        $nama_user = $_SESSION['nama'];
        $bulan_indo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $bulan_ini_teks = $bulan_indo[(int)date('n') - 1] . ' ' . date('Y');
    ?>
    <!-- MISI BULAN INI -->
    <section class="max-w-5xl mx-auto px-4 py-16">
        <div class="bg-gradient-to-br from-emerald-50 via-white to-amber-50 rounded-3xl shadow-xl border-2 border-emerald-100 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                    <i data-lucide="target" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide">🎯 Misi Bulan Ini</div>
                    <div class="font-bold text-slate-800"><?= $bulan_ini_teks ?></div>
                </div>
            </div>
            
            <p class="text-slate-700 mb-4">
                Hai <b><?= e($nama_user) ?></b>! 👋 
                <?php if ($total_misi > 0): ?>
                    Anda sudah daftarkan <b><?= number_format($total_misi) ?> objek baru</b> bulan ini. Keren!
                <?php else: ?>
                    Ayo mulai daftarkan objek pertama Anda bulan ini!
                <?php endif; ?>
            </p>
            
            <div class="bg-white rounded-2xl p-4 border-2 border-emerald-200 mb-4">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-3xl"><?= $level_capaian ? $level_capaian['emoji'] : '🌱' ?></span>
                        <div>
                            <div class="text-xs text-slate-500 font-semibold uppercase">Capaian Anda</div>
                            <div class="font-bold text-slate-800">
                                <?= $level_capaian ? $level_capaian['nama'] : 'Belum ada' ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 font-semibold uppercase">Level Berikutnya</div>
                        <div class="font-bold text-emerald-700">
                            <?= $level_next['emoji'] ?> <?= $level_next['nama'] ?> (<?= $level_next['target'] ?>)
                        </div>
                    </div>
                </div>
                
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden mb-2">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-700 rounded-full transition-all" 
                         style="width: <?= $progress_persen ?>%"></div>
                </div>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-emerald-700"><?= $progress_persen ?>%</span>
                    <span class="text-slate-600">
                        Butuh <b><?= $butuh ?></b> objek lagi untuk <b><?= $level_next['nama'] ?></b>
                    </span>
                </div>
            </div>
            
            <p class="text-xs text-slate-500 italic text-center">
                💡 Daftarkan objek baru (pohon, sungai, taman, kompos) untuk naik level. Reset otomatis tanggal 1 setiap bulan.
            </p>
        </div>
    </section>
    <?php endif; ?>

    <?php if(isset($_SESSION['user_id'])): 
        // Recompute untuk popup (tanpa duplikasi query — pakai variabel yang sudah ada di scope)
        if (!isset($total_misi)) $total_misi = 0;
        if (!isset($level_capaian)) $level_capaian = null;
        if (!isset($level_next)) $level_next = ['nama'=>'Rookie','emoji'=>'🥉','target'=>10];
    ?>
    <!-- MODAL MILESTONE -->
    <div id="modal-milestone" class="modal-backdrop fixed inset-0 bg-black/70 z-[4000] flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Selamat!</h2>
            <p class="text-slate-600 mb-4">Anda mencapai level baru:</p>
            <div class="bg-gradient-to-br from-emerald-50 to-amber-50 rounded-2xl p-5 mb-4 border-2 border-emerald-200">
                <div class="text-5xl mb-2" id="milestone-badge-emoji">🥉</div>
                <div class="text-2xl font-extrabold text-emerald-700" id="milestone-level-name">Rookie</div>
                <div class="text-sm text-slate-600 mt-1">
                    <span id="milestone-total">0</span> objek baru bulan ini
                </div>
            </div>
            <div class="text-sm text-slate-500 mb-6">
                Target berikutnya: <b id="milestone-next">🥈 Bronze (25)</b>
            </div>
            <button type="button" onclick="tutupModal('modal-milestone')" 
                    class="w-full py-3.5 bg-gradient-to-r from-emerald-700 to-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                Lanjut Beraksi! 🚀
            </button>
        </div>
    </div>

    <script>
    (function() {
        'use strict';
        var STORAGE_KEY = 'sweaping_misi_level_v1';
        var total_misi = <?= (int)$total_misi ?>;
        var level_sekarang = <?= $level_capaian ? json_encode($level_capaian['nama']) : 'null' ?>;
        var level_next_nama = <?= json_encode($level_next['nama']) ?>;
        var level_next_emoji = <?= json_encode($level_next['emoji']) ?>;
        var level_next_target = <?= (int)$level_next['target'] ?>;
        
        if (!level_sekarang) return;
        
        var level_sebelumnya = null;
        try { level_sebelumnya = localStorage.getItem(STORAGE_KEY); } catch(e) { return; }
        
        var levels_order = ['Rookie','Bronze','Silver','Gold','Platinum','Diamond','Legend','Master','Grandmaster'];
        var idx_baru = levels_order.indexOf(level_sekarang);
        var idx_lama = level_sebelumnya ? levels_order.indexOf(level_sebelumnya) : -1;
        
        if (idx_baru > idx_lama) {
            var level_emojis = {
                'Rookie':'🥉','Bronze':'🥉','Silver':'🥈','Gold':'🥇',
                'Platinum':'💎','Diamond':'🌟','Legend':'👑','Master':'🔥','Grandmaster':'💫'
            };
            
            setTimeout(function() {
                var e1 = document.getElementById('milestone-badge-emoji');
                var e2 = document.getElementById('milestone-level-name');
                var e3 = document.getElementById('milestone-total');
                var e4 = document.getElementById('milestone-next');
                
                if (e1) e1.textContent = level_emojis[level_sekarang] || '🎉';
                if (e2) e2.textContent = level_sekarang;
                if (e3) e3.textContent = total_misi;
                if (e4) e4.textContent = level_next_emoji + ' ' + level_next_nama + ' (' + level_next_target + ')';
                
                if (typeof bukaModal === 'function') bukaModal('modal-milestone');
            }, 1500);
            
            try { localStorage.setItem(STORAGE_KEY, level_sekarang); } catch(e) {}
        }
    })();
    </script>
    <?php endif; ?>


    <!-- CTA -->

<?php elseif ($halaman == 'tentang'): ?>
    
    <!-- HALAMAN TENTANG -->
    <section class="hero-bg pt-32 pb-20 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <span class="inline-block px-4 py-1.5 glass-dark rounded-full text-xs font-bold uppercase tracking-wide mb-4">Tentang Kami</span>
            <h1 class="text-3xl md:text-5xl font-extrabold mb-4">Tentang SWEAPING</h1>
            <p class="text-lg md:text-xl text-emerald-50 max-w-2xl mx-auto">
                Platform konservasi & akuntansi karbon untuk komunitas, organisasi, dan siapa saja yang peduli lingkungan.
            </p>
        </div>
    </section>

    <main class="max-w-4xl mx-auto px-4 py-16 -mt-10 relative z-10">
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-10 mb-6">
    <!-- Opening Story -->
    <div class="flex items-start gap-4 mb-5">
        <div class="feature-icon bg-emerald-100 text-emerald-700 flex-shrink-0"><i data-lucide="sprout"></i></div>
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-2">Apa itu SWEAPING?</h2>
            <!-- Arti Singkatan -->
<div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-4 md:p-5 border-2 border-emerald-200 mb-5">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
            <i data-lucide="book-open-text" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-1">SWEAPING adalah singkatan dari</div>
            <div class="text-base md:text-lg font-extrabold text-emerald-900 leading-tight mb-2">
                Swadaya, Edukasi & Aksi Peduli Lingkungan
            </div>
            <p class="text-xs md:text-sm text-emerald-800 leading-relaxed">
                Sebuah <b>program yang digagas oleh LSM KOMPAS</b> dalam mendukung keberlanjutan kebijakan lingkungan di Indonesia.
            </p>
        </div>
    </div>
</div>
            <p class="text-emerald-700 italic font-semibold mb-3">
                "Pernahkah Anda menanam pohon dan bertanya-tanya — berapa sih dampaknya untuk bumi?"
            </p>
            <p class="text-slate-600 leading-relaxed mb-3">
                SWEAPING menjawab pertanyaan itu. Ini adalah <b>platform konservasi & akuntansi karbon pertama di Indonesia</b> yang menghubungkan aksi lingkungan di lapangan dengan data ilmiah yang terukur.
            </p>
            <p class="text-slate-600 leading-relaxed">
                Dari <b>satu pohon di halaman rumah</b>, hingga <b>satu sungai yang dipatroli bersama</b> — setiap aksi bisa didokumentasikan, dihitung dampaknya, dan dibagikan ke publik.
            </p>
        </div>
    </div>

    <!-- 3 Keunggulan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-6">
        <div class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl p-4 border-2 border-emerald-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center mb-3 shadow-md">
                <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
            </div>
            <h3 class="font-bold text-slate-900 mb-1">🔬 Ilmiah</h3>
            <p class="text-xs text-slate-600 leading-relaxed">Hitung karbon dengan formula <b>Chave 2005</b> & <b>IPCC 2019</b> — diakui internasional.</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-white rounded-2xl p-4 border-2 border-blue-100">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center mb-3 shadow-md">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            </div>
            <h3 class="font-bold text-slate-900 mb-1">🎯 Multi-Aksi</h3>
            <p class="text-xs text-slate-600 leading-relaxed">Pohon, sungai, taman, kompos — semua dalam <b>satu platform</b>. Jarang ada.</p>
        </div>
        <div class="bg-gradient-to-br from-amber-50 to-white rounded-2xl p-4 border-2 border-amber-100">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center mb-3 shadow-md">
                <i data-lucide="heart-handshake" class="w-3.5 h-3.5"></i>
            </div>
            <h3 class="font-bold text-slate-900 mb-1">🆓 Gratis & Terbuka</h3>
            <p class="text-xs text-slate-600 leading-relaxed">Bisa dipakai <b>siapa saja</b> — komunitas, sekolah, LSM, individu.</p>
        </div>
    </div>

    <!-- Detail Teknis -->
    <div class="mt-6 bg-slate-50 rounded-2xl p-4 border border-slate-200">
        <div class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-3 flex items-center gap-2">
            <i data-lucide="cpu" class="w-3.5 h-3.5"></i> Untuk kalangan teknis & akademis
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-slate-600">
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Kalkulasi karbon otomatis (Chave 2005, IPCC 2019, RTH Konversi)</span>
            </div>
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Dokumentasi terstruktur: foto, GPS, tanggal, kondisi</span>
            </div>
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Privacy by design (publik/teman/privat)</span>
            </div>
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Multi-user dengan hierarki (User/Admin/Super Admin)</span>
            </div>
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Peta sebaran aksi interaktif (Leaflet + OSM)</span>
            </div>
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span>Mendukung Net Zero 2060 & SDGs 13, 15, 11</span>
            </div>
        </div>
    </div>

    <!-- Closing -->
    <p class="text-center text-sm text-emerald-700 font-semibold mt-5 italic">
        Bersama, kita rawat alam Indonesia. 🌱
    </p>
</div>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-10 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                <i data-lucide="target" class="w-6 h-6 text-emerald-700"></i> Fitur Utama
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="flex gap-3">
                    <div class="feature-icon bg-emerald-100 text-emerald-700 !w-12 !h-12 flex-shrink-0"><i data-lucide="file-text"></i></div>
                    <div><div class="font-bold text-slate-800">Pelaporan Mudah</div><div class="text-sm text-slate-600">Foto, GPS, catatan dalam 1 form</div></div>
                </div>
                <div class="flex gap-3">
                    <div class="feature-icon bg-amber-100 text-amber-700 !w-12 !h-12 flex-shrink-0"><i data-lucide="leaf"></i></div>
                    <div><div class="font-bold text-slate-800">Kalkulator Karbon</div><div class="text-sm text-slate-600">Estimasi serapan CO₂ otomatis</div></div>
                </div>
                <div class="flex gap-3">
                    <div class="feature-icon bg-blue-100 text-blue-700 !w-12 !h-12 flex-shrink-0"><i data-lucide="map"></i></div>
                    <div><div class="font-bold text-slate-800">Peta Interaktif</div><div class="text-sm text-slate-600">Visualisasi sebaran aksi</div></div>
                </div>
                <div class="flex gap-3">
                    <div class="feature-icon bg-purple-100 text-purple-700 !w-12 !h-12 flex-shrink-0"><i data-lucide="bar-chart-3"></i></div>
                    <div><div class="font-bold text-slate-800">Dashboard Statistik</div><div class="text-sm text-slate-600">Ringkasan dampak aksi</div></div>
                </div>
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-6 md:p-10 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                <i data-lucide="users" class="w-6 h-6 text-emerald-700"></i> Untuk Siapa?
            </h2>
            <ul class="space-y-3 text-slate-700">
                <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i> Komunitas lingkungan & relawan konservasi</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i> Sekolah & kampus dengan program lingkungan</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i> Lembaga sosial & organisasi masyarakat</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i> Kelompok tani, karang taruna, & PKK</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i> Pengelola komposter rumah tangga & komunal</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 mt-0.5"></i> Siapa saja yang peduli lingkungan</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-emerald-700 to-emerald-900 rounded-3xl p-6 md:p-10 text-white mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-14 h-14 glass-dark rounded-2xl flex items-center justify-center">
                    <i data-lucide="building-2" class="w-7 h-7"></i>
                </div>
                <div>
                    <div class="text-xs text-emerald-200 uppercase font-bold tracking-wide">Dikembangkan oleh</div>
                    <h2 class="text-xl md:text-2xl font-bold">LSM KOMPAS (Koordinat Masyarakat Pejuang Aspirasi)</h2>
                </div>
            </div>
            <p class="text-emerald-50 leading-relaxed mb-3">
                SWEAPING dikembangkan oleh <b>LSM KOMPAS</b> sebagai kontribusi untuk memperkuat gerakan lingkungan di Indonesia. Kami percaya dokumentasi yang rapi adalah langkah awal perubahan nyata.
            </p>
            <p class="text-emerald-50 leading-relaxed">
                Meskipun dikembangkan oleh LSM KOMPAS, <b>SWEAPING dapat digunakan oleh siapa saja</b> — komunitas, organisasi, maupun individu.
            </p>
        </div>

        <div class="text-center">
            <h3 class="text-xl font-bold text-slate-900 mb-3">Siap Memulai?</h3>
            <p class="text-slate-600 mb-5">Daftar gratis dan mulai dokumentasikan aksi lingkungan Anda.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <?php if(isset($_SESSION['user_id'])): ?>
                   <a href="index.php?page=objek_daftar" class="px-6 py-3.5 bg-emerald-700 text-white font-bold rounded-full shadow-lg hover:bg-emerald-800 flex items-center justify-center gap-2">
    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Tambah Objek
</a>
                <?php else: ?>
                    <a href="index.php?page=register" class="px-6 py-3.5 bg-emerald-700 text-white font-bold rounded-full shadow-lg hover:bg-emerald-800 flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Daftar Sekarang
                    </a>
                    <a href="index.php?page=login" class="px-6 py-3.5 bg-white border-2 border-emerald-700 text-emerald-700 font-bold rounded-full hover:bg-emerald-50 flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Masuk
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php elseif ($halaman == 'kontak'): ?>
    
    <section class="hero-bg pt-32 pb-20 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <span class="inline-block px-4 py-1.5 glass-dark rounded-full text-xs font-bold uppercase tracking-wide mb-4">Hubungi Kami</span>
            <h1 class="text-3xl md:text-5xl font-extrabold mb-4">Kontak Kami</h1>
            <p class="text-lg md:text-xl text-emerald-50 max-w-2xl mx-auto">
                Ada pertanyaan, saran, atau ingin bekerja sama? Kami siap mendengar.
            </p>
        </div>
    </section>

    <main class="max-w-5xl mx-auto px-4 py-16 -mt-10 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 text-center card-hover">
                <div class="feature-icon bg-emerald-100 text-emerald-700 mx-auto mb-3"><i data-lucide="mail"></i></div>
                <h3 class="font-bold mb-1">Email</h3>
                <a href="mailto:<?= ORG_EMAIL ?>" class="text-sm text-emerald-700 hover:underline"><?= ORG_EMAIL ?></a>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 text-center card-hover">
                <div class="feature-icon bg-blue-100 text-blue-700 mx-auto mb-3"><i data-lucide="globe"></i></div>
                <h3 class="font-bold mb-1">Website</h3>
                <a href="<?= ORG_WEBSITE ?>" target="_blank" class="text-sm text-emerald-700 hover:underline"><?= ORG_WEBSITE ?></a>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 text-center card-hover">
                <div class="feature-icon bg-amber-100 text-amber-700 mx-auto mb-3"><i data-lucide="map-pin"></i></div>
                <h3 class="font-bold mb-1">Lokasi</h3>
                <p class="text-sm text-slate-600">Indonesia</p>
            </div>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-10">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <i data-lucide="send" class="w-6 h-6 text-emerald-700"></i> Kirim Pesan
            </h2>
            <form action="index.php?page=kontak" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi_kontak" value="1">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nama Anda</label>
                        <input type="text" name="nama" required maxlength="150" value="<?= e($_SESSION['nama'] ?? '') ?>"
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Email</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Subjek</label>
                    <input type="text" name="subjek" required maxlength="200"
                        placeholder="Contoh: Kerja sama konservasi"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Pesan</label>
                    <textarea name="pesan" rows="6" required maxlength="2000"
                        placeholder="Tuliskan pesan Anda..."
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition"></textarea>
                </div>
                <button type="submit" class="w-full py-4 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i> Kirim Pesan
                </button>
            </form>
        </div>
    </main>

<?php elseif ($halaman == 'reset_password'): 
    // ============ HALAMAN RESET PASSWORD ============
    $token = trim($_GET['token'] ?? '');
    $error_msg = '';
    $user_data = null;
    
    if (empty($token) || strlen($token) !== 32) {
        $error_msg = 'Link tidak valid. Token tidak ditemukan atau format salah.';
    } else {
        // Cek token di database
        $stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, email, reset_expires FROM users WHERE reset_token = ? AND deleted_at IS NULL LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$user_data) {
            $error_msg = 'Link tidak valid atau sudah pernah dipakai.';
        } elseif (strtotime($user_data['reset_expires']) < time()) {
            $error_msg = 'Link sudah kadaluarsa. Silakan minta admin untuk membuat link baru.';
            $user_data = null;
        }
    }
?>
    <section class="hero-bg min-h-screen flex items-center justify-center pt-24 pb-16">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                
                <?php if($error_msg): ?>
                    <!-- TAMPILAN ERROR -->
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="x-circle" class="w-10 h-10 text-rose-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900 mb-2">Link Tidak Valid</h2>
                        <p class="text-sm text-slate-600 leading-relaxed"><?= e($error_msg) ?></p>
                    </div>
                    
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-6 text-xs text-amber-800">
                        <p class="font-bold mb-1">💡 Yang bisa Anda lakukan:</p>
                        <ul class="list-disc list-inside space-y-0.5 ml-1">
                            <li>Hubungi admin via WhatsApp untuk minta link baru</li>
                            <li>Link hanya berlaku 1 jam setelah dibuat</li>
                            <li>Link hanya bisa dipakai 1 kali</li>
                        </ul>
                    </div>
                    
                    <a href="index.php?page=login" class="w-full py-3.5 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Kembali ke Login
                    </a>
                    
                <?php else: ?>
                    <!-- TAMPILAN FORM RESET -->
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="key-round" class="w-10 h-10 text-amber-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900">Buat Password Baru</h2>
                        <p class="text-sm text-slate-500 mt-1">Untuk: <b><?= e($user_data['nama_lengkap']) ?></b></p>
                    </div>
                    
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-6 text-xs text-emerald-800">
                        <p class="font-bold mb-1">🔒 Tips Password Aman:</p>
                        <ul class="list-disc list-inside space-y-0.5 ml-1">
                            <li>Minimal 6 karakter</li>
                            <li>Kombinasi huruf & angka lebih baik</li>
                            <li>Jangan pakai tanggal lahir</li>
                        </ul>
                    </div>
                    
                    <form action="index.php" method="POST" class="space-y-4" onsubmit="return validasiResetPassword(this)">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi_simpan_password" value="1">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        
                        <div>
    <label class="block text-sm font-semibold mb-2">Password Baru</label>
    <div class="relative">
        <input type="password" name="password_baru" id="password_baru" required minlength="6" 
               autocomplete="new-password"
               class="w-full px-4 py-3 pr-12 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
        <button type="button" onclick="togglePass('password_baru', this)" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-emerald-600 transition"
                title="Lihat/Sembunyikan password" aria-label="Toggle password">
            <svg class="eye-icon w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off-icon w-3.5 h-3.5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
    </div>
</div>
                        
                        <div>
    <label class="block text-sm font-semibold mb-2">Konfirmasi Password</label>
    <div class="relative">
        <input type="password" name="password_konfirmasi" id="password_konfirmasi" required minlength="6"
               autocomplete="new-password"
               class="w-full px-4 py-3 pr-12 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
        <button type="button" onclick="togglePass('password_konfirmasi', this)" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-emerald-600 transition"
                title="Lihat/Sembunyikan password" aria-label="Toggle password">
            <svg class="eye-icon w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off-icon w-3.5 h-3.5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
    </div>
</div>
                        
                        <div id="msg-password" class="text-xs text-slate-500 hidden"></div>
                        
                        <button type="submit" class="w-full py-3.5 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i> Simpan Password Baru
                        </button>
                    </form>
                    
                    <script>
                    function validasiResetPassword(form) {
                        var p1 = document.getElementById('password_baru').value;
                        var p2 = document.getElementById('password_konfirmasi').value;
                        var msg = document.getElementById('msg-password');
                        
                        if (p1.length < 6) {
                            msg.textContent = '❌ Password minimal 6 karakter';
                            msg.className = 'text-xs text-rose-600 font-semibold';
                            msg.classList.remove('hidden');
                            return false;
                        }
                        if (p1 !== p2) {
                            msg.textContent = '❌ Konfirmasi password tidak sama';
                            msg.className = 'text-xs text-rose-600 font-semibold';
                            msg.classList.remove('hidden');
                            return false;
                        }
                        return true;
                    }
                    </script>
                <?php endif; ?>
                
                <p class="text-center text-sm text-slate-500 mt-6">
                    Ingat password? <a href="index.php?page=login" class="text-emerald-700 font-bold hover:underline">Masuk</a>
                </p>
            </div>
        </div>
    </section>


<?php elseif ($halaman == 'login'): ?>
    
    <section class="hero-bg min-h-screen flex items-center justify-center pt-24 pb-16">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <div class="text-center mb-6">
                    <img src="assets/logo-bulat.png" alt="SWEAPING" class="h-20 w-20 mx-auto mb-4 rounded-2xl" onerror="this.style.display='none'">
                    <h2 class="text-2xl font-bold text-slate-900">Masuk ke SWEAPING</h2>
                    <p class="text-sm text-slate-500 mt-1">Selamat datang kembali!</p>
                </div>
                
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-6 text-xs text-amber-800">
                    <b>Gunakan Akun Anda</b>  - Silahkan Daftar
                </div>
                
                <form action="index.php?page=login" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi_login" value="1">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Email</label>
                        <input type="email" name="email" required autocomplete="email"
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                    <div>
    <label class="block text-sm font-semibold mb-2">Kata Sandi</label>
    <div class="relative">
        <input type="password" name="password" id="password_login" required autocomplete="current-password"
            class="w-full px-4 py-3 pr-12 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
        <button type="button" onclick="togglePass('password_login', this)" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-emerald-600 transition"
                title="Lihat/Sembunyikan password" aria-label="Toggle password">
            <svg class="eye-icon w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off-icon w-3.5 h-3.5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
    </div>
</div>
                    <button type="submit" class="w-full py-3.5 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Masuk
                    </button>
                </form>
                
                <p class="text-center text-sm text-slate-500 mt-6">
                    Belum punya akun? <a href="index.php?page=register" class="text-emerald-700 font-bold hover:underline">Daftar</a>
                </p>
            </div>
        </div>
    </section>

<?php elseif ($halaman == 'register'): ?>
    
    <section class="hero-bg min-h-screen flex items-center justify-center pt-24 pb-16">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <div class="text-center mb-6">
                    <img src="assets/logo-bulat.png" alt="SWEAPING" class="h-20 w-20 mx-auto mb-4 rounded-2xl" onerror="this.style.display='none'">
                    <h2 class="text-2xl font-bold text-slate-900">Daftar Akun Kader</h2>
                    <p class="text-sm text-slate-500 mt-1">Gratis dan mudah</p>
                </div>
                
                <form action="index.php?page=register" method="POST" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="aksi_register" value="1">
    <div>
        <label class="block text-sm font-semibold mb-2">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" required maxlength="150" autocomplete="name"
            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
    </div>
    <div>
    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
        <i data-lucide="building-2" class="w-3.5 h-3.5 text-emerald-600"></i> 
        Organisasi / Lembaga 
        <span class="text-slate-400 font-normal text-xs">(opsional)</span>
    </label>
    <input type="text" name="organisasi" maxlength="150" 
        placeholder="Contoh: LSM KOMPAS, Komunitas Hijau Bandung"
        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
    <p class="text-xs text-slate-500 mt-1">Isi jika Anda bergabung dengan komunitas tertentu. Akan muncul di papan peringkat organisasi.</p>
</div>
    <div>
        <label class="block text-sm font-semibold mb-2">Email</label>
        <input type="email" name="email" required autocomplete="email"
            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
            <i data-lucide="phone" class="w-3.5 h-3.5"></i> Nomor WhatsApp / Telepon
        </label>
        <div class="flex">
            <span class="inline-flex items-center px-4 border-2 border-r-0 border-slate-200 rounded-l-xl bg-slate-50 text-sm font-semibold text-slate-600">
                +62
            </span>
            <input type="tel" name="telepon" required autocomplete="tel" 
                inputmode="numeric"
                placeholder="81234567890"
                pattern="[0-9]{9,13}"
                title="Masukkan nomor tanpa angka 0 di depan, contoh: 81234567890"
                class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-r-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
        </div>
        <p class="text-xs text-slate-500 mt-1">Tanpa angka 0 di depan. Contoh: <b>81234567890</b></p>
    </div>
    <div>
    <label class="block text-sm font-semibold mb-2">Kata Sandi (min. 6 karakter)</label>
    <div class="relative">
        <input type="password" name="password" id="password_register" required minlength="6" autocomplete="new-password"
            class="w-full px-4 py-3 pr-12 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
        <button type="button" onclick="togglePass('password_register', this)" 
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-emerald-600 transition"
                title="Lihat/Sembunyikan password" aria-label="Toggle password">
            <svg class="eye-icon w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="eye-off-icon w-3.5 h-3.5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
    </div>
</div>
    <button type="submit" class="w-full py-3.5 bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-800 transition flex items-center justify-center gap-2">
        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Daftar Sekarang
    </button>
</form>
                
                <p class="text-center text-sm text-slate-500 mt-6">
                    Sudah punya akun? <a href="index.php?page=login" class="text-emerald-700 font-bold hover:underline">Masuk</a>
                </p>
            </div>
        </div>
    </section>

<?php elseif ($halaman == 'lapor'): ?>
    <?php if (!isset($_SESSION['user_id'])): 
        notif('gagal','Silakan login untuk mengakses form laporan.');
        redirect('index.php?page=login');
    else: ?>
        <?php
    // ============ AMBIL DATA OBJEK DARI objek_id ============
    $lapor_objek_id = (int)($_GET['objek_id'] ?? 0);
    $lapor_objek = null;
    
    if ($lapor_objek_id > 0) {
        $stmt_lo = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        mysqli_stmt_bind_param($stmt_lo, 'i', $lapor_objek_id);
        mysqli_stmt_execute($stmt_lo);
        $lapor_objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_lo));
        mysqli_stmt_close($stmt_lo);
    }
    
    // Kalau objek_id tidak valid → redirect ke halaman objek
    if ($lapor_objek_id > 0 && !$lapor_objek) {
        notif('gagal', 'Objek tidak ditemukan atau sudah dihapus.');
        redirect('index.php?page=objek');
    }
    
    // Kalau tidak ada objek_id → redirect ke daftar objek
    if ($lapor_objek_id <= 0) {
        notif('gagal', 'Silakan pilih objek untuk dilaporkan.');
        redirect('index.php?page=objek');
    }
    ?>
    <main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="file-plus" class="w-7 h-7 text-emerald-700"></i> Formulir Laporan
            </h1>
            <p class="text-sm text-slate-500">Foto maks 8 MB, otomatis dikompres. Format: JPG/PNG/WEBP.</p>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="return validasiFormLapor(this)">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi_simpan" value="1">
                
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">1</div>
                    <div class="font-bold text-emerald-800">Jenis & Objek</div>
                </div>
             <input type="hidden" name="objek_id" value="<?= (int)($_GET['objek_id'] ?? 0) ?>">
             <?php
                $jenis_icons = [
                    'pohon'      => ['ikon' => 'tree-pine', 'label' => 'Pohon',      'warna' => 'emerald'],
                    'sungai'     => ['ikon' => 'waves',     'label' => 'Sungai',     'warna' => 'blue'],
                    'taman'      => ['ikon' => 'flower-2',  'label' => 'Taman',      'warna' => 'lime'],
                    'komposting' => ['ikon' => 'recycle',   'label' => 'Komposting', 'warna' => 'amber'],
                ];
                $lj = $jenis_icons[$lapor_objek['jenis_objek']] ?? $jenis_icons['pohon'];
                ?>
                <div>
                    <label class="block text-sm font-semibold mb-2">Jenis Objek <span class="text-slate-400 font-normal">(terkunci dari data objek)</span></label>
                    <div class="bg-slate-50 border-2 border-<?= $lj['warna'] ?>-200 rounded-xl p-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-<?= $lj['warna'] ?>-100 text-<?= $lj['warna'] ?>-700 flex items-center justify-center">
                            <i data-lucide="<?= $lj['ikon'] ?>" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-slate-500 font-semibold uppercase">Jenis Objek</div>
                            <div class="font-bold text-slate-800"><?= e($lj['label']) ?></div>
                        </div>
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                    <input type="hidden" name="jenis_objek" value="<?= e($lapor_objek['jenis_objek']) ?>">
                </div>
                <?php 
// Hitung umur otomatis dari tanggal tanam objek (server-side, konsisten dgn profil)
$tgl_tanam_obj   = $lapor_objek['tanggal_tanam'] ?? '';
$tgl_tanam_valid = ($tgl_tanam_obj && $tgl_tanam_obj !== '0000-00-00');
$umur_sekarang   = $tgl_tanam_valid ? hitung_umur($tgl_tanam_obj, date('Y-m-d')) : null;
?>
<div id="box_umur_pohon" class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-4 rounded-xl border-2 border-emerald-200">
    <label class="block text-sm font-semibold text-emerald-900 mb-1 flex items-center gap-2">
        <i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i> Umur Pohon (Otomatis)
    </label>
    <p class="text-xs text-emerald-700 mb-3">Dihitung otomatis dari tanggal tanam objek — tidak perlu diisi manual.</p>

    <?php if($umur_sekarang): ?>
        <div class="bg-white border-2 border-emerald-300 rounded-xl p-4">
            <div class="text-lg md:text-xl font-extrabold text-emerald-800 leading-tight">
                <?= e($umur_sekarang['teks_full']) ?>
            </div>
            <div class="text-[11px] text-emerald-600 mt-2 flex items-center gap-1.5 flex-wrap">
                <i data-lucide="info" class="w-3 h-3"></i>
                <span>Tanam: <b><?= date('d F Y', strtotime($tgl_tanam_obj)) ?></b></span>
                <span class="text-emerald-400">·</span>
                <span>Total: <b><?= number_format($umur_sekarang['total_hari']) ?> hari</b></span>
            </div>
        </div>
        <input type="hidden" name="umur_bulan" value="<?= (int)($umur_sekarang['total_hari'] / 30) ?>">
    <?php else: ?>
        <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-3">
            <p class="text-xs text-amber-800 font-semibold flex items-start gap-1.5">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
                <span>Tanggal tanam belum diisi di master objek. Umur tidak dapat dihitung otomatis.</span>
            </p>
            <a href="index.php?page=objek_edit&id=<?= (int)$lapor_objek['id'] ?>" 
               class="inline-flex items-center gap-1 mt-2 text-xs text-amber-900 font-bold underline">
                → Isi tanggal tanam di profil objek
            </a>
        </div>
        <input type="hidden" name="umur_bulan" value="0">
    <?php endif; ?>
</div>

                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI POHON — MULAI        -->
                <!-- ============================================ -->
                <div id="box_monitoring_pohon" class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-200 p-5 shadow-sm space-y-4">

                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-emerald-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-emerald-600">Kondisi & pengukuran pohon</div>
                        </div>
                    </div>

                    <!-- Waktu Monitoring -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Pengukuran -->
                    <div>
                        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-2">Pengukuran</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Diameter Batang (cm)</label>
                                <input type="number" name="diameter_batang" step="0.1" min="0" max="9999" inputmode="decimal" placeholder="Contoh: 25.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tinggi Pohon (m)</label>
                                <input type="number" name="tinggi_pohon" step="0.1" min="0" max="999" inputmode="decimal" placeholder="Contoh: 8.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lebar Kanopi (m)</label>
                                <input type="number" name="lebar_kanopi" step="0.1" min="0" max="999" inputmode="decimal" placeholder="Contoh: 4.2"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                            <!-- LIVE CALCULATOR KARBON -->
                            <div id="live-calc-karbon" class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl border-2 border-emerald-300 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i data-lucide="calculator" class="w-4 h-4 text-emerald-700"></i>
                                    <div class="font-bold text-emerald-900 text-sm">Kalkulator Karbon (Chave 2005)</div>
                                </div>
                                <p class="text-xs text-emerald-700 mb-3">Estimasi otomatis dari Diameter Batang & Tinggi Pohon yang Anda isi.</p>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white rounded-xl p-3 border border-emerald-200 text-center">
                                        <div class="text-[10px] text-emerald-600 font-semibold uppercase">AGB</div>
                                        <div id="calc-agb" class="text-base md:text-lg font-extrabold text-emerald-800 leading-tight">0.00</div>
                                        <div class="text-[10px] text-emerald-500">kg biomassa</div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 border border-emerald-200 text-center">
                                        <div class="text-[10px] text-emerald-600 font-semibold uppercase">Karbon (C)</div>
                                        <div id="calc-karbon" class="text-base md:text-lg font-extrabold text-emerald-800 leading-tight">0.00</div>
                                        <div class="text-[10px] text-emerald-500">kg C</div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 border-2 border-amber-300 text-center shadow-sm">
                                        <div class="text-[10px] text-amber-700 font-bold uppercase">Serapan CO₂</div>
                                        <div id="calc-co2" class="text-lg md:text-2xl font-extrabold text-amber-700 leading-tight">0.00</div>
                                        <div class="text-[10px] text-amber-600">kg CO₂-eq</div>
                                    </div>
                                </div>
                            </div>
                            <!-- END LIVE CALCULATOR KARBON -->
                            
                    
                    <!-- Lingkungan Tumbuh -->
                    <div>
                        
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lingkungan Tumbuh</label>
                        <select name="lingkungan_tumbuh" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">-- Pilih --</option>
                            <option value="Terbuka (full sun)">Terbuka (full sun)</option>
                            <option value="Setengah ternaung">Setengah ternaung</option>
                            <option value="Ternaung">Ternaung (di bawah kanopi)</option>
                            <option value="Dekat bangunan/jalan">Dekat bangunan/jalan</option>
                            <option value="Dekat sumber air">Dekat sumber air</option>
                            <option value="Lahan basah">Lahan basah</option>
                            <option value="Lahan kering">Lahan kering</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Kondisi (5 Level) -->
                    <div>
                        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-2">Kondisi (5 Tingkat: Sangat Baik → Sangat Buruk)</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php
                            $level_5 = ['Sangat Baik', 'Baik', 'Cukup', 'Buruk', 'Sangat Buruk'];
                            $kondisi_fields = [
                                'kondisi_pertumbuhan' => 'Kondisi Pertumbuhan',
                                'kondisi_batang'      => 'Kondisi Batang',
                                'kondisi_kanopi'      => 'Kondisi Kanopi',
                                'kondisi_akar'        => 'Kondisi Akar',
                                'kondisi_cabang'      => 'Kondisi Cabang',
                                'kondisi_daun'        => 'Kondisi Daun',
                                'kondisi_buah'        => 'Kondisi Buah',
                                'kondisi_bunga'       => 'Kondisi Bunga',
                                'kondisi_lainnya'     => 'Kondisi Lainnya',
                            ];
                            foreach ($kondisi_fields as $name => $label): ?>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700"><?= $label ?></label>
                                    <select name="<?= $name ?>" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach ($level_5 as $lvl): ?>
                                            <option value="<?= $lvl ?>"><?= $lvl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Penilaian Kesehatan -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Penilaian Kesehatan</label>
                        <select name="penilaian_kesehatan" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">-- Pilih --</option>
                            <option value="Sehat">Sehat</option>
                            <option value="Rawan">Rawan</option>
                            <option value="Kritis">Kritis</option>
                            <option value="Mati">Mati</option>
                        </select>
                    </div>

                    <!-- Potensi Dampak & Risiko -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="3" maxlength="1000" placeholder="Contoh: Risiko tumbang saat musim hujan, cabang mengganggu kabel listrik..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition"></textarea>
                    </div>

                    <!-- Rekomendasi -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="3" maxlength="500" placeholder="Contoh: Perlu perawatan rutin, perlu pemangkasan cabang, perlu penebangan..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition"></textarea>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI POHON — SELESAI      -->
                <!-- ============================================ -->
                
                                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI SUNGAI — MULAI       -->
                <!-- ============================================ -->
                <div id="box_monitoring_sungai" style="display:none;" class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-200 p-5 shadow-sm space-y-4 mt-4">

                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-blue-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-blue-600">Kondisi & pengukuran sungai</div>
                        </div>
                    </div>

                    <!-- Waktu Monitoring -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                    </div>

                    <!-- C. DATA MONITORING -->
                    <div>
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">C. Data Monitoring</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Debit Normal (m³/s)</label>
                                <input type="number" name="debit_air_normal" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 2.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Debit Pasang (m³/s)</label>
                                <input type="number" name="debit_air_pasang" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 3.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Debit Surut (m³/s)</label>
                                <input type="number" name="debit_air_surut" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 1.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tinggi Muka Air (m)</label>
                                <input type="number" name="tinggi_muka_air" step="0.01" min="0" max="999" inputmode="decimal" placeholder="Contoh: 0.8"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lebar Sungai (m)</label>
                                <input type="number" name="lebar_sungai_monitor" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 8.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kedalaman Air (m)</label>
                                <input type="number" name="kedalaman_air" step="0.01" min="0" max="999" inputmode="decimal" placeholder="Contoh: 1.2"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- D. INDIKATOR KONDISI -->
                    <div>
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">D. Indikator Kondisi</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kondisi Debit</label>
                                <select name="kondisi_debit" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Rendah">Rendah</option>
                                    <option value="Tinggi">Tinggi</option>
                                    <option value="Sangat tinggi">Sangat Tinggi</option>
                                    <option value="Kering">Kering</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kondisi Air (Visual)</label>
                                <select name="kondisi_air_visual" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Jernih">Jernih</option>
                                    <option value="Agak keruh">Agak Keruh</option>
                                    <option value="Keruh">Keruh</option>
                                    <option value="Sangat keruh">Sangat Keruh</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Warna Air</label>
                                <select name="warna_air" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Kecokelatan">Kecokelatan</option>
                                    <option value="Kehitaman">Kehitaman</option>
                                    <option value="Kehijauan">Kehijauan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Bau Air</label>
                                <select name="bau_air" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Tidak berbau">Tidak Berbau</option>
                                    <option value="Sedikit berbau">Sedikit Berbau</option>
                                    <option value="Berbau">Berbau</option>
                                    <option value="Sangat menyengat">Sangat Menyengat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Sampah</label>
                                <select name="sampah_sungai" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Tidak ada">Tidak Ada</option>
                                    <option value="Sedikit">Sedikit</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Banyak">Banyak</option>
                                    <option value="Sangat banyak">Sangat Banyak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Vegetasi Sempadan</label>
                                <select name="vegetasi_sempadan" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Rusak">Rusak</option>
                                    <option value="Sangat rusak">Sangat Rusak</option>
                                    <option value="Tidak ada">Tidak Ada</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Erosi / Longsor</label>
                                <select name="erosi_sungai" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Tidak ada">Tidak Ada</option>
                                    <option value="Ringan">Ringan</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Berat">Berat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- KUALITAS AIR (Opsional) -->
                    <div>
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">Kualitas Air <span class="text-slate-400 font-normal lowercase">(opsional — isi jika ada alat ukur)</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">pH</label>
                                <input type="number" name="kualitas_ph" step="0.1" min="0" max="14" inputmode="decimal" placeholder="7.0"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">Suhu (°C)</label>
                                <input type="number" name="kualitas_suhu" step="0.1" min="0" max="100" inputmode="decimal" placeholder="28.5"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">TDS (mg/L)</label>
                                <input type="number" name="kualitas_tds" step="1" min="0" max="999999" inputmode="numeric" placeholder="200"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">DO (mg/L)</label>
                                <input type="number" name="kualitas_do" step="0.1" min="0" max="30" inputmode="decimal" placeholder="6.5"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">Kekeruhan (NTU)</label>
                                <input type="number" name="kualitas_kekeruhan" step="0.1" min="0" max="99999" inputmode="decimal" placeholder="15.0"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                                            <!-- LIVE CALCULATOR CH4 SUNGAI -->
                        <div id="live-calc-ch4" class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-300 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-blue-700"></i>
                                <div class="font-bold text-blue-900 text-sm">Estimasi Emisi CH₄ (IPCC 2019)</div>
                            </div>
                            <p class="text-xs text-blue-700 mb-3">Otomatis dari DO, Sampah, dan Kondisi Visual Air.</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white rounded-xl p-3 border-2 border-blue-300 text-center shadow-sm">
                                    <div class="text-[10px] text-blue-700 font-bold uppercase">Potensi CH₄</div>
                                    <div id="ch4-value" class="text-lg md:text-2xl font-extrabold text-blue-800 leading-tight">0.00</div>
                                    <div class="text-[10px] text-blue-600">kg CO₂-eq</div>
                                </div>
                                <div class="bg-white rounded-xl p-3 border border-blue-200 text-center">
                                    <div class="text-[10px] text-blue-600 font-semibold uppercase">Level Risiko</div>
                                    <div id="ch4-level" class="text-base md:text-lg font-extrabold text-slate-500 leading-tight">—</div>
                                    <div class="text-[10px] text-blue-500">Berdasarkan IPCC</div>
                                </div>
                            </div>
                        </div>
                        <!-- END LIVE CALCULATOR CH4 SUNGAI -->
                        

                    <!-- RISIKO & REKOMENDASI -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="2" maxlength="1000" placeholder="Contoh: Pencemaran limbah domestik, sampah menumpuk di hilir..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" maxlength="500" placeholder="Contoh: Pembersihan sampah rutin, penanaman sempadan..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"></textarea>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI SUNGAI — SELESAI     -->
                <!-- ============================================ -->
                
                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI TAMAN — MULAI        -->
                <!-- ============================================ -->
                <div id="box_monitoring_taman" style="display:none;" class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-200 p-5 shadow-sm space-y-4 mt-4">

                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-lime-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-lime-700">Kondisi taman</div>
                        </div>
                    </div>

                    <!-- Tanggal Monitoring -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                    </div>

                    <!-- E. KONDISI TAMAN -->
                    <div>
                        <div class="text-xs font-bold text-lime-700 uppercase tracking-wide mb-2">E. Kondisi Taman</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kondisi Vegetasi</label>
                                <select name="kondisi_vegetasi" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Sangat Baik">Sangat Baik</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Rusak">Rusak</option>
                                    <option value="Sangat Rusak">Sangat Rusak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kondisi Fasilitas</label>
                                <select name="kondisi_fasilitas" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Sangat Baik">Sangat Baik</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Rusak">Rusak</option>
                                    <option value="Tidak Berfungsi">Tidak Berfungsi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kebersihan</label>
                                <select name="kebersihan_taman" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Sangat Bersih">Sangat Bersih</option>
                                    <option value="Bersih">Bersih</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Kotor">Kotor</option>
                                    <option value="Sangat Kotor">Sangat Kotor</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kondisi Lingkungan</label>
                                <select name="kondisi_lingkungan" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Teduh">Teduh</option>
                                    <option value="Cukup Teduh">Cukup Teduh</option>
                                    <option value="Kurang Teduh">Kurang Teduh</option>
                                    <option value="Panas">Panas</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Drainase</label>
                                <select name="kondisi_drainase" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Tersumbat">Tersumbat</option>
                                    <option value="Rusak">Rusak</option>
                                    <option value="Tidak Tersedia">Tidak Tersedia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- RISIKO & REKOMENDASI -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="2" maxlength="1000" placeholder="Contoh: Fasilitas rusak, sampah menumpuk, pohon rawan tumbang..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" maxlength="500" placeholder="Contoh: Perbaikan fasilitas, pembersihan rutin, penambahan tanaman..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition"></textarea>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI TAMAN — SELESAI      -->
                <!-- ============================================ -->
                
<div id="box_komposting" style="display:none;" class="bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-xl border-2 border-amber-200">
    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="recycle" class="w-3.5 h-3.5 text-amber-700"></i>
        <div class="font-bold text-amber-900">Data Komposting</div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-semibold text-amber-900 mb-1">Volume Sampah Organik (kg/bulan)</label>
            <input type="number" name="volume_sampah" value="0" min="0" max="10000" inputmode="numeric"
                class="w-full px-4 py-3 border-2 border-amber-300 rounded-xl bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-amber-900 mb-1">Hasil Kompos (kg/bulan)</label>
            <input type="number" name="hasil_kompos" value="0" min="0" max="10000" inputmode="numeric"
                class="w-full px-4 py-3 border-2 border-amber-300 rounded-xl bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
        </div>
    </div>
    
                        <!-- LIVE CALCULATOR CO2 KOMPOS (EDIT) -->
                    <div id="live-calc-kompos" class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border-2 border-amber-300 p-4 mt-3">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="calculator" class="w-4 h-4 text-amber-700"></i>
                            <div class="font-bold text-amber-900 text-sm">Kalkulator CO₂ Dicegah (IPCC)</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded-xl p-3 border border-amber-200 text-center">
                                <div class="text-[10px] text-amber-600 font-semibold uppercase">Sampah Organik</div>
                                <div id="kompos-calc-input" class="text-base md:text-lg font-extrabold text-amber-800 leading-tight">0</div>
                                <div class="text-[10px] text-amber-500">kg/bulan</div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border-2 border-amber-400 text-center shadow-sm">
                                <div class="text-[10px] text-amber-700 font-bold uppercase">CO₂ Dicegah</div>
                                <div id="kompos-calc-co2" class="text-lg md:text-2xl font-extrabold text-amber-700 leading-tight">0.00</div>
                                <div class="text-[10px] text-amber-600">kg CO₂-eq</div>
                            </div>
                        </div>
                        <p class="text-[10px] text-amber-600 mt-2 text-center italic">Rumus IPCC: 0.7 kg CO₂ per 1 kg sampah organik dikompos.</p>
                    </div>
                    <!-- END LIVE CALCULATOR CO2 KOMPOS (EDIT) -->
    
    <p class="text-xs text-amber-700 mt-3">💡 Sistem akan hitung otomatis CO₂ dicegah dari volume sampah.</p>
</div>
                <!-- ============================================ -->
                <!-- D-G. MONITORING KOMPOSTING LENGKAP — MULAI    -->
                <!-- ============================================ -->
                <div id="box_monitoring_komposting" style="display:none;" class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-200 p-5 shadow-sm space-y-4 mt-4">

                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-amber-900">D-G. Monitoring Komposting</div>
                            <div class="text-xs text-amber-700">Kondisi, hasil, pemanfaatan, dampak</div>
                        </div>
                    </div>

                    <!-- TANGGAL MONITORING -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                    </div>

                    <!-- D. MONITORING KONDISI -->
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">D. Monitoring Kondisi</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kelembapan</label>
                                <select name="kompos_kelembapan" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Buruk">Buruk</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Suhu (°C)</label>
                                <input type="number" name="kompos_suhu" step="0.1" min="0" max="100" inputmode="decimal" placeholder="Contoh: 45.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Aerasi</label>
                                <select name="kompos_aerasi" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Buruk">Buruk</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Bau</label>
                                <select name="kompos_bau" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Tidak berbau">Tidak Berbau</option>
                                    <option value="Sedikit">Sedikit</option>
                                    <option value="Berbau">Berbau</option>
                                    <option value="Menyengat">Menyengat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Hama / Lalat</label>
                                <select name="kompos_hama" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Tidak ada">Tidak Ada</option>
                                    <option value="Sedikit">Sedikit</option>
                                    <option value="Banyak">Banyak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kecepatan Penguraian</label>
                                <select name="kompos_kecepatan_penguraian" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Cepat">Cepat</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Lambat">Lambat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kondisi Wadah</label>
                                <select name="kompos_kondisi_wadah" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Buruk">Buruk</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kebersihan Area</label>
                                <select name="kompos_kebersihan_area" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Buruk">Buruk</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- E. HASIL KOMPOSTING -->
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">E. Hasil Komposting</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status</label>
                                <select name="kompos_status" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Belum mulai">Belum Mulai</option>
                                    <option value="Sedang berlangsung">Sedang Berlangsung</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Gagal">Gagal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Warna Kompos</label>
                                <select name="kompos_warna" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Cokelat tua">Cokelat Tua</option>
                                    <option value="Cokelat muda">Cokelat Muda</option>
                                    <option value="Hitam">Hitam</option>
                                    <option value="Kehijauan">Kehijauan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tekstur</label>
                                <select name="kompos_tekstur" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Halus">Halus</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Kasar">Kasar</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Aroma</label>
                                <select name="kompos_aroma" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Seperti tanah">Seperti Tanah</option>
                                    <option value="Tidak berbau">Tidak Berbau</option>
                                    <option value="Sedikit berbau">Sedikit Berbau</option>
                                    <option value="Menyengat">Menyengat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tingkat Kematangan</label>
                                <select name="kompos_kematangan" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Matang">Matang</option>
                                    <option value="Setengah matang">Setengah Matang</option>
                                    <option value="Belum matang">Belum Matang</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kebersihan dari Anorganik</label>
                                <select name="kompos_kebersihan_anorganik" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Bersih">Bersih</option>
                                    <option value="Sedikit kotor">Sedikit Kotor</option>
                                    <option value="Kotor">Kotor</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Volume Bahan Awal (kg)</label>
                                <input type="number" name="kompos_volume_awal" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 100"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Volume Kompos Akhir (kg)</label>
                                <input type="number" name="kompos_volume_akhir" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 40"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Berat Kompos (kg)</label>
                                <input type="number" name="kompos_berat_kompos" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 40"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- F. PEMANFAATAN -->
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">F. Pemanfaatan</div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm mb-3">
                            <?php 
                            $pemanfaatan_kompos = ['Taman','Pohon','Pertanian','Pembibitan','Tanaman rumah','Dijual','Dibagikan','Lainnya'];
                            foreach ($pemanfaatan_kompos as $pk): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="kompos_pemanfaatan_arr[]" value="<?= $pk ?>" class="peer hidden">
                                    <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 text-xs peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold hover:border-amber-300 transition text-center">
                                        <?= $pk ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jumlah Dimanfaatkan (kg)</label>
                                <input type="number" name="kompos_jumlah_dimanfaatkan" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 30"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jumlah Dijual (kg)</label>
                                <input type="number" name="kompos_jumlah_dijual" step="0.01" min="0" max="99999" inputmode="decimal" placeholder="Contoh: 10"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nilai Penjualan (Rp)</label>
                                <input type="number" name="kompos_nilai_penjualan" step="1" min="0" max="999999999" inputmode="numeric" placeholder="Contoh: 150000"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- G. DAMPAK & KEBERLANJUTAN -->
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">G. Dampak & Keberlanjutan</div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Dampak Lingkungan</label>
                                <textarea name="kompos_dampak" rows="2" maxlength="1000" placeholder="Contoh: Pengurangan sampah organik 50 kg/bulan, kompos untuk taman..."
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status Operasional</label>
                                <select name="kompos_status_operasional" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                                    <option value="">-- Pilih --</option>
                                    <option value="Aktif dan rutin">🟢 Aktif dan Rutin</option>
                                    <option value="Aktif tetapi tidak rutin">🟡 Aktif tetapi Tidak Rutin</option>
                                    <option value="Berjalan terbatas">🟠 Berjalan Terbatas</option>
                                    <option value="Tidak beroperasi">🔴 Tidak Beroperasi</option>
                                    <option value="Rusak">⚫ Rusak / Tidak Dapat Digunakan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Faktor Keberlanjutan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                    <?php
                                    $faktor_list = ['Pengelola aktif','Bahan baku tersedia','Biaya operasional','Peralatan memadai','Ada pasar kompos','Dukungan masyarakat','Dukungan pemerintah','Dukungan pihak lain'];
                                    foreach ($faktor_list as $fk): ?>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="kompos_faktor_arr[]" value="<?= $fk ?>" class="peer hidden">
                                            <div class="px-2 py-2 bg-white border-2 border-slate-200 rounded-lg text-slate-600 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold transition text-center">
                                                <?= $fk ?>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RISIKO & REKOMENDASI (SHARED) -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="2" maxlength="1000" placeholder="Contoh: Bau tidak sedap, hama lalat, gagal fermentasi..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" maxlength="500" placeholder="Contoh: Pengadukan rutin, penambahan bahan kering, pemisahan sampah..."
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition"></textarea>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- D-G. MONITORING KOMPOSTING LENGKAP — SELESAI   -->
                <!-- ============================================ -->
                
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">2</div>
                    <div class="font-bold text-emerald-800">Kondisi Objek</div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-blue-600"></i> PIC Monev
                    </label>
                    <p class="text-xs text-slate-500 mb-3">Penanggung jawab monitoring & evaluasi kali ini.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama PIC</label>
                            <input type="text" name="pic_monev_nama" maxlength="150" placeholder="Nama lengkap penanggung jawab"
                                value="<?= e($_SESSION['nama'] ?? '') ?>"
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Peran / Jabatan</label>
                            <select name="pic_monev_peran" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                <option value="">-- Pilih --</option>
                                <option value="Koordinator">Koordinator</option>
                                <option value="Peneliti">Peneliti</option>
                                <option value="Perekayasa">Perekayasa</option>
                                <option value="Arboris">Arboris</option>
                                <option value="Analis">Analis</option>
                                <option value="Pengelola">Pengelola</option>
                                <option value="Pendamping">Pendamping</option>
                                <option value="Penggiat/Pemerhati">Penggiat/Pemerhati</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div>
    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
        <i data-lucide="camera" class="w-3.5 h-3.5"></i> Foto Kondisi (maks 8 MB)
    </label>

    <!-- Input tersembunyi: untuk kamera -->
    <input type="file" name="foto" id="input_foto_kamera"
           accept="image/*" capture="environment"
           onchange="pilihFoto(this)" style="display:none;">

    <!-- Input tersembunyi: untuk galeri -->
    <input type="file" id="input_foto_galeri"
           accept="image/jpeg,image/png,image/webp"
           onchange="pilihFoto(this)" style="display:none;">

    <!-- Dua tombol -->
    <div class="grid grid-cols-2 gap-3 mb-3">
        <button type="button" onclick="document.getElementById('input_foto_kamera').click()"
            class="py-3 px-4 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition flex items-center justify-center gap-2">
            <i data-lucide="camera" class="w-3.5 h-3.5"></i> Kamera
        </button>
        <button type="button" onclick="document.getElementById('input_foto_galeri').click()"
            class="py-3 px-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
            <i data-lucide="image" class="w-3.5 h-3.5"></i> Galeri
        </button>
    </div>

    <!-- Preview -->
    <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center bg-slate-50">
        <img id="preview_foto" src="" alt="" style="display:none; max-height:200px; margin:0 auto; border-radius:12px;">
        <p id="info_foto" class="text-xs text-slate-500">Belum ada foto dipilih</p>
    </div>
</div>
                
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">3</div>
                    <div class="font-bold text-emerald-800">Kontributor</div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nama Kontributor</label>
                        <input type="text" name="nama_kontributor" required maxlength="150" value="<?= e($_SESSION['nama']) ?>" 
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Peran</label>
                        <input type="text" name="peran_kontributor" required maxlength="100" placeholder="Patroli Rutin / Penanam" 
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

<div class="mt-4">
    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
        <i data-lucide="eye" class="w-3.5 h-3.5 text-emerald-600"></i> Siapa yang Bisa Lihat Laporan Ini?
    </label>
    <p class="text-xs text-slate-500 mb-3">Pilih tingkat privasi sesuai kebutuhan Anda.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        
        <!-- PUBLIK -->
        <label class="cursor-pointer group">
            <input type="radio" name="visibility" value="publik" class="peer hidden" checked>
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-emerald-600 peer-checked:bg-emerald-50 hover:border-emerald-300 transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center peer-checked:bg-emerald-600 peer-checked:text-white transition">
                        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="font-bold text-slate-800 text-sm">🌍 Publik</div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">Tampil di beranda & arsip. Siapa saja bisa lihat.</p>
            </div>
        </label>

        <!-- TEMAN -->
        <label class="cursor-pointer group">
            <input type="radio" name="visibility" value="teman" class="peer hidden">
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-amber-300 transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center peer-checked:bg-amber-500 peer-checked:text-white transition">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="font-bold text-slate-800 text-sm">👥 Teman</div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">Hanya Anda & user yang saling follow.</p>
            </div>
        </label>

        <!-- PRIVAT -->
        <label class="cursor-pointer group">
            <input type="radio" name="visibility" value="privat" class="peer hidden">
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-slate-700 peer-checked:bg-slate-100 hover:border-slate-400 transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center peer-checked:bg-slate-700 peer-checked:text-white transition">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="font-bold text-slate-800 text-sm">🔒 Privat</div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">Hanya Anda yang bisa lihat.</p>
            </div>
        </label>

    </div>
</div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Catatan Lapangan</label>
                    <textarea name="catatan" rows="4" required maxlength="2000" placeholder="Deskripsikan kondisi objek, temuan, dan tindak lanjut..."
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>
                
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-700 to-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i> Kirim Laporan
                </button>
            </form>
        </div>
    </main>
    
    <?php endif; ?>
    
    <?php elseif ($halaman == 'objek_edit'): ?>
    <?php
    if (!isset($_SESSION['user_id'])) { 
        notif('gagal','Silakan login.'); 
        redirect('index.php?page=login'); 
    }
    
    $id = (int)($_GET['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$objek) { 
        notif('gagal', 'Objek tidak ditemukan.'); 
        redirect('index.php?page=objek'); 
    }
    
    if ($objek['user_id'] != $_SESSION['user_id'] && !is_admin()) { 
        notif('gagal', 'Tidak berhak mengedit objek ini.'); 
        redirect('index.php?page=objek'); 
    }
    ?>
    <main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
        <a href="index.php?page=objek_profil&id=<?= $objek['id'] ?>" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Profil Objek
        </a>
        
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="pencil" class="w-7 h-7 text-blue-600"></i> Edit Objek
            </h1>
            <p class="text-sm text-slate-500">Kode: <span class="font-mono font-bold text-slate-700"><?= e($objek['kode_objek']) ?></span></p>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi_update_objek" value="1">
                <input type="hidden" name="id" value="<?= $objek['id'] ?>">
                
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
                    <b>Jenis Objek:</b> <?= strtoupper($objek['jenis_objek']) ?> · 
                    <b>Kode:</b> <span class="font-mono"><?= e($objek['kode_objek']) ?></span>
                    <p class="text-slate-500 mt-1">Jenis dan kode tidak dapat diubah.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Nama Objek</label>
                    <input type="text" name="nama_objek" required maxlength="150" 
                        value="<?= e($objek['nama_objek']) ?>"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                </div>
                
                <!-- ============ FIELD KHUSUS POHON ============ -->
<div id="box_pohon_fields" style="display:none;">

    <!-- Identitas Pohon -->
    <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3 mt-3">
        <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">A</div>
        <div class="font-bold text-emerald-800">Identitas Pohon</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
        <div>
            <label class="block text-sm font-semibold mb-2">Nama Latin <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="nama_latin" maxlength="150" placeholder="Contoh: Garcinia mangostana"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Famili <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="famili" maxlength="100" placeholder="Contoh: Clusiaceae"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Asal Daerah <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="asal_daerah" maxlength="150" placeholder="Contoh: Kalimantan"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Nama Daerah Lain <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="nama_daerah_lain" maxlength="200" placeholder="Contoh: Manggis, Manggu"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-semibold mb-2">Status Pohon</label>
        <select name="status_pohon" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            <option value="">-- Pilih --</option>
            <option value="liar">Liar</option>
            <option value="ditanam">Ditanam</option>
            <option value="dibudidayakan">Dibudidayakan</option>
            <option value="penghijauan">Pohon Penghijauan</option>
            <option value="konservasi">Pohon Konservasi</option>
            <option value="lainnya">Lainnya</option>
        </select>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-semibold mb-2">Potensi Pemanfaatan <span class="text-slate-400 font-normal">(opsional)</span></label>
        <textarea name="potensi_pemanfaatan" rows="2" maxlength="500" placeholder="Contoh: Buah, kayu, peneduh"
            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
    </div>

    <!-- Karakteristik -->
    <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-4 mt-3">
        <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">B</div>
        <div class="font-bold text-emerald-800">Karakteristik</div>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-semibold mb-2">Karakteristik Fisik</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
            <?php foreach([
                'berbiji' => 'Berbiji',
                'berbuah' => 'Berbuah',
                'berbunga' => 'Berbunga',
                'akar_tunggang' => 'Akar Tunggang',
                'akar_serabut' => 'Akar Serabut',
                'berkayu' => 'Berkayu',
                'getah' => 'Memiliki Getah',
                'duri' => 'Memiliki Duri',
                'evergreen' => 'Evergreen',
                'menggugurkan_daun' => 'Menggugurkan Daun'
            ] as $key => $label): ?>
                <label class="flex items-center gap-2 p-2 border-2 border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" name="karakteristik[]" value="<?= $key ?>" class="w-3.5 h-3.5">
                    <span><?= $label ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-semibold mb-2">Manfaat</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
            <?php foreach([
                'ekologis' => 'Ekologis',
                'ekonomi' => 'Ekonomi',
                'pangan' => 'Pangan',
                'obat' => 'Obat',
                'budaya' => 'Budaya',
                'lainnya' => 'Lainnya'
            ] as $key => $label): ?>
                <label class="flex items-center gap-2 p-2 border-2 border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" name="manfaat[]" value="<?= $key ?>" class="w-3.5 h-3.5">
                    <span><?= $label ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Penanaman -->
    <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-4 mt-3">
        <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">C</div>
        <div class="font-bold text-emerald-800">Penanaman</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
        <div>
            <label class="block text-sm font-semibold mb-2">Tanggal Tanam <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="date" name="tanggal_tanam"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Sumber Dana <span class="text-slate-400 font-normal">(opsional)</span></label>
            <select name="sumber_dana" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">-- Pilih --</option>
                <option value="APBN">APBN</option>
                <option value="APBD">APBD</option>
                <option value="CSR">CSR</option>
                <option value="Swadaya">Swadaya</option>
                <option value="Hibah">Hibah</option>
                <option value="Donasi">Donasi</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Penyelenggara <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="penyelenggara" maxlength="200" placeholder="Contoh: Dinas Lingkungan Hidup"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Pelaksana <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="pelaksana" maxlength="200" placeholder="Contoh: Komunitas Hijau"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Jenis Bibit <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="jenis_bibit" maxlength="100" placeholder="Contoh: Bibit unggul"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2">Asal Bibit <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="asal_bibit" maxlength="150" placeholder="Contoh: Persemaian Dinas"
                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        </div>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-semibold mb-2">Alasan Penanaman <span class="text-slate-400 font-normal">(opsional)</span></label>
        <textarea name="alasan_penanaman" rows="2" maxlength="500" placeholder="Contoh: Penghijauan lingkungan sekolah"
            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
    </div>

</div>

<!-- ============ END FIELD POHON ============ -->

                <!-- ============ FIELD SUNGAI ============ -->
                <div id="box_sungai_fields" style="<?= $objek['jenis_objek']==='sungai' ? '' : 'display:none;' ?>" class="space-y-4 mt-4">
                    <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="waves" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-blue-900">A. Identitas Sungai</div>
                                <div class="text-xs text-blue-600">Data fisik & geografis</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama Lain</label>
                                <input type="text" name="nama_lain_sungai" maxlength="150" value="<?= e($objek['nama_lain_sungai'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Sumber Mata Air</label>
                                <input type="text" name="sumber_mata_air" maxlength="200" value="<?= e($objek['sumber_mata_air'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Panjang Sungai (km)</label>
                                <input type="number" name="panjang_sungai" step="0.01" min="0" value="<?= e($objek['panjang_sungai'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lebar Rata-rata (m)</label>
                                <input type="number" name="lebar_rata_rata" step="0.01" min="0" value="<?= e($objek['lebar_rata_rata'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kedalaman Rata-rata (m)</label>
                                <input type="number" name="kedalaman_rata_rata" step="0.01" min="0" value="<?= e($objek['kedalaman_rata_rata'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Daerah Hulu</label>
                                <input type="text" name="daerah_hulu" maxlength="200" value="<?= e($objek['daerah_hulu'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Daerah Hilir</label>
                                <input type="text" name="daerah_hilir" maxlength="200" value="<?= e($objek['daerah_hilir'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Muara</label>
                                <input type="text" name="muara" maxlength="200" value="<?= e($objek['muara'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="droplets" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-blue-900">B. Karakteristik Sungai</div>
                                <div class="text-xs text-blue-600">Tipe & pemanfaatan</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Jenis Sungai</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                <?php foreach (['Alami','Buatan','Anak Sungai','Saluran'] as $js): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_sungai" value="<?= $js ?>" class="peer hidden" <?= ($objek['jenis_sungai'] ?? '')===$js?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold hover:border-blue-300 transition text-center"><?= $js ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Dasar Sungai <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <?php 
                            $dasar_sungai_arr = !empty($objek['dasar_sungai']) ? json_decode($objek['dasar_sungai'], true) : [];
                            if (!is_array($dasar_sungai_arr)) $dasar_sungai_arr = [];
                            ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach (['Berbatu','Berlumpur','Berpasir'] as $ds): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="dasar_sungai[]" value="<?= $ds ?>" class="peer hidden" <?= in_array($ds, $dasar_sungai_arr)?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold hover:border-blue-300 transition text-center"><?= $ds ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-slate-700">Ada Vegetasi?</label>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_vegetasi" value="1" class="peer hidden" <?= (string)($objek['ada_vegetasi'] ?? '')==='1'?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Ya</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_vegetasi" value="0" class="peer hidden" <?= (string)($objek['ada_vegetasi'] ?? '')==='0'?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Tidak</div>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-slate-700">Ada Sempadan?</label>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_sempadan" value="1" class="peer hidden" <?= (string)($objek['ada_sempadan'] ?? '')==='1'?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Ya</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_sempadan" value="0" class="peer hidden" <?= (string)($objek['ada_sempadan'] ?? '')==='0'?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Tidak</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Pemanfaatan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <?php 
                            $pemanfaatan_sungai_arr = !empty($objek['pemanfaatan_sungai']) ? json_decode($objek['pemanfaatan_sungai'], true) : [];
                            if (!is_array($pemanfaatan_sungai_arr)) $pemanfaatan_sungai_arr = [];
                            ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach (['Air baku','Irigasi','Pertanian','Perikanan','Peternakan','Rekreasi','Kebutuhan rumah tangga','Tidak dimanfaatkan','Lainnya'] as $pm): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="pemanfaatan_sungai[]" value="<?= $pm ?>" class="peer hidden" <?= in_array($pm, $pemanfaatan_sungai_arr)?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold hover:border-blue-300 transition text-center"><?= $pm ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============ END FIELD SUNGAI ============ -->
                
                                <!-- ============ FIELD TAMAN ============ -->
                <div id="box_taman_fields" style="<?= $objek['jenis_objek']==='taman' ? '' : 'display:none;' ?>" class="space-y-4 mt-4">
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="flower-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">A. Identitas Taman</div>
                                <div class="text-xs text-lime-700">Pengelolaan & lokasi</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Pengelola</label>
                                <input type="text" name="taman_pengelola" maxlength="200" value="<?= e($objek['taman_pengelola'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Luas Area (m²)</label>
                                <input type="number" name="taman_luas_area" step="0.01" min="0" value="<?= e($objek['taman_luas_area'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tahun Pembangunan</label>
                                <input type="number" name="taman_tahun_pembangunan" min="1900" max="2100" value="<?= e($objek['taman_tahun_pembangunan'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tahun Revitalisasi</label>
                                <input type="number" name="taman_tahun_revitalisasi" min="1900" max="2100" value="<?= e($objek['taman_tahun_revitalisasi'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status Kepemilikan</label>
                            <input type="text" name="taman_status_kepemilikan" maxlength="100" value="<?= e($objek['taman_status_kepemilikan'] ?? '') ?>"
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="trees" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">B. Vegetasi Taman</div>
                                <div class="text-xs text-lime-700">Jenis & kategori tanaman</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Kategori Tanaman <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <?php 
                            $taman_kategori_arr = !empty($objek['taman_kategori_tanaman']) ? json_decode($objek['taman_kategori_tanaman'], true) : [];
                            if (!is_array($taman_kategori_arr)) $taman_kategori_arr = [];
                            ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach (['Tanaman Obat','Tanaman Pangan','Tanaman Hias','Tanaman Lokal','Tanaman Endemik','Vegetasi Penutup Tanah'] as $kt): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="taman_kategori_tanaman[]" value="<?= $kt ?>" class="peer hidden" <?= in_array($kt, $taman_kategori_arr)?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold hover:border-lime-300 transition text-center"><?= $kt ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jenis Pohon Dominan</label>
                                <input type="text" name="taman_vegetasi_pohon" maxlength="300" value="<?= e($objek['taman_vegetasi_pohon'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jenis Tanaman Lain</label>
                                <input type="text" name="taman_vegetasi_tanaman" maxlength="300" value="<?= e($objek['taman_vegetasi_tanaman'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">C. Pemanfaatan Taman</div>
                                <div class="text-xs text-lime-700">Fungsi & intensitas</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Fungsi Pemanfaatan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <?php 
                            $taman_pemanfaatan_arr = !empty($objek['taman_pemanfaatan']) ? json_decode($objek['taman_pemanfaatan'], true) : [];
                            if (!is_array($taman_pemanfaatan_arr)) $taman_pemanfaatan_arr = [];
                            ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach (['Ruang publik','Rekreasi','Olahraga','Edukasi','Bermain anak','Interaksi sosial','Konservasi','Kegiatan budaya','Kegiatan komunitas','Lainnya'] as $fg): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="taman_pemanfaatan[]" value="<?= $fg ?>" class="peer hidden" <?= in_array($fg, $taman_pemanfaatan_arr)?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold hover:border-lime-300 transition text-center"><?= $fg ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Intensitas Pemanfaatan</label>
                            <select name="taman_intensitas" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <?php foreach (['Sangat tinggi','Tinggi','Sedang','Rendah','Tidak dimanfaatkan'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= ($objek['taman_intensitas'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">D. Fasilitas Taman</div>
                                <div class="text-xs text-lime-700">Sarana & prasarana</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Fasilitas yang Tersedia <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <?php 
                            $taman_fasilitas_arr = !empty($objek['taman_fasilitas']) ? json_decode($objek['taman_fasilitas'], true) : [];
                            if (!is_array($taman_fasilitas_arr)) $taman_fasilitas_arr = [];
                            ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach (['Bangku','Tempat sampah','Lampu penerangan','Toilet','Gazebo','Jalur pejalan kaki','Jalur difabel','Playground','Fasilitas olahraga','Papan informasi','Area parkir','Drainase','Sistem penyiangan','Fasilitas lainnya'] as $fs): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="taman_fasilitas[]" value="<?= $fs ?>" class="peer hidden" <?= in_array($fs, $taman_fasilitas_arr)?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold hover:border-lime-300 transition text-center"><?= $fs ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============ END FIELD TAMAN ============ -->
                
                                <!-- ============ FIELD KOMPOSTING ============ -->
                <div id="box_komposting_fields" style="<?= $objek['jenis_objek']==='komposting' ? '' : 'display:none;' ?>" class="space-y-4 mt-4">
                    <div class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="recycle" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-amber-900">A. Identitas Komposting</div>
                                <div class="text-xs text-amber-700">Pengelola & kapasitas</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Pengelola</label>
                                <input type="text" name="kompos_pengelola" maxlength="200" value="<?= e($objek['kompos_pengelola'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Metode Komposting</label>
                                <select name="kompos_metode" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach (['Aerob','Anaerob','Takakura','Bokashi','Vermikomposting','Windrow','Biopori','Drum','Lainnya'] as $opt): ?>
                                        <option value="<?= $opt ?>" <?= ($objek['kompos_metode'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kapasitas (kg)</label>
                                <input type="number" name="kompos_kapasitas" step="0.01" min="0" value="<?= e($objek['kompos_kapasitas'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Luas Area (m²)</label>
                                <input type="number" name="kompos_luas_area" step="0.01" min="0" value="<?= e($objek['kompos_luas_area'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tahun Mulai</label>
                                <input type="number" name="kompos_tahun_mulai" min="1900" max="2100" value="<?= e($objek['kompos_tahun_mulai'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jumlah Komposter</label>
                                <input type="number" name="kompos_jumlah_komposter" min="0" max="9999" value="<?= e($objek['kompos_jumlah_komposter'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kelompok / Komunitas Pengelola</label>
                            <input type="text" name="kompos_kelompok" maxlength="200" value="<?= e($objek['kompos_kelompok'] ?? '') ?>"
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-amber-900">B. Bahan Organik</div>
                                <div class="text-xs text-amber-700">Jenis bahan yang dikompos</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Jenis Bahan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <?php 
                            $kompos_bahan_arr = !empty($objek['kompos_bahan_organik']) ? json_decode($objek['kompos_bahan_organik'], true) : [];
                            if (!is_array($kompos_bahan_arr)) $kompos_bahan_arr = [];
                            ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach (['Daun','Rumput','Ranting','Sisa makanan','Sampah dapur','Limbah pertanian','Limbah pasar','Kotoran ternak','Bahan organik lainnya'] as $bh): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="kompos_bahan_organik_arr[]" value="<?= $bh ?>" class="peer hidden" <?= in_array($bh, $kompos_bahan_arr)?'checked':'' ?>>
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold hover:border-amber-300 transition text-center"><?= $bh ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============ END FIELD KOMPOSTING ============ -->
                
                
                
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Lokasi / Wilayah</label>
                    <input type="text" name="lokasi" maxlength="255" 
                        value="<?= e($objek['lokasi']) ?>"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Koordinat GPS
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="koordinat" id="input_koordinat" maxlength="100" 
                            value="<?= e($objek['koordinat']) ?>"
                            class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                        <button type="button" onclick="ambilLokasi()" 
                            class="px-4 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition">
                            <i data-lucide="locate-fixed" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <p id="info_lokasi" class="text-xs text-slate-500 mt-2"></p>
                </div>
                                <!-- ============ FIELD POHON ============ -->
                <div id="box_pohon_fields" style="<?= $objek['jenis_objek']==='pohon' ? '' : 'display:none;' ?>" class="space-y-4 mt-4">
                    <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="tree-pine" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-emerald-900">A. Identitas Pohon</div>
                                <div class="text-xs text-emerald-600">Data ilmiah & klasifikasi</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama Latin</label>
                                <input type="text" name="nama_latin" maxlength="150" value="<?= e($objek['nama_latin'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Famili</label>
                                <input type="text" name="famili" maxlength="100" value="<?= e($objek['famili'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Asal Daerah</label>
                                <input type="text" name="asal_daerah" maxlength="150" value="<?= e($objek['asal_daerah'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama Daerah Lain</label>
                                <input type="text" name="nama_daerah_lain" maxlength="200" value="<?= e($objek['nama_daerah_lain'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status Pohon</label>
                                <select name="status_pohon" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach (['liar'=>'Liar','ditanam'=>'Ditanam','dibudidayakan'=>'Dibudidayakan','penghijauan'=>'Pohon Penghijauan','konservasi'=>'Pohon Konservasi','lainnya'=>'Lainnya'] as $k=>$v): ?>
                                        <option value="<?= $k ?>" <?= ($objek['status_pohon'] ?? '')===$k?'selected':'' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kategori IAS</label>
                                <select name="kategori_ias" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="">-- Pilih --</option>
                                    <option value="IAS" <?= ($objek['kategori_ias'] ?? '')==='IAS'?'selected':'' ?>>IAS</option>
                                    <option value="Non-IAS" <?= ($objek['kategori_ias'] ?? '')==='Non-IAS'?'selected':'' ?>>Non-IAS</option>
                                    <option value="Belum Diketahui" <?= ($objek['kategori_ias'] ?? '')==='Belum Diketahui'?'selected':'' ?>>Belum Diketahui</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Pemanfaatan</label>
                            <textarea name="potensi_pemanfaatan" rows="2" maxlength="500" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= e($objek['potensi_pemanfaatan'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Karakteristik -->
                    <?php 
                    $karakteristik_arr = !empty($objek['karakteristik']) ? json_decode($objek['karakteristik'], true) : [];
                    if (!is_array($karakteristik_arr)) $karakteristik_arr = [];
                    $manfaat_arr = !empty($objek['manfaat']) ? json_decode($objek['manfaat'], true) : [];
                    if (!is_array($manfaat_arr)) $manfaat_arr = [];
                    ?>
                    <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-100 p-5 shadow-sm">
                        <label class="block text-sm font-semibold mb-2 text-slate-700">Karakteristik Fisik</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach (['berbiji'=>'Berbiji','berbuah'=>'Berbuah','berbunga'=>'Berbunga','akar_tunggang'=>'Akar Tunggang','akar_serabut'=>'Akar Serabut','berkayu'=>'Berkayu','getah'=>'Memiliki Getah','duri'=>'Memiliki Duri','evergreen'=>'Evergreen','menggugurkan_daun'=>'Menggugurkan Daun'] as $key => $label): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="karakteristik[]" value="<?= $key ?>" class="peer hidden" <?= in_array($key, $karakteristik_arr)?'checked':'' ?>>
                                    <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-sm text-slate-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:font-semibold hover:border-emerald-300 transition text-center"><?= $label ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="block text-sm font-semibold mb-2 mt-5 text-slate-700">Manfaat</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach (['ekologis'=>'Ekologis','ekonomi'=>'Ekonomi','pangan'=>'Pangan','obat'=>'Obat','budaya'=>'Budaya','lainnya'=>'Lainnya'] as $key => $label): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="manfaat[]" value="<?= $key ?>" class="peer hidden" <?= in_array($key, $manfaat_arr)?'checked':'' ?>>
                                    <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-sm text-slate-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:font-semibold hover:border-emerald-300 transition text-center"><?= $label ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Penanaman -->
                    <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-100 p-5 shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Tanam</label>
                                <input type="date" name="tanggal_tanam" value="<?= e($objek['tanggal_tanam'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Sumber Dana</label>
                                <select name="sumber_dana" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach (['APBN','APBD','CSR','Swadaya','Hibah','Donasi','Lainnya'] as $opt): ?>
                                        <option value="<?= $opt ?>" <?= ($objek['sumber_dana'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Penyelenggara</label>
                                <input type="text" name="penyelenggara" maxlength="200" value="<?= e($objek['penyelenggara'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Pelaksana</label>
                                <input type="text" name="pelaksana" maxlength="200" value="<?= e($objek['pelaksana'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jenis Bibit</label>
                                <input type="text" name="jenis_bibit" maxlength="100" value="<?= e($objek['jenis_bibit'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Asal Bibit</label>
                                <input type="text" name="asal_bibit" maxlength="150" value="<?= e($objek['asal_bibit'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Alasan Penanaman</label>
                            <textarea name="alasan_penanaman" rows="2" maxlength="500" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= e($objek['alasan_penanaman'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <!-- ============ END FIELD POHON ============ -->
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Foto Objek</label>
                    
                    <?php if($objek['foto_pertama'] || !empty($objek['telegram_file_id'])): ?>
    <div class="mb-3 p-3 bg-slate-50 rounded-xl">
        <p class="text-xs text-slate-500 mb-2">Foto saat ini:</p>
        <img src="<?= e(url_foto($objek, 'foto_pertama', 'full')) ?>" alt="Foto" class="max-h-40 rounded-lg" onerror="this.style.display='none'">
    </div>
<?php endif; ?>
                    
                    <p class="text-xs text-slate-500 mb-2">Upload foto baru (opsional — akan mengganti foto lama):</p>
                    
                    <input type="file" name="foto" id="input_foto_kamera" accept="image/*" capture="environment" onchange="pilihFoto(this)" style="display:none;">
                    <input type="file" id="input_foto_galeri" accept="image/jpeg,image/png,image/webp" onchange="pilihFoto(this)" style="display:none;">
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button type="button" onclick="document.getElementById('input_foto_kamera').click()"
                            class="py-3 px-4 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                            <i data-lucide="camera" class="w-3.5 h-3.5"></i> Kamera
                        </button>
                        <button type="button" onclick="document.getElementById('input_foto_galeri').click()"
                            class="py-3 px-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i> Galeri
                        </button>
                    </div>
                    
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center bg-slate-50">
                        <img id="preview_foto" src="" alt="" style="display:none; max-height:200px; margin:0 auto; border-radius:12px;">
                        <p id="info_foto" class="text-xs text-slate-500">Belum ada foto baru dipilih</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Catatan</label>
                    <textarea name="catatan" rows="3" maxlength="2000" 
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition"><?= e($objek['catatan']) ?></textarea>
                </div>
                
                                <!-- ============ PIC MONEV ============ -->
                <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-100 p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-blue-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-blue-600">PIC & peran</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama PIC Monev <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" name="pic_monev_nama" maxlength="150" value="<?= e($objek['pic_monev_nama'] ?? '') ?>"
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Peran / Jabatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <select name="pic_monev_peran" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <?php foreach (['Koordinator','Peneliti','Perekayasa','Arboris','Analis','Pengelola','Pendamping','Penggiat/Pemerhati'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= ($objek['pic_monev_peran'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- ============ END PIC MONEV ============ -->
                
                
                                <!-- ============ VISIBILITY PICKER ============ -->
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="eye" class="w-4 h-4 text-emerald-600"></i> Siapa yang Bisa Lihat Objek Ini?
                    </label>
                    <p class="text-xs text-slate-500 mb-3">Atur tingkat privasi objek ini.</p>
                    <?php $vis_aktif = $objek['visibility'] ?? 'publik'; ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="cursor-pointer group">
                            <input type="radio" name="visibility" value="publik" class="peer hidden" <?= $vis_aktif==='publik'?'checked':'' ?>>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-emerald-600 peer-checked:bg-emerald-50 hover:border-emerald-300 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                        <i data-lucide="globe" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">🌍 Publik</div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">Tampil di beranda & arsip. Siapa saja bisa lihat.</p>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="visibility" value="teman" class="peer hidden" <?= $vis_aktif==='teman'?'checked':'' ?>>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-amber-300 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">👥 Teman</div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">Hanya Anda & user yang saling follow.</p>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="visibility" value="privat" class="peer hidden" <?= $vis_aktif==='privat'?'checked':'' ?>>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-slate-700 peer-checked:bg-slate-100 hover:border-slate-400 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">🔒 Privat</div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">Hanya Anda yang bisa lihat.</p>
                            </div>
                        </label>
                    </div>
                </div>
                <!-- ============ END VISIBILITY PICKER ============ -->
                
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="aktif" <?= $objek['status']=='aktif'?'selected':'' ?>>Aktif</option>
                        <option value="tidak_aktif" <?= $objek['status']=='tidak_aktif'?'selected':'' ?>>Tidak Aktif</option>
                    </select>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-1 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i> Simpan Perubahan
                    </button>
                    <a href="index.php?page=objek_profil&id=<?= $objek['id'] ?>" class="px-6 py-4 bg-slate-200 text-slate-700 font-bold rounded-xl text-center hover:bg-slate-300 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <?php elseif ($halaman == 'cari'): ?>
<?php
// ============ HALAMAN PENCARIAN ============
$q = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'objek';
if (!in_array($tab, ['objek', 'user'], true)) $tab = 'objek';

$hasil_objek = [];
$hasil_user  = [];

if ($q !== '' && mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';
    
    // === CARI OBJEK ===
    if (is_admin()) {
        // Admin: semua objek
        $stmt = mysqli_prepare($conn, "
            SELECT o.id, o.kode_objek, o.nama_objek, o.jenis_objek, o.lokasi, o.foto_pertama, o.telegram_file_id, o.user_id,
                   u.nama_lengkap AS pemilik
            FROM objek o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.deleted_at IS NULL AND o.status='aktif'
              AND (o.nama_objek LIKE ? OR o.kode_objek LIKE ? OR o.lokasi LIKE ?)
            ORDER BY o.id DESC LIMIT 50
        ");
        mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    } elseif (isset($_SESSION['user_id'])) {
        // User login: sendiri + publik (siapapun)
        $uid = (int)$_SESSION['user_id'];
        $stmt = mysqli_prepare($conn, "
            SELECT o.id, o.kode_objek, o.nama_objek, o.jenis_objek, o.lokasi, o.foto_pertama, o.telegram_file_id, o.user_id,
                   u.nama_lengkap AS pemilik
            FROM objek o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.deleted_at IS NULL AND o.status='aktif'
              AND (o.user_id = ? OR o.visibility = 'publik')
              AND (o.nama_objek LIKE ? OR o.kode_objek LIKE ? OR o.lokasi LIKE ?)
            ORDER BY o.id DESC LIMIT 50
        ");
        mysqli_stmt_bind_param($stmt, 'isss', $uid, $like, $like, $like);
    } else {
        // Guest: publik saja
        $stmt = mysqli_prepare($conn, "
            SELECT o.id, o.kode_objek, o.nama_objek, o.jenis_objek, o.lokasi, o.foto_pertama, o.telegram_file_id, o.user_id,
                   u.nama_lengkap AS pemilik
            FROM objek o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.deleted_at IS NULL AND o.status='aktif' AND o.visibility='publik'
              AND (o.nama_objek LIKE ? OR o.kode_objek LIKE ? OR o.lokasi LIKE ?)
            ORDER BY o.id DESC LIMIT 50
        ");
        mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    }
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($r)) $hasil_objek[] = $row;
    mysqli_stmt_close($stmt);
    
    // === CARI USER ===
    $stmt = mysqli_prepare($conn, "
        SELECT u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar,
               (SELECT COUNT(*) FROM objek WHERE user_id = u.id AND deleted_at IS NULL AND visibility='publik') AS total_objek
        FROM users u
        WHERE u.deleted_at IS NULL AND u.aktif = 1
          AND (u.nama_lengkap LIKE ? OR u.organisasi LIKE ?)
        ORDER BY u.id ASC LIMIT 50
    ");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($r)) $hasil_user[] = $row;
    mysqli_stmt_close($stmt);
}
?>
<main class="max-w-4xl mx-auto px-4 pt-32 pb-16">
    
    <!-- FORM SEARCH -->
    <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-5 mb-6">
        <form action="index.php" method="GET" class="space-y-3">
            <input type="hidden" name="page" value="cari">
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
            <div class="relative">
                <input type="text" name="q" value="<?= e($q) ?>" 
                    placeholder="Cari objek, kode, lokasi, nama user, atau organisasi..."
                    autofocus
                    class="w-full pl-12 pr-24 py-3.5 text-sm md:text-base border-2 border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <button type="submit" 
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs md:text-sm px-4 py-2 rounded-xl transition">
                    Cari
                </button>
            </div>
        </form>
    </div>
    
    <?php if($q === ''): ?>
        <!-- EMPTY STATE: Belum Ada Query -->
        <div class="bg-white rounded-3xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="search" class="w-10 h-10 text-emerald-600"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Mulai Pencarian</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                Ketik minimal <b>2 karakter</b> untuk mencari objek lingkungan atau pengguna SWEAPING.
            </p>
        </div>
        
    <?php elseif(mb_strlen($q) < 2): ?>
        <!-- EMPTY STATE: Query Terlalu Pendek -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <i data-lucide="alert-circle" class="w-10 h-10 text-amber-600 mx-auto mb-2"></i>
            <p class="text-sm text-amber-800">Kata kunci terlalu pendek. Minimal <b>2 karakter</b>.</p>
        </div>
        
    <?php else: ?>
        <!-- HASIL PENCARIAN -->
        <div class="mb-4 text-sm text-slate-500">
            Hasil untuk <b class="text-slate-800">"<?= e($q) ?>"</b> 
            · <b><?= count($hasil_objek) ?></b> objek 
            · <b><?= count($hasil_user) ?></b> user
        </div>
        
        <!-- TABS -->
        <div class="bg-white rounded-t-2xl shadow-sm border border-b-0 border-slate-100">
            <div class="flex">
                <a href="index.php?page=cari&q=<?= urlencode($q) ?>&tab=objek" 
                   class="flex-1 text-center py-3 text-sm font-bold border-b-2 transition <?= $tab==='objek' ? 'text-emerald-700 border-emerald-700 bg-emerald-50/50' : 'text-slate-500 border-transparent hover:bg-slate-50' ?>">
                    <i data-lucide="package" class="w-3.5 h-3.5 inline mr-1"></i>
                    Objek (<?= count($hasil_objek) ?>)
                </a>
                <a href="index.php?page=cari&q=<?= urlencode($q) ?>&tab=user" 
                   class="flex-1 text-center py-3 text-sm font-bold border-b-2 transition <?= $tab==='user' ? 'text-emerald-700 border-emerald-700 bg-emerald-50/50' : 'text-slate-500 border-transparent hover:bg-slate-50' ?>">
                    <i data-lucide="users" class="w-3.5 h-3.5 inline mr-1"></i>
                    User (<?= count($hasil_user) ?>)
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-b-2xl shadow-sm border border-slate-100 p-4 md:p-6">
            <?php if($tab === 'objek'): ?>
                <?php if(empty($hasil_objek)): ?>
                    <div class="text-center py-8">
                        <i data-lucide="package" class="w-12 h-12 text-slate-200 mx-auto mb-2"></i>
                        <p class="text-sm text-slate-500">Tidak ada objek yang cocok dengan <b>"<?= e($q) ?>"</b></p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach($hasil_objek as $o):
                            $jenis_info = [
                                'sungai'     => ['badge'=>'from-blue-500 to-blue-600',       'ikon'=>'waves'],
                                'pohon'      => ['badge'=>'from-emerald-600 to-emerald-700', 'ikon'=>'tree-pine'],
                                'taman'      => ['badge'=>'from-lime-500 to-lime-600',       'ikon'=>'flower-2'],
                                'komposting' => ['badge'=>'from-amber-600 to-amber-700',     'ikon'=>'recycle'],
                            ];
                            $ji = $jenis_info[$o['jenis_objek']] ?? $jenis_info['pohon'];
                        ?>
                        <a href="index.php?page=objek_profil&id=<?= (int)$o['id'] ?>" 
                           class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 hover:shadow-md transition">
                            <div class="w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100">
                                <?php if($o['foto_pertama'] || !empty($o['telegram_file_id'])): ?>
                                    <img src="<?= e(url_foto($o, 'foto_pertama', 'thumb')) ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-emerald-50">
                                        <i data-lucide="<?= $ji['ikon'] ?>" class="w-6 h-6 text-emerald-500"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="bg-gradient-to-r <?= $ji['badge'] ?> text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= e($o['jenis_objek']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= e($o['kode_objek']) ?></span>
                                </div>
                                <div class="font-bold text-sm text-slate-800 truncate"><?= e($o['nama_objek']) ?></div>
                                <?php if($o['lokasi']): ?>
                                    <div class="text-[11px] text-slate-500 truncate flex items-center gap-1 mt-0.5">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i> <?= e($o['lokasi']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if($o['pemilik']): ?>
                                    <div class="text-[10px] text-slate-400 truncate mt-0.5">oleh <?= e($o['pemilik']) ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: /* TAB USER */ ?>
                <?php if(empty($hasil_user)): ?>
                    <div class="text-center py-8">
                        <i data-lucide="users" class="w-12 h-12 text-slate-200 mx-auto mb-2"></i>
                        <p class="text-sm text-slate-500">Tidak ada user yang cocok dengan <b>"<?= e($q) ?>"</b></p>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach($hasil_user as $u):
                            $role_icon = ['super_admin'=>'👑','admin'=>'🛡️','user'=>'👤'];
                            $ri_icon = $role_icon[$u['role']] ?? '👤';
                            
                            // Cek state follow
                            $btn_state = 'not-following';
                            $is_me = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$u['id'];
                            if (isset($_SESSION['user_id']) && !$is_me) {
                                $stmt_ck = mysqli_prepare($conn, "SELECT status FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
                                mysqli_stmt_bind_param($stmt_ck, 'ii', $_SESSION['user_id'], $u['id']);
                                mysqli_stmt_execute($stmt_ck);
                                $rc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_ck));
                                mysqli_stmt_close($stmt_ck);
                                if ($rc) {
                                    if ($rc['status'] === 'accepted') $btn_state = 'following';
                                    elseif ($rc['status'] === 'pending') $btn_state = 'pending';
                                }
                            }
                            
                            $btn_class = 'bg-emerald-600 text-white hover:bg-emerald-700';
                            if ($btn_state === 'following') $btn_class = 'bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700';
                            elseif ($btn_state === 'pending') $btn_class = 'bg-amber-100 text-amber-700 hover:bg-amber-200';
                        ?>
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                            <a href="index.php?page=user_profil&id=<?= (int)$u['id'] ?>" class="flex-shrink-0">
                                <?= tampil_avatar($u, 'w-12 h-12', 'text-lg') ?>
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="index.php?page=user_profil&id=<?= (int)$u['id'] ?>" 
                                   class="font-bold text-sm text-slate-800 truncate block hover:text-emerald-700 transition">
                                    <?= e($u['nama_lengkap']) ?>
                                </a>
                                <div class="text-[11px] text-slate-500 truncate flex items-center gap-1 mt-0.5">
                                    <?= $ri_icon ?> 
                                    <?php if($u['organisasi']): ?>
                                        <span class="font-semibold text-emerald-700"><?= e($u['organisasi']) ?></span>
                                    <?php else: ?>
                                        <span><?= ucwords(str_replace('_',' ', $u['role'])) ?></span>
                                    <?php endif; ?>
                                    · <?= number_format($u['total_objek']) ?> objek publik
                                </div>
                            </div>
                            
                            <?php if(isset($_SESSION['user_id']) && !$is_me): ?>
                                <button type="button"
                                    data-follow-inline="<?= (int)$u['id'] ?>"
                                    data-state="<?= $btn_state ?>"
                                    data-primary="0"
                                    class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[11px] font-bold transition <?= $btn_class ?>">
                                    <?php if($btn_state === 'following'): ?>
                                        <i data-lucide="check" class="w-3 h-3"></i> Mengikuti
                                    <?php elseif($btn_state === 'pending'): ?>
                                        <i data-lucide="clock" class="w-3 h-3"></i> Menunggu
                                    <?php else: ?>
                                        <i data-lucide="plus" class="w-3 h-3"></i> Ikuti
                                    <?php endif; ?>
                                </button>
                            <?php elseif($is_me): ?>
                                <span class="text-[11px] text-slate-400 italic">Anda</span>
                            <?php else: ?>
                                <a href="index.php?page=login" class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                    <i data-lucide="log-in" class="w-3 h-3"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
</main>
    
    <?php elseif ($halaman == 'pengikut' || $halaman == 'mengikuti'): ?>
<?php

// ============ HALAMAN PENGIKUT & MENGIKUTI ============
$profil_id = (int)($_GET['id'] ?? 0);
if ($profil_id <= 0) {
    notif('gagal', 'User tidak ditemukan.');
    redirect('index.php?page=beranda');
}

$stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, organisasi, role, aktif, deleted_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$profil_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$profil_user || $profil_user['deleted_at'] !== null || !$profil_user['aktif']) {
    notif('gagal', 'User tidak ditemukan atau tidak aktif.');
    redirect('index.php?page=beranda');
}

$is_self = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $profil_id);
$is_logged = isset($_SESSION['user_id']);
$mode = $halaman; // 'pengikut' atau 'mengikuti'

// Query
if ($mode === 'pengikut') {
    // Cari semua user yang FOLLOW user ini (status accepted)
    $sql = "SELECT u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar, f.created_at AS since
            FROM follows f
            INNER JOIN users u ON u.id = f.follower_id
            WHERE f.following_id = ? AND f.status = 'accepted'
              AND u.deleted_at IS NULL AND u.aktif = 1
            ORDER BY f.created_at DESC";
    $judul = 'Pengikut';
    $judul_emoji = '👥';
    $empty_pesan = 'Belum ada yang mengikuti.';
} else {
    // Cari semua user yang DI-FOLLOW user ini (status accepted)
    $sql = "SELECT u.id, u.nama_lengkap, u.organisasi, u.role, u.avatar, f.created_at AS since
            FROM follows f
            INNER JOIN users u ON u.id = f.following_id
            WHERE f.follower_id = ? AND f.status = 'accepted'
              AND u.deleted_at IS NULL AND u.aktif = 1
            ORDER BY f.created_at DESC";
    $judul = 'Mengikuti';
    $judul_emoji = '🧑‍🤝‍🧑';
    $empty_pesan = 'Belum mengikuti siapapun.';
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$list_user = [];
while ($r = mysqli_fetch_assoc($res)) $list_user[] = $r;
mysqli_stmt_close($stmt);

$total = count($list_user);

// Kalau login, ambil daftar following Saya untuk cek state tombol
$my_following = [];
if ($is_logged) {
    $stmt = mysqli_prepare($conn, "SELECT following_id, status FROM follows WHERE follower_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($r)) {
        $my_following[(int)$row['following_id']] = $row['status'];
    }
    mysqli_stmt_close($stmt);
}
?>
<main class="max-w-3xl mx-auto px-4 pt-32 pb-16">
    
    <div class="mb-6">
        <a href="index.php?page=user_profil&id=<?= $profil_id ?>" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Profil <?= e($profil_user['nama_lengkap']) ?>
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
            <span class="text-3xl"><?= $judul_emoji ?></span> <?= $judul ?>
        </h1>
        <p class="text-sm text-slate-500">
            <b><?= $profil_user['nama_lengkap'] ?></b>
            · <?= $total ?> <?= strtolower($judul) ?>
        </p>
    </div>
    
    <?php if(empty($list_user)): ?>
        <div class="bg-white rounded-3xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="users" class="w-10 h-10 text-emerald-600"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Kosong</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto"><?= e($empty_pesan) ?></p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach($list_user as $u): 
                $my_status = $my_following[(int)$u['id']] ?? 'not-following';
                $btn_state = 'not-following';
                if ($my_status === 'accepted') $btn_state = 'following';
                elseif ($my_status === 'pending') $btn_state = 'pending';
                
                $btn_class = 'bg-emerald-600 text-white hover:bg-emerald-700';
                if ($btn_state === 'following') $btn_class = 'bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700';
                elseif ($btn_state === 'pending') $btn_class = 'bg-amber-100 text-amber-700 hover:bg-amber-200';
                
                $role_icon = ['super_admin'=>'👑','admin'=>'🛡️','user'=>'👤'];
                $ri_icon = $role_icon[$u['role']] ?? '👤';
                $is_me = $is_logged && (int)$_SESSION['user_id'] === (int)$u['id'];
            ?>
                <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-4 shadow-sm hover:shadow-md transition">
                    <a href="index.php?page=user_profil&id=<?= (int)$u['id'] ?>" class="flex-shrink-0 hover:opacity-80 transition">
                        <?= tampil_avatar($u, 'w-12 h-12', 'text-lg') ?>
                    </a>
                    
                    <div class="flex-1 min-w-0">
                        <a href="index.php?page=user_profil&id=<?= (int)$u['id'] ?>" 
                           class="font-bold text-slate-800 truncate block hover:text-emerald-700 transition">
                            <?= e($u['nama_lengkap']) ?>
                        </a>
                        <div class="text-xs text-slate-500 truncate flex items-center gap-1 mt-0.5">
                            <?= $ri_icon ?> 
                            <?php if($u['organisasi']): ?>
                                <span class="font-semibold text-emerald-700"><?= e($u['organisasi']) ?></span>
                            <?php else: ?>
                                <span><?= ucwords(str_replace('_',' ', $u['role'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if($is_logged && !$is_me): ?>
                        <button type="button" 
                            data-follow-inline="<?= (int)$u['id'] ?>"
                            data-state="<?= $btn_state ?>"
                            data-primary="0"
                            class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold transition <?= $btn_class ?>">
                            <?php if($btn_state === 'following'): ?>
                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Mengikuti
                            <?php elseif($btn_state === 'pending'): ?>
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i> Menunggu
                            <?php else: ?>
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Ikuti
                            <?php endif; ?>
                        </button>
                    <?php elseif($is_me): ?>
                        <span class="text-xs text-slate-400 italic">Anda</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
</main>
    
    <?php elseif ($halaman == 'permintaan_follow'): ?>
<?php
// ============ HALAMAN PERMINTAAN FOLLOW ============
if (!isset($_SESSION['user_id'])) {
    notif('gagal', 'Silakan login dulu.');
    redirect('index.php?page=login');
}

$me = (int)$_SESSION['user_id'];

// Ambil daftar pending
$stmt = mysqli_prepare($conn, "
    SELECT f.id AS follow_id, f.created_at,
           u.id AS follower_id, u.nama_lengkap, u.organisasi, u.avatar, u.role
    FROM follows f
    INNER JOIN users u ON u.id = f.follower_id
    WHERE f.following_id = ? AND f.status = 'pending'
      AND u.deleted_at IS NULL AND u.aktif = 1
    ORDER BY f.created_at DESC
");
mysqli_stmt_bind_param($stmt, 'i', $me);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$permintaan = [];
while ($r = mysqli_fetch_assoc($res)) $permintaan[] = $r;
mysqli_stmt_close($stmt);

// Statistik
$total_pending = count($permintaan);
?>
<main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
    
    <div class="mb-6">
        <a href="index.php?page=beranda" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Beranda
        </a>
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
            <i data-lucide="user-plus" class="w-7 h-7 text-amber-600"></i> Permintaan Follow
        </h1>
        <p class="text-sm text-slate-500">
            <?php if($total_pending > 0): ?>
                Anda memiliki <b><?= $total_pending ?></b> permintaan yang menunggu konfirmasi.
            <?php else: ?>
                Tidak ada permintaan follow untuk saat ini.
            <?php endif; ?>
        </p>
    </div>
    
    <?php if(empty($permintaan)): ?>
        <!-- EMPTY STATE -->
        <div class="bg-white rounded-3xl border-2 border-dashed border-slate-200 p-12 text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="inbox" class="w-10 h-10 text-emerald-600"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ada Permintaan</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                Ketika user lain ingin mengikuti Anda, permintaannya akan muncul di halaman ini untuk Anda terima atau tolak.
            </p>
        </div>
    <?php else: ?>
        <!-- DAFTAR PERMINTAAN -->
        <div class="space-y-3">
            <?php foreach($permintaan as $p): ?>
                <div class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-4 shadow-sm hover:shadow-md transition" 
                     data-request-id="<?= (int)$p['follow_id'] ?>">
                    
                    <a href="index.php?page=user_profil&id=<?= (int)$p['follower_id'] ?>" 
                       class="flex-shrink-0 hover:opacity-80 transition">
                        <?= tampil_avatar($p, 'w-14 h-14', 'text-xl') ?>
                    </a>
                    
                    <div class="flex-1 min-w-0">
                        <a href="index.php?page=user_profil&id=<?= (int)$p['follower_id'] ?>" 
                           class="font-bold text-slate-800 truncate block hover:text-emerald-700 transition">
                            <?= e($p['nama_lengkap']) ?>
                        </a>
                        <?php if($p['organisasi']): ?>
                            <div class="text-xs text-slate-500 truncate flex items-center gap-1 mt-0.5">
                                <i data-lucide="building-2" class="w-3 h-3"></i>
                                <?= e($p['organisasi']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="button" 
                            class="btn-respond-follow bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3 py-2 rounded-lg transition flex items-center gap-1 disabled:opacity-50"
                            data-follow-id="<?= (int)$p['follow_id'] ?>"
                            data-action="accept">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> <span class="hidden sm:inline">Terima</span>
                        </button>
                        <button type="button" 
                            class="btn-respond-follow bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-700 font-bold text-xs px-3 py-2 rounded-lg transition flex items-center gap-1 disabled:opacity-50"
                            data-follow-id="<?= (int)$p['follow_id'] ?>"
                            data-action="reject">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> <span class="hidden sm:inline">Tolak</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p class="text-xs text-slate-400 text-center mt-6 italic">
            💡 Terima permintaan untuk membuka interaksi sosial. Anda tetap tidak bisa mengakses objek privat mereka.
        </p>
    <?php endif; ?>
    
</main>

<script>
(function() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-respond-follow');
        if (!btn) return;
        e.preventDefault();
        
        if (btn.disabled) return;
        
        var followId = btn.dataset.followId;
        var action = btn.dataset.action;
        var card = document.querySelector('[data-request-id="' + followId + '"]');
        
        // Disable semua tombol di card ini
        if (card) {
            card.querySelectorAll('.btn-respond-follow').forEach(function(b) { b.disabled = true; });
        }
        
        var formData = new FormData();
        formData.append('aksi_respond_follow', '1');
        formData.append('follow_id', followId);
        formData.append('action', action);
        formData.append('csrf', '<?= $_SESSION['csrf'] ?? '' ?>');
        
        fetch('index.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status === 'ok') {
                // Animasi fade + slide
                if (card) {
                    card.style.transition = 'all 0.35s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(' + (action === 'accept' ? '30px' : '-30px') + ')';
                    setTimeout(function() {
                        card.remove();
                        // Cek kalau sudah kosong
                        var remaining = document.querySelectorAll('[data-request-id]').length;
                        if (remaining === 0) {
                            setTimeout(function() { location.reload(); }, 300);
                        }
                    }, 350);
                }
                
                // Update badge notif di navbar
                var badges = document.querySelectorAll('.ml-auto.bg-rose-500, .bg-rose-500');
                badges.forEach(function(b) {
                    if (b.closest('a[href*="permintaan_follow"]')) {
                        var current = parseInt(b.textContent) || 0;
                        if (current > 1) b.textContent = (current - 1);
                        else b.remove();
                    }
                });
            } else {
                alert(res.message || 'Gagal memproses.');
                if (card) {
                    card.querySelectorAll('.btn-respond-follow').forEach(function(b) { b.disabled = false; });
                }
            }
        })
        .catch(function() {
            alert('Koneksi gagal. Coba lagi.');
            if (card) {
                card.querySelectorAll('.btn-respond-follow').forEach(function(b) { b.disabled = false; });
            }
        });
    });
})();
</script>
    
    <?php elseif ($halaman == 'user_profil'): ?>
<?php
// ============ HALAMAN PROFIL USER PUBLIK ============
$profil_id = (int)($_GET['id'] ?? 0);
if ($profil_id <= 0) {
    notif('gagal', 'User tidak ditemukan.');
    redirect('index.php?page=beranda');
}

$stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, organisasi, avatar, role, aktif, tanggal_daftar, deleted_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$profil_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$profil_user || $profil_user['deleted_at'] !== null || !$profil_user['aktif']) {
    notif('gagal', 'User tidak ditemukan atau tidak aktif.');
    redirect('index.php?page=beranda');
}

$is_self = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $profil_id);
$is_logged = isset($_SESSION['user_id']);

// ===== Statistik =====
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM objek WHERE user_id = ? AND deleted_at IS NULL AND visibility = 'publik'");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$stat_objek = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total, COALESCE(SUM(serapan_karbon), 0) AS total_karbon FROM laporan_aksi WHERE user_id = ? AND deleted_at IS NULL AND visibility = 'publik'");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$stat_laporan = (int)$row['total'];
$stat_karbon  = (float)$row['total_karbon'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM follows WHERE following_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$stat_followers = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM follows WHERE follower_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$stat_following = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

// ===== Apakah saya follow user ini? =====
$i_follow = false;
if ($is_logged && !$is_self) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $_SESSION['user_id'], $profil_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $i_follow = (mysqli_stmt_num_rows($stmt) > 0);
    mysqli_stmt_close($stmt);
}

// ===== Apakah user ini primary admin? (tidak bisa di-unfollow) =====
$is_primary = (int)($profil_user['role'] === 'admin' && ($profil_user['is_primary_admin'] ?? 0) === 1);

// ===== Ambil objek publik user =====
$stmt = mysqli_prepare($conn, "SELECT id, kode_objek, nama_objek, jenis_objek, foto_pertama, telegram_file_id, lokasi FROM objek WHERE user_id = ? AND deleted_at IS NULL AND status = 'aktif' AND visibility = 'publik' ORDER BY id DESC LIMIT 12");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$objek_user = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// ===== Ambil aktivitas publik user =====
$stmt = mysqli_prepare($conn, "SELECT id, nama_objek, jenis_objek, status_kondisi, serapan_karbon, foto, telegram_file_id, tanggal_buat FROM laporan_aksi WHERE user_id = ? AND deleted_at IS NULL AND visibility = 'publik' ORDER BY tanggal_buat DESC LIMIT 10");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$aktivitas_user = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$role_info = [
    'super_admin' => ['emoji' => '👑', 'label' => 'Super Admin', 'warna' => 'purple'],
    'admin'       => ['emoji' => '🛡️', 'label' => 'Admin', 'warna' => 'indigo'],
    'user'        => ['emoji' => '👤', 'label' => 'User', 'warna' => 'emerald'],
];
$ri = $role_info[$profil_user['role']] ?? $role_info['user'];
?>
<main class="max-w-5xl mx-auto px-4 pt-28 pb-16">

    <!-- HEADER PROFIL -->
                    <div class="relative">
                    <?php if($is_self): ?>
                    <button type="button" onclick="bukaModal('modal-avatar')" class="group relative block" title="Ganti Foto Profil">
                        <?= tampil_avatar($profil_user, 'w-24 h-24 md:w-28 md:h-28 border-4 border-white shadow-xl', 'text-4xl') ?>
                        <div class="absolute bottom-0 right-0 w-8 h-8 md:w-9 md:h-9 bg-emerald-600 rounded-full flex items-center justify-center shadow-lg border-2 border-white group-hover:bg-emerald-700 transition">
                            <i data-lucide="camera" class="w-3.5 h-3.5 md:w-4 md:h-4 text-white"></i>
                        </div>
                    </button>
                    <?php else: ?>
                        <?= tampil_avatar($profil_user, 'w-24 h-24 md:w-28 md:h-28 border-4 border-white shadow-xl', 'text-4xl') ?>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0 pb-2">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 leading-tight truncate">
                        <?= e($profil_user['nama_lengkap']) ?>
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-<?= $ri['warna'] ?>-100 text-<?= $ri['warna'] ?>-700 rounded-full font-bold text-xs">
                            <?= $ri['emoji'] ?> <?= $ri['label'] ?>
                        </span>
                        <?php if($profil_user['organisasi']): ?>
                            <span class="inline-flex items-center gap-1 text-slate-600 text-xs">
                                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                                <?= e($profil_user['organisasi']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="inline-flex items-center gap-1 text-slate-500 text-xs">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            Bergabung <?= date('d M Y', strtotime($profil_user['tanggal_daftar'])) ?>
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0 pb-2">
                    <?php if($is_self): ?>
                        <a href="index.php?page=misi_saya" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-700 text-white font-bold rounded-full hover:bg-emerald-800 transition shadow-lg">
                            <i data-lucide="target" class="w-4 h-4"></i> Misi Saya
                        </a>
                    <?php elseif($is_logged): ?>
                        <button type="button" id="btn-follow" 
                            data-user="<?= $profil_id ?>"
                            data-primary="<?= $is_primary ? 1 : 0 ?>"
                            data-state="<?= $i_follow ? 'following' : 'not-following' ?>"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-bold transition shadow-lg
                                <?= $i_follow ? 'bg-slate-200 text-slate-700 hover:bg-rose-100 hover:text-rose-700' : 'bg-emerald-700 text-white hover:bg-emerald-800' ?>">
                            <span id="btn-follow-icon">
                                <i data-lucide="<?= $i_follow ? 'check' : 'user-plus' ?>" class="w-4 h-4"></i>
                            </span>
                            <span id="btn-follow-text"><?= $i_follow ? 'Mengikuti' : 'Ikuti' ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
        <a href="index.php?page=objek&f_cari=" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center card-hover block">
            <div class="text-2xl md:text-3xl font-extrabold text-emerald-700"><?= number_format($stat_objek) ?></div>
            <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Objek Publik</div>
        </a>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
            <div class="text-2xl md:text-3xl font-extrabold text-blue-600"><?= number_format($stat_laporan) ?></div>
            <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Laporan Publik</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
            <div class="text-2xl md:text-3xl font-extrabold text-amber-600"><?= number_format($stat_karbon, 0) ?></div>
            <div class="text-xs text-slate-500 font-semibold uppercase mt-1">kg CO₂</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
            <div class="flex justify-center gap-2 text-base md:text-lg font-extrabold">
                <a href="index.php?page=pengikut&id=<?= $profil_id ?>" class="text-emerald-700 hover:underline"><?= number_format($stat_followers) ?></a>
                <span class="text-slate-300">·</span>
                <a href="index.php?page=mengikuti&id=<?= $profil_id ?>" class="text-blue-600 hover:underline"><?= number_format($stat_following) ?></a>
            </div>
            <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Pengikut · Mengikuti</div>
        </div>
    </div>

    <!-- TAB -->
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="border-b border-slate-100 px-6">
            <div class="flex gap-1 overflow-x-auto">
                <button type="button" class="tab-profil px-4 py-3 font-bold text-sm text-emerald-700 border-b-2 border-emerald-700 whitespace-nowrap" data-tab="objek">
                    <i data-lucide="package" class="w-3.5 h-3.5 inline mr-1"></i> Objek
                </button>
                <button type="button" class="tab-profil px-4 py-3 font-bold text-sm text-slate-400 hover:text-slate-700 border-b-2 border-transparent whitespace-nowrap" data-tab="aktivitas">
                    <i data-lucide="activity" class="w-3.5 h-3.5 inline mr-1"></i> Aktivitas
                </button>
                <button type="button" class="tab-profil px-4 py-3 font-bold text-sm text-slate-400 hover:text-slate-700 border-b-2 border-transparent whitespace-nowrap" data-tab="tentang">
                    <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1"></i> Tentang
                </button>
            </div>
        </div>

        <!-- Tab: Objek -->
        <div id="tab-objek" class="p-6">
            <?php if(empty($objek_user)): ?>
                <div class="text-center py-12">
                    <i data-lucide="package" class="w-16 h-16 text-slate-200 mx-auto mb-3"></i>
                    <p class="text-slate-400">Belum ada objek publik</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach($objek_user as $o):
                        $jenis_info = [
                            'sungai'     => ['badge' => 'from-blue-500 to-blue-600',       'ikon' => 'waves'],
                            'pohon'      => ['badge' => 'from-emerald-600 to-emerald-700', 'ikon' => 'tree-pine'],
                            'taman'      => ['badge' => 'from-lime-500 to-lime-600',       'ikon' => 'flower-2'],
                            'komposting' => ['badge' => 'from-amber-600 to-amber-700',     'ikon' => 'recycle'],
                        ];
                        $ji = $jenis_info[$o['jenis_objek']] ?? $jenis_info['pohon'];
                    ?>
                    <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden card-hover block">
                        <div class="bg-gradient-to-r <?= $ji['badge'] ?> text-white text-[10px] font-bold px-3 py-1.5 uppercase flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i data-lucide="<?= $ji['ikon'] ?>" class="w-3 h-3"></i> <?= e($o['jenis_objek']) ?></span>
                            <span class="font-mono opacity-80"><?= e($o['kode_objek']) ?></span>
                        </div>
                        <?php if($o['foto_pertama'] || !empty($o['telegram_file_id'])): ?>
                            <img src="<?= e(url_foto($o, 'foto_pertama', 'thumb')) ?>" alt="" class="w-full h-32 object-cover" loading="lazy">
                        <?php else: ?>
                            <div class="w-full h-32 bg-emerald-50 flex items-center justify-center">
                                <i data-lucide="<?= $ji['ikon'] ?>" class="w-10 h-10 text-emerald-300"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-3">
                            <div class="font-bold text-sm text-slate-800 truncate"><?= e($o['nama_objek']) ?></div>
                            <div class="text-[11px] text-slate-500 truncate mt-0.5"><?= e($o['lokasi'] ?: '—') ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Aktivitas -->
        <div id="tab-aktivitas" class="p-6 hidden">
            <?php if(empty($aktivitas_user)): ?>
                <div class="text-center py-12">
                    <i data-lucide="activity" class="w-16 h-16 text-slate-200 mx-auto mb-3"></i>
                    <p class="text-slate-400">Belum ada aktivitas publik</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach($aktivitas_user as $a):
                        $jenis_info2 = [
                            'sungai'     => ['badge' => 'from-blue-500 to-blue-600',       'ikon' => 'waves'],
                            'pohon'      => ['badge' => 'from-emerald-600 to-emerald-700', 'ikon' => 'tree-pine'],
                            'taman'      => ['badge' => 'from-lime-500 to-lime-600',       'ikon' => 'flower-2'],
                            'komposting' => ['badge' => 'from-amber-600 to-amber-700',     'ikon' => 'recycle'],
                        ];
                        $ji2 = $jenis_info2[$a['jenis_objek']] ?? $jenis_info2['pohon'];
                    ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100">
                            <?php if($a['foto'] || !empty($a['telegram_file_id'])): ?>
                                <img src="<?= e(url_foto($a, 'foto', 'thumb')) ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-emerald-50">
                                    <i data-lucide="<?= $ji2['ikon'] ?>" class="w-5 h-5 text-emerald-500"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="bg-gradient-to-r <?= $ji2['badge'] ?> text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= e($a['jenis_objek']) ?></span>
                                <span class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($a['tanggal_buat'])) ?></span>
                            </div>
                            <div class="font-bold text-sm text-slate-800 truncate"><?= e($a['nama_objek']) ?></div>
                            <div class="text-xs text-slate-500 mt-0.5"><?= e($a['status_kondisi'] ?: '—') ?></div>
                        </div>
                        <?php if($a['serapan_karbon'] > 0): ?>
                            <div class="text-right flex-shrink-0">
                                <div class="text-xs font-bold text-amber-600"><?= number_format($a['serapan_karbon'], 0) ?></div>
                                <div class="text-[10px] text-slate-400">kg CO₂</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Tentang -->
        <div id="tab-tentang" class="p-6 hidden">
            <div class="space-y-4 max-w-2xl">
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase">Nama Lengkap</div>
                        <div class="font-bold text-slate-800"><?= e($profil_user['nama_lengkap']) ?></div>
                    </div>
                </div>
                <?php if($profil_user['organisasi']): ?>
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase">Organisasi</div>
                        <div class="font-bold text-slate-800"><?= e($profil_user['organisasi']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase">Bergabung</div>
                        <div class="font-bold text-slate-800"><?= date('d F Y', strtotime($profil_user['tanggal_daftar'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
(function() {
    // ===== Toggle Tab =====
    var tabs = document.querySelectorAll('.tab-profil');
    tabs.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = this.dataset.tab;
            tabs.forEach(function(b) {
                b.classList.remove('text-emerald-700', 'border-emerald-700');
                b.classList.add('text-slate-400', 'border-transparent');
            });
            this.classList.remove('text-slate-400', 'border-transparent');
            this.classList.add('text-emerald-700', 'border-emerald-700');
            ['objek', 'aktivitas', 'tentang'].forEach(function(t) {
                var el = document.getElementById('tab-' + t);
                if (el) el.classList.toggle('hidden', t !== target);
            });
        });
    });

    // ===== Tombol Follow =====
    var btn = document.getElementById('btn-follow');
    if (!btn) return;
    
    var isPrimary = btn.dataset.primary === '1';
    var state = btn.dataset.state;
    var userId = btn.dataset.user;
    
    btn.addEventListener('click', function() {
        // Kalau primary admin & sedang following, tolak unfollow
        if (isPrimary && state === 'following') {
            alert('Admin ini adalah admin utama dan tidak dapat di-unfollow.');
            return;
        }
        
        var action = state === 'following' ? 'unfollow' : 'follow';
        btn.disabled = true;
        
        var formData = new FormData();
        formData.append('aksi_follow', '1');
        formData.append('target_id', userId);
        formData.append('action', action);
        formData.append('csrf', '<?= $_SESSION['csrf'] ?? '' ?>');
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            if (res.status === 'ok') {
                state = res.new_state;
                btn.dataset.state = state;
                var iconWrap = document.getElementById('btn-follow-icon');
                var textEl = document.getElementById('btn-follow-text');
                
                if (state === 'following') {
                    btn.className = 'inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-bold transition shadow-lg bg-slate-200 text-slate-700 hover:bg-rose-100 hover:text-rose-700';
                    iconWrap.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
                    textEl.textContent = 'Mengikuti';
                } else {
                    btn.className = 'inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-bold transition shadow-lg bg-emerald-700 text-white hover:bg-emerald-800';
                    iconWrap.innerHTML = '<i data-lucide="user-plus" class="w-4 h-4"></i>';
                    textEl.textContent = 'Ikuti';
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                alert(res.message || 'Gagal memproses.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            alert('Koneksi gagal. Coba lagi.');
        });
    });
})();
</script>
    
    <?php elseif ($halaman == 'objek_profil'): ?>
    <?php
    $id = (int)($_GET['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$objek) {
        notif('gagal', 'Objek tidak ditemukan.');
        redirect('index.php?page=objek');
    }
    
    $jenis_info = [
        'sungai'     => ['badge' => 'from-blue-500 to-blue-600',       'ikon' => 'waves'],
        'pohon'      => ['badge' => 'from-emerald-600 to-emerald-700', 'ikon' => 'tree-pine'],
        'taman'      => ['badge' => 'from-lime-500 to-lime-600',       'ikon' => 'flower-2'],
        'komposting' => ['badge' => 'from-amber-600 to-amber-700',     'ikon' => 'recycle'],
    ];
    $info = $jenis_info[$objek['jenis_objek']] ?? $jenis_info['pohon'];
    
    // Query laporan yang terhubung ke objek ini
    $stmt2 = mysqli_prepare($conn, "SELECT * FROM laporan_aksi WHERE objek_id = ? AND deleted_at IS NULL ORDER BY tanggal_buat DESC");
    mysqli_stmt_bind_param($stmt2, 'i', $id);
    mysqli_stmt_execute($stmt2);
    $res_laporan = mysqli_stmt_get_result($stmt2);
    $data_laporan = [];
    while ($row = mysqli_fetch_assoc($res_laporan)) $data_laporan[] = $row;
    mysqli_stmt_close($stmt2);
    ?>
    
    <main class="max-w-4xl mx-auto px-4 pt-28 pb-16">
        <a href="index.php?page=objek" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-4">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Daftar Objek
        </a>
        <div class="flex justify-end gap-2 mb-4 flex-wrap">
    <?php if(isset($_SESSION['user_id']) && ($objek['user_id'] == $_SESSION['user_id'] || is_admin())): ?>
        <a href="index.php?page=objek_edit&id=<?= $objek['id'] ?>" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-full hover:bg-blue-700 transition flex items-center gap-2 text-sm">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit Objek
        </a>
        <a href="javascript:void(0)" onclick="konfirmasiHapusObjek(<?= $objek['id'] ?>, '<?= e($objek['nama_objek']) ?>')" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-full hover:bg-rose-700 transition flex items-center gap-2 text-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus Objek
        </a>
    <?php endif; ?>
</div>
        
        <!-- KARTU PROFIL -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r <?= $info['badge'] ?> text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="<?= $info['ikon'] ?>" class="w-3.5 h-3.5"></i>
                    <?= e($objek['jenis_objek']) ?>
                </span>
                <span class="font-mono font-bold"><?= e($objek['kode_objek']) ?></span>
            </div>
            
            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <?php if($objek['foto_pertama'] || !empty($objek['telegram_file_id'])): ?>
    <img src="<?= e(url_foto($objek, 'foto_pertama', 'full')) ?>" alt="Foto Objek" class="w-full rounded-2xl shadow-md" onerror="this.style.display='none'">
<?php else: ?>
    ...
<?php endif; ?>
                    </div>
                    
                    <div class="md:col-span-2">
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-3"><?= e($objek['nama_objek']) ?></h1>
                        
                        <?php if($objek['lokasi']): ?>
                            <p class="text-slate-600 mb-2 flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <?= e($objek['lokasi']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if($objek['koordinat']): ?>
                            <p class="text-slate-500 mb-2 flex items-center gap-2 font-mono text-sm">
                                <i data-lucide="globe" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <?= e($objek['koordinat']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="text-slate-500 mb-4 flex items-center gap-2 text-sm">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Didaftarkan: <?= date('d F Y', strtotime($objek['tanggal_daftar'])) ?>
                        </p>
                        
                        <?php if($objek['catatan']): ?>
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 mt-3">
                                <p class="text-sm text-emerald-900"><?= e($objek['catatan']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
                <!-- ============ RINGKASAN TOTAL KARBON ============ -->
        <?php
        $stmt_k = mysqli_prepare($conn, "SELECT 
            COUNT(*) as total_laporan,
                        COALESCE(SUM(COALESCE(serapan_karbon_ilmiah, serapan_karbon, 0)), 0) as total_karbon,
            COALESCE(SUM(CASE WHEN metode_hitung IS NOT NULL THEN 1 ELSE 0 END), 0) as laporan_dgn_metode,
            MAX(metode_hitung) as metode_terakhir
            FROM laporan_aksi 
            WHERE objek_id = ? AND deleted_at IS NULL");
        mysqli_stmt_bind_param($stmt_k, 'i', $id);
        mysqli_stmt_execute($stmt_k);
        $ringkasan_karbon = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_k));
        mysqli_stmt_close($stmt_k);
        ?>
        <?php if ($ringkasan_karbon['total_laporan'] > 0): ?>
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                    Ringkasan Monitoring
                </span>
                <span class="text-xs font-mono"><?= e($objek['kode_objek']) ?></span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center bg-emerald-50 rounded-2xl p-4 border-2 border-emerald-100">
                    <div class="text-3xl font-extrabold text-emerald-700 leading-tight"><?= number_format($ringkasan_karbon['total_laporan']) ?></div>
                    <div class="text-xs text-emerald-600 font-semibold uppercase mt-1">Laporan Monitoring</div>
                </div>
                <div class="text-center bg-amber-50 rounded-2xl p-4 border-2 border-amber-100">
                    <div class="text-3xl font-extrabold text-amber-700 leading-tight"><?= number_format($ringkasan_karbon['total_karbon'], 2) ?></div>
                    <div class="text-xs text-amber-600 font-semibold uppercase mt-1">Total kg CO₂</div>
                </div>
                <div class="text-center bg-blue-50 rounded-2xl p-4 border-2 border-blue-100">
                    <div class="text-sm font-extrabold text-blue-700 leading-tight pt-1">
                        <?php 
                        $metode_label = '-';
                        if ($ringkasan_karbon['metode_terakhir'] === 'chave_2005') $metode_label = 'Chave 2005';
                        elseif ($ringkasan_karbon['metode_terakhir'] === 'rth_konversi') $metode_label = 'RTH Konversi';
                        elseif ($ringkasan_karbon['metode_terakhir'] === 'ipcc_kompos') $metode_label = 'IPCC Kompos';
                        elseif ($ringkasan_karbon['metode_terakhir'] === 'ch4_estimasi') $metode_label = 'IPCC 2019';
                        elseif ($ringkasan_karbon['metode_terakhir'] === 'umur_fallback') $metode_label = 'Estimasi';
                        echo e($metode_label);
                        ?>
                    </div>
                    <div class="text-xs text-blue-600 font-semibold uppercase mt-2">Metode Utama</div>
                </div>
            </div>
            <div class="px-6 pb-5">
                <p class="text-xs text-slate-500 italic text-center">
                    💡 Total karbon dihitung dari <b><?= number_format($ringkasan_karbon['total_laporan']) ?> laporan monitoring</b> yang tersimpan.
                </p>
            </div>
        </div>
        <?php endif; ?>
        <!-- ============ END RINGKASAN TOTAL KARBON ============ -->
        
        
        <!-- QR CODE OBJECT -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r <?= $info['badge'] ?> text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                    QR Code Objek
                </span>
                <span class="font-mono font-bold text-xs"><?= e($objek['kode_objek']) ?></span>
            </div>
            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="bg-white p-4 rounded-2xl border-2 border-slate-100 shadow-md flex-shrink-0">
                        <?php 
                        $qr_url = 'https://sweaping.lsmkompas.id/index.php?page=objek_profil&id=' . $objek['id'];
                        $qr_api = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qr_url) . '&margin=10';
                        ?>
                        <img src="<?= $qr_api ?>" alt="QR Code <?= e($objek['kode_objek']) ?>" class="w-44 h-44 md:w-52 md:h-52 block">
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <div class="font-mono font-bold text-lg text-slate-800 mb-2"><?= e($objek['kode_objek']) ?></div>
                        <p class="text-sm text-slate-600 mb-4 leading-relaxed">
                            Scan QR Code ini dengan kamera HP untuk membuka profil <b><?= e($objek['nama_objek']) ?></b> secara langsung.
                        </p>
                        <p class="text-xs text-slate-500 mb-4 font-mono bg-slate-50 rounded-lg p-2 break-all">
                            <?= e($qr_url) ?>
                        </p>
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                            <a href="<?= $qr_api ?>" download="QR-<?= e($objek['kode_objek']) ?>.png" target="_blank" rel="noopener" class="px-4 py-2.5 bg-emerald-700 text-white font-bold rounded-full hover:bg-emerald-800 transition flex items-center gap-2 text-sm shadow-md">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download QR
                            </a>
                            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-full hover:bg-slate-300 transition flex items-center gap-2 text-sm">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if($objek['jenis_objek'] === 'pohon' && ($objek['nama_latin'] || $objek['famili'] || $objek['status_pohon'] || $objek['kategori_ias'] || $objek['karakteristik'] || $objek['manfaat'] || $objek['tanggal_tanam'] || $objek['sumber_dana'] || $objek['potensi_pemanfaatan'])): ?>
        <!-- INFORMASI DETAIL POHON -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-emerald-700 to-emerald-900 text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="tree-pine" class="w-3.5 h-3.5"></i>
                    Informasi Detail Pohon
                </span>
            </div>
            
            <div class="p-6 md:p-8 space-y-6">
                
                <!-- A. IDENTITAS -->
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-emerald-100">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Identitas Pohon</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['nama_latin']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Nama Latin</div>
                            <div class="text-sm font-semibold text-slate-800 italic"><?= e($objek['nama_latin']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['famili']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Famili</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['famili']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['asal_daerah']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Asal Daerah</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['asal_daerah']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['nama_daerah_lain']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Nama Daerah Lain</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['nama_daerah_lain']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['status_pohon']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Status Pohon</div>
                            <div class="text-sm font-semibold text-slate-800 capitalize"><?= e($objek['status_pohon']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kategori_ias']): 
                            $ias_warna = 'bg-emerald-100 text-emerald-800';
                            if ($objek['kategori_ias'] === 'IAS') $ias_warna = 'bg-rose-100 text-rose-800';
                            elseif ($objek['kategori_ias'] === 'Belum Diketahui') $ias_warna = 'bg-amber-100 text-amber-800';
                        ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Kategori IAS</div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?= $ias_warna ?>">
                                <?= e($objek['kategori_ias']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($objek['potensi_pemanfaatan']): ?>
                    <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 mt-3">
                        <div class="text-xs text-emerald-700 font-semibold uppercase mb-1">Potensi Pemanfaatan</div>
                        <div class="text-sm text-emerald-900"><?= e($objek['potensi_pemanfaatan']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- B. KARAKTERISTIK & MANFAAT -->
                <?php 
                    $karakteristik_arr = $objek['karakteristik'] ? json_decode($objek['karakteristik'], true) : [];
                    $manfaat_arr       = $objek['manfaat'] ? json_decode($objek['manfaat'], true) : [];
                    if (!is_array($karakteristik_arr)) $karakteristik_arr = [];
                    if (!is_array($manfaat_arr)) $manfaat_arr = [];
                    
                    $label_karakteristik = [
                        'berbiji' => 'Berbiji', 'berbuah' => 'Berbuah', 'berbunga' => 'Berbunga',
                        'akar_tunggang' => 'Akar Tunggang', 'akar_serabut' => 'Akar Serabut',
                        'berkayu' => 'Berkayu', 'getah' => 'Memiliki Getah', 'duri' => 'Memiliki Duri',
                        'evergreen' => 'Evergreen', 'menggugurkan_daun' => 'Menggugurkan Daun'
                    ];
                    $label_manfaat = [
                        'ekologis' => 'Ekologis', 'ekonomi' => 'Ekonomi', 'pangan' => 'Pangan',
                        'obat' => 'Obat', 'budaya' => 'Budaya', 'lainnya' => 'Lainnya'
                    ];
                ?>
                <?php if(!empty($karakteristik_arr) || !empty($manfaat_arr)): ?>
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-emerald-100">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <i data-lucide="sprout" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Karakteristik & Manfaat</h3>
                    </div>
                    
                    <?php if(!empty($karakteristik_arr)): ?>
                    <div class="mb-3">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-2">Karakteristik Fisik</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($karakteristik_arr as $k): ?>
                                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold">
                                    <?= e($label_karakteristik[$k] ?? $k) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($manfaat_arr)): ?>
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-2">Manfaat</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($manfaat_arr as $m): ?>
                                <span class="px-3 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-semibold">
                                    <?= e($label_manfaat[$m] ?? $m) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- C. PENANAMAN -->
                <?php if($objek['tanggal_tanam'] || $objek['penyelenggara'] || $objek['pelaksana'] || $objek['sumber_dana'] || $objek['jenis_bibit'] || $objek['asal_bibit'] || $objek['alasan_penanaman']): ?>
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-emerald-100">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Penanaman</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['tanggal_tanam'] && $objek['tanggal_tanam'] !== '0000-00-00'): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Tanggal Tanam</div>
                            <div class="text-sm font-semibold text-slate-800"><?= date('d F Y', strtotime($objek['tanggal_tanam'])) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['sumber_dana']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Sumber Dana</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['sumber_dana']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['penyelenggara']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Penyelenggara</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['penyelenggara']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['pelaksana']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Pelaksana</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['pelaksana']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['jenis_bibit']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Jenis Bibit</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['jenis_bibit']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['asal_bibit']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Asal Bibit</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['asal_bibit']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($objek['alasan_penanaman']): ?>
                    <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 mt-3">
                        <div class="text-xs text-emerald-700 font-semibold uppercase mb-1">Alasan Penanaman</div>
                        <div class="text-sm text-emerald-900"><?= e($objek['alasan_penanaman']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
                        <?php if(true): ?>
                <?php if($objek['jenis_objek'] === 'sungai' && ($objek['nama_lain_sungai'] || $objek['sumber_mata_air'] || $objek['panjang_sungai'] || $objek['lebar_rata_rata'] || $objek['kedalaman_rata_rata'] || $objek['daerah_hulu'] || $objek['daerah_hilir'] || $objek['muara'] || $objek['jenis_sungai'] || $objek['dasar_sungai'] || $objek['ada_vegetasi'] || $objek['ada_sempadan'] || $objek['pemanfaatan_sungai'])): ?>
        <!-- INFORMASI DETAIL SUNGAI -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="waves" class="w-3.5 h-3.5"></i>
                    Informasi Detail Sungai
                </span>
            </div>
            
            <div class="p-6 md:p-8 space-y-6">
                
                <!-- A. IDENTITAS SUNGAI -->
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-blue-100">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Identitas Sungai</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['nama_lain_sungai']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Nama Lain</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['nama_lain_sungai']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['sumber_mata_air']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Sumber Mata Air</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['sumber_mata_air']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['panjang_sungai'] && $objek['panjang_sungai'] > 0): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Panjang Sungai</div>
                            <div class="text-sm font-semibold text-slate-800"><?= number_format($objek['panjang_sungai'], 2) ?> km</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['lebar_rata_rata'] && $objek['lebar_rata_rata'] > 0): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Lebar Rata-rata</div>
                            <div class="text-sm font-semibold text-slate-800"><?= number_format($objek['lebar_rata_rata'], 2) ?> m</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kedalaman_rata_rata'] && $objek['kedalaman_rata_rata'] > 0): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Kedalaman Rata-rata</div>
                            <div class="text-sm font-semibold text-slate-800"><?= number_format($objek['kedalaman_rata_rata'], 2) ?> m</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['daerah_hulu']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Daerah Hulu</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['daerah_hulu']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['daerah_hilir']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Daerah Hilir</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['daerah_hilir']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['muara']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Muara</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['muara']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- B. KARAKTERISTIK SUNGAI -->
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-blue-100">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i data-lucide="droplets" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Karakteristik Sungai</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['jenis_sungai']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Jenis Sungai</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['jenis_sungai']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['ada_vegetasi'] !== null): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Ada Vegetasi?</div>
                            <div class="text-sm font-semibold text-slate-800"><?= $objek['ada_vegetasi'] ? 'Ya' : 'Tidak' ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['ada_sempadan'] !== null): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Ada Sempadan?</div>
                            <div class="text-sm font-semibold text-slate-800"><?= $objek['ada_sempadan'] ? 'Ya' : 'Tidak' ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                        $dasar_arr = $objek['dasar_sungai'] ? json_decode($objek['dasar_sungai'], true) : [];
                        if (!is_array($dasar_arr)) $dasar_arr = [];
                    ?>
                    <?php if(!empty($dasar_arr)): ?>
                    <div class="mt-3">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-2">Dasar Sungai</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($dasar_arr as $d): ?>
                                <span class="px-3 py-1.5 bg-blue-50 text-blue-800 border border-blue-200 rounded-full text-xs font-semibold">
                                    <?= e($d) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                        $pemanfaatan_arr = $objek['pemanfaatan_sungai'] ? json_decode($objek['pemanfaatan_sungai'], true) : [];
                        if (!is_array($pemanfaatan_arr)) $pemanfaatan_arr = [];
                    ?>
                    <?php if(!empty($pemanfaatan_arr)): ?>
                    <div class="mt-3">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-2">Pemanfaatan</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($pemanfaatan_arr as $pm): ?>
                                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold">
                                    <?= e($pm) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        <?php endif; ?>
        
                <?php if($objek['jenis_objek'] === 'taman' && ($objek['taman_pengelola'] || $objek['taman_luas_area'] || $objek['taman_tahun_pembangunan'] || $objek['taman_status_kepemilikan'] || $objek['taman_kategori_tanaman'] || $objek['taman_pemanfaatan'] || $objek['taman_fasilitas'])): ?>
        <!-- INFORMASI DETAIL TAMAN -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-lime-600 to-lime-800 text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="flower-2" class="w-3.5 h-3.5"></i>
                    Informasi Detail Taman
                </span>
            </div>
            
            <div class="p-6 md:p-8 space-y-6">
                
                <!-- A. IDENTITAS TAMAN -->
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-lime-100">
                        <div class="w-7 h-7 rounded-lg bg-lime-100 text-lime-700 flex items-center justify-center">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Identitas Taman</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['taman_pengelola']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Pengelola</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_pengelola']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['taman_luas_area'] && $objek['taman_luas_area'] > 0): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Luas Area</div>
                            <div class="text-sm font-semibold text-slate-800"><?= number_format($objek['taman_luas_area'], 2) ?> m²</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['taman_tahun_pembangunan']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Tahun Pembangunan</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_tahun_pembangunan']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['taman_tahun_revitalisasi']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Tahun Revitalisasi</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_tahun_revitalisasi']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['taman_status_kepemilikan']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 md:col-span-2">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Status Kepemilikan</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_status_kepemilikan']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- B. VEGETASI TAMAN -->
                <?php 
                    $taman_kategori_arr = $objek['taman_kategori_tanaman'] ? json_decode($objek['taman_kategori_tanaman'], true) : [];
                    if (!is_array($taman_kategori_arr)) $taman_kategori_arr = [];
                ?>
                <?php if(!empty($taman_kategori_arr) || $objek['taman_vegetasi_pohon'] || $objek['taman_vegetasi_tanaman']): ?>
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-lime-100">
                        <div class="w-7 h-7 rounded-lg bg-lime-100 text-lime-700 flex items-center justify-center">
                            <i data-lucide="trees" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Vegetasi Taman</h3>
                    </div>
                    
                    <?php if(!empty($taman_kategori_arr)): ?>
                    <div class="mb-3">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-2">Kategori Tanaman</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($taman_kategori_arr as $kt): ?>
                                <span class="px-3 py-1.5 bg-lime-50 text-lime-800 border border-lime-200 rounded-full text-xs font-semibold">
                                    <?= e($kt) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['taman_vegetasi_pohon']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Jenis Pohon Dominan</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_vegetasi_pohon']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['taman_vegetasi_tanaman']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Jenis Tanaman Lain</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_vegetasi_tanaman']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- C. PEMANFAATAN TAMAN -->
                <?php 
                    $taman_pemanfaatan_arr = $objek['taman_pemanfaatan'] ? json_decode($objek['taman_pemanfaatan'], true) : [];
                    if (!is_array($taman_pemanfaatan_arr)) $taman_pemanfaatan_arr = [];
                ?>
                <?php if(!empty($taman_pemanfaatan_arr) || $objek['taman_intensitas']): ?>
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-lime-100">
                        <div class="w-7 h-7 rounded-lg bg-lime-100 text-lime-700 flex items-center justify-center">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Pemanfaatan Taman</h3>
                    </div>
                    
                    <?php if(!empty($taman_pemanfaatan_arr)): ?>
                    <div class="mb-3">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-2">Fungsi Pemanfaatan</div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($taman_pemanfaatan_arr as $pm): ?>
                                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-semibold">
                                    <?= e($pm) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($objek['taman_intensitas']): ?>
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Intensitas Pemanfaatan</div>
                        <div class="text-sm font-semibold text-slate-800"><?= e($objek['taman_intensitas']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- D. FASILITAS TAMAN -->
                <?php 
                    $taman_fasilitas_arr = $objek['taman_fasilitas'] ? json_decode($objek['taman_fasilitas'], true) : [];
                    if (!is_array($taman_fasilitas_arr)) $taman_fasilitas_arr = [];
                ?>
                <?php if(!empty($taman_fasilitas_arr)): ?>
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-lime-100">
                        <div class="w-7 h-7 rounded-lg bg-lime-100 text-lime-700 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Fasilitas Taman</h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach($taman_fasilitas_arr as $fs): ?>
                            <span class="px-3 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-semibold">
                                <?= e($fs) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
        
                <?php if($objek['jenis_objek'] === 'komposting' && ($objek['kompos_pengelola'] || $objek['kompos_metode'] || $objek['kompos_kapasitas'] || $objek['kompos_luas_area'] || $objek['kompos_tahun_mulai'] || $objek['kompos_jumlah_komposter'] || $objek['kompos_kelompok'] || $objek['kompos_bahan_organik'])): ?>
        <!-- INFORMASI DETAIL KOMPOSTING -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-amber-600 to-amber-800 text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="recycle" class="w-3.5 h-3.5"></i>
                    Informasi Detail Komposting
                </span>
            </div>
            
            <div class="p-6 md:p-8 space-y-6">
                
                <!-- A. IDENTITAS KOMPOSTING -->
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-amber-100">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Identitas Komposting</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php if($objek['kompos_pengelola']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Pengelola</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_pengelola']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kompos_metode']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Metode Komposting</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_metode']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kompos_kapasitas'] && $objek['kompos_kapasitas'] > 0): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Kapasitas</div>
                            <div class="text-sm font-semibold text-slate-800"><?= number_format($objek['kompos_kapasitas'], 2) ?> kg</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kompos_luas_area'] && $objek['kompos_luas_area'] > 0): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Luas Area</div>
                            <div class="text-sm font-semibold text-slate-800"><?= number_format($objek['kompos_luas_area'], 2) ?> m²</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kompos_tahun_mulai']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Tahun Mulai</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_tahun_mulai']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kompos_jumlah_komposter']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Jumlah Komposter</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_jumlah_komposter']) ?> unit</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($objek['kompos_kelompok']): ?>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 md:col-span-2">
                            <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Kelompok / Komunitas</div>
                            <div class="text-sm font-semibold text-slate-800"><?= e($objek['kompos_kelompok']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- B. BAHAN ORGANIK -->
                <?php 
                    $kompos_bahan_arr = $objek['kompos_bahan_organik'] ? json_decode($objek['kompos_bahan_organik'], true) : [];
                    if (!is_array($kompos_bahan_arr)) $kompos_bahan_arr = [];
                ?>
                <?php if(!empty($kompos_bahan_arr)): ?>
                <div>
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b-2 border-amber-100">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                            <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">Bahan Organik</h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach($kompos_bahan_arr as $bh): ?>
                            <span class="px-3 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full text-xs font-semibold">
                                <?= e($bh) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>
        
        <!-- MONITORING & EVALUASI (PIC) -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white px-6 py-3 flex items-center justify-between">
                <span class="flex items-center gap-2 font-bold uppercase text-sm">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    Monitoring & Evaluasi
                </span>
            </div>
            
            <div class="p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i data-lucide="user-check" class="w-7 h-7"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-slate-500 font-semibold uppercase mb-1">Penanggung Jawab Monitoring & Evaluasi</div>
                        <div class="text-lg font-bold text-slate-800 mb-1">
                            <?= e($objek['pic_monev_nama'] ?: '-') ?>
                        </div>
                        <?php if($objek['pic_monev_peran']): ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                            <i data-lucide="badge-check" class="w-3 h-3"></i>
                            <?= e($objek['pic_monev_peran']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- TIMELINE MONITORING -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="activity" class="w-6 h-6 text-emerald-700"></i>
                    Riwayat Monitoring
                    <span class="text-sm font-normal text-slate-400">(<?= count($data_laporan) ?>)</span>
                </h2>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=lapor&objek_id=<?= $objek['id'] ?>" class="px-4 py-2 bg-emerald-700 text-white font-bold rounded-full hover:bg-emerald-800 transition flex items-center gap-2 text-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Buat Laporan
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($data_laporan)): ?>
                <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
                    <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                    <p class="text-slate-500 font-medium mb-2">Belum ada laporan monitoring</p>
                    <p class="text-sm text-slate-400">Laporan untuk objek ini akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                               <?php foreach($data_laporan as $l): 
                    $jenis_l = $l['jenis_objek'];
                    
                    // Cek apakah ada detail monitoring
                    if ($jenis_l === 'sungai') {
                        $ada_detail = ($l['tanggal_monitoring'] || $l['debit_air_normal'] || $l['debit_air_pasang'] || $l['debit_air_surut'] || $l['tinggi_muka_air'] || $l['lebar_sungai_monitor'] || $l['kedalaman_air'] || $l['kondisi_debit'] || $l['kondisi_air_visual'] || $l['warna_air'] || $l['bau_air'] || $l['sampah_sungai'] || $l['vegetasi_sempadan'] || $l['erosi_sungai'] || $l['kualitas_ph'] || $l['kualitas_suhu'] || $l['kualitas_tds'] || $l['kualitas_do'] || $l['kualitas_kekeruhan'] || $l['risiko'] || $l['rekomendasi']);
                        
                        $kondisi_list = [
                            'kondisi_debit'      => 'Debit Air',
                            'kondisi_air_visual' => 'Air (Visual)',
                            'warna_air'          => 'Warna Air',
                            'bau_air'            => 'Bau Air',
                            'sampah_sungai'      => 'Sampah',
                            'vegetasi_sempadan'  => 'Vegetasi Sempadan',
                            'erosi_sungai'       => 'Erosi/Longsor',
                        ];
                    } elseif ($jenis_l === 'taman') {
                    $ada_detail = ($l['tanggal_monitoring'] || $l['kondisi_vegetasi'] || $l['kondisi_fasilitas'] || $l['kebersihan_taman'] || $l['kondisi_lingkungan'] || $l['kondisi_drainase'] || $l['risiko'] || $l['rekomendasi']);
                    
                    $kondisi_list = [
                        'kondisi_vegetasi'   => 'Vegetasi',
                        'kondisi_fasilitas'  => 'Fasilitas',
                        'kebersihan_taman'   => 'Kebersihan',
                        'kondisi_lingkungan' => 'Lingkungan',
                        'kondisi_drainase'   => 'Drainase',
                    ];
                    } elseif ($jenis_l === 'komposting') {
                    $ada_detail = ($l['tanggal_monitoring'] || $l['kompos_kelembapan'] || $l['kompos_suhu'] || $l['kompos_aerasi'] || $l['kompos_bau'] || $l['kompos_hama'] || $l['kompos_kecepatan_penguraian'] || $l['kompos_kondisi_wadah'] || $l['kompos_kebersihan_area'] || $l['kompos_status'] || $l['kompos_warna'] || $l['kompos_tekstur'] || $l['kompos_aroma'] || $l['kompos_kematangan'] || $l['kompos_kebersihan_anorganik'] || $l['kompos_volume_awal'] || $l['kompos_volume_akhir'] || $l['kompos_berat_kompos'] || $l['kompos_pemanfaatan'] || $l['kompos_dampak'] || $l['kompos_status_operasional'] || $l['kompos_faktor_keberlanjutan'] || $l['risiko'] || $l['rekomendasi']);
                    
                    $kondisi_list = [
                        'kompos_kelembapan'           => 'Kelembapan',
                        'kompos_aerasi'               => 'Aerasi',
                        'kompos_bau'                  => 'Bau',
                        'kompos_hama'                 => 'Hama',
                        'kompos_kecepatan_penguraian' => 'Penguraian',
                        'kompos_kondisi_wadah'        => 'Wadah',
                        'kompos_kebersihan_area'      => 'Kebersihan Area',
                    ];
                } else {
                    $ada_detail = ($l['tanggal_monitoring'] ||
                        $l['diameter_batang'] || $l['tinggi_pohon'] ||
                        $l['lebar_kanopi'] || $l['lingkungan_tumbuh'] ||
                        $l['penilaian_kesehatan'] || $l['risiko'] ||
                        $l['rekomendasi']);
                    
                    $kondisi_list = [
                        'kondisi_pertumbuhan' => 'Pertumbuhan',
                        'kondisi_batang'      => 'Batang',
                        'kondisi_kanopi'      => 'Kanopi',
                        'kondisi_akar'        => 'Akar',
                        'kondisi_cabang'      => 'Cabang',
                        'kondisi_daun'        => 'Daun',
                        'kondisi_buah'        => 'Buah',
                        'kondisi_bunga'       => 'Bunga',
                        'kondisi_lainnya'     => 'Lainnya',
                    ];
                }                    
                    // Badge warna (berdasarkan penilaian_kesehatan atau kondisi_debit)
                    $badge_warna = 'bg-emerald-50 text-emerald-700';
                    $badge_teks = '';
                    
                    if ($jenis_l === 'sungai') {
                        $badge_teks = $l['kondisi_debit'] ?: '';
                        if ($l['kondisi_debit'] === 'Kering') $badge_warna = 'bg-rose-50 text-rose-700';
                        elseif ($l['kondisi_debit'] === 'Sangat tinggi') $badge_warna = 'bg-rose-50 text-rose-700';
                        elseif ($l['kondisi_debit'] === 'Rendah' || $l['kondisi_debit'] === 'Tinggi') $badge_warna = 'bg-amber-50 text-amber-700';
                        
                    } elseif ($jenis_l === 'taman') {
                    $badge_teks = $l['kondisi_vegetasi'] ?: '';
                    if ($l['kondisi_vegetasi'] === 'Rusak' || $l['kondisi_vegetasi'] === 'Sangat Rusak') $badge_warna = 'bg-rose-50 text-rose-700';
                    elseif ($l['kondisi_vegetasi'] === 'Cukup') $badge_warna = 'bg-amber-50 text-amber-700';
                    } elseif ($jenis_l === 'komposting') {
                    $badge_teks = $l['kompos_status'] ?: '';
                    if ($l['kompos_status'] === 'Gagal') $badge_warna = 'bg-rose-50 text-rose-700';
                    elseif ($l['kompos_status'] === 'Sedang berlangsung') $badge_warna = 'bg-amber-50 text-amber-700';
                    elseif ($l['kompos_status'] === 'Belum mulai') $badge_warna = 'bg-slate-100 text-slate-700';
                } else {
                    $badge_teks = $l['penilaian_kesehatan'] ?: '';
                        if ($l['penilaian_kesehatan'] === 'Rawan') $badge_warna = 'bg-amber-50 text-amber-700';
                        elseif ($l['penilaian_kesehatan'] === 'Kritis') $badge_warna = 'bg-rose-50 text-rose-700';
                        elseif ($l['penilaian_kesehatan'] === 'Mati') $badge_warna = 'bg-slate-100 text-slate-700';
                    }
                    
                    // Warna border card: sesuai jenis objek
if ($jenis_l === 'sungai') {
    $card_border = 'border-blue-100';
    $header_grad = 'from-blue-50 to-blue-100';
    $header_ikon = 'text-blue-700';
    $header_teks = 'text-blue-800';
} elseif ($jenis_l === 'taman') {
    $card_border = 'border-lime-100';
    $header_grad = 'from-lime-50 to-lime-100';
    $header_ikon = 'text-lime-700';
    $header_teks = 'text-lime-800';
} elseif ($jenis_l === 'komposting') {
    $card_border = 'border-amber-100';
    $header_grad = 'from-amber-50 to-amber-100';
    $header_ikon = 'text-amber-700';
    $header_teks = 'text-amber-800';
} else {
    $card_border = 'border-emerald-100';
    $header_grad = 'from-emerald-50 to-emerald-100';
    $header_ikon = 'text-emerald-700';
    $header_teks = 'text-emerald-800';
}
                ?>
                <div class="bg-white rounded-2xl border-2 <?= $card_border ?> overflow-hidden">
                    <!-- HEADER -->
                    <div class="bg-gradient-to-r <?= $header_grad ?> px-4 py-3 flex justify-between items-center flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 <?= $header_ikon ?>"></i>
                            <span class="text-sm font-bold <?= $header_teks ?>">
                                <?= date('d F Y, H:i', strtotime($l['tanggal_buat'])) ?>
                            </span>
                        </div>
                        <?php if($badge_teks): ?>
                        <span class="text-xs px-3 py-1 rounded-full font-bold <?= $badge_warna ?>">
                            <?= e($badge_teks) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        
                        <?php if($jenis_l === 'sungai'): ?>
                        <!-- ============ TAMPILAN SUNGAI ============ -->
                        
                        <!-- DATA MONITORING -->
                        <?php if($l['tanggal_monitoring'] || $l['debit_air_normal'] || $l['debit_air_pasang'] || $l['debit_air_surut'] || $l['tinggi_muka_air'] || $l['lebar_sungai_monitor'] || $l['kedalaman_air']): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-slate-100">
                                <i data-lucide="ruler" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Data Monitoring</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php if($l['tanggal_monitoring']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Tanggal</div>
                                    <div class="text-xs font-bold text-slate-800"><?= date('d/m/Y', strtotime($l['tanggal_monitoring'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['debit_air_normal']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Debit Normal</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['debit_air_normal'], 2) ?> m³/s</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['debit_air_pasang']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Debit Pasang</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['debit_air_pasang'], 2) ?> m³/s</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['debit_air_surut']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Debit Surut</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['debit_air_surut'], 2) ?> m³/s</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['tinggi_muka_air']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Tinggi Muka</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['tinggi_muka_air'], 2) ?> m</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['lebar_sungai_monitor']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Lebar Sungai</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['lebar_sungai_monitor'], 2) ?> m</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['kedalaman_air']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Kedalaman</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['kedalaman_air'], 2) ?> m</div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- INDIKATOR KONDISI (SUNGAI) -->
                        <?php 
                        $ada_kondisi = false;
                        foreach ($kondisi_list as $key => $label) { if ($l[$key]) { $ada_kondisi = true; break; } }
                        ?>
                        <?php if($ada_kondisi): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-slate-100">
                                <i data-lucide="waves" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Indikator Kondisi</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach ($kondisi_list as $key => $label): ?>
                                    <?php if($l[$key]): 
                                        $warna_k = 'bg-slate-50 text-slate-700';
                                        if ($l[$key] === 'Normal' || $l[$key] === 'Jernih' || $l[$key] === 'Baik' || $l[$key] === 'Tidak ada' || $l[$key] === 'Tidak berbau' || $l[$key] === 'Tidak Ada') $warna_k = 'bg-emerald-50 text-emerald-700';
                                        elseif ($l[$key] === 'Rendah' || $l[$key] === 'Tinggi' || $l[$key] === 'Agak keruh' || $l[$key] === 'Cukup' || $l[$key] === 'Sedikit' || $l[$key] === 'Sedikit berbau' || $l[$key] === 'Ringan' || $l[$key] === 'Kecokelatan' || $l[$key] === 'Sedang') $warna_k = 'bg-amber-50 text-amber-700';
                                        elseif ($l[$key] === 'Sangat tinggi' || $l[$key] === 'Kering' || $l[$key] === 'Keruh' || $l[$key] === 'Sangat keruh' || $l[$key] === 'Rusak' || $l[$key] === 'Sangat rusak' || $l[$key] === 'Banyak' || $l[$key] === 'Sangat banyak' || $l[$key] === 'Berbau' || $l[$key] === 'Sangat menyengat' || $l[$key] === 'Kehitaman' || $l[$key] === 'Kehijauan' || $l[$key] === 'Berat') $warna_k = 'bg-rose-50 text-rose-700';
                                    ?>
                                    <div class="rounded-lg p-2 <?= $warna_k ?>">
                                        <div class="text-[10px] font-semibold uppercase opacity-70"><?= $label ?></div>
                                        <div class="text-xs font-bold"><?= e($l[$key]) ?></div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- KUALITAS AIR (SUNGAI) -->
                        <?php if($l['kualitas_ph'] || $l['kualitas_suhu'] || $l['kualitas_tds'] || $l['kualitas_do'] || $l['kualitas_kekeruhan']): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-slate-100">
                                <i data-lucide="flask-conical" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Kualitas Air</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                                <?php if($l['kualitas_ph']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">pH</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['kualitas_ph'], 2) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['kualitas_suhu']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Suhu</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['kualitas_suhu'], 1) ?>°C</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['kualitas_tds']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">TDS</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['kualitas_tds'], 0) ?> mg/L</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['kualitas_do']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">DO</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['kualitas_do'], 2) ?> mg/L</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['kualitas_kekeruhan']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Kekeruhan</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['kualitas_kekeruhan'], 1) ?> NTU</div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <!-- ============ TAMPILAN POHON (lama) ============ -->
                        
                        <!-- DATA PENGUKURAN -->
                        <?php if($l['tanggal_monitoring'] || $l['diameter_batang'] || $l['tinggi_pohon'] || $l['lebar_kanopi'] || $l['lingkungan_tumbuh']): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-slate-100">
                                <i data-lucide="ruler" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Data Pengukuran</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php if($l['tanggal_monitoring']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Tanggal</div>
                                    <div class="text-xs font-bold text-slate-800"><?= date('d/m/Y', strtotime($l['tanggal_monitoring'])) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['diameter_batang']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">DBH</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['diameter_batang'], 1) ?> cm</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['tinggi_pohon']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Tinggi</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['tinggi_pohon'], 1) ?> m</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['lebar_kanopi']): ?>
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Kanopi</div>
                                    <div class="text-xs font-bold text-slate-800"><?= number_format($l['lebar_kanopi'], 1) ?> m</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['lingkungan_tumbuh']): ?>
                                <div class="bg-slate-50 rounded-lg p-2 col-span-2 md:col-span-1">
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Lingkungan</div>
                                    <div class="text-xs font-bold text-slate-800"><?= e($l['lingkungan_tumbuh']) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- KONDISI (5 LEVEL) -->
                        <?php 
                        $ada_kondisi = false;
                        foreach ($kondisi_list as $key => $label) { if ($l[$key]) { $ada_kondisi = true; break; } }
                        ?>
                        <?php if($ada_kondisi): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-slate-100">
                                <i data-lucide="leaf" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Kondisi</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach ($kondisi_list as $key => $label): ?>
                                    <?php if($l[$key]): 
                                        $warna_k = 'bg-slate-50 text-slate-700';
                                        if ($l[$key] === 'Sangat Baik') $warna_k = 'bg-emerald-50 text-emerald-700';
                                        elseif ($l[$key] === 'Baik') $warna_k = 'bg-emerald-50 text-emerald-600';
                                        elseif ($l[$key] === 'Cukup') $warna_k = 'bg-amber-50 text-amber-700';
                                        elseif ($l[$key] === 'Buruk') $warna_k = 'bg-rose-50 text-rose-700';
                                        elseif ($l[$key] === 'Sangat Buruk') $warna_k = 'bg-rose-100 text-rose-800';
                                    ?>
                                    <div class="rounded-lg p-2 <?= $warna_k ?>">
                                        <div class="text-[10px] font-semibold uppercase opacity-70"><?= $label ?></div>
                                        <div class="text-xs font-bold"><?= e($l[$key]) ?></div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php endif; ?>
                     
                     <!-- POTENSI CH₄ (SUNGAI) -->
                        <?php if($l['jenis_objek'] === 'sungai' && $l['serapan_karbon_ilmiah'] && $l['metode_hitung'] === 'ch4_estimasi'): 
                            $ch4_level = $l['umur_teks'] ?: 'Rendah';
                            $ch4_bg = 'bg-emerald-50';
                            $ch4_border = 'border-emerald-200';
                            $ch4_text = 'text-emerald-800';
                            $ch4_badge = 'bg-emerald-600';
                            if ($ch4_level === 'Tinggi') {
                                $ch4_bg = 'bg-rose-50'; $ch4_border = 'border-rose-200';
                                $ch4_text = 'text-rose-800'; $ch4_badge = 'bg-rose-600';
                            } elseif ($ch4_level === 'Sedang') {
                                $ch4_bg = 'bg-amber-50'; $ch4_border = 'border-amber-200';
                                $ch4_text = 'text-amber-800'; $ch4_badge = 'bg-amber-600';
                            }
                        ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-blue-100">
                                <i data-lucide="waves" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Potensi Emisi CH₄</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="<?= $ch4_bg ?> <?= $ch4_border ?> border rounded-lg p-3">
                                    <div class="text-[10px] <?= $ch4_text ?> font-semibold uppercase mb-1 flex items-center justify-between gap-2">
                                        <span>Estimasi Metana</span>
                                        <span class="px-2 py-0.5 <?= $ch4_badge ?> text-white rounded-full text-[9px] font-bold shadow-sm whitespace-nowrap">IPCC 2019</span>
                                    </div>
                                    <div class="text-sm font-bold <?= $ch4_text ?>"><?= number_format($l['serapan_karbon_ilmiah'], 2) ?> kg CO₂e</div>
                                </div>
                                <div class="<?= $ch4_bg ?> <?= $ch4_border ?> border rounded-lg p-3">
                                    <div class="text-[10px] <?= $ch4_text ?> font-semibold uppercase mb-1">Level Risiko</div>
                                    <div class="text-sm font-bold <?= $ch4_text ?>"><?= e($ch4_level) ?></div>
                                    <?php if($l['kualitas_do']): ?>
                                    <div class="text-[10px] <?= $ch4_text ?> mt-1">DO: <?= number_format($l['kualitas_do'], 2) ?> mg/L</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- UMUR & KARBON -->
                        <?php if($l['jenis_objek'] !== 'sungai' && ($l['umur_hari'] || $l['serapan_karbon_ilmiah'])): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-2 pb-1 border-b border-slate-100">
                                <i data-lucide="calendar-clock" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Umur & Karbon</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <?php if($l['umur_hari'] && $l['jenis_objek'] === 'pohon'): ?>
                                <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100">
                                    <div class="text-[10px] text-emerald-700 font-semibold uppercase mb-1">Umur Pohon</div>
                                    <div class="text-sm font-bold text-emerald-900"><?= e($l['umur_teks']) ?></div>
                                    <div class="text-[10px] text-emerald-600 mt-1">Total: <?= number_format($l['umur_hari']) ?> hari</div>
                                </div>
                                <?php endif; ?>
                                <?php if($l['serapan_karbon_ilmiah']): ?>
                                <div class="bg-amber-50 rounded-lg p-3 border border-amber-100">
                                    <div class="text-[10px] text-amber-700 font-semibold uppercase mb-1 flex items-center justify-between gap-2">
                                        <span>
                                            <?php if($l['jenis_objek'] === 'komposting'): ?>
                                                CO₂ Dicegah
                                            <?php else: ?>
                                                Serapan Karbon
                                            <?php endif; ?>
                                        </span>
                                        <?php if($l['metode_hitung'] === 'chave_2005'): ?>
                                            <span class="px-2 py-0.5 bg-emerald-600 text-white rounded-full text-[9px] font-bold shadow-sm whitespace-nowrap">Chave 2005</span>
                                        <?php elseif($l['metode_hitung'] === 'rth_konversi'): ?>
                                            <span class="px-2 py-0.5 bg-lime-600 text-white rounded-full text-[9px] font-bold shadow-sm whitespace-nowrap">RTH Konversi</span>
                                        <?php elseif($l['metode_hitung'] === 'ipcc_kompos'): ?>
                                            <span class="px-2 py-0.5 bg-amber-600 text-white rounded-full text-[9px] font-bold shadow-sm whitespace-nowrap">IPCC</span>
                                        <?php elseif($l['metode_hitung'] === 'umur_fallback'): ?>
                                            <span class="px-2 py-0.5 bg-slate-500 text-white rounded-full text-[9px] font-bold shadow-sm whitespace-nowrap">Estimasi</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm font-bold text-amber-900"><?= number_format($l['serapan_karbon_ilmiah'], 2) ?> kg CO₂</div>
                                    <?php if($l['jenis_objek'] === 'komposting' && $l['volume_sampah'] > 0): ?>
                                    <div class="text-[10px] text-amber-700 mt-1">Dari <?= number_format($l['volume_sampah']) ?> kg sampah organik</div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- RISIKO & REKOMENDASI (BERLAKU UNTUK KEDUA JENIS) -->
                        <?php if($l['risiko'] || $l['rekomendasi']): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php if($l['risiko']): ?>
                            <div class="bg-rose-50 border border-rose-100 rounded-lg p-3">
                                <div class="flex items-center gap-1 mb-1">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-600"></i>
                                    <span class="text-[10px] font-bold text-rose-700 uppercase">Potensi Dampak</span>
                                </div>
                                <div class="text-xs text-rose-900"><?= e($l['risiko']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if($l['rekomendasi']): ?>
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                                <div class="flex items-center gap-1 mb-1">
                                    <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-blue-600"></i>
                                    <span class="text-[10px] font-bold text-blue-700 uppercase">Rekomendasi</span>
                                </div>
                                <div class="text-xs text-blue-900"><?= e($l['rekomendasi']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- PIC & CATATAN -->
                        <?php if($l['pic_monev_nama'] || $l['catatan']): ?>
                        <div class="border-t border-slate-100 pt-3 space-y-2">
                            <?php if($l['pic_monev_nama']): ?>
                            <div class="flex items-center gap-2 text-xs flex-wrap">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-blue-600"></i>
                                <span class="font-semibold text-slate-700">PIC:</span>
                                <span class="text-slate-800"><?= e($l['pic_monev_nama']) ?></span>
                                <?php if($l['pic_monev_peran']): ?>
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold"><?= e($l['pic_monev_peran']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if($l['catatan']): ?>
                            <div class="bg-slate-50 rounded-lg p-2 text-xs text-slate-700">
                                <span class="font-semibold">Catatan:</span> <?= e($l['catatan']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
        </div>
    </main>
                <?php endif; ?>
        </div>
    <?php elseif ($halaman == 'objek_daftar'): ?>
    <?php if (!isset($_SESSION['user_id'])): 
        notif('gagal','Silakan login untuk mendaftarkan objek.');
        redirect('index.php?page=login');
    else: ?>
    <main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <a href="index.php?page=objek" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Daftar Objek
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="file-plus" class="w-7 h-7 text-emerald-700"></i> Daftar Objek Baru
            </h1>
            <p class="text-sm text-slate-500">Setiap objek akan mendapat kode unik otomatis.</p>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi_daftar_objek" value="1">

    <!-- BAGIAN 1: JENIS & NAMA -->
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">1</div>
                    <div class="font-bold text-emerald-800">Jenis & Nama Objek</div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Jenis Objek</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="cursor-pointer group">
                            <input type="radio" name="jenis_objek" value="pohon" onchange="togglePohonFields()" class="peer hidden" checked>
                            <div class="border-2 border-slate-200 peer-checked:border-emerald-700 peer-checked:bg-gradient-to-br peer-checked:from-emerald-50 peer-checked:to-emerald-100 rounded-xl p-3 text-center transition-all duration-300 group-hover:border-emerald-300 group-hover:shadow-md group-hover:-translate-y-0.5">
                                <i data-lucide="tree-pine" class="w-6 h-6 mx-auto mb-1 text-emerald-700 transition-transform group-hover:scale-110"></i>
                                <div class="text-xs font-bold">Pohon</div>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="jenis_objek" value="sungai" onchange="togglePohonFields()" class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-blue-700 peer-checked:bg-gradient-to-br peer-checked:from-blue-50 peer-checked:to-blue-100 rounded-xl p-3 text-center transition-all duration-300 group-hover:border-blue-300 group-hover:shadow-md group-hover:-translate-y-0.5">
                                <i data-lucide="waves" class="w-6 h-6 mx-auto mb-1 text-blue-700 transition-transform group-hover:scale-110"></i>
                                <div class="text-xs font-bold">Sungai</div>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="jenis_objek" value="taman" onchange="togglePohonFields()" class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-lime-700 peer-checked:bg-gradient-to-br peer-checked:from-lime-50 peer-checked:to-lime-100 rounded-xl p-3 text-center transition-all duration-300 group-hover:border-lime-300 group-hover:shadow-md group-hover:-translate-y-0.5">
                                <i data-lucide="flower-2" class="w-6 h-6 mx-auto mb-1 text-lime-700 transition-transform group-hover:scale-110"></i>
                                <div class="text-xs font-bold">Taman</div>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="jenis_objek" value="komposting" onchange="togglePohonFields()" class="peer hidden">
                            <div class="border-2 border-slate-200 peer-checked:border-amber-700 peer-checked:bg-gradient-to-br peer-checked:from-amber-50 peer-checked:to-amber-100 rounded-xl p-3 text-center transition-all duration-300 group-hover:border-amber-300 group-hover:shadow-md group-hover:-translate-y-0.5">
                                <i data-lucide="recycle" class="w-6 h-6 mx-auto mb-1 text-amber-700 transition-transform group-hover:scale-110"></i>
                                <div class="text-xs font-bold">Komposting</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Nama Objek</label>
                    <input type="text" name="nama_objek" required maxlength="150"
                        placeholder="Contoh: Manggis Manis / Sungai Citarum / Komposter RT 05"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Lokasi / Wilayah</label>
                    <input type="text" name="lokasi" maxlength="255"
                        placeholder="Contoh: Taman Kota Blok C, Bandung"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- ============================================ -->
                <!-- FIELD KHUSUS POHON — MULAI                    -->
                <!-- ============================================ -->
                <div id="box_pohon_fields" style="display:none;" class="space-y-4 mt-4">

                    <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="tree-pine" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-emerald-900">A. Identitas Pohon</div>
                                <div class="text-xs text-emerald-600">Data ilmiah & klasifikasi</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama Latin <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="nama_latin" maxlength="150" placeholder="Garcinia mangostana"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Famili <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="famili" maxlength="100" placeholder="Clusiaceae"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Asal Daerah <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="asal_daerah" maxlength="150" placeholder="Kalimantan"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama Daerah Lain <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="nama_daerah_lain" maxlength="200" placeholder="Manggis, Manggu"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status Pohon</label>
                            <select name="status_pohon" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <option value="liar">Liar</option>
                                <option value="ditanam">Ditanam</option>
                                <option value="dibudidayakan">Dibudidayakan</option>
                                <option value="penghijauan">Pohon Penghijauan</option>
                                <option value="konservasi">Pohon Konservasi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div
                        
                          <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">
                                Kategori IAS
                                <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <p class="text-xs text-slate-500 mb-2">Invasive Alien Species — jenis asing yang berpotensi invasif.</p>
                            <select name="kategori_ias" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                                <option value="">-- Pilih --</option>
                                <option value="IAS">IAS (Invasive Alien Species)</option>
                                <option value="Non-IAS">Non-IAS (Native/Lokal)</option>
                                <option value="Belum Diketahui">Belum Diketahui</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Pemanfaatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <textarea name="potensi_pemanfaatan" rows="2" maxlength="500" placeholder="Buah, kayu, peneduh..."
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                        </div>
                    <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-emerald-900">B. Karakteristik</div>
                                <div class="text-xs text-emerald-600">Ciri fisik & manfaat</div>
                            </div>
                        </div>
                        <label class="block text-sm font-semibold mb-2 text-slate-700">Karakteristik Fisik</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php
                            $karakteristik_list = [
                                'berbiji' => 'Berbiji', 'berbuah' => 'Berbuah', 'berbunga' => 'Berbunga',
                                'akar_tunggang' => 'Akar Tunggang', 'akar_serabut' => 'Akar Serabut',
                                'berkayu' => 'Berkayu', 'getah' => 'Memiliki Getah', 'duri' => 'Memiliki Duri',
                                'evergreen' => 'Evergreen', 'menggugurkan_daun' => 'Menggugurkan Daun',
                            ];
                            foreach ($karakteristik_list as $key => $label): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="karakteristik[]" value="<?= $key ?>" class="peer hidden">
                                    <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-sm text-slate-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:font-semibold hover:border-emerald-300 transition flex items-center gap-2">
                                        <span><?= $label ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="block text-sm font-semibold mb-2 mt-5 text-slate-700">Manfaat</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php
                            $manfaat_list = [
                                'ekologis' => 'Ekologis', 'ekonomi' => 'Ekonomi', 'pangan' => 'Pangan',
                                'obat' => 'Obat', 'budaya' => 'Budaya', 'lainnya' => 'Lainnya',
                            ];
                            foreach ($manfaat_list as $key => $label): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="manfaat[]" value="<?= $key ?>" class="peer hidden">
                                    <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-sm text-slate-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:font-semibold hover:border-emerald-300 transition flex items-center gap-2">
                                        <span><?= $label ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="sprout" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-emerald-900">C. Penanaman</div>
                                <div class="text-xs text-emerald-600">Data penanaman & bibit</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Tanam <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="date" name="tanggal_tanam"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Sumber Dana <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <select name="sumber_dana" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="">-- Pilih --</option>
                                    <option value="APBN">APBN</option>
                                    <option value="APBD">APBD</option>
                                    <option value="CSR">CSR</option>
                                    <option value="Swadaya">Swadaya</option>
                                    <option value="Hibah">Hibah</option>
                                    <option value="Donasi">Donasi</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Penyelenggara <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="penyelenggara" maxlength="200" placeholder="Dinas Lingkungan Hidup"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Pelaksana <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="pelaksana" maxlength="200" placeholder="Komunitas Hijau"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jenis Bibit <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="jenis_bibit" maxlength="100" placeholder="Bibit unggul"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Asal Bibit <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="asal_bibit" maxlength="150" placeholder="Persemaian Dinas"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Alasan Penanaman <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <textarea name="alasan_penanaman" rows="2" maxlength="500" placeholder="Penghijauan lingkungan sekolah..."
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                        </div>
                    </div>

                </div>
            </div>
                <!-- ============================================ -->
                <!-- FIELD KHUSUS POHON — SELESAI                  -->
                <!-- ============================================ -->
                
                                <!-- ============================================ -->
                <!-- FIELD KHUSUS SUNGAI — MULAI                   -->
                <!-- ============================================ -->
                <div id="box_sungai_fields" style="display:none;" class="space-y-4 mt-4">

                    <!-- A. IDENTITAS SUNGAI -->
                    <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="waves" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-blue-900">A. Identitas Sungai</div>
                                <div class="text-xs text-blue-600">Data fisik & geografis</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama Lain <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="nama_lain_sungai" maxlength="150" placeholder="Nama lain sungai"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Sumber Mata Air <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="sumber_mata_air" maxlength="200" placeholder="Contoh: Mata air Gunung X"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Panjang Sungai (km) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="panjang_sungai" step="0.01" min="0" max="999999" inputmode="decimal" placeholder="Contoh: 25.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lebar Rata-rata (m) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="lebar_rata_rata" step="0.01" min="0" max="999999" inputmode="decimal" placeholder="Contoh: 8.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kedalaman Rata-rata (m) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="kedalaman_rata_rata" step="0.01" min="0" max="999999" inputmode="decimal" placeholder="Contoh: 1.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Daerah Hulu <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="daerah_hulu" maxlength="200" placeholder="Contoh: Kecamatan X"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Daerah Hilir <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="daerah_hilir" maxlength="200" placeholder="Contoh: Kota Y"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Muara <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="muara" maxlength="200" placeholder="Contoh: Laut Jawa / Sungai Induk"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- B. KARAKTERISTIK SUNGAI -->
                    <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="droplets" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-blue-900">B. Karakteristik Sungai</div>
                                <div class="text-xs text-blue-600">Tipe & pemanfaatan</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Jenis Sungai</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                <?php foreach(['Alami','Buatan','Anak Sungai','Saluran'] as $js): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_sungai" value="<?= $js ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold hover:border-blue-300 transition text-center">
                                            <?= $js ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Dasar Sungai <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php foreach(['Berbatu','Berlumpur','Berpasir'] as $ds): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="dasar_sungai[]" value="<?= $ds ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold hover:border-blue-300 transition flex items-center gap-2">
                                            <span><?= $ds ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-slate-700">Ada Vegetasi?</label>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_vegetasi" value="1" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Ya</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_vegetasi" value="0" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Tidak</div>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-slate-700">Ada Sempadan?</label>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_sempadan" value="1" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Ya</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="ada_sempadan" value="0" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold text-center transition">Tidak</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Pemanfaatan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php
                                $pemanfaatan_list = [
                                    'Air baku','Irigasi','Pertanian','Perikanan','Peternakan',
                                    'Rekreasi','Kebutuhan rumah tangga','Tidak dimanfaatkan','Lainnya'
                                ];
                                foreach ($pemanfaatan_list as $pm): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="pemanfaatan_sungai[]" value="<?= $pm ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-checked:font-semibold hover:border-blue-300 transition flex items-center gap-2">
                                            <span><?= $pm ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- FIELD KHUSUS SUNGAI — SELESAI                  -->
                <!-- ============================================ -->
                
                                <!-- ============================================ -->
                <!-- FIELD KHUSUS TAMAN — MULAI                    -->
                <!-- ============================================ -->
                <div id="box_taman_fields" style="display:none;" class="space-y-4 mt-4">

                    <!-- A. IDENTITAS TAMAN -->
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="flower-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">A. Identitas Taman</div>
                                <div class="text-xs text-lime-700">Pengelolaan & lokasi</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Pengelola <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="taman_pengelola" maxlength="200" placeholder="Contoh: Dinas Lingkungan Hidup"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Luas Area (m²) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="taman_luas_area" step="0.01" min="0" max="9999999" inputmode="decimal" placeholder="Contoh: 1500.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tahun Pembangunan <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="taman_tahun_pembangunan" min="1900" max="2100" inputmode="numeric" placeholder="Contoh: 2015"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tahun Revitalisasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="taman_tahun_revitalisasi" min="1900" max="2100" inputmode="numeric" placeholder="Contoh: 2020"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status Kepemilikan / Pengelolaan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" name="taman_status_kepemilikan" maxlength="100" placeholder="Contoh: Pemerintah Kota / Swasta / Komunitas"
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- B. VEGETASI TAMAN -->
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="trees" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">B. Vegetasi Taman</div>
                                <div class="text-xs text-lime-700">Jenis & kategori tanaman</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Kategori Tanaman <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php
                                $kategori_list = ['Tanaman Obat','Tanaman Pangan','Tanaman Hias','Tanaman Lokal','Tanaman Endemik','Vegetasi Penutup Tanah'];
                                foreach ($kategori_list as $kt): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="taman_kategori_tanaman[]" value="<?= $kt ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold hover:border-lime-300 transition flex items-center gap-2">
                                            <span><?= $kt ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jenis Pohon Dominan <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="taman_vegetasi_pohon" maxlength="300" placeholder="Contoh: Mahoni, Beringin, Trembesi"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jenis Tanaman Lain <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="taman_vegetasi_tanaman" maxlength="300" placeholder="Contoh: Bougenvil, Kamboja"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- C. PEMANFAATAN TAMAN -->
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">C. Pemanfaatan Taman</div>
                                <div class="text-xs text-lime-700">Fungsi & intensitas</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Fungsi Pemanfaatan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php
                                $fungsi_list = ['Ruang publik','Rekreasi','Olahraga','Edukasi','Bermain anak','Interaksi sosial','Konservasi','Kegiatan budaya','Kegiatan komunitas','Lainnya'];
                                foreach ($fungsi_list as $fg): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="taman_pemanfaatan[]" value="<?= $fg ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold hover:border-lime-300 transition flex items-center gap-2">
                                            <span><?= $fg ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Intensitas Pemanfaatan</label>
                            <select name="taman_intensitas" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition">
                                <option value="">-- Pilih --</option>
                                <option value="Sangat tinggi">Sangat Tinggi</option>
                                <option value="Tinggi">Tinggi</option>
                                <option value="Sedang">Sedang</option>
                                <option value="Rendah">Rendah</option>
                                <option value="Tidak dimanfaatkan">Tidak Dimanfaatkan</option>
                            </select>
                        </div>
                    </div>

                    <!-- D. FASILITAS TAMAN -->
                    <div class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lime-900">D. Fasilitas Taman</div>
                                <div class="text-xs text-lime-700">Sarana & prasarana</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Fasilitas yang Tersedia <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php
                                $fasilitas_list = ['Bangku','Tempat sampah','Lampu penerangan','Toilet','Gazebo','Jalur pejalan kaki','Jalur difabel','Playground','Fasilitas olahraga','Papan informasi','Area parkir','Drainase','Sistem penyiraman','Fasilitas lainnya'];
                                foreach ($fasilitas_list as $fs): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="taman_fasilitas[]" value="<?= $fs ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-lime-600 peer-checked:bg-lime-50 peer-checked:text-lime-800 peer-checked:font-semibold hover:border-lime-300 transition flex items-center gap-2">
                                            <span><?= $fs ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- FIELD KHUSUS TAMAN — SELESAI                  -->
                <!-- ============================================ -->

                <!-- ============================================ -->
                <!-- FIELD KHUSUS KOMPOSTING — MULAI               -->
                <!-- ============================================ -->
                <div id="box_komposting_fields" style="display:none;" class="space-y-4 mt-4">

                    <!-- A. IDENTITAS KOMPOSTING -->
                    <div class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="recycle" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-amber-900">A. Identitas Komposting</div>
                                <div class="text-xs text-amber-700">Pengelola & kapasitas</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Pengelola <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="kompos_pengelola" maxlength="200" placeholder="Contoh: Komunitas RW 05"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Metode Komposting <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <select name="kompos_metode" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
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
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kapasitas (kg) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="kompos_kapasitas" step="0.01" min="0" max="999999" inputmode="decimal" placeholder="Contoh: 500"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Luas Area (m²) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="kompos_luas_area" step="0.01" min="0" max="999999" inputmode="decimal" placeholder="Contoh: 25.5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tahun Mulai <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="kompos_tahun_mulai" min="1900" max="2100" inputmode="numeric" placeholder="Contoh: 2020"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jumlah Komposter <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="kompos_jumlah_komposter" min="0" max="9999" inputmode="numeric" placeholder="Contoh: 5"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kelompok / Komunitas Pengelola <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" name="kompos_kelompok" maxlength="200" placeholder="Contoh: Kelompok Tani Melati"
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- B. BAHAN ORGANIK -->
                    <div class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-amber-900">B. Bahan Organik</div>
                                <div class="text-xs text-amber-700">Jenis bahan yang dikompos</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-slate-700">Jenis Bahan <span class="text-slate-400 font-normal">(bisa lebih dari satu)</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                <?php
                                $bahan_list = ['Daun','Rumput','Ranting','Sisa makanan','Sampah dapur','Limbah pertanian','Limbah pasar','Kotoran ternak','Bahan organik lainnya'];
                                foreach ($bahan_list as $bh): ?>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="kompos_bahan_organik_arr[]" value="<?= $bh ?>" class="peer hidden">
                                        <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold hover:border-amber-300 transition flex items-center gap-2">
                                            <span><?= $bh ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- C. PENGELOLAAN DASAR -->
                    <div class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-100 p-5 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                                <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <div class="font-bold text-amber-900">C. Pengelolaan Dasar</div>
                                <div class="text-xs text-amber-700">Frekuensi & lama proses</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Frekuensi Pengadukan <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="kompos_frekuensi_pengadukan" maxlength="50" placeholder="Contoh: 2x seminggu"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Frekuensi Pengisian <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="text" name="kompos_frekuensi_pemasukan" maxlength="50" placeholder="Contoh: setiap hari"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lama Proses (hari) <span class="text-slate-400 font-normal">(opsional)</span></label>
                                <input type="number" name="kompos_lama_proses" min="0" max="9999" inputmode="numeric" placeholder="Contoh: 30"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition">
                            </div>
                        </div>
                    </div>

                </div>
                <!-- ============================================ -->
                <!-- FIELD KHUSUS KOMPOSTING — SELESAI             -->
                <!-- ============================================ -->
                
                      <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI — MULAI              -->
                <!-- ============================================ -->
                <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-100 p-5 shadow-sm mt-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-blue-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-blue-600">PIC & peran</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nama PIC Monev <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" name="pic_monev_nama" maxlength="150" placeholder="Nama lengkap penanggung jawab"
                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-slate-700">Peran / Jabatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <select name="pic_monev_peran" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                <option value="">-- Pilih --</option>
                                <option value="Koordinator">Koordinator</option>
                                <option value="Peneliti">Peneliti</option>
                                <option value="Perekayasa">Perekayasa</option>
                                <option value="Arboris">Arboris</option>
                                <option value="Analis">Analis</option>
                                <option value="Pengelola">Pengelola</option>
                                <option value="Pendamping">Pendamping</option>
                                <option value="Penggiat/Pemerhati">Penggiat/Pemerhati</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- ============================================ -->
                <!-- D. MONITORING & EVALUASI — SELESAI            -->
                <!-- ============================================ -->
                
                <!-- BAGIAN 2: KOORDINAT -->
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">2</div>
                    <div class="font-bold text-emerald-800">Koordinat GPS</div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Koordinat (opsional)
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="koordinat" id="input_koordinat" maxlength="100" 
                            placeholder="-6.917, 107.619"
                            class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                        <button type="button" onclick="ambilLokasi()" title="Ambil Lokasi" 
                            class="px-4 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition">
                            <i data-lucide="locate-fixed" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <p id="info_lokasi" class="text-xs text-slate-500 mt-2"></p>
                </div>
                
                <!-- BAGIAN 3: FOTO -->
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">3</div>
                    <div class="font-bold text-emerald-800">Foto Pertama (opsional)</div>
                </div>
                
                <div>
                    <input type="file" name="foto" id="input_foto_kamera" accept="image/*" capture="environment" onchange="pilihFoto(this)" style="display:none;">
                    <input type="file" id="input_foto_galeri" accept="image/jpeg,image/png,image/webp" onchange="pilihFoto(this)" style="display:none;">
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button type="button" onclick="document.getElementById('input_foto_kamera').click()"
                            class="py-3 px-4 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                            <i data-lucide="camera" class="w-3.5 h-3.5"></i> Kamera
                        </button>
                        <button type="button" onclick="document.getElementById('input_foto_galeri').click()"
                            class="py-3 px-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i> Galeri
                        </button>
                    </div>
                    
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center bg-slate-50">
                        <img id="preview_foto" src="" alt="" style="display:none; max-height:200px; margin:0 auto; border-radius:12px;">
                        <p id="info_foto" class="text-xs text-slate-500">Belum ada foto dipilih</p>
                    </div>
                </div>
                
                <!-- BAGIAN 4: CATATAN -->
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">4</div>
                    <div class="font-bold text-emerald-800">Catatan</div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Catatan Tambahan (opsional)</label>
                    <textarea name="catatan" rows="3" maxlength="2000" 
                        placeholder="Informasi tambahan tentang objek ini..."
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition"></textarea>
                </div>
                
                                <!-- VISIBILITY PICKER -->
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="eye" class="w-4 h-4 text-emerald-600"></i> Siapa yang Bisa Lihat Objek Ini?
                    </label>
                    <p class="text-xs text-slate-500 mb-3">Atur tingkat privasi objek ini.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="cursor-pointer group">
                            <input type="radio" name="visibility" value="publik" class="peer hidden" checked>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-emerald-600 peer-checked:bg-emerald-50 hover:border-emerald-300 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                        <i data-lucide="globe" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">🌍 Publik</div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">Tampil di beranda & arsip. Siapa saja bisa lihat.</p>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="visibility" value="teman" class="peer hidden">
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-amber-300 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">👥 Teman</div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">Hanya Anda & user yang saling follow.</p>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="visibility" value="privat" class="peer hidden">
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-slate-700 peer-checked:bg-slate-100 hover:border-slate-400 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">🔒 Privat</div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed">Hanya Anda yang bisa lihat.</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-700 to-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i> Simpan Objek
                </button>
            </form>
        </div>
    </main>
    <?php endif; ?>

<?php elseif ($halaman == 'objek'): ?>
    <main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6 flex items-start justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                    <i data-lucide="package" class="w-7 h-7 text-emerald-700"></i> Daftar Objek
                </h1>
                <p class="text-sm text-slate-500">Master data pohon, sungai, taman & komposting.</p>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=objek_daftar" class="px-5 py-3 bg-emerald-700 text-white font-bold rounded-full hover:bg-emerald-800 transition flex items-center gap-2 text-sm">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Daftar Objek Baru
                </a>
            <?php endif; ?>
        </div>
        
        <?php
        // Filter & Search
        $where = ["status = 'aktif'", "deleted_at IS NULL"]; $params = []; $types = '';
        
                // ============ FILTER ROLE ============
        // Admin/Super → semua objek
        // User login → objek sendiri + objek publik orang lain
        // Guest → hanya objek publik
        if (is_admin()) {
            // Tidak ada filter tambahan — lihat semua
        } elseif (isset($_SESSION['user_id'])) {
            $where[] = "(user_id = ? OR visibility = 'publik')";
            $params[] = (int)$_SESSION['user_id'];
            $types .= 'i';
        } else {
            $where[] = "visibility = 'publik'";
        }
        // ============ END FILTER ROLE ============
        
        if (!empty($_GET['f_jenis']) && in_array($_GET['f_jenis'], ['sungai','pohon','taman','komposting'])) {
            $where[] = "jenis_objek = ?"; $params[] = $_GET['f_jenis']; $types .= 's';
        }
        if (!empty($_GET['f_cari'])) {
            $where[] = "(nama_objek LIKE ? OR kode_objek LIKE ? OR lokasi LIKE ?)";
            $cari = '%' . $_GET['f_cari'] . '%';
            $params[] = $cari; $params[] = $cari; $params[] = $cari; $types .= 'sss';
        }
        
        $sql = "SELECT * FROM objek WHERE " . implode(' AND ', $where) . " ORDER BY id DESC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res_objek = mysqli_stmt_get_result($stmt);
        $data_objek = [];
        while ($row = mysqli_fetch_assoc($res_objek)) $data_objek[] = $row;
        ?>
        
        <!-- Filter -->
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="hidden" name="page" value="objek">
            <select name="f_jenis" class="px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">-- Semua Jenis --</option>
                <option value="sungai" <?= ($_GET['f_jenis']??'')=='sungai'?'selected':'' ?>>🌊 Sungai</option>
                <option value="pohon" <?= ($_GET['f_jenis']??'')=='pohon'?'selected':'' ?>>🌳 Pohon</option>
                <option value="taman" <?= ($_GET['f_jenis']??'')=='taman'?'selected':'' ?>>🌿 Taman</option>
                <option value="komposting" <?= ($_GET['f_jenis']??'')=='komposting'?'selected':'' ?>>♻️ Komposting</option>
            </select>
            <input type="text" name="f_cari" value="<?= e($_GET['f_cari'] ?? '') ?>" placeholder="Cari kode/nama/lokasi..." class="px-3 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none md:col-span-2">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-700 text-white font-bold rounded-xl text-sm">Filter</button>
                <a href="index.php?page=objek" class="px-4 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl text-sm">Reset</a>
            </div>
        </form>
        
        <?php if (empty($data_objek)): ?>
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-16 text-center">
                <i data-lucide="package" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-500 font-medium mb-4">Belum ada objek terdaftar</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=objek_daftar" class="inline-flex px-5 py-3 bg-emerald-700 text-white font-bold rounded-full hover:bg-emerald-800 transition items-center gap-2 text-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Daftar Objek Pertama
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($data_objek as $o): 
                    $jenis_info = [
                        'sungai'     => ['badge' => 'from-blue-500 to-blue-600',       'ikon' => 'waves'],
                        'pohon'      => ['badge' => 'from-emerald-600 to-emerald-700', 'ikon' => 'tree-pine'],
                        'taman'      => ['badge' => 'from-lime-500 to-lime-600',       'ikon' => 'flower-2'],
                        'komposting' => ['badge' => 'from-amber-600 to-amber-700',     'ikon' => 'recycle'],
                    ];
                    $info = $jenis_info[$o['jenis_objek']] ?? $jenis_info['pohon'];
                ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover">
                        <div class="bg-gradient-to-r <?= $info['badge'] ?> text-white text-xs font-bold px-4 py-2 uppercase flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <i data-lucide="<?= $info['ikon'] ?>" class="w-3.5 h-3.5"></i>
                                <?= e($o['jenis_objek']) ?>
                            </span>
                            <span class="font-mono"><?= e($o['kode_objek']) ?></span>
                        </div>
                        
                        <?php if($o['foto_pertama'] || !empty($o['telegram_file_id'])): ?>
    <img src="<?= e(url_foto($o, 'foto_pertama', 'full')) ?>" alt="Foto" class="w-full h-40 object-cover" loading="lazy" onerror="this.style.display='none'">
<?php else: ?>
    <div class="w-full h-40 bg-emerald-50 flex items-center justify-center">
        <i data-lucide="<?= $info['ikon'] ?>" class="w-16 h-16 text-emerald-300"></i>
    </div>
<?php endif; ?>
                        
                        <div class="p-5">
                            <h3 class="font-bold text-lg mb-1"><?= e($o['nama_objek']) ?></h3>
                            <?php if($o['lokasi']): ?>
                                <p class="text-xs text-slate-500 mb-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    <?= e($o['lokasi']) ?>
                                </p>
                            <?php endif; ?>
                            <?php if($o['koordinat']): ?>
                                <p class="text-xs text-slate-400 mb-3 font-mono"><?= e($o['koordinat']) ?></p>
                            <?php endif; ?>
                            
                            <div class="flex gap-2 mt-3 pt-3 border-t">
    <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="flex-1 text-center py-2 bg-emerald-700 text-white font-bold rounded-lg text-xs hover:bg-emerald-800 transition">
        Lihat Profil
    </a>
    <?php if(isset($_SESSION['user_id']) && ($o['user_id'] == $_SESSION['user_id'] || is_admin())): ?>
        <a href="index.php?page=objek_edit&id=<?= $o['id'] ?>" class="text-center py-2 px-3 bg-blue-100 text-blue-700 font-bold rounded-lg text-xs hover:bg-blue-200 transition">
            Edit
        </a>
    <?php endif; ?>
</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<?php elseif ($halaman == 'arsip'): ?>
    <main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
    <i data-lucide="<?= is_admin() ? 'folder-open' : 'file-text' ?>" class="w-7 h-7 text-emerald-700"></i>
    <?= is_admin() ? 'Arsip Global' : 'Laporan Saya' ?>
</h1>
<p class="text-sm text-slate-500">
    <?= is_admin() ? 'Rekapitulasi aksi lingkungan & serapan emisi.' : 'Rekap aksi lingkungan yang Anda laporkan.' ?>
</p>
        </div>
        
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="index.php?export=xlsx" class="px-4 py-2.5 bg-emerald-600 text-white font-semibold rounded-full text-sm hover:bg-emerald-700 transition flex items-center gap-2">
    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> Excel
</a>
            <a href="index.php?export=csv" class="px-4 py-2.5 bg-emerald-700 text-white font-semibold rounded-full text-sm hover:bg-emerald-800 transition flex items-center gap-2">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
            </a>
            <button onclick="window.print()" class="hidden md:flex px-4 py-2.5 bg-rose-600 text-white font-semibold rounded-full text-sm hover:bg-rose-700 transition items-center gap-2">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
            </button>
            <button onclick="bagikan()" class="md:hidden px-4 py-2.5 bg-rose-600 text-white font-semibold rounded-full text-sm flex items-center gap-2">
                <i data-lucide="share-2" class="w-3.5 h-3.5"></i> Bagikan
            </button>
        </div>
        
        <?php
        $where = ["deleted_at IS NULL"]; $params = []; $types = '';
        // ============ FILTER ROLE ============
if (!is_admin()) {
    $where[] = "user_id = ?"; $params[] = (int)$_SESSION['user_id']; $types .= 'i';
}
        if (!empty($_GET['f_jenis']) && in_array($_GET['f_jenis'], ['sungai','pohon','taman','komposting'])) {
            $where[] = "jenis_objek = ?"; $params[] = $_GET['f_jenis']; $types .= 's';
        }
        if (!empty($_GET['f_status'])) { $where[] = "status_kondisi = ?"; $params[] = $_GET['f_status']; $types .= 's'; }
        if (!empty($_GET['f_cari'])) {
            $where[] = "(nama_objek LIKE ? OR nama_kontributor LIKE ?)";
            $cari = '%' . $_GET['f_cari'] . '%';
            $params[] = $cari; $params[] = $cari; $types .= 'ss';
        }
        $sql = "SELECT * FROM laporan_aksi WHERE " . implode(' AND ', $where) . " ORDER BY tanggal_buat DESC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res_arsip = mysqli_stmt_get_result($stmt);
        $data_arsip = [];
        while ($row = mysqli_fetch_assoc($res_arsip)) $data_arsip[] = $row;
        ?>
        
        <details class="lg:hidden bg-white rounded-2xl shadow-sm border border-slate-100 mb-4 overflow-hidden">
            <summary class="px-5 py-4 font-bold cursor-pointer select-none flex items-center gap-2">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-emerald-700"></i> Filter & Pencarian
            </summary>
            <form method="GET" class="px-5 pb-5 grid grid-cols-1 gap-3 border-t pt-4">
                <input type="hidden" name="page" value="arsip">
                <select name="f_jenis" class="px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Semua Jenis --</option>
                    <option value="sungai" <?= ($_GET['f_jenis']??'')=='sungai'?'selected':'' ?>>🌊 Sungai</option>
                    <option value="pohon" <?= ($_GET['f_jenis']??'')=='pohon'?'selected':'' ?>>🌳 Pohon</option>
                    <option value="taman" <?= ($_GET['f_jenis']??'')=='taman'?'selected':'' ?>>🌷 Taman</option>
                    <option value="komposting" <?= ($_GET['f_jenis']??'')=='komposting'?'selected':'' ?>>♻️ Komposting</option>
                </select>
                <input type="text" name="f_cari" value="<?= e($_GET['f_cari'] ?? '') ?>" placeholder="Cari..." class="px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <select name="f_status" class="px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="Aman / Bersih / Sehat">✅ Aman</option>
                    <option value="Waspada / Perlu Perawatan">⚠️ Waspada</option>
                    <option value="Tercemar / Rusak">❌ Tercemar</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-3 bg-emerald-700 text-white font-bold rounded-xl">Terapkan</button>
                    <a href="index.php?page=arsip" class="px-4 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl">Reset</a>
                </div>
            </form>
        </details>
        
        <form method="GET" class="hidden lg:grid bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 grid-cols-4 gap-3">
            <input type="hidden" name="page" value="arsip">
            <select name="f_jenis" class="px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">-- Semua Jenis --</option>
                <option value="sungai" <?= ($_GET['f_jenis']??'')=='sungai'?'selected':'' ?>>🌊 Sungai</option>
                <option value="pohon" <?= ($_GET['f_jenis']??'')=='pohon'?'selected':'' ?>>🌳 Pohon</option>
                <option value="taman" <?= ($_GET['f_jenis']??'')=='taman'?'selected':'' ?>>🌷 Taman</option>
                <option value="komposting" <?= ($_GET['f_jenis']??'')=='komposting'?'selected':'' ?>>♻️ Komposting</option>
            </select>
            <input type="text" name="f_cari" value="<?= e($_GET['f_cari'] ?? '') ?>" placeholder="Cari..." class="px-3 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            <select name="f_status" class="px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">-- Semua Status --</option>
                <option value="Aman / Bersih / Sehat">Aman</option>
                <option value="Waspada / Perlu Perawatan">Waspada</option>
                <option value="Tercemar / Rusak">Tercemar</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-700 text-white font-bold rounded-xl text-sm">Filter</button>
                <a href="index.php?page=arsip" class="px-4 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl text-sm">Reset</a>
            </div>
        </form>
        
        <?php if (empty($data_arsip)): ?>
    <?php if (is_admin()): ?>
        <!-- EMPTY STATE UNTUK ADMIN -->
        <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-16 text-center">
            <i data-lucide="folder-open" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
            <p class="text-slate-500 font-medium">Belum ada laporan dari user manapun</p>
            <p class="text-xs text-slate-400 mt-1">Laporan user akan muncul di sini.</p>
        </div>
    <?php else: ?>
        <!-- EMPTY STATE UNTUK USER BIASA -->
        <div class="bg-gradient-to-br from-emerald-50 to-white rounded-3xl border-2 border-dashed border-emerald-200 p-10 md:p-16 text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="sprout" class="w-10 h-10 text-emerald-600"></i>
            </div>
            <h3 class="text-xl md:text-2xl font-extrabold text-slate-900 mb-2">
                Anda Belum Punya Laporan
            </h3>
            <p class="text-slate-600 text-sm md:text-base mb-6 max-w-md mx-auto leading-relaxed">
                Mulai dokumentasikan aksi lingkungan Anda. Setiap pohon yang dipantau, setiap sungai yang dipatroli — <b>adalah langkah nyata</b> untuk Indonesia yang lebih hijau. 🌱
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <a href="index.php?page=objek" 
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-700 text-white font-bold rounded-full shadow-lg hover:bg-emerald-800 transition">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Buat Laporan Pertama
                </a>
                <a href="index.php?page=objek_daftar" 
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white border-2 border-emerald-700 text-emerald-700 font-bold rounded-full hover:bg-emerald-50 transition">
                    <i data-lucide="package-plus" class="w-3.5 h-3.5"></i> Daftar Objek Baru
                </a>
            </div>
            <p class="text-xs text-emerald-600 font-semibold mt-6 flex items-center justify-center gap-1.5">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                Tip: Kalau baru mulai, daftarkan objek dulu — baru buat laporan.
            </p>
        </div>
    <?php endif; ?>
<?php else: ?>
        
        <!-- CARD VIEW (Mobile) -->
        <div class="lg:hidden space-y-3">
            <?php foreach ($data_arsip as $r): 
                $jenis_info = [
    'sungai'     => ['badge' => 'from-blue-500 to-blue-600',       'ikon' => 'waves'],
    'pohon'      => ['badge' => 'from-emerald-600 to-emerald-700', 'ikon' => 'tree-pine'],
    'taman'      => ['badge' => 'from-lime-500 to-lime-600',       'ikon' => 'flower-2'],
    'komposting' => ['badge' => 'from-amber-600 to-amber-700',     'ikon' => 'recycle'],
];
$info = $jenis_info[$r['jenis_objek']] ?? $jenis_info['pohon'];
$badge = $info['badge'];
$ikon = $info['ikon'];
            ?>
            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
                <div class="flex">
                    <div class="w-24 h-24 flex-shrink-0 bg-slate-100">
                        <?php if($r['foto']): ?>
                            <a href="<?= e(url_foto($r, 'foto', 'full')) ?>" target="_blank">
                                <img src="<?= e(url_foto($r, 'foto', 'thumb')) ?>" class="w-24 h-24 object-cover">
                            </a>
                        <?php else: ?>
                            <div class="w-24 h-24 flex items-center justify-center bg-emerald-50">
                                <i data-lucide="<?= $ikon ?>" class="w-8 h-8 text-emerald-600"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 p-3 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <span class="bg-gradient-to-r <?= $badge ?> text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= e($r['jenis_objek']) ?></span>
                            <span class="text-[10px] text-slate-400 flex-shrink-0"><?= date('d/m/y', strtotime($r['tanggal_buat'])) ?></span>
                        </div>
                        <h4 class="font-bold text-sm leading-tight mb-0.5 truncate"><?= e($r['nama_objek']) ?></h4>
                        <p class="text-[11px] text-slate-500 truncate flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> <?= e($r['koordinat']) ?></p>
                                                    <?php 
                            $status_mobile = '-';
                            $status_mobile_warna = 'text-emerald-600';
                            
                            if ($r['jenis_objek'] === 'pohon') {
                                $status_mobile = $r['penilaian_kesehatan'] ?: '-';
                                if ($status_mobile === 'Rawan') $status_mobile_warna = 'text-amber-600';
                                elseif ($status_mobile === 'Kritis' || $status_mobile === 'Mati') $status_mobile_warna = 'text-rose-600';
                            } elseif ($r['jenis_objek'] === 'sungai') {
                                $status_mobile = $r['kondisi_debit'] ?: '-';
                                if ($status_mobile === 'Rendah' || $status_mobile === 'Tinggi') $status_mobile_warna = 'text-amber-600';
                                elseif ($status_mobile === 'Kering' || $status_mobile === 'Sangat tinggi') $status_mobile_warna = 'text-rose-600';
                            } elseif ($r['jenis_objek'] === 'taman') {
                                $status_mobile = $r['kondisi_vegetasi'] ?: '-';
                                if ($status_mobile === 'Cukup') $status_mobile_warna = 'text-amber-600';
                                elseif ($status_mobile === 'Rusak' || $status_mobile === 'Sangat Rusak') $status_mobile_warna = 'text-rose-600';
                            } elseif ($r['jenis_objek'] === 'komposting') {
                                $status_mobile = $r['kompos_status'] ?: '-';
                                if ($status_mobile === 'Sedang berlangsung') $status_mobile_warna = 'text-amber-600';
                                elseif ($status_mobile === 'Gagal') $status_mobile_warna = 'text-rose-600';
                            }
                            ?>
                            <p class="text-[11px] <?= $status_mobile_warna ?> font-semibold mt-0.5 truncate">
                                <?= e($status_mobile) ?>
                            </p>
                        <?php if($r['jenis_objek']=='pohon'): ?>
    <p class="text-[10px] bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-bold mt-1 inline-flex items-center gap-1">
        <i data-lucide="leaf" class="w-3 h-3"></i> <?= number_format($r['serapan_karbon'],2) ?> kg CO₂
    </p>
<?php elseif($r['jenis_objek']=='komposting' && $r['volume_sampah'] > 0): ?>
    <p class="text-[10px] bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-bold mt-1 inline-flex items-center gap-1">
        <i data-lucide="recycle" class="w-3 h-3"></i> <?= number_format($r['volume_sampah']) ?> kg/bulan
    </p>
<?php endif; ?>
                    </div>
                </div>
                <div class="border-t px-3 py-2 flex items-center justify-between gap-2 bg-slate-50">
                    <span class="text-[11px] text-slate-500 truncate flex items-center gap-1"><i data-lucide="user" class="w-3 h-3"></i> <?= e($r['nama_kontributor']) ?></span>
                    <div class="flex gap-2">
                        <?php if(isset($_SESSION['user_id']) && ($r['user_id']==$_SESSION['user_id'] || is_admin())): ?>
    <a href="index.php?page=edit&id=<?= $r['id'] ?>" class="text-xs px-3 py-1.5 bg-blue-100 text-blue-700 font-bold rounded-lg">Edit</a>
    <a href="javascript:void(0)" onclick="konfirmasiHapus(<?= $r['id'] ?>)" class="text-xs px-3 py-1.5 bg-rose-100 text-rose-700 font-bold rounded-lg">Hapus</a>
<?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- TABLE VIEW (Desktop) -->
        <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-emerald-50 to-emerald-100 text-xs uppercase font-bold text-emerald-800">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Foto</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Objek & Koordinat</th>
                        <th class="px-4 py-3">Status & Karbon</th>
                        <th class="px-4 py-3">Kontributor</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php foreach ($data_arsip as $r): ?>
                        <tr class="hover:bg-emerald-50/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                                <?= date('d/m/Y', strtotime($r['tanggal_buat'])) ?>
                            </td>
                            <td class="px-4 py-3">
    <?php if($r['foto'] || !empty($r['telegram_file_id'])): ?>
        <a href="<?= e(url_foto($r, 'foto', 'full')) ?>" target="_blank">
            <img src="<?= e(url_foto($r, 'foto', 'thumb')) ?>" class="w-24 h-24 object-cover">
        </a>
    <?php else: ?><span class="text-slate-300 text-xs">-</span><?php endif; ?>
</td>
<td class="px-4 py-3 uppercase font-bold text-xs">
                                <?= e($r['jenis_objek']) ?>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                <?= e($r['nama_objek']) ?>
                                <span class="block text-xs font-normal text-slate-400"><?= e($r['koordinat']) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?php 
                                // Tentukan status & warna per jenis objek
                                $status_teks = '-';
                                $status_warna = 'text-emerald-600';
                                
                                if ($r['jenis_objek'] === 'pohon') {
                                    $status_teks = $r['penilaian_kesehatan'] ?: '-';
                                    if ($status_teks === 'Rawan') $status_warna = 'text-amber-600';
                                    elseif ($status_teks === 'Kritis' || $status_teks === 'Mati') $status_warna = 'text-rose-600';
                                } elseif ($r['jenis_objek'] === 'sungai') {
                                    $status_teks = $r['kondisi_debit'] ?: '-';
                                    if ($status_teks === 'Rendah' || $status_teks === 'Tinggi') $status_warna = 'text-amber-600';
                                    elseif ($status_teks === 'Kering' || $status_teks === 'Sangat tinggi') $status_warna = 'text-rose-600';
                                } elseif ($r['jenis_objek'] === 'taman') {
                                    $status_teks = $r['kondisi_vegetasi'] ?: '-';
                                    if ($status_teks === 'Cukup') $status_warna = 'text-amber-600';
                                    elseif ($status_teks === 'Rusak' || $status_teks === 'Sangat Rusak') $status_warna = 'text-rose-600';
                                } elseif ($r['jenis_objek'] === 'komposting') {
                                    $status_teks = $r['kompos_status'] ?: '-';
                                    if ($status_teks === 'Sedang berlangsung') $status_warna = 'text-amber-600';
                                    elseif ($status_teks === 'Gagal') $status_warna = 'text-rose-600';
                                }
                                ?>
                                <span class="font-semibold text-xs <?= $status_warna ?>">
                                    <?= e($status_teks) ?>
                                </span>
                                <?php if($r['serapan_karbon'] > 0): ?>
                                    <span class="block text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-bold mt-1 w-fit">
                                        <?= ($r['jenis_objek'] === 'pohon') ? '🌿' : '♻️' ?> <?= number_format($r['serapan_karbon'],2) ?> kg CO₂
                                    </span>
                                <?php endif; ?>
                            </td>
                                                    <td class="px-4 py-3 text-xs"><?= e($r['nama_kontributor']) ?></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 text-xs">
                                    <?php if(isset($_SESSION['user_id']) && ($r['user_id']==$_SESSION['user_id'] || is_admin())): ?>
                                        <a href="index.php?page=edit&id=<?= $r['id'] ?>" class="text-blue-600 font-bold hover:underline">Edit</a>
                                        <a href="javascript:void(0)" onclick="konfirmasiHapus(<?= $r['id'] ?>)" class="text-rose-600 font-bold hover:underline">Hapus</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>

    <?php elseif ($halaman == 'edit'): 
    if (!isset($_SESSION['user_id'])) { redirect('index.php?page=login'); }
    
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { notif('gagal', 'ID tidak valid'); redirect('index.php?page=arsip'); }
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM laporan_aksi WHERE id=? AND deleted_at IS NULL");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    
    if (!$data) { notif('gagal', 'Data tidak ditemukan'); redirect('index.php?page=arsip'); }
    if ($data['user_id'] != $_SESSION['user_id'] && !is_admin()) { notif('gagal', 'Tidak berhak.'); redirect('index.php?page=arsip'); }
    
    // Ambil master objek
    $lapor_objek_id = (int)($data['objek_id'] ?? 0);
    $lapor_objek = null;
    if ($lapor_objek_id > 0) {
        $stmt_lo = mysqli_prepare($conn, "SELECT * FROM objek WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        mysqli_stmt_bind_param($stmt_lo, 'i', $lapor_objek_id);
        mysqli_stmt_execute($stmt_lo);
        $lapor_objek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_lo));
        mysqli_stmt_close($stmt_lo);
    }
    
    // Helper function untuk value
    if (!function_exists('ev')) {
        function ev($arr, $key, $default = '') { return e($arr[$key] ?? $default); }
    }
    
    $jenis_aktif = $data['jenis_objek'];
    $vis_aktif = $data['visibility'] ?? 'publik';
    
    // Decode JSON checkbox kompos
    $pemanfaatan_terpilih = !empty($data['kompos_pemanfaatan']) ? json_decode($data['kompos_pemanfaatan'], true) : [];
    if (!is_array($pemanfaatan_terpilih)) $pemanfaatan_terpilih = [];
    $faktor_terpilih = !empty($data['kompos_faktor_keberlanjutan']) ? json_decode($data['kompos_faktor_keberlanjutan'], true) : [];
    if (!is_array($faktor_terpilih)) $faktor_terpilih = [];
?>
    <main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <a href="index.php?page=arsip" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Arsip
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="pencil" class="w-7 h-7 text-emerald-700"></i> Edit Laporan #<?= (int)$data['id'] ?>
            </h1>
            <p class="text-sm text-slate-500">Perbarui data laporan. Semua field bisa diubah.</p>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8">
            <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi_update" value="1">
                <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
                <input type="hidden" name="jenis_objek" value="<?= e($jenis_aktif) ?>">
                
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">1</div>
                    <div class="font-bold text-emerald-800">Jenis & Objek</div>
                </div>
                <input type="hidden" name="objek_id" value="<?= (int)$lapor_objek_id ?>">
                <?php
                $jenis_icons = [
                    'pohon'      => ['ikon' => 'tree-pine', 'label' => 'Pohon',      'warna' => 'emerald'],
                    'sungai'     => ['ikon' => 'waves',     'label' => 'Sungai',     'warna' => 'blue'],
                    'taman'      => ['ikon' => 'flower-2',  'label' => 'Taman',      'warna' => 'lime'],
                    'komposting' => ['ikon' => 'recycle',   'label' => 'Komposting', 'warna' => 'amber'],
                ];
                $lj = $jenis_icons[$jenis_aktif] ?? $jenis_icons['pohon'];
                ?>
                <div>
                    <label class="block text-sm font-semibold mb-2">Jenis Objek <span class="text-slate-400 font-normal">(terkunci dari data objek)</span></label>
                    <div class="bg-slate-50 border-2 border-<?= $lj['warna'] ?>-200 rounded-xl p-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-<?= $lj['warna'] ?>-100 text-<?= $lj['warna'] ?>-700 flex items-center justify-center">
                            <i data-lucide="<?= $lj['ikon'] ?>" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-slate-500 font-semibold uppercase">Jenis Objek</div>
                            <div class="font-bold text-slate-800"><?= e($lj['label']) ?></div>
                        </div>
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Nama Objek</label>
                    <input type="text" name="nama_objek" value="<?= ev($data, 'nama_objek') ?>" required maxlength="150" 
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4"></i> Koordinat GPS
                    </label>
                    <input type="text" name="koordinat" value="<?= ev($data, 'koordinat') ?>" required maxlength="100"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                
                <?php 
                $tgl_tanam_obj = $lapor_objek['tanggal_tanam'] ?? '';
                $tgl_mon_ref = $data['tanggal_monitoring'] ?: date('Y-m-d');
                $umur_sekarang = ($tgl_tanam_obj && $tgl_tanam_obj !== '0000-00-00') ? hitung_umur($tgl_tanam_obj, $tgl_mon_ref) : null;
                ?>
                <div id="box_umur_pohon" style="<?= $jenis_aktif === 'pohon' ? '' : 'display:none;' ?>" class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-4 rounded-xl border-2 border-emerald-200">
                    <label class="block text-sm font-semibold text-emerald-900 mb-1 flex items-center gap-2">
                        <i data-lucide="calendar-clock" class="w-4 h-4"></i> Umur Pohon (Otomatis)
                    </label>
                    <p class="text-xs text-emerald-700 mb-3">Dihitung otomatis dari tanggal tanam objek.</p>
                    <?php if($umur_sekarang): ?>
                        <div class="bg-white border-2 border-emerald-300 rounded-xl p-4">
                            <div class="text-lg md:text-xl font-extrabold text-emerald-800 leading-tight">
                                <?= e($umur_sekarang['teks_full']) ?>
                            </div>
                            <div class="text-[11px] text-emerald-600 mt-2 flex items-center gap-1.5 flex-wrap">
                                <i data-lucide="info" class="w-3 h-3"></i>
                                <span>Tanam: <b><?= date('d F Y', strtotime($tgl_tanam_obj)) ?></b></span>
                                <span class="text-emerald-400">·</span>
                                <span>Total: <b><?= number_format($umur_sekarang['total_hari']) ?> hari</b></span>
                            </div>
                        </div>
                        <input type="hidden" name="umur_bulan" value="<?= (int)($umur_sekarang['total_hari'] / 30) ?>">
                    <?php else: ?>
                        <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-3">
                            <p class="text-xs text-amber-800 font-semibold">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 inline"></i>
                                Tanggal tanam belum diisi di master objek.
                            </p>
                        </div>
                        <input type="hidden" name="umur_bulan" value="0">
                    <?php endif; ?>
                </div>
                
                <!-- ========== MONITORING POHON ========== -->
                <div id="box_monitoring_pohon" style="<?= $jenis_aktif === 'pohon' ? '' : 'display:none;' ?>" class="bg-gradient-to-br from-emerald-50 via-white to-emerald-50/40 rounded-2xl border-2 border-emerald-200 p-5 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-emerald-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-emerald-600">Kondisi & pengukuran pohon</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring" value="<?= ev($data, 'tanggal_monitoring') ?>"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-2">Pengukuran</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Diameter Batang (cm)</label>
                                <input type="number" name="diameter_batang" step="0.1" min="0" value="<?= ev($data, 'diameter_batang') ?>" 
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tinggi Pohon (m)</label>
                                <input type="number" name="tinggi_pohon" step="0.1" min="0" value="<?= ev($data, 'tinggi_pohon') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lebar Kanopi (m)</label>
                                <input type="number" name="lebar_kanopi" step="0.1" min="0" value="<?= ev($data, 'lebar_kanopi') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                                                <!-- LIVE CALCULATOR KARBON (EDIT) -->
                            <div id="live-calc-karbon" class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl border-2 border-emerald-300 p-4 md:col-span-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <i data-lucide="calculator" class="w-4 h-4 text-emerald-700"></i>
                                    <div class="font-bold text-emerald-900 text-sm">Kalkulator Karbon (Chave 2005)</div>
                                </div>
                                <p class="text-xs text-emerald-700 mb-3">Estimasi otomatis dari Diameter Batang & Tinggi Pohon.</p>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white rounded-xl p-3 border border-emerald-200 text-center">
                                        <div class="text-[10px] text-emerald-600 font-semibold uppercase">AGB</div>
                                        <div id="calc-agb" class="text-base md:text-lg font-extrabold text-emerald-800 leading-tight">0.00</div>
                                        <div class="text-[10px] text-emerald-500">kg biomassa</div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 border border-emerald-200 text-center">
                                        <div class="text-[10px] text-emerald-600 font-semibold uppercase">Karbon (C)</div>
                                        <div id="calc-karbon" class="text-base md:text-lg font-extrabold text-emerald-800 leading-tight">0.00</div>
                                        <div class="text-[10px] text-emerald-500">kg C</div>
                                    </div>
                                    <div class="bg-white rounded-xl p-3 border-2 border-amber-300 text-center shadow-sm">
                                        <div class="text-[10px] text-amber-700 font-bold uppercase">Serapan CO₂</div>
                                        <div id="calc-co2" class="text-lg md:text-2xl font-extrabold text-amber-700 leading-tight">0.00</div>
                                        <div class="text-[10px] text-amber-600">kg CO₂-eq</div>
                                    </div>
                                </div>
                            </div>
                            <!-- END LIVE CALCULATOR KARBON (EDIT) -->
                            
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lingkungan Tumbuh</label>
                        <select name="lingkungan_tumbuh" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih --</option>
                            <?php foreach(['Terbuka (full sun)','Setengah ternaung','Ternaung','Dekat bangunan/jalan','Dekat sumber air','Lahan basah','Lahan kering','Lainnya'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($data['lingkungan_tumbuh'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-2">Kondisi (5 Tingkat)</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php
                            $level_5 = ['Sangat Baik','Baik','Cukup','Buruk','Sangat Buruk'];
                            $kondisi_fields = [
                                'kondisi_pertumbuhan'=>'Kondisi Pertumbuhan','kondisi_batang'=>'Kondisi Batang',
                                'kondisi_kanopi'=>'Kondisi Kanopi','kondisi_akar'=>'Kondisi Akar',
                                'kondisi_cabang'=>'Kondisi Cabang','kondisi_daun'=>'Kondisi Daun',
                                'kondisi_buah'=>'Kondisi Buah','kondisi_bunga'=>'Kondisi Bunga',
                                'kondisi_lainnya'=>'Kondisi Lainnya',
                            ];
                            foreach ($kondisi_fields as $name => $label): ?>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700"><?= $label ?></label>
                                    <select name="<?= $name ?>" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach ($level_5 as $lvl): ?>
                                            <option value="<?= $lvl ?>" <?= ($data[$name] ?? '')===$lvl?'selected':'' ?>><?= $lvl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Penilaian Kesehatan</label>
                        <select name="penilaian_kesehatan" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih --</option>
                            <?php foreach(['Sehat','Rawan','Kritis','Mati'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($data['penilaian_kesehatan'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="3" maxlength="1000" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= ev($data, 'risiko') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="3" maxlength="500" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= ev($data, 'rekomendasi') ?></textarea>
                    </div>
                </div>
                
                <!-- ========== MONITORING SUNGAI ========== -->
                <div id="box_monitoring_sungai" style="<?= $jenis_aktif === 'sungai' ? '' : 'display:none;' ?>" class="bg-gradient-to-br from-blue-50 via-white to-blue-50/40 rounded-2xl border-2 border-blue-200 p-5 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-blue-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-blue-600">Kondisi & pengukuran sungai</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring" value="<?= ev($data, 'tanggal_monitoring') ?>"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">Data Monitoring</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Debit Normal (m³/s)</label>
                                <input type="number" name="debit_air_normal" step="0.01" min="0" value="<?= ev($data, 'debit_air_normal') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Debit Pasang (m³/s)</label>
                                <input type="number" name="debit_air_pasang" step="0.01" min="0" value="<?= ev($data, 'debit_air_pasang') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Debit Surut (m³/s)</label>
                                <input type="number" name="debit_air_surut" step="0.01" min="0" value="<?= ev($data, 'debit_air_surut') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tinggi Muka Air (m)</label>
                                <input type="number" name="tinggi_muka_air" step="0.01" min="0" value="<?= ev($data, 'tinggi_muka_air') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Lebar Sungai (m)</label>
                                <input type="number" name="lebar_sungai_monitor" step="0.01" min="0" value="<?= ev($data, 'lebar_sungai_monitor') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Kedalaman Air (m)</label>
                                <input type="number" name="kedalaman_air" step="0.01" min="0" value="<?= ev($data, 'kedalaman_air') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">Indikator Kondisi</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php
                            $sungai_selects = [
                                'kondisi_debit'=>['Kondisi Debit',['Normal','Rendah','Tinggi','Sangat tinggi','Kering']],
                                'kondisi_air_visual'=>['Kondisi Air (Visual)',['Jernih','Agak keruh','Keruh','Sangat keruh']],
                                'warna_air'=>['Warna Air',['Normal','Kecokelatan','Kehitaman','Kehijauan','Lainnya']],
                                'bau_air'=>['Bau Air',['Tidak berbau','Sedikit berbau','Berbau','Sangat menyengat']],
                                'sampah_sungai'=>['Sampah',['Tidak ada','Sedikit','Sedang','Banyak','Sangat banyak']],
                                'vegetasi_sempadan'=>['Vegetasi Sempadan',['Baik','Cukup','Rusak','Sangat rusak','Tidak ada']],
                                'erosi_sungai'=>['Erosi / Longsor',['Tidak ada','Ringan','Sedang','Berat']]
                            ];
                            foreach ($sungai_selects as $name=>$arr): ?>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700"><?= $arr[0] ?></label>
                                    <select name="<?= $name ?>" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach($arr[1] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($data[$name] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">Kualitas Air <span class="text-slate-400 font-normal lowercase">(opsional)</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">pH</label>
                                <input type="number" name="kualitas_ph" step="0.1" min="0" max="14" value="<?= ev($data, 'kualitas_ph') ?>"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">Suhu (°C)</label>
                                <input type="number" name="kualitas_suhu" step="0.1" min="0" max="100" value="<?= ev($data, 'kualitas_suhu') ?>"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">TDS (mg/L)</label>
                                <input type="number" name="kualitas_tds" step="1" min="0" value="<?= ev($data, 'kualitas_tds') ?>"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">DO (mg/L)</label>
                                <input type="number" name="kualitas_do" step="0.1" min="0" max="30" value="<?= ev($data, 'kualitas_do') ?>"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-slate-600">Kekeruhan (NTU)</label>
                                <input type="number" name="kualitas_kekeruhan" step="0.1" min="0" value="<?= ev($data, 'kualitas_kekeruhan') ?>"
                                    class="w-full px-3 py-2 bg-white border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="2" maxlength="1000" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"><?= ev($data, 'risiko') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" maxlength="500" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"><?= ev($data, 'rekomendasi') ?></textarea>
                    </div>
                </div>
                
                <!-- ========== MONITORING TAMAN ========== -->
                <div id="box_monitoring_taman" style="<?= $jenis_aktif === 'taman' ? '' : 'display:none;' ?>" class="bg-gradient-to-br from-lime-50 via-white to-lime-50/40 rounded-2xl border-2 border-lime-200 p-5 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-600 to-lime-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-lime-900">D. Monitoring & Evaluasi</div>
                            <div class="text-xs text-lime-700">Kondisi taman</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring" value="<?= ev($data, 'tanggal_monitoring') ?>"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-lime-700 uppercase tracking-wide mb-2">Kondisi Taman</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php
                            $taman_selects = [
                                'kondisi_vegetasi'=>['Kondisi Vegetasi',['Sangat Baik','Baik','Cukup','Rusak','Sangat Rusak']],
                                'kondisi_fasilitas'=>['Kondisi Fasilitas',['Sangat Baik','Baik','Cukup','Rusak','Tidak Berfungsi']],
                                'kebersihan_taman'=>['Kebersihan',['Sangat Bersih','Bersih','Cukup','Kotor','Sangat Kotor']],
                                'kondisi_lingkungan'=>['Kondisi Lingkungan',['Teduh','Cukup Teduh','Kurang Teduh','Panas']],
                                'kondisi_drainase'=>['Drainase',['Baik','Cukup','Tersumbat','Rusak','Tidak Tersedia']]
                            ];
                            foreach ($taman_selects as $name=>$arr): ?>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700"><?= $arr[0] ?></label>
                                    <select name="<?= $name ?>" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach($arr[1] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($data[$name] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="2" maxlength="1000" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none"><?= ev($data, 'risiko') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" maxlength="500" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-lime-500 focus:outline-none"><?= ev($data, 'rekomendasi') ?></textarea>
                    </div>
                </div>
                
                <!-- ========== KOMPOSTING (simple) ========== -->
                <div id="box_komposting" style="<?= $jenis_aktif === 'komposting' ? '' : 'display:none;' ?>" class="bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-xl border-2 border-amber-200">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="recycle" class="w-5 h-5 text-amber-700"></i>
                        <div class="font-bold text-amber-900">Data Komposting</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-amber-900 mb-1">Volume Sampah Organik (kg/bulan)</label>
                            <input type="number" name="volume_sampah" value="<?= (int)($data['volume_sampah'] ?? 0) ?>" min="0" max="10000"
                                class="w-full px-4 py-3 border-2 border-amber-300 rounded-xl bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-amber-900 mb-1">Hasil Kompos (kg/bulan)</label>
                            <input type="number" name="hasil_kompos" value="<?= (int)($data['hasil_kompos'] ?? 0) ?>" min="0" max="10000"
                                class="w-full px-4 py-3 border-2 border-amber-300 rounded-xl bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        
                    </div>
                </div>
                
                                    <!-- LIVE CALCULATOR CO2 KOMPOS (EDIT) -->
                    <div id="live-calc-kompos" class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border-2 border-amber-300 p-4 mt-3">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="calculator" class="w-4 h-4 text-amber-700"></i>
                            <div class="font-bold text-amber-900 text-sm">Kalkulator CO₂ Dicegah (IPCC)</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded-xl p-3 border border-amber-200 text-center">
                                <div class="text-[10px] text-amber-600 font-semibold uppercase">Sampah Organik</div>
                                <div id="kompos-calc-input" class="text-base md:text-lg font-extrabold text-amber-800 leading-tight">0</div>
                                <div class="text-[10px] text-amber-500">kg/bulan</div>
                            </div>
                            <div class="bg-white rounded-xl p-3 border-2 border-amber-400 text-center shadow-sm">
                                <div class="text-[10px] text-amber-700 font-bold uppercase">CO₂ Dicegah</div>
                                <div id="kompos-calc-co2" class="text-lg md:text-2xl font-extrabold text-amber-700 leading-tight">0.00</div>
                                <div class="text-[10px] text-amber-600">kg CO₂-eq</div>
                            </div>
                        </div>
                        <p class="text-[10px] text-amber-600 mt-2 text-center italic">Rumus IPCC: 0.7 kg CO₂ per 1 kg sampah organik dikompos.</p>
                    </div>
                    <!-- END LIVE CALCULATOR CO2 KOMPOS (EDIT) -->
                    
                
                <!-- ========== KOMPOSTING LENGKAP ========== -->
                <div id="box_monitoring_komposting" style="<?= $jenis_aktif === 'komposting' ? '' : 'display:none;' ?>" class="bg-gradient-to-br from-amber-50 via-white to-amber-50/40 rounded-2xl border-2 border-amber-200 p-5 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-amber-800 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-amber-900">D-G. Monitoring Komposting</div>
                            <div class="text-xs text-amber-700">Kondisi, hasil, pemanfaatan, dampak</div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Tanggal Monitoring</label>
                        <input type="date" name="tanggal_monitoring" value="<?= ev($data, 'tanggal_monitoring') ?>"
                            class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">D. Monitoring Kondisi</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php
                            $kompos_selects1 = [
                                'kompos_kelembapan'=>['Kelembapan',['Baik','Cukup','Buruk']],
                                'kompos_aerasi'=>['Aerasi',['Baik','Cukup','Buruk']],
                                'kompos_bau'=>['Bau',['Tidak berbau','Sedikit','Berbau','Menyengat']],
                                'kompos_hama'=>['Hama / Lalat',['Tidak ada','Sedikit','Banyak']],
                                'kompos_kecepatan_penguraian'=>['Kecepatan Penguraian',['Cepat','Sedang','Lambat']],
                                'kompos_kondisi_wadah'=>['Kondisi Wadah',['Baik','Cukup','Buruk','Rusak']],
                                'kompos_kebersihan_area'=>['Kebersihan Area',['Baik','Cukup','Buruk']]
                            ];
                            foreach ($kompos_selects1 as $name=>$arr): ?>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700"><?= $arr[0] ?></label>
                                    <select name="<?= $name ?>" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach($arr[1] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($data[$name] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Suhu (°C)</label>
                                <input type="number" name="kompos_suhu" step="0.1" min="0" max="100" value="<?= ev($data, 'kompos_suhu') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">E. Hasil Komposting</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php
                            $kompos_selects2 = [
                                'kompos_status'=>['Status',['Belum mulai','Sedang berlangsung','Selesai','Gagal']],
                                'kompos_warna'=>['Warna Kompos',['Cokelat tua','Cokelat muda','Hitam','Kehijauan']],
                                'kompos_tekstur'=>['Tekstur',['Halus','Sedang','Kasar']],
                                'kompos_aroma'=>['Aroma',['Seperti tanah','Tidak berbau','Sedikit berbau','Menyengat']],
                                'kompos_kematangan'=>['Tingkat Kematangan',['Matang','Setengah matang','Belum matang']],
                                'kompos_kebersihan_anorganik'=>['Kebersihan Anorganik',['Bersih','Sedikit kotor','Kotor']]
                            ];
                            foreach ($kompos_selects2 as $name=>$arr): ?>
                                <div>
                                    <label class="block text-sm font-semibold mb-1.5 text-slate-700"><?= $arr[0] ?></label>
                                    <select name="<?= $name ?>" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach($arr[1] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($data[$name] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Volume Bahan Awal (kg)</label>
                                <input type="number" name="kompos_volume_awal" step="0.01" min="0" value="<?= ev($data, 'kompos_volume_awal') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Volume Kompos Akhir (kg)</label>
                                <input type="number" name="kompos_volume_akhir" step="0.01" min="0" value="<?= ev($data, 'kompos_volume_akhir') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Berat Kompos (kg)</label>
                                <input type="number" name="kompos_berat_kompos" step="0.01" min="0" value="<?= ev($data, 'kompos_berat_kompos') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">F. Pemanfaatan</div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm mb-3">
                            <?php 
                            $pemanfaatan_kompos = ['Taman','Pohon','Pertanian','Pembibitan','Tanaman rumah','Dijual','Dibagikan','Lainnya'];
                            foreach ($pemanfaatan_kompos as $pk): ?>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="kompos_pemanfaatan_arr[]" value="<?= $pk ?>" class="peer hidden" <?= in_array($pk, $pemanfaatan_terpilih)?'checked':'' ?>>
                                    <div class="px-3 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-slate-600 text-xs peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold hover:border-amber-300 transition text-center">
                                        <?= $pk ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jumlah Dimanfaatkan (kg)</label>
                                <input type="number" name="kompos_jumlah_dimanfaatkan" step="0.01" min="0" value="<?= ev($data, 'kompos_jumlah_dimanfaatkan') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Jumlah Dijual (kg)</label>
                                <input type="number" name="kompos_jumlah_dijual" step="0.01" min="0" value="<?= ev($data, 'kompos_jumlah_dijual') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Nilai Penjualan (Rp)</label>
                                <input type="number" name="kompos_nilai_penjualan" step="1" min="0" value="<?= ev($data, 'kompos_nilai_penjualan') ?>"
                                    class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-2">G. Dampak & Keberlanjutan</div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Dampak Lingkungan</label>
                                <textarea name="kompos_dampak" rows="2" maxlength="1000" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"><?= ev($data, 'kompos_dampak') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Status Operasional</label>
                                <select name="kompos_status_operasional" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach(['Aktif dan rutin','Aktif tetapi tidak rutin','Berjalan terbatas','Tidak beroperasi','Rusak'] as $opt): ?>
                                        <option value="<?= $opt ?>" <?= ($data['kompos_status_operasional'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5 text-slate-700">Faktor Keberlanjutan</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                    <?php
                                    $faktor_list = ['Pengelola aktif','Bahan baku tersedia','Biaya operasional','Peralatan memadai','Ada pasar kompos','Dukungan masyarakat','Dukungan pemerintah','Dukungan pihak lain'];
                                    foreach ($faktor_list as $fk): ?>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="kompos_faktor_arr[]" value="<?= $fk ?>" class="peer hidden" <?= in_array($fk, $faktor_terpilih)?'checked':'' ?>>
                                            <div class="px-2 py-2 bg-white border-2 border-slate-200 rounded-lg text-slate-600 peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:font-semibold transition text-center">
                                                <?= $fk ?>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Potensi Dampak & Risiko</label>
                        <textarea name="risiko" rows="2" maxlength="1000" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"><?= ev($data, 'risiko') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-700">Rekomendasi Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" maxlength="500" class="w-full px-4 py-2.5 bg-white border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none"><?= ev($data, 'rekomendasi') ?></textarea>
                    </div>
                </div>
                
                <!-- ========== PIC MONEV ========== -->
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">2</div>
                    <div class="font-bold text-emerald-800">Kondisi Objek</div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-blue-600"></i> PIC Monev
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama PIC</label>
                            <input type="text" name="pic_monev_nama" maxlength="150" value="<?= e($data['pic_monev_nama'] ?? $_SESSION['nama']) ?>"
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Peran / Jabatan</label>
                            <select name="pic_monev_peran" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <?php foreach(['Koordinator','Peneliti','Perekayasa','Arboris','Analis','Pengelola','Pendamping','Penggiat/Pemerhati'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= ($data['pic_monev_peran'] ?? '')===$opt?'selected':'' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- ========== FOTO ========== -->
                <div>
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4"></i> Foto Kondisi (opsional)
                    </label>
                    <?php if(!empty($data['foto']) || !empty($data['telegram_file_id'])): ?>
                        <div class="mb-3 p-3 bg-slate-50 rounded-xl">
                            <p class="text-xs text-slate-500 mb-2">Foto saat ini:</p>
                            <img src="<?= e(url_foto($data, 'foto', 'full')) ?>" alt="Foto" class="max-h-40 rounded-lg" onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>
                    <p class="text-xs text-slate-500 mb-2">Upload foto baru untuk menggantikan foto lama:</p>
                    <input type="file" name="foto" id="input_foto_kamera" accept="image/*" capture="environment" onchange="pilihFoto(this)" style="display:none;">
                    <input type="file" id="input_foto_galeri" accept="image/jpeg,image/png,image/webp" onchange="pilihFoto(this)" style="display:none;">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button type="button" onclick="document.getElementById('input_foto_kamera').click()"
                            class="py-3 px-4 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition flex items-center justify-center gap-2">
                            <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                        </button>
                        <button type="button" onclick="document.getElementById('input_foto_galeri').click()"
                            class="py-3 px-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i data-lucide="image" class="w-4 h-4"></i> Galeri
                        </button>
                    </div>
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center bg-slate-50">
                        <img id="preview_foto" src="" alt="" style="display:none; max-height:200px; margin:0 auto; border-radius:12px;">
                        <p id="info_foto" class="text-xs text-slate-500">Belum ada foto baru dipilih</p>
                    </div>
                </div>
                
                <!-- ========== KONTRIBUTOR ========== -->
                <div class="flex items-center gap-3 pb-3 border-b-2 border-emerald-100 pt-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm">3</div>
                    <div class="font-bold text-emerald-800">Kontributor</div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nama Kontributor</label>
                        <input type="text" name="nama_kontributor" required maxlength="150" value="<?= e($data['nama_kontributor'] ?? $_SESSION['nama']) ?>" 
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Peran</label>
                        <input type="text" name="peran_kontributor" required maxlength="100" value="<?= ev($data, 'peran_kontributor') ?>" 
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                
                <!-- ========== VISIBILITY ========== -->
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2 flex items-center gap-2">
                        <i data-lucide="eye" class="w-4 h-4 text-emerald-600"></i> Siapa yang Bisa Lihat Laporan Ini?
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="visibility" value="publik" class="peer hidden" <?= $vis_aktif==='publik'?'checked':'' ?>>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-emerald-600 peer-checked:bg-emerald-50 hover:border-emerald-300 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                        <i data-lucide="globe" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">🌍 Publik</div>
                                </div>
                                <p class="text-xs text-slate-500">Tampil di beranda & arsip</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="visibility" value="teman" class="peer hidden" <?= $vis_aktif==='teman'?'checked':'' ?>>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-amber-300 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">👥 Teman</div>
                                </div>
                                <p class="text-xs text-slate-500">Hanya Anda & teman</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="visibility" value="privat" class="peer hidden" <?= $vis_aktif==='privat'?'checked':'' ?>>
                            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-slate-700 peer-checked:bg-slate-100 hover:border-slate-400 transition">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </div>
                                    <div class="font-bold text-slate-800 text-sm">🔒 Privat</div>
                                </div>
                                <p class="text-xs text-slate-500">Hanya Anda</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- ========== CATATAN & STATUS ========== -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Status Kondisi</label>
                    <select name="status_kondisi" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <?php foreach(['Aman / Bersih / Sehat','Waspada / Perlu Perawatan','Tercemar / Rusak'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($data['status_kondisi'] ?? '')===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Catatan Lapangan</label>
                    <textarea name="catatan" rows="4" required maxlength="2000" placeholder="Deskripsikan kondisi objek, temuan, dan tindak lanjut..."
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"><?= ev($data, 'catatan') ?></textarea>
                </div>
                
                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-700 to-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </main>
    <script>
    function pilihFoto(input) {
        var info = document.getElementById('info_foto');
        var preview = document.getElementById('preview_foto');
        var file = input.files[0];
        if (!info || !preview) return;
        info.textContent = ''; info.className = 'text-xs mt-1 text-slate-500';
        preview.style.display = 'none';
        if (!file) return;
        if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
            info.textContent = '❌ Format harus JPG, PNG, atau WEBP';
            info.className = 'text-xs text-rose-600 mt-1 font-semibold';
            input.value = ''; return;
        }
        if (file.size > 8 * 1024 * 1024) {
            info.textContent = '❌ Ukuran melebihi 8 MB';
            info.className = 'text-xs text-rose-600 mt-1 font-semibold';
            input.value = ''; return;
        }
        info.textContent = '✔ ' + file.name + ' (' + (file.size/1024).toFixed(0) + ' KB)';
        info.className = 'text-xs text-emerald-600 mt-1 font-semibold';
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
        var inputKamera = document.getElementById('input_foto_kamera');
        if (inputKamera && input.id === 'input_foto_galeri') {
            var dt = new DataTransfer();
            dt.items.add(file);
            inputKamera.files = dt.files;
        }
    }
    </script>
    
    <?php elseif ($halaman == 'misi_saya'): 
        if (!isset($_SESSION['user_id'])) { 
            notif('gagal', 'Silakan login dulu.');
            redirect('index.php?page=login'); 
        }
        $uid = (int)$_SESSION['user_id'];
        
        $stmt = mysqli_prepare($conn, "SELECT nama_lengkap, organisasi, role, tanggal_daftar FROM users WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
        $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$user_data) { redirect('index.php?page=beranda'); }
        
        $levels = [
            ['nama'=>'Rookie','target'=>10,'emoji'=>'🥉'],
            ['nama'=>'Bronze','target'=>25,'emoji'=>'🥈'],
            ['nama'=>'Silver','target'=>50,'emoji'=>'🥇'],
            ['nama'=>'Gold','target'=>100,'emoji'=>'🏅'],
            ['nama'=>'Platinum','target'=>250,'emoji'=>'💎'],
            ['nama'=>'Diamond','target'=>500,'emoji'=>'🌟'],
            ['nama'=>'Legend','target'=>750,'emoji'=>'👑'],
            ['nama'=>'Master','target'=>1000,'emoji'=>'🔥'],
            ['nama'=>'Grandmaster','target'=>1500,'emoji'=>'💫'],
        ];
        
        $tentukan_level = function($jumlah, $levels) {
            $capaian = null;
            foreach ($levels as $lv) {
                if ($jumlah >= $lv['target']) $capaian = $lv;
                else break;
            }
            return $capaian;
        };
        
        $bulan_sekarang = date('Y-m');
        $stmt_b = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM objek WHERE user_id = ? AND deleted_at IS NULL AND DATE_FORMAT(tanggal_daftar,'%Y-%m') = ?");
        mysqli_stmt_bind_param($stmt_b, 'is', $uid, $bulan_sekarang);
        mysqli_stmt_execute($stmt_b);
        $r_b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_b));
        mysqli_stmt_close($stmt_b);
        $objek_bulan_ini = (int)($r_b['total'] ?? 0);
        
        $level_sekarang = $tentukan_level($objek_bulan_ini, $levels);
        $level_next = $levels[0];
        foreach ($levels as $lv) {
            if ($objek_bulan_ini < $lv['target']) { $level_next = $lv; break; }
        }
        
        $stmt_t = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM objek WHERE user_id = ? AND deleted_at IS NULL");
        mysqli_stmt_bind_param($stmt_t, 'i', $uid);
        mysqli_stmt_execute($stmt_t);
        $r_t = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_t));
        mysqli_stmt_close($stmt_t);
        $total_objek = (int)($r_t['total'] ?? 0);
        
        $riwayat = [];
        for ($i = 11; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("-$i month"));
            $riwayat[$ym] = 0;
        }
        $stmt_r = mysqli_prepare($conn, "SELECT DATE_FORMAT(tanggal_daftar,'%Y-%m') as bulan, COUNT(*) as total FROM objek WHERE user_id = ? AND deleted_at IS NULL AND tanggal_daftar >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY bulan");
        mysqli_stmt_bind_param($stmt_r, 'i', $uid);
        mysqli_stmt_execute($stmt_r);
        $res_r = mysqli_stmt_get_result($stmt_r);
        while ($r = mysqli_fetch_assoc($res_r)) {
            if (isset($riwayat[$r['bulan']])) $riwayat[$r['bulan']] = (int)$r['total'];
        }
        mysqli_stmt_close($stmt_r);
        
        $bulan_aktif = 0;
        $level_tertinggi = null;
        foreach ($riwayat as $bln => $jml) {
            if ($jml > 0) $bulan_aktif++;
            $lv = $tentukan_level($jml, $levels);
            if ($lv) {
                if (!$level_tertinggi) $level_tertinggi = $lv;
                else {
                    $idx_now = array_search($lv['nama'], array_column($levels, 'nama'));
                    $idx_max = array_search($level_tertinggi['nama'], array_column($levels, 'nama'));
                    if ($idx_now > $idx_max) $level_tertinggi = $lv;
                }
            }
        }
        
        $tgl_daftar = $user_data['tanggal_daftar'] ? new DateTime($user_data['tanggal_daftar']) : new DateTime();
        $hari_aktif = (new DateTime())->diff($tgl_daftar)->days + 1;
        $bulan_indo = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    ?>
        <main class="max-w-5xl mx-auto px-4 pt-28 pb-16">
            <div class="mb-6">
                <a href="index.php?page=beranda" class="text-sm text-emerald-700 hover:underline flex items-center gap-1 mb-3">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Beranda
                </a>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                    <i data-lucide="target" class="w-7 h-7 text-emerald-700"></i> Misi & Pencapaian
                </h1>
                <p class="text-sm text-slate-500">Riwayat kontribusi dan level Anda di SWEAPING</p>
            </div>
    
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                    <div class="text-3xl font-extrabold text-emerald-700"><?= number_format($total_objek) ?></div>
                    <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Total Objek</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                    <div class="text-3xl font-extrabold text-blue-600"><?= number_format($bulan_aktif) ?></div>
                    <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Bulan Aktif</div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                    <div class="text-3xl font-extrabold text-amber-600"><?= $level_tertinggi ? $level_tertinggi['emoji'] : '🌱' ?></div>
                    <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Level Tertinggi</div>
                    <?php if($level_tertinggi): ?>
                        <div class="text-[10px] text-amber-700 font-bold mt-0.5"><?= $level_tertinggi['nama'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                    <div class="text-3xl font-extrabold text-purple-600"><?= number_format($hari_aktif) ?></div>
                    <div class="text-xs text-slate-500 font-semibold uppercase mt-1">Hari Aktif</div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-emerald-50 via-white to-amber-50 rounded-3xl shadow-xl border-2 border-emerald-100 p-6 md:p-8 mb-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 text-white flex items-center justify-center shadow-md">
                        <i data-lucide="target" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-700 uppercase tracking-wide">🎯 Misi Bulan Ini</div>
                        <div class="font-bold text-slate-800"><?= $bulan_indo[(int)date('n')-1] . ' ' . date('Y') ?></div>
                    </div>
                </div>
                
                <p class="text-slate-700 mb-4">
                    Hai <b><?= e($user_data['nama_lengkap']) ?></b>! 
                    <?php if($objek_bulan_ini > 0): ?>
                        Anda sudah daftarkan <b><?= number_format($objek_bulan_ini) ?> objek baru</b> bulan ini. Keren!
                    <?php else: ?>
                        Belum ada objek baru bulan ini. Ayo mulai!
                    <?php endif; ?>
                </p>
                
                <div class="bg-white rounded-2xl p-4 border-2 border-emerald-200 mb-2">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-3xl"><?= $level_sekarang ? $level_sekarang['emoji'] : '🌱' ?></span>
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase">Capaian</div>
                                <div class="font-bold text-slate-800"><?= $level_sekarang ? $level_sekarang['nama'] : 'Belum ada' ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 font-semibold uppercase">Berikutnya</div>
                            <div class="font-bold text-emerald-700"><?= $level_next['emoji'] ?> <?= $level_next['nama'] ?> (<?= $level_next['target'] ?>)</div>
                        </div>
                    </div>
                    
                    <?php 
                    if ($level_sekarang) {
                        $prog = min(100, round((($objek_bulan_ini - $level_sekarang['target']) / ($level_next['target'] - $level_sekarang['target'])) * 100));
                    } else {
                        $prog = min(100, round(($objek_bulan_ini / $level_next['target']) * 100));
                    }
                    ?>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden mb-2">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-700 rounded-full" style="width: <?= $prog ?>%"></div>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="font-bold text-emerald-700"><?= $prog ?>%</span>
                        <span class="text-slate-600">Butuh <b><?= max(0, $level_next['target'] - $objek_bulan_ini) ?></b> lagi</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 mb-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-6 h-6 text-emerald-700"></i> Riwayat 12 Bulan
                </h2>
                
                <div class="space-y-2">
                    <?php foreach(array_reverse($riwayat, true) as $bln => $jml): 
                        $tahun = substr($bln, 0, 4);
                        $bln_num = (int)substr($bln, 5, 2);
                        $nama_bulan = $bulan_indo[$bln_num - 1] . ' ' . $tahun;
                        $lv = $tentukan_level($jml, $levels);
                        $bar_warna = $jml > 0 ? 'bg-emerald-500' : 'bg-slate-200';
                        $bar_persen = min(100, ($jml / 100) * 100);
                    ?>
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50">
                            <div class="w-24 text-xs font-semibold text-slate-600"><?= $nama_bulan ?></div>
                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="<?= $bar_warna ?> h-full rounded-full" style="width: <?= $bar_persen ?>%"></div>
                            </div>
                            <div class="w-16 text-right text-xs font-bold text-slate-700"><?= $jml ?> objek</div>
                            <div class="w-24 text-right text-xs">
                                <?php if($lv): ?>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-bold text-[10px]">
                                        <?= $lv['emoji'] ?> <?= $lv['nama'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-amber-50 to-emerald-50 rounded-2xl p-5 border-2 border-amber-100 text-center">
                <div class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">💡 Tips Naik Level</div>
                <p class="text-sm text-slate-700">
                    Daftarkan minimal <b>10 objek baru per bulan</b> untuk capai <b>🥉 Rookie</b>. Setiap 10, 25, 50, 100, 250, 500, 750, 1000, 1500 objek → naik level!
                </p>
            </div>
        </main>


<?php elseif ($halaman == 'peta'): ?>
    <main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="map" class="w-7 h-7 text-emerald-700"></i> Peta Sebaran Aksi
            </h1>
            <p class="text-sm text-slate-500">Visualisasi lokasi laporan dari koordinat GPS.</p>
        </div>
        <div id="map" class="rounded-2xl overflow-hidden shadow-xl border border-slate-100 h-[70vh] md:h-[600px]"></div>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([-2.5, 118], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
        <?php
        $res_peta = mysqli_query($conn, "SELECT nama_objek,koordinat,jenis_objek,status_kondisi FROM laporan_aksi WHERE deleted_at IS NULL");
        while ($p = mysqli_fetch_assoc($res_peta)) {
            $parts = array_map('trim', explode(',', $p['koordinat']));
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $warna_map = ['sungai' => '#2563eb', 'pohon' => '#1a5c2e', 'taman' => '#84cc16', 'komposting' => '#b86900'];
$warna = $warna_map[$p['jenis_objek']] ?? '#1a5c2e';
                echo "L.circleMarker([{$parts[0]}, {$parts[1]}], {radius:8, color:'$warna', fillColor:'$warna', fillOpacity:0.8}).addTo(map).bindPopup('<b>" . addslashes(e($p['nama_objek'])) . "</b><br>" . addslashes(e($p['status_kondisi'])) . "');\n";
            }
        }
        ?>
    });
    </script>

<?php elseif ($halaman == 'recycle' && is_admin()): ?>
    <main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="recycle" class="w-7 h-7 text-amber-600"></i> Recycle Bin
            </h1>
            <p class="text-sm text-slate-500">Hanya Super Admin yang bisa restore/hapus permanen.</p>
        </div>
        <?php
        $res_del = mysqli_query($conn, "SELECT * FROM laporan_aksi WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        $data_del = [];
        while($row = mysqli_fetch_assoc($res_del)) $data_del[] = $row;
        ?>
        <?php if(empty($data_del)): ?>
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-16 text-center">
                <i data-lucide="sparkles" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-500">Recycle Bin kosong</p>
            </div>
        <?php else: ?>
            <div class="space-y-3 md:bg-white md:rounded-2xl md:shadow-sm md:border md:border-slate-100 md:overflow-hidden">
                <?php foreach($data_del as $r): ?>
                    <div class="bg-white rounded-2xl border border-slate-100 p-4 md:border-0 md:border-b md:rounded-none md:p-4 md:flex md:items-center md:gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm truncate"><?= e($r['nama_objek']) ?></div>
                            <div class="text-xs text-slate-500"><?= e($r['nama_kontributor']) ?> · dihapus <?= date('d/m/y H:i', strtotime($r['deleted_at'])) ?></div>
                        </div>
                        <div class="flex gap-2 mt-3 md:mt-0">
                            <?php if(is_super_admin()): ?>
                                <a href="index.php?restore=<?= $r['id'] ?>" class="flex-1 md:flex-none text-center text-xs px-3 py-2 bg-emerald-100 text-emerald-700 font-bold rounded-lg hover:bg-emerald-200">Pulihkan</a>
                                <a href="javascript:void(0)" onclick="konfirmasiHapusPermanen(<?= $r['id'] ?>)" class="flex-1 md:flex-none text-center text-xs px-3 py-2 bg-rose-100 text-rose-700 font-bold rounded-lg hover:bg-rose-200">Hapus Permanen</a>
                            <?php else: ?><span class="text-xs text-slate-400">Hanya Super Admin</span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<?php elseif ($halaman == 'kelola_user' && is_admin()): ?>
    <main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6 flex items-start justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                    <i data-lucide="users" class="w-7 h-7 text-indigo-600"></i> Kelola User
                </h1>
                <p class="text-sm text-slate-500">
                    <?php if(is_super_admin()): ?>
                        Anda dapat mengubah role & status user.
                    <?php else: ?>
                        Mode <b>lihat saja</b> — hubungi Super Admin untuk perubahan role/status.
                    <?php endif; ?>
                </p>
            </div>
            <?php if(!is_super_admin()): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Read-Only
                </span>
            <?php endif; ?>
        </div>

<?php 
// ============ TAMPILKAN LINK RESET (sekali pakai) ============
if (is_super_admin() && isset($_SESSION['reset_link_tmp'])): 
    $link_reset   = $_SESSION['reset_link_tmp'];
    $link_nama    = $_SESSION['reset_link_nama'] ?? '-';
    $link_expires = $_SESSION['reset_link_expires'] ?? '';
    // Hapus dari session biar tidak muncul terus
    unset($_SESSION['reset_link_tmp'], $_SESSION['reset_link_nama'], $_SESSION['reset_link_expires']);
?>
<div class="mb-6 bg-gradient-to-br from-amber-50 to-amber-100 border-2 border-amber-300 rounded-2xl p-5 shadow-lg">
    <div class="flex items-start gap-3 mb-3">
        <div class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
        </div>
        <div class="flex-1">
            <div class="font-bold text-amber-900 text-base">🔑 Link Reset Berhasil Dibuat</div>
            <div class="text-xs text-amber-700">Untuk: <b><?= e($link_nama) ?></b></div>
        </div>
    </div>
    
    <div class="bg-white border-2 border-amber-200 rounded-xl p-3 mb-3">
        <div class="text-[10px] text-slate-500 font-semibold uppercase mb-1">Link Reset:</div>
        <div id="link-reset-value" class="text-xs text-slate-800 font-mono break-all leading-relaxed"><?= e($link_reset) ?></div>
    </div>
    
    <div class="text-[10px] text-amber-700 mb-3 flex items-center gap-1">
        <i data-lucide="clock" class="w-3 h-3"></i>
        Berlaku sampai: <b><?= date('d M Y, H:i', strtotime($link_expires)) ?></b> (1 jam dari sekarang)
    </div>
    
    <div class="flex flex-wrap gap-2">
        <button type="button" onclick="copyLinkReset()" 
                class="flex-1 min-w-[120px] px-4 py-2.5 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 transition inline-flex items-center justify-center gap-1.5 text-sm">
            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Link
        </button>
        <button type="button" onclick="kirimLinkWA('<?= e(addslashes($link_nama)) ?>')" 
                class="flex-1 min-w-[120px] px-4 py-2.5 bg-[#25D366] text-white font-bold rounded-lg hover:bg-[#1faa52] transition inline-flex items-center justify-center gap-1.5 text-sm">
            <i data-lucide="send" class="w-3.5 h-3.5"></i> Kirim via WA
        </button>
    </div>
    
    <div class="text-[10px] text-amber-700 mt-3 italic">
        ⚠️ Link ini hanya muncul sekali. Setelah halaman di-refresh, link akan hilang. Pastikan sudah di-copy & kirim ke user.
    </div>
</div>
<script>
function copyLinkReset() {
    var link = document.getElementById('link-reset-value').textContent.trim();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(function() {
            alert('✅ Link berhasil di-copy!\n\nSekarang paste (Ctrl+V) di WhatsApp user.');
        }).catch(function() {
            prompt('Copy link di bawah ini:', link);
        });
    } else {
        prompt('Copy link di bawah ini:', link);
    }
}
function kirimLinkWA(nama) {
    var link = document.getElementById('link-reset-value').textContent.trim();
    var pesan = 'Halo ' + nama + ',\n\n';
    pesan += 'Kami menerima permintaan reset password akun SWEAPING Anda.\n\n';
    pesan += 'Klik link di bawah ini untuk membuat password baru:\n';
    pesan += link + '\n\n';
    pesan += '⚠️ Link hanya berlaku 1 jam. Jika sudah kadaluarsa, hubungi admin kembali.\n\n';
    pesan += 'Terima kasih,\nAdmin SWEAPING';
    var url = 'https://wa.me/?text=' + encodeURIComponent(pesan);
    window.open(url, '_blank');
}
</script>
<?php endif; ?>

<!-- END TAMPILKAN LINK RESET -->

        <?php
        $where = []; $params = []; $types = '';
        if (!empty($_GET['f_role']) && in_array($_GET['f_role'], ['user','admin','super_admin'], true)) {
            $where[] = "role = ?"; $params[] = $_GET['f_role']; $types .= 's';
        }
        if (isset($_GET['f_aktif']) && $_GET['f_aktif'] !== '') {
            $where[] = "aktif = ?"; $params[] = (int)$_GET['f_aktif']; $types .= 'i';
        }
        if (!empty($_GET['f_cari'])) {
            $where[] = "(nama_lengkap LIKE ? OR email LIKE ? OR telepon LIKE ?)";
            $c = '%' . $_GET['f_cari'] . '%';
            $params[] = $c; $params[] = $c; $params[] = $c; $types .= 'sss';
        }
        $sql = "SELECT * FROM users" . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY tanggal_daftar DESC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res_u = mysqli_stmt_get_result($stmt);
        $data_u = [];
        while($row = mysqli_fetch_assoc($res_u)) $data_u[] = $row;
        mysqli_stmt_close($stmt);

        $stat_user = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN role='user' THEN 1 ELSE 0 END) as jml_user,
                SUM(CASE WHEN role='admin' THEN 1 ELSE 0 END) as jml_admin,
                SUM(CASE WHEN role='super_admin' THEN 1 ELSE 0 END) as jml_super,
                SUM(CASE WHEN aktif=0 THEN 1 ELSE 0 END) as jml_banned
            FROM users
        "));
        ?>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-slate-800"><?= number_format($stat_user['total']) ?></div>
                <div class="text-xs text-slate-500 font-semibold uppercase">Total</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-emerald-600"><?= number_format($stat_user['jml_user']) ?></div>
                <div class="text-xs text-slate-500 font-semibold uppercase">User</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-indigo-600"><?= number_format($stat_user['jml_admin']) ?></div>
                <div class="text-xs text-slate-500 font-semibold uppercase">Admin</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-purple-600"><?= number_format($stat_user['jml_super']) ?></div>
                <div class="text-xs text-slate-500 font-semibold uppercase">Super</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-rose-600"><?= number_format($stat_user['jml_banned']) ?></div>
                <div class="text-xs text-slate-500 font-semibold uppercase">Banned</div>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="hidden" name="page" value="kelola_user">
            <select name="f_role" class="px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">-- Semua Role --</option>
                <option value="user" <?= ($_GET['f_role']??'')=='user'?'selected':'' ?>>👤 User</option>
                <option value="admin" <?= ($_GET['f_role']??'')=='admin'?'selected':'' ?>>🛡️ Admin</option>
                <option value="super_admin" <?= ($_GET['f_role']??'')=='super_admin'?'selected':'' ?>>👑 Super Admin</option>
            </select>
            <select name="f_aktif" class="px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">-- Semua Status --</option>
                <option value="1" <?= ($_GET['f_aktif']??'')==='1'?'selected':'' ?>>✅ Aktif</option>
                <option value="0" <?= ($_GET['f_aktif']??'')==='0'?'selected':'' ?>>🚫 Banned</option>
            </select>
            <input type="text" name="f_cari" value="<?= e($_GET['f_cari'] ?? '') ?>" placeholder="Cari nama/email/telepon..." class="px-3 py-2.5 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-sm hover:bg-indigo-700 transition">Filter</button>
                <a href="index.php?page=kelola_user" class="px-4 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl text-sm hover:bg-slate-300 transition">Reset</a>
            </div>
        </form>

        <?php if(empty($data_u)): ?>
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-16 text-center">
                <i data-lucide="users" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-500">Tidak ada user ditemukan</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden divide-y divide-slate-100">
                <?php foreach($data_u as $u): 
                    $role_badge = [
                        'user'        => ['bg-emerald-100 text-emerald-700', '👤 User'],
                        'admin'       => ['bg-indigo-100 text-indigo-700',   '🛡️ Admin'],
                        'super_admin' => ['bg-purple-100 text-purple-700',   '👑 Super Admin'],
                    ];
                    $rb = $role_badge[$u['role']] ?? $role_badge['user'];
                    $is_self = ($u['id'] == ($_SESSION['user_id'] ?? 0));
                ?>
                    <div class="p-4 hover:bg-slate-50 transition flex items-start justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center font-bold flex-shrink-0 <?= !$u['aktif'] ? 'opacity-40 grayscale' : '' ?>">
                                <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold flex items-center gap-2 flex-wrap">
                                    <span class="truncate"><?= e($u['nama_lengkap']) ?></span>
                                    <?php if($is_self): ?>
                                        <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">ANDA</span>
                                    <?php endif; ?>
                                    <?php if(!$u['aktif']): ?>
                                        <span class="text-[10px] bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-bold">BANNED</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-500 truncate"><?= e($u['email']) ?></div>
                                <div class="flex flex-wrap gap-2 mt-1 text-xs">
                                    <?php if(!empty($u['telepon'])): ?>
                                        <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                            <i data-lucide="phone" class="w-3 h-3"></i> <?= e($u['telepon']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-slate-400 flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?= date('d M Y', strtotime($u['tanggal_daftar'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 items-center flex-wrap">
                            <span class="text-xs px-3 py-1.5 rounded-full font-bold <?= $rb[0] ?>">
                                <?= $rb[1] ?>
                            </span>

                            <?php if(is_super_admin() && !$is_self): ?>
                                <form method="POST" class="flex-shrink-0">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi_ubah_role" value="1">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="role" onchange="this.form.submit()" class="text-xs px-3 py-2 border-2 border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                        <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                                        <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                        <option value="super_admin" <?= $u['role']=='super_admin'?'selected':'' ?>>Super Admin</option>
                                    </select>
                                </form>
                                <a href="index.php?page=kelola_user&toggle_aktif=<?= $u['id'] ?>" 
                                   onclick="return confirm('<?= $u['aktif'] ? 'Ban user ini?' : 'Aktifkan kembali user ini?' ?>')"
                                   class="text-xs px-3 py-2 font-bold rounded-lg flex-shrink-0 transition <?= $u['aktif']?'bg-rose-100 text-rose-700 hover:bg-rose-200':'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' ?>">
                                    <?= $u['aktif']?'Ban':'Aktifkan' ?>
                                </a>
                                <?php if($u['role'] !== 'super_admin'): ?>
    <button type="button" 
            onclick="konfirmasiResetPassword(<?= $u['id'] ?>, '<?= e(addslashes($u['nama_lengkap'])) ?>')"
            class="text-xs px-3 py-2 bg-amber-100 text-amber-700 font-bold rounded-lg flex-shrink-0 hover:bg-amber-200 transition inline-flex items-center gap-1"
            title="Buat link reset password">
        <i data-lucide="key-round" class="w-3.5 h-3.5"></i> Reset
    </button>
    <button type="button" 
            onclick="konfirmasiHapusUser(<?= $u['id'] ?>, '<?= e(addslashes($u['nama_lengkap'])) ?>')"
            class="text-xs px-3 py-2 bg-slate-800 text-white font-bold rounded-lg flex-shrink-0 hover:bg-rose-700 transition inline-flex items-center gap-1"
            title="Hapus user">
        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
    </button>
<?php endif; ?>
                            <?php elseif(is_super_admin() && $is_self): ?>
                                <span class="text-xs text-slate-400 italic">Akun Anda</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if(!is_super_admin()): ?>
                <p class="text-xs text-slate-500 mt-4 text-center bg-amber-50 border border-amber-200 rounded-xl p-3">
                    <i data-lucide="info" class="w-3 h-3 inline"></i>
                    Hanya <b>Super Admin</b> yang dapat mengubah role atau status akun.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </main>

<?php elseif ($halaman == 'audit' && is_admin()): ?>
    <main class="max-w-5xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6 flex items-start justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-7 h-7 text-slate-600"></i> Audit Log
                </h1>
                <p class="text-sm text-slate-500">200 aktivitas terakhir sistem.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Read-Only
            </span>
        </div>

        <?php
        $filter_aksi = $_GET['f_aksi'] ?? '';
        $where = []; $params = []; $types = '';
        if ($filter_aksi !== '') {
            $where[] = "aksi = ?"; $params[] = $filter_aksi; $types .= 's';
        }
        $sql = "SELECT * FROM audit_log" . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY waktu DESC LIMIT 200";
        $stmt = mysqli_prepare($conn, $sql);
        if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res_a = mysqli_stmt_get_result($stmt);
        $data_a = [];
        while($row = mysqli_fetch_assoc($res_a)) $data_a[] = $row;
        mysqli_stmt_close($stmt);

        $aksi_list = [];
        $res_aksi = mysqli_query($conn, "SELECT DISTINCT aksi FROM audit_log ORDER BY aksi ASC");
        while($row = mysqli_fetch_assoc($res_aksi)) $aksi_list[] = $row['aksi'];

        function ikon_aksi($aksi) {
            $map = [
                'login'            => ['log-in',        'emerald'],
                'login_gagal'      => ['x-circle',      'rose'],
                'logout'           => ['log-out',       'slate'],
                'register'         => ['user-plus',     'blue'],
                'buat_laporan'     => ['file-plus',     'emerald'],
                'edit_laporan'     => ['pencil',        'amber'],
                'hapus_laporan'    => ['trash-2',       'rose'],
                'restore_laporan'  => ['undo-2',        'blue'],
                'hapus_permanen'   => ['alert-triangle','rose'],
                'daftar_objek'     => ['package-plus',  'emerald'],
                'edit_objek'       => ['pencil',        'amber'],
                'hapus_objek'      => ['trash-2',       'rose'],
                'ubah_role'        => ['shield',        'purple'],
                'toggle_aktif_user'=> ['user-x',        'rose'],
                'kirim_kontak'     => ['mail',          'blue'],
            ];
            return $map[$aksi] ?? ['activity', 'slate'];
        }
        ?>

        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 flex gap-3 flex-wrap">
            <input type="hidden" name="page" value="audit">
            <select name="f_aksi" class="flex-1 min-w-[200px] px-3 py-2.5 border-2 border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-slate-500 focus:outline-none">
                <option value="">-- Semua Aksi --</option>
                <?php foreach($aksi_list as $ak): ?>
                    <option value="<?= e($ak) ?>" <?= $filter_aksi===$ak?'selected':'' ?>><?= e($ak) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-5 py-2.5 bg-slate-700 text-white font-bold rounded-xl text-sm hover:bg-slate-800 transition">Filter</button>
            <a href="index.php?page=audit" class="px-5 py-2.5 bg-slate-200 text-slate-700 font-bold rounded-xl text-sm hover:bg-slate-300 transition">Reset</a>
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <?php if(empty($data_a)): ?>
                <div class="p-16 text-center">
                    <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                    <p class="text-slate-500">Belum ada log</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100">
                    <?php foreach($data_a as $a): 
                        [$ikon, $warna] = ikon_aksi($a['aksi']);
                    ?>
                        <div class="p-4 text-xs hover:bg-slate-50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-lg bg-<?= $warna ?>-100 text-<?= $warna ?>-700 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="<?= $ikon ?>" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-2 mb-1 flex-wrap">
                                        <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-wide text-[10px]">
                                            <?= e($a['aksi']) ?>
                                        </span>
                                        <span class="text-slate-400 text-[10px] flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <?= date('d/m/y H:i:s', strtotime($a['waktu'])) ?>
                                        </span>
                                    </div>
                                    <div class="text-slate-600 mb-1"><?= e($a['detail'] ?: '-') ?></div>
                                    <div class="flex gap-3 text-slate-400 text-[10px] flex-wrap">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="user" class="w-3 h-3"></i>
                                            UID: <?= e($a['user_id'] ?: '-') ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="globe" class="w-3 h-3"></i>
                                            IP: <?= e($a['ip_address'] ?: '-') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

<?php elseif ($halaman == 'pesan' && is_super_admin()): ?>
    <main class="max-w-5xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="mail" class="w-7 h-7 text-blue-600"></i> Pesan Kontak
            </h1>
        </div>
        <?php
        $res_m = mysqli_query($conn, "SELECT * FROM pesan_kontak ORDER BY tanggal_kirim DESC");
        $data_m = [];
        while($row = mysqli_fetch_assoc($res_m)) $data_m[] = $row;
        ?>
        <?php if(empty($data_m)): ?>
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-16 text-center">
                <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                <p class="text-slate-500">Belum ada pesan</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach($data_m as $m): ?>
                    <div class="bg-white rounded-2xl shadow-sm border-2 <?= $m['sudah_dibaca']?'border-slate-100':'border-blue-200' ?> p-5">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <div class="font-bold"><?= e($m['nama']) ?> <?= $m['sudah_dibaca']?'':'<span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold ml-2">BARU</span>' ?></div>
                                <div class="text-xs text-slate-500"><?= e($m['email']) ?></div>
                            </div>
                            <div class="text-xs text-slate-400 flex-shrink-0"><?= date('d/m/y H:i', strtotime($m['tanggal_kirim'])) ?></div>
                        </div>
                        <div class="font-semibold text-sm mb-2"><?= e($m['subjek']) ?></div>
                        <div class="text-sm text-slate-600 mb-3 whitespace-pre-line"><?= e($m['pesan']) ?></div>
                        <?php if(!$m['sudah_dibaca']): ?>
                            <a href="index.php?tandai_dibaca=<?= $m['id'] ?>" class="text-xs text-blue-600 font-bold hover:underline">Tandai sudah dibaca</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<?php elseif ($halaman == 'diagnostik' && is_super_admin()): ?>
    <main class="max-w-3xl mx-auto px-4 pt-28 pb-16">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-1 flex items-center gap-2">
                <i data-lucide="wrench" class="w-7 h-7 text-slate-600"></i> Diagnostik Sistem
            </h1>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-3 text-sm">
            <?php
            $checks = [
                ['PHP Version', PHP_VERSION, version_compare(PHP_VERSION, '7.4', '>=')],
                ['HTTPS Aktif', $is_https ? 'Ya' : 'Tidak', $is_https],
                ['Ekstensi GD', $gd_aktif ? 'Aktif' : 'Tidak Aktif', $gd_aktif],
                ['Ekstensi mysqli', extension_loaded('mysqli') ? 'Aktif' : 'Tidak', extension_loaded('mysqli')],
                ['Folder uploads', is_dir('uploads') ? 'Ada' : 'Belum', is_dir('uploads')],
                ['File .htaccess', file_exists('.htaccess') ? 'Ada' : 'Belum', file_exists('.htaccess')],
                ['File manifest.json', file_exists('manifest.json') ? 'Ada' : 'Belum', file_exists('manifest.json')],
                ['File sw.js', file_exists('sw.js') ? 'Ada' : 'Belum', file_exists('sw.js')],
                ['Upload max size', ini_get('upload_max_filesize'), true],
                ['Memory limit', ini_get('memory_limit'), true],
            ];
            foreach ($checks as $c):
                $ikon = $c[2] ? 'check-circle' : 'x-circle';
                $warna = $c[2] ? 'text-emerald-600' : 'text-rose-600';
            ?>
                <div class="flex justify-between border-b border-slate-100 pb-3 items-center">
                    <span class="font-semibold flex items-center gap-2"><i data-lucide="<?= $ikon ?>" class="w-3.5 h-3.5 <?= $warna ?>"></i> <?= $c[0] ?></span>
                    <span class="<?= $warna ?> font-mono text-xs"><?= e($c[1]) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
<?php endif; ?>

<!-- FOOTER -->
<footer class="bg-gradient-to-br from-emerald-950 to-emerald-900 text-emerald-100 pt-12 pb-32 md:pb-12 mt-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <img src="assets/logo-horizontal.png" alt="SWEAPING" class="h-12 w-auto mb-3 brightness-0 invert" onerror="this.outerHTML='<div class=\'text-2xl font-extrabold text-white\'>SWEAPING</div>'">
                <p class="text-sm text-emerald-200 leading-relaxed">Platform Konservasi & Akuntansi Karbon untuk Indonesia yang lebih hijau.</p>
            </div>
            <div>
                <h3 class="font-bold text-white mb-3">Menu</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php?page=beranda" class="hover:text-amber-300 transition">Beranda</a></li>
                    <li><a href="index.php?page=arsip" class="hover:text-amber-300 transition">Arsip Global</a></li>
                    <li><a href="index.php?page=peta" class="hover:text-amber-300 transition">Peta Sebaran</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold text-white mb-3">Info</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php?page=tentang" class="hover:text-amber-300 transition">Tentang Kami</a></li>
                    <li><a href="index.php?page=kontak" class="hover:text-amber-300 transition">Kontak</a></li>
                    <li><a href="<?= ORG_WEBSITE ?>" target="_blank" class="hover:text-amber-300 transition"><?= ORG_NAME ?></a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-emerald-800 pt-6 text-center text-xs text-emerald-300">
            <p>&copy; <?= date('Y') ?> SWEAPING · Dikembangkan oleh <a href="<?= ORG_WEBSITE ?>" target="_blank" class="text-amber-300 hover:underline"><?= ORG_NAME ?></a></p>
        </div>
    </div>
</footer>

<!-- BACK TO TOP -->
<button id="toTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" 
    class="fixed bottom-24 right-6 z-40 w-12 h-12 bg-emerald-700 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-emerald-800 transition" aria-label="Kembali ke atas">
    <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
</button>
<?php if(in_array($halaman, ['beranda', 'kontak', 'tentang'], true)): ?>
<!-- WHATSAPP FLOATING BUTTON -->
<a href="https://wa.me/6285795861888?text=Halo%20Admin%20SWEAPING%2C%20saya%20ingin%20bertanya..." 
   target="_blank" 
   rel="noopener noreferrer"
   class="wa-float-btn fixed bottom-20 right-4 md:bottom-6 md:right-6 z-40 w-14 h-14 bg-[#25D366] text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-transform"
   aria-label="Chat WhatsApp Admin"
   title="Chat Admin via WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
</a>
<?php endif; ?>

<!-- MODAL HAPUS -->
<div id="modal-hapus" class="modal-backdrop fixed inset-0 bg-black/60 z-[3000] flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="trash-2" class="w-8 h-8 text-rose-600"></i>
            </div>
            <h3 class="text-lg font-bold mb-2">Hapus Laporan?</h3>
            <p class="text-sm text-slate-600 mb-6">Laporan akan dipindah ke Recycle Bin.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="tutupModal('modal-hapus')" class="flex-1 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
            <form method="POST" action="index.php" class="flex-1">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi_hapus" value="1">
                <input type="hidden" name="id" id="hapus-id" value="">
                <button type="submit" class="w-full py-3 bg-rose-600 text-white font-bold rounded-xl">Hapus</button>
            </form>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD AVATAR -->

<div id="modal-avatar" class="modal-backdrop fixed inset-0 bg-black/60 z-[3000] flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="camera" class="w-8 h-8 text-emerald-600"></i>
            </div>
            <h3 class="text-lg font-bold mb-2">Ganti Foto Profil</h3>
            <p class="text-sm text-slate-600 mb-4">Pilih foto dari perangkat Anda</p>
        </div>
        
        <form method="POST" action="index.php" enctype="multipart/form-data" id="form-avatar" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi_upload_avatar" value="1">
            
            <div>
                <label class="block text-sm font-semibold mb-2">Pilih Foto</label>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required
                       id="input-avatar-file"
                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-100 file:text-emerald-700 file:font-bold file:text-xs">
                <p class="text-xs text-slate-500 mt-1.5">Format JPG/PNG/WEBP · Maks 2 MB · Otomatis di-crop jadi kotak</p>
            </div>
            
            <div id="avatar-preview-wrap" style="display:none;" class="text-center">
                <img id="avatar-preview" src="" alt="" class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-emerald-100 shadow-md">
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-avatar')" 
                        class="flex-1 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-emerald-700 text-white font-bold rounded-xl hover:bg-emerald-800 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL HAPUS PERMANEN -->

<div id="modal-hapus-permanen" class="modal-backdrop fixed inset-0 bg-black/60 z-[3000] flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="alert-triangle" class="w-8 h-8 text-rose-600"></i>
            </div>
            <h3 class="text-lg font-bold mb-2 text-rose-700">Hapus Permanen?</h3>
            <p class="text-sm text-slate-600 mb-6">Data dan foto akan <b>hilang selamanya</b>!</p>
        </div>
        <div class="flex gap-3">
            <button onclick="tutupModal('modal-hapus-permanen')" class="flex-1 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
            <a id="btn-hapus-permanen-confirm" href="#" class="flex-1 py-3 bg-rose-600 text-white font-bold rounded-xl text-center">Hapus</a>
        </div>
    </div>
</div>

<!-- MODAL HAPUS OBJEK -->

<div id="modal-hapus-objek" class="modal-backdrop fixed inset-0 bg-black/60 z-[3000] flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="trash-2" class="w-8 h-8 text-rose-600"></i>
            </div>
            <h3 class="text-lg font-bold mb-2">Hapus Objek?</h3>
            <p class="text-sm text-slate-600 mb-1" id="nama-objek-hapus"></p>
            <p class="text-xs text-slate-500 mb-6">Objek akan dipindah ke Recycle Bin. Tidak akan muncul di daftar.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="tutupModal('modal-hapus-objek')" class="flex-1 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
            <a id="btn-hapus-objek-confirm" href="#" class="flex-1 py-3 bg-rose-600 text-white font-bold rounded-xl text-center">Hapus</a>
        </div>
    </div>
</div>

<!-- MODAL RESET PASSWORD -->

<div id="modal-reset-pass" class="modal-backdrop fixed inset-0 bg-black/60 z-[3000] flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="key-round" class="w-8 h-8 text-amber-600"></i>
            </div>
            <h3 class="text-lg font-bold mb-2">Buat Link Reset Password?</h3>
            <p class="text-sm text-slate-600 mb-1">Untuk user:</p>
            <p class="text-base font-bold text-slate-900 mb-4" id="nama-user-reset">-</p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-left text-xs text-amber-800 mb-4">
                <p class="font-bold mb-1">⚠️ Catatan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    <li>Link berlaku <b>1 jam</b></li>
                    <li>Setelah dibuat, link muncul di atas tabel user</li>
                    <li>Copy link & kirim ke user via WhatsApp</li>
                </ul>
            </div>
        </div>
        <form method="POST" action="index.php" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi_generate_reset" value="1">
            <input type="hidden" name="user_id" id="reset-user-id" value="">
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-reset-pass')" 
                        class="flex-1 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 transition">
                    Buat Link Reset
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL HAPUS USER -->
<div id="modal-hapus-user" class="modal-backdrop fixed inset-0 bg-black/60 z-[3000] flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-6">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i data-lucide="user-x" class="w-8 h-8 text-rose-600"></i>
            </div>
            <h3 class="text-lg font-bold mb-2 text-rose-700">Hapus User?</h3>
            <p class="text-sm text-slate-600 mb-1">Anda akan menghapus user:</p>
            <p class="text-base font-bold text-slate-900 mb-4" id="nama-user-hapus">-</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-left text-xs text-amber-800 mb-4">
            <p class="font-bold mb-1 flex items-center gap-1">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Perhatian:
            </p>
            <ul class="list-disc list-inside space-y-0.5 ml-1">
                <li>User tidak akan bisa login lagi</li>
                <li>Laporan & objek tetap tersimpan</li>
                <li>Data dapat di-restore manual oleh developer</li>
            </ul>
        </div>
        <form method="POST" action="index.php" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi_hapus_user" value="1">
            <input type="hidden" name="user_id" id="hapus-user-id" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Ketik nama user <b>persis sama</b> untuk konfirmasi:
                </label>
                <input type="text" name="konfirmasi" id="konfirmasi-nama-user" required
                       placeholder="Ketik nama di sini..."
                       autocomplete="off"
                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:outline-none transition text-sm">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="tutupModal('modal-hapus-user')" 
                        class="flex-1 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition">
                    Hapus User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============ ONBOARDING TOUR ============ -->
<div id="tour-overlay"></div>
<div id="tour-spotlight"></div>

<div id="tour-tooltip" role="dialog" aria-labelledby="tour-title">
    <button id="tour-close" aria-label="Tutup panduan" type="button">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="tour-progress">
        <div class="tour-progress-bar"><div class="tour-progress-fill" id="tour-progress-fill"></div></div>
        <div class="tour-step-counter" id="tour-step-counter">1 / 8</div>
    </div>
    <h4 id="tour-title">Judul</h4>
    <p id="tour-content">Isi</p>
    <div class="tour-buttons">
        <button type="button" class="tour-btn tour-btn-skip" id="tour-btn-skip">Lewati</button>
        <button type="button" class="tour-btn tour-btn-back" id="tour-btn-back">Kembali</button>
        <button type="button" class="tour-btn tour-btn-next" id="tour-btn-next">Lanjut</button>
    </div>
    <div class="tour-arrow"></div>
</div>

<?php if(isset($_SESSION['user_id'])): ?>
<button id="tour-help-btn" onclick="tourStart(true)" title="Buka panduan" aria-label="Buka panduan" type="button">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
</button>
<?php endif; ?>
<script>
// Init Lucide icons

lucide.createIcons();
// Auto-load objek saat halaman siap
window.addEventListener('DOMContentLoaded', function() {
    // Trigger toggle jenis objek
    // (untuk form Lapor yang sudah punya objek_id, radio button diganti hidden input)
    var hiddenJenis = document.querySelector('input[name="jenis_objek"][type="hidden"]');
    if (hiddenJenis && typeof toggleJenisObjek === 'function') {
        toggleJenisObjek();
    } else if (typeof toggleJenisObjek === 'function' && document.getElementById('select_objek')) {
        toggleJenisObjek();
    }
});

// ============ FUNGSI GLOBAL: PILIH FOTO & AMBIL LOKASI ============
function pilihFoto(input) {
    var info = document.getElementById('info_foto');
    var preview = document.getElementById('preview_foto');
    var file = input.files[0];
    if (!info || !preview) return;
    
    info.textContent = ''; info.className = 'text-xs mt-1 text-slate-500';
    preview.style.display = 'none';
    if (!file) return;
    
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        info.textContent = '❌ Format harus JPG, PNG, atau WEBP';
        info.className = 'text-xs text-rose-600 mt-1 font-semibold';
        input.value = '';
        return;
    }
    
    if (file.size > 8 * 1024 * 1024) {
        info.textContent = '❌ Ukuran ' + (file.size/1024/1024).toFixed(2) + ' MB melebihi 8 MB';
        info.className = 'text-xs text-rose-600 mt-1 font-semibold';
        input.value = '';
        return;
    }
    
    info.textContent = '✔ ' + file.name + ' (' + (file.size/1024).toFixed(0) + ' KB)';
    info.className = 'text-xs text-emerald-600 mt-1 font-semibold';
    
    var reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
    
    var inputKamera = document.getElementById('input_foto_kamera');
    if (inputKamera && input.id === 'input_foto_galeri') {
        var dt = new DataTransfer();
        dt.items.add(file);
        inputKamera.files = dt.files;
    }
}

function ambilLokasi() {
    var info = document.getElementById('info_lokasi');
    var input = document.getElementById('input_koordinat');
    if (!info || !input) return;
    if (!navigator.geolocation) { info.textContent = 'GPS tidak didukung.'; return; }
    info.textContent = '⏳ Mengambil lokasi...';
    navigator.geolocation.getCurrentPosition(
        pos => {
            input.value = pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6);
            info.textContent = '✔ Akurasi ±' + Math.round(pos.coords.accuracy) + ' m';
            info.className = 'text-xs text-emerald-600 mt-2 font-semibold';
        },
        err => {
            info.textContent = '❌ Gagal: ' + err.message;
            info.className = 'text-xs text-rose-600 mt-2 font-semibold';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// ============ LIVE CALCULATOR KARBON (Chave 2005) ============
(function() {
    function hitungChave2005(dbh_cm, tinggi_m, density) {
        if (!dbh_cm || !tinggi_m || dbh_cm <= 0 || tinggi_m <= 0) return null;
        density = density || 0.6;
        var agb = 0.0509 * density * Math.pow(dbh_cm, 2) * tinggi_m;
        var biomassa_total = agb * 1.2;
        var karbon = biomassa_total * 0.47;
        var co2 = karbon * 3.667;
        return {
            agb: agb,
            karbon: karbon,
            co2: co2
        };
    }
    
    function updateLiveCalc() {
        var box = document.getElementById('live-calc-karbon');
        if (!box) return;
        var elDbh = document.querySelector('input[name="diameter_batang"]');
        var elTinggi = document.querySelector('input[name="tinggi_pohon"]');
        if (!elDbh || !elTinggi) return;
        
        var dbh = parseFloat(elDbh.value) || 0;
        var tinggi = parseFloat(elTinggi.value) || 0;
        var hasil = hitungChave2005(dbh, tinggi, 0.6);
        
        var elAgb = document.getElementById('calc-agb');
        var elKar = document.getElementById('calc-karbon');
        var elCo2 = document.getElementById('calc-co2');
        
        if (hasil && elAgb && elKar && elCo2) {
            elAgb.textContent = hasil.agb.toFixed(2);
            elKar.textContent = hasil.karbon.toFixed(2);
            elCo2.textContent = hasil.co2.toFixed(2);
        } else if (elAgb && elKar && elCo2) {
            elAgb.textContent = '0.00';
            elKar.textContent = '0.00';
            elCo2.textContent = '0.00';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var elDbh = document.querySelector('input[name="diameter_batang"]');
        var elTinggi = document.querySelector('input[name="tinggi_pohon"]');
        if (elDbh) elDbh.addEventListener('input', updateLiveCalc);
        if (elTinggi) elTinggi.addEventListener('input', updateLiveCalc);
        updateLiveCalc();
    });
})();

// ============ END LIVE CALCULATOR KARBON ============

// ============ LIVE CALCULATOR CO2 KOMPOS ============
(function() {
    function updateKomposCalc() {
        var elVolume = document.querySelector('input[name="volume_sampah"]');
        var elInput = document.getElementById('kompos-calc-input');
        var elCo2 = document.getElementById('kompos-calc-co2');
        if (!elVolume || !elInput || !elCo2) return;
        
        var vol = parseFloat(elVolume.value) || 0;
        var co2 = vol * 0.7;
        
        elInput.textContent = vol.toFixed(0);
        elCo2.textContent = co2.toFixed(2);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var elVolume = document.querySelector('input[name="volume_sampah"]');
        if (elVolume) elVolume.addEventListener('input', updateKomposCalc);
        updateKomposCalc();
    });
})();
// ============ END LIVE CALCULATOR CO2 KOMPOS ============

// ============ LIVE CALCULATOR CH4 SUNGAI (AJAX) ============
(function() {
    var debounceTimer = null;
    
    function fetchCh4() {
        var box = document.getElementById('live-calc-ch4');
        if (!box) return;
        
        var elDo = document.querySelector('select[name="kualitas_do"], input[name="kualitas_do"]');
        var elSampah = document.querySelector('select[name="sampah_sungai"]');
        var elVisual = document.querySelector('select[name="kondisi_air_visual"]');
        if (!elSampah || !elVisual) return;
        
        var doVal = elDo ? elDo.value : '';
        var sampahVal = elSampah.value || '';
        var visualVal = elVisual.value || '';
        
        var url = 'index.php?ajax=ch4_estimate'
            + '&do=' + encodeURIComponent(doVal)
            + '&sampah=' + encodeURIComponent(sampahVal)
            + '&visual=' + encodeURIComponent(visualVal);
        
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var elValue = document.getElementById('ch4-value');
                var elLevel = document.getElementById('ch4-level');
                if (!elValue || !elLevel) return;
                
                if (res.status === 'ok') {
                    elValue.textContent = parseFloat(res.value).toFixed(2);
                    elLevel.textContent = res.level;
                    
                    // Warna level
                    elLevel.className = 'text-base md:text-lg font-extrabold leading-tight';
                    if (res.level === 'Tinggi') elLevel.classList.add('text-rose-600');
                    else if (res.level === 'Sedang') elLevel.classList.add('text-amber-600');
                    else elLevel.classList.add('text-emerald-600');
                }
            })
            .catch(function() { /* silent fail */ });
    }
    
    function debouncedFetch() {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchCh4, 400);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var elDo = document.querySelector('select[name="kualitas_do"], input[name="kualitas_do"]');
        var elSampah = document.querySelector('select[name="sampah_sungai"]');
        var elVisual = document.querySelector('select[name="kondisi_air_visual"]');
        
        if (elDo) elDo.addEventListener('input', debouncedFetch);
        if (elSampah) elSampah.addEventListener('change', debouncedFetch);
        if (elVisual) elVisual.addEventListener('change', debouncedFetch);
    });
})();

// ============ END LIVE CALCULATOR CH4 SUNGAI ============

// ============ PREVIEW AVATAR ============
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('input-avatar-file');
        var preview = document.getElementById('avatar-preview');
        var wrap = document.getElementById('avatar-preview-wrap');
        if (!input || !preview || !wrap) return;
        
        input.addEventListener('change', function() {
            var file = this.files[0];
            if (!file) {
                wrap.style.display = 'none';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file melebihi 2 MB. Silakan pilih foto yang lebih kecil.');
                this.value = '';
                wrap.style.display = 'none';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                wrap.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    });
})();
// ============ END PREVIEW AVATAR ============

// ============ FOLLOW INLINE 3-STATE (Event Delegation) ============
(function() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-follow-inline]');
        if (!btn) return;
        e.preventDefault();
        
        if (btn.disabled) return;
        
        var targetId = btn.dataset.followInline;
        var state = btn.dataset.state || 'not-following';
        var isPrimary = btn.dataset.primary === '1';
        var isSelf = btn.dataset.self === '1';
        
        if (isSelf) return;
        
        // Primary admin tidak bisa di-unfollow
        if (isPrimary && state === 'following') {
            alert('Admin ini adalah admin utama dan tidak dapat di-unfollow.');
            return;
        }
        
        // Tentukan action berdasarkan state
        var action = 'follow';
        if (state === 'following') action = 'unfollow';
        else if (state === 'pending') action = 'cancel';
        
        btn.disabled = true;
        
        var formData = new FormData();
        formData.append('aksi_follow', '1');
        formData.append('target_id', targetId);
        formData.append('action', action);
        formData.append('csrf', '<?= $_SESSION['csrf'] ?? '' ?>');
        
        fetch('index.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            if (res.status === 'ok') {
                var newState = res.new_state;
                btn.dataset.state = newState;
                
                if (newState === 'following') {
                    btn.className = btn.className.replace(/bg-[a-z]+-[0-9]+|text-white|text-slate-[0-9]+|bg-emerald-[0-9]+/g, '')
                        .replace(/\s+/g, ' ') + ' bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700';
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><span>Mengikuti</span>';
                } else if (newState === 'pending') {
                    btn.className = btn.className.replace(/bg-[a-z]+-[0-9]+|text-white|text-slate-[0-9]+|bg-emerald-[0-9]+/g, '')
                        .replace(/\s+/g, ' ') + ' bg-amber-100 text-amber-700 hover:bg-amber-200';
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Menunggu</span>';
                } else {
                    btn.className = btn.className.replace(/bg-[a-z]+-[0-9]+|text-white|text-slate-[0-9]+|bg-emerald-[0-9]+/g, '')
                        .replace(/\s+/g, ' ') + ' bg-emerald-600 text-white hover:bg-emerald-700';
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>Ikuti</span>';
                }
                
                // Update state tombol di seluruh halaman (untuk konsistensi)
                var allSameTarget = document.querySelectorAll('[data-follow-inline="' + targetId + '"]');
                allSameTarget.forEach(function(b) {
                    if (b !== btn) {
                        b.dataset.state = newState;
                        b.outerHTML = btn.outerHTML;
                    }
                });
            } else {
                alert(res.message || 'Gagal memproses.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            alert('Koneksi gagal. Coba lagi.');
        });
    });
})();
// ============ END FOLLOW INLINE 3-STATE ============

function toggleJenisObjek() {
        var checked = document.querySelector('input[name="jenis_objek"]:checked') 
               || document.querySelector('input[name="jenis_objek"][type="hidden"]');
    if (!checked) return;
    var val = checked.value;
    
    // Toggle kotak umur pohon
    var boxUmur = document.getElementById('box_umur_pohon');
    if (boxUmur) boxUmur.style.display = (val === 'pohon') ? 'block' : 'none';
    
    // Toggle kotak komposting
    var boxKompos = document.getElementById('box_komposting');
    if (boxKompos) boxKompos.style.display = (val === 'komposting') ? 'block' : 'none';
    
    // Toggle box monitoring pohon (Bagian D)
    var boxMonPohon = document.getElementById('box_monitoring_pohon');
    if (boxMonPohon) boxMonPohon.style.display = (val === 'pohon') ? 'block' : 'none';
        // Toggle box monitoring sungai (Bagian D)
    var boxMonSungai = document.getElementById('box_monitoring_sungai');
    if (boxMonSungai) boxMonSungai.style.display = (val === 'sungai') ? 'block' : 'none';
        // Toggle box monitoring taman (Bagian D)
    var boxMonTaman = document.getElementById('box_monitoring_taman');
    if (boxMonTaman) boxMonTaman.style.display = (val === 'taman') ? 'block' : 'none';
        // Toggle box monitoring komposting (Bagian D-G)
    var boxMonKompos = document.getElementById('box_monitoring_komposting');
    if (boxMonKompos) boxMonKompos.style.display = (val === 'komposting') ? 'block' : 'none';
    
    // Isi dropdown objek via AJAX
    var selectObjek = document.getElementById('select_objek');
    if (selectObjek) {
        selectObjek.innerHTML = '<option value="">-- Memuat... --</option>';
        
        fetch('index.php?ajax=objek_list&jenis=' + encodeURIComponent(val))
            .then(response => response.json())
            .then(result => {
                if (result.status === 'ok') {
                    var html = '<option value="">-- Pilih Objek --</option>';
                    if (result.data.length === 0) {
                        html += '<option value="" disabled>Tidak ada objek jenis ' + val + '</option>';
                    } else {
                        result.data.forEach(function(o) {
                            var label = o.kode_objek + ' — ' + o.nama_objek;
                            if (o.lokasi) label += ' (' + o.lokasi + ')';
                            html += '<option value="' + o.id + '">' + label + '</option>';
                        });
                    }
                    selectObjek.innerHTML = html;
                } else {
                    selectObjek.innerHTML = '<option value="">-- Gagal memuat --</option>';
                }
            })
            .catch(err => {
                selectObjek.innerHTML = '<option value="">-- Error: ' + err.message + ' --</option>';
            });
    }
}

// Toggle field pohon di form daftar objek
function togglePohonFields() {
    var checked = document.querySelector('input[name="jenis_objek"]:checked');
    if (!checked) return;
    var val = checked.value;

    // Toggle box pohon
    var boxPohon = document.getElementById('box_pohon_fields');
    if (boxPohon) {
        if (val === 'pohon') {
            boxPohon.style.display = 'block';
        } else {
            boxPohon.style.display = 'none';
        }
    }

    // Toggle box sungai
    var boxSungai = document.getElementById('box_sungai_fields');
    if (boxSungai) {
        if (val === 'sungai') {
            boxSungai.style.display = 'block';
        } else {
            boxSungai.style.display = 'none';
        }
    }
        // Toggle box taman
    var boxTaman = document.getElementById('box_taman_fields');
    if (boxTaman) {
        if (val === 'taman') {
            boxTaman.style.display = 'block';
        } else {
            boxTaman.style.display = 'none';
        }
    }
        // Toggle box komposting
    var boxKompos = document.getElementById('box_komposting_fields');
    if (boxKompos) {
        if (val === 'komposting') {
            boxKompos.style.display = 'block';
        } else {
            boxKompos.style.display = 'none';
        }
    }
        // Toggle box monitoring sungai
    var boxMonSungai = document.getElementById('box_monitoring_sungai');
    if (boxMonSungai) {
        if (val === 'sungai') {
            boxMonSungai.style.display = 'block';
        } else {
            boxMonSungai.style.display = 'none';
        }
    }
}

// Panggil saat halaman load (kalau default pohon terpilih)
window.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('box_pohon_fields')) {
        togglePohonFields();
    }
});

// Nav mobile toggle
function toggleNavMobile() {
    var nav = document.getElementById('nav-mobile');
    var iMenu = document.getElementById('icon-menu');
    var iClose = document.getElementById('icon-close');
    if (nav.classList.contains('hidden')) {
        nav.classList.remove('hidden');
        iMenu.classList.add('hidden');
        iClose.classList.remove('hidden');
    } else {
        nav.classList.add('hidden');
        iMenu.classList.remove('hidden');
        iClose.classList.add('hidden');
    }
}

// Navbar scroll
window.addEventListener('scroll', function() {
    var btn = document.getElementById('toTop');
    if (window.scrollY > 400) btn.classList.add('show'); else btn.classList.remove('show');
});

// Modals
function bukaModal(id) { document.getElementById(id).classList.add('show'); document.body.style.overflow = 'hidden'; }
function tutupModal(id) { document.getElementById(id).classList.remove('show'); document.body.style.overflow = ''; }
document.querySelectorAll('.modal-backdrop').forEach(el => {
    el.addEventListener('click', function(e) { if (e.target === this) tutupModal(this.id); });
});
function konfirmasiHapus(id) {
    document.getElementById('hapus-id').value = id;
    bukaModal('modal-hapus');
}
function konfirmasiHapusPermanen(id) {
    document.getElementById('btn-hapus-permanen-confirm').href = 'index.php?hapus_permanen=' + id;
    bukaModal('modal-hapus-permanen');
}
function konfirmasiHapusObjek(id, nama) {
    document.getElementById('btn-hapus-objek-confirm').href = 'index.php?hapus_objek=' + id;
    document.getElementById('nama-objek-hapus').textContent = '"' + nama + '"';
    bukaModal('modal-hapus-objek');
}

function konfirmasiResetPassword(id, nama) {
    document.getElementById('reset-user-id').value = id;
    document.getElementById('nama-user-reset').textContent = '"' + nama + '"';
    bukaModal('modal-reset-pass');
}

function konfirmasiHapusUser(id, nama) {
    document.getElementById('hapus-user-id').value = id;
    document.getElementById('nama-user-hapus').textContent = '"' + nama + '"';
    
    // Reset input konfirmasi + kasih placeholder dinamis
    var inp = document.getElementById('konfirmasi-nama-user');
    if (inp) {
        inp.value = '';
        inp.placeholder = 'Ketik: ' + nama;
    }
    
    bukaModal('modal-hapus-user');
}

// Share
function bagikan() {
    if (navigator.share) {
        navigator.share({ title: 'SWEAPING', text: 'Platform Konservasi & Akuntansi Karbon', url: window.location.href }).catch(() => {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(() => alert('Link disalin'));
    }
}

// Service Worker - dinonaktifkan sementara
// if ('serviceWorker' in navigator) {
//     window.addEventListener('load', () => {
//         navigator.serviceWorker.register('sw.js').catch(() => {});
//     });
// }

// ============ ONBOARDING TOUR ============
(function() {
    'use strict';
    
    var TOUR_KEY = 'sweaping_tour_v1';
    var STORAGE_OK = (function() {
        try { localStorage.setItem('__t','1'); localStorage.removeItem('__t'); return true; }
        catch(e) { return false; }
    })();
    
    var steps = [
        { target: null,
          title: '👋 Selamat Datang di SWEAPING!',
          content: 'Mari saya kenalkan fitur-fiturnya sebentar (±1 menit). Anda bisa lewati kapan saja.' },
        { target: 'header a[href*="page=beranda"]',
          title: '🏠 Beranda',
          content: 'Klik logo untuk kembali ke halaman utama kapan saja.',
          position: 'bottom', optional: true },
        { target: 'section.hero-bg',
          title: '🌱 Misi Kami',
          content: 'Hulu Jaga Hilir, Merawat Alam Indonesia — platform untuk mendokumentasikan aksi lingkungan Anda.',
          position: 'bottom' },
        { target: 'a[href*="page=objek_daftar"]',
          title: '➕ Mulai dari Sini',
          content: 'Klik "Tambah Objek" untuk mendaftarkan pohon, sungai, taman, atau tempat komposting di sekitarmu.',
          position: 'bottom', optional: true },
        { target: '#stats',
          title: '📊 Statistik Komunitas',
          content: 'Lihat total laporan, jumlah pohon, dan CO₂ yang berhasil diserap komunitas SWEAPING.',
          position: 'bottom' },
        { target: 'a[href*="page=arsip"]',
          title: '📁 Arsip Global',
          content: 'Telusuri semua laporan aksi dari seluruh Indonesia.',
          position: 'bottom', optional: true },
        { target: 'a[href*="page=peta"]',
          title: '🗺️ Peta Sebaran',
          content: 'Lihat sebaran aksi di peta interaktif.',
          position: 'bottom', optional: true },
        { target: null,
          title: '🎉 Siap Memulai!',
          content: 'Sekarang giliran Anda! Klik tombol "Tambah Objek" untuk mendaftarkan objek pertama Anda.' }
    ];
    
    var currentStep = 0, activeSteps = [], isActive = false;
    var overlay, spotlight, tooltip, titleEl, contentEl, counterEl, fillEl, btnNext, btnBack, btnSkip, btnClose;
    
    function initElements() {
        overlay = document.getElementById('tour-overlay');
        spotlight = document.getElementById('tour-spotlight');
        tooltip = document.getElementById('tour-tooltip');
        if (!overlay || !spotlight || !tooltip) return false;
        
        titleEl = document.getElementById('tour-title');
        contentEl = document.getElementById('tour-content');
        counterEl = document.getElementById('tour-step-counter');
        fillEl = document.getElementById('tour-progress-fill');
        btnNext = document.getElementById('tour-btn-next');
        btnBack = document.getElementById('tour-btn-back');
        btnSkip = document.getElementById('tour-btn-skip');
        btnClose = document.getElementById('tour-close');
        
        if (btnNext) btnNext.onclick = tourNext;
        if (btnBack) btnBack.onclick = tourPrev;
        if (btnSkip) btnSkip.onclick = function() { tourEnd('skipped'); };
        if (btnClose) btnClose.onclick = function() { tourEnd('skipped'); };
        
        document.addEventListener('keydown', function(e) {
            if (!isActive) return;
            if (e.key === 'Escape') tourEnd('skipped');
            else if (e.key === 'ArrowRight') tourNext();
            else if (e.key === 'ArrowLeft') tourPrev();
        });
        return true;
    }
    
    function buildActiveSteps() {
        activeSteps = [];
        for (var i = 0; i < steps.length; i++) {
            var s = steps[i];
            if (!s.target) { activeSteps.push(s); continue; }
            var found = false, selectors = s.target.split(',');
            for (var j = 0; j < selectors.length; j++) {
                try {
                    var el = document.querySelector(selectors[j].trim());
                    if (el && el.offsetParent !== null) { found = true; s._element = el; break; }
                } catch(e) {}
            }
            if (found || !s.optional) activeSteps.push(s);
        }
    }
    
    function tourStart(replay) {
        if (isActive) return;
        
        if (!replay) {
            var page = new URLSearchParams(window.location.search).get('page') || 'beranda';
            if (page !== 'beranda') return;
            if (STORAGE_OK) {
                try {
                    var v = localStorage.getItem(TOUR_KEY);
                    if (v === 'done' || v === 'skipped') return;
                } catch(e) {}
            }
        }
        
        if (!initElements()) return;
        buildActiveSteps();
        if (activeSteps.length === 0) return;
        
        currentStep = 0; isActive = true;
        document.body.style.overflow = 'hidden';
        overlay.classList.add('active');
        tooltip.classList.add('active');
        showStep(0);
    }
    
    function showStep(index) {
        if (index < 0 || index >= activeSteps.length) return;
        currentStep = index;
        var step = activeSteps[index];
        
        titleEl.textContent = step.title || '';
        contentEl.textContent = step.content || '';
        counterEl.textContent = (index + 1) + ' / ' + activeSteps.length;
        fillEl.style.width = ((index + 1) / activeSteps.length * 100) + '%';
        btnBack.style.display = index === 0 ? 'none' : '';
        btnNext.textContent = index === activeSteps.length - 1 ? 'Selesai' : 'Lanjut';
        
        if (step._element) {
            var rect = step._element.getBoundingClientRect();
            if (rect.top < 0 || rect.bottom > window.innerHeight) {
                step._element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function() { drawStep(step); }, 400);
                return;
            }
            drawStep(step);
        } else {
            spotlight.classList.remove('active');
            tooltip.setAttribute('data-pos', 'center');
            tooltip.style.top = '50%';
            tooltip.style.left = '50%';
            tooltip.style.transform = 'translate(-50%, -50%)';
            tooltip.style.maxWidth = '420px';
        }
    }
    
    function drawStep(step) {
        var pad = 8, el = step._element;
        var r = el.getBoundingClientRect();
        
        spotlight.style.top = (r.top - pad) + 'px';
        spotlight.style.left = (r.left - pad) + 'px';
        spotlight.style.width = (r.width + pad * 2) + 'px';
        spotlight.style.height = (r.height + pad * 2) + 'px';
        spotlight.classList.add('active');
        
        tooltip.style.maxWidth = '340px';
        tooltip.style.transform = 'none';
        
        var tw = tooltip.offsetWidth || 340;
        var th = tooltip.offsetHeight || 180;
        var margin = 16;
        var pos = step.position || 'bottom';
        var top, left;
        
        if (pos === 'bottom') {
            top = r.bottom + pad + margin;
            left = r.left;
            if (top + th > window.innerHeight - 10) {
                pos = 'top';
                top = r.top - pad - margin - th;
            }
        } else if (pos === 'top') {
            top = r.top - pad - margin - th;
            left = r.left;
            if (top < 10) {
                pos = 'bottom';
                top = r.bottom + pad + margin;
            }
        } else {
            top = r.bottom + pad + margin;
            left = r.left;
        }
        tooltip.setAttribute('data-pos', pos);
        
        if (left + tw > window.innerWidth - 12) left = window.innerWidth - tw - 12;
        if (left < 12) left = 12;
        if (top < 12) top = 12;
        if (top + th > window.innerHeight - 12) top = window.innerHeight - th - 12;
        
        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';
        
        var arrow = tooltip.querySelector('.tour-arrow');
        if (arrow) {
            var arrowLeft = (r.left + r.width / 2) - left - 6;
            if (arrowLeft < 12) arrowLeft = 12;
            if (arrowLeft > tw - 24) arrowLeft = tw - 24;
            arrow.style.left = arrowLeft + 'px';
        }
    }
    
    function tourNext() {
        if (currentStep + 1 >= activeSteps.length) tourEnd('done');
        else showStep(currentStep + 1);
    }
    function tourPrev() { if (currentStep > 0) showStep(currentStep - 1); }
    
    function tourEnd(status) {
        isActive = false;
        if (STORAGE_OK) {
            try { localStorage.setItem(TOUR_KEY, status === 'done' ? 'done' : 'skipped'); } catch(e) {}
        }
        if (overlay) overlay.classList.remove('active');
        if (spotlight) spotlight.classList.remove('active');
        if (tooltip) tooltip.classList.remove('active');
        document.body.style.overflow = '';
        
        var hb = document.getElementById('tour-help-btn');
        if (hb) hb.classList.add('show');
    }
    
    window.tourStart = tourStart;
    window.tourNext = tourNext;
    window.tourPrev = tourPrev;
    window.tourEnd = tourEnd;
    
    document.addEventListener('DOMContentLoaded', function() {
        if (!initElements()) return;
        <?php if(isset($_SESSION['user_id'])): ?>
        var hb = document.getElementById('tour-help-btn');
        if (hb) hb.classList.add('show');
        setTimeout(function() { tourStart(false); }, 1500);
        <?php endif; ?>
    });
})();
// ============ END ONBOARDING TOUR ============

// ============ TOGGLE PASSWORD (GLOBAL) ============
function togglePass(inputId, btn) {
    var inp = document.getElementById(inputId);
    if (!inp) return;
    var eye = btn.querySelector('.eye-icon');
    var eyeOff = btn.querySelector('.eye-off-icon');
    if (inp.type === 'password') {
        inp.type = 'text';
        if (eye) eye.classList.add('hidden');
        if (eyeOff) eyeOff.classList.remove('hidden');
    } else {
        inp.type = 'password';
        if (eye) eye.classList.remove('hidden');
        if (eyeOff) eyeOff.classList.add('hidden');
    }
}
// ============ END TOGGLE PASSWORD ============

// Refresh icons after dynamic content
setTimeout(() => lucide.createIcons(), 100);
</script>
</body>
</html>