<?php
/**
 * public/feedback.php
 *
 * Enhanced features:
 * • CSRF protection
 * • Honeypot anti-spam field (hidden, must be empty to pass)
 * • Pre-fill name + email when user is logged in
 * • Live character counter (max 2000)
 * • Binds customer_id when a logged-in user submits
 * • Success message with form reset
 */

require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$database = new Database();
$db       = $database->connect();

$errors  = [];
$success = '';

// Pre-fill when logged in
$prefill_name    = '';
$prefill_email   = '';
$logged_in_id    = null;

if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare(
        "SELECT id, first_name, last_name, email FROM customers WHERE id = :id"
    );
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $prefill_name  = trim($u['first_name'] . ' ' . $u['last_name']);
        $prefill_email = $u['email'];
        $logged_in_id  = $u['id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    $submitted_csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrf_token, $submitted_csrf)) {
        $errors[] = 'Security token invalid. Please refresh and try again.';
    }

    // Honeypot check – bots fill hidden fields; humans don't
    if (!empty($_POST['website'])) {
        // Silently succeed to not alert the bot
        $success = "Thank you for your feedback! We'll get back to you soon.";
        goto render;
    }

    if (empty($errors)) {
        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name))                                          $errors[] = 'Name is required.';
        if (mb_strlen($name) > 100)                               $errors[] = 'Name is too long (max 100 characters).';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
        if (empty($message))                                       $errors[] = 'Message cannot be empty.';
        if (mb_strlen($message) > 2000)                           $errors[] = 'Message is too long (max 2000 characters).';

        if (empty($errors)) {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO feedback (customer_id, name, email, message, status)
                     VALUES (:cid, :name, :email, :message, 'new')"
                );
                $stmt->execute([
                    ':cid'     => $logged_in_id,
                    ':name'    => $name,
                    ':email'   => $email,
                    ':message' => $message,
                ]);
                $success = "Thank you! Your message has been received. We'll be in touch soon.";
                // Clear posted values so form resets
                $_POST = [];
            } catch (Exception $e) {
                error_log('Feedback insert error: ' . $e->getMessage());
                $errors[] = 'Something went wrong. Please try again later.';
            }
        }
    }
}

render:
$theme      = getCurrentTheme();
$page_title = 'Contact & Feedback';
include ROOT_PATH . 'src/includes/header.php';
?>

<style>
.feedback-wrap  { max-width: 620px; margin: 0 auto; padding: 2rem 0 3rem; }
.feedback-head  { text-align: center; margin-bottom: 2rem; }
.feedback-head h1 { font-size: 1.875rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
.feedback-head p  { color: var(--text-secondary); }
.feedback-card  { background: var(--bg-primary); border: 1px solid var(--border);
                  border-radius: var(--radius-lg); padding: 2rem; }
.char-hint      { text-align: right; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
.char-hint.over { color: var(--danger); }
/* Honeypot must be invisible */
.hp-field       { position: absolute; left: -9999px; opacity: 0; tab-index: -1; }
</style>

<div class="container feedback-wrap">
    <div class="feedback-head">
        <h1>Get in Touch</h1>
        <p>Have a question or suggestion? We'd love to hear from you.</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <div><?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?></div>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="feedback-card">
        <form method="POST" id="feedbackForm">
            <!-- CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <!-- Honeypot (invisible to humans, bots fill it) -->
            <div class="hp-field" aria-hidden="true">
                <label for="website">Leave this empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="name">Your Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['name'] ?? $prefill_name); ?>"
                       maxlength="100" required autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? $prefill_email); ?>"
                       maxlength="100" required autocomplete="email"
                       <?php echo $logged_in_id ? 'readonly style="background:var(--bg-tertiary);"' : ''; ?>>
                <?php if ($logged_in_id): ?>
                <div class="form-hint">Email is linked to your account.</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="message">Message <span style="color:var(--danger)">*</span></label>
                <textarea id="message" name="message" class="form-control" rows="6"
                          maxlength="2000" placeholder="Tell us what's on your mind…"
                          required oninput="updateCounter(this)"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                <div class="char-hint" id="charHint">0 / 2000</div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:0.875rem; font-size:1rem;">
                Send Message
            </button>
        </form>
    </div>
</div>

<script>
function updateCounter(el) {
    const len  = el.value.length;
    const max  = 2000;
    const hint = document.getElementById('charHint');
    if (hint) {
        hint.textContent = len + ' / ' + max;
        hint.className   = 'char-hint' + (len > max ? ' over' : '');
    }
}
// Initialise counter on page load (value may be repopulated after error)
document.addEventListener('DOMContentLoaded', function () {
    const ta = document.getElementById('message');
    if (ta) updateCounter(ta);
});
</script>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
