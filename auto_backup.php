<?php
// auto_backup.php
// Script ini dipanggil oleh Cron Job atau via Web
// Jangan lupa ganti path dan token di bawah

require_once 'config/database.php';

// --- KONFIGURASI ---
$telegram_token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '8999092634:AAFEvCHzZEqVby51F8XeCQFWTCKuQZO36s0';
$telegram_chat_id = '-1004482565833'; // GANTI DENGAN CHAT ID TELEGRAM ANDA (Admin)
$project_path = __DIR__; // Path folder project
$git_branch = 'main'; // Atau 'master', sesuaikan dengan repo Anda

// Cek apakah dijalankan via CLI (Terminal) atau Web
$is_cli = php_sapi_name() === 'cli';
if (!$is_cli) {
    // Jika via web, pastikan hanya admin yang bisa akses
    session_start();
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['super_admin', 'admin'])) {
        die("Akses ditolak. Hanya admin yang bisa menjalankan backup manual.");
    }
    header('Content-Type: text/plain');
}

echo "=== MEMULAI PROSES BACKUP ===\n";
$start_time = microtime(true);

// 1. BUAT DUMP DATABASE
$filename = 'backup_sweaping_' . date('Ymd_His') . '.sql';
$zip_filename = 'backup_sweaping_' . date('Ymd_His') . '.zip';
$zip_path = __DIR__ . '/uploads/' . $zip_filename;

$tables = [];
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) { $tables[] = $row[0]; }

$sqlScript = "-- SWEAPING AUTO BACKUP\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
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
echo "[1/4] Database berhasil di-dump.\n";

// 2. ZIP FILE SQL
$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $zip->addFromString($filename, $sqlScript);
    $zip->close();
    echo "[2/4] File ZIP berhasil dibuat: " . $zip_filename . "\n";
} else {
    die("Gagal membuat file ZIP.\n");
}

// 3. KIRIM KE TELEGRAM
if (!empty($telegram_token) && !empty($telegram_chat_id)) {
    $url = "https://api.telegram.org/bot{$telegram_token}/sendDocument";
    $post_fields = [
        'chat_id' => $telegram_chat_id,
        'document' => new CURLFile($zip_path),
        'caption' => "📦 *Auto-Backup Database SWEAPING*\n🗓️ Tanggal: " . date('d M Y H:i') . "\n💾 Ukuran: " . round(filesize($zip_path)/1024/1024, 2) . " MB\n\n_Sistem otomatis oleh Dev Hub_",
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post_fields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $res_data = json_decode($response, true);
    if (isset($res_data['ok']) && $res_data['ok']) {
        echo "[3/4] File ZIP berhasil dikirim ke Telegram.\n";
        @unlink($zip_path); // Hapus file lokal setelah dikirim agar hosting tidak penuh
    } else {
        echo "[3/4] GAGAL mengirim ke Telegram: " . ($res_data['description'] ?? 'Unknown Error') . "\n";
    }
} else {
    echo "[3/4] SKIP: Token Telegram atau Chat ID belum diset.\n";
}

// 4. GIT COMMIT & PUSH
// Pastikan Git sudah terkonfigurasi di server (git config user.name & user.email)
if (function_exists('shell_exec')) {
    echo "[4/4] Memulai Git Commit & Push...\n";
    $cmd = "cd {$project_path} && git add -A && git commit -m 'Auto-commit: Weekly backup & maintenance " . date('Ymd') . "' && git push origin {$git_branch} 2>&1";
    $output = shell_exec($cmd);
    echo "Hasil Git:\n" . $output . "\n";
} else {
    echo "[4/4] SKIP: Fungsi shell_exec dinonaktifkan oleh hosting.\n";
}

$end_time = microtime(true);
$duration = round($end_time - $start_time, 2);
echo "=== SELESAI dalam {$duration} detik ===\n";