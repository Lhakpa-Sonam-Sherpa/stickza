<?php
/**
 * admin/feedback/index.php
 *
 * New features vs original:
 * • Status filter tabs  – All / New / Read / Replied
 * • Per-row buttons     – Mark Read, Mark Replied, Delete (each separate form)
 * • Bulk delete         – checkbox select → "Delete Selected (N)" button
 * • CSRF protection on every state-changing POST
 * • "N new" badge in page title
 */

$page_title = 'Feedback';
require_once '../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db       = $database->connect();
$admin    = new Admin($db);

// Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$success = '';
$error   = '';

// ── POST handler ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrf_token, $submitted)) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        $post_action = $_POST['post_action'] ?? '';

        if ($post_action === 'update_status') {
            $fid    = (int)($_POST['feedback_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($fid && $admin->updateFeedbackStatus($fid, $status)) {
                $success = 'Marked as ' . ucfirst(htmlspecialchars($status)) . '.';
            } else {
                $error = 'Failed to update status.';
            }

        } elseif ($post_action === 'delete_single') {
            $fid = (int)($_POST['feedback_id'] ?? 0);
            if ($fid && $admin->deleteFeedback($fid)) {
                $success = 'Feedback deleted.';
            } else {
                $error = 'Failed to delete.';
            }

        } elseif ($post_action === 'bulk_delete') {
            $ids = array_filter(array_map('intval', (array)($_POST['selected_ids'] ?? [])));
            if (empty($ids)) {
                $error = 'No items selected.';
            } elseif ($admin->deleteFeedbackBulk($ids)) {
                $success = count($ids) . ' ' . (count($ids) === 1 ? 'entry' : 'entries') . ' deleted.';
            } else {
                $error = 'Bulk delete failed.';
            }
        }
    }
}

// ── Query params ─────────────────────────────────────────────────────────────
$page          = max(1, (int)($_GET['page'] ?? 1));
$limit         = 20;
$allowed_tabs  = ['all', 'new', 'read', 'replied'];
$status_filter = in_array($_GET['status'] ?? '', $allowed_tabs) ? $_GET['status'] : 'all';

$result      = $admin->getFeedback($page, $limit, $status_filter);
$feedbacks   = $result['data'];
$total       = $result['total'];
$total_pages = (int)ceil($total / $limit);

try {
    $unread = (int)$db->query("SELECT COUNT(*) FROM feedback WHERE status='new'")->fetchColumn();
} catch (Exception $e) {
    $unread = 0;
}

require_once '../includes/header.php';
?>

<!-- Page header -->
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div>
        <h1 style="display:flex; align-items:center; gap:0.5rem;">
            Customer Feedback
            <?php if ($unread > 0): ?>
            <span style="background:var(--danger); color:#fff; font-size:0.7rem; font-weight:700;
                         padding:0.15rem 0.5rem; border-radius:99px;">
                <?php echo $unread; ?> new
            </span>
            <?php endif; ?>
        </h1>
        <p style="color:var(--text-muted); margin-top:0.2rem;">Contact form submissions from customers</p>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom:1.25rem;">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <?php echo $success; ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error" style="margin-bottom:1.25rem;">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<!-- Status filter tabs -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem;">
    <?php foreach (['all' => 'All', 'new' => 'New', 'read' => 'Read', 'replied' => 'Replied'] as $val => $label): ?>
    <a href="?status=<?php echo $val; ?>"
       class="btn btn-sm <?php echo $status_filter === $val ? 'btn-primary' : 'btn-secondary'; ?>">
        <?php echo $label; ?>
        <?php if ($val === 'new' && $unread > 0): ?>
        <span style="background:rgba(255,255,255,0.3); border-radius:99px;
                     padding:0 0.35rem; font-size:0.7rem; margin-left:0.25rem;">
            <?php echo $unread; ?>
        </span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Bulk-delete wrapper form -->
