<?php
// sticker-shop/src/pages/home.php

require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/src/classes/Database.php';
require_once ROOT_PATH . '/src/classes/Product.php';

// Instantiate Database & Product
$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);

// Fetch all available products
$all_products = $product_manager->fetchAll();

// Include the header (starts HTML, includes navigation)
include ROOT_PATH . 'src/includes/header.php';
?>
<div class="main-content">


    <h1>Featured Stickers</h1>

    <?php if (empty($all_products)): ?>
        <p>No stickers are currently available. Please add some products to the database!</p>
    <?php else: ?>
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
                    <!-- Add to Cart button will be added later -->
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
<?php
include __DIR__ . '/../includes/footer.php';
?>