<?php
// sticker-shop/public/cart.php


require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';
require_once ROOT_PATH . 'src/classes/Cart.php';

$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);
$cart = new Cart();
$message = '';

// Handle cart actions (Add, Update, Remove)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($action == 'add' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $cart->add($product_id, $quantity);
        $message = '<p class="success">Product added to cart!</p>';
    } elseif ($action == 'update' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $cart->update($product_id, $quantity);
        $message = '<p class="success">Cart updated!</p>';
    } elseif ($action == 'remove' && $product_id > 0) {
        $cart->remove($product_id);
        $message = '<p class="success">Product removed from cart!</p>';
    }
    // Redirect to prevent form resubmission on refresh
    header('Location: ' . SITE_URL . 'public/cart.php');
    exit();
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
            // Remove item if product no longer exists
            $cart->remove($product_id);
        }
    }
}

include ROOT_PATH . 'src/includes/header.php';
?>

<h1>Your Shopping Cart</h1>

<?php echo $message; ?>

<?php if ($cart->isEmpty()): ?>
    <p>Your cart is empty. <a href="<?php echo SITE_URL; ?>public/">Start shopping!</a></p>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_details as $item): ?>
                <tr>
                    <td>
                        <div class="cart-item-info">
                            <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($item['image_url'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50">
                            <a href="<?php echo SITE_URL; ?>public/product.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                        </div>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST" class="update-form">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" required>
                            <div class="con">
                                <button type="submit" name="action" value="update">Update</button>
                            </div>
                        </form>
                    </td>
                    <td>$<?php echo number_format($item['line_total'], 2); ?></td>
                    <td>
                        <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <div class="con">
                                <button type="submit" name="action" value="remove" class="btn-danger">Remove</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <h3>Subtotal: $<?php echo number_format($subtotal, 2); ?></h3>
        <a href="<?php echo SITE_URL; ?>public/checkout.php" class="checkout-btn">Proceed to Checkout</a>
    </div>
<?php endif; ?>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>