<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

require '../koneksi.php';

// Cek jika form kosong
if (empty($_POST['nama']) || empty($_POST['nim']) || empty($_POST['jurusan']) || empty($_POST['angkatan'])) {
    header("Location: ../views/tambah.php?error=empty");
    exit;
}

$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$nim = mysqli_real_escape_string($conn, $_POST['nim']);
$jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
$angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);

// Cek apakah NIM sudah ada
$check = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim = '$nim'");
if (mysqli_num_rows($check) > 0) {
    header("Location: ../views/tambah.php?error=nim");
    exit;
}

// Insert data ke database
$query = "INSERT INTO mahasiswa (nama, nim, jurusan, angkatan) VALUES ('$nama', '$nim', '$jurusan', '$angkatan')";
if (mysqli_query($conn, $query)) {
    // Data berhasil ditambahkan
    header("Location: ../views/dashboard.php?status=success&action=add");
    exit;
} else {
    // Error
    header("Location: ../views/tambah.php?error=db");
    exit;
}