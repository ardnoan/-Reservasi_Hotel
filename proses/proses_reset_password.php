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

// Cek apakah user yang akan di-reset password-nya ada
$query_check = mysqli_query($conn, "SELECT * FROM tabel_users WHERE id_user = $id_user");
if (mysqli_num_rows($query_check) == 0) {
    header("Location: ../views/manage_users.php?error=user_not_found");
    exit;
}

$user = mysqli_fetch_assoc($query_check);

// Cek apakah user mencoba me-reset password dirinya sendiri
if ($id_user == $_SESSION['id_user']) {
    header("Location: ../views/manage_users.php?error=cannot_reset_self");
    exit;
}

// Generate default password (username123)
$default_password = $user['username'] . '123';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Update password
$query = "UPDATE tabel_users SET password = '$hashed_password' WHERE id_user = $id_user";
if (mysqli_query($conn, $query)) {
    // Catat log aktivitas
    $aktivitas = "Mereset password pengguna dengan ID #$id_user (username: " . $user['username'] . ")";
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
?>