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
     * Fetches all products with category names, with optional filtering and sorting.
     * @param string $category_filter The ID of the category to filter by.
     * @param string $sort_order The sorting order.
     * @return array An array of all products.
     */
    public function fetchAll($category_filter = 'all', $sort_order = 'newest')
    {
        $query = 'SELECT p.*, c.name as category_name 
                  FROM ' . $this->table . ' p
                  LEFT JOIN categories c ON p.category_id = c.id';
        
        $where = ' WHERE p.stock_quantity >= 0 ';
        if ($category_filter !== 'all' && is_numeric($category_filter)) {
            $where .= ' AND p.category_id = :category_id ';
        }

        $order = ' ORDER BY p.id DESC '; // Default: newest
        if ($sort_order === 'price_asc') {
            $order = ' ORDER BY p.price ASC ';
        } elseif ($sort_order === 'price_desc') {
            $order = ' ORDER BY p.price DESC ';
        }

        $query .= $where . $order;

        $stmt = $this->conn->prepare($query);

        if ($category_filter !== 'all' && is_numeric($category_filter)) {
            $stmt->bindParam(':category_id', $category_filter, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetches all categories.
     * @return array An array of all categories.
     */
    public function getCategories() {
        $query = 'SELECT * FROM categories ORDER BY name ASC';
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
     * Finds related products based on category.
     * @param int $category_id The category ID to search for.
     * @param int $current_product_id The ID of the product to exclude.
     * @param int $limit The maximum number of related products to return.
     * @return array An array of related products.
     */
    public function findRelated($category_id, $current_product_id, $limit = 8)
    {
        // If category_id is null or invalid, return an empty array
        if (empty($category_id)) {
            return [];
        }

        $query = 'SELECT p.*, c.name as category_name 
                  FROM ' . $this->table . ' p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id AND p.id != :current_product_id
                  ORDER BY RAND()
                  LIMIT :limit';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_product_id', $current_product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Searches for products by name, description, or category.
     * @param string $keyword The search term.
     * @return array An array of matching products.
     */
    public function search($keyword) {
        $query = 'SELECT p.*, c.name as category_name 
                  FROM ' . $this->table . ' p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.name LIKE :keyword_name 
                     OR p.description LIKE :keyword_desc 
                     OR c.name LIKE :keyword_cat
                  ORDER BY p.id DESC';
        
        $stmt = $this->conn->prepare($query);
        $search_param = '%' . $keyword . '%';
        
        // Bind the search parameter to each unique placeholder
        $stmt->bindParam(':keyword_name', $search_param);
        $stmt->bindParam(':keyword_desc', $search_param);
        $stmt->bindParam(':keyword_cat', $search_param);
        
        $stmt->execute();
        return $stmt->fetchAll();
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

    public function addProduct($name, $description, $price, $stock, $image, $discount_percent = null, $discount_price = null) {
    $query = "INSERT INTO products (name, description, price, stock_quantity, image_url, discount_percent, discount_price) 
              VALUES (:name, :description, :price, :stock, :image, :disc_perc, :disc_price)";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':stock' => $stock,
        ':image' => $image,
        ':disc_perc' => $discount_percent,
        ':disc_price' => $discount_price
        ]);
    }
}
