<?php
// NO session_start() here - handled by admin_init.php
// NO recursive includes - this caused infinite loop
require_once '../src/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?></title>
    <style>
        /* .admin-header { background: #343a40; padding: 1rem; }
        .admin-nav { display: flex; gap: 1rem; margin-top: 1rem; }
        .admin-nav a { color: white; text-decoration: none; }
        .admin-nav a:hover { text-decoration: underline; }
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
        .card { border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 1.5rem; text-align: center; } */
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container-fluid">
            <h1 class="text-white">Admin Dashboard</h1>
            <nav class="admin-nav">
                <a href="<?php echo SITE_URL; ?>admin/index.php">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>admin/products/">Products</a>
                <a href="<?php echo SITE_URL; ?>admin/orders/">Orders</a>
                <a href="<?php echo SITE_URL; ?>admin/users/">Users</a>
                <a href="<?php echo SITE_URL; ?>admin/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container-fluid py-4">