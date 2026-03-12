<?php
/**
 * public/forgot_password.php
 *
 * Step 1 of the password-reset flow:
 * • User enters their email address
 * • A 64-char cryptographic token is generated (1-hour expiry)
 * • An email is sent with a link to reset_password.php?token=…
 *
 * Note: Requires the `password_resets` table (see database_migrations.sql).
 *       Configure PHP mail() or a mail library (e.g. PHPMailer) in production.
 */

require_once __DIR__ . '/../src/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . 'vendor/autoload.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in? Redirect away
if (isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'public/profile.php');
    exit();
}

// Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$database = new Database();
$db       = $database->connect();
$admin    = new Admin($db);

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF
    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Always show success to prevent email enumeration
            $token = $admin->createPasswordResetToken($email);

            if ($token) {
                $reset_link = SITE_URL . 'public/reset_password.php?token=' . urlencode($token);

                // Build a simple plain-text email
                $subject  = 'Stickza - Password Reset Request';
                $body     = "Hello,\n\n"
                          . "We received a request to reset the password for your Stickza account.\n\n"
                          . "Click the link below to set a new password (expires in 1 hour):\n\n"
                          . $reset_link . "\n\n"
                          . "If you did not request this, you can safely ignore this email.\n\n"
                          . "— Stickza Team";
                
                $mail = new PHPMailer(true);

                try {
                    // Use constants from config.php
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USER;
                    $mail->Password = SMTP_PASS;
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = SMTP_PORT;

                    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                    $mail->addAddress($email);

                    $mail->Subject = $subject;
                    $mail->Body = $body;

                    $mail->send();

                } catch (Exception $e) {
                    // Log error instead of echoing to user
                    error_log("Mailer Error: {$mail->ErrorInfo}");
                }

                error_log("Password reset token generated for $email: $reset_link");
            }

            // Always show same message (security: don't reveal if email exists)
            $success = "If an account exists for that email, we've sent password reset instructions. "
                     . "Please check your inbox (and spam folder).";
        }
    }
}

$theme      = getCurrentTheme();
$page_title = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> – Stickza</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>public/css/style.css">
    <style>
        .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .auth-card { background: var(--bg-primary ); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: var(--shadow-md); }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header .back-link { font-size: 0.875rem; color: var(--text-muted); }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-primary); margin-bottom: 0.5rem; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">

        <div class="auth-header">
            <a href="<?php echo SITE_URL; ?>public/" class="logo"
               style="justify-content:center; display:flex; align-items:center; gap:0.5rem;
                      font-size:1.5rem; font-weight:700; color:var(--text-primary); margin-bottom:1.25rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" width="32" height="32">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Stickza
            </a>
            <h1 style="font-size:1.375rem; font-weight:700; margin-bottom:0.375rem;">Forgot Password?</h1>
            <p style="color:var(--text-secondary); font-size:0.875rem;">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1.25rem; font-size:0.875rem;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom:1.25rem; font-size:0.875rem;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php else: ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="you@example.com" required autofocus autocomplete="email"
                       style="width:100%; padding:0.875rem; font-size:0.9375rem;">
            </div>

            <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:0.9rem; font-size:1rem; margin-top:0.25rem;">
                Send Reset Link
            </button>
        </form>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1.5rem; padding-top:1.25rem;
                    border-top:1px solid var(--border); font-size:0.875rem; color:var(--text-secondary);">
            Remember your password?
            <a href="<?php echo SITE_URL; ?>public/login.php" style="color:var(--primary); font-weight:500;">
                Sign In
            </a>
        </div>

    </div>
</div>
</body>
</html>
