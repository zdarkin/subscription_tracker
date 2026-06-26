<?php
$action          = $action ?? '';
$old             = $old ?? [];
$submitLabel     = $submitLabel ?? 'Save';

/**
 * Shared subscription form partial.
 * Variables expected: $action (form action URL), $method ('POST'),
 * $old (prefilled values array), $submitLabel (string).
 */
require_once __DIR__ . '/../../models/Subscription.php';
$cycles         = Subscription::CYCLES;
$categories     = Subscription::CATEGORIES;
$paymentMethods = Subscription::PAYMENT_METHODS;
$statuses       = ['active', 'paused', 'cancelled'];
?>
<form method="POST" action="<?= htmlspecialchars($action, ENT_QUOTES) ?>" id="subscriptionForm" novalidate>
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

        <!-- Renewal Date -->
        <div class="form-group">
            <label for="renewal_date" class="form-label">Next Renewal Date <span class="text-red-400">*</span></label>
            <input type="date" id="renewal_date" name="renewal_date" class="form-input"
                   required min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($old['renewal_date'] ?? '', ENT_QUOTES) ?>" />
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
            <?= htmlspecialchars($submitLabel ?? 'Save', ENT_QUOTES) ?>
        </button>
        <a href="/subscriptions" class="btn-secondary">Cancel</a>
    </div>
</form>
