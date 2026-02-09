<?php
// admin/products/delete.php
require_once '../../src/classes/Database.php';
require_once '../../src/classes/Product.php';

$db = (new Database())->connect();
$productObj = new Product($db);

// Get product ID from query string
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    // Optional: fetch product to remove its image from server
    $product = $productObj->findById($product_id);

    if ($product) {
        // Delete product from DB
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $deleted = $stmt->execute([':id' => $product_id]);

        if ($deleted) {
            // Remove image file from server
            $image_path = "../../public/assets/images/products/" . $product['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
}

// Redirect back to product list
header("Location: index.php");
exit;
