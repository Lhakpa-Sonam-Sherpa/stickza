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
     * @param array $cart_details The cart details array, which now includes final_price.
     * @param float $total_amount The total cost of the order.
     * @return int|false The new order ID on success, false on failure.
     */
    public function create($customer_id, $cart_details, $total_amount)
    {
        if (empty($cart_details)) return false;

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
            foreach ($cart_details as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $price_at_time_of_order = $item['final_price']; // Use the calculated final price

                // Re-check stock just in case
                $product_query = 'SELECT stock_quantity FROM ' . $this->products_table . ' WHERE id = :id FOR UPDATE';
                $product_stmt = $this->conn->prepare($product_query);
                $product_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                $product_stmt->execute();
                $product_stock = $product_stmt->fetchColumn();

                if ($product_stock === false || $product_stock < $quantity) {
                    // Stock check failed, roll back
                    $this->conn->rollBack();
                    return false;
                }

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
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

        /**
     * Fetches all orders for a specific customer with pagination.
     * @param int $customer_id The ID of the customer.
     * @param int $page The current page number.
     * @param int $limit The number of orders per page.
     * @return array An array containing orders and total count.
     */
    public function getCustomerOrders($customer_id, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        // Get total count of orders for the customer
        $count_query = 'SELECT COUNT(*) FROM ' . $this->orders_table . ' WHERE customer_id = :customer_id';
        $count_stmt = $this->conn->prepare($count_query);
        $count_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $count_stmt->execute();
        $total_orders = $count_stmt->fetchColumn();

        // Get paginated orders with item count
        $query = 'SELECT o.id, o.total_amount, o.order_status, o.order_date, o.updated_at,
                         (SELECT COUNT(*) FROM ' . $this->items_table . ' oi WHERE oi.order_id = o.id) as item_count
                  FROM ' . $this->orders_table . ' o
                  WHERE o.customer_id = :customer_id 
                  ORDER BY o.order_date DESC
                  LIMIT :limit OFFSET :offset';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        return ['orders' => $orders, 'total' => $total_orders];
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

    /**
     * Return the line‑items for a given order, only if the order belongs
     * to the supplied customer id.  Returns an array of rows or false.
     */
    public function getOrderDetailsForCustomer(int $order_id, int $user_id)
    {
        $sql = "
            SELECT oi.quantity,
                   oi.price,
                   p.name
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p    ON oi.product_id = p.id
            WHERE o.id = :order_id
              AND o.customer_id = :user_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id',  $user_id,  PDO::PARAM_INT);

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows ? $rows : false;
        }

        return false;
    }
}
