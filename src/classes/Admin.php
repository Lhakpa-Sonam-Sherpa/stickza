<?php
/**
 * Admin – handles all admin-side business logic.
 *
 * New in this version
 * ───────────────────
 * • login()                   – rate-limited (5 attempts / 15 min per IP+email)
 * • cancelOrder()             – transactional, restores stock for every line item ← HIGH PRIORITY FIX
 * • exportOrdersCSV()         – export filtered orders as downloadable CSV
 * • updateFeedbackStatus()    – mark feedback new / read / replied
 * • deleteFeedback()          – single delete
 * • deleteFeedbackBulk()      – bulk delete by array of IDs
 * • createPasswordResetToken() / findPasswordResetToken() / consumePasswordResetToken()
 */
class Admin
{
    private $conn;
    private string $table = 'customers';

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS    = 900; // 15 minutes

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // =========================================================
    //  AUTHENTICATION  (rate-limited)
    // =========================================================

    /**
     * Attempt admin login.
     *
     * Rate-limit is tracked in $_SESSION keyed on IP+email hash so
     * no extra DB table is needed.
     *
     * @return int|string|false
     *   – (int) admin ID on success
     *   – 'locked:N' when locked (N = minutes remaining, rounded up)
     *   – false on bad credentials
     */
    public function login(string $email, string $password)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $key = 'admin_login_' . md5(($_SERVER['REMOTE_ADDR'] ?? 'cli') . $email);

        // ── Check / enforce lockout ───────────────────────
        if (isset($_SESSION[$key])) {
            $att     = $_SESSION[$key];
            $elapsed = time() - $att['first'];

            if ($att['count'] >= self::MAX_LOGIN_ATTEMPTS && $elapsed < self::LOCKOUT_SECONDS) {
                $remaining = self::LOCKOUT_SECONDS - $elapsed;
                return 'locked:' . (int)ceil($remaining / 60);
            }

            // Window expired – reset counter
            if ($elapsed >= self::LOCKOUT_SECONDS) {
                unset($_SESSION[$key]);
            }
        }