<form method="POST" id="bulkForm">
    <input type="hidden" name="csrf_token"  value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="post_action" value="bulk_delete">

    <div class="content-card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="card-title">
                <?php echo $status_filter === 'all' ? 'All Feedback' : ucfirst($status_filter) . ' Feedback'; ?>
            </h2>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:0.8125rem; color:var(--text-muted);">
                    Showing <?php echo count($feedbacks); ?> of <?php echo $total; ?>
                </span>
                <button type="submit" id="bulkDeleteBtn" class="btn btn-sm btn-danger"
                        style="display:none;" onclick="return confirmBulk()">
                    Delete Selected
                </button>
            </div>
        </div>

        <?php if (empty($feedbacks)): ?>
        <div class="empty-state" style="padding:3rem; text-align:center;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="40"
                 style="margin:0 auto 1rem; color:var(--text-muted);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <h3>No feedback found</h3>
            <p>Try a different filter tab.</p>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" id="selectAll" style="cursor:pointer;" title="Select all">
                    </th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $badge_map = [
                'new'     => 'status-pending',
                'read'    => 'status-processing',
                'replied' => 'status-shipped',
            ];
            foreach ($feedbacks as $fb):
                $badge    = $badge_map[$fb['status']] ?? 'status-pending';
                $preview  = mb_strlen($fb['message']) > 110
                          ? mb_substr($fb['message'], 0, 110) . '…'
                          : $fb['message'];
            ?>
            <tr>
                <td>
                    <input type="checkbox" name="selected_ids[]" value="<?php echo $fb['id']; ?>"
                           class="row-cb" style="cursor:pointer;">
                </td>
                <td style="font-weight:500; white-space:nowrap;">
                    <?php echo htmlspecialchars($fb['name']); ?>
                </td>
                <td style="white-space:nowrap;">
                    <a href="mailto:<?php echo htmlspecialchars($fb['email']); ?>"
                       style="color:var(--primary); font-size:0.8rem;">
                        <?php echo htmlspecialchars($fb['email']); ?>
                    </a>
                </td>
                <td style="max-width:260px; font-size:0.8125rem; color:var(--text-secondary);"
                    title="<?php echo htmlspecialchars($fb['message']); ?>">
                    <?php echo htmlspecialchars($preview); ?>
                </td>
                <td>
                    <span class="status-badge <?php echo $badge; ?>"><?php echo ucfirst($fb['status']); ?></span>
                </td>
                <td style="white-space:nowrap; color:var(--text-muted); font-size:0.8rem;">
                    <?php echo date('M d, Y', strtotime($fb['submitted_at'])); ?>
                </td>
                <td>
                    <div style="display:flex; gap:0.3rem; flex-wrap:nowrap;">
                        <!-- Mark Read (only if 'new') -->
                        <?php if ($fb['status'] === 'new'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token"  value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="post_action" value="update_status">
                            <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                            <input type="hidden" name="status"      value="read">
                            <button type="submit" class="btn btn-sm btn-secondary" title="Mark as Read">
                                Read
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Mark Replied (if not already) -->
                        <?php if ($fb['status'] !== 'replied'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token"  value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="post_action" value="update_status">
                            <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                            <input type="hidden" name="status"      value="replied">
                            <button type="submit" class="btn btn-sm btn-secondary" title="Mark as Replied">
                                Replied
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Delete single -->
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Delete this feedback?');">
                            <input type="hidden" name="csrf_token"  value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="post_action" value="delete_single">
                            <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div><!-- .content-card -->
</form>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div style="display:flex; gap:0.5rem; justify-content:center; margin-top:1.5rem; flex-wrap:wrap;">
    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
    <a href="?status=<?php echo urlencode($status_filter); ?>&page=<?php echo $p; ?>"
       class="btn btn-sm <?php echo $p === $page ? 'btn-primary' : 'btn-secondary'; ?>">
        <?php echo $p; ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<script>
const selectAll = document.getElementById('selectAll');
const bulkBtn   = document.getElementById('bulkDeleteBtn');

selectAll?.addEventListener('change', function () {
    document.querySelectorAll('.row-cb').forEach(cb => { cb.checked = this.checked; });
    refreshBulkBtn();
});
document.querySelectorAll('.row-cb').forEach(cb => {
    cb.addEventListener('change', refreshBulkBtn);
});

function refreshBulkBtn() {
    const n = document.querySelectorAll('.row-cb:checked').length;
    if (bulkBtn) {
        bulkBtn.style.display = n > 0 ? 'inline-flex' : 'none';
        bulkBtn.textContent   = 'Delete Selected (' + n + ')';
    }
}
function confirmBulk() {
    const n = document.querySelectorAll('.row-cb:checked').length;
    return confirm('Permanently delete ' + n + ' feedback ' + (n === 1 ? 'entry' : 'entries') + '?\nThis cannot be undone.');
}
</script>

<?php require_once '../includes/footer.php'; ?>
