
// ==============================================================
// 5A. HANDLE DAFTAR OBJEK BARU (LENGKAP)
// ==============================================================
if (isset($_POST['aksi_daftar_objek'])) {
    csrf_check();
    if (!is_logged_in()) { header("Location: index.php?page=login"); exit; }
    $uid = (int)$_SESSION['user_id'];

    $jenis_objek = $_POST['jenis_objek'] ?? '';
    if (!in_array($jenis_objek, ['pohon','sungai','taman','komposting'], true)) {
        $_SESSION['notif'] = ['tipe'=>'gagal','pesan'=>'Jenis objek tidak valid.'];
        header("Location: index.php?page=objek_daftar"); exit;
    }

    $nama_objek = trim($_POST['nama_objek'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $koordinat = trim($_POST['koordinat'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $visibility = trim($_POST['visibility'] ?? 'publik');
    if (!in_array($visibility, ['publik','teman','privat'], true)) $visibility = 'publik';

    // Field Umum (Pohon, Sungai, Taman, Kompos)
    $nama_latin = trim($_POST['nama_latin'] ?? '') ?: null;
    $famili = trim($_POST['famili'] ?? '') ?: null;
    $asal_daerah = trim($_POST['asal_daerah'] ?? '') ?: null;
    $nama_daerah_lain = trim($_POST['nama_daerah_lain'] ?? '') ?: null;
    $status_pohon = trim($_POST['status_pohon'] ?? '') ?: null;
    $kategori_ias = trim($_POST['kategori_ias'] ?? '') ?: null;
    $potensi_pemanfaatan = trim($_POST['potensi_pemanfaatan'] ?? '') ?: null;
    $tanggal_tanam = trim($_POST['tanggal_tanam'] ?? '') ?: null;
    $alasan_penanaman = trim($_POST['alasan_penanaman'] ?? '') ?: null;
    $penyelenggara = trim($_POST['penyelenggara'] ?? '') ?: null;
    $pelaksana = trim($_POST['pelaksana'] ?? '') ?: null;
    $sumber_dana = trim($_POST['sumber_dana'] ?? '') ?: null;
    $jenis_bibit = trim($_POST['jenis_bibit'] ?? '') ?: null;
    $asal_bibit = trim($_POST['asal_bibit'] ?? '') ?: null;

    $karakteristik_json = !empty($_POST['karakteristik']) && is_array($_POST['karakteristik']) ? json_encode(array_values($_POST['karakteristik'])) : null;
    $manfaat_json = !empty($_POST['manfaat']) && is_array($_POST['manfaat']) ? json_encode(array_values($_POST['manfaat'])) : null;

    // Field Sungai
    $nama_lain_sungai = trim($_POST['nama_lain_sungai'] ?? '') ?: null;
    $sumber_mata_air = trim($_POST['sumber_mata_air'] ?? '') ?: null;
    $panjang_sungai = trim($_POST['panjang_sungai'] ?? ''); $panjang_sungai = $panjang_sungai === '' ? null : (float)$panjang_sungai;
    $lebar_rata_rata = trim($_POST['lebar_rata_rata'] ?? ''); $lebar_rata_rata = $lebar_rata_rata === '' ? null : (float)$lebar_rata_rata;
    $kedalaman_rata_rata = trim($_POST['kedalaman_rata_rata'] ?? ''); $kedalaman_rata_rata = $kedalaman_rata_rata === '' ? null : (float)$kedalaman_rata_rata;
    $daerah_hulu = trim($_POST['daerah_hulu'] ?? '') ?: null;
    $daerah_hilir = trim($_POST['daerah_hilir'] ?? '') ?: null;
    $muara = trim($_POST['muara'] ?? '') ?: null;
    $jenis_sungai = trim($_POST['jenis_sungai'] ?? '') ?: null;
    $dasar_sungai_json = !empty($_POST['dasar_sungai']) && is_array($_POST['dasar_sungai']) ? json_encode(array_values($_POST['dasar_sungai'])) : null;
    $pemanfaatan_sungai_json = !empty($_POST['pemanfaatan_sungai']) && is_array($_POST['pemanfaatan_sungai']) ? json_encode(array_values($_POST['pemanfaatan_sungai'])) : null;
    $ada_vegetasi = isset($_POST['ada_vegetasi']) && $_POST['ada_vegetasi'] !== '' ? (int)$_POST['ada_vegetasi'] : null;
    $ada_sempadan = isset($_POST['ada_sempadan']) && $_POST['ada_sempadan'] !== '' ? (int)$_POST['ada_sempadan'] : null;

    // Field Taman
    $taman_pengelola = trim($_POST['taman_pengelola'] ?? '') ?: null;
    $taman_luas_area = trim($_POST['taman_luas_area'] ?? ''); $taman_luas_area = $taman_luas_area === '' ? null : (float)$taman_luas_area;
    $taman_tahun_pembangunan = trim($_POST['taman_tahun_pembangunan'] ?? ''); $taman_tahun_pembangunan = $taman_tahun_pembangunan === '' ? null : (int)$taman_tahun_pembangunan;
    $taman_tahun_revitalisasi = trim($_POST['taman_tahun_revitalisasi'] ?? ''); $taman_tahun_revitalisasi = $taman_tahun_revitalisasi === '' ? null : (int)$taman_tahun_revitalisasi;
    $taman_status_kepemilikan = trim($_POST['taman_status_kepemilikan'] ?? '') ?: null;
    $taman_vegetasi_pohon = trim($_POST['taman_vegetasi_pohon'] ?? '') ?: null;
    $taman_vegetasi_tanaman = trim($_POST['taman_vegetasi_tanaman'] ?? '') ?: null;
    $taman_intensitas = trim($_POST['taman_intensitas'] ?? '') ?: null;
    $taman_kategori_json = !empty($_POST['taman_kategori_tanaman']) && is_array($_POST['taman_kategori_tanaman']) ? json_encode(array_values($_POST['taman_kategori_tanaman'])) : null;
    $taman_pemanfaatan_json = !empty($_POST['taman_pemanfaatan']) && is_array($_POST['taman_pemanfaatan']) ? json_encode(array_values($_POST['taman_pemanfaatan'])) : null;
    $taman_fasilitas_json = !empty($_POST['taman_fasilitas']) && is_array($_POST['taman_fasilitas']) ? json_encode(array_values($_POST['taman_fasilitas'])) : null;

    // Field Komposting
    $kompos_pengelola = trim($_POST['kompos_pengelola'] ?? '') ?: null;
    $kompos_metode = trim($_POST['kompos_metode'] ?? '') ?: null;
    $kompos_kapasitas = trim($_POST['kompos_kapasitas'] ?? ''); $kompos_kapasitas = $kompos_kapasitas === '' ? null : (float)$kompos_kapasitas;
    $kompos_luas_area = trim($_POST['kompos_luas_area'] ?? ''); $kompos_luas_area = $kompos_luas_area === '' ? null : (float)$kompos_luas_area;
    $kompos_tahun_mulai = trim($_POST['kompos_tahun_mulai'] ?? ''); $kompos_tahun_mulai = $kompos_tahun_mulai === '' ? null : (int)$kompos_tahun_mulai;
    $kompos_jumlah_komposter = trim($_POST['kompos_jumlah_komposter'] ?? ''); $kompos_jumlah_komposter = $kompos_jumlah_komposter === '' ? null : (int)$kompos_jumlah_komposter;
    $kompos_kelompok = trim($_POST['kompos_kelompok'] ?? '') ?: null;
    $kompos_bahan_json = !empty($_POST['kompos_bahan_organik_arr']) && is_array($_POST['kompos_bahan_organik_arr']) ? json_encode(array_values($_POST['kompos_bahan_organik_arr'])) : null;

    // PIC Monev
    $pic_monev_nama = trim($_POST['pic_monev_nama'] ?? '') ?: null;
    $pic_monev_peran = trim($_POST['pic_monev_peran'] ?? '') ?: null;

    // Generate Kode Objek
    $prefix_map = ['pohon'=>'TRB','sungai'=>'SNG','taman'=>'TMN','komposting'=>'KMP'];
    $prefix = $prefix_map[$jenis_objek];
    $tahun = date('Y');
    $q_last = mysqli_query($conn, "SELECT kode_objek FROM objek WHERE kode_objek LIKE '$prefix-$tahun-%' ORDER BY id DESC LIMIT 1");
    $last = mysqli_fetch_assoc($q_last);
    $next_num = $last ? ((int)substr($last['kode_objek'], -5) + 1) : 1;
    $kode_objek = $prefix . '-' . $tahun . '-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);

    // Upload Foto
    $nama_foto = null;
    $telegram_file_id = null;
    $telegram_message_id = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        if ($file['size'] <= MAX_FILE_SIZE) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
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
                        $nama_foto = 'objek_' . $kode_objek . '_' . date('Ymd_His') . '.jpg';
                        imagejpeg($new_img, UPLOAD_DIR . $nama_foto, JPEG_QUALITY);
                        imagedestroy($img); imagedestroy($new_img);
                    }
                } else {
                    $nama_foto = 'objek_' . $kode_objek . '_' . date('Ymd_His') . '.jpg';
                    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama_foto);
                }
            }
        }
    }

    if ($nama_foto && file_exists(UPLOAD_DIR . $nama_foto)) {
        $caption = "📷 OBJEK " . strtoupper($jenis_objek) . " - " . $nama_objek . "\n📍 " . $lokasi;
        $tg_result = upload_ke_telegram(UPLOAD_DIR . $nama_foto, $jenis_objek, $caption);
        if ($tg_result) {
            $telegram_file_id = $tg_result['file_id'];
            $telegram_message_id = $tg_result['message_id'];
            @unlink(UPLOAD_DIR . $nama_foto);
            $nama_foto = null;
        }
    }

    $insert_data = [
        'kode_objek' => $kode_objek, 'jenis_objek' => $jenis_objek, 'nama_objek' => $nama_objek,
        'lokasi' => $lokasi, 'koordinat' => $koordinat, 'user_id' => $uid,
        'foto_pertama' => $nama_foto, 'catatan' => $catatan, 'telegram_file_id' => $telegram_file_id,
        'telegram_message_id' => $telegram_message_id, 'visibility' => $visibility,
        'nama_latin' => $nama_latin, 'famili' => $famili, 'asal_daerah' => $asal_daerah, 'nama_daerah_lain' => $nama_daerah_lain,
        'status_pohon' => $status_pohon, 'kategori_ias' => $kategori_ias, 'potensi_pemanfaatan' => $potensi_pemanfaatan,
        'karakteristik' => $karakteristik_json, 'manfaat' => $manfaat_json, 'tanggal_tanam' => $tanggal_tanam,
        'alasan_penanaman' => $alasan_penanaman, 'penyelenggara' => $penyelenggara, 'pelaksana' => $pelaksana,
        'sumber_dana' => $sumber_dana, 'jenis_bibit' => $jenis_bibit, 'asal_bibit' => $asal_bibit,
        'pic_monev_nama' => $pic_monev_nama, 'pic_monev_peran' => $pic_monev_peran,
        'nama_lain_sungai' => $nama_lain_sungai, 'sumber_mata_air' => $sumber_mata_air, 'panjang_sungai' => $panjang_sungai,
        'lebar_rata_rata' => $lebar_rata_rata, 'kedalaman_rata_rata' => $kedalaman_rata_rata, 'daerah_hulu' => $daerah_hulu,
        'daerah_hilir' => $daerah_hilir, 'muara' => $muara, 'jenis_sungai' => $jenis_sungai,
        'dasar_sungai' => $dasar_sungai_json, 'pemanfaatan_sungai' => $pemanfaatan_sungai_json, 'ada_vegetasi' => $ada_vegetasi, 'ada_sempadan' => $ada_sempadan,
        'taman_pengelola' => $taman_pengelola, 'taman_luas_area' => $taman_luas_area, 'taman_tahun_pembangunan' => $taman_tahun_pembangunan,
        'taman_tahun_revitalisasi' => $taman_tahun_revitalisasi, 'taman_status_kepemilikan' => $taman_status_kepemilikan,
        'taman_vegetasi_pohon' => $taman_vegetasi_pohon, 'taman_vegetasi_tanaman' => $taman_vegetasi_tanaman,
        'taman_kategori_tanaman' => $taman_kategori_json, 'taman_pemanfaatan' => $taman_pemanfaatan_json,
        'taman_intensitas' => $taman_intensitas, 'taman_fasilitas' => $taman_fasilitas_json,
        'kompos_pengelola' => $kompos_pengelola, 'kompos_metode' => $kompos_metode, 'kompos_kapasitas' => $kompos_kapasitas,
        'kompos_luas_area' => $kompos_luas_area, 'kompos_tahun_mulai' => $kompos_tahun_mulai,
        'kompos_jumlah_komposter' => $kompos_jumlah_komposter, 'kompos_kelompok' => $kompos_kelompok, 'kompos_bahan_organik' => $kompos_bahan_json
    ];

    $cols = []; $placeholders = []; $types = ''; $vals = [];
    foreach ($insert_data as $col => $val) {
        $cols[] = $col; $placeholders[] = '?';
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
            $new_id = mysqli_insert_id($conn);
            audit_log($conn, 'daftar_objek', $new_id, "Kode: $kode_objek | Nama: $nama_objek");
            $_SESSION['notif'] = ['tipe' => 'sukses', 'pesan' => "Objek $kode_objek berhasil didaftarkan!"];
            header("Location: index.php?page=objek_profil&id=$new_id"); exit;
        } else {
            $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal menyimpan: ' . mysqli_stmt_error($stmt)];
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['notif'] = ['tipe' => 'gagal', 'pesan' => 'Gagal prepare: ' . mysqli_error($conn)];
    }
    header("Location: index.php?page=objek_daftar"); exit;
}
