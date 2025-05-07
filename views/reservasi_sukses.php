<?php
session_start();
require '../koneksi.php';

if (!isset($_GET['kode']) || empty($_GET['kode'])) {
    header("Location: ../index.php");
    exit;
}

$kode_booking = mysqli_real_escape_string($conn, $_GET['kode']);
$query_reservasi = mysqli_query($conn, "SELECT * FROM tabel_reservasi WHERE kode_booking = '$kode_booking'");

if (mysqli_num_rows($query_reservasi) == 0) {
    header("Location: ../index.php");
    exit;
}

$reservasi = mysqli_fetch_assoc($query_reservasi);
$id_tamu = $reservasi['id_tamu'];

// Ambil data tamu
$query_tamu = mysqli_query($conn, "SELECT * FROM tabel_tamu WHERE id_tamu = $id_tamu");
$tamu = mysqli_fetch_assoc($query_tamu);

// Ambil data pembayaran
$id_reservasi = $reservasi['id_reservasi'];
$query_pembayaran = mysqli_query($conn, "SELECT * FROM tabel_pembayaran WHERE id_reservasi = $id_reservasi");
$pembayaran = mysqli_fetch_assoc($query_pembayaran);

// Ambil detail kamar
$query_detail = mysqli_query($conn, "
    SELECT dr.*, k.nomor_kamar, jk.nama_jenis, jk.harga, jk.kapasitas, jk.fasilitas
    FROM tabel_detail_reservasi dr
    JOIN tabel_kamar k ON dr.id_kamar = k.id_kamar
    JOIN tabel_jenis_kamar jk ON k.id_jenis = jk.id_jenis
    WHERE dr.id_reservasi = $id_reservasi
");
$detail = mysqli_fetch_assoc($query_detail);

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
    <title>Reservasi Sukses - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <?php include '../components/navbar.php'; ?>

        <div class="dashboard-container">
            <h2>Reservasi Berhasil!</h2>
            <div class="detail-container">
                <div class="detail-header">
                    <div>
                        <h3>Kode Booking: <?= $reservasi['kode_booking'] ?></h3>
                        <p>Status:
                            <?php
                            switch ($reservasi['status']) {
                                case 'pending':
                                    echo '<span class="status-pending">Menunggu Pembayaran</span>';
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
                        <a href="javascript:window.print()" class="btn">Cetak</a>
                        <a href="cek_reservasi.php?kode_booking=<?= $kode_booking ?>" class="btn">Lihat Detail</a>
                        <a href="../index.php" class="btn">Kembali ke Home</a>
                    </div>
                </div>

                <div class="detail-content">
                    <div class="detail-section">
                        <h3>Detail Reservasi</h3>
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
                    </div>

                    <div class="detail-section">
                        <h3>Data Tamu</h3>
                        <div class="detail-item">
                            <div class="detail-label">Nama Tamu:</div>
                            <div class="detail-value"><?= $tamu['nama_tamu'] ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value"><?= $tamu['email'] ?></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Detail Kamar</h3>
                        <div class="room-details">
                            <div class="detail-item">
                                <div class="detail-label">Tipe Kamar:</div>
                                <div class="detail-value"><?= $detail['nama_jenis'] ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Nomor Kamar:</div>
                                <div class="detail-value"><?= $detail['nomor_kamar'] ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Informasi Pembayaran</h3>
                        <div class="detail-item">
                            <div class="detail-label">Bank:</div>
                            <div class="detail-value"><strong>Bank ABC</strong></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">No. Rekening:</div>
                            <div class="detail-value">1234-5678-9012</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Atas Nama:</div>
                            <div class="detail-value">Hotel Reservation System</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Jumlah yang harus dibayar:</div>
                            <div class="detail-value"><strong>Rp <?= number_format($reservasi['total_harga'], 0, ',', '.') ?></strong></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Kode Booking untuk Transfer:</div>
                            <div class="detail-value"><strong><?= $kode_booking ?></strong></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Batas Waktu Pembayaran:</div>
                            <div class="detail-value"><strong><?= date('d F Y H:i', strtotime('+24 hours', strtotime($reservasi['created_at']))) ?></strong></div>
                        </div>
                    </div>

                    <div class="note">
                        <p><strong>Note:</strong> Pembayaran harus dilakukan dalam waktu 24 jam setelah reservasi dibuat, atau reservasi akan otomatis dibatalkan.</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../components/footer.php'; ?>

    </div>
</body>

</html>