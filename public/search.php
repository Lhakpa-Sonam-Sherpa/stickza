<?php
require_once __DIR__ . '/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Product.php';

$keyword = trim($_GET['q'] ?? '');
$page_title = 'Search Results';

$results = [];
if (!empty($keyword)) {
    $database = new Database();
    $db = $database->connect();
    $product_manager = new Product($db);
    $results = $product_manager->search($keyword);
    $page_title = 'Search for "' . htmlspecialchars($keyword) . '"';
}

include ROOT_PATH . 'src/includes/header.php';
?>

<div class="section-header">
    <h1>Search Results</h1>
    <form action="<?php echo SITE_URL; ?>public/search.php" method="GET" style="max-width: 500px; margin: 1.5rem auto 0;">
        <div style="display: flex; gap: 0.5rem;">
            <input type="search" name="q" class="form-control" placeholder="Search for stickers..." value="<?php echo htmlspecialchars($keyword); ?>" style="flex-grow: 1;">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>
</div>

<?php if (!empty($keyword)): ?>
    <?php if (empty($results)): ?>
        <div class="empty-cart" style="text-align: center; padding: 3rem 0;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64" style="margin-bottom: 1rem; color: var(--text-muted);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3>No results found for "<?php echo htmlspecialchars($keyword); ?>"</h3>
            <p>Try searching for something else or browse all our products.</p>
            <a href="<?php echo SITE_URL; ?>public/" class="btn btn-secondary" style="margin-top: 1rem;">Browse Products</a>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2.5rem;">
            Found <?php echo count($results); ?> result(s) for "<?php echo htmlspecialchars($keyword); ?>".
        </p>
        <div class="product-grid">
            <?php foreach ($results as $prod): ?>
                <article class="product-card fade-in">
                    <a href="<?php echo SITE_URL; ?>public/product.php?id=<?php echo $prod['id']; ?>">
                        <div class="product-card-image">
                            <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($prod['image_url'] ?? 'placeholder.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                 loading="lazy">
                        </div>
                        <div class="product-card-content">
                            <div class="product-card-category"><?php echo htmlspecialchars($prod['category_name'] ?? 'Sticker'); ?></div>
                            <h3 class="product-card-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <div class="product-card-price">
                                <?php if (!is_null($prod['discount_price']) || !is_null($prod['discount_percent'])): ?>
                                    <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.9em;">
                                        Rs <?php echo number_format($prod['price'], 2); ?>
                                    </span>
                                    <span style="color: var(--danger); font-weight: bold; margin-left: 0.5rem;">
                                        Rs <?php
                                            $final_price = $prod['price'];
                                            if (!is_null($prod['discount_price'])) {
                                                $final_price = $prod['discount_price'];
                                            } elseif (!is_null($prod['discount_percent'])) {
                                                $final_price = $prod['price'] * (1 - ($prod['discount_percent'] / 100));
                                            }
                                            echo number_format($final_price, 2);
                                        ?>
                                    </span>
                                <?php else: ?>
                                    Rs <?php echo number_format($prod['price'], 2); ?>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-footer">
                                <span class="btn btn-primary btn-full">View Details</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include ROOT_PATH . 'src/includes/footer.php'; ?>
