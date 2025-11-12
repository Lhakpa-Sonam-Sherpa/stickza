<?php
require_once __DIR__.'/../src/config.php';
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the homepage
header('Location: '.SITE_URL.'public/');
exit();
?>
