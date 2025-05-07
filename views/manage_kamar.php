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
$jenis_filter = isset($_GET['jenis']) ? (int)$_GET['jenis'] : 0;

// Base query
$query = "
    SELECT k.*, jk.nama_jenis, jk.harga, jk.kapasitas, jk.fasilitas
    FROM tabel_kamar k
    JOIN tabel_jenis_kamar jk ON k.id_jenis = jk.id_jenis
    WHERE 1=1
";

// Add search condition
if (!empty($search)) {
    $query .= " AND (
        k.nomor_kamar LIKE '%$search%' OR 
        jk.nama_jenis LIKE '%$search%'
    )";
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND k.status = '$status_filter'";
}

// Add jenis filter
if ($jenis_filter > 0) {
    $query .= " AND k.id_jenis = $jenis_filter";
}

// Query to count total records
$total_query = $query;
$query .= " ORDER BY k.nomor_kamar ASC LIMIT $start, $limit";

// Execute query
$result = mysqli_query($conn, $query);
$total_result = mysqli_query($conn, $total_query);
$total_records = mysqli_num_rows($total_result);
$total_pages = ceil($total_records / $limit);

// Get jenis kamar for filter
$query_jenis = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar ORDER BY nama_jenis ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">

        <?php include '../components/navbar.php'; ?>

        <div class="dashboard-container">
            <h2>Kelola Kamar</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php if ($_GET['success'] == 'added'): ?>
                        Kamar berhasil ditambahkan.
                    <?php elseif ($_GET['success'] == 'updated'): ?>
                        Kamar berhasil diperbarui.
                    <?php elseif ($_GET['success'] == 'deleted'): ?>
                        Kamar berhasil dihapus.
                    <?php else: ?>
                        Operasi berhasil dilakukan.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php if ($_GET['error'] == 'cannot_delete'): ?>
                        Tidak dapat menghapus kamar karena masih ada reservasi aktif.
                    <?php else: ?>
                        Terjadi kesalahan. Silakan coba lagi.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="filters">
                <form action="" method="GET" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Cari nomor kamar atau tipe..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="form-group">
                        <select name="status">
                            <option value="">-- Semua Status --</option>
                            <option value="tersedia" <?= $status_filter == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="terpakai" <?= $status_filter == 'terpakai' ? 'selected' : '' ?>>Terpakai</option>
                            <option value="perbaikan" <?= $status_filter == 'perbaikan' ? 'selected' : '' ?>>Dalam Perbaikan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="jenis">
                            <option value="0">-- Semua Tipe Kamar --</option>
                            <?php while ($jenis = mysqli_fetch_assoc($query_jenis)): ?>
                                <option value="<?= $jenis['id_jenis'] ?>" <?= $jenis_filter == $jenis['id_jenis'] ? 'selected' : '' ?>><?= $jenis['nama_jenis'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filter</button>
                    <a href="manage_kamar.php" class="btn">Reset</a>
                </form>
            </div>

            <div class="action-buttons" style="display: flex; justify-content: end">
                <a href="tambah_kamar.php" class="btn btn-success">+ Tambah Kamar</a>
            </div>

            <div class="reservation-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Kamar</th>
                            <th>Tipe Kamar</th>
                            <th>Harga/Malam</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th>Lantai</th>
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
                                    <td><?= $row['nomor_kamar'] ?></td>
                                    <td><?= $row['nama_jenis'] ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td><?= $row['kapasitas'] ?> orang</td>
                                    <td>
                                        <?php
                                        switch ($row['status']) {
                                            case 'tersedia':
                                                echo '<span class="status-available">Tersedia</span>';
                                                break;
                                            case 'terpakai':
                                                echo '<span class="status-occupied">Terpakai</span>';
                                                break;
                                            case 'perbaikan':
                                                echo '<span class="status-maintenance">Dalam Perbaikan</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $row['lantai'] ?></td>
                                    <td class="actions">
                                        <a href="edit_kamar.php?id=<?= $row['id_kamar'] ?>" class="btn-small">Edit</a>
                                        <a href="../proses/proses_update_status_kamar.php?id=<?= $row['id_kamar'] ?>&status=tersedia" class="btn-small btn-success" onclick="return confirm('Set kamar ini sebagai tersedia?')">Set Tersedia</a>
                                        <a href="../proses/proses_update_status_kamar.php?id=<?= $row['id_kamar'] ?>&status=perbaikan" class="btn-small btn-warning" onclick="return confirm('Set kamar ini dalam perbaikan?')">Set Perbaikan</a>
                                        <a href="../proses/proses_delete_kamar.php?id=<?= $row['id_kamar'] ?>" class="btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kamar ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data kamar.</td>
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