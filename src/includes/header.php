<?php
require_once __DIR__ . '/../config.php';

// Start session and get theme
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$theme = getCurrentTheme();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stickza - Premium Stickers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>public/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="<?php echo SITE_URL; ?>public/index.php" class="logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Stickza
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="main-nav desktop-nav">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>public/index.php">Shop</a></li>
                    <li>
                        <form action="<?php echo SITE_URL; ?>public/search.php" method="GET" class="header-search-form">
                            <input type="search" name="q" placeholder="Search..." class="header-search-input">
                            <button type="submit" class="header-search-button">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </form>
                    </li>
                    <?php if (isset($_SESSION['user_id'] )): ?>
                        <li><a href="<?php echo SITE_URL; ?>public/profile.php">Account</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>public/login.php">Sign In</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header-actions">
                <!-- Cart Icon (Visible on all screen sizes) -->
                <a href="<?php echo SITE_URL; ?>public/cart.php" class="nav-cart">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <?php 
                    $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                    if ($cartCount > 0): 
                    ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Hamburger Menu Icon (Mobile only) -->
                <button id="mobileMenuBtn" class="mobile-menu-toggle">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Slide-in Navigation -->
    <div id="mobileNav" class="mobile-nav-panel">
        <button id="closeMobileNav" class="mobile-nav-close">&times;</button>
        <nav>
            <a href="<?php echo SITE_URL; ?>public/index.php">Shop</a>
            <a href="<?php echo SITE_URL; ?>public/search.php">Search</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo SITE_URL; ?>public/profile.php">My Account</a>
                <a href="<?php echo SITE_URL; ?>public/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>public/login.php">Sign In</a>
                <a href="<?php echo SITE_URL; ?>public/register.php">Create Account</a>
            <?php endif; ?>
        </nav>
    </div>
    <div id="mobileNavOverlay" class="mobile-nav-overlay"></div>
    
    <main>
        <div class="container">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNavPanel = document.getElementById('mobileNav');
    const closeMobileNav = document.getElementById('closeMobileNav');
    const mobileNavOverlay = document.getElementById('mobileNavOverlay');

    function openNav() {
        mobileNavPanel.style.transform = 'translateX(0)';
        mobileNavOverlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeNav() {
        mobileNavPanel.style.transform = 'translateX(100%)';
        mobileNavOverlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    mobileMenuBtn.addEventListener('click', openNav);
    closeMobileNav.addEventListener('click', closeNav);
    mobileNavOverlay.addEventListener('click', closeNav);
});
</script>
