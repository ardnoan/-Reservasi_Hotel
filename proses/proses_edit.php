<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

require '../koneksi.php';

// Cek jika form kosong
if (empty($_POST['id']) || empty($_POST['nama']) || empty($_POST['nim']) || empty($_POST['jurusan']) || empty($_POST['angkatan'])) {
    header("Location: ../views/edit.php?id={$_POST['id']}&error=empty");
    exit;
}

$id = mysqli_real_escape_string($conn, $_POST['id']);
$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$nim = mysqli_real_escape_string($conn, $_POST['nim']);
$jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
$angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);

// Cek apakah NIM sudah digunakan oleh mahasiswa lain
$check = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim = '$nim' AND id != $id");
if (mysqli_num_rows($check) > 0) {
    header("Location: ../views/edit.php?id=$id&error=nim");
    exit;
}

// Update data di database
$query = "UPDATE mahasiswa SET nama='$nama', nim='$nim', jurusan='$jurusan', angkatan='$angkatan' WHERE id=$id";
if (mysqli_query($conn, $query)) {
    // Data berhasil diupdate
    header("Location: ../views/dashboard.php?status=success&action=edit");
    exit;
} else {
    // Error
    header("Location: ../views/edit.php?id=$id&error=db");
    exit;
}
 
CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    jurusan VARCHAR(50),
    angkatan YEAR
);
 