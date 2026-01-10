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

include 'includes/header.php';
?>

<div>
    <div >
        <h5 >Total Products</h5>
        <div >
            <h2 ><?php echo $stats['total_products']; ?></h2>
            <a href="products/index.php" >Manage Products</a>
        </div>
    </div>
    
    <div>
        <h5>Total Orders</h5>
        <div>
            <h2><?php echo $stats['total_orders']; ?></h2>
            <a href="orders/" >View Orders</a>
        </div>
    </div>
    
    <div>
        <h5>Total Users</h5>
        <div>
            <h2><?php echo $stats['total_users']; ?></h2>
            <a href="users/" >Manage Users</a>
        </div>
    </div>
    
    <div>
        <h5>Revenue</h5>
        <div>
            <h2>$<?php echo number_format($stats['total_revenue'], 2); ?></h2>
            <a href="orders/?status=delivered" >View Revenue</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>