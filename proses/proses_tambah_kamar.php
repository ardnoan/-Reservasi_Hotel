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
    $nomor_kamar = htmlspecialchars($_POST['nomor_kamar']);
    $id_jenis = htmlspecialchars($_POST['id_jenis']);
    $lantai = htmlspecialchars($_POST['lantai']);
    $status = htmlspecialchars($_POST['status']);
    
    // Validasi data
    if (empty($nomor_kamar) || empty($id_jenis) || empty($lantai) || empty($status)) {
        header("Location: ../views/tambah_kamar.php?error=empty_fields");
        exit;
    }
    
    // Cek apakah nomor kamar sudah ada
    $check_query = "SELECT * FROM tabel_kamar WHERE nomor_kamar = '$nomor_kamar'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        header("Location: ../views/tambah_kamar.php?error=nomor_exists");
        exit;
    }
    
    // Insert data ke database
    $query = "INSERT INTO tabel_kamar (nomor_kamar, id_jenis, lantai, status) VALUES ('$nomor_kamar', '$id_jenis', '$lantai', '$status')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../views/manage_kamar.php?success=added");
        exit;
    } else {
        header("Location: ../views/tambah_kamar.php?error=failed");
        exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman tambah kamar
    header("Location: ../views/tambah_kamar.php");
    exit;
}
?>