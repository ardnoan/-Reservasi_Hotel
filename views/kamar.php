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
        <div class="navbar">
            <div class="logo">Hotel Reservation System</div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="kamar.php">Kamar</a>
                <a href="reservasi.php">Reservasi</a>
                <a href="cek_reservasi.php">Cek Reservasi</a>
                <?php if(isset($_SESSION['login'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
        
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
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="kamar.php">Kamar</a></li>
                        <li><a href="reservasi.php">Reservasi</a></li>
                        <li><a href="cek_reservasi.php">Cek Reservasi</a></li>
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