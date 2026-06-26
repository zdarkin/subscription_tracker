<?php
$error           = $error ?? null;
$old             = $old ?? [];

$pageTitle       = 'Create Account';
$pageDescription = 'Create your SubsTrack account and start tracking your subscriptions.';
require_once dirname(__DIR__) . '/layout/header.php';

// Split piped error string into array for bullet list
$errors = [];
if (!empty($error)) {
    $errors = explode('|', $error);
}
?>

<div class="min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    <!-- Decorative blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-700/20 rounded-full blur-3xl animate-pulse-glow"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-brand-600/15 rounded-full blur-3xl animate-pulse-glow" style="animation-delay:1.2s"></div>
    </div>

    <div class="w-full max-w-md animate-slide-up">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/images/logo.png" class="w-24 h-24 mx-auto rounded-3xl shadow-glow-lg mb-4 hover:scale-105 transition-transform duration-300 border border-white/5 bg-surface-800" alt="SubsTrack Logo" />
            <h1 class="text-3xl font-bold text-white mb-1">Create your account</h1>
            <p class="text-gray-400 text-sm">Start tracking subscriptions for free</p>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="flash-message mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 animate-fade-in">
                <div class="flex items-center gap-2 text-red-400 font-medium text-sm mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Please fix the following:
                </div>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $e): ?>
                        <li class="text-red-400 text-sm"><?= htmlspecialchars(trim($e), ENT_QUOTES) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Card -->
        <div class="glass-card p-8">
            <form method="POST" action="/register" id="registerForm" novalidate>
                <div class="space-y-5">

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
                                placeholder="johndoe" autocomplete="username"
                                value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES) ?>" required />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Letters, numbers, and underscores only</p>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" class="form-input pl-10"
                                placeholder="you@example.com" autocomplete="email"
                                value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" required />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-input pl-10 pr-12"
                                placeholder="••••••••" autocomplete="new-password" required />
                            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <!-- Password strength bar -->
                        <div class="mt-2 space-y-1.5">
                            <div class="h-1.5 w-full bg-surface-600 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-full w-0 rounded-full transition-all duration-500"></div>
                            </div>
                            <p id="strengthText" class="text-xs text-gray-500">Min. 8 chars · 1 uppercase · 1 number</p>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm" class="form-label">Confirm Password</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="confirm" name="confirm" class="form-input pl-10"
                                placeholder="••••••••" autocomplete="new-password" required />
                        </div>
                        <p id="matchText" class="mt-1 text-xs hidden"></p>
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="registerSubmit" class="btn-primary w-full mt-2">
                        Create Account
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            Already have an account?
            <a href="/login" class="text-brand-300 hover:text-brand-400 font-medium transition-colors">Sign in</a>
        </p>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>