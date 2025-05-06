<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    header("Location: ../views/manage_kamar.php?error=invalid_params");
    exit;
}

$id_kamar = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validasi status
if (!in_array($status, ['tersedia', 'terpakai', 'perbaikan'])) {
    header("Location: ../views/manage_kamar.php?error=invalid_status");
    exit;
}

// Ambil data kamar
$query_kamar = mysqli_query($conn, "SELECT * FROM tabel_kamar WHERE id_kamar = $id_kamar");

if (mysqli_num_rows($query_kamar) == 0) {
    header("Location: ../views/manage_kamar.php?error=not_found");
    exit;
}

// Cek apakah kamar sedang digunakan dalam reservasi aktif
if ($status == 'tersedia' || $status == 'perbaikan') {
    $query_check = mysqli_query($conn, "
        SELECT r.id_reservasi 
        FROM tabel_reservasi r
        JOIN tabel_detail_reservasi dr ON r.id_reservasi = dr.id_reservasi
        WHERE dr.id_kamar = $id_kamar 
        AND r.status IN ('confirmed', 'checked_in')
        LIMIT 1
    ");
    
    if (mysqli_num_rows($query_check) > 0 && $status == 'perbaikan') {
        header("Location: ../views/manage_kamar.php?error=room_in_use");
        exit;
    }
}

// Update status kamar
$update_kamar = mysqli_query($conn, "
    UPDATE tabel_kamar 
    SET status = '$status' 
    WHERE id_kamar = $id_kamar
");

if (!$update_kamar) {
    header("Location: ../views/manage_kamar.php?error=update_failed");
    exit;
}

// Catat log aktivitas
$kamar = mysqli_fetch_assoc($query_kamar);
$status_text = ($status == 'tersedia') ? 'Tersedia' : (($status == 'terpakai') ? 'Terpakai' : 'Dalam Perbaikan');
$aktivitas = "Memperbarui status kamar #" . $kamar['nomor_kamar'] . " menjadi $status_text";
$id_user = $_SESSION['id_user'];
$log_query = mysqli_query($conn, "
    INSERT INTO tabel_log (id_user, aktivitas, created_at)
    VALUES ($id_user, '$aktivitas', NOW())
");

// Redirect kembali ke halaman manage kamar
header("Location: ../views/manage_kamar.php?success=status_updated");
exit;
?>