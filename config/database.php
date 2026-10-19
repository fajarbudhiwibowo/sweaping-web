<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'lsmw8652_sweaping_user');
define('DB_PASS', 'FajarFitri@040815');
define('DB_NAME', 'lsmw8652_sweaping');


// 2. Konfigurasi Bot Telegram (TAMBAHKAN DI SINI)
define('TELEGRAM_BOT_TOKEN', '8993807405:AAG7YMWB8Bju2Wg3s1xM9GMJGO6Sns9OhC4'); // <--- GANTI DENGAN TOKEN ASLI DARI BOTFATHER
// (Opsional) Biarkan BOT_TOKEN jika Anda memakainya di tempat lain
if (!defined('BOT_TOKEN')) {
    define('BOT_TOKEN', TELEGRAM_BOT_TOKEN);
}

// 3. Konfigurasi Chat ID Grup (Untuk Notifikasi Otomatis)
// (Opsional, jika Anda menggunakan fitur kirim pesan otomatis)
define('TELEGRAM_CHAT_POHON', '-5514076425');
define('TELEGRAM_CHAT_SUNGAI', '-5570325700');
define('TELEGRAM_CHAT_TAMAN', '-5557672201');
define('TELEGRAM_CHAT_KOMPOSTING', '-5567901340');
define('TELEGRAM_CHAT_HUTAN', '-1004453102625');
define('TELEGRAM_CHAT_LINGKUNGAN', '-1004482565833');

// 4. Koneksi Database
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("<div style='font-family:sans-serif; padding:20px; color:red;'><b>Koneksi Database Gagal:</b> " . mysqli_connect_error() . "</div>");
}
mysqli_set_charset($conn, "utf8mb4");
?>
