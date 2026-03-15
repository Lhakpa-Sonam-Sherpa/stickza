<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/User.php';
require_once ROOT_PATH . 'src/helpers/Validator.php';

initSecureSession();

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);

$errors = [];
$success = '';
$formData = ['first_name' => '', 'last_name' => '', 'email' => '', 'address' => '', 'city' => '', 'phone_no' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();
    
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'phone_no' => trim($_POST['phone_no'] ?? '')
    ];

    $validator = new Validator($formData);
    $validator->required('first_name', 'First Name')
              ->required('email', 'Email')->email('email')
              ->required('password', 'Password')->minLength('password', 8, 'Password')
              ->required('address', 'Address')
              ->required('city', 'City');

    // Custom password complexity check
    if (!preg_match('/[A-Z]/', $formData['password'])) $validator->addError('password', 'Password must contain at least one uppercase letter.');
    if (!preg_match('/[a-z]/', $formData['password'])) $validator->addError('password', 'Password must contain at least one lowercase letter.');
    if (!preg_match('/[0-9]/', $formData['password'])) $validator->addError('password', 'Password must contain at least one number.');

    if ($validator->fails()) {
        $errors = $validator->errors();
    } else {
        if ($user_manager->register($formData)) {
            $success = "Account created successfully! You can now log in.";
            // Clear form data on success
            $formData = array_fill_keys(array_keys($formData), '');
        } else {
            $errors[] = "Registration failed. Email may already be in use.";
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
    <title>Register - Stickza</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>public/css/style.css">
    <style>
        /* (Styles remain the same) */
        .auth-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--bg-secondary); padding: 1rem; }
        .auth-card { background: var(--bg-primary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.5rem; width: 100%; max-width: 480px; box-shadow: var(--shadow-md); }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header .logo { display: flex; align-items: center; justify-content: center; gap: 0.75rem; font-size: 1.75rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; }
        .auth-header .logo svg { width: 32px; height: 32px; stroke: var(--primary); }
        .auth-header p { color: var(--text-secondary); }
        .auth-form .form-group { margin-bottom: 1.25rem; }
        .auth-form label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-primary); margin-bottom: 0.5rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .password-wrapper { position: relative; }
        .password-toggle { position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); cursor: pointer; color: var(--text-muted); background: none; border: none; padding: 0.25rem; }
        .password-toggle:hover { color: var(--text-primary); }
        .password-toggle svg { width: 18px; height: 18px; }
        .auth-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); font-size: 0.875rem; color: var(--text-secondary); }
        .auth-footer a { color: var(--primary); font-weight: 500; }
        #password-strength { list-style-type: none; padding: 0; margin: 0.5rem 0 0 0; font-size: 0.75rem; }
        #password-strength li { color: var(--danger); transition: color 0.3s ease; }
        #password-strength li.valid { color: var(--success); text-decoration: line-through; }
        @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }
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
                <h1>Create an Account</h1>
                <p>Join us and start your sticker collection.</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <div><?php foreach ($errors as $error) echo '<div>' . htmlspecialchars($error) . '</div>'; ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($success); ?> <a href="login.php">Log in</a>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo SITE_URL; ?>public/register.php" method="POST" class="auth-form">
                <?php echo csrfField(); ?>
                <div class="form-row">
                    <div class="form-group"><label for="first_name">First Name *</label><input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($formData['first_name']); ?>" required></div>
                    <div class="form-group"><label for="last_name">Last Name</label><input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($formData['last_name']); ?>"></div>
                </div>
                <div class="form-group"><label for="email">Email Address *</label><input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($formData['email']); ?>" required></div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" required>
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                            <svg class="eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="eye-closed" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .95-3.11 3.8-5.448 7.29-6.101M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.938 21.938l-14-14"/></svg>
                        </button>
                    </div>
                    <ul id="password-strength">
                        <li id="length">At least 8 characters</li>
                        <li id="uppercase">At least one uppercase letter</li>
                        <li id="lowercase">At least one lowercase letter</li>
                        <li id="number">At least one number</li>
                    </ul>
                </div>
                <div class="form-group"><label for="phone_no">Phone Number</label><input type="tel" id="phone_no" name="phone_no" class="form-control" placeholder="98XXXXXXXX" value="<?php echo htmlspecialchars($formData['phone_no']); ?>"></div>
                <div class="form-group"><label for="address">Address *</label><input type="text" id="address" name="address" class="form-control" placeholder="Street address" value="<?php echo htmlspecialchars($formData['address']); ?>" required></div>
                <div class="form-group"><label for="city">City *</label><input type="text" id="city" name="city" class="form-control" placeholder="Your city" value="<?php echo htmlspecialchars($formData['city']); ?>" required></div>
                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>
            <div class="auth-footer">Already have an account? <a href="<?php echo SITE_URL; ?>public/login.php">Sign in</a></div>
        </div>
    </div>
    <script>
        // (JavaScript remains the same)
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const lengthReq = document.getElementById('length');
            const uppercaseReq = document.getElementById('uppercase');
            const lowercaseReq = document.getElementById('lowercase');
            const numberReq = document.getElementById('number');

            passwordInput.addEventListener('input', function() {
                const value = passwordInput.value;
                lengthReq.classList.toggle('valid', value.length >= 8);
                uppercaseReq.classList.toggle('valid', /[A-Z]/.test(value));
                lowercaseReq.classList.toggle('valid', /[a-z]/.test(value));
                numberReq.classList.toggle('valid', /[0-9]/.test(value));
            });
        });
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
