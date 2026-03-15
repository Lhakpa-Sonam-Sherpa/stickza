<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/User.php';
require_once ROOT_PATH . 'src/classes/Order.php';

// Start session and check login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);
$order_manager = new Order($db);

// Fetch user data
$user = $user_manager->findById($_SESSION['user_id']);

if (!$user) {
    session_destroy();
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

// --- PAGINATION LOGIC ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$limit = 5; // Orders per page, reduced for more detail per order
$order_data = $order_manager->getCustomerOrders($_SESSION['user_id'], $page, $limit);
$orders = $order_data['orders'];
$total_orders = $order_data['total'];
$total_pages = ceil($total_orders / $limit);

// Get user initials for avatar
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Get theme
$theme = getCurrentTheme();

$page_title = 'My Profile';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
    .profile-container { max-width: 1200px; margin: 0 auto; }
    .profile-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-lg);
    padding: 2rem 2.5rem; /* Reduced padding */
    color: white;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
   .profile-header::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -15%;
    width: 350px;
    height: 350px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    opacity: 0.8;
}
    .profile-header-content { display: flex; align-items: center; gap: 2rem; position: relative; z-index: 1; }
    .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: var(--primary); border: 4px solid rgba(255,255,255,0.3); flex-shrink: 0; }
    .profile-info h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
    .profile-info p {
    opacity: 0.85;
    font-size: 1rem; /* Slightly smaller email */
    word-break: break-all;
}
    .profile-stats {
    display: flex;
    gap: 1.75rem; /* Adjusted gap */
    margin-top: 1rem; /* Reduced margin */
}
    .profile-stat { text-align: center; }
    .profile-stat-value {
    font-size: 1.25rem; /* Smaller stat value */
    font-weight: 600;
}
    .profile-stat-label {
    font-size: 0.75rem; /* Smaller stat label */
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
    .profile-grid { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; }
    .profile-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
    .profile-card { background: var(--bg-primary); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow); border: 1px solid var(--border); }
    .profile-card h3 { font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
    .profile-card h3 svg { width: 20px; height: 20px; color: var(--primary); }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 0; border-bottom: 1px solid var(--border); }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 0.875rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; }
    .info-value { font-weight: 500; color: var(--text-primary); text-align: right; }
    .profile-menu { list-style: none; }
    .profile-menu li { margin-bottom: 0.25rem; }
    .profile-menu a { display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: var(--radius); color: var(--text-secondary); font-size: 0.9375rem; font-weight: 500; transition: all 0.2s; }
    .profile-menu a:hover, .profile-menu a.active { background: var(--primary-light); color: var(--primary); }
    .profile-menu a svg { width: 20px; height: 20px; }
    .profile-main { display: flex; flex-direction: column; gap: 1.5rem; }
    .orders-section h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem; }
    .order-card { background: var(--bg-primary); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 1.5rem; }
    .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
    .order-id { font-weight: 600; color: var(--text-primary); }
    .order-date { font-size: 0.875rem; color: var(--text-muted); }
    .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); }
    .order-total { font-size: 1.25rem; font-weight: 700; color: var(--primary); }
    .empty-orders { text-align: center; padding: 4rem 2rem; color: var(--text-secondary); }
    .empty-orders svg { width: 80px; height: 80px; margin-bottom: 1.5rem; color: var(--text-muted); }
    .empty-orders h3 { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; }
    .pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 2rem; }
    .pagination a, .pagination span { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; font-size: 0.875rem; }
    .pagination a { background: var(--bg-tertiary); color: var(--text-secondary); border: 1px solid var(--border); }
    .pagination a:hover { background: var(--bg-secondary); border-color: var(--border-strong); }
    .pagination span.current { background: var(--primary); color: white; font-weight: 600; border: 1px solid var(--primary); }
    
    /* Order Tracking Timeline */
    .order-timeline { display: flex; justify-content: space-between; margin-bottom: 1.5rem; }
    .timeline-step { text-align: center; flex: 1; position: relative; }
    .timeline-step .icon { width: 40px; height: 40px; border-radius: 50%; background: var(--bg-tertiary); border: 2px solid var(--border); color: var(--text-muted); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem; transition: all 0.3s; }
    .timeline-step .label { font-size: 0.75rem; font-weight: 500; color: var(--text-muted); }
    .timeline-step.completed .icon { background: var(--success-light); border-color: var(--success); color: var(--success); }
    .timeline-step.active .icon { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
    .timeline-step:not(:last-child)::after { content: ''; position: absolute; top: 20px; left: 50%; width: 100%; height: 2px; background: var(--border); transform: translateY(-50%); z-index: -1; }
    .timeline-step.completed:not(:last-child)::after { background: var(--success); }
    
    .delivery-info { text-align: center; font-size: 0.875rem; color: var(--text-secondary); background: var(--bg-tertiary); padding: 0.75rem; border-radius: var(--radius); }

    @media (max-width: 968px) {
        .profile-grid { grid-template-columns: 1fr; } 
        .profile-header-content {
        flex-direction: column;
        text-align: center;}
        .profile-stats {
            justify-content: center;
    } }
    @media (max-width: 640px) {
    .profile-header {
        padding: 1.5rem; /* Further reduced padding for mobile */
    }
    .profile-avatar {
        width: 70px; /* Even smaller avatar for mobile */
        height: 70px;
        font-size: 1.5rem;
        border-width: 2px;
    }
    .profile-info h1 {
        font-size: 1.375rem; /* Smaller name for mobile */
    }
    .profile-info p {
        font-size: 0.9375rem; /* Smaller email for mobile */
    }
    .profile-stats {
        gap: 1.25rem;
    }
    .profile-stat-value {
        font-size: 1.125rem;
    }
}
</style>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-avatar"><?php echo $initials; ?></div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="profile-stats">
                    <div class="profile-stat"><div class="profile-stat-value"><?php echo $total_orders; ?></div><div class="profile-stat-label">Total Orders</div></div>
                    <div class="profile-stat"><div class="profile-stat-value"><?php echo htmlspecialchars($user['city']); ?></div><div class="profile-stat-label">City</div></div>
                    <div class="profile-stat"><div class="profile-stat-value">Member</div><div class="profile-stat-label">Since <?php echo date('Y', strtotime($user['created_at'])); ?></div></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="profile-grid">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <ul class="profile-menu">
                    <li><a href="#" class="active"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>My Orders</a></li>
                    <li><a href="<?php echo SITE_URL; ?>public/settings.php"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Settings</a></li>
                    <li><a href="<?php echo SITE_URL; ?>public/logout.php"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Logout</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="profile-main">
            <div class="orders-section">
                <h2>Order History</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-orders profile-card">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="<?php echo SITE_URL; ?>public/" class="btn btn-primary" style="margin-top: 1.5rem;">Browse Products</a>
                    </div>
                <?php else: ?>
                    <?php 
                    $statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
                    foreach ($orders as $order): 
                        $current_status_index = array_search($order['order_status'], $statuses);
                        if ($current_status_index === false) $current_status_index = -1;
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-id">Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                    <div class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                                </div>
                                <div class="order-total">Rs <?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>

                            <!-- Order Timeline -->
                            <div class="order-timeline">
                                <?php foreach ($statuses as $index => $status): ?>
                                    <div class="timeline-step <?php echo ($index < $current_status_index) ? 'completed' : ''; ?> <?php echo ($index === $current_status_index) ? 'active' : ''; ?>">
                                        <div class="icon">
                                            <?php if ($index <= $current_status_index): ?>
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="label"><?php echo ucfirst($status); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="delivery-info">
                                <?php if ($order['order_status'] === 'delivered'): ?>
                                    <strong>Delivered!</strong> Thank you for your order.
                                <?php elseif ($order['order_status'] === 'cancelled'): ?>
                                    <strong>Order Cancelled.</strong>
                                <?php else: ?>
                                    Estimated Delivery: <strong><?php echo date('F j, Y', strtotime($order['order_date'] . ' +3 days')); ?></strong>
                                <?php endif; ?>
                            </div>

                            <div class="order-footer">
                                <span style="color: var(--text-muted); font-size: 0.875rem;"><?php echo $order['item_count'] ?? '1'; ?> item(s)</span>
                                <a href="<?php echo SITE_URL; ?>public/feedback.php" class="btn btn-secondary btn-sm">Contact Support</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- PAGINATION BUTTONS -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?><a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a><?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?><span class="current"><?php echo $i; ?></span>
                            <?php else: ?><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a><?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?><a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
