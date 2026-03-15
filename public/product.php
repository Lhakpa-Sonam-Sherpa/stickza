<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';

$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $product_manager->findById($product_id);

// Fetch related products (NEW)
$related_products = [];
if ($product && isset($product['category_id'])) {
    $related_products = $product_manager->findRelated($product['category_id'], $product['id'], 8);
}

$page_title = $product ? $product['name'] : 'Product Not Found';
include ROOT_PATH . 'src/includes/header.php';

if (!$product):
?>
    <div class="text-center" style="padding: 4rem 0;">
        <h1>Product Not Found</h1>
        <p style="color: var(--text-secondary); margin: 1rem 0;">The sticker you're looking for doesn't exist.</p>
        <a href="<?php echo SITE_URL; ?>public/" class="btn btn-primary">Browse All Stickers</a>
    </div>
<?php
else:
    $image_filename = !empty($product['image_url']) ? $product['image_url'] : 'placeholder.png';
?>
    <div class="product-detail fade-in">
        <div class="product-detail-image">
            <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($image_filename); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        
        <div class="product-detail-info">
            <div class="product-detail-meta">
                <span class="product-card-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Sticker'); ?></span>
            </div>
            
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="product-detail-price">
                <?php if (!is_null($product['discount_price']) || !is_null($product['discount_percent'])): ?>
                    <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.8em; margin-right: 0.5rem;">
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
            </div>
            
            <?php if ($product['stock_quantity'] > 10): ?>
                <span class="stock-status in-stock">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
            <?php elseif ($product['stock_quantity'] > 0): ?>
                <span class="stock-status low-stock">Low Stock (<?php echo $product['stock_quantity']; ?> left)</span>
            <?php else: ?>
                <span class="stock-status out-of-stock">Out of Stock</span>
            <?php endif; ?>
            
            <p class="product-detail-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <?php
                // Ensure stock_quantity is a positive integer for input max attribute
                $max_quantity = (isset($product['stock_quantity']) && is_numeric($product['stock_quantity']) && $product['stock_quantity'] > 0)
                    ? (int)$product['stock_quantity']
                    : 1;
            ?>
            <?php if ($product['stock_quantity'] > 0): ?>
                <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST">
                    <?php echo csrfField(); ?>
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $max_quantity; ?>" required>
                    </div>
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
    <!-- RELATED PRODUCTS SECTION (NEW) -->
    <?php if (!empty($related_products)): ?>
    <div class="related-products-section" style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--border);">
        <h2 style="text-align: center; font-size: 1.75rem; font-weight: 600; margin-bottom: 2.5rem;">Related Products</h2>
        <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
            <?php foreach ($related_products as $related_prod): ?>
                <article class="product-card fade-in">
                    <a href="<?php echo SITE_URL; ?>public/product.php?id=<?php echo $related_prod['id']; ?>">
                        <div class="product-card-image">
                            <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars(empty($related_prod['image_url']) ? 'placeholder.png' : $related_prod['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($related_prod['name']); ?>"
                                 loading="lazy">
                        </div>
                        <div class="product-card-content">
                            <div class="product-card-category"><?php echo htmlspecialchars($related_prod['category_name'] ?? 'Sticker'); ?></div>
                            <h3 class="product-card-title"><?php echo htmlspecialchars($related_prod['name']); ?></h3>
                            <div class="product-card-price">
                                <?php
                                if (
                                    (isset($related_prod['discount_price']) && !is_null($related_prod['discount_price'])) ||
                                    (isset($related_prod['discount_percent']) && !is_null($related_prod['discount_percent']))
                                ):
                                    ?>
                                    <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.85rem;">
                                        Rs <?php echo number_format($related_prod['price'], 2); ?>
                                    </span>
                                    <span style="color: var(--danger);">
                                        Rs <?php
                                            $final_related_price = $related_prod['price'];

                                            if (!is_null($related_prod['discount_price'])) {
                                                $final_related_price = $related_prod['discount_price'];
                                            } elseif (!is_null($related_prod['discount_percent'])) {
                                                $final_related_price = $related_prod['price'] * (1 - ($related_prod['discount_percent'] / 100));
                                            }

                                            echo number_format($final_related_price, 2);
                                        ?>
                                        </span>

                                <?php else: ?>
                                    Rs <?php echo number_format($related_prod['price'], 2); ?>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- END RELATED PRODUCTS SECTION -->
<?php
endif;

include ROOT_PATH . 'src/includes/footer.php';
?>
