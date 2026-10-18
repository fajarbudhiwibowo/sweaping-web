<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$uid = (int)$_SESSION['user_id'];
$error = '';
$sukses = '';

// Proses Buat Organisasi
if (isset($_POST['aksi_buat_org'])) {
    $nama_org = trim($_POST['nama_org'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kode_unik = strtoupper(str_replace(' ', '-', $nama_org)) . '-' . rand(10, 99);

    if ($nama_org === '') {
        $error = 'Nama organisasi wajib diisi.';
    } else {
        // Cek apakah user sudah punya organisasi
        $stmt_cek = mysqli_prepare($conn, "SELECT organisasi_id FROM organisasi_anggota WHERE user_id = ? AND role = 'pic'");
        mysqli_stmt_bind_param($stmt_cek, 'i', $uid);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = 'Anda sudah terdaftar sebagai PIC di organisasi lain.';
        } else {
            mysqli_stmt_close($stmt_cek);
            
            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, "INSERT INTO organisasi (nama, kode_unik, pic_user_id, deskripsi) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'ssis', $nama_org, $kode_unik, $uid, $deskripsi);
                mysqli_stmt_execute($stmt);
                $org_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                $stmt2 = mysqli_prepare($conn, "INSERT INTO organisasi_anggota (organisasi_id, user_id, role) VALUES (?, ?, 'pic')");
                mysqli_stmt_bind_param($stmt2, 'ii', $org_id, $uid);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                mysqli_commit($conn);
                $sukses = 'Organisasi berhasil dibuat!';
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Gagal membuat organisasi: ' . $e->getMessage();
            }
        }
    }
}

$q_org = mysqli_query($conn, "SELECT o.*, u.nama_lengkap as pic_nama, COUNT(oa.user_id) as total_anggota FROM organisasi o LEFT JOIN users u ON o.pic_user_id = u.id LEFT JOIN organisasi_anggota oa ON o.id = oa.organisasi_id GROUP BY o.id ORDER BY o.id DESC");
?>

<main class="max-w-6xl mx-auto px-4 pt-24 pb-16">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 flex items-center gap-3">
                <i data-lucide="building-2" class="w-8 h-8 text-emerald-600"></i> Organisasi
            </h1>
            <p class="text-slate-500 mt-2">Berkolaborasi bersama komunitas untuk aksi lingkungan yang lebih besar.</p>
        </div>
        <button onclick="document.getElementById('form-buat-org').classList.toggle('hidden')" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-lg hover:bg-emerald-700 transition">
            <i data-lucide="plus" class="w-5 h-5"></i> Buat Organisasi
        </button>
    </div>

    <?php if($error): ?><div class="bg-rose-50 text-rose-700 p-4 rounded-xl mb-6 font-bold border border-rose-200"><?= e($error) ?></div><?php endif; ?>
    <?php if($sukses): ?><div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl mb-6 font-bold border border-emerald-200"><?= e($sukses) ?></div><?php endif; ?>

    <div id="form-buat-org" class="hidden bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:p-8 mb-10">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Daftarkan Organisasi Baru</h2>
        <form method="POST" action="index.php?page=organisasi" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Organisasi / Komunitas</label>
                <input type="text" name="nama_org" required placeholder="Contoh: Karang Taruna RW 05" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Singkat</label>
                <textarea name="deskripsi" rows="3" placeholder="Visi misi atau fokus kegiatan organisasi..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>
            <button type="submit" name="aksi_buat_org" class="w-full py-3 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition">Simpan & Jadi PIC</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if(mysqli_num_rows($q_org) > 0): while($org = mysqli_fetch_assoc($q_org)): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-md transition">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4">
                    <i data-lucide="building" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-1"><?= e($org['nama']) ?></h3>
                <div class="text-xs font-mono text-slate-400 mb-3"><?= e($org['kode_unik']) ?></div>
                <p class="text-sm text-slate-500 line-clamp-2 mb-4 flex-1"><?= e($org['deskripsi'] ?: 'Tidak ada deskripsi.') ?></p>
                
                <div class="border-t border-slate-100 pt-4 flex items-center justify-between text-sm mb-4">
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <i data-lucide="users" class="w-4 h-4"></i> <span class="font-bold"><?= number_format($org['total_anggota']) ?></span> Anggota
                    </div>
                    <div class="text-xs text-slate-500">PIC: <b><?= e($org['pic_nama'] ?? '-') ?></b></div>
                </div>
                
                <!-- TOMBOL DASHBOARD -->
                <a href="index.php?page=organisasi_detail&id=<?= $org['id'] ?>" class="w-full py-2.5 bg-slate-100 text-slate-700 font-bold text-center rounded-xl hover:bg-emerald-50 hover:text-emerald-700 transition text-sm flex items-center justify-center gap-2">
                    Buka Dashboard <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        <?php endwhile; else: ?>
            <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                <p class="text-slate-500">Belum ada organisasi terdaftar.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
