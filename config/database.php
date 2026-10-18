<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'lsmw8652_sweaping_user');
define('DB_PASS', 'FajarFitri@040815');
define('DB_NAME', 'lsmw8652_sweaping');

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("<div style='font-family:sans-serif;padding:2rem;'><h1>Koneksi Database Gagal</h1><p>" . mysqli_connect_error() . "</p></div>");
}
mysqli_set_charset($conn, 'utf8mb4');
?>
