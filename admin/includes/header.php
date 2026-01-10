<?php
require_once __DIR__.'./../../src/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
    <header>
        <div>
            <h1>Admin Dashboard</h1>
            <nav>
                <a href="<?php echo SITE_URL; ?>admin/index.php">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>admin/products/">Products</a>
                <a href="<?php echo SITE_URL; ?>admin/orders/">Orders</a>
                <a href="<?php echo SITE_URL; ?>admin/users/">Users</a>
                <a href="<?php echo SITE_URL; ?>admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main>