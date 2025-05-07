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
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Base query
$query = "
    SELECT r.*, t.nama_tamu, t.email, t.no_telepon 
    FROM tabel_reservasi r
    JOIN tabel_tamu t ON r.id_tamu = t.id_tamu
    WHERE 1=1
";

// Add search condition
if (!empty($search)) {
    $query .= " AND (
        r.kode_booking LIKE '%$search%' OR 
        t.nama_tamu LIKE '%$search%' OR 
        t.email LIKE '%$search%' OR 
        t.no_telepon LIKE '%$search%'
    )";
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND r.status = '$status_filter'";
}

// Query to count total records
$total_query = $query;
$query .= " ORDER BY r.created_at DESC LIMIT $start, $limit";

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
    <title>Kelola Reservasi - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <?php include '../components/navbar.php'; ?>

        <div class="dashboard-container">
            <h2>Kelola Reservasi</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php if ($_GET['success'] == 'deleted'): ?>
                        Reservasi berhasil dihapus.
                    <?php elseif ($_GET['success'] == 'updated'): ?>
                        Reservasi berhasil diperbarui.
                    <?php else: ?>
                        Operasi berhasil dilakukan.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="filters">
                <form action="" method="GET" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Cari kode booking, nama, email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group">
                        <select name="status">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                            <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Terkonfirmasi</option>
                            <option value="checked_in" <?= $status_filter == 'checked_in' ? 'selected' : '' ?>>Check-in</option>
                            <option value="checked_out" <?= $status_filter == 'checked_out' ? 'selected' : '' ?>>Check-out</option>
                            <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filter</button>
                    <a href="manage_reservasi.php" class="btn">Reset</a>
                </form>
            </div>

            <div class="reservation-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Booking</th>
                            <th>Nama Tamu</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Total Harga</th>
                            <th>Tanggal Pemesanan</th>
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
                                    <td><?= $row['kode_booking'] ?></td>
                                    <td><?= $row['nama_tamu'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_checkin'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_checkout'])) ?></td>
                                    <td>
                                        <?php
                                        switch ($row['status']) {
                                            case 'pending':
                                                echo '<span class="status-pending">Menunggu Konfirmasi</span>';
                                                break;
                                            case 'confirmed':
                                                echo '<span class="status-confirmed">Terkonfirmasi</span>';
                                                break;
                                            case 'checked_in':
                                                echo '<span class="status-checked-in">Check-in</span>';
                                                break;
                                            case 'checked_out':
                                                echo '<span class="status-checked-out">Check-out</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="status-cancelled">Dibatalkan</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td class="actions">
                                        <a href="detail_reservasi.php?id=<?= $row['id_reservasi'] ?>" class="btn-small">Detail</a>
                                        <?php if ($row['status'] != 'checked_out' && $row['status'] != 'cancelled'): ?>
                                            <a href="../proses/proses_delete_reservasi.php?id=<?= $row['id_reservasi'] ?>" class="btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus reservasi ini?')">Hapus</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data reservasi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . $search : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" class="pagination-item">← Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . $search : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" class="pagination-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . $search : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" class="pagination-item">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php include '../components/footer.php'; ?>

    </div>
</body>

</html>