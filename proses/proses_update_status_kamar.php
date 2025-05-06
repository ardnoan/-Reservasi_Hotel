<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

// Cek apakah user adalah admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../views/dashboard.php");
    exit;
}

// Cek apakah ada parameter id dan status
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id_kamar = $_GET['id'];
    $status = $_GET['status'];
    
    // Validasi status
    $allowed_status = ['tersedia', 'terpakai', 'perbaikan'];
    if (!in_array($status, $allowed_status)) {
        header("Location: ../views/manage_kamar.php?error=invalid_status");
        exit;
    }
    
    // Jika status diubah menjadi 'tersedia' atau 'perbaikan', perlu cek reservasi aktif
    if ($status == 'tersedia' || $status == 'perbaikan') {
        $check_query = "SELECT * FROM tabel_detail_reservasi WHERE id_kamar = '$id_kamar' AND 
                      id_reservasi IN (SELECT id_reservasi FROM tabel_reservasi WHERE status = 'checked_in')";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0 && $status == 'perbaikan') {
            // Kamar masih digunakan dalam reservasi check-in
            header("Location: ../views/manage_kamar.php?error=room_in_use");
            exit;
        }
    }
    
    // Update status kamar
    $query = "UPDATE tabel_kamar SET status = '$status' WHERE id_kamar = '$id_kamar'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../views/manage_kamar.php?success=updated");
        exit;
    } else {
        header("Location: ../views/manage_kamar.php?error=failed");
        exit;
    }
} else {
    // Jika tidak ada parameter id atau status, redirect ke halaman kelola kamar
    header("Location: ../views/manage_kamar.php");
    exit;
}
?>