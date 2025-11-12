<?php
// sticker-shop/public/profile.php

require_once __DIR__.'/../src/config.php';
require_once ROOT_PATH.'src/classes/Database.php';
require_once ROOT_PATH.'src/classes/User.php';
require_once ROOT_PATH.'src/classes/Order.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: '.SITE_URL.'public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);
$order_manager = new Order($db);

// Fetch user data
$user = $user_manager->findById($_SESSION['user_id']);

if (!$user) {
    // Should not happen if login worked, but good for safety
    session_destroy();
    header('Location: '.SITE_URL.'public/login.php');
    exit();
}

// Fetch user orders
$orders = $order_manager->getCustomerOrders($_SESSION['user_id']);

include ROOT_PATH . 'src/includes/header.php';
?>

<h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?></h1>

<h2>Your Profile Details</h2>
<div class="profile-details">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
    <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_no']); ?></p>
</div>

<h2>Order History</h2>
<?php if (empty($orders)): ?>
    <p>You have not placed any orders yet.</p>
<?php else: ?>
    <table class="order-history-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($order['order_status'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>
