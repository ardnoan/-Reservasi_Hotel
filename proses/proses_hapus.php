<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

require '../koneksi.php';

// Validasi id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../views/dashboard.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah data ada
$check = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id = $id");
if (mysqli_num_rows($check) == 0) {
    header("Location: ../views/dashboard.php");
    exit;
}

// Hapus data dari database
if (mysqli_query($conn, "DELETE FROM mahasiswa WHERE id = $id")) {
    // Data berhasil dihapus
    header("Location: ../views/dashboard.php?status=success&action=delete");
    exit;
} else {
    // Error
    header("Location: ../views/dashboard.php?error=db");
    exit;
}