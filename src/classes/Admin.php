<?php
class Admin
{
    private $conn;
    private $table = 'customers';

    public function __construct($db)
    {
        $this->conn = $db;  
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public function login($email, $password)
    {
        $sql = 'SELECT id, password FROM '.$this->table.'
        WHERE email = :email AND is_admin = 1
        LIMIT 1';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            echo $admin['id'];
            return $admin['id'];
        }
        return false;
    }

    /**
     * Finds a user by ID.
     * @param int $id User ID.
     * @return array|false User data or false if not found.
     */
    public function findById($id)
    {
        $query = 'SELECT id, first_name, last_name, email, address, city, phone_no, is_admin
        FROM '. $this->table . '
        WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get dashboard statistics
     public function getDashboardStats()
    {
        $stats = [
            'total_products' => 0,
            'total_orders' => 0,
            'total_users' => 0,
            'total_revenue' => 0.00
        ];

        try {
            // Get total products
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM products");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_products'] = (int)($result['total'] ?? 0);

            // Get total orders
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM orders");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_orders'] = (int)($result['total'] ?? 0);

            // Get total users (non-admins)
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM customers WHERE is_admin = 0");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_users'] = (int)($result['total'] ?? 0);

            // Get total revenue
            $stmt = $this->conn->query("SELECT COALESCE(SUM(total_amount), 0) as revenue 
                                        FROM orders 
                                        WHERE status = 'delivered'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_revenue'] = (float)($result['revenue'] ?? 0.00);

        } catch (PDOException $e) {
            error_log("Dashboard error: " . $e->getMessage());
        }

        return $stats;
    }
}
