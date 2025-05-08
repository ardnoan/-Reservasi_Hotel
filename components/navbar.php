<?php
// Session start should be at the beginning of the file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check user authentication status
$loggedIn = isset($_SESSION['user_id']) || isset($_SESSION['login']);
$isAdmin = $loggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Determine if we're in the root directory or a subdirectory
$current_path = $_SERVER['PHP_SELF'];
$in_subdirectory = strpos($current_path, '/views/') !== false || 
                   strpos($current_path, '/admin/') !== false;
$base_path = $in_subdirectory ? '..' : '.';

// Define navigation items with their paths and visibility conditions
$nav_items = [
    ['title' => 'Home', 'path' => '/index.php', 'visible' => true],
    ['title' => 'Rooms', 'path' => '/views/kamar.php', 'visible' => true],
    ['title' => 'Reservation', 'path' => '/views/reservasi.php', 'visible' => true],
    ['title' => 'Check Reservation', 'path' => '/views/cek_reservasi.php', 'visible' => true],
    ['title' => 'Admin Panel', 'path' => '/admin/dashboard.php', 'visible' => $isAdmin],
    ['title' => 'My Account', 'path' => '/views/dashboard.php', 'visible' => $loggedIn && !$isAdmin],
    ['title' => 'Logout', 'path' => '/logout.php', 'visible' => $loggedIn],
    ['title' => 'Login', 'path' => '/views/login.php', 'visible' => !$loggedIn],
];

// Determine current active page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Responsive Navigation Bar -->
<nav class="navbar">
    <div class="container">
        <div class="navbar-content">
            <div class="navbar-brand">
                <a href="<?php echo $base_path; ?>/index.php" class="logo"> &nbsp;&nbsp;Hotel Reservation System</a>
            </div>
            &nbsp;&nbsp;
            <div class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <div class="nav-links" id="nav-links">
                <?php foreach ($nav_items as $item): ?>
                    <?php if ($item['visible']): ?>
                        <a href="<?php echo $base_path . $item['path']; ?>" 
                           class="<?php echo (basename($item['path']) === $current_page) ? 'active' : ''; ?>">
                            <?php echo $item['title']; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</nav>

<style>
/* Modern Navigation Bar Styles */
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --light-color: #ecf0f1;
    --dark-color: #2c3e50;
    --hover-color: #1abc9c;
    --shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

.navbar {
    background-color: var(--primary-color);
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 0;
}

.navbar-content {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px 0;
}

.navbar-brand {
    display: flex;
    align-items: center;
}

.logo {
    color: var(--light-color);
    font-size: 1.5rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.logo:hover {
    color: var(--hover-color);
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 5px;
}

.nav-links a {
    color: var(--light-color);
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 4px;
    font-weight: 500;
    transition: var(--transition);
    position: relative;
}

.nav-links a:hover, 
.nav-links a.active {
    color: var(--hover-color);
    background-color: rgba(255, 255, 255, 0.1);
}

.nav-links a.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 3px;
    background-color: var(--hover-color);
    border-radius: 2px;
}

/* Menu toggle button */
.menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 21px;
    cursor: pointer;
}

.menu-toggle span {
    display: block;
    height: 3px;
    width: 100%;
    background-color: var(--light-color);
    border-radius: 3px;
    transition: var(--transition);
}

/* Responsive styles */
@media (max-width: 768px) {
    .menu-toggle {
        display: flex;
    }
    
    .nav-links {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        flex-direction: column;
        background-color: var(--primary-color);
        box-shadow: var(--shadow);
        padding: 10px 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-in-out, padding 0.4s ease;
        align-items: stretch;
        gap: 0;
    }
    
    .nav-links.active {
        max-height: 500px;
        padding: 10px 0;
    }
    
    .nav-links a {
        padding: 15px 20px;
        border-radius: 0;
        border-left: 3px solid transparent;
    }
    
    .nav-links a:hover,
    .nav-links a.active {
        background-color: rgba(255, 255, 255, 0.05);
        border-left: 3px solid var(--hover-color);
    }
    
    .nav-links a.active::after {
        display: none;
    }
    
    /* Animate menu toggle button */
    .menu-toggle.active span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
    }
    
    .menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .menu-toggle.active span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    
    // Toggle mobile menu
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            menuToggle.classList.toggle('active');
            
            // Accessibility - Set aria attributes
            const expanded = menuToggle.getAttribute('aria-expanded') === 'true' || false;
            menuToggle.setAttribute('aria-expanded', !expanded);
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInsideNavbar = event.target.closest('.navbar-content');
        if (!isClickInsideNavbar && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    });
    
    // Close menu when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    });
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId !== '#') {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu after clicking a link
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        menuToggle.classList.remove('active');
                    }
                }
            }
        });
    });
});
</script>
<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">