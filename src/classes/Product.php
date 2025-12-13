<?php
// sticker-shop/src/classes/Product.php

require_once __DIR__.'/../config.php';
require_once ROOT_PATH.'src/classes/Database.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Fetches all products with category names.
     * @return array An array of all products.
     */
    public function fetchAll() {
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
    public function findById($id) {
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
}
?>