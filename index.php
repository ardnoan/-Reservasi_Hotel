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
        <div class="navbar">
            <div class="logo">Hotel Reservation System</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="views/kamar.php">Kamar</a>
                <a href="views/reservasi.php">Reservasi</a>
                <a href="views/cek_reservasi.php">Cek Reservasi</a>
                <?php if(isset($_SESSION['login'])): ?>
                <a href="views/dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                <a href="views/login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
        
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="views/kamar.php">Kamar</a></li>
                        <li><a href="views/reservasi.php">Reservasi</a></li>
                        <li><a href="views/cek_reservasi.php">Cek Reservasi</a></li>
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