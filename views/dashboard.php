<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Get user role information
$role = $_SESSION['role'];

// Define base URL for includes
$base_url = '..';

// Ambil data reservasi terbaru
$query_reservasi = mysqli_query($conn, "
    SELECT r.*, t.nama_tamu, t.email, t.no_telepon, p.status_pembayaran, p.id_pembayaran
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

// Get today's check-ins
$today = date('Y-m-d');
$query_checkin = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM tabel_reservasi WHERE tanggal_checkin = '$today' AND status = 'confirmed'");
$checkin_hari_ini = mysqli_fetch_assoc($query_checkin)['jumlah'];

// Handle alerts
$alert_message = '';
$alert_type = '';

if (isset($_GET['success'])) {
    $alert_type = 'success';
    switch ($_GET['success']) {
        case 'status_updated':
            $alert_message = 'Status reservasi berhasil diperbarui!';
            break;
        case 'payment_updated':
            $alert_message = 'Status pembayaran berhasil diperbarui!';
            break;
        default:
            $alert_message = 'Operasi berhasil dilakukan!';
    }
}

if (isset($_GET['error'])) {
    $alert_type = 'error';
    switch ($_GET['error']) {
        case 'database':
            $alert_message = 'Terjadi kesalahan pada database!';
            break;
        case 'invalid':
            $alert_message = 'Parameter tidak valid!';
            break;
        default:
            $alert_message = 'Terjadi kesalahan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="container">
        <div class="dashboard-container">
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo $alert_type; ?>">
                    <?php echo $alert_message; ?>
                </div>
            <?php endif; ?>

            <div class="welcome-user">
                <div class="user-info">
                    <h2>Dashboard</h2>
                    <p>Selamat datang, <strong><?= $_SESSION['nama_lengkap'] ?? 'User' ?></strong> (<?= $role ?>)</p>
                </div>
                <div>
                    <a href="../logout.php" class="btn btn-danger p-5" style="margin-right: 20px; padding: 10px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="dashboard-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_reservasi.php"><i class="fas fa-calendar-check"></i> Kelola Reservasi</a>
                <a href="manage_kamar.php"><i class="fas fa-bed"></i> Kelola Kamar</a>
                <?php if ($role == "admin"):?>
                    <a href="manage_users.php"><i class="fas fa-users"></i> Kelola Users</a>
                    <a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a>
                <?php endif; ?>
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
                    <div class="stat-value"><?= $checkin_hari_ini ?></div>
                </div>
                <div class="stat-card">
                    <h3>Kamar Tersedia</h3>
                    <div class="stat-value"><?= $kamar_tersedia ?></div>
                </div>
            </div>

            <h3><i class="fas fa-list"></i> Reservasi Terbaru</h3>
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
                        <?php if (mysqli_num_rows($query_reservasi) > 0): ?>
                            <?php while ($reservasi = mysqli_fetch_assoc($query_reservasi)): ?>
                                <tr>
                                    <td><?= $reservasi['kode_booking'] ?></td>
                                    <td><?= $reservasi['nama_tamu'] ?></td>
                                    <td><?= date('d-m-Y', strtotime($reservasi['tanggal_checkin'])) ?></td>
                                    <td><?= date('d-m-Y', strtotime($reservasi['tanggal_checkout'])) ?></td>
                                    <td>Rp <?= number_format($reservasi['total_harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';

                                        switch ($reservasi['status']) {
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                $status_text = 'Menunggu Konfirmasi';
                                                break;
                                            case 'confirmed':
                                                $status_class = 'status-confirmed';
                                                $status_text = 'Terkonfirmasi';
                                                break;
                                            case 'checked_in':
                                                $status_class = 'status-checked-in';
                                                $status_text = 'Check-in';
                                                break;
                                            case 'checked_out':
                                                $status_class = 'status-checked-out';
                                                $status_text = 'Check-out';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'status-cancelled';
                                                $status_text = 'Dibatalkan';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $payment_class = '';
                                        $payment_text = '-';

                                        if (isset($reservasi['status_pembayaran'])) {
                                            switch ($reservasi['status_pembayaran']) {
                                                case 'pending':
                                                    $payment_class = 'status-pending';
                                                    $payment_text = 'Menunggu Pembayaran';
                                                    break;
                                                case 'success':
                                                    $payment_class = 'status-checked-in';
                                                    $payment_text = 'Lunas';
                                                    break;
                                                case 'failed':
                                                    $payment_class = 'status-cancelled';
                                                    $payment_text = 'Gagal';
                                                    break;
                                            }
                                        }
                                        ?>
                                        <span class="status-badge <?= $payment_class ?>"><?= $payment_text ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- View Details Button (always visible) -->
                                            <a href="detail_reservasi.php?id=<?= $reservasi['id_reservasi'] ?>" class="btn btn-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- Payment Button (only for pending payments) -->
                                            <?php if ($reservasi['status_pembayaran'] == 'pending'): ?>
                                                <a href="../proses/proses_update_pembayaran.php?id=<?= $reservasi['id_pembayaran'] ?>&status=success"
                                                    class="btn btn-success" title="Konfirmasi Pembayaran">
                                                    <i class="fas fa-money-bill"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Status Management Buttons -->
                                            <?php if ($reservasi['status'] == 'pending'): ?>
                                                <?php if ($reservasi['status_pembayaran'] == 'success'): ?>
                                                    <!-- Only show confirm button if payment is successful -->
                                                    <a href="../proses/proses_updatestatus.php?id=<?= $reservasi['id_reservasi'] ?>&status=confirmed"
                                                        class="btn btn-success" title="Konfirmasi Reservasi">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <!-- Show disabled button with tooltip if payment pending -->
                                                    <a href="javascript:void(0)" class="btn btn-secondary disabled"
                                                        title="Pembayaran harus diselesaikan sebelum konfirmasi"
                                                        data-toggle="tooltip" data-placement="top">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>

                                            <?php elseif ($reservasi['status'] == 'confirmed'): ?>
                                                <!-- Only show check-in button if payment is successful -->
                                                <a href="../proses/proses_updatestatus.php?id=<?= $reservasi['id_reservasi'] ?>&status=checked_in"
                                                    class="btn btn-success" title="Check-in">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </a>

                                            <?php elseif ($reservasi['status'] == 'checked_in'): ?>
                                                <!-- Check-out button -->
                                                <a href="../proses/proses_updatestatus.php?id=<?= $reservasi['id_reservasi'] ?>&status=checked_out"
                                                    class="btn btn-success" title="Check-out">
                                                    <i class="fas fa-sign-out-alt"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Cancel Button (hide for completed/cancelled reservations) -->
                                            <?php if ($reservasi['status'] != 'cancelled' && $reservasi['status'] != 'checked_out'): ?>
                                                <a href="../proses/proses_updatestatus.php?id=<?= $reservasi['id_reservasi'] ?>&status=cancelled"
                                                    class="btn btn-danger" title="Batalkan"
                                                    onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Tidak ada data reservasi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include_once '../components/footer.php'; ?>

    <script>
        // Show alert message and hide after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alertElement = document.querySelector('.alert');
            if (alertElement) {
                setTimeout(function() {
                    alertElement.style.opacity = '0';
                    setTimeout(function() {
                        alertElement.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>

</html>