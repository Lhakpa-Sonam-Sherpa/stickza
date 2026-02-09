<?php
class AdminProduct
{
    private $conn;
    private $table = 'products';
    private $upload_dir = '../uploads/products/';

    public function __construct($db)
    {
        $this->conn = $db;
        
        // Ensure upload directory exists
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    // Add product with image upload
    public function addProduct($name, $description, $price, $stock, $image)
    {
        // Handle image upload
        $image_name = $this->uploadImage($image);

        $sql = "INSERT INTO {$this->table} (name, description, price, stock, image_url) 
                VALUES (:name, :description, :price, :stock, :image_url)";
                
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'name'=>$name,
            'description'=>$description,
            'price'=>$price,
            'stock'=>$stock,
            'image_url'=>$image_name,
        ]);
    }

    // Update product
    public function updateProduct($product_id, $name, $description, $price, $stock, $image = null)
    {
        if ($image) {
            $image_name = $this->uploadImage($image);
            $sql = "UPDATE products SET name=:name, description=:description, price=:price, stock_quantity=:stock, image_url=:image_url 
                    WHERE id=:product_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':name'=>$name,
                ':description'=>$description,
                ':price'=>$price,
                ':stock'=>$stock,
                ':image_url'=>$image_name,
                ':product_id'=>$product_id
                ]);
            return true;
        } else {
            $sql = "UPDATE products SET name=:name, description=:description, price=:price, stock_quantity=:stock 
                    WHERE id=:product_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':name'=>$name,
                ':description'=>$description,
                ':price'=>$price,
                ':stock'=>$stock,
                ':product_id'=>$product_id
            ]);
            return true;
        }
        return false;
    }

    // Delete product
    public function deleteProduct($product_id)
    {
        // First get image to delete from server
        $sql = "SELECT image_url FROM products WHERE id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['product_id' => $product_id]);
        $product = $stmt->fetch();

        // Delete image file
        if ($product['image_url']) {
            unlink("../uploads/products/" . $product['image_url']);
        }

        // Delete product from database
        $sql = "DELETE FROM products WHERE id = :product_id";
        $stmt = $this->conn->prepare($sql);
       
        return $stmt->execute(['product_id' => $product_id]);
    }

    // Get all products with pagination
    public function getAllProducts($page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Upload image helper function
    private function uploadImage($image)
    {
        $target_dir = "../uploads/products/";
        $image_name = time() . "_" . basename($image["name"]);
        $target_file = $target_dir . $image_name;

        // Check if image file is actual image
        $check = getimagesize($image["tmp_name"]);
        if ($check === false) {
            return false;
        }

        // Check file size (5MB limit)
        if ($image["size"] > 5000000) {
            return false;
        }

        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            return false;
        }

        // Upload file
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            return $image_name;
        }

        return false;
    }
}
