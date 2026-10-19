<?php
if (!is_logged_in() || !is_super_admin()) { 
    redirect('index.php?page=beranda'); 
}

$tab = $_GET['tab'] ?? 'overview';
$allowed_tabs = ['overview', 'projects', 'roadmap', 'changelog', 'context', 'logs', 'database'];
if (!in_array($tab, $allowed_tabs)) $tab = 'overview';

// Ambil Data
$q_projects = mysqli_query($conn, "SELECT * FROM dev_projects");
$q_roadmap = mysqli_query($conn, "SELECT * FROM dev_roadmap ORDER BY id ASC");
$q_changelog = mysqli_query($conn, "SELECT * FROM dev_changelog ORDER BY id DESC LIMIT 20");

// Statistik Dasar
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_objek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM objek"))['c'];
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM laporan_aksi"))['c'];
$total_karbon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(serapan_karbon) as c FROM laporan_aksi"))['c'] ?? 0;

// Ambil Data Schema DB untuk Context Generator
$schema_md = "";
$tables = mysqli_query($conn, "SHOW TABLES");
while($t = mysqli_fetch_row($tables)) {
    $schema_md .= "- **$t[0]**: ";
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM $t[0]");
    $col_names = [];
    while($c = mysqli_fetch_assoc($cols)) { $col_names[] = $c['Field']; }
    $schema_md .= implode(', ', $col_names) . "\n";
}
?>

