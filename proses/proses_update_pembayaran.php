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
    header("Location: ../views/dashboard.php?error=invalid_params");
    exit;
}

$id_pembayaran = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validasi status
if (!in_array($status, ['success', 'failed'])) {
    header("Location: ../views/dashboard.php?error=invalid_status");
    exit;
}

// Ambil data pembayaran
$query_pembayaran = mysqli_query($conn, "SELECT * FROM tabel_pembayaran WHERE id_pembayaran = $id_pembayaran");

if (mysqli_num_rows($query_pembayaran) == 0) {
    header("Location: ../views/dashboard.php?error=not_found");
    exit;
}

$pembayaran = mysqli_fetch_assoc($query_pembayaran);
$id_reservasi = $pembayaran['id_reservasi'];

// Update status pembayaran
$update_pembayaran = mysqli_query($conn, "
    UPDATE tabel_pembayaran 
    SET status_pembayaran = '$status' 
    WHERE id_pembayaran = $id_pembayaran
");

if (!$update_pembayaran) {
    header("Location: ../views/detail_reservasi.php?id=$id_reservasi&error=update_failed");
    exit;
}

// Jika status pembayaran berhasil (success), update status reservasi jadi confirmed
if ($status == 'success') {
    $query_reservasi = mysqli_query($conn, "SELECT status FROM tabel_reservasi WHERE id_reservasi = $id_reservasi");
    $reservasi = mysqli_fetch_assoc($query_reservasi);
    
    // Jika status reservasi masih pending, ubah menjadi confirmed
    if ($reservasi['status'] == 'pending') {
        $update_reservasi = mysqli_query($conn, "
            UPDATE tabel_reservasi 
            SET status = 'confirmed' 
            WHERE id_reservasi = $id_reservasi
        ");
        
        if (!$update_reservasi) {
            header("Location: ../views/detail_reservasi.php?id=$id_reservasi&error=update_failed");
            exit;
        }
    }
}

// Catat log aktivitas
$aktivitas = "Memperbarui status pembayaran #$id_pembayaran menjadi " . ($status == 'success' ? 'Berhasil' : 'Gagal');
$id_user = $_SESSION['id_user'];
$log_query = mysqli_query($conn, "
    INSERT INTO tabel_log (id_user, aktivitas, created_at)
    VALUES ($id_user, '$aktivitas', NOW())
");

// Redirect kembali ke halaman detail reservasi
header("Location: ../views/detail_reservasi.php?id=$id_reservasi&success=payment_updated");
exit;
?>