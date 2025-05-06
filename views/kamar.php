<?php
session_start();
require '../koneksi.php';

// Ambil data jenis kamar untuk ditampilkan
$query_jenis_kamar = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar ORDER BY harga ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamar - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        
        <h2>Daftar Kamar Hotel</h2>
        
        <div class="room-list">
            <?php while($jenis_kamar = mysqli_fetch_assoc($query_jenis_kamar)): ?>
            <div class="room-card">
                <div class="room-image">
                    <img src="../assets/images/<?= $jenis_kamar['gambar'] ?>" alt="<?= $jenis_kamar['nama_jenis'] ?>">
                </div>
                <div class="room-details">
                    <h3><?= $jenis_kamar['nama_jenis'] ?></h3>
                    <p class="room-price">Rp <?= number_format($jenis_kamar['harga'], 0, ',', '.') ?> / malam</p>
                    <p class="room-capacity">Kapasitas: <?= $jenis_kamar['kapasitas'] ?> orang</p>
                    <p class="room-description"><?= $jenis_kamar['deskripsi'] ?></p>
                    <div class="room-facilities">
                        <p><strong>Fasilitas:</strong> <?= $jenis_kamar['fasilitas'] ?></p>
                    </div>
                    
                    <?php
                    // Hitung jumlah kamar tersedia untuk jenis kamar ini
                    $id_jenis = $jenis_kamar['id_jenis'];
                    $query_kamar_tersedia = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM tabel_kamar WHERE id_jenis = $id_jenis AND status = 'tersedia'");
                    $kamar_tersedia = mysqli_fetch_assoc($query_kamar_tersedia);
                    ?>
                    
                    <p><strong>Kamar tersedia:</strong> <?= $kamar_tersedia['jumlah'] ?></p>
                    <a href="reservasi.php?id_jenis=<?= $jenis_kamar['id_jenis'] ?>" class="btn">Pesan Sekarang</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>