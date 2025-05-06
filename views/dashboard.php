<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil data reservasi terbaru
$query_reservasi = mysqli_query($conn, "
    SELECT r.*, t.nama_tamu, t.email, t.no_telepon, p.status_pembayaran 
    FROM tabel_reservasi r
    JOIN tabel_tamu t ON r.id_tamu = t.id_tamu
    LEFT JOIN tabel_pembayaran p ON r.id_reservasi = p.id_reservasi
    ORDER BY r.created_at DESC
    LIMIT 10
");

// Hitung total reservasi
$query_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM tabel_reservasi");
$total_reservasi = mysqli_fetch_assoc($query_total)['total'];

// Hitung reservasi per status
$query_status = mysqli_query($conn, "SELECT status, COUNT(*) as jumlah FROM tabel_reservasi GROUP BY status");
$status_data = [];
while ($row = mysqli_fetch_assoc($query_status)) {
    $status_data[$row['status']] = $row['jumlah'];
}

// Hitung kamar tersedia
$query_kamar = mysqli_query($conn, "SELECT COUNT(*) as tersedia FROM tabel_kamar WHERE status = 'tersedia'");
$kamar_tersedia = mysqli_fetch_assoc($query_kamar)['tersedia'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .stat-card h3 {
            margin-top: 0;
            color: #3498db;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        .reservation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .reservation-table th, .reservation-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .reservation-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .reservation-table tr:hover {
            background-color: #f9f9f9;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .welcome-user {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="dashboard-container">
            <div class="welcome-user">
                <div class="user-info">
                    Selamat datang, <?= $_SESSION['nama_lengkap'] ?> (<?= $_SESSION['role'] ?>)
                </div>
                <div>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
            
            <div class="dashboard-header">
                <h2>Dashboard Admin</h2>
                <div>
                    <a href="manage_reservasi.php" class="btn">Kelola Reservasi</a>
                    <a href="manage_kamar.php" class="btn">Kelola Kamar</a>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="manage_users.php" class="btn">Kelola Users</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Reservasi</h3>
                    <div class="stat-value"><?= $total_reservasi ?></div>
                </div>
                <div class="stat-card">
                    <h3>Menunggu Konfirmasi</h3>
                    <div class="stat-value"><?= isset($status_data['pending']) ? $status_data['pending'] : 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Check-In Hari Ini</h3>
                    <div class="stat-value">
                        <?php
                        $today = date('Y-m-d');
                        $query_checkin = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM tabel_reservasi WHERE tanggal_checkin = '$today' AND status = 'confirmed'");
                        $checkin_hari_ini = mysqli_fetch_assoc($query_checkin)['jumlah'];
                        echo $checkin_hari_ini;
                        ?>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Kamar Tersedia</h3>
                    <div class="stat-value"><?= $kamar_tersedia ?></div>
                </div>
            </div>
            
            <h3>Reservasi Terbaru</h3>
            <div style="overflow-x: auto;">
                <table class="reservation-table">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Nama Tamu</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reservasi = mysqli_fetch_assoc($query_reservasi)): ?>
                        <tr>
                            <td><?= $reservasi['kode_booking'] ?></td>
                            <td><?= $reservasi['nama_tamu'] ?></td>
                            <td><?= date('d-m-Y', strtotime($reservasi['tanggal_checkin'])) ?></td>
                            <td><?= date('d-m-Y', strtotime($reservasi['tanggal_checkout'])) ?></td>
                            <td>Rp <?= number_format($reservasi['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                switch ($reservasi['status']) {
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
                            <td>
                                <?php
                                if (isset($reservasi['status_pembayaran'])) {
                                    switch ($reservasi['status_pembayaran']) {
                                        case 'pending':
                                            echo '<span class="status-pending">Menunggu Pembayaran</span>';
                                            break;
                                        case 'success':
                                            echo '<span class="status-checked-in">Lunas</span>';
                                            break;
                                        case 'failed':
                                            echo '<span class="status-cancelled">Gagal</span>';
                                            break;
                                    }
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="detail_reservasi.php?id=<?= $reservasi['id_reservasi'] ?>" class="btn">Detail</a>
                                    <?php if ($reservasi['status'] == 'pending'): ?>
                                    <a href="../proses/update_status.php?id=<?= $reservasi['id_reservasi'] ?>&status=confirmed" class="btn btn-success">Konfirmasi</a>
                                    <?php elseif ($reservasi['status'] == 'confirmed'): ?>
                                    <a href="../proses/update_status.php?id=<?= $reservasi['id_reservasi'] ?>&status=checked_in" class="btn btn-success">Check-in</a>
                                    <?php elseif ($reservasi['status'] == 'checked_in'): ?>
                                    <a href="../proses/update_status.php?id=<?= $reservasi['id_reservasi'] ?>&status=checked_out" class="btn btn-success">Check-out</a>
                                    <?php endif; ?>
                                    <?php if ($reservasi['status'] != 'cancelled' && $reservasi['status'] != 'checked_out'): ?>
                                    <a href="../proses/update_status.php?id=<?= $reservasi['id_reservasi'] ?>&status=cancelled" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">Batalkan</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($query_reservasi) == 0): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tidak ada data reservasi</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="manage_reservasi.php" class="btn">Lihat Semua Reservasi</a>
            </div>
        </div>
    </div>
</body>
</html>