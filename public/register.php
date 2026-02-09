<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/User.php';

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);

$errors = [];
$success = '';

// Preserve form data
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'address' => '',
    'city' => '',
    'phone_no' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'phone_no' => trim($_POST['phone_no'] ?? '')
    ];
    
    // Validation
    if (empty($formData['first_name'])) {
        $errors[] = "First name is required.";
    }
    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (strlen($formData['password']) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if (empty($formData['address'])) {
        $errors[] = "Address is required.";
    }
    if (empty($formData['city'])) {
        $errors[] = "City is required.";
    }
    
    if (empty($errors)) {
        if ($user_manager->register($formData)) {
            $success = "Account created successfully! You can now log in.";
            $formData = ['first_name' => '', 'last_name' => '', 'email' => '', 'address' => '', 'city' => '', 'phone_no' => ''];
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
        .auth-form .form-group {
            margin-bottom: 1.25rem;
        }
        .auth-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        .auth-form input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        .auth-form button {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .auth-footer {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            text-align: center;
        }
        .form-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 480px; padding: 2.5rem;">
            <div class="auth-header" style="margin-bottom: 2rem;">
                <a href="<?php echo SITE_URL; ?>public/" class="logo" style="justify-content: center; margin-bottom: 1.5rem; font-size: 2rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    Stickza
                </a>
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Create account</h1>
                <p style="color: var(--text-secondary);">Join us and start shopping</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                    <svg fill="currentColor" viewBox="0 0 20 20" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <?php echo htmlspecialchars($success); ?> <a href="login.php">Log in</a>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo SITE_URL; ?>public/register.php" method="POST" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($formData['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($formData['last_name']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_no">Phone Number</label>
                    <input type="tel" id="phone_no" name="phone_no" class="form-control" placeholder="98XXXXXXXX" value="<?php echo htmlspecialchars($formData['phone_no']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address *</label>
                    <input type="text" id="address" name="address" class="form-control" placeholder="Street address" value="<?php echo htmlspecialchars($formData['address']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" class="form-control" placeholder="Your city" value="<?php echo htmlspecialchars($formData['city']); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="<?php echo SITE_URL; ?>public/login.php">Sign in</a>
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
            fetch('<?php echo SITE_URL; ?>public/theme/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + newTheme
            });
        }
    </script>
</body>
</html>