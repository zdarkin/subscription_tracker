<?php
$flash_error     = $flash_error ?? null;
$subscription    = $subscription ?? [];
$old             = $old ?? [];

$pageTitle       = 'Edit Subscription';
$pageDescription = 'Update your subscription details.';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="min-h-screen">
<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Flash Errors -->
    <?php if (!empty($flash_error)):
        $errors = explode('|', $flash_error);
    ?>
    <div class="flash-message mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 animate-fade-in">
        <div class="flex items-center gap-2 text-red-400 font-medium text-sm mb-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Please fix the following errors:
        </div>
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $e): ?>
            <li class="text-red-400 text-sm"><?= htmlspecialchars(trim($e), ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="/subscriptions"
           class="p-2 rounded-xl bg-surface-700 hover:bg-surface-600 text-gray-400 hover:text-white transition-all duration-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Subscription</h1>
            <p class="text-gray-400 text-sm mt-0.5">
                Updating: <span class="text-brand-300 font-medium"><?= htmlspecialchars($subscription['service_name'] ?? '', ENT_QUOTES) ?></span>
            </p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="glass-card p-6 sm:p-8 animate-slide-up">
        <?php
        $action      = "/subscriptions/edit/{$subscription['id']}";
        $submitLabel = 'Save Changes';
        require_once __DIR__ . '/_form.php';
        ?>
    </div>

</main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
