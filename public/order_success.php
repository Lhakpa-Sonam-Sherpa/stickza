<?php
// sticker-shop/public/order_success.php

require_once __DIR__.'/../src/config.php';
require_once ROOT_PATH.'/src/classes/Database.php';
require_once ROOT_PATH.'/src/classes/Order.php';

$database = new Database();
$db = $database->connect();
$order_manager = new Order($db);

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id === 0) {
    header('Location: '.SITE_URL.'public/product.php');
    exit();
}

$order_details = $order_manager->getOrderDetails($order_id);

include ROOT_PATH.'src/includes/header.php';
?>

<div class="order-confirmation">
    <h1>Order Placed Successfully!</h1>
    <p class="success-message">Thank you for your purchase! Your order #<?php echo $order_id; ?> has been placed and is being processed.</p>
    <p>We will contact you shortly to arrange local delivery.</p>

    <h2>Order Details</h2>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price per Item</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            foreach ($order_details as $item): 
                $line_total = $item['price'] * $item['quantity'];
                $grand_total += $line_total;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>$<?php echo number_format($line_total, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>
                <td><strong>$<?php echo number_format($grand_total, 2); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <p><a href="<?php echo SITE_URL?>public/">Continue Shopping</a></p>
</div>

<?php
include ROOT_PATH.'src/includes/footer.php';
?>
