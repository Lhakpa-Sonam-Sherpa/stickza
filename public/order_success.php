<?php
// sticker-shop/public/order_success.php

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . '/src/classes/Database.php';
require_once ROOT_PATH . '/src/classes/Order.php';

initSecureSession();

$database = new Database();
$db = $database->connect();
$order_manager = new Order($db);

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Security: Ensure the order belongs to the logged-in user
if ($order_id === 0 || !isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'public/');
    exit();
}

$order_details = $order_manager->getOrderDetailsForCustomer($order_id, $_SESSION['user_id']);

if (!$order_details) {
    // Order not found or doesn't belong to this user
    header('Location: ' . SITE_URL . 'public/profile.php');
    exit();
}

$page_title = 'Order Successful';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
/* Order Success Page Redesign */
.order-success-container {
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
    padding: 2rem 0;
}
.success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: var(--success-light);
    color: var(--success);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.success-icon svg {
    width: 40px;
    height: 40px;
}
.order-success-container h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}
.order-success-container .lead-text {
    font-size: 1.125rem;
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}
.order-number-box {
    background: var(--bg-tertiary);
    border: 1px dashed var(--border-strong);
    border-radius: var(--radius-md);
    padding: 1rem;
    margin-bottom: 2rem;
    font-size: 1rem;
}
.order-number-box span {
    color: var(--text-muted);
}
.order-number-box strong {
    font-weight: 600;
    color: var(--primary);
    font-size: 1.125rem;
}
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2.5rem;
}
.order-details-summary {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    text-align: left;
    margin-top: 2rem;
}
.order-details-summary h2 {
    font-size: 1.125rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
.cart-table {
    width: 100%;
    border-collapse: collapse;
}
.cart-table th, .cart-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
.cart-table th {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
}
.cart-table tfoot td {
    border-bottom: none;
    font-weight: 600;
}
</style>

<div class="order-success-container">
    <div class="success-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1>Order Placed Successfully!</h1>
    <p class="lead-text">Thank you for your purchase. Your order is being processed.</p>

    <div class="order-number-box">
        <span>Your Order Number:</span>
        <strong>#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></strong>
    </div>

    <p style="color: var(--text-secondary); margin-bottom: 2rem;">
        We will contact you shortly to arrange local delivery. Estimated delivery is within <strong>2-3 business days</strong>.
    </p>

    <div class="action-buttons">
        <a href="<?php echo SITE_URL; ?>public/" class="btn btn-primary">Continue Shopping</a>
        <button class="btn btn-secondary" onclick="alert('Invoice generation coming soon!');">Download Invoice</button>
    </div>

    <div class="order-details-summary">
        <h2>Order Details</h2>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th style="text-align: right;">Total</th>
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
                        <td style="text-align: right;">Rs <?php echo number_format($line_total, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Grand Total:</strong></td>
                    <td style="text-align: right;"><strong>Rs <?php echo number_format($grand_total, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php
include ROOT_PATH . 'src/includes/footer.php';
?>
