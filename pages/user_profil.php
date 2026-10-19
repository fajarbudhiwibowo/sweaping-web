<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$profil_id = (int)($_GET['id'] ?? $_SESSION['user_id']);
$my_id = (int)$_SESSION['user_id'];
$is_my_profile = ($profil_id === $my_id);
$is_admin_view = is_admin();

// 1. Ambil data user
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $profil_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) { echo "<div class='pt-32 text-center text-slate-500'>User tidak ditemukan.</div>"; exit; }

// 2. Ambil data Organisasi (jika Institusi)
$org = null;
$team_members = [];
if ($user['tipe_akun'] === 'institusi') {
    $stmt_org = mysqli_prepare($conn, "SELECT * FROM organisasi WHERE pic_user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_org, 'i', $profil_id);
    mysqli_stmt_execute($stmt_org);
    $org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_org));
    mysqli_stmt_close($stmt_org);

    if ($org) {
        $stmt_tim = mysqli_prepare($conn, "SELECT u.id, u.nama_lengkap, u.avatar, oa.role FROM organisasi_anggota oa JOIN users u ON oa.user_id = u.id WHERE oa.organisasi_id = ?");
        mysqli_stmt_bind_param($stmt_tim, 'i', $org['id']);
        mysqli_stmt_execute($stmt_tim);
        $res_tim = mysqli_stmt_get_result($stmt_tim);
        while($t = mysqli_fetch_assoc($res_tim)) $team_members[] = $t;
        mysqli_stmt_close($stmt_tim);
    }
}

$is_member = false;
$is_pending = false;
if ($user['tipe_akun'] === 'institusi' && $org) {
    $stmt_m = mysqli_prepare($conn, "SELECT 1 FROM organisasi_anggota WHERE organisasi_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt_m, 'ii', $org['id'], $my_id);
    mysqli_stmt_execute($stmt_m);
    mysqli_stmt_store_result($stmt_m);
    if (mysqli_stmt_num_rows($stmt_m) > 0) $is_member = true;
    mysqli_stmt_close($stmt_m);

    if (!$is_member) {
        $stmt_p = mysqli_prepare($conn, "SELECT 1 FROM organisasi_permintaan WHERE organisasi_id = ? AND user_id = ? AND status = 'pending'");
        mysqli_stmt_bind_param($stmt_p, 'ii', $org['id'], $my_id);
        mysqli_stmt_execute($stmt_p);
        mysqli_stmt_store_result($stmt_p);
        if (mysqli_stmt_num_rows($stmt_p) > 0) $is_pending = true;
        mysqli_stmt_close($stmt_p);
    }
}

// 3. Statistik
$total_objek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM objek WHERE user_id = $profil_id AND deleted_at IS NULL"))['c'];
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM laporan_aksi WHERE user_id = $profil_id AND deleted_at IS NULL"))['c'];
$total_karbon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(serapan_karbon),0) as c FROM laporan_aksi WHERE user_id = $profil_id AND deleted_at IS NULL"))['c'];

// 4. Data Objek
$q_objek = mysqli_query($conn, "SELECT * FROM objek WHERE user_id = $profil_id AND deleted_at IS NULL ORDER BY id DESC LIMIT 6");

