<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';

$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $product_manager->findById($product_id);

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
            
            <div class="product-detail-price">Rs <?php echo number_format($product['price'], 2); ?></div>
            
            <?php if ($product['stock_quantity'] > 10): ?>
                <span class="stock-status in-stock">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
            <?php elseif ($product['stock_quantity'] > 0): ?>
                <span class="stock-status low-stock">Low Stock (<?php echo $product['stock_quantity']; ?> left)</span>
            <?php else: ?>
                <span class="stock-status out-of-stock">Out of Stock</span>
            <?php endif; ?>
            
            <p class="product-detail-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <?php if ($product['stock_quantity'] > 0): ?>
                <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
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
<?php
endif;

include ROOT_PATH . 'src/includes/footer.php';
?>