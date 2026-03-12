<?php
$page_title = "Manage Products";
require_once './../admin_init.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

$database = new Database();
$db = $database->connect();
$admin_obj = new Admin($db);

$filters = [
    'product_id' => trim($_GET['product_id'] ?? ''),
    'name'       => trim($_GET['name'] ?? ''),
    'category'   => trim($_GET['category'] ?? ''),
    'price_min'  => $_GET['price_min'] ?? '',
    'price_max'  => $_GET['price_max'] ?? '',
    'stock_max'  => $_GET['stock_max'] ?? '',
];

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

$result   = $admin_obj->getProducts($filters, $page, $limit);
$products = $result['data'];
$total    = $result['total'];
$total_pages = ceil($total / $limit);

// Handle delete
$success = $error = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $del = $db->prepare("DELETE FROM products WHERE id = :id");
        $del->execute([':id' => (int)$_GET['delete']]);
        $success = "Product deleted.";
    } catch (Exception $e) {
        $error = "Could not delete product.";
    }
}

require_once '../includes/header.php';
?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1>Products</h1>
        <p>Manage your sticker inventory (<?php echo $total; ?> total)</p>
    </div>
    <a href="add.php" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Filter Card -->
<div class="content-card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h2 class="card-title">Filter Products</h2></div>
    <div style="padding: 1.25rem;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Product ID</label>
                <input type="number" name="product_id" class="form-control" placeholder="ID" value="<?php echo htmlspecialchars($filters['product_id']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Name</label>
                <input type="text" name="name" class="form-control" placeholder="Search name..." value="<?php echo htmlspecialchars($filters['name']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Category</label>
                <input type="text" name="category" class="form-control" placeholder="Category..." value="<?php echo htmlspecialchars($filters['category']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Min Price (Rs)</label>
                <input type="number" name="price_min" class="form-control" placeholder="0" value="<?php echo htmlspecialchars($filters['price_min']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Max Price (Rs)</label>
                <input type="number" name="price_max" class="form-control" placeholder="9999" value="<?php echo htmlspecialchars($filters['price_max']); ?>">
            </div>
            <div>
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem;">Max Stock</label>
                <input type="number" name="stock_max" class="form-control" placeholder="e.g. 5 for low stock" value="<?php echo htmlspecialchars($filters['stock_max']); ?>">
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Products</h2>
        <span style="font-size:0.8125rem; color:var(--text-muted);">Showing <?php echo count($products); ?> of <?php echo $total; ?></span>
    </div>
    <div>
        <?php if (empty($products)): ?>
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <h3>No products found</h3>
            <p>Try adjusting your filters or <a href="add.php">add a new product</a>.</p>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <?php if ($product['image_url']): ?>
                    <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="" style="width:44px; height:44px; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                    <?php else: ?>
                    <div style="width:44px; height:44px; background:var(--bg-tertiary); border-radius:var(--radius); border:1px solid var(--border);"></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-sku">#<?php echo $product['id']; ?></div>
                </td>
                <td><?php echo htmlspecialchars($product['category_name'] ?? '—'); ?></td>
                <td style="font-weight:500;">Rs <?php echo number_format($product['price'], 2); ?></td>
                <td>
                    <?php if ($product['stock_quantity'] == 0): ?>
                        <span class="stock-badge stock-out">Out of Stock</span>
                    <?php elseif ($product['stock_quantity'] < 5): ?>
                        <span class="stock-badge stock-low"><?php echo $product['stock_quantity']; ?> left</span>
                    <?php else: ?>
                        <span class="stock-badge stock-in"><?php echo $product['stock_quantity']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions">
                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="?delete=<?php echo $product['id']; ?>&<?php echo http_build_query(array_filter($filters)); ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this product?')">Delete</a>
                    </div>
                </td>
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
    $qs = http_build_query(array_filter($filters));
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
