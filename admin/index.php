<?php
require_once 'admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$admin_id = $_SESSION['admin_id'];
$admin_details = $admin->findAdminById($admin_id);
$stats = $admin->getDashboardStats();

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Welcome back, <?php echo htmlspecialchars($admin_details['first_name']); ?>! 👋</h1>
    <p>Here's what's happening with your store today.</p>
</div>

<div class="stats-grid">
    <!-- Products Card -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Products</span>
            <div class="stat-icon products">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
        <a href="products/index.php" class="stat-link">
            Manage Products →
        </a>
    </div>

    <!-- Orders Card -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Orders</span>
            <div class="stat-icon orders">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
        <a href="orders/index.php" class="stat-link">
            View Orders →
        </a>
    </div>

    <!-- Users Card -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Users</span>
            <div class="stat-icon users">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
        <a href="users/index.php" class="stat-link">
            Manage Users →
        </a>
    </div>

    <!-- Revenue Card -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Revenue</span>
            <div class="stat-icon revenue">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
        <a href="orders/index.php?status=delivered" class="stat-link">
            View Revenue →
        </a>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Quick Actions</h2>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="products/add.php" class="btn btn-primary">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add New Product
            </a>
            <a href="orders/index.php" class="btn btn-secondary">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                View Recent Orders
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>