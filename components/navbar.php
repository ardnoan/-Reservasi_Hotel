<?php
// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$isAdmin = $loggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<nav class="navbar">
    <div class="container">
        <div class="navbar">
            <div class="logo">Hotel Reservation System</div>
            <div class="nav-links" id="nav-links">
                <a href="../index.php">Home</a>
                <a href="views/kamar.php">Rooms</a>
                <a href="views/reservasi.php">Reservation</a>
                <a href="views/cek_reservasi.php">Check Reservation</a>

                <?php if ($loggedIn): ?>
                    <?php if ($isAdmin): ?>
                        <a href="admin/manage_reservasi.php">Admin Panel</a>
                    <?php else: ?>
                        <a href="views/dashboard.php">My Account</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="views/login.php">Login</a>
                <?php endif; ?>
            </div>

        </div>

    </div>
</nav>

<script>
    // Simple toggle for mobile menu
    document.getElementById('menu-toggle').addEventListener('click', function() {
        document.getElementById('nav-links').classList.toggle('active');
    });
</script>