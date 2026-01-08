<?php
require_once './admin_init.php'; 

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . SITE_URL . 'admin/login.php');
    exit();
}