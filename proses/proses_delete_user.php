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

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../views/manage_users.php?error=invalid_params");
    exit;
}

$id_user = (int)$_GET['id'];

// Cek apakah user yang akan dihapus adalah admin utama
$query_check = mysqli_query($conn, "SELECT * FROM tabel_users WHERE id_user = $id_user");
$user = mysqli_fetch_assoc($query_check);

if ($user['username'] == 'admin') {
    header("Location: ../views/manage_users.php?error=cannot_delete_admin");
    exit;
}

// Cek apakah user mencoba menghapus dirinya sendiri
if ($id_user == $_SESSION['id_user']) {
    header("Location: ../views/manage_users.php?error=cannot_delete_self");
    exit;
}

// Hapus user
$query = "DELETE FROM tabel_users WHERE id_user = $id_user";
if (mysqli_query($conn, $query)) {
    // Catat log aktivitas
    $aktivitas = "Menghapus pengguna dengan ID #$id_user (username: " . $user['username'] . ")";
    $id_admin = $_SESSION['id_user'];
    mysqli_query($conn, "
        INSERT INTO tabel_log (id_user, aktivitas, created_at)
        VALUES ($id_admin, '$aktivitas', NOW())
    ");
    
    header("Location: ../views/manage_users.php?success=deleted");
    exit;
} else {
    header("Location: ../views/manage_users.php?error=delete_failed");
    exit;
}