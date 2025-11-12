<?php
// sticker-shop/public/register.php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH.'src/classes/Database.php';
require_once ROOT_PATH.'src/classes/User.php';

$database = new Database();
$db = $database->connect();
$user_manager = new User($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'password' => $_POST['password'],
        'address' => trim($_POST['address']),
        'city' => trim($_POST['city']),
        'phone_no' => trim($_POST['phone_no']),
    ];

    // Simple validation
    if (empty($data['first_name']) || empty($data['email']) || empty($data['password'])) {
        $message = '<p class="error">Please fill in all required fields (First Name, Email, Password).</p>';
    } elseif ($user_manager->register($data)) {
        $message = '<p class="success">Registration successful! You can now <a href="login.php">log in</a>.</p>';
    } else {
        $message = '<p class="error">Registration failed. The email may already be in use.</p>';
    }
}

include ROOT_PATH . 'src/includes/header.php';
?>

<h1>Register Account</h1>

<?php echo $message; ?>

<form action="register.php" method="POST" class="auth-form">
    <label for="first_name">First Name *</label>
    <input type="text" id="first_name" name="first_name" required><br><br>

    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" name="last_name"><br><br>

    <label for="email">Email *</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password *</label>
    <input type="password" id="password" name="password" required><br><br>

    <label for="address">Address</label>
    <input type="text" id="address" name="address"><br><br>

    <label for="city">City</label>
    <input type="text" id="city" name="city"><br><br>

    <label for="phone_no">Phone Number</label>
    <input type="text" id="phone_no" name="phone_no"><br><br>

    <button type="submit">Register</button>
</form>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>
