<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() { return isset($_SESSION['user_id']); }
function is_admin() { return isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin', 'admin_website'], true); }
function is_super_admin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'; }

function tampil_badge_verified($role) {
    if (in_array($role, ['super_admin', 'admin_website'])) {
        return '<svg class="w-4 h-4 text-blue-500 inline-block ml-1" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
    }
    return '';
}

function redirect($url) {
    header("Location: " . $url);
    exit;
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
