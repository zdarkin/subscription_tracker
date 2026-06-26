<?php
$pageTitle = '404 – Page Not Found';
require_once __DIR__ . '/layout/header.php';
?>
<div class="min-h-screen flex items-center justify-center px-4 text-center">
    <div class="animate-slide-up">
        <p class="text-8xl font-black bg-gradient-to-r from-brand-400 to-purple-500 bg-clip-text text-transparent">404</p>
        <h1 class="text-2xl font-bold text-white mt-4 mb-2">Page Not Found</h1>
        <p class="text-gray-400 text-sm mb-8">The page you're looking for doesn't exist or has been moved.</p>
        <a href="/dashboard" class="btn-primary">← Back to Dashboard</a>
    </div>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
