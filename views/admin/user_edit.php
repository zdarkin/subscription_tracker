<?php
$old             = $old ?? [];
$flash_error     = $flash_error ?? null;
$pageTitle       = 'Edit User Account';
$pageDescription = 'Modify user account credentials, roles, and password details.';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md animate-slide-up">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="/admin/users" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Users List
            </a>
        </div>

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-white mb-1">Edit User Account</h1>
            <p class="text-gray-400 text-sm">Update username, email, role, or reset password</p>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flash_error)): ?>
            <div class="flash-message mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <?php foreach (explode('|', $flash_error) as $err): ?>
                        <p><?= htmlspecialchars($err, ENT_QUOTES) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Card -->
        <div class="glass-card p-8">
            <form method="POST" action="/admin/users/<?= $old['id'] ?>/edit" novalidate>
                <div class="space-y-4">

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" id="username" name="username" class="form-input pl-10"
                                value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES) ?>" required />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" class="form-input pl-10"
                                value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" required />
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group">
                        <label for="role" class="form-label">Account Role</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <select id="role" name="role" class="form-input pl-10 pr-10" required>
                                <option value="user" <?= ($old['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User (Subscription Tracker)</option>
                                <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (User Manager)</option>
                            </select>
                        </div>
                    </div>

                    <!-- New Password (Optional) -->
                    <div class="form-group pt-4 border-t border-white/5">
                        <label for="password" class="form-label">New Password <span class="text-xs text-gray-500 font-normal">(Leave blank to keep current)</span></label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-input pl-10"
                                placeholder="••••••••" />
                        </div>
                        <div class="mt-2 h-1.5 w-full bg-surface-600 rounded-full overflow-hidden">
                            <div id="strengthBar" class="h-full w-0 transition-all duration-300"></div>
                        </div>
                        <p id="strengthText" class="text-xxs text-gray-500 mt-1">Min. 8 chars · 1 uppercase · 1 number</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm" class="form-label">Confirm New Password</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="confirm" name="confirm" class="form-input pl-10"
                                placeholder="••••••••" />
                        </div>
                        <p id="matchText" class="text-xxs hidden mt-1"></p>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary w-full mt-4">Save Changes</button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>