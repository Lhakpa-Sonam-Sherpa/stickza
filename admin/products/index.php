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
    // CSRF check for delete action
    if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        $error = "Invalid security token for delete action.";
    } else {
        try {
            $del = $db->prepare("DELETE FROM products WHERE id = :id");
            $del->execute([':id' => (int)$_GET['delete']]);
            $success = "Product deleted.";
        } catch (Exception $e) {
            $error = "Could not delete product.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="page-header-actions">
    <div>
        <h1>Products</h1>
        <p>Manage your sticker inventory (<?php echo $total; ?> total)</p>
    </div>
    <a href="add.php" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
</div>

<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<!-- Filter Card -->
<div class="content-card card-spacing">
    <div class="card-header"><h2 class="card-title">Filter Products</h2></div>
    <form method="GET" class="filter-form">
        <div>
            <label class="form-label">Product ID</label>
            <input type="number" name="product_id" class="form-control" placeholder="ID" value="<?php echo htmlspecialchars($filters['product_id']); ?>">
        </div>
        <div>
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" placeholder="Search name..." value="<?php echo htmlspecialchars($filters['name']); ?>">
        </div>
        <div>
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control" placeholder="Category..." value="<?php echo htmlspecialchars($filters['category']); ?>">
        </div>
        <div>
            <label class="form-label">Min Price (Rs)</label>
            <input type="number" name="price_min" class="form-control" placeholder="0" value="<?php echo htmlspecialchars($filters['price_min']); ?>">
        </div>
        <div>
            <label class="form-label">Max Price (Rs)</label>
            <input type="number" name="price_max" class="form-control" placeholder="9999" value="<?php echo htmlspecialchars($filters['price_max']); ?>">
        </div>
        <div>
            <label class="form-label">Max Stock</label>
            <input type="number" name="stock_max" class="form-control" placeholder="e.g. 5 for low stock" value="<?php echo htmlspecialchars($filters['stock_max']); ?>">
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="index.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Products</h2>
        <span class="meta-text">Showing <?php echo count($products); ?> of <?php echo $total; ?></span>
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
                <td class="col-image">
                    <?php if ($product['image_url']): ?>
                    <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="">
                    <?php else: ?>
                    <div style="width:40px; height:40px; background:var(--bg-tertiary); border-radius:var(--radius); border:1px solid var(--border);"></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-sku">#<?php echo $product['id']; ?></div>
                </td>
                <td><?php echo htmlspecialchars($product['category_name'] ?? '—'); ?></td>
                <td class="cell-primary">
                    <?php if (!is_null($product['discount_price']) || !is_null($product['discount_percent'])): ?>
                        <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.85em;">
                            Rs <?php echo number_format($product['price'], 2); ?>
                        </span>

                        <span style="color: var(--danger); font-weight: bold;">
                            Rs <?php
                                $final_price = $product['price'];
                                if (!is_null($product['discount_price'])) {
                                    $final_price = $product['discount_price'];
                                } elseif (!is_null($product['discount_percent'])) {
                                    $final_price = $product['price'] * (1 - ($product['discount_percent'] / 100));
                                }
                                echo number_format($final_price, 2);
                            ?>
                        </span>
                    <?php else: ?>
                        Rs <?php echo number_format($product['price'], 2); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($product['stock_quantity'] == 0): ?>
                        <span class="stock-badge stock-out">Out of Stock</span>
                    <?php elseif ($product['stock_quantity'] < 5): ?>
                        <span class="stock-badge stock-low"><?php echo $product['stock_quantity']; ?> left</span>
                    <?php else: ?>
                        <span class="stock-badge stock-in"><?php echo $product['stock_quantity']; ?></span>
                    <?php endif; ?>
                </td>
                <td class="col-actions">
                    <div class="actions">
                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="?delete=<?php echo $product['id']; ?>&token=<?php echo generateCSRFToken(); ?>&<?php echo http_build_query(array_filter($filters  )); ?>"
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
<div class="pagination">
    <?php
    $qs = http_build_query(array_filter($filters  ));
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

