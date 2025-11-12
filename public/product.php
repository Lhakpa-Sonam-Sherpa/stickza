<?php
// sticker-shop/public/product.php

// Include necessary files
require_once __DIR__.'/../src/config.php';
require_once ROOT_PATH.'src/classes/Database.php';
require_once ROOT_PATH.'src/classes/Product.php';


// Instantiate Database & Product
$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the product
$product = $product_manager->findById($product_id);

// Include the header
include ROOT_PATH . 'src/includes/header.php';

if (!$product):
?>
    <h1>Product Not Found</h1>
    <p>The sticker you are looking for does not exist or is out of stock.</p>
    <p><a href="<?php echo SITE_URL?>public/">Go back to the homepage</a></p>
<?php
else:
?>
    <div class="product-detail">
        <div class="product-image">
            <img src="/assets/images/products/<?php echo htmlspecialchars($product['image_url'] ?? 'placeholder.png'); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
            <p class="category">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
            
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <p class="stock">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span style="color: green;">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                <?php else: ?>
                    <span style="color: red;">Out of Stock</span>
                <?php endif; ?>
            </p>

            <?php if ($product['stock_quantity'] > 0): ?>
                <form action="<?php echo SITE_URL;?>public/cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
                    <button type="submit" name="action" value="add">Add to Cart</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php
endif;

// Include the footer
include ROOT_PATH . 'src/includes/footer.php';
?>
