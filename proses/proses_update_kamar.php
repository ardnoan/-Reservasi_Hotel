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

// Cek method request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $id_kamar = htmlspecialchars($_POST['id_kamar']);
    $nomor_kamar = htmlspecialchars($_POST['nomor_kamar']);
    $id_jenis = htmlspecialchars($_POST['id_jenis']);
    $lantai = htmlspecialchars($_POST['lantai']);
    $status = htmlspecialchars($_POST['status']);
    
    // Validasi data
    if (empty($id_kamar) || empty($nomor_kamar) || empty($id_jenis) || empty($lantai) || empty($status)) {
        header("Location: ../views/edit_kamar.php?id=$id_kamar&error=empty_fields");
        exit;
    }
    
    // Cek apakah nomor kamar sudah ada (kecuali kamar yang sedang diedit)
    $check_query = "SELECT * FROM tabel_kamar WHERE nomor_kamar = '$nomor_kamar' AND id_kamar != '$id_kamar'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        header("Location: ../views/edit_kamar.php?id=$id_kamar&error=nomor_exists");
        exit;
    }
    
    // Update data di database
    $query = "UPDATE tabel_kamar SET 
                nomor_kamar = '$nomor_kamar', 
                id_jenis = '$id_jenis', 
                lantai = '$lantai', 
                status = '$status'
                WHERE id_kamar = '$id_kamar'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../views/manage_kamar.php?success=updated");
        exit;
    } else {
        header("Location: ../views/edit_kamar.php?id=$id_kamar&error=failed");
        exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman kelola kamar
    header("Location: ../views/manage_kamar.php");
    exit;
}
?>