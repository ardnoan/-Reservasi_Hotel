<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login, jika ya redirect ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Cek username
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($query) == 1) {
        $row = mysqli_fetch_assoc($query);
        
        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['role'] = $row['role'];
            
            // Redirect ke dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        
        <div class="login-container">
            <div class="login-logo">
                <h2>Admin Login</h2>
                <p>Masuk ke Dashboard Admin</p>
            </div>
            
            <?php if (isset($error)): ?>
            <div class="alert">
                <?= $error ?>
            </div>
            <?php endif; ?>
            
            <div class="login-form">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
            
            <div class="back-link">
                <a href="../index.php">Kembali ke Halaman Utama</a>
            </div>
        </div>
        
    </div>
</body>
</html>