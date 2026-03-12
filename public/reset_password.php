<?php
/**
 * public/reset_password.php
 *
 * Step 2 of the password-reset flow:
 * • Validates the one-time token from the URL (?token=…)
 * • Shows a form to enter + confirm a new password
 * • On valid submission: hashes the new password, updates DB, marks token used
 * • Requires password_resets table (see database_migrations.sql)
 */

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

if (session_status() === PHP_SESSION_NONE) session_start();

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

$token       = trim($_GET['token'] ?? '');
$token_valid = false;
$token_row   = null;
$error       = '';
$success     = '';

// ── Validate token from URL ──────────────────────────────────────────────────
if (empty($token)) {
    $error = 'No reset token provided. Please request a new password reset.';
} else {
    $token_row = $admin->findPasswordResetToken($token);
    if (!$token_row) {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    } else {
        $token_valid = true;
    }
}

// ── POST: set new password ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {

    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        $new_password     = $_POST['new_password']     ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (mb_strlen($new_password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            if ($admin->consumePasswordResetToken($token, $new_password)) {
                $success     = 'Your password has been reset successfully! You can now sign in.';
                $token_valid = false; // hide the form
            } else {
                $error = 'Failed to reset password. The link may have expired. Please try again.';
            }
        }
    }
}

$theme      = getCurrentTheme();
$page_title = 'Reset Password';
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
        .auth-card { background: var(--bg-primary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: var(--shadow-md); }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-primary); margin-bottom: 0.5rem; }
        .strength-bar { height: 4px; border-radius: 2px; margin-top: 0.375rem; background: var(--bg-tertiary); overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 2px; transition: width 0.25s, background 0.25s; }
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
            <h1 style="font-size:1.375rem; font-weight:700; margin-bottom:0.375rem;">Set New Password</h1>
            <?php if ($token_valid && $token_row): ?>
            <p style="color:var(--text-secondary); font-size:0.875rem;">
                Resetting password for <strong><?php echo htmlspecialchars($token_row['email']); ?></strong>
            </p>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1.25rem; font-size:0.875rem;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem; font-size:0.875rem;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <div style="text-align:center;">
            <a href="<?php echo SITE_URL; ?>public/login.php" class="btn btn-primary"
               style="display:inline-block; padding:0.75rem 2rem;">
                Sign In Now
            </a>
        </div>

        <?php elseif ($token_valid): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password"
                       class="form-control" required minlength="8"
                       placeholder="At least 8 characters"
                       oninput="checkStrength(this.value)"
                       style="width:100%; padding:0.875rem; font-size:0.9375rem;">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill" style="width:0%;"></div>
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;"
                     id="strengthLabel"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       class="form-control" required minlength="8"
                       placeholder="Repeat your new password"
                       style="width:100%; padding:0.875rem; font-size:0.9375rem;">
            </div>

            <button type="submit" class="btn btn-primary"
                    style="width:100%; padding:0.9rem; font-size:1rem; margin-top:0.25rem;">
                Reset Password
            </button>
        </form>

        <?php else: ?>
        <!-- Token invalid and no success: show link to request a new one -->
        <div style="text-align:center; margin-top:0.5rem;">
            <a href="<?php echo SITE_URL; ?>public/forgot_password.php"
               class="btn btn-primary" style="display:inline-block; padding:0.75rem 2rem;">
                Request New Reset Link
            </a>
        </div>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1.5rem; padding-top:1.25rem;
                    border-top:1px solid var(--border); font-size:0.875rem; color:var(--text-secondary);">
            <a href="<?php echo SITE_URL; ?>public/login.php" style="color:var(--primary); font-weight:500;">
                ← Back to Sign In
            </a>
        </div>

    </div>
</div>

<script>
function checkStrength(pw) {
    let score = 0;
    if (pw.length >= 8)                       score++;
    if (pw.length >= 12)                      score++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
    if (/[0-9]/.test(pw))                     score++;
    if (/[^A-Za-z0-9]/.test(pw))             score++;

    const levels = [
        { w: '20%',  bg: '#b85c5c', label: 'Weak'        },
        { w: '40%',  bg: '#c9a227', label: 'Fair'        },
        { w: '60%',  bg: '#c9a227', label: 'Fair'        },
        { w: '80%',  bg: '#5a8a6a', label: 'Good'        },
        { w: '100%', bg: '#5a8a6a', label: 'Strong'      },
    ];
    const lvl   = levels[Math.min(score, 4)];
    const fill  = document.getElementById('strengthFill');
    const lbl   = document.getElementById('strengthLabel');
    if (fill) { fill.style.width = lvl.w; fill.style.background = lvl.bg; }
    if (lbl)  { lbl.textContent  = lvl.label; lbl.style.color = lvl.bg; }
}
</script>
</body>
</html>
