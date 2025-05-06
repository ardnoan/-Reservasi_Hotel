<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../views/manage_reservasi.php?error=invalid_params");
    exit;
}

$id_reservasi = (int)$_GET['id'];

// Ambil data reservasi
$query_reservasi = mysqli_query($conn, "SELECT * FROM tabel_reservasi WHERE id_reservasi = $id_reservasi");

if (mysqli_num_rows($query_reservasi) == 0) {
    header("Location: ../views/manage_reservasi.php?error=not_found");
    exit;
}

$reservasi = mysqli_fetch_assoc($query_reservasi);

// Cek apakah reservasi sudah check-out atau cancelled
if (in_array($reservasi['status'], ['checked_out', 'cancelled'])) {
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Hapus detail pembayaran
        mysqli_query($conn, "DELETE FROM tabel_pembayaran WHERE id_reservasi = $id_reservasi");
        
        // Hapus detail reservasi
        mysqli_query($conn, "DELETE FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        
        // Hapus reservasi
        mysqli_query($conn, "DELETE FROM tabel_reservasi WHERE id_reservasi = $id_reservasi");
        
        // Catat log aktivitas
        $aktivitas = "Menghapus reservasi #$id_reservasi dengan kode booking " . $reservasi['kode_booking'];
        $id_user = $_SESSION['id_user'];
        mysqli_query($conn, "
            INSERT INTO tabel_log (id_user, aktivitas, created_at)
            VALUES ($id_user, '$aktivitas', NOW())
        ");
        
        // Commit transaksi
        mysqli_commit($conn);
        
        header("Location: ../views/manage_reservasi.php?success=deleted");
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($conn);
        
        header("Location: ../views/manage_reservasi.php?error=delete_failed");
        exit;
    }
} else {
    // Jika reservasi masih active (pending, confirmed, checked_in), update status menjadi cancelled
    
    // Ambil data detail reservasi
    $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Update status reservasi menjadi cancelled
        mysqli_query($conn, "UPDATE tabel_reservasi SET status = 'cancelled' WHERE id_reservasi = $id_reservasi");
        
        // Update status kamar menjadi tersedia
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = " . $detail['id_kamar']);
        }
        
        // Catat log aktivitas
        $aktivitas = "Membatalkan reservasi #$id_reservasi dengan kode booking " . $reservasi['kode_booking'];
        $id_user = $_SESSION['id_user'];
        mysqli_query($conn, "
            INSERT INTO tabel_log (id_user, aktivitas, created_at)
            VALUES ($id_user, '$aktivitas', NOW())
        ");
        
        // Commit transaksi
        mysqli_commit($conn);
        
        header("Location: ../views/manage_reservasi.php?success=cancelled");
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($conn);
        
        header("Location: ../views/manage_reservasi.php?error=cancel_failed");
        exit;
    }
}
?>