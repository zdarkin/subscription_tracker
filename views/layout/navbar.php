<?php

/**
 * Shared navigation bar (included from authenticated views only).
 * Requires Session to be active.
 */
require_once dirname(__DIR__, 2) . '/controllers/Session.php';
$currentUsername = Session::get('user_username', 'User');
$currentRole     = Session::get('user_role', 'user');
$currentPath     = strtok($_SERVER['REQUEST_URI'], '?');
?>
<nav class="sticky top-0 z-50 bg-surface-800/80 backdrop-blur-xl border-b border-white/5 shadow-card">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="<?= $currentRole === 'admin' ? '/admin' : '/dashboard' ?>" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl overflow-hidden shadow-glow group-hover:shadow-glow-lg transition-all duration-300 bg-surface-800 flex items-center justify-center">
                    <img src="/images/logo.png" class="w-full h-full scale-125" style="object-fit: cover; object-position: 50% 25%;" alt="SubsTrack Logo" />
                </div>
                <span class="text-lg font-bold bg-gradient-to-r from-brand-300 to-purple-400 bg-clip-text text-transparent">SubsTrack</span>
            </a>

            <!-- Nav Links -->
            <div class="hidden md:flex items-center gap-1">
                <?php if ($currentRole === 'admin'): ?>
                    <a href="/admin"
                        class="nav-link <?= $currentPath === '/admin' ? 'nav-link--active' : '' ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Admin Dashboard
                    </a>
                    <a href="/admin/users"
                        class="nav-link <?= str_contains($currentPath, 'admin/users') ? 'nav-link--active' : '' ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Users
                    </a>
                <?php else: ?>
                    <a href="/dashboard"
                        class="nav-link <?= str_contains($currentPath, 'dashboard') ? 'nav-link--active' : '' ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="/subscriptions"
                        class="nav-link <?= str_contains($currentPath, 'subscriptions') ? 'nav-link--active' : '' ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Subscriptions
                    </a>
                <?php endif; ?>
            </div>

            <!-- User Menu & Mobile Toggle -->
            <div class="flex items-center gap-3">
                <?php if ($currentRole === 'admin'): ?>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-400 text-xs font-semibold border border-amber-500/30">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.243 3.03a1 1 0 01.514 1.306L9.11 6h1.89a1 1 0 01.724 1.69l-5 5.5a1 1 0 01-1.448-1.38L6.89 10H5a1 1 0 01-.707-1.707l4.5-4.5a1 1 0 011.45.237z" clip-rule="evenodd" />
                        </svg>
                        Admin
                    </span>
                <?php endif; ?>

                <!-- User Dropdown -->
                <div class="relative group">
                    <button id="userMenuBtn" class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">
                        <div class="w-8 h-8 rounded-full bg-gradient-brand flex items-center justify-center text-sm font-bold text-white">
                            <?= strtoupper(substr($currentUsername, 0, 1)) ?>
                        </div>
                        <span class="hidden sm:block text-sm font-medium"><?= htmlspecialchars($currentUsername, ENT_QUOTES) ?></span>
                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 py-2 bg-surface-700 border border-white/10 rounded-xl shadow-card animate-fade-in z-50">
                        <div class="px-4 py-2 border-b border-white/5">
                            <p class="text-xs text-gray-500">Signed in as</p>
                            <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($currentUsername, ENT_QUOTES) ?></p>
                        </div>

                        <!-- Navigation Links (Mobile only) -->
                        <div class="md:hidden border-b border-white/5 pb-1 mb-1">
                            <?php if ($currentRole === 'admin'): ?>
                                <a href="/admin"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-white/5 transition-colors duration-150 <?= $currentPath === '/admin' ? 'text-brand-300 font-semibold' : '' ?>">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="/admin/users"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-white/5 transition-colors duration-150 <?= str_contains($currentPath, 'admin/users') ? 'text-brand-300 font-semibold' : '' ?>">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Users
                                </a>
                            <?php else: ?>
                                <a href="/dashboard"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-white/5 transition-colors duration-150 <?= str_contains($currentPath, 'dashboard') ? 'text-brand-300 font-semibold' : '' ?>">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="/subscriptions"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-white/5 transition-colors duration-150 <?= str_contains($currentPath, 'subscriptions') ? 'text-brand-300 font-semibold' : '' ?>">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    Subscriptions
                                </a>
                            <?php endif; ?>
                        </div>

                        <a href="/settings"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-300 hover:bg-white/5 transition-colors duration-150 mt-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </a>
                        <form action="/logout" method="POST" class="block w-full">
                            <button type="submit"
                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors duration-150 text-left">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>