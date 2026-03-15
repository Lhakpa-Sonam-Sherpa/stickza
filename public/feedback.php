<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$database = new Database();
$db = $database->connect();

$errors  = [];
$success = '';
$prefill_name = '';
$prefill_email = '';
$logged_in_id = null;

if (!empty($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT id, first_name, last_name, email FROM customers WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u) {
        $prefill_name  = trim($u['first_name'] . ' ' . $u['last_name']);
        $prefill_email = $u['email'];
        $logged_in_id  = $u['id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    if (!empty($_POST['website'])) { // Honeypot check
        $success = "Thank you for your feedback! We'll get back to you soon.";
        goto render;
    }

    $validator = new Validator($_POST);
    $validator->required('name', 'Name')->maxLength('name', 100)
              ->required('email', 'Email')->email('email')
              ->required('message', 'Message')->maxLength('message', 2000);

    if ($validator->fails()) {
        $errors = $validator->errors();
    } else {
        try {
            $stmt = $db->prepare(
                "INSERT INTO feedback (customer_id, name, email, message, status)
                 VALUES (:cid, :name, :email, :message, 'new')"
            );
            $stmt->execute([
                ':cid'     => $logged_in_id,
                ':name'    => trim($_POST['name']),
                ':email'   => trim($_POST['email']),
                ':message' => trim($_POST['message']),
            ]);
            $success = "Thank you! Your message has been received. We'll be in touch soon.";
            $_POST = []; // Clear form on success
        } catch (Exception $e) {
            error_log('Feedback insert error: ' . $e->getMessage());
            $errors[] = 'Something went wrong. Please try again later.';
        }
    }
}

render:
$theme = getCurrentTheme();
$page_title = 'Contact & Feedback';
include ROOT_PATH . 'src/includes/header.php';
?>

<!-- The HTML and JavaScript for this file remain unchanged. -->
<style>
.feedback-wrap {
    max-width: 680px;
    margin: 0 auto;
    padding: 3rem 2rem 4rem;
}

.feedback-head {
    text-align: center;
    margin-bottom: 2.5rem;
}

.feedback-head h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
    letter-spacing: -0.02em;
}

.feedback-head p {
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.5;
}

.feedback-card {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.feedback-form {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.form-group-fb {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.form-group-fb label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--text-muted);
}

.form-group-fb label span {
    color: var(--danger);
}

.form-control-fb {
    padding: 0.875rem 1rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 0.875rem;
    background: var(--bg-primary);
    color: var(--text-primary);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    font-family: inherit;
}

.form-control-fb:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.form-control-fb:disabled,
.form-control-fb:read-only {
    background: var(--bg-tertiary);
    cursor: not-allowed;
}

textarea.form-control-fb {
    min-height: 150px;
    resize: vertical;
    line-height: 1.6;
}

.char-counter-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
    font-size: 0.8125rem;
}

.char-hint {
    text-align: right;
    font-size: 0.8125rem;
    color: var(--text-muted);
    font-weight: 500;
}

.char-hint.over {
    color: var(--danger);
    font-weight: 600;
}

.char-hint-text {
    color: var(--text-muted);
    font-size: 0.8125rem;
}

.hp-field {
    position: absolute;
    left: -9999px;
    opacity: 0;
}

.form-actions-fb {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1.75rem;
    border-top: 1px solid var(--border);
}

.btn-submit-fb {
    flex: 1;
    padding: 0.875rem 1.5rem;
    background: var(--primary);
    color: var(--white);
    border: none;
    border-radius: var(--radius);
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-submit-fb:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-submit-fb:active {
    transform: translateY(0);
}

.btn-submit-fb svg {
    width: 18px;
    height: 18px;
}

@media (max-width: 640px) {
    .feedback-wrap {
        padding: 2rem 1rem 3rem;
    }

    .feedback-head h1 {
        font-size: 1.5rem;
    }

    .feedback-card {
        padding: 1.75rem;
    }
}
</style>

<div class="container feedback-wrap">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2.5rem;">
        <div class="feedback-head" style="text-align:left; margin-bottom:0;">
            <h1 style="margin-bottom:0.25rem;">Get in Touch</h1>
            <p style="margin-bottom:0;">Have a question or suggestion? We'd love to hear from you.</p>
        </div>
        <a href="settings.php" class="btn btn-secondary btn-sm">← Back</a>
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
        <form method="POST" id="feedbackForm" class="feedback-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="hp-field" aria-hidden="true">
                <label for="website">Leave this empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group-fb">
                <label for="name">Your Name <span>*</span></label>
                <input type="text" id="name" name="name" class="form-control-fb"
                       value="<?php echo htmlspecialchars($_POST['name'] ?? $prefill_name); ?>"
                       maxlength="100" required autocomplete="name" placeholder="John Doe">
            </div>

            <div class="form-group-fb">
                <label for="email">Email Address <span>*</span></label>
                <input type="email" id="email" name="email" class="form-control-fb"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? $prefill_email); ?>"
                       maxlength="100" required autocomplete="email" placeholder="you@example.com"
                       <?php echo $logged_in_id ? 'readonly style="background:var(--bg-tertiary);"' : ''; ?>>
                <?php if ($logged_in_id): ?>
                <div class="char-hint-text">✓ Email is linked to your account</div>
                <?php endif; ?>
            </div>

            <div class="form-group-fb">
                <label for="message">Message <span>*</span></label>
                <textarea id="message" name="message" class="form-control-fb" rows="7"
                          maxlength="2000" placeholder="Tell us what's on your mind…"
                          required oninput="updateCounter(this)"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                <div class="char-counter-container">
                    <div class="char-hint-text">Maximum 2,000 characters</div>
                    <div class="char-hint" id="charHint">0 / 2000</div>
                </div>
            </div>

            <div class="form-actions-fb">
                <button type="submit" class="btn-submit-fb">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Send Message
                </button>
            </div>
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
document.addEventListener('DOMContentLoaded', function () {
    const ta = document.getElementById('message');
    if (ta) updateCounter(ta);
});
</script>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
