<?php
// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']) || isset($_SESSION['login']);
$isAdmin = $loggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Determine if we're in the root directory or a subdirectory
$in_subdirectory = strpos($_SERVER['PHP_SELF'], '/views/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base_path = $in_subdirectory ? '..' : '.';
?>

<nav class="navbar">
    <div class="container">
        <div class="navbar">
            <div class="logo">Hotel Reservation System</div>
            
            <div class="menu-toggle" id="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <div class="nav-links" id="nav-links">
                <a href="<?php echo $base_path; ?>/index.php">Home</a>
                <a href="<?php echo $base_path; ?>/views/kamar.php">Rooms</a>
                <a href="<?php echo $base_path; ?>/views/reservasi.php">Reservation</a>
                <a href="<?php echo $base_path; ?>/views/cek_reservasi.php">Check Reservation</a>

                <?php if ($loggedIn): ?>
                    <?php if ($isAdmin): ?>
                        <a href="<?php echo $base_path; ?>/admin/dashboard.php">Admin Panel</a>
                    <?php else: ?>
                        <a href="<?php echo $base_path; ?>/views/dashboard.php">My Account</a>
                    <?php endif; ?>
                    <a href="<?php echo $base_path; ?>/logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>/views/login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menu-toggle');
        const navLinks = document.getElementById('nav-links');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                menuToggle.classList.toggle('active');
            });
        }
    });
</script>