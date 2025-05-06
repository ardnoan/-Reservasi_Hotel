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
    <title>Register - Sistem Manajemen Data Mahasiswa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">Sistem Manajemen Data Mahasiswa</div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="login.php">Login</a>
            </div>
        </div>
        
        <h2>Register</h2>
        
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert">
                <?php 
                    if ($_GET['error'] == 'empty') {
                        echo "Username dan password tidak boleh kosong!";
                    } elseif ($_GET['error'] == 'exists') {
                        echo "Username sudah digunakan!";
                    } elseif ($_GET['error'] == 'password') {
                        echo "Password minimal 6 karakter!";
                    } elseif ($_GET['error'] == 'match') {
                        echo "Password dan konfirmasi password tidak sama!";
                    } else {
                        echo "Terjadi kesalahan, silahkan coba lagi.";
                    }
                ?>
            </div>
        <?php } ?>
        
        <form action="../proses/proses_register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
    </div>
</body>
</html>