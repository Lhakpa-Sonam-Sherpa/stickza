<?php
require_once __DIR__.'/../src/config.php';
require_once ROOT_PATH . 'src/classes/Database.php';
require_once ROOT_PATH . 'src/classes/Admin.php';

// Start admin session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if not logged in then go to login
if(!isset($_SESSION['admin_id'])){
    header('Location: '.SITE_URL.'admin/login.php');
    exit();
}

// Validate admin session
if (isset($_SESSION['admin_id'])) {
    $db = (new Database())->connect();
    $admin = new Admin($db);
    $admin_data = $admin->findAdminById($_SESSION['admin_id']);

    if (!$admin_data || empty($admin_data['is_admin']) || $admin_data['is_admin'] != 1) {
        session_unset();
        session_destroy();
        header('Location: ' . SITE_URL . 'admin/login.php?error=invalid_session');
        exit();
    }
}
