<?php
$page_title = "Edit Product";
require_once '../includes/header.php';
require_once '../../src/classes/Database.php';
require_once '../../src/classes/Product.php';

// Simple auth check
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$db = (new Database())->connect();
$productObj = new Product($db);

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = $productObj->findById($product_id);

if (!$product) {
    echo "<div class='alert alert-error'>Product not found.</div>";
    require_once '../includes/footer.php';
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image = $_FILES['image'] ?? null;

    if (empty($name)) $errors[] = "Product name is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($price <= 0) $errors[] = "Valid price is required.";
    if ($stock < 0) $errors[] = "Valid stock quantity is required.";

    $image_name = $product['image_url'];

    if (empty($errors) && $image && $image['name']) {
        $target_dir = "../../public/assets/images/products/";
        $image_name = time() . "_" . basename($image['name']);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($image['size'] > 2000000) {
            $errors[] = "File is too large (max 2MB).";
        } elseif (!in_array($imageFileType, ['jpg','jpeg','png','gif','webp'])) {
            $errors[] = "Invalid image format.";
        } elseif (move_uploaded_file($image['tmp_name'], $target_file)) {
            // Delete old image
            $old_image = $target_dir . $product['image_url'];
            if (file_exists($old_image) && $product['image_url'] !== 'placeholder.png') {
                unlink($old_image);
            }
        } else {
            $errors[] = "Error uploading the image.";
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE products SET 
                                name = :name,
                                description = :description,
                                price = :price,
                                stock_quantity = :stock,
                                image_url = :image
                              WHERE id = :id");
        if ($stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':image' => $image_name,
            ':id' => $product_id
        ])) {
            $success = "Product updated successfully!";
            $product = $productObj->findById($product_id);
        } else {
            $errors[] = "Failed to update product.";
        }
    }
}
?>

<div class="page-header">
    <h1>Edit Product</h1>
    <p>Update product details</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
            <div><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="form-grid">
            <div class="form-group">
                <label>Product Name <span>*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Description <span>*</span></label>
                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Price (Rs) <span>*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Stock Quantity <span>*</span></label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $product['stock_quantity']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <div class="form-hint">Leave empty to keep current image</div>
                <div class="current-image">
                    <img src="../../public/assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current">
                    <span style="color: var(--gray-500); font-size: 0.875rem; margin-left: 0.5rem;">Current image</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>