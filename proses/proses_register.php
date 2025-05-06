<?php
require '../koneksi.php';

// Cek jika form kosong
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
    header("Location: ../views/register.php?error=empty");
    exit;
}

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Cek apakah password minimal 6 karakter
if (strlen($password) < 6) {
    header("Location: ../views/register.php?error=password");
    exit;
}

// Cek apakah password dan konfirmasi password sama
if ($password !== $confirm_password) {
    header("Location: ../views/register.php?error=match");
    exit;
}

// Cek apakah username sudah digunakan
$result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
if (mysqli_num_rows($result) > 0) {
    header("Location: ../views/register.php?error=exists");
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user ke database
$query = "INSERT INTO users (username, password) VALUES ('$username', '$hash')";
if (mysqli_query($conn, $query)) {
    // Registrasi berhasil
    header("Location: ../views/login.php?registered=true");
    exit;
} else {
    // Error
    header("Location: ../views/register.php?error=db");
    exit;
}