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

// Fetch user orders
$orders = $order_manager->getCustomerOrders($_SESSION['user_id']);

// Get user initials for avatar
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Get theme
$theme = getCurrentTheme();

$page_title = 'My Profile';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: var(--radius-lg);
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    .profile-header-content {
        display: flex;
        align-items: center;
        gap: 2rem;
        position: relative;
        z-index: 1;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        border: 4px solid rgba(255,255,255,0.3);
        flex-shrink: 0;
    }
    
    .profile-info h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .profile-info p {
        opacity: 0.9;
        font-size: 1.1rem;
    }
    
    .profile-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .profile-stat {
        text-align: center;
    }
    
    .profile-stat-value {
        font-size: 1.75rem;
        font-weight: 700;
    }
    
    .profile-stat-label {
        font-size: 0.875rem;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .profile-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
    }
    
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .profile-card {
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }
    
    .profile-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .profile-card h3 svg {
        width: 20px;
        height: 20px;
        color: var(--primary);
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.875rem 0;
        border-bottom: 1px solid var(--border);
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-size: 0.875rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-value {
        font-weight: 500;
        color: var(--text-primary);
        text-align: right;
    }
    
    .profile-menu {
        list-style: none;
    }
    
    .profile-menu li {
        margin-bottom: 0.25rem;
    }
    
    .profile-menu a {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        border-radius: var(--radius);
        color: var(--text-secondary);
        font-size: 0.9375rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .profile-menu a:hover,
    .profile-menu a.active {
        background: var(--primary-light);
        color: var(--primary);
    }
    
    .profile-menu a svg {
        width: 20px;
        height: 20px;
    }
    
    .profile-main {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .orders-section h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
    }
    
    .order-card {
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        margin-bottom: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }
    
    .order-id {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .order-date {
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    
    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }
    
    .order-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
    }
    
    .empty-orders {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-secondary);
    }
    
    .empty-orders svg {
        width: 80px;
        height: 80px;
        margin-bottom: 1.5rem;
        color: var(--text-muted);
    }
    
    .empty-orders h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    @media (max-width: 968px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
        
        .profile-header-content {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-stats {
            justify-content: center;
        }
    }
    
    @media (max-width: 640px) {
        .profile-header {
            padding: 2rem 1.5rem;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            font-size: 1.75rem;
        }
        
        .profile-info h1 {
            font-size: 1.5rem;
        }
    }
</style>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-avatar">
                <?php echo $initials; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="profile-stat-value"><?php echo count($orders); ?></div>
                        <div class="profile-stat-label">Orders</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value"><?php echo htmlspecialchars($user['city']); ?></div>
                        <div class="profile-stat-label">City</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">Member</div>
                        <div class="profile-stat-label">Since <?php echo date('Y'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="profile-grid">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <!-- Navigation Menu -->
            <div class="profile-card">
                <ul class="profile-menu">
                    <li>
                        <a href="#" class="active">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            My Orders
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>public/logout.php">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="profile-card">
                <h3>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Contact Information
                </h3>
                <div class="info-row">
                    <span class="info-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email
                    </span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Phone
                    </span>
                    <span class="info-value"><?php echo !empty($user['phone_no']) ? htmlspecialchars($user['phone_no']) : 'Not added'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Address
                    </span>
                    <span class="info-value" style="max-width: 150px;"><?php echo htmlspecialchars($user['address']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        City
                    </span>
                    <span class="info-value"><?php echo htmlspecialchars($user['city']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="profile-main">
            <div class="orders-section">
                <h2>Order History</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-orders">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="<?php echo SITE_URL; ?>public/" class="btn btn-primary" style="margin-top: 1.5rem;">Browse Products</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): 
                        $statusColors = [
                            'pending' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Pending'],
                            'processing' => ['bg' => '#cffafe', 'text' => '#155e75', 'label' => 'Processing'],
                            'shipped' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'label' => 'Shipped'],
                            'delivered' => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Delivered'],
                            'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Cancelled']
                        ];
                        $status = $statusColors[$order['order_status']] ?? $statusColors['pending'];
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <div class="order-id">Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                    <div class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                                </div>
                                <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: <?php echo $status['bg']; ?>; color: <?php echo $status['text']; ?>;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                    <?php echo $status['label']; ?>
                                </span>
                            </div>
                            <div class="order-footer">
                                <span style="color: var(--text-muted); font-size: 0.875rem;">
                                    <?php echo $order['item_count'] ?? '1'; ?> item(s)
                                </span>
                                <div class="order-total">
                                    Rs <?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>