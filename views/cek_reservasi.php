<?php
session_start();
require '../koneksi.php';

$reservasi = null;
$detail_reservasi = null;
$tamu = null;

if (isset($_GET['kode_booking']) && !empty($_GET['kode_booking'])) {
    $kode_booking = mysqli_real_escape_string($conn, $_GET['kode_booking']);
    
    // Ambil data reservasi
    $query_reservasi = mysqli_query($conn, "SELECT * FROM tabel_reservasi WHERE kode_booking = '$kode_booking'");
    
    if (mysqli_num_rows($query_reservasi) > 0) {
        $reservasi = mysqli_fetch_assoc($query_reservasi);
        $id_reservasi = $reservasi['id_reservasi'];
        $id_tamu = $reservasi['id_tamu'];
        
        // Ambil data tamu
        $query_tamu = mysqli_query($conn, "SELECT * FROM tabel_tamu WHERE id_tamu = $id_tamu");
        $tamu = mysqli_fetch_assoc($query_tamu);
        
        // Ambil detail reservasi
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
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Reservasi - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        import components/navbar.php
        <h2>Cek Status Reservasi</h2>
        
        <div class="search-container">
            <form action="" method="get">
                <input type="text" name="kode_booking" placeholder="Masukkan kode booking" required>
                <button type="submit" class="btn">Cek</button>
            </form>
        </div>
        
        <?php if (isset($_GET['kode_booking']) && empty($reservasi)): ?>
        <div class="alert">
            Kode booking tidak ditemukan. Silakan periksa kembali kode booking Anda.
        </div>
        <?php endif; ?>
        
        <?php if ($reservasi): ?>
        <div class="detail-container">
            <div class="detail-section">
                <h3>Detail Reservasi</h3>
                <div class="detail-item">
                    <div class="detail-label">Kode Booking:</div>
                    <div class="detail-value"><?= $reservasi['kode_booking'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
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
                    </div>
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
                    <div class="detail-value"><?= $tamu['nama_tamu'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?= $tamu['email'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">No. Telepon:</div>
                    <div class="detail-value"><?= $tamu['no_telepon'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Alamat:</div>
                    <div class="detail-value"><?= $tamu['alamat'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Identitas:</div>
                    <div class="detail-value"><?= $tamu['jenis_identitas'] ?> - <?= $tamu['no_identitas'] ?></div>
                </div>
            </div>
            
            <div class="detail-section">
                <h3>Detail Kamar</h3>
                <?php while($detail = mysqli_fetch_assoc($query_detail)): ?>
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
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="javascript:window.print()" class="btn">Cetak</a>
                <a href="../index.php" class="btn">Kembali ke Home</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>