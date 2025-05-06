<?php
session_start();
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Data Mahasiswa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">Sistem Manajemen Data Mahasiswa</div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="register.php">Register</a>
            </div>
        </div>
        
        <h2>Login</h2>
        
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert">
                <?php 
                    if ($_GET['error'] == 'empty') {
                        echo "Username dan password tidak boleh kosong!";
                    } elseif ($_GET['error'] == 'invalid') {
                        echo "Username atau password salah!";
                    } else {
                        echo "Terjadi kesalahan, silahkan coba lagi.";
                    }
                ?>
            </div>
        <?php } ?>
        
        <?php if (isset($_GET['registered'])) { ?>
            <div class="alert alert-success">
                Registrasi berhasil! Silahkan login.
            </div>
        <?php } ?>
        
        <form action="../proses/proses_login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <p>Belum punya akun? <a href="register.php">Register disini</a></p>
    </div>
</body>
</html>