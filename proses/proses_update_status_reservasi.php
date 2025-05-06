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
    header("Location: ../views/manage_reservasi.php?error=invalid_params");
    exit;
}

$id_reservasi = (int)$_GET['id'];
$status = $_GET['status'];

// Validasi status
$allowed_status = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
if (!in_array($status, $allowed_status)) {
    header("Location: ../views/manage_reservasi.php?error=invalid_status");
    exit;
}

// Ambil data reservasi
$query_reservasi = mysqli_query($conn, "SELECT * FROM tabel_reservasi WHERE id_reservasi = $id_reservasi");

if (mysqli_num_rows($query_reservasi) == 0) {
    header("Location: ../views/manage_reservasi.php?error=not_found");
    exit;
}

$reservasi = mysqli_fetch_assoc($query_reservasi);

// Periksa transisi status yang valid
$valid_transition = true;
switch ($status) {
    case 'confirmed':
        if ($reservasi['status'] != 'pending') {
            $valid_transition = false;
        }
        break;
    case 'checked_in':
        if ($reservasi['status'] != 'confirmed') {
            $valid_transition = false;
        }
        break;
    case 'checked_out':
        if ($reservasi['status'] != 'checked_in') {
            $valid_transition = false;
        }
        break;
    case 'cancelled':
        if (!in_array($reservasi['status'], ['pending', 'confirmed'])) {
            $valid_transition = false;
        }
        break;
}

if (!$valid_transition) {
    header("Location: ../views/manage_reservasi.php?error=invalid_transition");
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Update status reservasi
    mysqli_query($conn, "UPDATE tabel_reservasi SET status = '$status' WHERE id_reservasi = $id_reservasi");
    
    // Jika status menjadi cancelled, update status kamar menjadi tersedia
    if ($status == 'cancelled') {
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = " . $detail['id_kamar']);
        }
    }
    
    // Jika status menjadi checked_out, update status kamar menjadi tersedia
    if ($status == 'checked_out') {
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = " . $detail['id_kamar']);
        }
        
        // Update status pembayaran menjadi 'paid'
        mysqli_query($conn, "UPDATE tabel_pembayaran SET status_pembayaran = 'paid' WHERE id_reservasi = $id_reservasi");
    }
    
    // Catat log aktivitas
    $aktivitas = "Mengubah status reservasi #$id_reservasi dengan kode booking " . $reservasi['kode_booking'] . " menjadi " . ucfirst($status);
    $id_user = $_SESSION['id_user'];
    mysqli_query($conn, "
        INSERT INTO tabel_log (id_user, aktivitas, created_at)
        VALUES ($id_user, '$aktivitas', NOW())
    ");
    
    // Commit transaksi
    mysqli_commit($conn);
    
    header("Location: ../views/manage_reservasi.php?success=status_updated");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($conn);
    
    header("Location: ../views/manage_reservasi.php?error=update_failed");
    exit;
}