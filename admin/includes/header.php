<?php
require_once __DIR__.'/../../src/config.php';

$theme = getCurrentTheme();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>Admin — Stickza</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>admin/css/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-container">
            <a href="<?php echo SITE_URL; ?>admin/index.php" class="admin-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Stickza</span>
            </a>
            
            <nav class="admin-nav">
                <a href="<?php echo SITE_URL; ?>admin/index.php" 
                   class="<?php echo ($current_page === 'index' && $current_dir === 'admin') ? 'active' : ''; ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="<?php echo SITE_URL; ?>admin/products/index.php" 
                   class="<?php echo $current_dir === 'products' ? 'active' : ''; ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Products
                </a>
                <a href="<?php echo SITE_URL; ?>admin/orders/index.php" 
                   class="<?php echo $current_dir === 'orders' ? 'active' : ''; ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Orders
                </a>
                <a href="<?php echo SITE_URL; ?>admin/users/index.php" 
                   class="<?php echo $current_dir === 'users' ? 'active' : ''; ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users
                </a>
                <a href="<?php echo SITE_URL; ?>admin/feedback/index.php" 
                   class="<?php echo $current_dir === 'feedback' ? 'active' : ''; ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Feedback
                </a>
            </nav>
            
            <div class="admin-actions">
                <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme" 
                        style="position:static; box-shadow:none; border:1px solid var(--border); background:var(--bg-tertiary); width:36px; height:36px;">
                    <svg class="sun-icon" style="display: <?php echo $theme === 'dark' ? 'none' : 'block'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg class="moon-icon" style="display: <?php echo $theme === 'dark' ? 'block' : 'none'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                
                <a href="<?php echo SITE_URL; ?>public/" class="logout-btn" target="_blank" style="font-size:0.75rem;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Store
                </a>
                
                <a href="<?php echo SITE_URL; ?>admin/logout.php" class="logout-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </header>
    
    <main class="admin-main">
    
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            document.querySelectorAll('.sun-icon').forEach(el => el.style.display = newTheme === 'dark' ? 'none' : 'block');
            document.querySelectorAll('.moon-icon').forEach(el => el.style.display = newTheme === 'dark' ? 'block' : 'none');
            fetch('<?php echo SITE_URL; ?>public/theme/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + newTheme
            }).catch(() => localStorage.setItem('theme', newTheme));
        }
    </script>
