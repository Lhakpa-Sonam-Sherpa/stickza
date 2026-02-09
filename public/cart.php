<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';
require_once ROOT_PATH . 'src/classes/Cart.php';

$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);
$cart = new Cart();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($action == 'add' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $cart->add($product_id, $quantity);
        header('Location: ' . SITE_URL . 'public/cart.php?added=1');
        exit();
    } elseif ($action == 'update' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $cart->update($product_id, $quantity);
        header('Location: ' . SITE_URL . 'public/cart.php?updated=1');
        exit();
    } elseif ($action == 'remove' && $product_id > 0) {
        $cart->remove($product_id);
        header('Location: ' . SITE_URL . 'public/cart.php?removed=1');
        exit();
    }
}

$cart_contents = $cart->getContents();
$cart_details = [];
$subtotal = 0;

if (!empty($cart_contents)) {
    foreach ($cart_contents as $product_id => $quantity) {
        $product = $product_manager->findById($product_id);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['line_total'] = $product['price'] * $quantity;
            $subtotal += $product['line_total'];
            $cart_details[] = $product;
        } else {
            $cart->remove($product_id);
        }
    }
}

include ROOT_PATH . 'src/includes/header.php';
?>

<div class="section-header">
    <h1>Shopping Cart</h1>
    <p>Review your items and proceed to checkout</p>
</div>

<?php if (isset($_GET['added'])): ?>
    <div class="alert alert-success">
        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        Item added to cart successfully!
    </div>
<?php endif; ?>

<?php if ($cart->isEmpty()): ?>
    <div class="empty-cart">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any stickers yet.</p>
        <a href="<?php echo SITE_URL; ?>public/" class="btn btn-primary btn-lg" style="margin-top: 1.5rem;">Start Shopping</a>
    </div>
<?php else: ?>
    <div class="cart-container">
        <div class="cart-items">
            <?php foreach ($cart_details as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($item['image_url'] ?? 'placeholder.png'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Rs <?php echo number_format($item['price'], 2); ?> each</p>
                    </div>
                    <div class="cart-item-price">Rs <?php echo number_format($item['line_total'], 2); ?></div>
                    <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST" class="cart-item-quantity">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" onchange="this.form.submit()">
                    </form>
                    <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h2>Order Summary</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rs <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span style="color: var(--success);">Free</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span>Rs <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <a href="<?php echo SITE_URL; ?>public/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top: 1.5rem;">
                Proceed to Checkout
            </a>
            <a href="<?php echo SITE_URL; ?>public/" class="btn btn-ghost btn-full" style="margin-top: 0.75rem;">
                Continue Shopping
            </a>
        </div>
    </div>
<?php endif; ?>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>