<?php
require_once '../src/config.php';
require_once '../src/classes/Database.php';
require_once '../src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_id'])) {
    header('Location: '.SITE_URL.'admin/index.php');
    exit();
}

$error = '';
$success = ($_GET['message']??'') === 'logged_out' ? "You have been logged out." : '';
$error = ($_GET['error']??'') === 'invalid_session' ? "Your session expired. Please login again." : '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $admin_id = $admin->login($email, $password);
    
    if ($admin_id) {
        $_SESSION['admin_id'] = $admin_id;

        $redirect = $_SESSION['redirect_to'] ?? SITE_URL . 'admin/index.php';
        unset($_SESSION['redirect_to']);

        if (strpos($redirect, SITE_URL . 'admin/') !== 0) {
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
</head>
<body>
    <div>
        <div>
            <div>
                <div>
                    <div>
                        <h3>Admin login</h3>
                    </div>
                    <div>
                        <?php if ($success): ?>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div>
                                <label for="email">Email address</label>
                                <input type="email" id="email" name="email" 
                                       required autofocus value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div>
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <button type="submit">Sign in</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>