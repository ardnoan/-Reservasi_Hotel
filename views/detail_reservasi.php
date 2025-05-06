<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id_reservasi = (int)$_GET['id'];

// Ambil data reservasi
$query_reservasi = mysqli_query($conn, "
    SELECT r.*, t.nama_tamu, t.email, t.no_telepon, t.alamat, t.jenis_identitas, t.no_identitas
    FROM tabel_reservasi r
    JOIN tabel_tamu t ON r.id_tamu = t.id_tamu
    WHERE r.id_reservasi = $id_reservasi
");

if (mysqli_num_rows($query_reservasi) == 0) {
    header("Location: dashboard.php");
    exit;
}

$reservasi = mysqli_fetch_assoc($query_reservasi);

// Ambil detail kamar
$query_detail = mysqli_query($conn, "
    SELECT dr.*, k.nomor_kamar, jk.nama_jenis, jk.harga, jk.kapasitas, jk.fasilitas
    FROM tabel_detail_reservasi dr
    JOIN tabel_kamar k ON dr.id_kamar = k.id_kamar
    JOIN tabel_jenis_kamar jk ON k.id_jenis = jk.id_jenis
    WHERE dr.id_reservasi = $id_reservasi
");

// Ambil data pembayaran
$query_pembayaran = mysqli_query($conn, "SELECT * FROM tabel_pembayaran WHERE id_reservasi = $id_reservasi");
$pembayaran = mysqli_fetch_assoc($query_pembayaran);

// Hitung jumlah hari
$checkin = new DateTime($reservasi['tanggal_checkin']);
$checkout = new DateTime($reservasi['tanggal_checkout']);
$interval = $checkin->diff($checkout);
$jumlah_hari = $interval->days;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Reservasi - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <?php include '../components/navbar.php'; ?>


        <div class="dashboard-container">
            <h2>Detail Reservasi</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php if ($_GET['success'] == 'status_updated'): ?>
                        Status reservasi berhasil diperbarui.
                    <?php else: ?>
                        Operasi berhasil dilakukan.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="detail-container">
                <div class="detail-header">
                    <div>
                        <h3>Kode Booking: <?= $reservasi['kode_booking'] ?></h3>
                        <p>Status:
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
                        </p>
                    </div>
                    <div class="action-buttons">
                        <?php if ($reservasi['status'] == 'pending'): ?>
                            <a href="../proses/update_status.php?id=<?= $id_reservasi ?>&status=confirmed" class="btn btn-success">Konfirmasi</a>
                            <a href="../proses/update_status.php?id=<?= $id_reservasi ?>&status=cancelled" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">Batalkan</a>
                        <?php elseif ($reservasi['status'] == 'confirmed'): ?>
                            <a href="../proses/update_status.php?id=<?= $id_reservasi ?>&status=checked_in" class="btn btn-success">Check-in</a>
                            <a href="../proses/update_status.php?id=<?= $id_reservasi ?>&status=cancelled" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')">Batalkan</a>
                        <?php elseif ($reservasi['status'] == 'checked_in'): ?>
                            <a href="../proses/update_status.php?id=<?= $id_reservasi ?>&status=checked_out" class="btn btn-success">Check-out</a>
                        <?php endif; ?>
                        <a href="javascript:window.print()" class="btn">Cetak</a>
                    </div>
                </div>

                <div class="detail-content">
                    <div class="detail-section">
                        <h3>Detail Reservasi</h3>
                        <div class="detail-item">
                            <div class="detail-label">Kode Booking:</div>
                            <div class="detail-value"><?= $reservasi['kode_booking'] ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tanggal Check-in:</div>
                            <div class="detail-value"><?= date('d F Y', strtotime($reservasi['tanggal_checkin'])) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tanggal Check-out:</div>
                            <div class="detail-value"><?= date('d F Y', strtotime($reservasi['tanggal_checkout'])) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Lama Menginap:</div>
                            <div class="detail-value"><?= $jumlah_hari ?> malam</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Jumlah Tamu:</div>
                            <div class="detail-value"><?= $reservasi['jumlah_tamu'] ?> orang</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Harga:</div>
                            <div class="detail-value">Rp <?= number_format($reservasi['total_harga'], 0, ',', '.') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tanggal Pemesanan:</div>
                            <div class="detail-value"><?= date('d F Y H:i', strtotime($reservasi['created_at'])) ?></div>
                        </div>
                        <?php if (!empty($reservasi['catatan'])): ?>
                            <div class="detail-item">
                                <div class="detail-label">Catatan:</div>
                                <div class="detail-value"><?= $reservasi['catatan'] ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="detail-section">
                        <h3>Data Tamu</h3>
                        <div class="detail-item">
                            <div class="detail-label">Nama Tamu:</div>
                            <div class="detail-value"><?= $reservasi['nama_tamu'] ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value"><?= $reservasi['email'] ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">No. Telepon:</div>
                            <div class="detail-value"><?= $reservasi['no_telepon'] ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Alamat:</div>
                            <div class="detail-value"><?= $reservasi['alamat'] ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Identitas:</div>
                            <div class="detail-value"><?= $reservasi['jenis_identitas'] ?> - <?= $reservasi['no_identitas'] ?></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Detail Kamar</h3>
                        <?php while ($detail = mysqli_fetch_assoc($query_detail)): ?>
                            <div class="room-details">
                                <div class="detail-item">
                                    <div class="detail-label">Nomor Kamar:</div>
                                    <div class="detail-value"><?= $detail['nomor_kamar'] ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Tipe Kamar:</div>
                                    <div class="detail-value"><?= $detail['nama_jenis'] ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Harga:</div>
                                    <div class="detail-value">Rp <?= number_format($detail['harga'], 0, ',', '.') ?> / malam</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Kapasitas:</div>
                                    <div class="detail-value"><?= $detail['kapasitas'] ?> orang</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Fasilitas:</div>
                                    <div class="detail-value"><?= $detail['fasilitas'] ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php if (isset($pembayaran)): ?>
                        <div class="detail-section">
                            <h3>Detail Pembayaran</h3>
                            <div class="detail-item">
                                <div class="detail-label">Jumlah Dibayar:</div>
                                <div class="detail-value">Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.') ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Metode Pembayaran:</div>
                                <div class="detail-value payment-method"><?= str_replace('_', ' ', $pembayaran['metode_pembayaran']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status Pembayaran:</div>
                                <div class="detail-value">
                                    <?php
                                    switch ($pembayaran['status_pembayaran']) {
                                        case 'pending':
                                            echo '<span class="status-pending">Menunggu Pembayaran</span>';
                                            break;
                                        case 'success':
                                            echo '<span class="status-checked-in">Pembayaran Berhasil</span>';
                                            break;
                                        case 'failed':
                                            echo '<span class="status-cancelled">Pembayaran Gagal</span>';
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Tanggal Pembayaran:</div>
                                <div class="detail-value"><?= date('d F Y H:i', strtotime($pembayaran['tanggal_pembayaran'])) ?></div>
                            </div>

                            <?php if ($pembayaran['status_pembayaran'] == 'pending'): ?>
                                <div class="payment-actions">
                                    <a href="../proses/update_pembayaran.php?id=<?= $pembayaran['id_pembayaran'] ?>&status=success" class="btn btn-success" onclick="return confirm('Konfirmasi pembayaran sebagai berhasil?')">Konfirmasi Pembayaran</a>
                                    <a href="../proses/update_pembayaran.php?id=<?= $pembayaran['id_pembayaran'] ?>&status=failed" class="btn btn-danger" onclick="return confirm('Tandai pembayaran sebagai gagal?')">Tolak Pembayaran</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php include '../components/footer.php'; ?>

    </div>
</body>

</html>