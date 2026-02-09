<?php
// sticker-shop/src/classes/User.php

class User
{
    private $conn;
    private $table = 'customers';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Registers a new user.
     * @param array $data User data including first_name, last_name, email, password, address, city, phone_no.
     * @return bool True on success, false on failure.
     */
    public function register($data)
    {
        // Check if email already exists
        if ($this->findByEmail($data['email'])) {
            return false; // Email already in use
        }

        $query = 'INSERT INTO ' . $this->table . ' 
                  (first_name, last_name, email, password, address, city, phone_no) 
                  VALUES (:first_name, :last_name, :email, :password, :address, :city, :phone_no)';

        $stmt = $this->conn->prepare($query);

        // Hash the password securely
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Bind data
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':phone_no', $data['phone_no']);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log error for debugging, but return false to the user
            // echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Logs in a user.
     * @param string $email User email.
     * @param string $password Plain text password.
     * @return int|false User ID on success, false on failure.
     */
    public function login($email, $password)
    {
        // Debug: log attempt
        error_log("Login attempt for: $email");
        
        $user = $this->findByEmail($email);
        
        if (!$user) {
            error_log("User not found: $email");
            return false;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            error_log("Password verified for user ID: " . $user['id']);
            return $user['id']; // Make sure this returns the ID!
        }
        
        error_log("Password verification failed for: $email");
        return false;
    }

    /**
     * Finds a user by email.
     * @param string $email User email.
     * @return array|false User data or false if not found.
     */
    public function findByEmail($email)
    {
        $query = 'SELECT id, password FROM ' . $this->table . '
        WHERE email = :email
        LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Finds a user by ID.
     * @param int $id User ID.
     * @return array|false User data or false if not found.
     */
    public function findById($id)
    {
        $query = 'SELECT id, first_name, last_name, email, address, city, phone_no FROM ' . $this->table . ' WHERE id = :id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }


    public function getUserCount()
    {
        $sql = "SELECT COUNT(*) as count FROM customers";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
