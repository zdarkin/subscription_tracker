<?php
$users           = $users ?? [];
$flash_success   = $flash_success ?? null;
$flash_error     = $flash_error ?? null;
$query           = $query ?? '';

$pageTitle       = 'User Management';
$pageDescription = 'Manage user accounts, roles, credentials, and their subscriptions.';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
$loggedInAdminId = (int) \Session::get('user_id');
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
                <a href="/admin"
                   class="p-2 rounded-xl bg-surface-700 hover:bg-surface-600 text-gray-400 hover:text-white transition-all duration-200"
                   aria-label="Go back">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">User Accounts</h1>
                    <p class="text-gray-400 text-sm mt-0.5"><?= count($users) ?> user accounts registered</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Search Form -->
                <form method="GET" action="/admin/users" class="relative flex-1 sm:flex-initial">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="q" placeholder="Search by name or email..."
                        value="<?= htmlspecialchars($query ?? '', ENT_QUOTES) ?>"
                        class="form-input pl-9 py-2 text-sm w-full sm:w-64 focus:w-80 transition-all duration-300" />
                </form>
                <a href="/admin/users/create" class="btn-primary whitespace-nowrap">
                    <svg class="w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add User
                </a>
            </div>
        </div>

        <!-- Users Table/Grid -->
        <div class="glass-card p-6">
            <?php if (empty($users)): ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-surface-600 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-white font-medium">No users found</h3>
                    <p class="text-gray-500 text-xs mt-1">Try resetting your search query.</p>
                    <a href="/admin/users" class="btn-secondary text-xs mt-4">Reset Filter</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-xs text-gray-500 uppercase tracking-wider font-semibold">
                                <th class="pb-3 pr-4 cursor-pointer select-none hover:text-white transition-colors duration-150 sort-header group" data-sort="name">
                                    <div class="flex items-center gap-1.5">
                                        <span>User</span>
                                        <svg class="w-3 h-3 text-brand-400 opacity-0 group-hover:opacity-50 transition-all duration-150 transform sort-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="pb-3 px-4 cursor-pointer select-none hover:text-white transition-colors duration-150 sort-header group" data-sort="role">
                                    <div class="flex items-center gap-1.5">
                                        <span>Role</span>
                                        <svg class="w-3 h-3 text-brand-400 opacity-0 group-hover:opacity-50 transition-all duration-150 transform sort-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="pb-3 px-4 cursor-pointer select-none hover:text-white transition-colors duration-150 sort-header group" data-sort="registrationDate">
                                    <div class="flex items-center gap-1.5">
                                        <span>Registration Date</span>
                                        <svg class="w-3 h-3 text-brand-400 opacity-0 group-hover:opacity-50 transition-all duration-150 transform sort-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="pb-3 px-4 cursor-pointer select-none hover:text-white transition-colors duration-150 sort-header group" data-sort="subscriptionsCount">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span>Subscriptions Count</span>
                                        <svg class="w-3 h-3 text-brand-400 opacity-0 group-hover:opacity-50 transition-all duration-150 transform sort-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="pb-3 pl-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-white/[0.01] transition-colors duration-150"
                                    data-name="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
                                    data-role="<?= htmlspecialchars($u['role'], ENT_QUOTES) ?>"
                                    data-date="<?= htmlspecialchars($u['created_at'], ENT_QUOTES) ?>"
                                    data-subs="<?= (int)$u['subscription_count'] ?>">
                                    <td class="py-3.5 pr-4 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-brand flex items-center justify-center text-xs font-bold text-white">
                                            <?= htmlspecialchars(strtoupper(substr($u['username'], 0, 1)), ENT_QUOTES) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-white truncate"><?= htmlspecialchars($u['username'], ENT_QUOTES) ?></p>
                                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($u['email'], ENT_QUOTES) ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border <?= $u['role'] === 'admin' ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' : 'bg-brand-500/10 border-brand-500/20 text-brand-400' ?>">
                                            <?= htmlspecialchars($u['role'], ENT_QUOTES) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-400">
                                        <?= date('M j, Y g:i A', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center text-white font-medium">
                                        <?= (int)$u['subscription_count'] ?>
                                    </td>
                                    <td class="py-3.5 pl-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="/admin/users/<?= $u['id'] ?>/subscriptions"
                                                class="btn-secondary py-1 px-3 text-xs flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View Subs
                                            </a>
                                            <a href="/admin/users/<?= $u['id'] ?>/edit"
                                                class="btn-secondary py-1 px-2 text-xs flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                            <?php if ($u['id'] !== $loggedInAdminId): ?>
                                                <form method="POST" action="/admin/users/<?= $u['id'] ?>/delete"
                                                    class="inline-block delete-confirm-form"
                                                    data-confirm="Are you sure you want to permanently delete user &quot;<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>&quot;?<br/><br/>This will also cascade delete all subscriptions and email alerts for this account!">
                                                    <button type="submit"
                                                        class="flex items-center justify-center p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 hover:border-red-500/40 transition-all duration-200">
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

<?php require_once __DIR__ . '/../layout/footer.php'; ?>