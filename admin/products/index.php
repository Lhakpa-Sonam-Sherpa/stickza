<?php
$page_title = "Manage Products";

require_once './../admin_init.php';
require_once '../../src/classes/Database.php';
require_once '../../src/classes/Product.php';

$db = (new Database())->connect();
$productObj = new Product($db);
$products = $productObj->getAllProducts();

require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Products</h1>
    <p>Manage your sticker inventory</p>
</div>

<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">All Products</h2>
        <a href="add.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Product
        </a>
    </div>
    
    <div class="card-body" style="padding: 0;">
        <?php if (count($products) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>#<?php echo $p['id']; ?></td>
                    <td>
                        <img src="../../public/assets/images/products/<?php echo htmlspecialchars($p['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($p['name']); ?>">
                    </td>
                    <td>
                        <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div class="product-sku">SKU: STK-<?php echo str_pad($p['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </td>
                    <td>Rs <?php echo number_format($p['price'], 2); ?></td>
                    <td>
                        <?php if ($p['stock_quantity'] > 10): ?>
                            <span style="color: var(--success); font-weight: 600;"><?php echo $p['stock_quantity']; ?> in stock</span>
                        <?php elseif ($p['stock_quantity'] > 0): ?>
                            <span style="color: var(--warning); font-weight: 600;"><?php echo $p['stock_quantity']; ?> low stock</span>
                        <?php else: ?>
                            <span style="color: var(--danger); font-weight: 600;">Out of stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="delete.php?id=<?php echo $p['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <h3>No products found</h3>
            <p>Get started by adding your first product.</p>
            <br>
            <a href="add.php" class="btn btn-primary">Add Product</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>