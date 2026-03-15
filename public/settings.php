<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/User.php';
require_once ROOT_PATH . 'src/helpers/Validator.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$database = new Database();
$db = $database->connect();

$stmt = $db->prepare("SELECT id, first_name, last_name, email, phone_no, address, city FROM customers WHERE id = :id LIMIT 1");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    $form = $_POST['form'] ?? '';

    // --- Profile update ---
    if ($form === 'update_profile') {
        $validator = new Validator($_POST);
        $validator->required('first_name')->required('last_name')->required('email')->email('email');

        if ($validator->fails()) {
            $profile_error = $validator->firstError();
        } else {
            $email = trim($_POST['email']);
            $dup = $db->prepare("SELECT id FROM customers WHERE email = :email AND id != :id LIMIT 1");
            $dup->execute([':email' => $email, ':id' => $user['id']]);
            if ($dup->fetch()) {
                $profile_error = 'That email address is already in use by another account.';
            } else {
                $upd = $db->prepare(
                    "UPDATE customers SET first_name = :fn, last_name = :ln, email = :email, phone_no = :phone, address = :addr, city = :city WHERE id = :id"
                );
                if ($upd->execute([
                    ':fn'    => trim($_POST['first_name']),
                    ':ln'    => trim($_POST['last_name']),
                    ':email' => $email,
                    ':phone' => trim($_POST['phone_no'] ?? ''),
                    ':addr'  => trim($_POST['address'] ?? ''),
                    ':city'  => trim($_POST['city'] ?? ''),
                    ':id'    => $user['id'],
                ])) {
                    $profile_success = 'Profile updated successfully.';
                    // Refresh user data
                    $stmt->execute([':id' => $_SESSION['user_id']]);
                    $user = $stmt->fetch();
                } else {
                    $profile_error = 'Failed to update profile. Please try again.';
                }
            }
        }
    }

    // --- Password change ---
    elseif ($form === 'change_password') {
        $validator = new Validator($_POST);
        $validator->required('current_password', 'Current Password')
                  ->required('new_password', 'New Password')->minLength('new_password', 8)
                  ->required('confirm_password', 'Confirm Password')->matches('new_password', 'confirm_password', 'Passwords');

        if ($validator->fails()) {
            $password_error = $validator->firstError();
        } else {
            $pw_row = $db->prepare("SELECT password FROM customers WHERE id = :id");
            $pw_row->execute([':id' => $user['id']]);
            $stored_hash = $pw_row->fetchColumn();

            if (!password_verify($_POST['current_password'], $stored_hash)) {
                $password_error = 'Current password is incorrect.';
            } elseif ($_POST['current_password'] === $_POST['new_password']) {
                $password_error = 'New password must differ from your current password.';
            } else {
                $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $upd  = $db->prepare("UPDATE customers SET password = :hash WHERE id = :id");
                if ($upd->execute([':hash' => $hash, ':id' => $user['id']])) {
                    $password_success = 'Password changed successfully.';
                } else {
                    $password_error = 'Failed to change password. Please try again.';
                }
            }
        }
    }
}

$theme = getCurrentTheme();
$page_title = 'Account Settings';
include ROOT_PATH . 'src/includes/header.php';
?>

<!-- The HTML and CSS for this file remain unchanged. -->
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
    <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
        My Profile
    </a>
    <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
        Account Settings
    </a>
    <a href="privacy.php" class="<?php echo $current_page == 'privacy.php' ? 'active' : ''; ?>">
        Privacy Policy
    </a>
    <a href="terms.php" class="<?php echo $current_page == 'terms.php' ? 'active' : ''; ?>">
        Terms of Service
    </a>
    <a href="feedback.php" class="<?php echo $current_page == 'feedback.php' ? 'active' : ''; ?>">
        Feedback
    </a>
</nav>

    <!-- Main content -->
    <div>
        <!-- Profile update form -->
        <div class="settings-card">
            <h2>Profile Information</h2>
            <?php if ($profile_success): ?><div class="alert alert-success" style="margin-bottom:1.25rem;"><?php echo htmlspecialchars($profile_success); ?></div><?php endif; ?>
            <?php if ($profile_error): ?><div class="alert alert-error" style="margin-bottom:1.25rem;"><?php echo htmlspecialchars($profile_error); ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="form" value="update_profile">
                <div class="form-row" style="margin-bottom:1rem;">
                    <div class="form-group"><label for="first_name">First Name <span style="color:var(--danger)">*</span></label><input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required maxlength="50"></div>
                    <div class="form-group"><label for="last_name">Last Name <span style="color:var(--danger)">*</span></label><input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required maxlength="50"></div>
                </div>
                <div class="form-group" style="margin-bottom:1rem;"><label for="email">Email Address <span style="color:var(--danger)">*</span></label><input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required maxlength="100"></div>
                <div class="form-row" style="margin-bottom:1rem;">
                    <div class="form-group"><label for="phone_no">Phone Number</label><input type="tel" id="phone_no" name="phone_no" class="form-control" value="<?php echo htmlspecialchars($user['phone_no'] ?? ''); ?>" maxlength="20"></div>
                    <div class="form-group"><label for="city">City</label><input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" maxlength="60"></div>
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;"><label for="address">Address</label><input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" maxlength="150"></div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Password change form -->
        <div class="settings-card">
            <h2>Change Password</h2>
            <?php if ($password_success): ?><div class="alert alert-success" style="margin-bottom:1.25rem;"><?php echo htmlspecialchars($password_success); ?></div><?php endif; ?>
            <?php if ($password_error): ?><div class="alert alert-error" style="margin-bottom:1.25rem;"><?php echo htmlspecialchars($password_error); ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="form" value="change_password">
                <div class="form-group" style="margin-bottom:1rem;"><label for="current_password">Current Password</label><input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password"></div>
                <div class="form-row" style="margin-bottom:1.25rem;">
                    <div class="form-group"><label for="new_password">New Password</label><input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password"><div class="form-hint">Minimum 8 characters.</div></div>
                    <div class="form-group"><label for="confirm_password">Confirm New Password</label><input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password"></div>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
