<?php
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

$errors = [];
$success = '';
$name = $description = $price = $stock = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image = $_FILES['image'] ?? null;

    // Validation
    if (empty($name)) $errors[] = "Product name is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($price <= 0) $errors[] = "Valid price is required.";
    if ($stock < 0) $errors[] = "Valid stock quantity is required.";
    
    if (empty($errors) && $image && $image['error'] !== UPLOAD_ERR_NO_FILE) {
        $target_dir = "../../public/assets/images/products/";
        $image_name = time() . "_" . basename($image['name']);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (!getimagesize($image['tmp_name'])) {
            $errors[] = "File is not a valid image.";
        } elseif ($image['size'] > 2 * 1024 * 1024) {
            $errors[] = "File size exceeds 2MB.";
        } elseif (!in_array($imageFileType, ['jpg','jpeg','png','gif','webp'])) {
            $errors[] = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
        } elseif (move_uploaded_file($image['tmp_name'], $target_file)) {
            if ($productObj->addProduct($name, $description, $price, $stock, $image_name)) {
                $success = "Product added successfully!";
                $name = $description = $price = $stock = '';
            } else {
                $errors[] = "Failed to add product to database.";
                unlink($target_file);
            }
        } else {
            $errors[] = "Error uploading the image.";
        }
    } elseif (empty($errors)) {
        $errors[] = "Product image is required.";
    }
}

$page_title = "Add Product";
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Add New Product</h1>
    <p>Create a new sticker product</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <div>
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="form-grid">
            <div class="form-group">
                <label>Product Name <span>*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group">
                <label>Description <span>*</span></label>
                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Price (Rs) <span>*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $price; ?>" required>
                </div>

                <div class="form-group">
                    <label>Stock Quantity <span>*</span></label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $stock; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Product Image <span>*</span></label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
                <div class="form-hint">Max 2MB. JPG, PNG, GIF, or WEBP recommended.</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>