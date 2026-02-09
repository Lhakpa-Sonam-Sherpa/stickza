<?php
// public/theme/theme.php - Unified theme endpoint$_COOKIE['theme']
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'light';
    if (in_array($theme, ['light', 'dark'])) {
        $_SESSION['theme'] = $theme;
        // Set a long-lived cookie as backup
        setcookie('theme', $theme, time() + (86400 * 365), '/', '', false, true);
    }
}

// Return current theme
$currentTheme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? 'light';
header('Content-Type: application/json');
echo json_encode(['theme' => $currentTheme]);