<!-- Desain Khusus Dev Hub (Dark Mode) -->
<style>
    .dev-hub-body { background-color: #0f172a; color: #f8fafc; min-height: 100vh; }
    .dev-card { background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; }
    .dev-tab-active { background: linear-gradient(90deg, #e11d48, #be123c); color: white; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.4); }
    .dev-tab-inactive { color: #94a3b8; }
    .dev-tab-inactive:hover { background-color: #334155; color: white; }
    .terminal-box { background-color: #000000; font-family: monospace; color: #22c55e; border: 1px solid #334155; border-radius: 0.5rem; padding: 1rem; height: 400px; overflow-y: auto; }
</style>

<div class="p-4 md:p-8 max-w-7xl mx-auto dev-hub-body">
    
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-rose-700 flex items-center justify-center shadow-lg">
                <i data-lucide="terminal" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-white">Command Center</h1>
                <p class="text-sm text-slate-400 mt-1">Pusat kendali developer, AI, & infrastruktur.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="refreshStats()" class="px-4 py-2 bg-slate-800 text-slate-200 border border-slate-700 font-bold rounded-xl hover:bg-slate-700 transition flex items-center gap-2 text-sm">
                <i data-lucide="refresh-cw" class="w-4 h-4" id="icon-refresh"></i> Refresh
            </button>
            <a href="index.php?page=dashboard" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition flex items-center gap-2 text-sm shadow-lg shadow-rose-900/50">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Tabs Navigasi (Scrollable) -->
    <div class="flex overflow-x-auto bg-slate-800 rounded-2xl p-1 mb-6 hide-scrollbar border border-slate-700">
        <?php 
        $tabs = [
            'overview' => ['icon' => 'layout-dashboard', 'label' => 'Overview'],
            'projects' => ['icon' => 'layers', 'label' => 'Projects'],
            'roadmap' => ['icon' => 'kanban-square', 'label' => 'Roadmap'],
            'changelog' => ['icon' => 'git-commit', 'label' => 'Changelog'],
            'context' => ['icon' => 'bot', 'label' => 'AI Context'],
            'logs' => ['icon' => 'terminal-square', 'label' => 'Logs'],
            'database' => ['icon' => 'database', 'label' => 'Database'],
        ];
        foreach($tabs as $key => $t): 
            $active = $tab === $key;
        ?>
            <a href="index.php?page=dev_hub&tab=<?= $key ?>" class="flex-1 py-3 px-4 text-xs md:text-sm font-bold rounded-xl text-center transition whitespace-nowrap flex items-center justify-center gap-2 <?= $active ? 'dev-tab-active' : 'dev-tab-inactive' ?>">
                <i data-lucide="<?= $t['icon'] ?>" class="w-4 h-4"></i> <?= $t['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- KONTEN TAB -->
    <div class="space-y-6">
        
        <!-- 1. OVERVIEW -->
        <?php if($tab === 'overview'): ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dev-card p-5 relative overflow-hidden">
                <i data-lucide="users" class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-700 opacity-20"></i>
                <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Pengguna</div>
                <div class="text-3xl font-black text-white"><?= number_format($total_users) ?></div>
            </div>
            <div class="dev-card p-5 relative overflow-hidden">
                <i data-lucide="package" class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-700 opacity-20"></i>
                <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Objek</div>
                <div class="text-3xl font-black text-emerald-400"><?= number_format($total_objek) ?></div>
            </div>
            <div class="dev-card p-5 relative overflow-hidden">
                <i data-lucide="file-text" class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-700 opacity-20"></i>
                <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total Laporan</div>
                <div class="text-3xl font-black text-blue-400"><?= number_format($total_laporan) ?></div>
            </div>
            <div class="dev-card p-5 relative overflow-hidden">
                <i data-lucide="wind" class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-700 opacity-20"></i>
                <div class="text-xs font-bold text-slate-400 uppercase mb-1">Karbon (Kg)</div>
                <div class="text-3xl font-black text-amber-400"><?= number_format($total_karbon, 0) ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="dev-card p-6">
                <h2 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="server" class="w-5 h-5 text-rose-500"></i> Server Health</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-400">Disk Usage</span>
                            <span class="text-white font-bold" id="disk-stats">Memuat...</span>
                        </div>
                        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-500 rounded-full" id="disk-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-400">RAM Usage</span>
                            <span class="text-white font-bold" id="ram-stats">Memuat...</span>
                        </div>
                        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" id="ram-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-700">
                        <div class="text-xs text-slate-400">PHP Version: <span class="text-white font-bold block" id="php-ver">-</span></div>
                        <div class="text-xs text-slate-400">MariaDB: <span class="text-white font-bold block" id="db-ver">-</span></div>
                    </div>
                </div>
            </div>
            
            <div class="dev-card p-6">
                <h2 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="activity" class="w-5 h-5 text-emerald-500"></i> Quick Links</h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="index.php?page=dashboard_cms" class="p-3 bg-slate-800 rounded-xl border border-slate-700 hover:border-emerald-500 transition text-center">
                        <i data-lucide="layout-template" class="w-6 h-6 text-emerald-400 mx-auto mb-2"></i>
                        <div class="text-xs font-bold text-white">Page Builder</div>
                    </a>
                    <a href="index.php?page=dashboard_info" class="p-3 bg-slate-800 rounded-xl border border-slate-700 hover:border-rose-500 transition text-center">
                        <i data-lucide="megaphone" class="w-6 h-6 text-rose-400 mx-auto mb-2"></i>
                        <div class="text-xs font-bold text-white">Ruang Info</div>
                    </a>
                    <a href="index.php?page=kelola_user" class="p-3 bg-slate-800 rounded-xl border border-slate-700 hover:border-blue-500 transition text-center">
                        <i data-lucide="users" class="w-6 h-6 text-blue-400 mx-auto mb-2"></i>
                        <div class="text-xs font-bold text-white">Kelola User</div>
                    </a>
                    <a href="index.php?page=dev_hub&tab=context" class="p-3 bg-slate-800 rounded-xl border border-slate-700 hover:border-purple-500 transition text-center">
                        <i data-lucide="bot" class="w-6 h-6 text-purple-400 mx-auto mb-2"></i>
                        <div class="text-xs font-bold text-white">AI Context</div>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 2. PROJECTS -->
        <?php if($tab === 'projects'): ?>
        <div class="dev-card p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-white flex items-center gap-2"><i data-lucide="layers" class="w-5 h-5 text-rose-500"></i> Multi-Project Registry</h2>
                <button class="px-4 py-2 bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700 text-sm flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Project</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="text-[10px] uppercase text-slate-400 border-b border-slate-700">
                            <th class="pb-3 pr-4 font-bold">Project</th>
                            <th class="pb-3 pr-4 font-bold">Subdomain</th>
                            <th class="pb-3 pr-4 font-bold">Stack</th>
                            <th class="pb-3 pr-4 font-bold">Folder</th>
                            <th class="pb-3 pr-4 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        <?php while($pr = mysqli_fetch_assoc($q_projects)): ?>
                        <tr class="border-b border-slate-700/50 hover:bg-slate-800/50 transition">
                            <td class="py-3 pr-4 font-bold text-white"><?= e($pr['nama']) ?></td>
                            <td class="py-3 pr-4 text-slate-300"><?= e($pr['subdomain']) ?></td>
                            <td class="py-3 pr-4 text-slate-400"><?= e($pr['stack']) ?></td>
                            <td class="py-3 pr-4 text-slate-500 font-mono text-[10px] truncate max-w-[150px]"><?= e($pr['folder_path']) ?></td>
                            <td class="py-3 pr-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $pr['status'] == 'AKTIF' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' ?>">
                                    <?= e($pr['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3. ROADMAP (Kanban) -->
        <?php if($tab === 'roadmap'): ?>
        <div class="flex gap-4 overflow-x-auto pb-4">
            <?php 
            $columns = [
                'todo' => ['title' => 'To-Do', 'color' => 'bg-slate-500', 'bg' => 'bg-slate-800/50'],
                'progress' => ['title' => 'In Progress', 'color' => 'bg-amber-500', 'bg' => 'bg-amber-900/20'],
                'done' => ['title' => 'Done', 'color' => 'bg-emerald-500', 'bg' => 'bg-emerald-900/20']
            ];
            foreach($columns as $col_key => $col): 
            ?>
            <div class="flex-1 min-w-[250px] <?= $col['bg'] ?> rounded-2xl p-4 border border-slate-700">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-3 h-3 rounded-full <?= $col['color'] ?>"></div>
                    <h3 class="font-bold text-white text-sm"><?= $col['title'] ?></h3>
                </div>
                <div class="space-y-3">
                    <?php 
                    mysqli_data_seek($q_roadmap, 0); // Reset pointer
                    while($rm = mysqli_fetch_assoc($q_roadmap)): 
                        if($rm['status'] !== $col_key) continue;
                    ?>
                    <div class="dev-card p-3 cursor-grab hover:border-slate-500 transition">
                        <div class="text-xs font-bold text-white mb-1"><?= e($rm['judul']) ?></div>
                        <div class="text-[10px] text-slate-400 mb-2"><?= e($rm['deskripsi']) ?></div>
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-700 text-slate-300"><?= e($rm['prioritas']) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- 4. CHANGELOG -->
        <?php if($tab === 'changelog'): ?>
        <div class="dev-card p-6 max-w-3xl mx-auto">
            <h2 class="font-bold text-white mb-6 flex items-center gap-2"><i data-lucide="git-commit" class="w-5 h-5 text-rose-500"></i> Timeline Pembaruan</h2>
            <div class="relative border-l-2 border-slate-700 ml-3 space-y-8 pb-4">
                <?php while($cl = mysqli_fetch_assoc($q_changelog)): ?>
                <div class="ml-6 relative">
                    <div class="absolute -left-[31px] top-1 w-4 h-4 rounded-full bg-rose-500 border-4 border-slate-900 shadow"></div>
                    <div class="text-xs text-slate-400 mb-1"><?= date('d M Y, H:i', strtotime($cl['created_at'])) ?></div>
                    <div class="font-bold text-white text-sm mb-1">
                        <span class="text-[10px] bg-rose-500/20 text-rose-400 px-2 py-0.5 rounded mr-2 border border-rose-500/30"><?= e($cl['tipe']) ?></span>
                        <?= e($cl['judul']) ?>
                    </div>
                    <div class="text-[10px] text-slate-500">Versi <?= e($cl['versi']) ?></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 5. AI CONTEXT GENERATOR -->
        <?php if($tab === 'context'): ?>
        <div class="dev-card p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="font-bold text-white flex items-center gap-2"><i data-lucide="bot" class="w-5 h-5 text-purple-500"></i> AI Context Generator</h2>
                    <p class="text-xs text-slate-400 mt-1">Salin teks ini ke chat baru AI agar ia langsung paham struktur kode Anda.</p>
                </div>
                <button onclick="copyContext()" class="px-4 py-2 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 text-sm shadow-lg shadow-purple-900/50 flex items-center gap-2"><i data-lucide="copy" class="w-4 h-4"></i> Copy Prompt</button>
            </div>
            <?php
            $prompt = "## 🎯 ANCHOR SWEAPING v6.0 (Next-Gen Dev Hub)\n";
            $prompt .= "**Generated:** " . date('d M Y H:i') . "\n\n";
            $prompt .= "### 📌 Info Sistem\n";
            $prompt .= "- **Server:** cPanel / PHP " . phpversion() . "\n";
            $prompt .= "- **Database:** MariaDB (" . mysqli_get_server_info($conn) . ")\n";
            $prompt .= "- **Path:** /home/lsmw8652/public_html/sweaping.lsmkompas.id\n\n";
            $prompt .= "### 🗄️ Struktur Database (Schema)\n";
            $prompt .= $schema_md . "\n";
            $prompt .= "### 🛠️ Status Roadmap Terkini\n";
            mysqli_data_seek($q_roadmap, 0);
            while($r = mysqli_fetch_assoc($q_roadmap)) {
                $prompt .= "- [{$r['status']}] {$r['judul']} ({$r['prioritas']})\n";
            }
            $prompt .= "\n### 🚀 Tugas Selanjutnya\n";
            $prompt .= "Tolong bantu saya menyelesaikan roadmap yang berstatus 'progress' dan 'todo' di atas. Fokus pada penulisan kode PHP 8.3 Native dan UI Tailwind CSS yang Mobile-First.\n";
            ?>
            <textarea id="context-box" readonly class="w-full h-96 bg-black text-emerald-400 font-mono text-xs p-4 rounded-xl border border-slate-700 focus:outline-none"><?= htmlspecialchars($prompt) ?></textarea>
        </div>
        <?php endif; ?>

        <!-- 6. LOGS -->
        <?php if($tab === 'logs'): ?>
        <div class="dev-card p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-white flex items-center gap-2"><i data-lucide="terminal-square" class="w-5 h-5 text-blue-500"></i> System Logs (PHP Error)</h2>
                <button onclick="clearLog()" class="px-3 py-1.5 bg-slate-700 text-white text-xs font-bold rounded hover:bg-slate-600">Clear Log</button>
            </div>
            <?php
            $log_file = ini_get('error_log');
            $log_content = "";
            if ($log_file && file_exists($log_file)) {
                $log_content = @file_get_contents($log_file);
                // Ambil 5000 karakter terakhir agar tidak berat
                if (strlen($log_content) > 5000) { $log_content = substr($log_content, -5000); }
            } else {
                $log_content = "File log tidak ditemukan atau tidak dapat dibaca. Cek path di php.ini.";
            }
            ?>
            <div class="terminal-box"><?= htmlspecialchars($log_content) ?></div>
        </div>
        <?php endif; ?>

                <!-- 7. DATABASE -->
        <?php if($tab === 'database'): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Panel Kiri: Backup Manual -->
            <div class="dev-card p-6 text-center">
                <div class="w-20 h-20 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/30">
                    <i data-lucide="database" class="w-10 h-10 text-emerald-400"></i>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Backup Manual</h2>
                <p class="text-sm text-slate-400 mb-6">Unduh seluruh data (User, Laporan, Objek, CMS) dalam format .sql untuk keamanan.</p>
                <a href="index.php?aksi_dev=backup_db" class="w-full py-3.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-900/50 flex items-center justify-center gap-2 mb-3">
                    <i data-lucide="download" class="w-5 h-5"></i> Download Backup (.sql)
                </a>
            </div>

            <!-- Panel Kanan: Auto Backup (Telegram & Git) -->
            <div class="dev-card p-6">
                <div class="flex items-center gap-3 mb-4 border-b border-slate-700 pb-3">
                    <div class="w-10 h-10 bg-rose-500/20 rounded-xl flex items-center justify-center border border-rose-500/30">
                        <i data-lucide="bot" class="w-5 h-5 text-rose-400"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Auto-Backup (Cron Job)</h2>
                        <p class="text-xs text-slate-400">Kirim otomatis ke Telegram & Push ke GitHub.</p>
                    </div>
                </div>
                
                <div class="bg-slate-900 rounded-xl p-4 border border-slate-700 mb-4">
                    <div class="text-xs text-slate-400 mb-2 font-mono">Perintah Cron Job (cPanel):</div>
                    <code class="text-[10px] text-emerald-400 break-all select-all">php /home/lsmw8652/public_html/sweaping.lsmkompas.id/auto_backup.php</code>
                </div>
                
                <p class="text-xs text-slate-400 mb-4">Jalankan tes di bawah ini untuk memastikan script berjalan di server Anda (mengirim ke Telegram & Git).</p>
                
                <a href="auto_backup.php" target="_blank" class="w-full py-3 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition border border-slate-600 flex items-center justify-center gap-2">
                    <i data-lucide="play" class="w-4 h-4"></i> Test Auto-Backup Sekarang
                </a>
            </div>

        </div>
        <?php endif; ?>

    </div>
</div>

<script>
// === SCRIPT UNTUK AJAX SYSTEM STATS ===
function refreshStats() {
    const icon = document.getElementById('icon-refresh');
    if(icon) icon.classList.add('animate-spin');

    fetch('index.php?ajax=system_stats')
    .then(res => res.json())
    .then(data => {
        if(data.status === 'ok') {
            // Update Disk
            const diskBar = document.getElementById('disk-bar');
            const diskStats = document.getElementById('disk-stats');
            if(diskBar) diskBar.style.width = data.disk_persen + '%';
            if(diskStats) diskStats.innerText = data.disk_persen + '% (' + data.disk_free_gb + ' GB sisa)';
            
            // Update RAM
            const ramBar = document.getElementById('ram-bar');
            const ramStats = document.getElementById('ram-stats');
            if(ramBar && data.ram_persen !== 'N/A') {
                ramBar.style.width = data.ram_persen;
                ramStats.innerText = data.ram_persen;
            } else if(ramStats) {
                ramStats.innerText = 'N/A (Shared Hosting)';
            }

            // Update Vars
            const phpVer = document.getElementById('php-ver');
            const dbVer = document.getElementById('db-ver');
            if(phpVer) phpVer.innerText = data.php_version;
            if(dbVer) dbVer.innerText = data.mysql_version;
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        if(icon) icon.classList.remove('animate-spin');
    });
}

// === SCRIPT COPY CONTEXT ===
function copyContext() {
    var copyText = document.getElementById("context-box");
    if(!copyText) return;
    copyText.select();
    copyText.setSelectionRange(0, 99999); 
    navigator.clipboard.writeText(copyText.value).then(function() {
        alert("Prompt AI berhasil di-copy! Silakan paste di chat baru.");
    });
}

// Load stats on page load jika di tab overview
document.addEventListener('DOMContentLoaded', () => {
    if(window.location.href.includes('tab=overview') || !window.location.href.includes('tab=')) {
        refreshStats();
        // Auto refresh setiap 30 detik
        setInterval(refreshStats, 30000);
    }
});
</script>