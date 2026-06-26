<?php
$stats           = $stats ?? [];
$recentUsers     = $recentUsers ?? [];

$pageTitle       = 'Admin Dashboard';
$pageDescription = 'Manage users and platform-wide metrics.';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
$loggedInAdminId = (int) \Session::get('user_id');
?>

<div class="min-h-screen">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 animate-slide-up">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">System Overview</h1>
                    <p class="text-gray-400 text-sm mt-0.5">Real-time metrics and administration controls.</p>
                </div>
            </div>
            <div>
                <a href="/admin/users" class="btn-primary">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manage Users
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1: Total Users (Interactive) -->
            <a href="/admin/users" class="glass-card p-5 hover:border-brand-500 hover:shadow-glow hover:-translate-y-0.5 transition-all duration-300 block cursor-pointer">
                <div class="flex items-center justify-between mb-5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Users</span>
                    <div class="w-8 h-8 rounded-lg bg-brand-500/10 flex items-center justify-center text-brand-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white"><?= number_format($stats['total_users']) ?></p>
            </a>

            <!-- Card 2: Digital Clock -->
            <div class="glass-card p-5 transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System Time</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex flex-col">
                    <p id="digital-clock" class="text-3xl font-bold text-white font-mono">--:--:--</p>
                    <p id="digital-date" class="text-xs text-gray-400 mt-0.5">Loading date...</p>
                </div>
            </div>

            <!-- Card 3: Trigger Email Worker -->
            <div class="glass-card p-5 transition-all duration-300 sm:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Alerts Dispatcher</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-white">Trigger Email Worker</p>
                        <p class="text-xs text-gray-400">Executes background script to dispatch pending renewal notifications.</p>
                    </div>
                    <button id="trigger-worker-btn" class="btn-primary flex items-center justify-center gap-2 whitespace-nowrap self-start sm:self-auto min-w-[140px]">
                        <span id="btn-text">Run Dispatcher</span>
                        <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
                <div id="worker-console" class="mt-4 hidden p-3 rounded bg-black/40 border border-white/5 text-xs text-gray-300 font-mono overflow-y-auto max-h-40 whitespace-pre-wrap"></div>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Recent User Registrations</h2>
            <?php if (empty($recentUsers)): ?>
                <p class="text-gray-500 text-sm py-4">No users registered in the system.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-xs text-gray-500 uppercase tracking-wider font-semibold">
                                <th class="pb-3 pr-4">User</th>
                                <th class="pb-3 px-4">Role</th>
                                <th class="pb-3 px-4">Joined Date</th>
                                <th class="pb-3 px-4 text-center">Subscriptions</th>
                                <th class="pb-3 pl-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td class="py-3.5 pr-4 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-brand flex items-center justify-center text-xs font-bold text-white">
                                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-white"><?= htmlspecialchars($u['username'], ENT_QUOTES) ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($u['email'], ENT_QUOTES) ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full border <?= $u['role'] === 'admin' ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' : 'bg-brand-500/10 border-brand-500/20 text-brand-400' ?>">
                                            <?= htmlspecialchars($u['role'], ENT_QUOTES) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-400">
                                        <?= date('M j, Y g:i A', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center text-white font-medium">
                                        <?= $u['subscription_count'] ?>
                                    </td>
                                    <td class="py-3.5 pl-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="/admin/users/<?= $u['id'] ?>/subscriptions" class="btn-secondary py-1 px-3 text-xs">View Subs</a>
                                            <a href="/admin/users/<?= $u['id'] ?>/edit" class="btn-secondary py-1 px-2 text-xs">Edit</a>
                                            <?php if ($u['id'] !== $loggedInAdminId): ?>
                                                <form method="POST" action="/admin/users/<?= $u['id'] ?>/delete"
                                                    class="inline-block delete-confirm-form"
                                                    data-confirm="Are you sure you want to permanently delete user &quot;<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>&quot;?<br/><br/>This will also cascade delete all subscriptions and email alerts for this account!">
                                                    <button type="submit"
                                                        class="flex items-center justify-center p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 hover:border-red-500/40 transition-all duration-200"
                                                        title="Delete User">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="p-1.5 text-gray-600 cursor-not-allowed border border-white/5 rounded-lg" title="Cannot delete yourself">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </span>
                                            <?php endif; ?>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Digital Clock
    const clockEl = document.getElementById('digital-clock');
    const dateEl = document.getElementById('digital-date');
    
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString([], { hour12: false });
        const dateStr = now.toLocaleDateString([], { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
        
        if (clockEl) clockEl.textContent = timeStr;
        if (dateEl) dateEl.textContent = dateStr;
    }
    
    updateClock();
    setInterval(updateClock, 1000);

    // 2. Trigger Email Worker Action Button
    const triggerBtn = document.getElementById('trigger-worker-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    const consoleEl = document.getElementById('worker-console');

    if (triggerBtn) {
        triggerBtn.addEventListener('click', async () => {
            triggerBtn.disabled = true;
            btnText.textContent = 'Executing...';
            btnSpinner.classList.remove('hidden');
            consoleEl.classList.add('hidden');
            consoleEl.textContent = '';
            consoleEl.className = "mt-4 p-3 rounded bg-black/40 border border-white/5 text-xs text-gray-300 font-mono overflow-y-auto max-h-40 whitespace-pre-wrap";

            try {
                const response = await fetch('/admin/trigger-worker', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                consoleEl.classList.remove('hidden');
                if (result.success) {
                    consoleEl.classList.add('border-emerald-500/30', 'text-emerald-400');
                    consoleEl.textContent = `[SUCCESS] ${result.message}\n\n${result.output}`;
                } else {
                    consoleEl.classList.add('border-red-500/30', 'text-red-400');
                    consoleEl.textContent = `[ERROR] ${result.message}\n\n${result.output}`;
                }
            } catch (err) {
                consoleEl.classList.remove('hidden');
                consoleEl.classList.add('border-red-500/30', 'text-red-400');
                consoleEl.textContent = `[CONNECTION ERROR] Failed to contact the backend server.\n${err.message}`;
            } finally {
                triggerBtn.disabled = false;
                btnText.textContent = 'Run Dispatcher';
                btnSpinner.classList.add('hidden');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>