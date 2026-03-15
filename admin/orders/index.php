<?php
$page_title = "Manage Orders";
// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    require_once '../admin_init.php';
    require_once ROOT_PATH . 'src/classes/Database.php';
    require_once ROOT_PATH . 'src/classes/Admin.php';
    $db    = (new Database())->connect();
    $admin = new Admin($db);
    // pass same filters
    $f = $_GET; unset($f['export']);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders-' . date('Y-m-d') . '.csv"');
    echo $admin->exportOrdersCSV($f);
    exit();
}
require_once './../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin_obj = new Admin($db);

// Filters
$filters = [
    'order_id'  => trim($_GET['order_id'] ?? ''),
    'email'     => trim($_GET['email'] ?? ''),
    'status'    => $_GET['status'] ?? 'all',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
];

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

$result = $admin_obj->getOrders($filters, $page, $limit);
$orders = $result['data'];
$total  = $result['total'];
$total_pages = ceil($total / $limit);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Orders</h1>
    <p>Manage and track all customer orders (<?php echo $total; ?> total)</p>
</div>

<!-- Filter Card -->
<div class="content-card card-spacing">
    <div class="card-header"><h2 class="card-title">Filter Orders</h2></div>
    <form method="GET" class="filter-form">
        <div>
            <label class="form-label">Order ID</label>
            <input type="number" name="order_id" class="form-control" placeholder="e.g. 42" value="<?php echo htmlspecialchars($filters['order_id']); ?>">
        </div>
        <div>
            <label class="form-label">Customer Email</label>
            <input type="text" name="email" class="form-control" placeholder="Search email..." value="<?php echo htmlspecialchars($filters['email']); ?>">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <?php
                // Added 'delivered' to the filter options
                $statuses = ['all', 'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
                foreach ($statuses as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $filters['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
        </div>
        <div>
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="index.php" class="btn btn-secondary">Clear</a>
            <a href="?<?php echo http_build_query(array_merge($filters, ['export' => 'csv'] )); ?>" class="btn btn-sm btn-secondary">Export CSV</a>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Orders</h2>
        <span class="meta-text">
            Showing <?php echo count($orders); ?> of <?php echo $total; ?>
        </span>
    </div>
    <div>
        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <h3>No orders found</h3>
            <p>Try adjusting your filters.</p>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $sc = [
                'pending'=>'status-pending',
                'paid'=>'status-processing',
                'processing'=>'status-processing',
                'shipped'=>'status-shipped',
                'delivered'=>'status-delivered',
                'cancelled'=>'status-cancelled'
            ];
            foreach ($orders as $order): 
                $badge = $sc[$order['order_status']] ?? 'status-pending';
            ?>
            <tr>
                <td class="col-id cell-bold">#<?php echo str_pad($order['id'],4,'0',STR_PAD_LEFT); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars($order['customer_email']); ?></td>
                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                <td class="cell-primary">Rs <?php echo number_format($order['total_amount'],2); ?></td>
                <td><span class="status-badge <?php echo $badge; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                <td class="col-actions"><a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary">View</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php
    $qs = http_build_query(array_filter($filters ));
    for ($p = 1; $p <= $total_pages; $p++):
    ?>
    <a href="?<?php echo $qs; ?>&page=<?php echo $p; ?>"
       class="btn btn-sm <?php echo $p === $page ? 'btn-primary' : 'btn-secondary'; ?>">
        <?php echo $p; ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
