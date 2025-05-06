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

// Generate default password
$default_password = 'password123'; // Anda bisa mengubah ini
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Update password
$query = "UPDATE tabel_users SET password = '$hashed_password' WHERE id_user = $id_user";
if (mysqli_query($conn, $query)) {
    // Catat log aktivitas
    $aktivitas = "Mereset password pengguna dengan ID #$id_user";
    $id_admin = $_SESSION['id_user'];
    mysqli_query($conn, "
        INSERT INTO tabel_log (id_user, aktivitas, created_at)
        VALUES ($id_admin, '$aktivitas', NOW())
    ");
    
    header("Location: ../views/manage_users.php?success=password_reset");
    exit;
} else {
    header("Location: ../views/manage_users.php?error=reset_failed");
    exit;
}