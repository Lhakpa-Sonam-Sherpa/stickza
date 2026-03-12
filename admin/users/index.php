<?php
$page_title = "Manage Users";
require_once './../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin_obj = new Admin($db);

$filters = [
    'user_id'   => trim($_GET['user_id'] ?? ''),
    'name'      => trim($_GET['name'] ?? ''),
    'email'     => trim($_GET['email'] ?? ''),
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
];

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

$result = $admin_obj->getUsers($filters, $page, $limit);
$users  = $result['data'];
$total  = $result['total'];
$total_pages = ceil($total / $limit);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Users</h1>
    <p>Manage registered customers (<?php echo $total; ?> total)</p>
</div>

<!-- Filter Card -->
<div class="content-card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h2 class="card-title">Filter Users</h2></div>
    <div style="padding: 1.25rem;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">User ID</label>
                <input type="number" name="user_id" class="form-control" placeholder="e.g. 5" value="<?php echo htmlspecialchars($filters['user_id']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Name</label>
                <input type="text" name="name" class="form-control" placeholder="Search name..." value="<?php echo htmlspecialchars($filters['name']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Email</label>
                <input type="text" name="email" class="form-control" placeholder="Search email..." value="<?php echo htmlspecialchars($filters['email']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Registered From</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Registered To</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Users</h2>
        <span style="font-size:0.8125rem; color:var(--text-muted);">Showing <?php echo count($users); ?> of <?php echo $total; ?></span>
    </div>
    <div>
        <?php if (empty($users)): ?>
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <h3>No users found</h3>
            <p>Try adjusting your search filters.</p>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>City</th>
                    <th>Phone</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td style="color:var(--text-muted);">#<?php echo $user['id']; ?></td>
                <td style="font-weight:500; color:var(--text-primary);"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['city'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($user['phone_no'] ?? '—'); ?></td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div style="display:flex; gap:0.5rem; justify-content:center; margin-top:1.5rem; flex-wrap:wrap;">
    <?php
    $qs = http_build_query(array_filter(['user_id'=>$filters['user_id'],'name'=>$filters['name'],'email'=>$filters['email'],'date_from'=>$filters['date_from'],'date_to'=>$filters['date_to']]));
    for ($p = 1; $p <= $total_pages; $p++):
    ?>
    <a href="?<?php echo $qs; ?>&page=<?php echo $p; ?>" 
       class="btn btn-sm <?php echo $p === $page ? 'btn-primary' : 'btn-secondary'; ?>">
        <?php echo $p; ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
