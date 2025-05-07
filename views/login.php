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
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.25);
            padding: 40px 36px 32px 36px;
            width: 100%;
            max-width: 370px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.8s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo h2 {
            margin: 0 0 8px 0;
            font-size: 2rem;
            color: #1e3c72;
            letter-spacing: 1px;
        }
        .login-logo p {
            color: #555;
            font-size: 1rem;
        }
        .alert {
            background: #ffe0e0;
            color: #c0392b;
            border: 1px solid #e57373;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            width: 100%;
            text-align: center;
            font-size: 0.98rem;
        }
        .login-form {
            width: 100%;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #1e3c72;
            font-weight: 500;
            font-size: 1rem;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #bfc9d1;
            border-radius: 7px;
            font-size: 1rem;
            background: #f7fafd;
            transition: border 0.2s;
        }
        .form-group input:focus {
            border: 1.5px solid #2a5298;
            outline: none;
            background: #fff;
        }
        .login-btn {
            width: 100%;
            padding: 12px 0;
            background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 1.08rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(46, 91, 255, 0.07);
            transition: background 0.2s, transform 0.1s;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%);
            transform: translateY(-2px) scale(1.02);
        }
        .back-link {
            margin-top: 22px;
            text-align: center;
        }
        .back-link a {
            color: #2a5298;
            text-decoration: none;
            font-size: 0.98rem;
            transition: color 0.2s;
        }
        .back-link a:hover {
            color: #1e3c72;
            text-decoration: underline;
        }
    </style>
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
                <form action="" method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
            
            <div class="back-link">
                <a href="../index.php">&larr; Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>
</body>
</html>