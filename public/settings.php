<?php
/**
 * public/settings.php
 *
 * User account settings page:
 * • Update profile (name, email, phone, address, city) with email uniqueness check
 * • Change password (verify current → bcrypt new)
 * • CSRF protection on both forms
 * • Sidebar navigation: Profile, Order History, Feedback, Privacy, Terms
 */

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/User.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

// Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$database = new Database();
$db       = $database->connect();

// Fetch fresh user data
$stmt = $db->prepare(
    "SELECT id, first_name, last_name, email, phone_no, address, city
     FROM customers WHERE id = :id LIMIT 1"
);
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

$profile_success = '';
$profile_error   = '';
$password_success = '';
$password_error   = '';

// ── POST handler ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation (shared by both forms)
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrf_token, $submitted)) {
        $profile_error = $password_error = 'Security token mismatch. Please refresh and try again.';
    } else {

        $form = $_POST['form'] ?? '';

        // ── Profile update ────────────────────────────────
        if ($form === 'update_profile') {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name  = trim($_POST['last_name']  ?? '');
            $email      = trim($_POST['email']      ?? '');
            $phone_no   = trim($_POST['phone_no']   ?? '');
            $address    = trim($_POST['address']    ?? '');
            $city       = trim($_POST['city']       ?? '');

            if (empty($first_name) || empty($last_name)) {
                $profile_error = 'First and last name are required.';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $profile_error = 'A valid email address is required.';
            } else {
                // Email uniqueness check (excluding current user)
                $dup = $db->prepare(
                    "SELECT id FROM customers WHERE email = :email AND id != :id LIMIT 1"
                );
                $dup->execute([':email' => $email, ':id' => $user['id']]);
                if ($dup->fetch()) {
                    $profile_error = 'That email address is already in use by another account.';
                } else {
                    $upd = $db->prepare(
                        "UPDATE customers
                         SET first_name = :fn, last_name = :ln, email = :email,
                             phone_no = :phone, address = :addr, city = :city
                         WHERE id = :id"
                    );
                    if ($upd->execute([
                        ':fn'    => $first_name,
                        ':ln'    => $last_name,
                        ':email' => $email,
                        ':phone' => $phone_no,
                        ':addr'  => $address,
                        ':city'  => $city,
                        ':id'    => $user['id'],
                    ])) {
                        $profile_success = 'Profile updated successfully.';
                        // Refresh local $user array
                        $user['first_name'] = $first_name;
                        $user['last_name']  = $last_name;
                        $user['email']      = $email;
                        $user['phone_no']   = $phone_no;
                        $user['address']    = $address;
                        $user['city']       = $city;
                    } else {
                        $profile_error = 'Failed to update profile. Please try again.';
                    }
                }
            }

        // ── Password change ───────────────────────────────
        } elseif ($form === 'change_password') {
            $current  = $_POST['current_password']  ?? '';
            $new_pw   = $_POST['new_password']       ?? '';
            $confirm  = $_POST['confirm_password']   ?? '';

            // Verify current password
            $pw_row = $db->prepare("SELECT password FROM customers WHERE id = :id");
            $pw_row->execute([':id' => $user['id']]);
            $stored = $pw_row->fetchColumn();

            if (!password_verify($current, $stored)) {
                $password_error = 'Current password is incorrect.';
            } elseif (mb_strlen($new_pw) < 8) {
                $password_error = 'New password must be at least 8 characters.';
            } elseif ($new_pw !== $confirm) {
                $password_error = 'New passwords do not match.';
            } elseif ($current === $new_pw) {
                $password_error = 'New password must differ from your current password.';
            } else {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $upd  = $db->prepare(
                    "UPDATE customers SET password = :hash WHERE id = :id"
                );
                if ($upd->execute([':hash' => $hash, ':id' => $user['id']])) {
                    $password_success = 'Password changed successfully.';
                } else {
                    $password_error = 'Failed to change password. Please try again.';
                }
            }
        }
    }
}

$theme      = getCurrentTheme();
$page_title = 'Account Settings';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
.settings-layout   { display: grid; grid-template-columns: 220px 1fr; gap: 2rem; max-width: 920px; margin: 0 auto; padding: 1.5rem 0 3rem; }
.settings-sidebar  { display: flex; flex-direction: column; gap: 0.25rem; }
.settings-sidebar a { display: flex; align-items: center; gap: 0.6rem; padding: 0.625rem 0.875rem; border-radius: var(--radius); font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); transition: background var(--transition-fast), color var(--transition-fast); text-decoration: none; }
.settings-sidebar a:hover, .settings-sidebar a.active { background: var(--primary-light); color: var(--primary); }
.settings-sidebar a svg { width: 16px; height: 16px; flex-shrink: 0; }
.settings-card     { background: var(--bg-primary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.75rem; margin-bottom: 1.5rem; }
.settings-card h2  { font-size: 1.0625rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.25rem; padding-bottom: 0.875rem; border-bottom: 1px solid var(--border); }
.form-row          { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) {
    .settings-layout { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="settings-layout">

    <!-- Sidebar -->
    <nav class="settings-sidebar">
        <a href="profile.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            My Profile
        </a>
        <a href="settings.php" class="active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            Account Settings
        </a>
        <a href="feedback.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Send Feedback
        </a>
        <a href="privacy.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Privacy Policy
        </a>
        <a href="terms.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Terms of Service
        </a>
        <a href="logout.php" style="color:var(--danger); margin-top:0.75rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Sign Out
        </a>
    </nav>

    <!-- Main content -->
    <div>

        <!-- Profile update form -->
        <div class="settings-card">
            <h2>Profile Information</h2>

            <?php if ($profile_success): ?>
            <div class="alert alert-success" style="margin-bottom:1.25rem;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <?php echo htmlspecialchars($profile_success); ?>
            </div>
            <?php elseif ($profile_error): ?>
            <div class="alert alert-error" style="margin-bottom:1.25rem;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <?php echo htmlspecialchars($profile_error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="form"       value="update_profile">

                <div class="form-row" style="margin-bottom:1rem;">
                    <div class="form-group">
                        <label for="first_name">First Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control"
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control"
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required maxlength="50">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($user['email']); ?>" required maxlength="100">
                </div>

                <div class="form-row" style="margin-bottom:1rem;">
                    <div class="form-group">
                        <label for="phone_no">Phone Number</label>
                        <input type="tel" id="phone_no" name="phone_no" class="form-control"
                               value="<?php echo htmlspecialchars($user['phone_no'] ?? ''); ?>" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control"
                               value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" maxlength="60">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control"
                           value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" maxlength="150">
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Password change form -->
        <div class="settings-card">
            <h2>Change Password</h2>

            <?php if ($password_success): ?>
            <div class="alert alert-success" style="margin-bottom:1.25rem;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <?php echo htmlspecialchars($password_success); ?>
            </div>
            <?php elseif ($password_error): ?>
            <div class="alert alert-error" style="margin-bottom:1.25rem;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <?php echo htmlspecialchars($password_error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="form"       value="change_password">

                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           class="form-control" required autocomplete="current-password">
                </div>

                <div class="form-row" style="margin-bottom:1.25rem;">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password"
                               class="form-control" required minlength="8" autocomplete="new-password">
                        <div class="form-hint">Minimum 8 characters.</div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

    </div><!-- main content -->
</div><!-- settings-layout -->

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
