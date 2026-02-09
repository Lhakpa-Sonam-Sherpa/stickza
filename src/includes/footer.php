        </div>
    </main>
    
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Stickza. Crafted with 💜 for sticker lovers.</p>
        </div>
    </footer>
    
    <!-- Theme Toggle -->
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle dark mode">
        <svg class="sun-icon" style="display: <?php echo $theme === 'dark' ? 'none' : 'block'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg class="moon-icon" style="display: <?php echo $theme === 'dark' ? 'block' : 'none'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>
    
    <script>
        // Unified theme toggle function
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            
            // Update icons
            document.querySelectorAll('.sun-icon').forEach(el => {
                el.style.display = newTheme === 'dark' ? 'none' : 'block';
            });
            document.querySelectorAll('.moon-icon').forEach(el => {
                el.style.display = newTheme === 'dark' ? 'block' : 'none';
            });
            
            // Save to server (single endpoint)
            fetch('<?php echo SITE_URL; ?>public/theme/theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + newTheme
            }).catch(() => {
                // Fallback: save to localStorage if server fails
                localStorage.setItem('theme', newTheme);
            });
        }
        
        // Check for theme on page load (backup from localStorage if cookie not set)
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme && !document.cookie.includes('theme=')) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
</body>
</html>