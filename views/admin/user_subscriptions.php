<?php
$user            = $user ?? [];
$subscriptions   = $subscriptions ?? [];
$flash_success   = $flash_success ?? null;
$flash_error     = $flash_error ?? null;
$pageTitle       = "Subscriptions for " . htmlspecialchars($user['full_name'] ?? $user['username'] ?? '', ENT_QUOTES);
$pageDescription = "Manage subscriptions on behalf of " . htmlspecialchars($user['full_name'] ?? $user['username'] ?? '', ENT_QUOTES);
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="min-h-screen">
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Flash Messages -->
    <?php if (!empty($flash_success)): ?>
    <div class="flash-message p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-3 animate-fade-in">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($flash_success, ENT_QUOTES) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
    <div class="flash-message p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3 animate-fade-in">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($flash_error, ENT_QUOTES) ?>
    </div>
    <?php endif; ?>

    <!-- Back Button & Page Header -->
    <div class="space-y-4">
        <a href="/admin/users" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Users List
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    Subscriptions: <span class="bg-gradient-brand bg-clip-text text-transparent"><?= htmlspecialchars($user['full_name'] ?? $user['username'], ENT_QUOTES) ?></span>
                </h1>
                <p class="text-gray-500 text-sm mt-0.5"><?= htmlspecialchars($user['email'], ENT_QUOTES) ?> (@<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>) · <?= count($subscriptions) ?> subscription(s)</p>
            </div>
            <div>
                <a href="/admin/users/<?= $user['id'] ?>/subscriptions/create" class="btn-primary">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Subscription
                </a>
            </div>
        </div>
    </div>

    <!-- Subscriptions Card Grid -->
    <?php if (empty($subscriptions)): ?>
    <div class="glass-card flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-2xl bg-surface-600 flex items-center justify-center mb-4 text-gray-500">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <h3 class="text-white font-semibold mb-1">No subscriptions tracked</h3>
        <p class="text-gray-500 text-xs mb-6">Create a subscription to begin monitoring recurring expenses for this user.</p>
        <a href="/admin/users/<?= $user['id'] ?>/subscriptions/create" class="btn-primary text-xs">Add First Subscription</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($subscriptions as $index => $sub):
            $statusClasses = [
                'active'    => ['badge' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30', 'dot' => 'bg-emerald-400'],
                'paused'    => ['badge' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',   'dot' => 'bg-amber-400'],
                'cancelled' => ['badge' => 'bg-red-500/15 text-red-400 border-red-500/30',         'dot' => 'bg-red-400'],
            ];
            $sc        = $statusClasses[$sub['status']] ?? $statusClasses['active'];
            $daysLeft  = (int) ceil((strtotime($sub['next_renewal_date']) - time()) / 86400);
            $isUrgent  = $daysLeft <= 3 && $sub['status'] === 'active';
        ?>
        <div class="glass-card p-5 hover:border-brand-600/30 transition-all duration-300">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div>
                        <h3 class="font-semibold text-white text-sm leading-tight"><?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?></h3>
                        <p class="text-gray-500 text-xs"><?= htmlspecialchars($sub['category'], ENT_QUOTES) ?></p>
                    </div>
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full border capitalize flex items-center gap-1.5 <?= $sc['badge'] ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $sc['dot'] ?>"></span>
                    <?= htmlspecialchars($sub['status'], ENT_QUOTES) ?>
                </span>
            </div>

            <!-- Cost -->
            <div class="mb-4 p-3 rounded-xl bg-surface-600/60 border border-white/5">
                <p class="text-2xl font-bold text-white">₱<?= number_format((float)$sub['cost'], 2) ?></p>
                <p class="text-xs text-gray-500 capitalize mt-0.5"><?= htmlspecialchars($sub['billing_cycle'], ENT_QUOTES) ?> · <?= htmlspecialchars($sub['payment_method'], ENT_QUOTES) ?></p>
            </div>

            <!-- Renewal -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Next Renewal</p>
                    <p class="text-sm font-medium text-white"><?= date('M j, Y', strtotime($sub['next_renewal_date'])) ?></p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $isUrgent ? 'bg-red-500/15 text-red-400 border border-red-500/30 animate-pulse' : 'bg-surface-600 text-gray-400' ?>">
                    <?= $daysLeft < 0 ? 'Overdue' : ($daysLeft === 0 ? 'Today' : "In {$daysLeft}d") ?>
                </span>
            </div>

            <!-- Notes -->
            <?php if (!empty($sub['notes'])): ?>
            <p class="text-xs text-gray-500 mb-4 line-clamp-2 italic"><?= htmlspecialchars($sub['notes'], ENT_QUOTES) ?></p>
            <?php endif; ?>

            <!-- Actions -->
            <div class="flex items-center gap-2 pt-3 border-t border-white/5">
                <a href="/admin/users/<?= $user['id'] ?>/subscriptions/<?= $sub['id'] ?>/edit"
                   class="flex-1 btn-secondary text-center text-xs py-2">
                    <svg class="w-3.5 h-3.5 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form method="POST" action="/admin/users/<?= $user['id'] ?>/subscriptions/<?= $sub['id'] ?>/delete"
                      class="flex-1 delete-confirm-form"
                      data-confirm="Are you sure you want to permanently delete &quot;<?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?>&quot; for this user?">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-1 px-3 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-medium border border-red-500/20 hover:border-red-500/40 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
