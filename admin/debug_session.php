<?php
require_once './admin_init.php';
echo '<h2>Session Debug</h2>';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

echo '<h2>Database Check</h2>';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$db = (new Database())->connect();
$admin = new Admin($db);

if (isset($_SESSION['admin_id'])) {
    $admin_data = $admin->findAdminById($_SESSION['admin_id']);
    echo '<h3>Admin Data:</h3>';
    print_r($admin_data);
} else {
    echo '<p>No admin_id in session</p>';
}

// Check database connection
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM customers WHERE is_admin = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<h3>Admin Count:</h3>';
    print_r($result);
} catch (Exception $e) {
    echo '<h3 style="color:red">Database Error:</h3>';
    echo htmlspecialchars($e->getMessage());
}