<?php
// sticker-shop/src/classes/User.php

class User {
    private $conn;
    private $table = 'customers';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registers a new user.
     * @param array $data User data including first_name, last_name, email, password, address, city, phone_no.
     * @return bool True on success, false on failure.
     */
    public function register($data) {
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
    public function login($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user['id']; // Login successful, return user ID
        }
        return false; // Login failed
    }

    /**
     * Finds a user by email.
     * @param string $email User email.
     * @return array|false User data or false if not found.
     */
    public function findByEmail($email) {
        $query = 'SELECT id, password, first_name FROM ' . $this->table . ' WHERE email = :email LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Finds a user by ID.
     * @param int $id User ID.
     * @return array|false User data or false if not found.
     */
    public function findById($id) {
        $query = 'SELECT id, first_name, last_name, email, address, city, phone_no FROM ' . $this->table . ' WHERE id = :id LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
