<?php
$error           = $error ?? null;
$success         = $success ?? null;

$pageTitle       = 'Sign In';
$pageDescription = 'Sign in to your SubsTrack account to manage your subscriptions.';
require_once dirname(__DIR__) . '/layout/header.php';
?>

<div class="min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    <!-- Decorative background blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl animate-pulse-glow"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-purple-700/15 rounded-full blur-3xl animate-pulse-glow" style="animation-delay:1s"></div>
    </div>

    <div class="w-full max-w-md animate-slide-up">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/images/logo.png" class="w-24 h-24 mx-auto rounded-3xl shadow-glow-lg mb-4 hover:scale-105 transition-transform duration-300 border border-white/5 bg-surface-800" alt="SubsTrack Logo" />
            <h1 class="text-3xl font-bold text-white mb-1">Welcome back</h1>
            <p class="text-gray-400 text-sm">Sign in to manage your subscriptions</p>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($error)): ?>
        <div class="flash-message mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-start gap-3 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><?= htmlspecialchars($error, ENT_QUOTES) ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="flash-message mb-5 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-start gap-3 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><?= htmlspecialchars($success, ENT_QUOTES) ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
        <div class="flash-message mb-5 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm flex items-start gap-3 animate-fade-in">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Your session expired due to inactivity. Please sign in again.</span>
        </div>
        <?php endif; ?>

        <!-- Card -->
        <div class="glass-card p-8">
            <form method="POST" action="/login" id="loginForm" novalidate>
                <div class="space-y-5">

                    <!-- Username or Email -->
                    <div class="form-group">
                        <label for="identifier" class="form-label">Username or Email</label>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input type="text" id="identifier" name="identifier" class="form-input pl-10"
                                   placeholder="Enter username or email" autocomplete="username" required />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="form-label mb-0">Password</label>
                        </div>
                        <div class="relative">
                            <span class="input-icon">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input type="password" id="password" name="password" class="form-input pl-10 pr-12"
                                   placeholder="••••••••" autocomplete="current-password" required />
                            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="loginSubmit" class="btn-primary w-full mt-2">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading hidden">
                            <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Signing in...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Register Link -->
        <p class="text-center text-gray-500 text-sm mt-6">
            Don't have an account?
            <a href="/register" class="text-brand-300 hover:text-brand-400 font-medium transition-colors">Create one free</a>
        </p>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
