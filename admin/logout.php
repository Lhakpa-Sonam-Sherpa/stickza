<?php
require_once 'admin_init.php'; // Starts session

// Destroy ONLY admin session
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/website/'); // Match path
}
session_destroy();

header('Location: ' . SITE_URL . 'admin/login.php?message=logged_out');
exit();