<?php
// sticker-shop/src/classes/Cart.php

class Cart {
    private $session_key = 'cart';

    public function __construct() {
        // Ensure session is started (already done in header.php, but good practice)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Initialize cart if it doesn't exist
        if (!isset($_SESSION[$this->session_key])) {
            $_SESSION[$this->session_key] = [];
        }
    }

    /**
     * Adds a product to the cart or updates its quantity.
     * @param int $product_id The ID of the product.
     * @param int $quantity The quantity to add.
     */
    public function add($product_id, $quantity = 1) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) return;

        if (isset($_SESSION[$this->session_key][$product_id])) {
            $_SESSION[$this->session_key][$product_id] += $quantity;
        } else {
            $_SESSION[$this->session_key][$product_id] = $quantity;
        }
    }

    /**
     * Updates the quantity of a product in the cart.
     * @param int $product_id The ID of the product.
     * @param int $quantity The new quantity.
     */
    public function update($product_id, $quantity) {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            $this->remove($product_id);
        } else {
            $_SESSION[$this->session_key][$product_id] = $quantity;
        }
    }

    /**
     * Removes a product from the cart.
     * @param int $product_id The ID of the product.
     */
    public function remove($product_id) {
        $product_id = (int)$product_id;
        if (isset($_SESSION[$this->session_key][$product_id])) {
            unset($_SESSION[$this->session_key][$product_id]);
        }
    }

    /**
     * Gets the contents of the cart.
     * @return array The cart contents (product_id => quantity).
     */
    public function getContents() {
        return $_SESSION[$this->session_key];
    }

    /**
     * Clears the cart.
     */
    public function clear() {
        $_SESSION[$this->session_key] = [];
    }

    /**
     * Checks if the cart is empty.
     * @return bool True if empty, false otherwise.
     */
    public function isEmpty() {
        return empty($_SESSION[$this->session_key]);
    }
}
?>
