<?php
require_once 'admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';
require_once ROOT_PATH . 'src/classes/Order.php';

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);
$order_manager = new Order($db);

$admin_id = $_SESSION['admin_id'];
$admin_details = $admin->findAdminById($admin_id);
$stats = $admin->getDashboardStats();
$status_breakdown = $admin->getOrderStatusBreakdown();
$sales_analytics = $admin->getSalesAnalytics();
$low_stock = $admin->getLowStockProducts(5);
$best_sellers = $admin->getBestSellingProducts(5);
$recent_orders = $order_manager->getRecentOrders(8);

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<!-- NEW CSS for clickable rows -->
<style>
.clickable-row {
    display: table-row;
    cursor: pointer;
    position: relative;
}
.clickable-row:hover {
    background-color: var(--bg-tertiary);
}
.clickable-row a {
    display: contents; /* Makes the link behave like its container */
    text-decoration: none;
    color: inherit;
}
.clickable-row a::after {
    content: 'Click to edit/restock';
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    background: var(--gray-800);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
}
.clickable-row:hover a::after {
    opacity: 1;
}
</style>


<div class="page-header">
    <h1>Welcome back, <?php echo htmlspecialchars($admin_details['first_name']); ?>! 👋</h1>
    <p>Here's what's happening with your store today.</p>
</div>

<!-- Core Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon products">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-title">Total Products</div>
            <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
            <a href="products/index.php" class="stat-link">Manage Products →</a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orders">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-title">Total Orders</div>
            <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
            <a href="orders/index.php" class="stat-link">View Orders →</a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon users">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-title">Total Users</div>
            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
            <a href="users/index.php" class="stat-link">Manage Users →</a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon revenue">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-title">Total Revenue</div>
            <div class="stat-value">Rs <?php echo number_format($stats['total_revenue'], 2); ?></div>
            <a href="orders/index.php" class="stat-link">View Revenue →</a>
        </div>
    </div>
</div>

<!-- Real-Time Activity Metrics -->
<div class="stats-grid" style="margin-top: -0.5rem;">
    <div class="stat-card" style="border-left: 3px solid var(--info);">
        <div class="stat-icon blue"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="stat-content">
            <div class="stat-title">Orders Today</div>
            <div class="stat-value"><?php echo $stats['orders_today']; ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-left: 3px solid var(--success);">
        <div class="stat-icon green"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
        <div class="stat-content">
            <div class="stat-title">Revenue Today</div>
            <div class="stat-value">Rs <?php echo number_format($stats['revenue_today'], 2); ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-left: 3px solid var(--primary);">
        <div class="stat-icon purple"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></div>
        <div class="stat-content">
            <div class="stat-title">New Users Today</div>
            <div class="stat-value"><?php echo $stats['new_users_today']; ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-left: 3px solid var(--warning);">
        <div class="stat-icon yellow"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <div class="stat-content">
            <div class="stat-title">Orders (Last 24h)</div>
            <div class="stat-value"><?php echo $stats['orders_24h']; ?></div>
        </div>
    </div>
</div>

<!-- Order Status Breakdown + Best Sellers -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Order Status Breakdown -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Order Status Breakdown</h2>
        </div>
        <div style="padding: 1.25rem;">
            <?php
            $status_config = [
                'pending'    => ['label' => 'Pending',    'class' => 'status-pending'],
                'paid'       => ['label' => 'Paid',       'class' => 'status-processing'],
                'processing' => ['label' => 'Processing', 'class' => 'status-processing'],
                'shipped'    => ['label' => 'Shipped',    'class' => 'status-shipped'],
                'cancelled'  => ['label' => 'Cancelled',  'class' => 'status-cancelled'],
            ];
            $total_orders_for_pct = max(array_sum($status_breakdown), 1);
            foreach ($status_config as $key => $cfg):
                $count = $status_breakdown[$key] ?? 0;
                $pct = round($count / $total_orders_for_pct * 100);
            ?>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                    <span class="status-badge <?php echo $cfg['class']; ?>"><?php echo $cfg['label']; ?></span>
                    <span style="font-weight: 600; font-size: 0.875rem;"><?php echo $count; ?></span>
                </div>
                <div style="background: var(--bg-tertiary); border-radius: 99px; height: 6px;">
                    <div style="background: var(--primary); height: 6px; border-radius: 99px; width: <?php echo $pct; ?>%; transition: width 0.3s;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Best Selling Products -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Top Products (Last 30 Days)</h2>
        </div>
        <div style="padding: 0;">
            <?php if (empty($best_sellers)): ?>
            <div class="empty-state" style="padding: 2rem;">
                <p>No sales data yet.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>#</th><th>Product</th><th>Units Sold</th></tr></thead>
                <tbody>
                <?php foreach ($best_sellers as $i => $product): ?>
                <tr>
                    <td style="color: var(--text-muted); font-weight: 600;"><?php echo $i + 1; ?></td>
                    <td style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><span style="font-weight: 600; color: var(--primary);"><?php echo $product['units_sold']; ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Sales Analytics Chart -->
