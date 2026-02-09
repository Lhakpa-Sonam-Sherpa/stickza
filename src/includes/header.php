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
            
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>public/index.php">Home</a></li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>public/cart.php" class="nav-cart">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Cart
                            <?php 
                            $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                            if ($cartCount > 0): 
                            ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo SITE_URL; ?>public/profile.php">Profile</a></li>
                        <li><a href="<?php echo SITE_URL; ?>public/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>public/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">