<?php
$subscription = $subscription ?? [];
$pageTitle       = htmlspecialchars($subscription['service_name'], ENT_QUOTES) . ' Details';
$pageDescription = 'Granular details for subscription service.';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';

$statusClasses = [
    'active'    => ['badge' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30', 'dot' => 'bg-emerald-400'],
    'paused'    => ['badge' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',   'dot' => 'bg-amber-400'],
    'cancelled' => ['badge' => 'bg-red-500/15 text-red-400 border-red-500/30',         'dot' => 'bg-red-400'],
];
$sc = $statusClasses[$subscription['status']] ?? $statusClasses['active'];
$daysLeft  = (int) ceil((strtotime($subscription['next_renewal_date']) - time()) / 86400);
$isUrgent  = $daysLeft <= 3 && $subscription['status'] === 'active';


?>

<div class="min-h-screen">
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6 animate-slide-up">
        
        <!-- Back navigation -->
        <div class="flex items-center justify-between">
            <a href="/subscriptions" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Subscriptions
            </a>
            
            <div class="flex items-center gap-2">
                <a href="/subscriptions/edit/<?= $subscription['id'] ?>" class="btn-secondary py-1.5 px-4 text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <form method="POST" action="/subscriptions/delete/<?= $subscription['id'] ?>" class="inline-block delete-confirm-form" data-confirm="Are you sure you want to permanently delete &quot;<?= htmlspecialchars($subscription['service_name'], ENT_QUOTES) ?>&quot;?">
                    <button type="submit" class="flex items-center justify-center py-1.5 px-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 hover:border-red-500/40 transition-all duration-200 text-sm font-medium gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Details Card -->
        <div class="glass-card overflow-hidden">
            <!-- Glass Header Banner -->
            <div class="p-6 sm:p-8 bg-gradient-to-r from-brand-500/10 to-indigo-500/10 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full border capitalize inline-flex items-center gap-1.5 <?= $sc['badge'] ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $sc['dot'] ?>"></span>
                        <?= htmlspecialchars($subscription['status'], ENT_QUOTES) ?>
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight pt-1">
                        <?= htmlspecialchars($subscription['service_name'], ENT_QUOTES) ?>
                    </h2>
                    <p class="text-gray-400 text-sm"><?= htmlspecialchars($subscription['category'], ENT_QUOTES) ?></p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-3xl font-black text-white">₱<?= number_format((float)$subscription['cost'], 2) ?></p>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mt-1">Billed <?= htmlspecialchars($subscription['billing_cycle'], ENT_QUOTES) ?></p>
                </div>
            </div>

            <!-- Granular Metadata Grid -->
            <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-white/5">
                <div class="space-y-1">
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold block">Payment Method</span>
                    <p class="text-white text-base font-medium"><?= htmlspecialchars($subscription['payment_method'], ENT_QUOTES) ?></p>
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold block">Next Renewal Date</span>
                    <p class="text-white text-base font-medium flex items-center gap-2">
                        <?= date('F j, Y', strtotime($subscription['next_renewal_date'])) ?>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $isUrgent ? 'bg-red-500/15 text-red-400 border border-red-500/30' : 'bg-surface-600 text-gray-400' ?>">
                            <?= $daysLeft < 0 ? 'Overdue' : ($daysLeft === 0 ? 'Today' : "In {$daysLeft}d") ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="p-6 sm:p-8 space-y-3">
                <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold block">Notes / Description</span>
                <?php if (!empty($subscription['notes'])): ?>
                    <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-wrap italic">
                        "<?= htmlspecialchars($subscription['notes'], ENT_QUOTES) ?>"
                    </p>
                <?php else: ?>
                    <p class="text-gray-500 text-sm italic">No description provided for this subscription.</p>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php 
require_once __DIR__ . '/../layout/footer.php'; 
?>
