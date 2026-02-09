<?php
$page_title = "Manage Orders";
require_once './../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Order.php';

$database = new Database();
$db = $database->connect();
$order_manager = new Order($db);

// Get filter status
$status_filter = $_GET['status'] ?? 'all';
$valid_statuses = ['all', 'pending', 'processing', 'shipped', 'delivered', 'cancelled'];

if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

// Fetch orders
if ($status_filter === 'all') {
    $stmt = $db->query("SELECT o.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        ORDER BY o.order_date DESC");
} else {
    $stmt = $db->prepare("SELECT o.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
                          FROM orders o 
                          JOIN customers c ON o.customer_id = c.id 
                          WHERE o.order_status = :status 
                          ORDER BY o.order_date DESC");
    $stmt->execute([':status' => $status_filter]);
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Orders</h1>
    <p>Manage customer orders</p>
</div>

<div class="content-card">
    <div class="card-header">
        <div style="display: flex; gap: 0.5rem;">
            <a href="?status=all" class="btn btn-sm <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
            <a href="?status=pending" class="btn btn-sm <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
            <a href="?status=processing" class="btn btn-sm <?php echo $status_filter === 'processing' ? 'btn-primary' : 'btn-secondary'; ?>">Processing</a>
            <a href="?status=delivered" class="btn btn-sm <?php echo $status_filter === 'delivered' ? 'btn-primary' : 'btn-secondary'; ?>">Delivered</a>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        <?php if (count($orders) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <?php 
                        $status_colors = [
                            'pending' => 'var(--warning)',
                            'processing' => 'var(--secondary)',
                            'shipped' => '#8b5cf6',
                            'delivered' => 'var(--success)',
                            'cancelled' => 'var(--danger)'
                        ];
                        $color = $status_colors[$order['order_status']] ?? 'var(--gray-500)';
                        ?>
                        <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.75rem; background: <?php echo $color; ?>20; color: <?php echo $color; ?>; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                            <span style="width: 6px; height: 6px; background: <?php echo $color; ?>; border-radius: 50%;"></span>
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <h3>No orders found</h3>
            <p>No orders match the selected filter.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>