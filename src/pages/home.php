<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . '/src/classes/Database.php';
require_once ROOT_PATH . '/src/classes/Product.php';

$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);

// Get filter and sort parameters from URL
$category_filter = $_GET['category'] ?? 'all';
$sort_order = $_GET['sort'] ?? 'newest';

// Fetch products based on filters
$all_products = $product_manager->fetchAll($category_filter, $sort_order);
$categories = $product_manager->getCategories();

include ROOT_PATH . 'src/includes/header.php';
?>

<style>
/* Filter Modal Styles */
.filter-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: none; /* Hidden by default */
    justify-content: center;
    align-items: center;
}
.filter-modal {
    background: var(--bg-primary);
    padding: 2rem;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 450px;
    box-shadow: var(--shadow-lg);
}
.filter-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}
.filter-modal-header h2 {
    font-size: 1.25rem;
    margin: 0;
}
.filter-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}
.filter-modal-body .form-group {
    margin-bottom: 1.5rem;
}
.filter-modal-body select {
    width: 100%;
    padding: 0.75rem;
}
.filter-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 2rem;
}

/* Filter Trigger Button Bar */
.filter-trigger-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
}
.product-count {
    font-size: 0.9375rem;
    color: var(--text-secondary);
}
</style>

<!-- Hero Section -->
<section class="hero-section fade-in">
    <h1>Premium Stickers for Every Occasion</h1>
    <p>Handpicked designs, high-quality materials, and endless ways to express yourself. Discover stickers that speak to you.</p>
</section>

<!-- Filter Trigger Button -->
<div class="filter-trigger-bar">
    <div class="product-count">
        <strong><?php echo count($all_products); ?></strong> Products
    </div>
    <button id="openFilterModal" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L16 11.414V16l-4 2v-6.586L3.293 6.707A1 1 0 013 6V4z"></path></svg>
        Filter & Sort
    </button>
</div>

<!-- Filter Modal -->
<div id="filterModalOverlay" class="filter-modal-overlay">
    <div class="filter-modal">
        <div class="filter-modal-header">
            <h2>Filter & Sort</h2>
            <button id="closeFilterModal" class="filter-modal-close">&times;</button>
        </div>
        <form id="filterSortForm" method="GET" action="<?php echo SITE_URL; ?>public/index.php">
            <div class="filter-modal-body">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="all" <?php echo ($category_filter === 'all') ? 'selected' : ''; ?>>All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sort">Sort by</label>
                    <select name="sort" id="sort" class="form-control">
                        <option value="newest" <?php echo ($sort_order === 'newest') ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_asc" <?php echo ($sort_order === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo ($sort_order === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>
            <div class="filter-modal-footer">
                <a href="<?php echo SITE_URL; ?>public/index.php" class="btn btn-secondary">Clear</a>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($all_products)): ?>
    <div class="empty-cart" style="padding: 3rem 0;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64" style="margin-bottom: 1rem; color: var(--text-muted);">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3>No stickers found</h3>
        <p>Try adjusting your filters or check back soon for new arrivals!</p>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($all_products as $prod): ?>
            <article class="product-card fade-in">
                <a href="<?php echo SITE_URL; ?>public/product.php?id=<?php echo $prod['id']; ?>">
                    <div class="product-card-image">
                        <img src="<?php echo SITE_URL; ?>public/assets/images/products/<?php echo htmlspecialchars($prod['image_url'] ?? 'placeholder.png'); ?>" 
                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                             loading="lazy">
                        <?php if ($prod['stock_quantity'] < 5 && $prod['stock_quantity'] > 0): ?>
                            <span class="product-card-badge">Low Stock</span>
                        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('filterModalOverlay');
    const openBtn = document.getElementById('openFilterModal');
    const closeBtn = document.getElementById('closeFilterModal');

    openBtn.addEventListener('click', () => {
        modalOverlay.style.display = 'flex';
    });

    closeBtn.addEventListener('click', () => {
        modalOverlay.style.display = 'none';
    });

    // Also close if user clicks outside the modal content
    modalOverlay.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            modalOverlay.style.display = 'none';
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
