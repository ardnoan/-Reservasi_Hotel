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
    $id_user = $_POST['id_user'];
    $nama_lengkap = htmlspecialchars($_POST['nama_lengkap']);
    $email = htmlspecialchars($_POST['email']);
    $level = htmlspecialchars($_POST['level']);
    
    // Validasi data
    if (empty($nama_lengkap) || empty($email) || empty($level)) {
        header("Location: ../views/edit_user.php?id=$id_user&error=empty_fields");
        exit;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../views/edit_user.php?id=$id_user&error=invalid_email");
        exit;
    }

    // Cek email sudah ada atau belum (kecuali email user itu sendiri)
    $check_email = "SELECT * FROM tabel_users WHERE email = '$email' AND id_user != $id_user";
    $email_result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($email_result) > 0) {
        header("Location: ../views/edit_user.php?id=$id_user&error=email_exists");
        exit;
    }

    // Update data user
    $query = "UPDATE tabel_users SET 
              nama_lengkap = '$nama_lengkap', 
              email = '$email', 
              level = '$level' 
              WHERE id_user = $id_user";

    if (mysqli_query($conn, $query)) {
        // Catat log aktivitas
        $aktivitas = "Mengubah data pengguna dengan ID #$id_user";
        $id_admin = $_SESSION['id_user'];
        mysqli_query($conn, "
            INSERT INTO tabel_log (id_user, aktivitas, created_at)
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