<?php
// sticker-shop/public/product.php

// Include necessary files
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';


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
    <p><a href="<?php echo SITE_URL ?>public/">Go back to the homepage</a></p>
<?php
else:
?>
    <div class="product-detail">
        <?php
        $image_filename = !empty($product['image_url']) ? $product['image_url'] : 'placeholder.png';

        $safe_filename = htmlspecialchars($image_filename);
        $full_image_path = SITE_URL . 'public/assets/images/products/' . $safe_filename;
        ?>
        <div class="product-image"
            style="background-image: url('<?php echo $full_image_path; ?>');"
            role="img"
            aria-label="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
            <p class="category"><b>Category:</b> <?php echo htmlspecialchars($product['category_name']); ?></p>

            <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <p class="stock">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span style="color: green;">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                <?php else: ?>
                    <span style="color: red;">Out of Stock</span>
                <?php endif; ?>
            </p>

            <?php if ($product['stock_quantity'] > 0): ?>
                <form action="<?php echo SITE_URL; ?>public/cart.php" method="POST">
                    <div>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required><br>
                    </div>
                    <div>
                        <button type="submit" name="action" value="add">Add to Cart</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="main-content">
    <?php
endif;
$all_products = $product_manager->fetchAll();
if (empty($all_products)): ?>
        <p>No stickers are currently available. Please add some products to the database!</p>
    <?php
else: ?>
        <div class="product-grid">
            <?php foreach ($all_products as $prod): ?>
                <div class="product-card">
                    <a href="<?php echo SITE_URL ?>public/product.php?id=<?php echo $prod['id']; ?>">
                        <?php
                        $image_filename = !empty($prod['image_url']) ? $prod['image_url'] : 'placeholder.png';
                        $safe_filename = htmlspecialchars($image_filename);
                        $full_image_path = SITE_URL . 'public/assets/images/products/' . $safe_filename;
                        ?>
                        <div class="product-image"
                            style="background-image: url('<?php echo $full_image_path; ?>');"
                            role="img"
                            aria-label="<?php echo htmlspecialchars($prod['name']); ?>">
                        </div>
                        <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <p>$<?php echo number_format($prod['price'], 2); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
endif;

// Include the footer
include ROOT_PATH . 'src/includes/footer.php';
?>