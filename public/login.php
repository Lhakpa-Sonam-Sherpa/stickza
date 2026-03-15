<?php
// PHP logic remains the same...
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . '/src/classes/Database.php';
require_once ROOT_PATH . '/src/classes/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$db = $database->connect();
$user = new User($db);

$message = '';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'public/index.php');
    exit();
}

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 900;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    $email = trim($_POST['email'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'];
    $lockout_key = 'login_lockout_' . md5($email . $ip);

    if (isset($_SESSION[$lockout_key])) {
        $attempts = $_SESSION[$lockout_key]['attempts'];
        $last_attempt_time = $_SESSION[$lockout_key]['time'];

        if ($attempts >= MAX_LOGIN_ATTEMPTS && (time() - $last_attempt_time) < LOCKOUT_TIME) {
            $remaining_time = ceil((LOCKOUT_TIME - (time() - $last_attempt_time)) / 60);
            $message = '<div class="alert alert-error">Too many login attempts. Please try again in ' . $remaining_time . ' minutes.</div>';
            goto render_page;
        } elseif ((time() - $last_attempt_time) >= LOCKOUT_TIME) {
            unset($_SESSION[$lockout_key]);
        }
    }

    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = '<div class="alert alert-error">Please enter both email and password.</div>';
    } else {
        $user_id = $user->login($email, $password);
        
        if ($user_id) {
            unset($_SESSION[$lockout_key]);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['login_time'] = time();
            session_regenerate_id(true);
            
            $redirect = $_SESSION['redirect_to'] ?? SITE_URL . 'public/index.php';
            unset($_SESSION['redirect_to']);
            
            header('Location: ' . $redirect);
            exit();
        } else {
            if (!isset($_SESSION[$lockout_key])) {
                $_SESSION[$lockout_key] = ['attempts' => 1, 'time' => time()];
            } else {
                $_SESSION[$lockout_key]['attempts']++;
            }
            $message = '<div class="alert alert-error">Invalid email or password. Please try again.</div>';
            error_log("Login failed for email: $email from IP: $ip");
        }
    }
}

render_page:
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
        /* Redesigned Auth Page Styles */
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-secondary);
            padding: 1rem;
        }
        .auth-card {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-md);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .auth-header .logo svg {
            width: 32px;
            height: 32px;
            stroke: var(--primary);
        }
        .auth-header p {
            color: var(--text-secondary);
        }
        .auth-form .form-group {
            margin-bottom: 1.25rem;
        }
        .auth-form label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .password-wrapper {
            position: relative;
        }
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
        .password-toggle:hover {
            color: var(--text-primary);
        }
        .password-toggle svg {
            width: 18px;
            height: 18px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        .auth-footer a {
            color: var(--primary);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?php echo SITE_URL; ?>public/" class="logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Stickza</span>
                </a>
                <p>Sign in to your account</p>
            </div>
            
            <?php echo $message; ?>
            
            <form action="<?php echo SITE_URL; ?>public/login.php" method="POST" class="auth-form">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
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
                
                <div style="text-align:right; margin-top:-0.75rem; margin-bottom: 1.5rem;">
                    <a href="forgot_password.php" style="font-size:0.8125rem; color:var(--text-muted);">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="<?php echo SITE_URL; ?>public/register.php">Create one</a>
            </div>
        </div>
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