// 5. Status Follow & Daftar Teman
$is_following = false;
$stmt_f = mysqli_prepare($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ? AND status = 'accepted'");
mysqli_stmt_bind_param($stmt_f, 'ii', $my_id, $profil_id);
mysqli_stmt_execute($stmt_f);
mysqli_stmt_store_result($stmt_f);
if (mysqli_stmt_num_rows($stmt_f) > 0) $is_following = true;
mysqli_stmt_close($stmt_f);

$friends = [];
$q_friends = mysqli_query($conn, "SELECT u.id, u.nama_lengkap, u.avatar FROM follows f JOIN users u ON f.follower_id = u.id WHERE f.following_id = $profil_id AND f.status = 'accepted' LIMIT 5");
while($fr = mysqli_fetch_assoc($q_friends)) $friends[] = $fr;

// 6. Ambil Data Event, Info, dan Organisasi yang diikuti
$q_events = mysqli_query($conn, "SELECT * FROM events WHERE user_id = $profil_id ORDER BY id DESC");
$q_info = mysqli_query($conn, "SELECT * FROM ruang_informasi ORDER BY id DESC LIMIT 5");
$q_orgs = mysqli_query($conn, "SELECT o.nama, o.logo FROM organisasi_anggota oa JOIN organisasi o ON oa.organisasi_id = o.id WHERE oa.user_id = $profil_id LIMIT 5");
?>

<main class="max-w-6xl mx-auto px-4 pt-28 pb-16">
    
        <!-- HEADER PROFIL (Desain Elegan & Modern) -->
    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-8">
        
        <!-- Banner / Cover -->
        <div class="h-48 md:h-64 relative bg-gradient-to-br <?= $user['tipe_akun'] === 'institusi' ? 'from-purple-700 via-purple-600 to-indigo-800' : 'from-emerald-600 via-emerald-500 to-teal-700' ?>">
            <?php if(!empty($user['cover_foto']) && file_exists('uploads/covers/' . $user['cover_foto'])): ?>
                <img src="uploads/covers/<?= e($user['cover_foto']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
            <?php endif; ?>
        </div>
        
        <!-- Area Info (Di bawah Banner) -->
        <div class="px-6 md:px-10 pb-8 relative">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6 -mt-16 md:-mt-20 mb-4">
                <!-- Avatar -->
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white bg-slate-200 flex items-center justify-center text-5xl font-black text-slate-500 shadow-2xl z-10 overflow-hidden ring-4 ring-white/50 flex-shrink-0">
                    <?php if(!empty($user['avatar']) && file_exists('uploads/avatar/' . $user['avatar'])): ?>
                        <img src="uploads/avatar/<?= e($user['avatar']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                
                <!-- Nama & Tombol -->
                <div class="flex-1 w-full text-center md:text-left mt-4 md:mt-24 flex flex-col md:flex-row justify-between items-center md:items-end gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-slate-900 flex items-center justify-center md:justify-start gap-2">
                            <?= e($user['tipe_akun'] === 'institusi' ? ($org['nama_pendek'] ?? $user['nama_lengkap']) : $user['nama_lengkap']) ?>
                            <?php if($user['role'] === 'super_admin' || $user['role'] === 'admin'): ?>
                                <i data-lucide="check-circle" class="w-5 h-5 md:w-6 md:h-6 text-blue-500" title="Admin Terverifikasi"></i>
                            <?php endif; ?>
                        </h1>
                        <?php if($user['tipe_akun'] === 'institusi' && !empty($org['nama'])): ?>
                            <p class="text-slate-500 font-medium text-sm mt-1"><?= e($org['nama']) ?></p>
                        <?php endif; ?>
                        
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mt-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold <?= $user['tipe_akun'] === 'institusi' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                <?= $user['tipe_akun'] === 'institusi' ? 'Institusi / Lembaga' : 'Individu / Relawan' ?>
                            </span>
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3"></i> <?= e($user['organisasi'] ?: 'Lokasi tidak diatur') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex gap-2 pb-2">
                        <?php if($is_my_profile || $is_admin_view): ?>
                            <a href="index.php?page=user_profil_edit&id=<?= $profil_id ?>" class="px-6 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition shadow-md flex items-center gap-2">
                                <i data-lucide="settings" class="w-4 h-4"></i> Edit Profil
                            </a>
                        <?php else: ?>
                            <?php if($user['tipe_akun'] === 'institusi'): ?>
                                <?php if($is_member): ?>
                                    <span class="px-6 py-2.5 bg-emerald-100 text-emerald-700 font-bold rounded-xl border border-emerald-200 flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> Anda Anggota Tim</span>
                                <?php elseif($is_pending): ?>
                                    <span id="btn_gabung_org" class="px-6 py-2.5 bg-amber-100 text-amber-700 font-bold rounded-xl border border-amber-200 flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4"></i> Menunggu Persetujuan</span>
                                <?php else: ?>
                                    <button onclick="gabungOrganisasi(<?= $org['id'] ?>)" id="btn_gabung_org" class="px-6 py-2.5 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition shadow-lg flex items-center gap-2"><i data-lucide="user-plus" class="w-4 h-4"></i> Gabung Organisasi</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button onclick="toggleFollow(<?= $profil_id ?>)" id="btn_follow" class="px-8 py-2.5 <?= $is_following ? 'bg-slate-100 text-slate-700' : 'bg-emerald-600 text-white' ?> font-bold rounded-xl transition shadow-md hover:scale-105">
                                    <?= $is_following ? 'Unfollow' : 'Follow' ?>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KONTEN UTAMA (Grid Layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- SIDEBAR KIRI -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Pencapaian</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center bg-emerald-50 rounded-xl p-3 border border-emerald-100">
                        <div class="text-2xl font-black text-emerald-600"><?= $total_objek ?></div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Objek Dipantau</div>
                    </div>
                    <div class="text-center bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <div class="text-2xl font-black text-blue-600"><?= $total_laporan ?></div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Laporan</div>
                    </div>
                    <div class="text-center bg-amber-50 rounded-xl p-3 border border-amber-100">
                        <div class="text-2xl font-black text-amber-600"><?= number_format($total_karbon, 0) ?></div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Kg CO₂e</div>
                    </div>
                    <div class="text-center bg-purple-50 rounded-xl p-3 border border-purple-100">
                        <div class="text-2xl font-black text-purple-600"><?= number_format($user['eco_points']) ?></div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">Eco-Points</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Organisasi Diikuti</h3>
                <?php if(mysqli_num_rows($q_orgs) > 0): ?>
                    <div class="space-y-3">
                        <?php while($o = mysqli_fetch_assoc($q_orgs)): ?>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
                                <?php if($o['logo']): ?><img src="uploads/avatar/<?= e($o['logo']) ?>" class="w-full h-full object-cover rounded-lg"><?php else: ?><i data-lucide="building" class="w-5 h-5 text-slate-400"></i><?php endif; ?>
                            </div>
                            <div class="font-bold text-sm text-slate-700"><?= e($o['nama']) ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-400 text-center">Belum mengikuti organisasi.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- KONTEN KANAN -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Tab Navigasi -->
            <div class="flex overflow-x-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-1 hide-scrollbar">
                <button onclick="switchTab('kerja')" id="tab-btn-kerja" class="tab-btn active flex-1 py-3 px-4 text-sm font-bold rounded-xl text-emerald-700 bg-emerald-50 transition whitespace-nowrap">Hasil Kerja</button>
                <button onclick="switchTab('tentang')" id="tab-btn-tentang" class="tab-btn flex-1 py-3 px-4 text-sm font-bold rounded-xl text-slate-500 hover:bg-slate-50 transition whitespace-nowrap">Tentang</button>
                <button onclick="switchTab('tim')" id="tab-btn-tim" class="tab-btn flex-1 py-3 px-4 text-sm font-bold rounded-xl text-slate-500 hover:bg-slate-50 transition whitespace-nowrap"><?= $user['tipe_akun'] === 'institusi' ? 'Tim Kami' : 'Pertemanan' ?></button>
                <button onclick="switchTab('info')" id="tab-btn-info" class="tab-btn flex-1 py-3 px-4 text-sm font-bold rounded-xl text-slate-500 hover:bg-slate-50 transition whitespace-nowrap">Ruang Informasi</button>
                <button onclick="switchTab('event')" id="tab-btn-event" class="tab-btn flex-1 py-3 px-4 text-sm font-bold rounded-xl text-slate-500 hover:bg-slate-50 transition whitespace-nowrap">Event & Aksi</button>
            </div>

            <!-- 1. TAB HASIL KERJA -->
            <div id="tab-content-kerja" class="tab-content block">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-slate-800">Objek Terbaru</h2>
                    <?php if($is_my_profile): ?><a href="index.php?page=objek_daftar" class="text-sm font-bold text-emerald-600 hover:underline">+ Tambah Objek</a><?php endif; ?>
                </div>
                <?php if(mysqli_num_rows($q_objek) > 0): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php while($o = mysqli_fetch_assoc($q_objek)): ?>
                            <a href="index.php?page=objek_profil&id=<?= $o['id'] ?>" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-emerald-300 transition block group">
                                <div class="text-xs font-mono text-slate-400 mb-1"><?= e($o['kode_objek']) ?></div>
                                <div class="font-bold text-slate-800 truncate group-hover:text-emerald-600"><?= e($o['nama_objek']) ?></div>
                                <div class="text-xs text-slate-500 truncate mt-1 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> <?= e($o['lokasi'] ?: 'Lokasi tidak spesifik') ?></div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-slate-100 text-slate-500">Belum ada objek yang didaftarkan.</div>
                <?php endif; ?>
            </div>

            <!-- 2. TAB TENTANG -->
            <div id="tab-content-tentang" class="tab-content hidden">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 md:p-8 space-y-6">
                    <h2 class="text-xl font-bold text-slate-800 border-b border-slate-100 pb-3">Profil Lengkap</h2>
                    <div>
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Bio Singkat</h3>
                        <p class="text-slate-700"><?= nl2br(e($user['bio_singkat'] ?: 'Belum ada bio.')) ?></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                        <div>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Kontak & Sosial Media</h3>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2 text-sm"><i data-lucide="mail" class="w-4 h-4 text-emerald-500"></i> <?= e($user['email']) ?></li>
                                <?php if($user['sosmed_instagram']): ?><li class="flex items-center gap-2 text-sm"><i data-lucide="instagram" class="w-4 h-4 text-emerald-500"></i> <a href="https://instagram.com/<?= e($user['sosmed_instagram']) ?>" target="_blank" class="text-blue-600 hover:underline">@<?= e($user['sosmed_instagram']) ?></a></li><?php endif; ?>
                                <?php if($user['sosmed_linkedin']): ?><li class="flex items-center gap-2 text-sm"><i data-lucide="linkedin" class="w-4 h-4 text-emerald-500"></i> <a href="<?= e($user['sosmed_linkedin']) ?>" target="_blank" class="text-blue-600 hover:underline">LinkedIn</a></li><?php endif; ?>
                                <?php if($user['sosmed_website']): ?><li class="flex items-center gap-2 text-sm"><i data-lucide="globe" class="w-4 h-4 text-emerald-500"></i> <a href="<?= e($user['sosmed_website']) ?>" target="_blank" class="text-blue-600 hover:underline">Website</a></li><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. TAB TIM / PERTEMANAN -->
            <div id="tab-content-tim" class="tab-content hidden">
                <?php if($user['tipe_akun'] === 'institusi'): ?>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-slate-800">Anggota Tim</h2>
                        <?php if($is_my_profile || $is_admin_view): ?>
                            <button onclick="alert('Fitur undang anggota akan diimplementasikan.')" class="text-sm bg-purple-100 text-purple-700 font-bold px-4 py-2 rounded-lg hover:bg-purple-200 transition">+ Undang Anggota</button>
                        <?php endif; ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach($team_members as $tm): ?>
                            <a href="index.php?page=user_profil&id=<?= $tm['id'] ?>" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 hover:shadow-md hover:border-purple-300 transition group">
                                <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-lg overflow-hidden">
                                    <?php if($tm['avatar']): ?><img src="uploads/avatar/<?= e($tm['avatar']) ?>" class="w-full h-full object-cover"><?php else: ?><?= strtoupper(substr($tm['nama_lengkap'], 0, 1)) ?><?php endif; ?>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 group-hover:text-purple-700 transition"><?= e($tm['nama_lengkap']) ?></div>
                                    <div class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded uppercase mt-1 inline-block"><?= e($tm['role']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <h2 class="text-xl font-bold text-slate-800 mb-4">Pertemanan</h2>
                    <?php if(!empty($friends)): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <?php foreach($friends as $f): ?>
                                <a href="index.php?page=user_profil&id=<?= $f['id'] ?>" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 hover:shadow-md transition">
                                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg overflow-hidden">
                                        <?php if($f['avatar']): ?><img src="uploads/avatar/<?= e($f['avatar']) ?>" class="w-full h-full object-cover"><?php else: ?><?= strtoupper(substr($f['nama_lengkap'], 0, 1)) ?><?php endif; ?>
                                    </div>
                                    <div class="font-bold text-slate-800"><?= e($f['nama_lengkap']) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-2xl border border-slate-100 text-slate-500 mb-6">Belum ada pertemanan.</div>
                    <?php endif; ?>

                    <?php if($is_my_profile): 
                        $q_req = mysqli_query($conn, "SELECT f.id as follow_id, u.nama_lengkap, u.avatar, u.id as user_id FROM follows f JOIN users u ON f.follower_id = u.id WHERE f.following_id = $my_id AND f.status = 'pending'");
                        if(mysqli_num_rows($q_req) > 0):
                    ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                            <h3 class="font-bold text-blue-800 mb-3 text-sm">Permintaan Pertemanan Masuk</h3>
                            <div class="space-y-3">
                                <?php while($r = mysqli_fetch_assoc($q_req)): ?>
                                <div class="flex items-center justify-between bg-white p-3 rounded-xl shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600"><?= strtoupper(substr($r['nama_lengkap'], 0, 1)) ?></div>
                                        <div class="font-bold text-slate-800"><?= e($r['nama_lengkap']) ?></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="respondFollow(<?= $r['follow_id'] ?>, 'accepted')" class="px-3 py-1.5 bg-emerald-500 text-white font-bold rounded-lg text-xs hover:bg-emerald-600">Terima</button>
                                        <button onclick="respondFollow(<?= $r['follow_id'] ?>, 'rejected')" class="px-3 py-1.5 bg-rose-100 text-rose-600 font-bold rounded-lg text-xs hover:bg-rose-200">Tolak</button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endif; endif; ?>
                <?php endif; ?>
            </div>

            <!-- 4. TAB RUANG INFORMASI -->
            <div id="tab-content-info" class="tab-content hidden">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Informasi Terbaru</h2>
                <?php if(mysqli_num_rows($q_info) > 0): ?>
                    <div class="space-y-4">
                        <?php while($info = mysqli_fetch_assoc($q_info)): ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                            <h3 class="font-bold text-blue-900 text-lg mb-2"><?= e($info['judul']) ?></h3>
                            <p class="text-blue-800 text-sm mb-3"><?= nl2br(e($info['konten'])) ?></p>
                            <?php if($info['link']): ?>
                                <a href="<?= e($info['link']) ?>" target="_blank" class="inline-block bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-blue-700">Buka Link</a>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-slate-100 text-slate-500">Belum ada informasi dari Admin.</div>
                <?php endif; ?>
            </div>

            <!-- 5. TAB EVENT & AKSI -->
            <div id="tab-content-event" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-slate-800">Event & Rencana Aksi</h2>
                    <?php if($is_my_profile): ?>
                        <button onclick="document.getElementById('modal_event').classList.remove('hidden'); document.getElementById('modal_event').classList.add('flex');" class="text-sm bg-emerald-100 text-emerald-700 font-bold px-4 py-2 rounded-lg hover:bg-emerald-200 transition">+ Buat Event</button>
                    <?php endif; ?>
                </div>
                
                <?php if(mysqli_num_rows($q_events) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php while($ev = mysqli_fetch_assoc($q_events)): ?>
                        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition relative group overflow-hidden">
                            
                            <!-- Tombol Edit & Hapus -->
                            <?php if($is_my_profile): ?>
                            <div class="absolute top-3 right-3 flex gap-2 z-10 opacity-0 group-hover:opacity-100 transition">
                                <button onclick='editEvent(<?= htmlspecialchars(json_encode($ev), ENT_QUOTES, "UTF-8") ?>)' class="p-1.5 bg-white/90 text-blue-600 rounded-lg shadow hover:bg-blue-50"><i data-lucide="edit" class="w-4 h-4"></i></button>
                                <form method="POST" onsubmit="return confirm('Hapus event ini?');" class="inline">
                                    <input type="hidden" name="aksi_hapus_event" value="1">
                                    <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
                                    <button type="submit" class="p-1.5 bg-white/90 text-rose-600 rounded-lg shadow hover:bg-rose-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                            <?php endif; ?>

                            <a href="index.php?page=event_detail&id=<?= $ev['id'] ?>" class="block">
                                <?php if($ev['gambar']): ?>
                                    <img src="uploads/events/<?= e($ev['gambar']) ?>" class="w-full h-40 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-24 bg-gradient-to-r from-emerald-400 to-teal-500 flex items-center justify-center">
                                        <i data-lucide="calendar" class="w-10 h-10 text-white/50"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="p-5">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded">
                                            <?= date('d M Y', strtotime($ev['tanggal_mulai'])) ?>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase"><i data-lucide="<?= $ev['visibility'] == 'publik' ? 'globe' : ($ev['visibility'] == 'teman' ? 'users' : 'lock') ?>" class="w-3 h-3 inline"></i> <?= e($ev['visibility']) ?></span>
                                    </div>
                                    <h3 class="font-bold text-slate-800 text-lg group-hover:text-emerald-600 transition"><?= e($ev['judul']) ?></h3>
                                    <p class="text-xs text-slate-500 line-clamp-2 mt-1"><?= e($ev['deskripsi']) ?></p>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-slate-100 text-slate-500">Belum ada event yang dibuat.</div>
                <?php endif; ?>
            </div>

        </div> <!-- End Konten Kanan -->
    </div>
</main>

<!-- MODAL EVENT -->
<div id="modal_event" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="bg-emerald-600 p-6 text-white flex justify-between items-center sticky top-0 z-10">
            <h3 class="text-xl font-bold" id="modal_event_title">Buat Rencana Aksi / Event</h3>
            <button type="button" onclick="document.getElementById('modal_event').classList.add('hidden')" class="text-white hover:text-rose-200"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form action="index.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="aksi_tambah_event" id="ev_aksi" value="1">
            <input type="hidden" name="event_id" id="ev_id" value="0">
            <input type="hidden" name="gambar_lama" id="ev_gambar_lama" value="">
            
            <div>
                <label class="block text-sm font-bold mb-1">Judul Kegiatan</label>
                <input type="text" name="judul" id="ev_judul" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" id="ev_deskripsi" rows="3" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="ev_tanggal" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Lokasi</label>
                    <input type="text" name="lokasi" id="ev_lokasi" required class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Link Pendaftaran / Info (Opsional)</label>
                <input type="url" name="link" id="ev_link" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:border-emerald-500 focus:outline-none" placeholder="https://...">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Privasi Event</label>
                    <select name="visibility" id="ev_visibility" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl bg-white">
                        <option value="publik">Publik</option>
                        <option value="teman">Hanya Teman</option>
                        <option value="privat">Privat (Hanya Saya)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Gambar Poster</label>
                    <input type="file" name="gambar" accept="image/*" class="w-full text-sm pt-2">
                </div>
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition mt-4">Simpan Rencana Aksi</button>
        </form>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active', 'text-emerald-700', 'bg-emerald-50');
        el.classList.add('text-slate-500');
    });
    document.getElementById('tab-content-' + tabId).classList.remove('hidden');
    const btn = document.getElementById('tab-btn-' + tabId);
    btn.classList.remove('text-slate-500');
    btn.classList.add('active', 'text-emerald-700', 'bg-emerald-50');
}

