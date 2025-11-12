<?php
// sticker-shop/public/login.php

require_once __DIR__.'/../src/config.php';
require_once ROOT_PATH.'/src/classes/Database.php';
require_once ROOT_PATH.'/src/classes/User.php';

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);
$message = '';

// If user is already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: '.SITE_URL.'src/pages/home.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user_id = $user_manager->login($email, $password);

    if ($user_id) {
        // Login successful
        $_SESSION['user_id'] = $user_id; 
        $redirect = $_SESSION['redirect_to'] ?? SITE_URL.'public/';
        unset($_SESSION['redirect_to']);
        header('Location: ',$redirect); // Redirect to homepage
        exit();
    } else {
        $message = '<p class="error">Login failed. Invalid email or password.</p>';
    }
}

include ROOT_PATH . 'src/includes/header.php';
?>

<h1>Login to Your Account</h1>

<?php echo $message; ?>

<form action="<?php echo SITE_URL;?>public/login.php" method="POST" class="auth-form">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="<?php echo SITE_URL;?>public/register.php">Register here</a>.</p>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>
