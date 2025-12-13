<?php
// sticker-shop/src/includes/header.php

// Start the session on every page
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the configuration file
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>stickza</title>
    <!-- Link to the main stylesheet -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>public/css/style.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>public/index.php" class="logo">Stickza</a>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>public/index.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>public/cart.php">Cart</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo SITE_URL; ?>public//profile.php">Profile</a></li>
                        <li><a href="<?php echo SITE_URL; ?>public//logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>public//login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <div class="main-center">