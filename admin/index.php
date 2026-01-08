<?php
require_once 'includes/auth_check.php';

require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$admin_id = $_SESSION['admin_id'];
$admin_details = $admin->findById($admin_id);
$stats = $admin->getDashboardStats();

$page_title = "Dashboard";
include 'includes/header.php';
?>

<div class="dashboard-cards">
    <div class="card border-primary">
        <h5 class="card-header bg-primary text-white">Total Products</h5>
        <div class="card-body">
            <h2 class="display-4 fw-bold"><?php echo $stats['total_products']; ?></h2>
            <a href="products/" class="btn btn-outline-primary mt-3">Manage Products</a>
        </div>
    </div>
    
    <div class="card border-success">
        <h5 class="card-header bg-success text-white">Total Orders</h5>
        <div class="card-body">
            <h2 class="display-4 fw-bold"><?php echo $stats['total_orders']; ?></h2>
            <a href="orders/" class="btn btn-outline-success mt-3">View Orders</a>
        </div>
    </div>
    
    <div class="card border-info">
        <h5 class="card-header bg-info text-white">Total Users</h5>
        <div class="card-body">
            <h2 class="display-4 fw-bold"><?php echo $stats['total_users']; ?></h2>
            <a href="users/" class="btn btn-outline-info mt-3">Manage Users</a>
        </div>
    </div>
    
    <div class="card border-warning">
        <h5 class="card-header bg-warning text-white">Revenue</h5>
        <div class="card-body">
            <h2 class="display-4 fw-bold">$<?php echo number_format($stats['total_revenue'], 2); ?></h2>
            <a href="orders/?status=delivered" class="btn btn-outline-warning mt-3">View Revenue</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>