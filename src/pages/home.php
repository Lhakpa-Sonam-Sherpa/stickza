<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/src/classes/Database.php';
require_once ROOT_PATH . '/src/classes/Product.php';

$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);
$all_products = $product_manager->fetchAll();

include ROOT_PATH . 'src/includes/header.php';
?>
<!-- <?php
// At top of src/pages/home.php
echo '<pre>SESSION: ';
print_r($_SESSION);
echo '</pre>';
?> -->
<div class="section-header fade-in">
</div>

<?php if (empty($all_products)): ?>
    <div class="empty-cart">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3>No stickers available</h3>
        <p>Check back soon for new arrivals!</p>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($all_products as $prod): ?>
            <article class="product-card fade-in">
                <a href="<?php echo SITE_URL; ?>public/product.php?id=<?php echo $prod['id']; ?>">
                    <div class="product-card-image">
                        <?php
                        $image_filename = !empty($prod['image_url']) ? $prod['image_url'] : 'placeholder.png';
                        ?>
                        <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($image_filename); ?>" 
                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                             loading="lazy">
                        <?php if ($prod['stock_quantity'] < 5 && $prod['stock_quantity'] > 0): ?>
                            <span class="product-card-badge">Low Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-content">
                        <div class="product-card-category"><?php echo htmlspecialchars($prod['category_name'] ?? 'Sticker'); ?></div>
                        <h3 class="product-card-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <div class="product-card-price">Rs <?php echo number_format($prod['price'], 2); ?></div>
                        <div class="product-card-footer">
                            <span class="btn btn-primary btn-full">View Details →</span>
                        </div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>