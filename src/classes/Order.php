<?php
// sticker-shop/src/classes/Order.php

class Order
{
    private $conn;
    private $orders_table = 'orders';
    private $items_table = 'order_items';
    private $products_table = 'products';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Creates a new order from the cart contents.
     * @param int $customer_id The ID of the customer placing the order.
     * @param array $cart_items The cart contents (product_id => quantity).
     * @param float $total_amount The total cost of the order.
     * @return int|false The new order ID on success, false on failure.
     */
    public function create($customer_id, $cart_items, $total_amount)
    {
        if (empty($cart_items)) return false;

        try {
            // 1. Start Transaction
            $this->conn->beginTransaction();

            // 2. Insert into orders table
            $order_query = 'INSERT INTO ' . $this->orders_table . ' 
                            (customer_id, total_amount, order_status) 
                            VALUES (:customer_id, :total_amount, :order_status)';

            $order_stmt = $this->conn->prepare($order_query);
            $status = 'pending';

            $order_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
            $order_stmt->bindParam(':total_amount', $total_amount);
            $order_stmt->bindParam(':order_status', $status);
            $order_stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // 3. Insert into order_items and update product stock
            foreach ($cart_items as $product_id => $quantity) {
                // Get product price at time of order
                $product_query = 'SELECT price, stock_quantity FROM ' . $this->products_table . ' WHERE id = :id';
                $product_stmt = $this->conn->prepare($product_query);
                $product_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                $product_stmt->execute();
                $product = $product_stmt->fetch();

                if (!$product || $product['stock_quantity'] < $quantity) {
                    // Stock check failed, roll back
                    $this->conn->rollBack();
                    return false;
                }

                $price_at_time_of_order = $product['price'];

                // Insert into order_items
                $item_query = 'INSERT INTO ' . $this->items_table . ' 
                               (order_id, product_id, quantity, price) 
                               VALUES (:order_id, :product_id, :quantity, :price)';
                $item_stmt = $this->conn->prepare($item_query);
                $item_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $item_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $item_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $item_stmt->bindParam(':price', $price_at_time_of_order);
                $item_stmt->execute();

                // Update product stock
                $stock_query = 'UPDATE ' . $this->products_table . ' SET stock_quantity = stock_quantity - :quantity WHERE id = :id';
                $stock_stmt = $this->conn->prepare($stock_query);
                $stock_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stock_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                $stock_stmt->execute();
            }

            // 4. Commit Transaction
            $this->conn->commit();
            return $order_id;
        } catch (PDOException $e) {
            // Rollback on any error
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // In a real app, log the error
            // echo "Order creation failed: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Fetches all orders for a specific customer.
     * @param int $customer_id The ID of the customer.
     * @return array An array of orders.
     */
    public function getCustomerOrders($customer_id)
    {
        $query = 'SELECT id, total_amount, order_status, order_date FROM ' . $this->orders_table . ' WHERE customer_id = :customer_id ORDER BY order_date DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetches details for a specific order.
     * @param int $order_id The ID of the order.
     * @return array|false The order details or false if not found.
     */
    public function getOrderDetails($order_id)
    {
        $query = 'SELECT oi.quantity, oi.price, p.name, p.image_url 
                  FROM ' . $this->items_table . ' oi
                  JOIN ' . $this->products_table . ' p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderCount()
    {
        $sql = "SELECT COUNT(*) as count FROM orders";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getRecentOrders($limit = 5)
    {
        $sql = "SELECT o.id,
        concat(c.first_name,' ', c.last_name) as customer_name,
        o.total_amount, o.order_status,  o.order_date
                FROM orders o 
                JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.order_date DESC 
                LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
