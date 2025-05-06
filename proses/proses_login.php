<?php
session_start();
require '../koneksi.php';

// Cek jika form kosong
if (empty($_POST['username']) || empty($_POST['password'])) {
    header("Location: ../views/login.php?error=empty");
    exit;
}

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

// Cek apakah username ada
if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Set session login
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header("Location: ../views/dashboard.php");
        exit;
    }
}

// Jika login gagal
header("Location: ../views/login.php?error=invalid");
exit;
