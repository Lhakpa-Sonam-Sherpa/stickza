<?php
require_once './admin_init.php'; // Handles session setup

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    $redirect = $_SESSION['redirect_to'] ?? (SITE_URL . 'admin/index.php');
    unset($_SESSION['redirect_to']);
    
    // VALIDATE REDIRECT PATH 
    $admin_base = SITE_URL . 'admin/';
    if (strpos($redirect, $admin_base) !== 0) {
        $redirect = SITE_URL . 'admin/index.php';
    }
    
    header('Location: ' . $redirect);
    exit();
}

require_once '../src/classes/Database.php';
require_once '../src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$error = '';
$success = '';

// Handle logout message
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success = "You have been logged out successfully.";

}

// Handle invalid session message
if (isset($_GET['error']) && $_GET['error'] === 'invalid_session') {
    $error = "Your session expired. Please login again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $admin_id = $admin->login($email, $password);

    if ($admin_id) {
        $_SESSION['admin_id'] = $admin_id;
        
        // Validate redirect URL to prevent open redirects
        $redirect = $_SESSION['redirect_to'] ?? (SITE_URL . 'admin/index.php');
        unset($_SESSION['redirect_to']);
        
        $admin_base = SITE_URL . 'admin/';
        if (strpos($redirect, $admin_base) !== 0) {
            $redirect = SITE_URL . 'admin/index.php';
        }
        
        header('Location: ' . $redirect);
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Stickza</title>
    <style>
        body { background-color: #f8f9fa; height: 100vh; }
        .card { border-radius: 10px; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); }
        .bg-primary { background-color: #435ebe !important; }
    </style>
</head>
<body class="d-flex align-items-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Stickza Admin</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required autofocus value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Sign in</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>