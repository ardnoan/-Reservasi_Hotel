<?php
session_start();
require 'koneksi.php';

// Ambil data jenis kamar untuk ditampilkan di halaman utama
$query_jenis_kamar = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar ORDER BY harga ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservation System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        
        <div class="hero">
            <div class="hero-content">
                <h1>Selamat Datang di Hotel Kami</h1>
                <p>Nikmati pengalaman menginap terbaik dengan fasilitas modern dan pelayanan prima</p>
                <a href="views/reservasi.php" class="btn">Pesan Kamar Sekarang</a>
            </div>
        </div>
        
        <h2>Kamar Tersedia</h2>
        
        <div class="room-list">
            <?php while($jenis_kamar = mysqli_fetch_assoc($query_jenis_kamar)): ?>
            <div class="room-card">
                <div class="room-image">
                    <img src="assets/images/<?= $jenis_kamar['gambar'] ?>" alt="<?= $jenis_kamar['nama_jenis'] ?>">
                </div>
                <div class="room-details">
                    <h3><?= $jenis_kamar['nama_jenis'] ?></h3>
                    <p class="room-price">Rp <?= number_format($jenis_kamar['harga'], 0, ',', '.') ?> / malam</p>
                    <p class="room-capacity">Kapasitas: <?= $jenis_kamar['kapasitas'] ?> orang</p>
                    <p class="room-description"><?= $jenis_kamar['deskripsi'] ?></p>
                    <div class="room-facilities">
                        <p><strong>Fasilitas:</strong> <?= $jenis_kamar['fasilitas'] ?></p>
                    </div>
                    <a href="views/reservasi.php?id_jenis=<?= $jenis_kamar['id_jenis'] ?>" class="btn">Pesan Sekarang</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="about-hotel">
            <h2>Tentang Hotel Kami</h2>
            <p>Hotel kami menawarkan akomodasi premium dengan lokasi strategis. Dengan beragam pilihan kamar yang nyaman dan fasilitas modern, kami siap memberikan pengalaman menginap yang tak terlupakan.</p>
            <p>Nikmati layanan terbaik dari staff kami yang profesional dan ramah. Hotel kami juga menyediakan restoran dengan beragam menu lokal dan internasional serta fasilitas pendukung lainnya.</p>
        </div>
        
        <div class="features">
            <div class="feature">
                <h3>Lokasi Strategis</h3>
                <p>Terletak di pusat kota, mudah diakses dari berbagai lokasi penting</p>
            </div>
            <div class="feature">
                <h3>Wifi Gratis</h3>
                <p>Koneksi internet cepat tersedia di seluruh area hotel</p>
            </div>
            <div class="feature">
                <h3>Parkir Luas</h3>
                <p>Area parkir yang aman dan luas untuk kendaraan tamu</p>
            </div>
            <div class="feature">
                <h3>Sarapan Gratis</h3>
                <p>Nikmati sarapan dengan menu beragam setiap pagi</p>
            </div>
        </div>
        
    </div>
</body>
</html>