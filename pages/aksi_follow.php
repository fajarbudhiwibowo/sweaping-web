<?php
if (!is_logged_in()) { redirect('index.php?page=login'); }

$target_id = (int)($_POST['target_id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$my_id = (int)$_SESSION['user_id'];

if ($target_id <= 0 || $target_id === $my_id) {
    redirect('index.php?page=user_profil&id=' . $target_id);
}

if ($aksi === 'follow') {
    // Cek jika sudah pernah follow (rejected/pending)
    $stmt = mysqli_prepare($conn, "INSERT INTO follows (follower_id, following_id, status) VALUES (?, ?, 'pending') ON DUPLICATE KEY UPDATE status = IF(status = 'rejected', 'pending', status)");
    mysqli_stmt_bind_param($stmt, 'ii', $my_id, $target_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Kirim Notifikasi ke target
    $pesan = $_SESSION['nama'] . ' ingin mengikuti Anda.';
    $stmt_n = mysqli_prepare($conn, "INSERT INTO notifications (user_id, tipe, aktor_id, pesan) VALUES (?, 'follow_request', ?, ?)");
    mysqli_stmt_bind_param($stmt_n, 'iis', $target_id, $my_id, $pesan);
    mysqli_stmt_execute($stmt_n);
    mysqli_stmt_close($stmt_n);

} elseif ($aksi === 'unfollow') {
    $stmt = mysqli_prepare($conn, "DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $my_id, $target_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

redirect('index.php?page=user_profil&id=' . $target_id);
?>
