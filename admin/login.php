<?php
require_once '../src/config.php';
require_once '../src/classes/Database.php';
require_once '../src/classes/Admin.php';

initSecureSession();

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

if (isset($_SESSION['admin_id'])) {
    header('Location: '.SITE_URL.'admin/index.php');
    exit();
}

$error = '';
$success = '';

if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $success = "You have been logged out successfully.";
}
if (isset($_GET['error']) && $_GET['error'] === 'invalid_session') {
    $error = "Your session expired. Please login again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $admin_id = $admin->login($email, $password);
        
        if (is_int($admin_id)) {
            $_SESSION['admin_id'] = $admin_id;
            session_regenerate_id(true);
            header('Location: ' . SITE_URL . 'admin/index.php');
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Stickza</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>admin/css/admin.css">
    <style>
        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            background: none;
            border: none;
            padding: 0.25rem;
        }
        .password-toggle:hover { color: var(--text-primary); }
        .password-toggle svg { width: 18px; height: 18px; }
    </style>
</head>
<body class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Stickza Admin
            </div>
            <h1>Admin Login</h1>
            <p>Sign in to access the admin panel</p>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="POST" class="auth-form">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@example.com" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                        <svg class="eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="eye-closed" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .95-3.11 3.8-5.448 7.29-6.101M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.938 21.938l-14-14"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Sign In</button>
        </form>
    </div>
    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const toggle = input.nextElementSibling;
            const eyeOpen = toggle.querySelector('.eye-open');
            const eyeClosed = toggle.querySelector('.eye-closed');
            if (input.type === "password") {
                input.type = "text";
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = "password";
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>
</body>
</html>
