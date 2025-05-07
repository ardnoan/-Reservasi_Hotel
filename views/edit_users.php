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
$query = "SELECT * FROM users WHERE id = $id_user";
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
        <?php include_once '../components/navbar.php'; ?>


        <div class="form-container">
            <h2>Edit Pengguna</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    switch ($_GET['error']) {
                        case 'empty_fields':
                            echo "Semua field harus diisi.";
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
                <input type="hidden" name="id_user" value="<?= $user['id'] ?>">

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
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="resepsionis" <?= $user['role'] == 'resepsionis' ? 'selected' : '' ?>>Resepsionis</option>
                        <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                        <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="manage_users.php" class="btn">Kembali</a>
                </div>
            </form>
        </div>
        <?php include_once '../components/footer.php'; ?>

    </div>
</body>

</html>