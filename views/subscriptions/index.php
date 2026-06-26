<?php
$subscriptions   = $subscriptions ?? [];
$flash_success   = $flash_success ?? null;
$flash_error     = $flash_error ?? null;
$statusClasses   = [
    'active'    => ['badge' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30', 'dot' => 'bg-emerald-400'],
    'paused'    => ['badge' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',   'dot' => 'bg-amber-400'],
    'cancelled' => ['badge' => 'bg-red-500/15 text-red-400 border-red-500/30',         'dot' => 'bg-red-400'],
];
$pageTitle       = 'My Subscriptions';
$pageDescription = 'Manage all your recurring subscription services.';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="min-h-screen">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Flash Messages -->
        <?php if (!empty($flash_success)): ?>
            <div class="flash-message p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?= htmlspecialchars($flash_success, ENT_QUOTES) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="flash-message p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?= htmlspecialchars($flash_error, ENT_QUOTES) ?>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="/dashboard"
                   class="p-2 rounded-xl bg-surface-700 hover:bg-surface-600 text-gray-400 hover:text-white transition-all duration-200"
                   aria-label="Go back">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">My Subscriptions</h1>
                    <p class="text-gray-400 text-sm mt-0.5"><?= count($subscriptions) ?> subscription<?= count($subscriptions) !== 1 ? 's' : '' ?> tracked</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="searchInput" placeholder="Search subscriptions..."
                        class="form-input pl-9 py-2 text-sm w-48 focus:w-64 transition-all duration-300" />
                </div>
                <?php if (!empty($subscriptions)): ?>
                    <!-- View Switcher -->
                    <div class="flex items-center bg-surface-700/80 p-0.5 rounded-lg border border-white/5">
                        <button id="btnGridView" class="p-1.5 rounded-md text-brand-400 bg-brand-500/10 hover:text-white transition-all duration-200" title="Grid View">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button id="btnCompactView" class="p-1.5 rounded-md text-gray-400 hover:text-white transition-all duration-200" title="Compact List View">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
                <a href="/subscriptions/create" class="btn-primary whitespace-nowrap">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New
                </a>
            </div>
        </div>

        <!-- Subscriptions Grid -->
        <?php if (empty($subscriptions)): ?>
            <div class="glass-card flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-2xl bg-surface-600 flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">No subscriptions yet</h3>
                <p class="text-gray-500 text-sm mb-6">Start tracking your recurring services to stay on top of your spending.</p>
                <a href="/subscriptions/create" class="btn-primary">Add Your First Subscription</a>
            </div>
        <?php else: ?>
            <div id="subscriptionsGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <?php foreach ($subscriptions as $index => $sub):
                    $sc        = $statusClasses[$sub['status']] ?? $statusClasses['active'];
                    $daysLeft  = (int) ceil((strtotime($sub['next_renewal_date']) - time()) / 86400);
                    $isUrgent  = $daysLeft <= 3 && $sub['status'] === 'active';
                ?>
                    <div class="sub-card glass-card p-5 hover:border-brand-600/40 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-glow animate-slide-up"
                        style="animation-delay: <?= $index * 0.04 ?>s"
                        data-name="<?= htmlspecialchars(strtolower($sub['service_name']), ENT_QUOTES) ?>">

                        <!-- Card Header -->
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
                            <a href="/subscriptions/show/<?= $sub['id'] ?>"
                                class="flex-1 btn-secondary text-center text-xs py-2 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/20 hover:border-brand-500/40 transition-all duration-200">
                                <svg class="w-3.5 h-3.5 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
                            </a>
                            <a href="/subscriptions/edit/<?= $sub['id'] ?>"
                                class="flex-1 btn-secondary text-center text-xs py-2">
                                <svg class="w-3.5 h-3.5 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                            <form method="POST" action="/subscriptions/delete/<?= $sub['id'] ?>"
                                class="flex-1 delete-confirm-form"
                                data-confirm="Are you sure you want to permanently delete &quot;<?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?>&quot;?">
                                <button type="submit"
                                    class="w-full flex items-center justify-center gap-1 px-3 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-medium border border-red-500/20 hover:border-red-500/40 transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Compact View Table -->
            <div id="subscriptionsCompactTable" class="hidden glass-card overflow-hidden">
                <div class="overflow-x-hidden">
                    <!-- Desktop Table View -->
                    <table class="w-full text-sm text-left border-collapse hidden md:table">
                        <thead>
                            <tr class="border-b border-white/5 text-xs text-gray-500 uppercase tracking-wider font-semibold">
                                <th class="px-6 py-3.5">Service</th>
                                <th class="px-6 py-3.5">Category</th>
                                <th class="px-6 py-3.5">Cost</th>
                                <th class="px-6 py-3.5">Cycle</th>
                                <th class="px-6 py-3.5">Renewal Date</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-gray-300">
                            <?php foreach ($subscriptions as $sub):
                                $sc = $statusClasses[$sub['status']] ?? $statusClasses['active'];
                            ?>
                                <tr class="sub-card hover:bg-white/2 transition-colors cursor-pointer" onclick="window.location.href='/subscriptions/show/<?= $sub['id'] ?>'" data-name="<?= htmlspecialchars(strtolower($sub['service_name']), ENT_QUOTES) ?>">
                                    <td class="px-6 py-4 font-medium text-white"><?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?></td>
                                    <td class="px-6 py-4 text-gray-400"><?= htmlspecialchars($sub['category'], ENT_QUOTES) ?></td>
                                    <td class="px-6 py-4 text-white font-semibold">₱<?= number_format((float)$sub['cost'], 2) ?></td>
                                    <td class="px-6 py-4 text-gray-400 capitalize"><?= htmlspecialchars($sub['billing_cycle'], ENT_QUOTES) ?></td>
                                    <td class="px-6 py-4 text-gray-400"><?= date('M j, Y', strtotime($sub['next_renewal_date'])) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full border capitalize <?= $sc['badge'] ?>">
                                            <?= htmlspecialchars($sub['status'], ENT_QUOTES) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right" onclick="event.stopPropagation();">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="/subscriptions/show/<?= $sub['id'] ?>" class="btn-secondary py-1 px-2.5 text-xs flex items-center gap-1 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/20 hover:border-brand-500/40 transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </a>
                                            <a href="/subscriptions/edit/<?= $sub['id'] ?>" class="btn-secondary py-1 px-2.5 text-xs flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                            <form method="POST" action="/subscriptions/delete/<?= $sub['id'] ?>" class="inline-block delete-confirm-form" data-confirm="Are you sure you want to permanently delete &quot;<?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?>&quot;?">
                                                <button type="submit" class="flex items-center justify-center p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 hover:border-red-500/40 transition-all duration-200">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Mobile Stacked Card View (Hidden on Desktop) -->
                    <div class="block md:hidden divide-y divide-white/5 text-gray-300">
                        <?php foreach ($subscriptions as $sub):
                            $sc = $statusClasses[$sub['status']] ?? $statusClasses['active'];
                        ?>
                            <div class="sub-card p-4 space-y-3 hover:bg-white/2 transition-colors cursor-pointer" onclick="window.location.href='/subscriptions/show/<?= $sub['id'] ?>'" data-name="<?= htmlspecialchars(strtolower($sub['service_name']), ENT_QUOTES) ?>">
                                <!-- Service & Status -->
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white text-base"><?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?></span>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full border capitalize <?= $sc['badge'] ?>">
                                        <?= htmlspecialchars($sub['status'], ENT_QUOTES) ?>
                                    </span>
                                </div>
                                <!-- Details Grid -->
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-400">
                                    <div>
                                        <span class="block text-gray-500 text-[10px] uppercase tracking-wider font-semibold">Category</span>
                                        <span><?= htmlspecialchars($sub['category'], ENT_QUOTES) ?></span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-500 text-[10px] uppercase tracking-wider font-semibold">Cost</span>
                                        <span class="text-white font-semibold">₱<?= number_format((float)$sub['cost'], 2) ?> <span class="capitalize text-gray-500 font-normal">/ <?= htmlspecialchars($sub['billing_cycle'], ENT_QUOTES) ?></span></span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-500 text-[10px] uppercase tracking-wider font-semibold">Renewal Date</span>
                                        <span><?= date('M j, Y', strtotime($sub['next_renewal_date'])) ?></span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-500 text-[10px] uppercase tracking-wider font-semibold">Payment</span>
                                        <span><?= htmlspecialchars($sub['payment_method'], ENT_QUOTES) ?></span>
                                    </div>
                                </div>
                                <!-- Actions -->
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/5" onclick="event.stopPropagation();">
                                    <a href="/subscriptions/show/<?= $sub['id'] ?>" class="btn-secondary py-1 px-3 text-xs flex items-center gap-1 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/20 hover:border-brand-500/40 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                    <a href="/subscriptions/edit/<?= $sub['id'] ?>" class="btn-secondary py-1 px-3 text-xs flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <form method="POST" action="/subscriptions/delete/<?= $sub['id'] ?>" class="inline-block delete-confirm-form" data-confirm="Are you sure you want to permanently delete &quot;<?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?>&quot;?">
                                        <button type="submit" class="flex items-center justify-center p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 hover:border-red-500/40 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnGrid = document.getElementById('btnGridView');
    const btnCompact = document.getElementById('btnCompactView');
    const gridView = document.getElementById('subscriptionsGrid');
    const compactView = document.getElementById('subscriptionsCompactTable');

    if (btnGrid && btnCompact && gridView && compactView) {
        function setView(isGrid) {
            if (isGrid) {
                gridView.classList.remove('hidden');
                compactView.classList.add('hidden');

                // Update toggle button styles
                btnGrid.classList.add('text-brand-400', 'bg-brand-500/10');
                btnGrid.classList.remove('text-gray-400');
                btnCompact.classList.add('text-gray-400');
                btnCompact.classList.remove('text-brand-400', 'bg-brand-500/10');
                
                localStorage.setItem('sub_view_preference', 'grid');
            } else {
                gridView.classList.add('hidden');
                compactView.classList.remove('hidden');

                // Update toggle button styles
                btnCompact.classList.add('text-brand-400', 'bg-brand-500/10');
                btnCompact.classList.remove('text-gray-400');
                btnGrid.classList.add('text-gray-400');
                btnGrid.classList.remove('text-brand-400', 'bg-brand-500/10');
                
                localStorage.setItem('sub_view_preference', 'compact');
            }
        }

        btnGrid.addEventListener('click', () => setView(true));
        btnCompact.addEventListener('click', () => setView(false));

        // Load preference
        const savedPreference = localStorage.getItem('sub_view_preference');
        if (savedPreference === 'compact') {
            setView(false);
        } else {
            setView(true);
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>