<div class="content-card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Sales Analytics — Last 7 Days</h2>
    </div>
    <div style="padding: 1.5rem;">
        <canvas id="salesChart" height="80"></canvas>
    </div>
</div>

<!-- Recent Orders + Low Stock -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Recent Orders -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Recent Orders</h2>
            <a href="orders/index.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div>
            <?php if (empty($recent_orders)): ?>
            <div class="empty-state"><p>No orders yet.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recent_orders as $order):
                    $sc = [
                        'pending' => 'status-pending', 'paid' => 'status-processing',
                        'processing' => 'status-processing', 'shipped' => 'status-shipped',
                        'delivered' => 'status-delivered', 'cancelled' => 'status-cancelled'
                    ];
                    $badge = $sc[$order['order_status']] ?? 'status-pending';
                ?>
                <tr>
                    <td style="font-weight:600;">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span class="status-badge <?php echo $badge; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td><a href="orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary">View</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low Stock Warning -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">⚠️ Low Stock</h2>
        </div>
        <div>
            <?php if (empty($low_stock)): ?>
            <div class="empty-state" style="padding: 2rem;">
                <p>All products are well-stocked!</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                <tbody>
                <?php foreach ($low_stock as $p): ?>
                <!-- Each row is now a clickable link -->
                <tr class="clickable-row">
                        <td style="font-weight: 500; color: var(--text-primary);"><a href="products/edit.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a></td>
                        <td>
                            <?php if ($p['stock_quantity'] == 0): ?>
                            <span class="stock-badge stock-out">Out of Stock</span>
                            <?php else: ?>
                            <span class="stock-badge stock-low"><?php echo $p['stock_quantity']; ?> left</span>
                            <?php endif; ?>
                        </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="content-card">
    <div class="card-header"><h2 class="card-title">Quick Actions</h2></div>
    <div class="card-body" style="padding: 1.25rem;">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="products/add.php" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </a>
            <a href="orders/index.php" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                View Orders
            </a>
            <a href="users/index.php" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Manage Users
            </a>
            <a href="feedback/index.php" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                View Feedback
            </a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const analyticsData = <?php echo json_encode($sales_analytics); ?>;
const labels = analyticsData.map(d => {
    const dt = new Date(d.day);
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});
const revenues = analyticsData.map(d => parseFloat(d.revenue));
const orderCounts = analyticsData.map(d => parseInt(d.order_count));

const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
const textColor = isDark ? '#b8b4ae' : '#7c7770';

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Revenue (Rs)',
                data: revenues,
                backgroundColor: 'rgba(196, 112, 90, 0.7)',
                borderColor: '#c4705a',
                borderWidth: 1,
                borderRadius: 4,
                yAxisID: 'y'
            },
            {
                label: 'Orders',
                data: orderCounts,
                type: 'line',
                borderColor: '#5a7a9a',
                backgroundColor: 'rgba(90, 122, 154, 0.1)',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.3,
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: textColor, font: { size: 12 } } }
        },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor } },
            y: {
                type: 'linear', position: 'left',
                grid: { color: gridColor }, ticks: { color: textColor },
                title: { display: true, text: 'Revenue (Rs)', color: textColor }
            },
            y1: {
                type: 'linear', position: 'right',
                grid: { drawOnChartArea: false }, ticks: { color: textColor },
                title: { display: true, text: 'Orders', color: textColor }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
