<?php
// sticker-shop/public/checkout.php

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';
require_once ROOT_PATH . 'src/classes/Cart.php';
require_once ROOT_PATH . 'src/classes/User.php';
require_once ROOT_PATH . 'src/classes/Order.php';

initSecureSession();

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

// Validate cart contents and calculate total based on final prices
$cart_contents = $cart->getContents();
$cart_details = [];
$total_amount = 0;

foreach ($cart_contents as $product_id => $quantity) {
    $product = $product_manager->findById($product_id);
    if (!$product || $product['stock_quantity'] < $quantity) {
        $_SESSION['cart_error'] = "Not enough stock for \"{$product['name']}\". Please review your cart.";
        header('Location: ' . SITE_URL . 'public/cart.php');
        exit();
    }
    
    // Calculate the final price, considering discounts
    $final_price = $product['price'];
    if (!is_null($product['discount_price'])) {
        $final_price = $product['discount_price'];
    } elseif (!is_null($product['discount_percent'])) {
        $final_price = $product['price'] * (1 - ($product['discount_percent'] / 100));
    }

    $product['final_price'] = $final_price;
    $product['quantity'] = $quantity;
    $cart_details[] = $product;
    $total_amount += $final_price * $quantity;
}

// 2. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = SITE_URL . 'public/checkout.php';
    header('Location: ' . SITE_URL . 'public/login.php');
    exit();
}

// 3. Get user info
$customer_id = $_SESSION['user_id'];
$user = $user_manager->findById($customer_id);

// 4. Handle form submission (Place Order)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    validateCSRF();
    // Pass the cart with calculated final prices to the order manager
    $order_id = $order_manager->create($customer_id, $cart_details, $total_amount);

    if ($order_id) {
        $cart->clear();
        header('Location: ' . SITE_URL . 'public/order_success.php?order_id=' . $order_id);
        exit();
    } else {
        $message = '<div class="alert alert-error">Order failed! Please check stock availability or contact support.</div>';
    }
}

$page_title = 'Checkout';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
/* Checkout Page Redesign */
.checkout-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2.5rem;
    font-size: 0.875rem;
}
.checkout-progress-step {
    color: var(--text-muted);
    display: flex;
    align-items: center;
}
.checkout-progress-step.active {
    color: var(--primary);
    font-weight: 600;
}
.checkout-progress-step .separator {
    margin: 0 1rem;
    color: var(--border-strong);
}
.checkout-layout {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 2.5rem;
    align-items: flex-start;
}
.checkout-form, .checkout-summary {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2rem;
}
.checkout-summary {
    position: sticky;
    top: calc(var(--header-height) + 1.5rem);
}
.checkout-form h2, .checkout-summary h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}
.info-group {
    margin-bottom: 1.5rem;
}
.info-group h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.info-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}
.info-row strong {
    color: var(--text-primary);
    font-weight: 500;
}
.info-note {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin-top: 1rem;
    background: var(--bg-tertiary);
    padding: 0.75rem;
    border-radius: var(--radius);
}
.info-note a { color: var(--primary); }

.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}
.summary-item-img {
    width: 64px;
    height: 64px;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--bg-tertiary);
}
.summary-item-img img { width: 100%; height: 100%; object-fit: cover; }
.summary-item-details { flex-grow: 1; }
.summary-item-details h4 { font-size: 0.9375rem; font-weight: 500; margin-bottom: 0.25rem; }
.summary-item-details p { font-size: 0.8125rem; color: var(--text-muted); }
.summary-item-price { font-weight: 600; }

.summary-totals {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
.summary-totals .summary-row { font-size: 1rem; }
.summary-totals .summary-row.grand-total { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); }

@media (max-width: 768px) {
    .checkout-layout { grid-template-columns: 1fr; }
    .checkout-summary { order: -1; position: static; margin-bottom: 1.5rem; }
}
</style>

<!-- Checkout Progress Indicator -->
<div class="checkout-progress">
    <div class="checkout-progress-step">Cart</div>
    <div class="checkout-progress-step active">
        <span class="separator">&rarr;</span>
        Checkout
    </div>
    <div class="checkout-progress-step">
        <span class="separator">&rarr;</span>
        Confirmation
    </div>
</div>

<?php echo $message; ?>

<div class="checkout-layout">
    <!-- Left Column: Form -->
    <div class="checkout-form">
        <form action="<?php echo SITE_URL; ?>public/checkout.php" method="POST">
            <?php echo csrfField(); ?>
            
            <div class="info-group">
                <h2>Shipping Information</h2>
                <div class="info-row"><span>Name:</span> <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></div>
                <div class="info-row"><span>Address:</span> <strong><?php echo htmlspecialchars($user['address']); ?></strong></div>
                <div class="info-row"><span>City:</span> <strong><?php echo htmlspecialchars($user['city']); ?></strong></div>
                <div class="info-row"><span>Phone:</span> <strong><?php echo htmlspecialchars($user['phone_no']); ?></strong></div>
                <p class="info-note">To change your address, please update your <a href="<?php echo SITE_URL; ?>public/profile.php">profile</a>.</p>
            </div>

            <div class="info-group">
                <h2>Payment Method</h2>
                <div class="info-row">
                    <span>Method:</span>
                    <strong>Cash on Delivery (Local Only)</strong>
                </div>
                <p class="info-note">You will pay in cash when your order is delivered.</p>
            </div>

            <button type="submit" name="place_order" class="btn btn-primary btn-full btn-lg">Place Order</button>
        </form>
    </div>

    <!-- Right Column: Summary -->
    <div class="checkout-summary">
        <h2>Order Summary</h2>
        <?php foreach ($cart_details as $item): ?>
            <div class="summary-item">
                <div class="summary-item-img">
                    <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                </div>
                <div class="summary-item-details">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                </div>
                <div class="summary-item-price">Rs <?php echo number_format($item['final_price'] * $item['quantity'], 2); ?></div>
            </div>
        <?php endforeach; ?>

        <div class="summary-totals">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rs <?php echo number_format($total_amount, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span style="color: var(--success);">Free</span>
            </div>
            <div class="summary-row grand-total">
                <span>Total</span>
                <span>Rs <?php echo number_format($total_amount, 2); ?></span>
            </div>
        </div>
    </div>
</div>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>
