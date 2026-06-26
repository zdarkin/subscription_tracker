<?php
$user             = $user ?? [];
$error_profile    = $error_profile ?? null;
$success_profile  = $success_profile ?? null;
$error_password   = $error_password ?? null;
$success_password = $success_password ?? null;

$pageTitle       = 'Account Settings';
$pageDescription = 'Manage your account credentials and security settings.';
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/layout/navbar.php';
?>

<div class="min-h-screen">
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 animate-slide-up">

    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="<?= Session::isAdmin() ? '/admin' : '/dashboard' ?>"
           class="p-2 rounded-xl bg-surface-700 hover:bg-surface-600 text-gray-400 hover:text-white transition-all duration-200"
           aria-label="Go back">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Account Settings</h1>
            <p class="text-gray-400 text-sm mt-0.5">Manage your account profile and security credentials.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Profile Settings Card -->
        <div class="glass-card p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile Information
                </h2>
                <p class="text-xs text-gray-500 mb-6">Update your account username and email address.</p>

                <!-- Profile Flash Messages -->
                <?php if (!empty($error_profile)): ?>
                <div class="flash-message mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="flex-1">
                        <?php foreach (explode('|', $error_profile) as $err): ?>
                            <p><?= htmlspecialchars($err, ENT_QUOTES) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_profile)): ?>
                <div class="flash-message mb-5 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?= htmlspecialchars($success_profile, ENT_QUOTES) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="/settings/profile" novalidate>
                    <div class="space-y-4">
                        <!-- Username -->
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div class="relative">
                                <span class="input-icon">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" id="username" name="username" class="form-input pl-10"
                                       value="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>" required />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="relative">
                                <span class="input-icon">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <input type="email" id="email" name="email" class="form-input pl-10"
                                       value="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>" required />
                            </div>
                        </div>
                    </div>
            </div>
            <div class="pt-6 mt-6 border-t border-white/5">
                <button type="submit" class="btn-primary w-full">Save Profile Changes</button>
            </div>
            </form>
        </div>

        <!-- Password Settings Card -->
        <div class="glass-card p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Security & Password
                </h2>
                <p class="text-xs text-gray-500 mb-6">Change your password. Must enter current password first.</p>

                <!-- Password Flash Messages -->
                <?php if (!empty($error_password)): ?>
                <div class="flash-message mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="flex-1">
                        <?php foreach (explode('|', $error_password) as $err): ?>
                            <p><?= htmlspecialchars($err, ENT_QUOTES) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_password)): ?>
                <div class="flash-message mb-5 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?= htmlspecialchars($success_password, ENT_QUOTES) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="/settings/password" novalidate>
                    <div class="space-y-4">
                        <!-- Current Password -->
                        <div class="form-group">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="relative">
                                <span class="input-icon">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" id="current_password" name="current_password" class="form-input pl-10"
                                       placeholder="••••••••" required />
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <div class="relative">
                                <span class="input-icon">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" id="password" name="new_password" class="form-input pl-10"
                                       placeholder="••••••••" required />
                            </div>
                            <!-- Strength bar -->
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
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" id="confirm" name="confirm_password" class="form-input pl-10"
                                       placeholder="••••••••" required />
                            </div>
                            <p id="matchText" class="text-xxs hidden mt-1"></p>
                        </div>
                    </div>
            </div>
            <div class="pt-6 mt-6 border-t border-white/5">
                <button type="submit" class="btn-primary w-full">Update Password</button>
            </div>
            </form>
        </div>

    </div>

</main>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
