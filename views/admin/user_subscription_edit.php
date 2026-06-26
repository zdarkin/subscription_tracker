<?php
$user            = $user ?? [];
$subscription    = $subscription ?? [];
$old             = $old ?? [];
$flash_error     = $flash_error ?? null;

$pageTitle       = "Edit Subscription for " . htmlspecialchars($user['username'] ?? '', ENT_QUOTES);
$pageDescription = "Edit subscription on behalf of " . htmlspecialchars($user['username'] ?? '', ENT_QUOTES);
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
require_once dirname(__DIR__, 2) . '/models/Subscription.php';

$cycles         = Subscription::CYCLES;
$categories     = Subscription::CATEGORIES;
$paymentMethods = Subscription::PAYMENT_METHODS;
$statuses       = ['active', 'paused', 'cancelled'];
?>

<div class="min-h-screen">
<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Flash Errors -->
    <?php if (!empty($flash_error)):
        $errors = explode('|', $flash_error);
    ?>
    <div class="flash-message mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 animate-fade-in">
        <div class="flex items-center gap-2 text-red-400 font-medium text-sm mb-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Please fix the following errors:
        </div>
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $e): ?>
            <li class="text-red-400 text-sm"><?= htmlspecialchars(trim($e), ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="/admin/users/<?= $user['id'] ?>/subscriptions"
           class="p-2 rounded-xl bg-surface-700 hover:bg-surface-600 text-gray-400 hover:text-white transition-all duration-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Subscription</h1>
            <p class="text-gray-400 text-sm mt-0.5">Modifying subscription for <strong><?= htmlspecialchars($user['username'], ENT_QUOTES) ?></strong></p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="glass-card p-6 sm:p-8 animate-slide-up">
        <form method="POST" action="/admin/users/<?= $user['id'] ?>/subscriptions/<?= $subscription['id'] ?>/edit" id="subscriptionForm" novalidate>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Service Name -->
                <div class="form-group md:col-span-2">
                    <label for="service_name" class="form-label">Service Name <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="input-icon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </span>
                        <input type="text" id="service_name" name="service_name" class="form-input pl-10"
                               placeholder="e.g., Netflix, Spotify, GitHub" required
                               value="<?= htmlspecialchars($old['service_name'] ?? '', ENT_QUOTES) ?>" />
                    </div>
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-input">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($old['category'] ?? 'Other') === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat, ENT_QUOTES) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Cost -->
                <div class="form-group">
                    <label for="cost" class="form-label">Cost <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">₱</span>
                        <input type="number" id="cost" name="cost" class="form-input pl-7"
                               placeholder="0.00" step="0.01" min="0.01" required
                               value="<?= htmlspecialchars($old['cost'] ?? '', ENT_QUOTES) ?>" />
                    </div>
                </div>

                <!-- Billing Cycle -->
                <div class="form-group">
                    <label for="billing_cycle" class="form-label">Billing Cycle <span class="text-red-400">*</span></label>
                    <select id="billing_cycle" name="billing_cycle" class="form-input">
                        <?php foreach (array_keys($cycles) as $cycle): ?>
                        <option value="<?= $cycle ?>" <?= ($old['billing_cycle'] ?? 'monthly') === $cycle ? 'selected' : '' ?>>
                            <?= ucfirst($cycle) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment Method -->
                <div class="form-group">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-input">
                        <?php foreach ($paymentMethods as $pm): ?>
                        <option value="<?= $pm ?>" <?= ($old['payment_method'] ?? 'Credit Card') === $pm ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pm, ENT_QUOTES) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="form-group">
                    <label for="start_date" class="form-label">Subscription Start Date <span class="text-red-400">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-input"
                           required
                           value="<?= htmlspecialchars($old['start_date'] ?? $old['renewal_date'] ?? '', ENT_QUOTES) ?>" />
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($old['status'] ?? 'active') === $st ? 'selected' : '' ?>>
                            <?= ucfirst($st) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Notes -->
                <div class="form-group md:col-span-2">
                    <label for="notes" class="form-label">Notes <span class="text-gray-600 font-normal">(optional)</span></label>
                    <textarea id="notes" name="notes" class="form-input resize-none" rows="3"
                              placeholder="Any additional details about this subscription..."><?= htmlspecialchars($old['notes'] ?? '', ENT_QUOTES) ?></textarea>
                </div>

            </div>

            <!-- Monthly cost preview -->
            <div id="costPreview" class="mt-4 p-3 rounded-xl bg-brand-600/10 border border-brand-600/20 text-sm text-brand-300 hidden">
                📊 Estimated monthly cost: <strong id="monthlyCostDisplay">₱0.00</strong>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-white/5">
                <button type="submit" class="btn-primary">
                    Save Changes
                </button>
                <a href="/admin/users/<?= $user['id'] ?>/subscriptions" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

</main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
