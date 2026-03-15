<?php
require_once __DIR__ . '/../src/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$theme = getCurrentTheme();
$page_title = 'Terms of Service';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
.policy-container { max-width: 780px; margin: 0 auto; padding: 2rem 0 4rem; }
.policy-container h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
.policy-container .meta { color: var(--text-muted); font-size: 0.875rem; margin-bottom: 2.5rem; }
.policy-section { margin-bottom: 2rem; }
.policy-section h2 { font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem; }
.policy-section p { color: var(--text-secondary); line-height: 1.7; margin-bottom: 0.75rem; }
.policy-section ul { color: var(--text-secondary); line-height: 1.7; padding-left: 1.5rem; }
.policy-section ul li { margin-bottom: 0.375rem; }
</style>

<div class="container policy-container">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem;">
        <div>
            <h1>Terms of Service</h1>
            <p class="meta">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
        <a href="settings.php" class="btn btn-secondary btn-sm">← Back</a>
    </div>

    <div class="policy-section">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using Stickza, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our store.</p>
    </div>

    <div class="policy-section">
        <h2>2. Products and Orders</h2>
        <p>All products are subject to availability. We reserve the right to limit quantities and refuse orders at our discretion. Prices are displayed in Nepali Rupees (Rs) and are subject to change without notice.</p>
        <ul>
            <li>Orders are confirmed only after successful payment processing.</li>
            <li>We reserve the right to cancel orders due to stock issues or pricing errors.</li>
            <li>Descriptions and images are as accurate as possible; minor variations may occur.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>3. Payment</h2>
        <p>All transactions are processed securely. By placing an order you confirm that the payment information you provide is accurate and that you are authorized to use the payment method. We do not store full payment card details.</p>
    </div>

    <div class="policy-section">
        <h2>4. Shipping and Delivery</h2>
        <p>Delivery times are estimates and not guaranteed. We are not liable for delays caused by shipping carriers, customs, or circumstances outside our control. Risk of loss transfers to you upon delivery to the carrier.</p>
    </div>

    <div class="policy-section">
        <h2>5. Returns and Refunds</h2>
        <p>We want you to be completely satisfied with your purchase. If you have an issue with your order, please contact us within 7 days of delivery. Refunds and replacements are handled on a case-by-case basis.</p>
    </div>

    <div class="policy-section">
        <h2>6. User Accounts</h2>
        <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. Please notify us immediately of any unauthorized use. We reserve the right to terminate accounts that violate these terms.</p>
    </div>

    <div class="policy-section">
        <h2>7. Intellectual Property</h2>
        <p>All content on Stickza, including product designs, images, and text, is owned by Stickza or its suppliers and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works without express written permission.</p>
    </div>

    <div class="policy-section">
        <h2>8. Limitation of Liability</h2>
        <p>Stickza shall not be liable for any indirect, incidental, or consequential damages arising from the use of our store or products. Our liability is limited to the purchase price of the product(s) in question.</p>
    </div>

    <div class="policy-section">
        <h2>9. Changes to Terms</h2>
        <p>We may update these Terms of Service at any time. Continued use of Stickza after changes are posted constitutes acceptance of the new terms.</p>
    </div>

    <div class="policy-section">
        <h2>10. Contact</h2>
        <p>If you have questions about these Terms, please <a href="feedback.php" style="color:var(--primary);">contact us</a>.</p>
    </div>
</div>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