function toggleFollow(targetId) {
    const btn = event.target.closest('button');
    const isCurrentlyFollowing = btn.innerText.trim() === 'Unfollow';
    const action = isCurrentlyFollowing ? 'unfollow' : 'follow';
    
    btn.innerText = 'Memproses...';
    btn.disabled = true;

    fetch('index.php?page=aksi_follow', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `target_id=${targetId}&action=${action}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            if (data.action === 'follow') {
                btn.innerText = 'Unfollow';
                btn.className = btn.className.replace('bg-emerald-500', 'bg-slate-100').replace('text-white', 'text-slate-700').replace('hover:bg-emerald-700', 'hover:bg-slate-200');
            } else {
                btn.innerText = 'Follow';
                btn.className = btn.className.replace('bg-slate-100', 'bg-emerald-500').replace('text-slate-700', 'text-white').replace('hover:bg-slate-200', 'hover:bg-emerald-700');
            }
            setTimeout(() => location.reload(), 500);
        }
        btn.disabled = false;
    })
    .catch(err => {
        console.error(err);
        btn.innerText = isCurrentlyFollowing ? 'Unfollow' : 'Follow';
        btn.disabled = false;
    });
}

function gabungOrganisasi(orgId) {
    if(!orgId) return alert('Organisasi tidak valid.');
    if(confirm('Apakah Anda yakin ingin mengirim permintaan bergabung ke organisasi ini?')) {
        fetch('index.php?page=aksi_gabung_org', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `organisasi_id=${orgId}`
        }).then(res => res.json()).then(data => {
            if(data.status === 'ok') {
                const btn = document.getElementById('btn_gabung_org');
                if(btn) {
                    btn.outerHTML = `<span class="px-6 py-2.5 bg-amber-100 text-amber-700 font-bold rounded-xl border border-amber-200 flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4"></i> Menunggu Persetujuan</span>`;
                    lucide.createIcons();
                }
            } else {
                alert(data.message);
            }
        });
    }
}

function respondFollow(followId, action) {
    fetch('index.php?page=aksi_respond_follow', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `follow_id=${followId}&action=${action}`
    }).then(res => res.json()).then(data => {
        if(data.status === 'ok') location.reload();
    });
}

function respondOrg(reqId, action) {
    fetch('index.php?page=aksi_respond_org', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `req_id=${reqId}&action=${action}`
    }).then(res => res.json()).then(data => {
        if(data.status === 'ok') location.reload();
    });
}

// Fungsi untuk menangani klik Edit Event (mengisi form modal)
function editEvent(data) {
    document.getElementById('modal_event_title').innerText = 'Edit Rencana Aksi / Event';
    document.getElementById('ev_aksi').value = '1'; // Aksi tetap sama, backend membedakan dari ID
    document.getElementById('ev_id').value = data.id;
    document.getElementById('ev_judul').value = data.judul || '';
    document.getElementById('ev_deskripsi').value = data.deskripsi || '';
    document.getElementById('ev_tanggal').value = data.tanggal_mulai || '';
    document.getElementById('ev_lokasi').value = data.lokasi || '';
    document.getElementById('ev_link').value = data.link || '';
    document.getElementById('ev_visibility').value = data.visibility || 'publik';
    document.getElementById('ev_gambar_lama').value = data.gambar || '';
    
    document.getElementById('modal_event').classList.remove('hidden');
    document.getElementById('modal_event').classList.add('flex');
}
</script>