<?php
$page_title = "Manage Users";
require_once './../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/User.php';

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);

// Fetch all non-admin users
$stmt = $db->query("SELECT id, first_name, last_name, email, city, phone_no, created_at 
                    FROM customers 
                    WHERE is_admin = 0 
                    ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Users</h1>
    <p>Manage customer accounts</p>
</div>

<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">All Customers</h2>
        <span class="btn btn-sm btn-secondary"><?php echo count($users); ?> total</span>
    </div>
    
    <div class="card-body" style="padding: 0;">
        <?php if (count($users) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>City</th>
                    <th>Phone</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td>
                        <div class="product-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['city']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone_no'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <h3>No users found</h3>
            <p>No customer accounts have been created yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>