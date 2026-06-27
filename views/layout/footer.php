<?php
/* =================================================================
   Layout: footer.php
   Closes the page body, renders the site footer, includes the
   global delete confirmation modal partial, and loads app.js.
   ================================================================= */

// Get current year once for the copyright notice
$currentYear = date('Y');

// Load Session class to determine navigation/links in footer if not already loaded
if (!class_exists('Session')) {
    require_once dirname(__DIR__, 2) . '/controllers/Session.php';
}
$isLoggedIn = Session::isLoggedIn();
$currentRole = Session::get('user_role', 'user');
?>

<!-- Site Footer -->
<footer class="relative z-10 bg-surface-800/40 border-t border-white/5 backdrop-blur-xl mt-auto py-6 text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Branding & Copyright Info -->
            <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                <a href="<?= $isLoggedIn ? ($currentRole === 'admin' ? '/admin' : '/dashboard') : '/' ?>" class="flex items-center gap-2.5 group">
                    <span class="text-base font-bold bg-gradient-to-r from-brand-300 to-purple-400 bg-clip-text text-transparent">SubsTrack</span>
                </a>
                <div class="h-4 w-px bg-white/10 hidden sm:block"></div>
                <p class="text-xs text-gray-500">&copy; <?= $currentYear ?> SubsTrack. All rights reserved.</p>
            </div>

            <!-- Links & Heart Credit -->
            <div class="flex flex-col sm:flex-row items-center gap-6 text-xs text-gray-500">
                <nav class="flex items-center gap-4">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($currentRole === 'admin'): ?>
                            <a href="/admin" class="hover:text-brand-300 transition-colors duration-200">Admin Dashboard</a>
                            <a href="/admin/users" class="hover:text-brand-300 transition-colors duration-200">Manage Users</a>
                        <?php else: ?>
                            <a href="/dashboard" class="hover:text-brand-300 transition-colors duration-200">Dashboard</a>
                            <a href="/subscriptions" class="hover:text-brand-300 transition-colors duration-200">Subscriptions</a>
                        <?php endif; ?>
                        <a href="/settings" class="hover:text-brand-300 transition-colors duration-200">Account Settings</a>
                    <?php else: ?>
                        <a href="/login" class="hover:text-brand-300 transition-colors duration-200">Sign In</a>
                        <a href="/register" class="hover:text-brand-300 transition-colors duration-200">Register</a>
                    <?php endif; ?>
                </nav>
                <div class="h-4 w-px bg-white/10 hidden sm:block"></div>
                <p class="flex items-center gap-1 text-gray-500">
                    Made with love
                    <svg class="w-3.5 h-3.5 text-brand-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                    </svg>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Delete Confirmation Modal (global partial) -->
<?php require_once __DIR__ . '/DeleteModal.php'; ?>

<!-- App JS -->
<script src="/js/app.js" defer></script>

<!-- Lazy Cron Trigger (InfinityFree Compatibility) -->
<?php
if ($isLoggedIn):
    $cronFile = dirname(__DIR__, 2) . '/cron_last_run.txt';
    $lastRun  = file_exists($cronFile) ? trim(file_get_contents($cronFile)) : '';
    $today    = date('Y-m-d');
    
    if ($lastRun !== $today):
        $workerToken = $_ENV['WORKER_TOKEN'] ?? '';
?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetch('/scripts/email_worker.php?token=<?= urlencode($workerToken) ?>')
            .then(res => res.text())
            .then(text => console.log('[Lazy Cron] Result:', text.trim()))
            .catch(err => console.error('[Lazy Cron] Failed:', err));
    });
</script>
<?php 
    endif;
endif; 
?>

</body>

</html>