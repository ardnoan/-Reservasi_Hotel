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

// Cek apakah ada parameter id
if (isset($_GET['id'])) {
    $id_kamar = $_GET['id'];

    // Cek apakah kamar sedang digunakan dalam reservasi aktif
    $check_query = "SELECT * FROM tabel_detail_reservasi WHERE id_kamar = '$id_kamar' AND 
                  id_reservasi IN (SELECT id_reservasi FROM tabel_reservasi WHERE status IN ('pending', 'confirmed', 'checked_in'))";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Kamar masih digunakan dalam reservasi aktif
        header("Location: ../views/manage_kamar.php?error=cannot_delete");
        exit;
    }

    // Hapus data kamar dari database
    $query = "DELETE FROM tabel_kamar WHERE id_kamar = '$id_kamar'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../views/manage_kamar.php?success=deleted");
        exit;
    } else {
        header("Location: ../views/manage_kamar.php?error=failed");
        exit;
    }
} else {
    // Jika tidak ada parameter id, redirect ke halaman kelola kamar
    header("Location: ../views/manage_kamar.php");
    exit;
}
