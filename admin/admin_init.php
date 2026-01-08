<?php
require_once '../src/config.php';

// ISOLATE ADMIN SESSIONS BUT ALLOW ACCESS ACROSS /website/
session_name('ADMIN_SESSION');
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/website/', // CRITICAL: Cover entire app
    'domain' => 'localhost',
    'secure' => false, // Change to true in production
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only validate if we have an admin_id
if (isset($_SESSION['admin_id'])) {
    require_once ROOT_PATH . 'src/classes/Database.php';
    require_once ROOT_PATH . 'src/classes/Admin.php';
    
    try {
        $db = (new Database())->connect();
        $admin = new Admin($db);
        $admin_data = $admin->findById($_SESSION['admin_id']);
        
        // Destroy session ONLY if validation fails
        if (!$admin_data || !isset($admin_data['is_admin']) || $admin_data['is_admin'] != 1) {
            $old_id = $_SESSION['admin_id'];
            session_unset();
            session_destroy();
            error_log("Destroyed invalid admin session for ID: {$old_id}");
            header('Location: ' . SITE_URL . 'admin/login.php?error=invalid_session');
            exit();
        }
    } catch (Exception $e) {
        error_log("Session validation failed: " . $e->getMessage());
        session_unset();
        session_destroy();
        header('Location: ' . SITE_URL . 'admin/login.php?error=system_error');
        exit();
    }
}