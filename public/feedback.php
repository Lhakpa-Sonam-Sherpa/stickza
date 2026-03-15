<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/helpers/Validator.php';

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

<div class="container" style="max-width: 720px;">
    <div class="section-header" style="margin-bottom: 2rem;">
        <h1>Get in Touch</h1>
        <p>Have a question or suggestion? We'd love to hear from you.</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <div><?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?></div>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" id="feedbackForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div style="position:absolute; left: -9999px;" aria-hidden="true">
                <label for="website">Leave this empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="name">Your Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['name'] ?? $prefill_name); ?>"
                       maxlength="100" required autocomplete="name" placeholder="John Doe">
            </div>

            <div class="form-group">
                <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? $prefill_email); ?>"
                       maxlength="100" required autocomplete="email" placeholder="you@example.com"
                       <?php echo $logged_in_id ? 'readonly' : ''; ?>>
                <?php if ($logged_in_id): ?>
                <div class="form-hint">Email is linked to your account.</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="message">Message <span style="color:var(--danger)">*</span></label>
                <textarea id="message" name="message" class="form-control" rows="6"
                          maxlength="2000" placeholder="Tell us what's on your mind…"
                          required oninput="updateCounter(this)"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                <div id="charHint" class="form-hint" style="text-align: right;">0 / 2000</div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Send Message</button>
        </form>
    </div>
</div>

<style>
.form-card {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    box-shadow: var(--shadow-md);
}
</style>
<script>
function updateCounter(el) {
    const len  = el.value.length;
    const max  = 2000;
    const hint = document.getElementById('charHint');
    if (hint) {
        hint.textContent = len + ' / ' + max;
        hint.classList.toggle('over', len > max);
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const ta = document.getElementById('message');
    if (ta) updateCounter(ta);
});
</script>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
