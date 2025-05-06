<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah user adalah admin
if ($_SESSION['level'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$level_filter = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : '';

// Base query
$query = "
    SELECT * FROM tabel_users
    WHERE 1=1
";

// Add search condition
if (!empty($search)) {
    $query .= " AND (
        username LIKE '%$search%' OR 
        nama_lengkap LIKE '%$search%' OR 
        email LIKE '%$search%'
    )";
}

// Add level filter
if (!empty($level_filter)) {
    $query .= " AND level = '$level_filter'";
}

// Query to count total records
$total_query = $query;
$query .= " ORDER BY id_user ASC LIMIT $start, $limit";

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
                        <input type="text" name="search" placeholder="Cari username, nama, email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group">
                        <select name="level">
                            <option value="">-- Semua Level --</option>
                            <option value="admin" <?= $level_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="resepsionis" <?= $level_filter == 'resepsionis' ? 'selected' : '' ?>>Resepsionis</option>
                            <option value="manager" <?= $level_filter == 'manager' ? 'selected' : '' ?>>Manager</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filter</button>
                    <a href="manage_users.php" class="btn">Reset</a>
                </form>
            </div>
            
            <div class="action-buttons">
                <a href="tambah_user.php" class="btn btn-success">+ Tambah Pengguna</a>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Terakhir Login</th>
                            <th>Aksi</th>
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
                            <td><?= $row['email'] ?></td>
                            <td><?= ucfirst($row['level']) ?></td>
                            <td>
                                <?php if ($row['status'] == 'aktif'): ?>
                                <span class="status-available">Aktif</span>
                                <?php else: ?>
                                <span class="status-cancelled">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : 'Belum Pernah' ?></td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?= $row['id_user'] ?>" class="btn-small">Edit</a>
                                <a href="../proses/reset_password.php?id=<?= $row['id_user'] ?>" class="btn-small btn-warning" onclick="return confirm('Reset password pengguna ini?')">Reset Password</a>
                                <?php if ($row['id_user'] != $_SESSION['id_user'] && $row['username'] != 'admin'): ?>
                                <?php if ($row['status'] == 'aktif'): ?>
                                <a href="../proses/update_status_user.php?id=<?= $row['id_user'] ?>&status=nonaktif" class="btn-small btn-warning" onclick="return confirm('Nonaktifkan pengguna ini?')">Nonaktifkan</a>
                                <?php else: ?>
                                <a href="../proses/update_status_user.php?id=<?= $row['id_user'] ?>&status=aktif" class="btn-small btn-success" onclick="return confirm('Aktifkan pengguna ini?')">Aktifkan</a>
                                <?php endif; ?>
                                <a href="../proses/delete_user.php?id=<?= $row['id_user'] ?>" class="btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                                <?php endif; ?>
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
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search='.$search : '' ?><?= !empty($level_filter) ? '&level='.$level_filter : '' ?>" class="pagination-item">← Prev</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?><?= !empty($search) ? '&search='.$search : '' ?><?= !empty($level_filter) ? '&level='.$level_filter : '' ?>" class="pagination-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search='.$search : '' ?><?= !empty($level_filter) ? '&level='.$level_filter : '' ?>" class="pagination-item">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-actions">
                <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
            </div>
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