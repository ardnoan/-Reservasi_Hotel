<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: ../views/dashboard.php?error=invalid");
    exit;
}

$id_reservasi = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validasi status
$valid_statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header("Location: ../views/dashboard.php?error=status");
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Update status reservasi
    $query_update = mysqli_query($conn, "UPDATE tabel_reservasi SET status = '$status', updated_at = NOW() WHERE id_reservasi = $id_reservasi");
    
    // Update status kamar jika diperlukan
    if ($status == 'cancelled') {
        // Jika dibatalkan, kembalikan status kamar menjadi tersedia
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            $id_kamar = $detail['id_kamar'];
            mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = $id_kamar");
        }
    } elseif ($status == 'checked_in') {
        // Jika check-in, update status kamar menjadi 'ditempati'
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            $id_kamar = $detail['id_kamar'];
            mysqli_query($conn, "UPDATE tabel_kamar SET status = 'ditempati' WHERE id_kamar = $id_kamar");
        }
    } elseif ($status == 'checked_out') {
        // Jika check-out, update status kamar menjadi 'tersedia'
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            $id_kamar = $detail['id_kamar'];
            mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = $id_kamar");
        }
        
        // Jika status pembayaran masih pending, update menjadi success
        mysqli_query($conn, "UPDATE tabel_pembayaran SET status_pembayaran = 'success', tanggal_pembayaran = NOW() WHERE id_reservasi = $id_reservasi AND status_pembayaran = 'pending'");
    }
    
    // Commit transaksi
    mysqli_commit($conn);
    
    // Redirect ke halaman sebelumnya dengan pesan sukses
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/dashboard.php';
    header("Location: $redirect?success=status_updated");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($conn);
    header("Location: ../views/dashboard.php?error=database");
    exit;
}