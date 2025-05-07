<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah user adalah admin
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../components/navbar.php'; ?>


        <div class="form-container">
            <h2>Tambah Pengguna Baru</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    switch ($_GET['error']) {
                        case 'empty_fields':
                            echo "Semua field harus diisi.";
                            break;
                        case 'invalid_email':
                            echo "Format email tidak valid.";
                            break;
                        case 'username_exists':
                            echo "Username sudah digunakan.";
                            break;
                        case 'failed':
                            echo "Gagal menambahkan pengguna.";
                            break;
                        default:
                            echo "Terjadi kesalahan. Silakan coba lagi.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="../proses/proses_tambah_user.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password minimal 8 karakter</small>
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                </div>

                <div class="form-group">
                    <label for="level">Role</label>
                    <select id="level" name="level" required>
                        <option value="">-- Pilih Level --</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                    <a href="manage_users.php" class="btn">Kembali</a>
                </div>
            </form>
        </div>

        <?php include_once '../components/footer.php'; ?>

    </div>
</body>

</html>