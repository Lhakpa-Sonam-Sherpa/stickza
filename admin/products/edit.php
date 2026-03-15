<?php
$page_title = "Edit Product";
require_once '../admin_init.php';
require_once '../../src/classes/Database.php';
require_once '../../src/classes/Product.php';
require_once '../../src/helpers/Validator.php';

$db = (new Database())->connect();
$productObj = new Product($db);

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = $productObj->findById($product_id);

if (!$product) {
    header('Location: index.php?error=not_found');
    exit;
}

$errors = [];
$success = isset($_GET['success']) ? "Product updated successfully!" : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF();

    $validator = new Validator($_POST);
    $validator->required('name', 'Product Name')
              ->required('description', 'Description')
              ->required('price', 'Price')->numeric('price')->min('price', 0.01)
              ->required('stock', 'Stock Quantity')->numeric('stock')->min('stock', 0);

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validator->image('image');
    }
    
    if (isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '') {
        $validator->numeric('discount_percent')->min('discount_percent', 0)->max('discount_percent', 100);
    }
    if (isset($_POST['discount_price']) && $_POST['discount_price'] !== '') {
        $validator->numeric('discount_price')->min('discount_price', 0);
    }

    if ($validator->fails()) {
        $errors = $validator->errors();
    }
    else {
        $image_name = $product['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../../public/assets/images/products/";
            $new_image_name = time() . "_" . basename($_FILES['image']['name']);
            $target_file = $target_dir . $new_image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $old_image = $target_dir . $product['image_url'];
                if (!empty($product['image_url']) && file_exists($old_image)) {
                    unlink($old_image);
                }
                $image_name = $new_image_name;
            } else {
                $errors[] = "Error uploading new image.";
            }
        }

        if (empty($errors)) {
            $stmt = $db->prepare("UPDATE products SET 
                                        name = :name, description = :description, price = :price, 
                                        stock_quantity = :stock, image_url = :image, 
                                        discount_percent = :discount_percent, discount_price = :discount_price
                                      WHERE id = :id");
            if ($stmt->execute([
                ':name' => trim($_POST['name']),
                ':description' => trim($_POST['description']),
                ':price' => floatval($_POST['price']),
                ':stock' => intval($_POST['stock']),
                ':image' => $image_name,
                ':discount_percent' => (isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '') ? floatval($_POST['discount_percent']) : null,
                ':discount_price' => (isset($_POST['discount_price']) && $_POST['discount_price'] !== '') ? floatval($_POST['discount_price']) : null,
                ':id' => $product_id
            ])) {
                header("Location: edit.php?id=$product_id&success=1");
                exit;
            } else {
                $errors[] = "Failed to update product.";
            }
        }
    }
}
require_once '../includes/header.php';
?>

<!-- Page Header with Back Button -->
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h1>Edit Product</h1>
        <p>Update details for "<?php echo htmlspecialchars($product['name']); ?>"</p>
    </div>
    <a href="index.php" class="btn btn-secondary btn-sm">← Back to Products</a>
</div>

<?php if (!empty($errors)): ?><div class="alert alert-error"><div><?php foreach ($errors as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?></div></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="content-card">
    <form method="POST" enctype="multipart/form-data" class="form-container">
        <?php echo csrfField(); ?>

        <!-- Basic Information Section -->
        <div class="form-section">
            <h3 class="form-section-title">Basic Information</h3>
            <div class="form-grid">
                <div class="form-group form-full">
                    <label for="name">Product Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group form-full">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" class="form-control textarea-lg" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & Inventory Section -->
        <div class="form-section">
            <h3 class="form-section-title">Pricing & Inventory</h3>
            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label for="price">Price (Rs) <span class="required">*</span></label>
                    <div class="input-prefix"><span class="prefix">Rs</span><input type="number" id="price" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required></div>
                </div>
                <div class="form-group">
                    <label for="stock">Stock Quantity <span class="required">*</span></label>
                    <input type="number" id="stock" name="stock" class="form-control" value="<?php echo $product['stock_quantity']; ?>" required>
                </div>
            </div>
        </div>

        <!-- Product Image Section -->
        <div class="form-section">
            <h3 class="form-section-title">Product Image</h3>
            <div class="form-group form-full">
                <label for="image">Update Image (Optional)</label>
                <label for="image" class="file-upload-wrapper">
                    <input type="file" id="image" name="image" class="file-input" accept="image/*" onchange="previewImage(event)">
                    <div class="file-upload-hint">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <div>
                            <p class="font-weight-500">Click to upload a new image</p>
                            <p class="text-muted">Leave empty to keep the current image</p>
                        </div>
                    </div>
                </label>
                <div class="current-image-section">
                    <div class="image-label">Current Image</div>
                    <div class="current-image"><img src="../../public/assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current"></div>
                </div>
                <div id="imagePreview" style="margin-top: 1rem; display:none;">
                    <div class="image-label">New Image Preview</div>
                    <img id="previewImg" src="" alt="Preview">
                </div>
            </div>
        </div>

        <!-- Discount Section -->
        <div class="form-section">
            <h3 class="form-section-title">Discounts (Optional)</h3>
            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label for="discount_percent">Discount Percentage (%)</label>
                    <input type="number" id="discount_percent" step="0.01" name="discount_percent" class="form-control" value="<?php echo $product['discount_percent'] ?? ''; ?>" placeholder="e.g. 10" min="0" max="100">
                </div>
                <div class="form-group">
                    <label for="discount_price">Fixed Discount Price (Rs)</label>
                    <div class="input-prefix"><span class="prefix">Rs</span><input type="number" id="discount_price" step="0.01" name="discount_price" class="form-control" value="<?php echo $product['discount_price'] ?? ''; ?>" placeholder="e.g. 50"></div>
                </div>
            </div>
            <div class="form-hint info-hint">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <span>If both are set, the fixed discount price will take priority.</span>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions-group">
            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Product
            </button>
            <a href="index.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div></div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const img = document.getElementById('previewImg');
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
