<div class="navbar">
    <div class="logo">Hotel Reservation System</div>
    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="kamar.php">Kamar</a>
        <a href="reservasi.php">Reservasi</a>
        <a href="cek_reservasi.php">Cek Reservasi</a>
        <?php if (isset($_SESSION['login'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="../logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</div>