<?php
require_once __DIR__ . '/../src/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$theme = getCurrentTheme();
$page_title = 'Privacy Policy';
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
            <h1>Privacy Policy</h1>
            <p class="meta">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
        <a href="settings.php" class="btn btn-secondary btn-sm">← Back</a>
    </div>

    <div class="policy-section">
        <h2>1. Information We Collect</h2>
        <p>When you create an account or place an order on Stickza, we collect information you provide directly, including:</p>
        <ul>
            <li>Name, email address, and password</li>
            <li>Shipping address and city</li>
            <li>Phone number (optional)</li>
            <li>Order history and transaction details</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
            <li>Process and fulfill your orders</li>
            <li>Send you order confirmations and updates</li>
            <li>Respond to your questions and feedback</li>
            <li>Improve our store and product offerings</li>
            <li>Prevent fraudulent transactions</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>3. Data Storage and Security</h2>
        <p>Your data is stored securely on our servers. Passwords are hashed using industry-standard bcrypt encryption and are never stored in plain text. We take reasonable technical and organizational measures to protect your personal information.</p>
    </div>

    <div class="policy-section">
        <h2>4. Cookies</h2>
        <p>We use session cookies to keep you logged in and remember your preferences (such as your theme setting). We do not use third-party advertising cookies or tracking pixels.</p>
    </div>

    <div class="policy-section">
        <h2>5. Third-Party Sharing</h2>
        <p>We do not sell, trade, or otherwise transfer your personal information to third parties. We may share information with trusted service providers who assist us in operating our website and serving you, subject to confidentiality agreements.</p>
    </div>

    <div class="policy-section">
        <h2>6. Your Rights</h2>
        <p>You have the right to access, correct, or delete your personal information. You can update your profile at any time through your <a href="settings.php" style="color:var(--primary);">Account Settings</a>. To request deletion of your account, please <a href="feedback.php" style="color:var(--primary);">contact us</a>.</p>
    </div>

    <div class="policy-section">
        <h2>7. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting a notice on our website. Your continued use of Stickza after changes constitutes acceptance of the updated policy.</p>
    </div>

    <div class="policy-section">
        <h2>8. Contact Us</h2>
        <p>If you have questions about this Privacy Policy, please <a href="feedback.php" style="color:var(--primary);">contact us</a>.</p>
    </div>
</div>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
