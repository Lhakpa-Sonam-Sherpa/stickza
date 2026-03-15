<?php
/**
 * admin/orders/view.php
 *
 * Key changes from original:
 * • Cancel button calls $admin->cancelOrder() which restores stock in a
 *   DB transaction (HIGH PRIORITY FIX).
 * • updateOrderStatus() no longer accepts 'cancelled' – stock-safe cancel
 *   path only via cancelOrder().
 * • Every POST form is CSRF-protected.
 * • Print invoice button added (CSS print styles hide admin chrome).
 */

$page_title = "Order Details";
require_once './../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db       = $database->connect();
$admin    = new Admin($db);

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    header('Location: index.php');
    exit();
}

$order = $admin->getOrderWithCustomer($order_id);
if (!$order) {
    header('Location: index.php?error=not_found');
    exit();
}

$items   = $admin->getOrderItems($order_id);
$success = '';
$error   = '';

// ── Ensure CSRF token exists in session ──────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ── POST handler ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF token
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrf_token, $submitted)) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // ── Cancel order + restore stock ──────────────────────────────────
        if ($action === 'cancel_order') {
            $result = $admin->cancelOrder($order_id);

            if ($result === true) {
                $success               = 'Order #' . str_pad($order_id, 4, '0', STR_PAD_LEFT)
                                       . ' cancelled. Stock has been restored for all items.';
                $order['order_status'] = 'cancelled';
            } else {
                $error = is_string($result) ? htmlspecialchars($result) : 'Failed to cancel order.';
            }

        // ── Regular status update (cancelled blocked intentionally) ───────
        } elseif ($action === 'update_status') {
            $new_status = $_POST['status'] ?? '';

            if ($new_status === 'cancelled') {
                $error = 'Use the "Cancel Order" button to cancel — it also restores product stock.';
            } elseif ($admin->updateOrderStatus($order_id, $new_status)) {
                $success               = 'Status updated to ' . ucfirst($new_status) . '.';
                $order['order_status'] = $new_status;
            } else {
                $error = 'Failed to update order status.';
            }
        }
    }
}
 
$status_colors = [
    'pending'    => 'status-pending',
    'paid'       => 'status-processing',
    'processing' => 'status-processing',
    'shipped'    => 'status-shipped',
    'delivered'  => 'status-delivered', // Added for badge consistency
    'cancelled'  => 'status-cancelled',
];
$badge_class = $status_colors[$order['order_status']] ?? 'status-pending';

require_once '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h1>Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h1>
        <p style="color:var(--text-muted); margin-top:0.25rem;">
            Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?>
        </p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;" class="no-print">
        <span class="status-badge <?php echo $badge_class; ?>" style="font-size:0.8rem; padding:0.4rem 0.8rem;">
            <?php echo ucfirst($order['order_status']); ?>
        </span>
        <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
        <button onclick="window.print()" class="btn btn-secondary btn-sm"
                style="display:flex; align-items:center; gap:0.4rem;">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Invoice
        </button>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom:1.5rem;">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <?php echo $success; ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error" style="margin-bottom:1.5rem;">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<!-- Customer + Manage grid -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

    <!-- Customer Information -->
    <div class="content-card">
        <div class="card-header"><h2 class="card-title">Customer Information</h2></div>
        <div style="padding:1.25rem;">
            <table style="width:100%; border-collapse:collapse;">
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:0.75rem 0; color:var(--text-muted); width:38%;">Name</td>
                    <td style="padding:0.75rem 0; font-weight:500;"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:0.75rem 0; color:var(--text-muted);">Email</td>
                    <td style="padding:0.75rem 0;">
                        <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" style="color:var(--primary);">
                            <?php echo htmlspecialchars($order['customer_email']); ?>
                        </a>
                    </td>
                </tr>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:0.75rem 0; color:var(--text-muted);">Phone</td>
                    <td style="padding:0.75rem 0;"><?php echo htmlspecialchars($order['phone_no'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td style="padding:0.75rem 0; color:var(--text-muted);">Shipping Address</td>
                    <td style="padding:0.75rem 0;"><?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Manage Order (hidden in print) -->
    <div class="content-card no-print">
        <div class="card-header"><h2 class="card-title">Manage Order</h2></div>
        <div style="padding:1.25rem;">

            <?php if ($order['order_status'] === 'cancelled' || $order['order_status'] === 'delivered'): ?>
            <div style="padding:1rem; background:var(--<?php echo $order['order_status'] === 'cancelled' ? 'danger' : 'success'; ?>-light); border-radius:var(--radius);
                        color:var(--<?php echo $order['order_status'] === 'cancelled' ? 'danger' : 'success'; ?>); font-size:0.875rem; text-align:center; font-weight:500;">
                <?php if ($order['order_status'] === 'cancelled'): ?>
                    This order has been cancelled. Stock has already been restored.
                <?php else: ?>
                    This order has been marked as delivered.
                <?php endif; ?>
            </div>

        <?php else: ?>

        <!-- Status update form -->
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="action"     value="update_status">
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="font-size:0.8125rem; font-weight:500; display:block; margin-bottom:0.5rem;">
                    Update Status
                </label>
                <select name="status" class="form-control">
                    <?php
                    // Added 'delivered' to the dropdown options
                    $statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
                    foreach ($statuses as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $order['order_status'] === $s ? 'selected' : ''; ?>>
                        <?php echo ucfirst($s); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
        </form>

            <hr style="border:none; border-top:1px solid var(--border); margin:1.25rem 0;">

            <!-- Cancel form – calls cancelOrder() which restores stock -->
            <form method="POST"
                  onsubmit="return confirm('Cancel this order?\n\nStock will be restored for ALL items.');">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action"     value="cancel_order">
                <p style="font-size:0.8125rem; color:var(--text-muted); margin-bottom:0.75rem; line-height:1.5;">
                    Cancelling will automatically restore product stock for every item in this order.
                </p>
                <button type="submit" class="btn btn-danger btn-sm"
                        style="display:flex; align-items:center; gap:0.375rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel Order &amp; Restore Stock
                </button>
            </form>

            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Order Items</h2>
    </div>
    <div>
        <?php if (empty($items)): ?>
        <div class="empty-state"><p>No items found for this order.</p></div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <?php if (!empty($item['image_url'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>"
                             alt="" style="width:44px; height:44px; object-fit:cover;
                                          border-radius:var(--radius); border:1px solid var(--border);">
                        <?php endif; ?>
                        <span style="font-weight:500;"><?php echo htmlspecialchars($item['name']); ?></span>
                    </div>
                </td>
                <td>Rs <?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo (int)$item['quantity']; ?></td>
                <td style="font-weight:600;">Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right; padding:1rem; font-weight:600;">
                        Order Total
                    </td>
                    <td style="padding:1rem; font-weight:700; font-size:1rem; color:var(--primary);">
                        Rs <?php echo number_format($order['total_amount'], 2); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .no-print, .admin-header { display: none !important; }
    body { background: white; color: black; }
    .content-card { box-shadow: none !important; border: 1px solid #ccc !important; margin-bottom: 1rem; }
    .page-header h1 { font-size: 1.5rem; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
