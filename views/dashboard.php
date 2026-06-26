<?php

$data            = $data ?? [];
$pageTitle       = 'Dashboard';
$pageDescription = 'Overview of your subscription spending, active plans, and upcoming renewals.';
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/layout/navbar.php';

// Build chart data JSON for JS
$chartLabels  = array_column($data['category_data'], 'category');
$chartValues  = array_map(fn($r) => round((float)$r['monthly_spend'], 2), $data['category_data']);
?>

<div class="min-h-screen">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Flash Messages -->
        <?php if (!empty($data['flash_success'])): ?>
            <div class="flash-message p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?= htmlspecialchars($data['flash_success'], ENT_QUOTES) ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="javascript:history.back()"
                   class="p-2 rounded-xl bg-surface-700 hover:bg-surface-600 text-gray-400 hover:text-white transition-all duration-200"
                   aria-label="Go back">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white">
                        Welcome back,
                        <span class="bg-gradient-to-r from-brand-300 to-purple-400 bg-clip-text text-transparent"><?= htmlspecialchars($data['username'], ENT_QUOTES) ?></span>!
                    </h1>
                    <p class="text-gray-400 mt-1 text-sm"><?= date('l, F j, Y') ?></p>
                </div>
            </div>
            <a href="/subscriptions/create" class="btn-primary self-start sm:self-auto">
                <svg class="w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Subscription
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- Monthly Spend -->
            <div class="stat-card animate-slide-up" style="animation-delay:0.05s">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">Monthly Spend</p>
                        <p class="text-3xl font-bold text-white">₱<?= number_format($data['monthly_spend'], 2) ?></p>
                        <p class="text-gray-500 text-xs mt-1.5">≈ ₱<?= number_format($data['annual_spend'], 2) ?>/yr</p>
                    </div>
                    <div class="stat-icon bg-brand-600/20 text-brand-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Subscriptions -->
            <div class="stat-card animate-slide-up" style="animation-delay:0.1s">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">Active Subscriptions</p>
                        <p class="text-3xl font-bold text-white"><?= $data['active_count'] ?></p>
                        <p class="text-gray-500 text-xs mt-1.5">Services tracked</p>
                    </div>
                    <div class="stat-icon bg-emerald-500/20 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Upcoming Renewals -->
            <div class="stat-card animate-slide-up sm:col-span-2 lg:col-span-1" style="animation-delay:0.15s">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-medium uppercase tracking-wider mb-2">Renewals This Week</p>
                        <p class="text-3xl font-bold text-white"><?= count($data['upcoming']) ?></p>
                        <p class="text-gray-500 text-xs mt-1.5">Due within 7 days</p>
                    </div>
                    <div class="stat-icon bg-amber-500/20 text-amber-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Chart + Upcoming Renewals -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            <!-- Spend by Category Donut Chart -->
            <div class="lg:col-span-3 glass-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-semibold text-white">Spend by Category</h2>
                    <span class="text-xs text-gray-500 bg-surface-600 px-2.5 py-1 rounded-full">Monthly</span>
                </div>

                <?php if (empty($data['category_data'])): ?>
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="w-12 h-12 text-gray-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">No active subscriptions yet</p>
                        <a href="/subscriptions/create" class="mt-3 text-brand-300 hover:text-brand-400 text-sm transition-colors">Add your first one →</a>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        <canvas id="categoryChart" width="200" height="200" class="flex-shrink-0"></canvas>
                        <div class="w-full space-y-2" id="chartLegend"></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Renewals -->
            <div class="lg:col-span-2 glass-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-semibold text-white">Upcoming Renewals</h2>
                    <span class="text-xs text-gray-500 bg-surface-600 px-2.5 py-1 rounded-full">7 days</span>
                </div>

                <?php if (empty($data['upcoming'])): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="w-10 h-10 text-gray-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">No renewals this week</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1 scrollbar-thin">
                        <?php foreach ($data['upcoming'] as $sub):
                            $daysUntil = (int) ceil((strtotime($sub['renewal_date']) - time()) / 86400);
                            $urgency   = $daysUntil <= 3 ? 'text-red-400 bg-red-500/10 border-red-500/20'
                                : ($daysUntil <= 5 ? 'text-amber-400 bg-amber-500/10 border-amber-500/20'
                                    : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20');
                        ?>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-600/50 border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?></p>
                                    <p class="text-xs text-gray-500"><?= date('M j, Y', strtotime($sub['renewal_date'])) ?></p>
                                </div>
                                <span class="flex-shrink-0 text-xs font-semibold px-2 py-1 rounded-full border <?= $urgency ?>">
                                    <?= $daysUntil === 0 ? 'Today' : "In {$daysUntil}d" ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Subscriptions Table -->
        <div class="glass-card overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-white/5">
                <h2 class="text-base font-semibold text-white">All Subscriptions</h2>
                <a href="/subscriptions" class="text-sm text-brand-300 hover:text-brand-400 transition-colors">View all →</a>
            </div>

            <?php if (empty($data['all_subs'])): ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <p class="text-gray-500 text-sm">No subscriptions found.</p>
                    <a href="/subscriptions/create" class="mt-3 text-brand-300 hover:text-brand-400 text-sm transition-colors">Add your first subscription →</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="text-left px-6 py-3 font-medium">Service</th>
                                <th class="text-left px-6 py-3 font-medium hidden sm:table-cell">Category</th>
                                <th class="text-left px-6 py-3 font-medium">Cost</th>
                                <th class="text-left px-6 py-3 font-medium hidden md:table-cell">Cycle</th>
                                <th class="text-left px-6 py-3 font-medium">Renewal</th>
                                <th class="text-left px-6 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach (array_slice($data['all_subs'], 0, 8) as $sub): ?>
                                <tr class="hover:bg-white/2 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="font-medium text-white"><?= htmlspecialchars($sub['service_name'], ENT_QUOTES) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-400 hidden sm:table-cell"><?= htmlspecialchars($sub['category'], ENT_QUOTES) ?></td>
                                    <td class="px-6 py-4 font-semibold text-white">₱<?= number_format((float)$sub['cost'], 2) ?></td>
                                    <td class="px-6 py-4 text-gray-400 hidden md:table-cell capitalize"><?= htmlspecialchars($sub['billing_cycle'], ENT_QUOTES) ?></td>
                                    <td class="px-6 py-4 text-gray-400"><?= date('M j, Y', strtotime($sub['renewal_date'])) ?></td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusClasses = [
                                            'active'    => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                            'paused'    => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                            'cancelled' => 'bg-red-500/15 text-red-400 border-red-500/30',
                                        ];
                                        $sc = $statusClasses[$sub['status']] ?? 'bg-gray-500/15 text-gray-400';
                                        ?>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full border capitalize <?= $sc ?>">
                                            <?= htmlspecialchars($sub['status'], ENT_QUOTES) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- Chart Data (injected for app.js) -->
<script>
    window.CHART_DATA = {
        labels: <?= json_encode($chartLabels) ?>,
        values: <?= json_encode($chartValues) ?>
    };
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>