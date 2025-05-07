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
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $nama_lengkap = htmlspecialchars($_POST['nama_lengkap']);
    $level = htmlspecialchars($_POST['level']);
    
    // Validasi data
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($level)) {
        header("Location: ../views/tambah_user.php?error=empty_fields");
        exit;
    }
    
    // Cek username sudah ada atau belum
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        header("Location: ../views/tambah_user.php?error=username_exists");
        exit;
    }
    
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert data ke database
    $query = "INSERT INTO users (username, password, nama_lengkap, role) 
              VALUES ('$username', '$hashed_password', '$nama_lengkap', '$level')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../views/manage_users.php?success=added");
        exit;
    } else {
        header("Location: ../views/tambah_user.php?error=failed");
        exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman tambah user
    header("Location: ../views/tambah_user.php");
    exit;
}
?>