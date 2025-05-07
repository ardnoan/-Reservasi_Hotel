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
    $id_user = (int)$_POST['id'];
    $nama_lengkap = htmlspecialchars($_POST['nama_lengkap']);
    $level = htmlspecialchars($_POST['level']);
    
    // Validasi data
    if (empty($nama_lengkap) || empty($level)) {
        header("Location: ../views/edit_user.php?id=$id_user&error=empty_fields");
        exit;
    }
    
    // Update data ke database
    $query = "UPDATE users SET 
              nama_lengkap = '$nama_lengkap', 
              role = '$level' 
              WHERE id = $id_user";
    
    if (mysqli_query($conn, $query)) {
        // Catat log aktivitas
        $aktivitas = "Mengedit pengguna dengan ID #$id_user";
        $id_admin = $_SESSION['user_id'];
        mysqli_query($conn, "
            INSERT INTO tabel_log (id, aktivitas, created_at)
            VALUES ($id_admin, '$aktivitas', NOW())
        ");
        
        header("Location: ../views/manage_users.php?success=updated");
        exit;
    } else {
        header("Location: ../views/edit_user.php?id=$id_user&error=failed");
        exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman manage users
    header("Location: ../views/manage_users.php");
    exit;
}
?>