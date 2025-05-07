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
    echo "<script>alert('Anda Bukan Admin!')</script>";
    header("Location: dashboard.php");
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$level_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';

// Base query
$query = "
    SELECT * FROM users
    WHERE 1=1
";

// Add search condition
if (!empty($search)) {
    $query .= " AND (
        username LIKE '%$search%' OR 
        nama_lengkap LIKE '%$search%' OR 
        role LIKE '%$search%'
    )";
}

// Add level filter
if (!empty($level_filter)) {
    $query .= " AND role = '$level_filter'";
}

// Query to count total records
$total_query = $query;
$query .= " ORDER BY id ASC LIMIT $start, $limit";

// Execute query
$result = mysqli_query($conn, $query);
$total_result = mysqli_query($conn, $total_query);
$total_records = mysqli_num_rows($total_result);
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .reservation-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .reservation-table th,
        .reservation-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #bd2130;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include '../components/navbar.php'; ?>


        <div class="dashboard-container">
            <h2>Kelola Pengguna</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php if ($_GET['success'] == 'added'): ?>
                        Pengguna berhasil ditambahkan.
                    <?php elseif ($_GET['success'] == 'updated'): ?>
                        Pengguna berhasil diperbarui.
                    <?php elseif ($_GET['success'] == 'deleted'): ?>
                        Pengguna berhasil dihapus.
                    <?php elseif ($_GET['success'] == 'password_reset'): ?>
                        Password pengguna berhasil direset.
                    <?php else: ?>
                        Operasi berhasil dilakukan.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php if ($_GET['error'] == 'cannot_delete_admin'): ?>
                        Tidak dapat menghapus akun admin utama.
                    <?php elseif ($_GET['error'] == 'cannot_delete_self'): ?>
                        Tidak dapat menghapus akun Anda sendiri.
                    <?php else: ?>
                        Terjadi kesalahan. Silakan coba lagi.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="filters">
                <form action="" method="GET" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Cari username, nama..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group">
                        <select name="role">
                            <option value="">-- Semua Level --</option>
                            <option value="admin" <?= $level_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="staff" <?= $level_filter == 'staff' ? 'selected' : '' ?>>Staff</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filter</button>
                    <a href="manage_users.php" class="btn">Reset</a>
                </form>
            </div>

            <div class="action-buttons" style="flex: 1; display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <a href="tambah_users.php" class="btn btn-success">+ Tambah Pengguna</a>
            </div>

            <div class="reservation-table">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $start + 1;
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $row['username'] ?></td>
                                    <td><?= $row['nama_lengkap'] ?></td>
                                    <td><?= $row['role'] ?></td>
                                    <td class="actions">
                                        <a href="edit_users.php?id=<?= $row['id'] ?>" class="btn-small">Edit</a>
                                        <a href="../proses/reset_password.php?id=<?= $row['id'] ?>" class="btn-small btn-warning" onclick="return confirm('Reset password pengguna ini?')">Reset Password</a>
                                        <a href="../proses/proses_delete_user.php?id=<?= $row['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data pengguna.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../components/footer.php'; ?>

    </div>
</body>

</html>