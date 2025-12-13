<?php
// sticker-shop/public/checkout.php

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';
require_once ROOT_PATH . 'src/classes/Cart.php';
require_once ROOT_PATH . 'src/classes/User.php';
require_once ROOT_PATH . 'src/classes/Order.php';

$database = new Database();
$db = $database->connect();
$cart = new Cart();
$user_manager = new User($db);
$order_manager = new Order($db);
$product_manager = new Product($db);
$message = '';

// 1. Check if cart is empty
if ($cart->isEmpty()) {
    header('Location: ' . SITE_URL . 'public/cart.php');
    exit();
}

// Validate cart contents before proceeding
$cart_contents = $cart->getContents();
foreach ($cart_contents as $product_id => $quantity) {
    $product = $product_manager->findById($product_id);
    if (!$product || $product['stock_quantity'] < $quantity) {
        // Product is out of stock or not enough quantity
        // Redirect back to the cart page with an error message
        $_SESSION['cart_error'] = "Not enough stock for one of your items. Please review your cart.";
        header('Location: ' . SITE_URL . 'public/cart.php');
        exit();
    }
}

// 2. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the intended destination and redirect to login
    $_SESSION['redirect_to'] = SITE_URL . 'public/checkout.php';
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

// 3. Calculate total and get user info
$customer_id = $_SESSION['user_id'];
$user = $user_manager->findById($customer_id);
$cart_contents = $cart->getContents();
$total_amount = 0;

// Recalculate total amount for security
foreach ($cart_contents as $product_id => $quantity) {
    $product = $product_manager->findById($product_id);
    if ($product) {
        $total_amount += $product['price'] * $quantity;
    }
}

// 4. Handle form submission (Place Order)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {

    $order_id = $order_manager->create($customer_id, $cart_contents, $total_amount);

    if ($order_id) {
        $cart->clear(); // Clear the cart after successful order
        header('Location: ' . SITE_URL . 'public/order_success.php?order_id=' . $order_id);
        exit();
    } else {
        $message = '<p class="message error">Order failed! Please check stock availability or contact support.</p>';
    }
}

include ROOT_PATH . 'src/includes/header.php';
?>

<h1>Checkout</h1>

<?php echo $message; ?>

<div class="checkout-layout">
    <div class="shipping-info">
        <h2>Shipping Information</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_no']); ?></p>
        <p class="note">To change your address, please update your <a href="<?PHP echo SITE_URL; ?>public/profile.php">profile</a>.</p>
    </div>

    <div class="order-summary">
        <h2>Order Summary</h2>
        <p>Total Items: <?php echo count($cart_contents); ?></p>
        <p>Shipping: FREE (Local Delivery)</p>
        <h3>Order Total: $<?php echo number_format($total_amount, 2); ?></h3>

        <form action="<?php echo SITE_URL; ?>public/checkout.php" method="POST">
            <p><strong>Payment Method:</strong> Cash on Delivery (Local Only)</p>
            <div class="con">
                <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
            </div>
        </form>
    </div>
</div>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>