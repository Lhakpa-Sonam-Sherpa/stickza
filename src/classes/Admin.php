<?php
class Admin
{
    private $conn;
    private $table = 'customers';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Admin login
    public function login($email, $password)
    {
        $sql = 'SELECT id, password FROM '.$this->table.' WHERE email = :email AND is_admin = 1 LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin['id'];
        }
        return false;
    }

    // Find admin by ID
    public function findAdminById($id)
    {
        $sql = 'SELECT id, first_name, last_name, email, address, city, phone_no
                FROM '.$this->table.' WHERE id = :id AND is_admin = 1 LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Dashboard stats
    public function getDashboardStats()
    {
        $stats = [
            'total_products' => 0,
            'total_orders' => 0,
            'total_users' => 0,
            'total_revenue' => 0.00
        ];

        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM products");
            $stats['total_products'] = (int)($stmt->fetch()['total'] ?? 0);

            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM orders");
            $stats['total_orders'] = (int)($stmt->fetch()['total'] ?? 0);

            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM customers WHERE is_admin = 0");
            $stats['total_users'] = (int)($stmt->fetch()['total'] ?? 0);

            $stmt = $this->conn->query("SELECT COALESCE(SUM(total_amount),0) as revenue FROM orders WHERE order_status='delivered'");
            $stats['total_revenue'] = (float)($stmt->fetch()['revenue'] ?? 0.00);

        } catch (PDOException $e) {
            error_log("Dashboard error: " . $e->getMessage());
        }

        return $stats;
    }
}
