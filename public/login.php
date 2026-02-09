<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . '/src/classes/Database.php';
require_once ROOT_PATH . '/src/classes/User.php';

// Start session BEFORE any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$db = $database->connect();
$user = new User($db);

$message = '';

// DEBUG: Check if already logged in
if (isset($_SESSION['user_id'])) {
    // Already logged in, redirect to home
    header('Location: ' . SITE_URL . 'public/index.php');
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // DEBUG: Check if form data is received
    if (empty($email) || empty($password)) {
        $message = '<div class="alert alert-error">
            <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            Please enter both email and password.
        </div>';
    } else {
        // Attempt login
        $user_id = $user->login($email, $password);
        
        // DEBUG: Check what login returned
        if ($user_id) {
            // SUCCESS - Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['login_time'] = time();
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Determine redirect
            $redirect = SITE_URL . 'public/index.php';
            if (isset($_SESSION['redirect_to']) && !empty($_SESSION['redirect_to'])) {
                // Validate redirect URL is local
                $redirect_url = $_SESSION['redirect_to'];
                if (strpos($redirect_url, SITE_URL) === 0) {
                    $redirect = $redirect_url;
                }
                unset($_SESSION['redirect_to']);
            }
            
            // DEBUG: Log success (check your error logs)
            error_log("Login successful for user ID: $user_id, redirecting to: $redirect");
            
            // Redirect
            header('Location: ' . $redirect);
            exit();
        } else {
            // FAILED
            $message = '<div class="alert alert-error">
                <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                Invalid email or password. Please try again.
            </div>';
            
            // DEBUG
            error_log("Login failed for email: $email");
        }
    }
}

$theme = getCurrentTheme();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stickza</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>public/css/style.css">
    <style>
        .auth-form .form-group { margin-bottom: 1.5rem; }
        .auth-form label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary); }
        .auth-form input { width: 100%; padding: 0.875rem 1rem; font-size: 1rem; }
        .auth-form button { width: 100%; padding: 1rem; font-size: 1rem; margin-top: 0.5rem; }
        .auth-footer { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); text-align: center; }
    </style>
</head>
<body>
    <!-- DEBUG INFO (remove after fixing) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div style="background: yellow; padding: 10px; text-align: center;">
        DEBUG: Already logged in as user <?php echo $_SESSION['user_id']; ?> - 
        <a href="logout.php">Logout first</a>
    </div>
    <?php endif; ?>

    <div class="auth-container">
        <div class="auth-card" style="max-width: 420px; padding: 2.5rem;">
            <div class="auth-header" style="margin-bottom: 2rem;">
                <a href="<?php echo SITE_URL; ?>public/" class="logo" style="justify-content: center; margin-bottom: 1.5rem; font-size: 2rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    Stickza
                </a>
                <p style="color: var(--text-secondary);">Sign in to your account</p>
            </div>
            
            <?php echo $message; ?>
            
            <form action="<?php echo SITE_URL; ?>public/login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required autofocus 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="<?php echo SITE_URL; ?>public/register.php">Create one</a>
            </div>
        </div>
    </div>
    
    <!-- Theme Toggle -->
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle dark mode">
        <svg class="sun-icon" style="display: <?php echo $theme === 'dark' ? 'none' : 'block'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg class="moon-icon" style="display: <?php echo $theme === 'dark' ? 'block' : 'none'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>
    
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            document.querySelector('.sun-icon').style.display = newTheme === 'dark' ? 'none' : 'block';
            document.querySelector('.moon-icon').style.display = newTheme === 'dark' ? 'block' : 'none';
            fetch('<?php echo SITE_URL; ?>public/api/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + newTheme
            });
        }
    </script>
</body>
</html>