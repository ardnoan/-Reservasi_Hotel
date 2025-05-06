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

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php?error=invalid_params");
    exit;
}

$id_user = (int)$_GET['id'];

// Ambil data user
$query = "SELECT * FROM tabel_users WHERE id_user = $id_user";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: manage_users.php?error=not_found");
    exit;
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">Hotel Reservation System</div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="kamar.php">Kamar</a>
                <a href="reservasi.php">Reservasi</a>
                <a href="cek_reservasi.php">Cek Reservasi</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
        
        <div class="form-container">
            <h2>Edit Pengguna</h2>
            
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
                    case 'email_exists':
                        echo "Email sudah digunakan oleh pengguna lain.";
                        break;
                    case 'failed':
                        echo "Gagal memperbarui data pengguna.";
                        break;
                    default:
                        echo "Terjadi kesalahan. Silakan coba lagi.";
                }
                ?>
            </div>
            <?php endif; ?>
            
            <form action="../proses/proses_edit_user.php" method="POST">
                <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?= $user['username'] ?>" readonly class="readonly">
                    <small>Username tidak dapat diubah</small>
                </div>
                
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $user['email'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="level">Level</label>
                    <select id="level" name="level" required>
                        <option value="admin" <?= $user['level'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="resepsionis" <?= $user['level'] == 'resepsionis' ? 'selected' : '' ?>>Resepsionis</option>
                        <option value="manager" <?= $user['level'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <input type="text" id="status" value="<?= ucfirst($user['status']) ?>" readonly class="readonly">
                    <small>Status dapat diubah melalui halaman kelola pengguna</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="manage_users.php" class="btn">Kembali</a>
                </div>
            </form>
        </div>
        
        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Hotel Reservation System</h3>
                    <p>Jl. Hotel Indah No. 123, Kota</p>
                    <p>Telepon: (021) 1234-5678</p>
                    <p>Email: info@hotelreservation.com</p>
                </div>
                <div class="footer-section">
                    <h3>Link</h3>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="kamar.php">Kamar</a></li>
                        <li><a href="reservasi.php">Reservasi</a></li>
                        <li><a href="cek_reservasi.php">Cek Reservasi</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Sosial Media</h3>
                    <div class="social-links">
                        <a href="#">Facebook</a>
                        <a href="#">Instagram</a>
                        <a href="#">Twitter</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Hotel Reservation System. All Rights Reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>