        // ── Fetch admin record ────────────────────────────
        $sql  = "SELECT id, password FROM {$this->table}
                 WHERE email = :email AND is_admin = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            unset($_SESSION[$key]);           // clear failed-attempt counter
            session_regenerate_id(true);      // prevent session fixation
            return (int)$admin['id'];
        }

        // ── Record failure ────────────────────────────────
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first' => time()];
        }
        $_SESSION[$key]['count']++;

        return false;
    }

    public function findAdminById(int $id): array|false
    {
        $sql  = "SELECT id, first_name, last_name, email, address, city, phone_no, is_admin
                 FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // =========================================================
    //  DASHBOARD
    // =========================================================

    public function getDashboardStats(): array
    {
        $stats = [
            'total_products'  => 0,
            'total_orders'    => 0,
            'total_users'     => 0,
            'total_revenue'   => 0.00,
            'orders_today'    => 0,
            'revenue_today'   => 0.00,
            'new_users_today' => 0,
            'orders_24h'      => 0,
        ];

        try {
            $rows = [
                'total_products' => "SELECT COUNT(*) as v FROM products",
                'total_orders'   => "SELECT COUNT(*) as v FROM orders",
                'total_users'    => "SELECT COUNT(*) as v FROM customers WHERE is_admin = 0",
                'total_revenue'  => "SELECT COALESCE(SUM(total_amount),0) as v FROM orders WHERE order_status != 'cancelled'",
                'orders_today'   => "SELECT COUNT(*) as v FROM orders WHERE DATE(order_date) = CURDATE()",
                'revenue_today'  => "SELECT COALESCE(SUM(total_amount),0) as v FROM orders WHERE DATE(order_date) = CURDATE() AND order_status != 'cancelled'",
                'new_users_today'=> "SELECT COUNT(*) as v FROM customers WHERE DATE(created_at) = CURDATE() AND is_admin = 0",
                'orders_24h'     => "SELECT COUNT(*) as v FROM orders WHERE order_date >= NOW() - INTERVAL 24 HOUR",
            ];

            foreach ($rows as $key => $sql) {
                $r = $this->conn->query($sql)->fetch();
                $stats[$key] = in_array($key, ['total_revenue', 'revenue_today'])
                    ? (float)$r['v']
                    : (int)$r['v'];
            }
        } catch (PDOException $e) {
            error_log("getDashboardStats: " . $e->getMessage());
        }

        return $stats;
    }

    public function getOrderStatusBreakdown(): array
    {
        $statuses  = ['pending', 'paid', 'processing', 'shipped', 'cancelled'];
        $breakdown = array_fill_keys($statuses, 0);

        try {
            $stmt = $this->conn->query(
                "SELECT order_status, COUNT(*) as total FROM orders GROUP BY order_status"
            );
            while ($row = $stmt->fetch()) {
                if (array_key_exists($row['order_status'], $breakdown)) {
                    $breakdown[$row['order_status']] = (int)$row['total'];
                }
            }
        } catch (PDOException $e) {
            error_log("getOrderStatusBreakdown: " . $e->getMessage());
        }

        return $breakdown;
    }

    public function getSalesAnalytics(): array
    {
        $data = [];

        try {
            $stmt = $this->conn->query(
                "SELECT DATE(order_date) as day,
                        COALESCE(SUM(total_amount),0) as revenue,
                        COUNT(*) as order_count
                 FROM orders
                 WHERE order_date >= CURDATE() - INTERVAL 6 DAY
                   AND order_status != 'cancelled'
                 GROUP BY DATE(order_date)
                 ORDER BY day ASC"
            );
            $rows = $stmt->fetchAll();

            for ($i = 6; $i >= 0; $i--) {
                $date        = date('Y-m-d', strtotime("-$i days"));
                $data[$date] = ['day' => $date, 'revenue' => 0, 'order_count' => 0];
            }
            foreach ($rows as $row) {
                $data[$row['day']] = $row;
            }
        } catch (PDOException $e) {
            error_log("getSalesAnalytics: " . $e->getMessage());
        }

        return array_values($data);
    }

    public function getLowStockProducts(int $threshold = 5): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, name, stock_quantity FROM products
                 WHERE stock_quantity < :threshold ORDER BY stock_quantity ASC"
            );
            $stmt->execute([':threshold' => $threshold]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getBestSellingProducts(int $limit = 5): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT p.id, p.name, COALESCE(SUM(oi.quantity),0) as units_sold
                 FROM products p
                 LEFT JOIN order_items oi ON oi.product_id = p.id
                 LEFT JOIN orders o
                     ON o.id = oi.order_id
                     AND o.order_date >= CURDATE() - INTERVAL 30 DAY
                     AND o.order_status != 'cancelled'
                 GROUP BY p.id, p.name
                 ORDER BY units_sold DESC
                 LIMIT :lim"
            );
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // =========================================================
    //  ORDERS
    // =========================================================

    public function getOrders(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $where  = ["1=1"];
        $params = [];

        if (!empty($filters['order_id'])) {
            $where[]             = "o.id = :order_id";
            $params[':order_id'] = (int)$filters['order_id'];
        }
        if (!empty($filters['email'])) {
            $where[]           = "c.email LIKE :email";
            $params[':email']  = '%' . $filters['email'] . '%';
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[]           = "o.order_status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]              = "DATE(o.order_date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]            = "DATE(o.order_date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (isset($filters['amount_min']) && $filters['amount_min'] !== '') {
            $where[]               = "o.total_amount >= :amount_min";
            $params[':amount_min'] = (float)$filters['amount_min'];
        }
        if (isset($filters['amount_max']) && $filters['amount_max'] !== '') {
            $where[]               = "o.total_amount <= :amount_max";
            $params[':amount_max'] = (float)$filters['amount_max'];
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $countStmt = $this->conn->prepare(
            "SELECT COUNT(*) as total
             FROM orders o JOIN customers c ON o.customer_id = c.id
             WHERE $whereSQL"
        );
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $sql  = "SELECT o.*, CONCAT(c.first_name,' ',c.last_name) as customer_name,
                        c.email as customer_email
                 FROM orders o JOIN customers c ON o.customer_id = c.id
                 WHERE $whereSQL
                 ORDER BY o.order_date DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function getOrderWithCustomer(int $order_id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT o.*,
                    CONCAT(c.first_name,' ',c.last_name) as customer_name,
                    c.email as customer_email,
                    c.address, c.city, c.phone_no
             FROM orders o
             JOIN customers c ON o.customer_id = c.id
             WHERE o.id = :id"
        );
        $stmt->execute([':id' => $order_id]);
        return $stmt->fetch();
    }

    public function getOrderItems(int $order_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT oi.quantity, oi.price, p.name, p.image_url, p.id as product_id
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = :id"
        );
        $stmt->execute([':id' => $order_id]);
        return $stmt->fetchAll();
    }

    /**
     * Update order status (non-cancel).
     * For cancellation always use cancelOrder() to ensure stock is restored.
     */
    public function updateOrderStatus(int $order_id, string $status): bool
    {
        $allowed = ['pending', 'paid', 'processing', 'shipped'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->conn->prepare(
            "UPDATE orders SET order_status = :status, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $order_id]);
    }

    /**
     * ── HIGH PRIORITY FIX ──────────────────────────────────────────────────
     *
     * Cancel an order AND restore stock for every line item.
     *
     * Uses a DB transaction: if anything fails mid-way the entire
     * operation is rolled back (no partial stock updates).
     *
     * @return true|string  true on success; descriptive error string on failure.
     */
    public function cancelOrder(int $order_id): bool|string
    {
        // Guard: don't cancel an already-cancelled order
        $check = $this->conn->prepare(
            "SELECT order_status FROM orders WHERE id = :id LIMIT 1"
        );
        $check->execute([':id' => $order_id]);
        $row = $check->fetch();

        if (!$row) {
            return 'Order not found.';
        }
        if ($row['order_status'] === 'cancelled') {
            return 'Order is already cancelled.';
        }

        try {
            $this->conn->beginTransaction();

            // 1. Fetch every line item
            $itemStmt = $this->conn->prepare(
                "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id"
            );
            $itemStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $itemStmt->execute();
            $items = $itemStmt->fetchAll();

            // 2. Restore stock for each item
            $restoreStmt = $this->conn->prepare(
                "UPDATE products
                 SET stock_quantity = stock_quantity + :qty
                 WHERE id = :product_id"
            );
            foreach ($items as $item) {
                $restoreStmt->bindParam(':qty',        $item['quantity'],   PDO::PARAM_INT);
                $restoreStmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                $restoreStmt->execute();
            }

            // 3. Mark order cancelled
            $cancelStmt = $this->conn->prepare(
                "UPDATE orders
                 SET order_status = 'cancelled', updated_at = NOW()
                 WHERE id = :order_id"
            );
            $cancelStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $cancelStmt->execute();

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("cancelOrder #$order_id: " . $e->getMessage());
            return 'A database error occurred. Please try again.';
        }
    }

    /**
     * Export filtered orders as a CSV download string.
     * Usage in a controller:
     *   header('Content-Type: text/csv');
     *   header('Content-Disposition: attachment; filename="orders.csv"');
     *   echo $admin->exportOrdersCSV($filters);
     */
    public function exportOrdersCSV(array $filters = []): string
    {
        $result = $this->getOrders($filters, 1, 99999);

        ob_start();
        $fh = fopen('php://output', 'w');

        fputcsv($fh, ['Order ID', 'Customer', 'Email', 'Date', 'Total (Rs)', 'Status']);
        foreach ($result['data'] as $o) {
            fputcsv($fh, [
                '#' . str_pad((string)$o['id'], 4, '0', STR_PAD_LEFT),
                $o['customer_name'],
                $o['customer_email'],
                date('Y-m-d', strtotime($o['order_date'])),
                number_format((float)$o['total_amount'], 2, '.', ''),
                $o['order_status'],
            ]);
        }
        fclose($fh);
        return ob_get_clean();
    }

    // =========================================================
    //  PRODUCTS
    // =========================================================

    public function getProducts(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $where  = ["1=1"];
        $params = [];

        if (!empty($filters['product_id'])) {
            $where[]               = "p.id = :product_id";
            $params[':product_id'] = (int)$filters['product_id'];
        }
        if (!empty($filters['name'])) {
            $where[]         = "p.name LIKE :name";
            $params[':name'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['category'])) {
            $where[]             = "c.name LIKE :category";
            $params[':category'] = '%' . $filters['category'] . '%';
        }
        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $where[]              = "p.price >= :price_min";
            $params[':price_min'] = (float)$filters['price_min'];
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $where[]              = "p.price <= :price_max";
            $params[':price_max'] = (float)$filters['price_max'];
        }
        if (isset($filters['stock_max']) && $filters['stock_max'] !== '') {
            $where[]              = "p.stock_quantity <= :stock_max";
            $params[':stock_max'] = (int)$filters['stock_max'];
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $countStmt = $this->conn->prepare(
            "SELECT COUNT(*) as total
             FROM products p LEFT JOIN categories c ON p.category_id = c.id
             WHERE $whereSQL"
        );
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $sql  = "SELECT p.*, c.name as category_name
                 FROM products p LEFT JOIN categories c ON p.category_id = c.id
                 WHERE $whereSQL ORDER BY p.id DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    // =========================================================
    //  USERS
    // =========================================================

    public function getUsers(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $where  = ["is_admin = 0"];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[]             = "id = :user_id";
            $params[':user_id']  = (int)$filters['user_id'];
        }
        if (!empty($filters['name'])) {
            $where[]         = "(first_name LIKE :name OR last_name LIKE :name)";
            $params[':name'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['email'])) {
            $where[]          = "email LIKE :email";
            $params[':email'] = '%' . $filters['email'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[]              = "DATE(created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]            = "DATE(created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $countStmt = $this->conn->prepare(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE $whereSQL"
        );
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $sql  = "SELECT id, first_name, last_name, email, city, phone_no, created_at
                 FROM {$this->table}
                 WHERE $whereSQL ORDER BY created_at DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    // =========================================================
    //  FEEDBACK
    // =========================================================

    /**
     * Paginated feedback with optional status filter.
     *
     * @param string $statusFilter  'all' | 'new' | 'read' | 'replied'
     */
    public function getFeedback(int $page = 1, int $limit = 20, string $statusFilter = 'all'): array
    {
        $where  = ["1=1"];
        $params = [];

        if ($statusFilter !== 'all') {
            $where[]           = "status = :status";
            $params[':status'] = $statusFilter;
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $limit;

        $countStmt = $this->conn->prepare(
            "SELECT COUNT(*) as total FROM feedback WHERE $whereSQL"
        );
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $stmt = $this->conn->prepare(
            "SELECT * FROM feedback WHERE $whereSQL
             ORDER BY submitted_at DESC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Update a feedback row's status.
     * @param string $status  'new' | 'read' | 'replied'
     */
    public function updateFeedbackStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['new', 'read', 'replied'], true)) {
            return false;
        }
        $stmt = $this->conn->prepare(
            "UPDATE feedback SET status = :status WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function deleteFeedback(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM feedback WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Bulk-delete feedback rows by an array of IDs.
     * @param int[] $ids
     */
    public function deleteFeedbackBulk(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->conn->prepare(
            "DELETE FROM feedback WHERE id IN ($placeholders)"
        );
        foreach (array_values($ids) as $i => $id) {
            $stmt->bindValue($i + 1, (int)$id, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    // =========================================================
    //  PASSWORD RESET  (used by public/forgot_password.php)
    // =========================================================

    /**
     * Create a 1-hour reset token for a non-admin customer email.
     * Returns the raw 64-char hex token, or false if email not found.
     */
    public function createPasswordResetToken(string $email): string|false
    {
        $check = $this->conn->prepare(
            "SELECT id FROM customers WHERE email = :email AND is_admin = 0 LIMIT 1"
        );
        $check->execute([':email' => $email]);
        if (!$check->fetch()) {
            return false;
        }

        // Invalidate any previous tokens for this email
        $this->conn->prepare("DELETE FROM password_resets WHERE email = :email")
                   ->execute([':email' => $email]);

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->conn->prepare(
            "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)"
        )->execute([':email' => $email, ':token' => $token, ':expires' => $expires]);

        return $token;
    }

    /** Find a valid (non-expired, non-used) reset token. */
    public function findPasswordResetToken(string $token): array|false
    {
        // Purge stale tokens
        $this->conn->exec(
            "DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1"
        );

        $stmt = $this->conn->prepare(
            "SELECT * FROM password_resets
             WHERE token = :token AND expires_at > NOW() AND used = 0 LIMIT 1"
        );
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    /** Consume a token and update the customer's password. */
    public function consumePasswordResetToken(string $token, string $newPassword): bool
    {
        $row = $this->findPasswordResetToken($token);
        if (!$row) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $this->conn->prepare(
                "UPDATE customers SET password = :hash WHERE email = :email"
            )->execute([
                ':hash'  => password_hash($newPassword, PASSWORD_DEFAULT),
                ':email' => $row['email'],
            ]);

            $this->conn->prepare(
                "UPDATE password_resets SET used = 1 WHERE id = :id"
            )->execute([':id' => $row['id']]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("consumePasswordResetToken: " . $e->getMessage());
            return false;
        }
    }
}
