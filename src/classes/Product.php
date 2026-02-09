<?php
// sticker-shop/src/classes/Product.php

require_once __DIR__ . '/../config.php';
require_once ROOT_PATH . 'src/classes/Database.php';

class Product
{
    private $conn;
    private $table = 'products';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Fetches all products with category names
     * @return array An array of all products with their product category type
     */
    public function fetchAll()
    {
        $query = 'SELECT p.*, c.name as category_name 
                  FROM ' . $this->table . ' p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.stock_quantity >= 0
                  ORDER BY p.id DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Finds a product by ID.
     * @param int $id Product ID.
     * @return array|false Product data or false if not found.
     */
    public function findById($id)
    {
        $query = 'SELECT p.*, c.name as category_name 
                  FROM ' . $this->table . ' p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id
                  LIMIT 0,1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Counts total products
     * @return int Total products
     */
    public function getProductCount()
    {
        $sql = "SELECT COUNT(*) as count FROM products";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch;
        return $row['count'];
    }

    /**
     * Fetches all products
     * @return array An array of all products
     */
    public function getAllProducts()
    {
        $sql = "SELECT * FROM products ORDER BY id DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProduct($name, $description, $price, $stock, $image)
    {
        $sql = 'INSERT INTO products (name, description, price, stock_quantity, image_url) VALUES (:name, :description, :price, :stock, :image)';

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':image' => $image
        ]);
    }
}
