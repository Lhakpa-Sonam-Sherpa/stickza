<?php
require_once 'admin_init.php';

session_unset();
session_destroy();

header('Location: ' . SITE_URL . 'admin/login.php?message=logged_out');
exit();