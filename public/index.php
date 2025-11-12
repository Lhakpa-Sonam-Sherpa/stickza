<?php
// sticker-shop/public/index.php

// Include necessary files
require_once __DIR__.'/../src/config.php';
require_once ROOT_PATH.'/src/classes/Database.php';
require_once ROOT_PATH.'/src/classes/Product.php';

// Instantiate Database & Product
$database = new Database();
$db = $database->connect();
$product_manager = new Product($db);

// Include the homepage content
include ROOT_PATH.'src/pages/home.php';
?>
