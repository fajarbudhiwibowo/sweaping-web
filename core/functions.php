<?php
// ==========================================
// 1. SESSION & AUTENTIKASI DASAR
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($str) { 
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); 
}

function is_logged_in() { 
    return isset($_SESSION['user_id']); 
}

// Mendukung role 'admin' dan 'admin_website' agar kompatibel dengan database lama/baru
function is_admin() { 
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin', 'admin', 'admin_website'], true); 
}

function is_super_admin() { 
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'; 
}

function redirect($url) { 
    header("Location: " . $url); 
    exit; 
}

// ==========================================
// 2. SECURITY (CSRF PROTECTION) - Disederhanakan
// ==========================================
if (!function_exists('csrf_field')) {
    function csrf_field() {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf']) . '">';
    }
}

if (!function_exists('csrf_check')) {
    function csrf_check() {
        if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
            http_response_code(419);
            die("Permintaan tidak valid (CSRF token missing/mismatch).");
        }
    }
}

// ==========================================
// 3. UI & TAMPILAN (Avatar, Badge, URL)
// ==========================================
function tampil_badge_verified($role) {
    if (in_array($role, ['super_admin', 'admin', 'admin_website'])) {
        return '<svg class="w-4 h-4 text-blue-500 inline-block ml-1" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
    }
    return '';
}

function url_foto($row, $kolom_foto = 'foto', $tipe = 'thumb') {
    if (!empty($row['telegram_file_id'])) { 
        return 'index.php?telegram_photo=' . urlencode($row['telegram_file_id']); 
    }
    if (empty($row[$kolom_foto])) return '';
    $prefix = ($tipe === 'thumb') ? 'thumb_' : '';
    return 'uploads/' . $prefix . $row[$kolom_foto];
}

function url_avatar($row) {
    if (empty($row['avatar'])) return '';
    if (!file_exists('uploads/avatar/' . $row['avatar'])) return '';
    return 'uploads/avatar/' . $row['avatar'];
}

function tampil_avatar($row, $size_class = 'w-8 h-8', $font_class = 'text-xs') {
    $avatar_url = url_avatar($row);
    if ($avatar_url) { 
        return '<img src="' . e($avatar_url) . '" alt="" class="' . e($size_class) . ' rounded-full object-cover flex-shrink-0 ring-2 ring-white">'; 
    }
    $initial = strtoupper(substr($row['nama_lengkap'] ?? '?', 0, 1));
    return '<div class="' . e($size_class) . ' rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center font-bold ' . e($font_class) . ' flex-shrink-0 ring-2 ring-white">' . e($initial) . '</div>';
}

// ==========================================
// 4. HELPER ORGANISASI
// ==========================================
if (!function_exists('is_pic_organisasi')) {
    function is_pic_organisasi($conn, $user_id) {
        if (!$user_id) return false;
        
        $stmt = mysqli_prepare($conn, "SELECT id FROM organisasi WHERE pic_user_id = ? AND deleted_at IS NULL LIMIT 1");
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        $is_pic = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        
        return $is_pic;
    }
}

// ==========================================
// 5. FUNGSI TELEGRAM (Notifikasi Eksternal)
// ==========================================
function telegram_chat_id($jenis_objek) {
    $map = [
        'pohon'      => defined('TELEGRAM_CHAT_POHON')      ? TELEGRAM_CHAT_POHON      : '-5514076425',
        'sungai'     => defined('TELEGRAM_CHAT_SUNGAI')     ? TELEGRAM_CHAT_SUNGAI     : '-5570325700',
        'taman'      => defined('TELEGRAM_CHAT_TAMAN')      ? TELEGRAM_CHAT_TAMAN      : '-5557672201',
        'komposting' => defined('TELEGRAM_CHAT_KOMPOSTING') ? TELEGRAM_CHAT_KOMPOSTING : '-5567901340',
        'hutan'      => defined('TELEGRAM_CHAT_HUTAN')      ? TELEGRAM_CHAT_HUTAN      : '-1004453102625',
        'lingkungan' => defined('TELEGRAM_CHAT_LINGKUNGAN') ? TELEGRAM_CHAT_LINGKUNGAN : '-1004482565833',
    ];
    return $map[$jenis_objek] ?? '';
}

// ==========================================
// 6. GAMIFIKASI (Misi & XP)
// ==========================================
function cek_dan_update_misi($conn, $user_id, $aksi_type) {
    // Pastikan array session untuk notif misi sudah ada
    if (!isset($_SESSION['notif_misi'])) {
        $_SESSION['notif_misi'] = [];
    }

    $stmt_c = mysqli_prepare($conn, "SELECT id, target_jumlah, hadiah_xp FROM challenges WHERE target_aksi = ? AND is_aktif = 1");
    if (!$stmt_c) return;
    
    mysqli_stmt_bind_param($stmt_c, 's', $aksi_type);
    mysqli_stmt_execute($stmt_c);
    $res_c = mysqli_stmt_get_result($stmt_c);

    while ($c = mysqli_fetch_assoc($res_c)) {
        $ch_id = $c['id']; 
        $target = $c['target_jumlah']; 
        $xp = $c['hadiah_xp'];
        
        $stmt_p = mysqli_prepare($conn, "SELECT id, progres_saat_ini, status FROM user_challenges WHERE user_id = ? AND challenge_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_p, 'ii', $user_id, $ch_id);
        mysqli_stmt_execute($stmt_p);
        $prog = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_p));
        mysqli_stmt_close($stmt_p);

        if ($prog) {
            if ($prog['status'] === 'berjalan') {
                $new_prog = $prog['progres_saat_ini'] + 1;
                if ($new_prog >= $target) {
                    $stmt_u = mysqli_prepare($conn, "UPDATE user_challenges SET progres_saat_ini = ?, status = 'selesai' WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_u, 'ii', $new_prog, $prog['id']);
                    mysqli_stmt_execute($stmt_u); mysqli_stmt_close($stmt_u);

                    $stmt_xp = mysqli_prepare($conn, "UPDATE users SET eco_points = eco_points + ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_xp, 'ii', $xp, $user_id);
                    mysqli_stmt_execute($stmt_xp); mysqli_stmt_close($stmt_xp);
                    
                    $_SESSION['notif_misi'][] = "🎉 Misi Selesai! Anda mendapat " . $xp . " Eco-Points!";
                } else {
                    $stmt_u = mysqli_prepare($conn, "UPDATE user_challenges SET progres_saat_ini = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_u, 'ii', $new_prog, $prog['id']);
                    mysqli_stmt_execute($stmt_u); mysqli_stmt_close($stmt_u);
                }
            }
        } else {
            $new_prog = 1; 
            $status = ($new_prog >= $target) ? 'selesai' : 'berjalan';
            
            $stmt_i = mysqli_prepare($conn, "INSERT INTO user_challenges (user_id, challenge_id, progres_saat_ini, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_i, 'iiis', $user_id, $ch_id, $new_prog, $status);
            mysqli_stmt_execute($stmt_i); mysqli_stmt_close($stmt_i);

            if ($status === 'selesai') {
                $stmt_xp = mysqli_prepare($conn, "UPDATE users SET eco_points = eco_points + ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt_xp, 'ii', $xp, $user_id);
                mysqli_stmt_execute($stmt_xp); mysqli_stmt_close($stmt_xp);
                $_SESSION['notif_misi'][] = "🎉 Misi Selesai! Anda mendapat " . $xp . " Eco-Points!";
            }
        }
    }
    mysqli_stmt_close($stmt_c);
}