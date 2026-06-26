<?php
/* =================================================================
   Partial: DeleteModal.php
   Renders the global delete confirmation modal.
   Triggered by JS in app.js (section 7) via .delete-confirm-form.
   ================================================================= */
?>

<!-- Delete Confirmation Modal -->
<div
    id="deleteConfirmModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteConfirmTitle"
    aria-describedby="deleteConfirmMessage"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"
>
    <div id="deleteConfirmCard" class="glass-card max-w-md w-full p-6 border border-white/10 shadow-glow-lg transform scale-95 opacity-0 transition-all duration-300">

        <!-- Modal Header -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 id="deleteConfirmTitle" class="text-lg font-bold text-white">Confirm Deletion</h3>
        </div>

        <!-- Modal Body -->
        <p id="deleteConfirmMessage" class="text-sm text-gray-400 mb-6 leading-relaxed">
            Are you sure you want to permanently delete this item? This action cannot be undone.
        </p>

        <!-- Modal Actions -->
        <div class="flex items-center justify-end gap-3">
            <button type="button" id="deleteConfirmCancel" class="btn-secondary py-2 px-4 text-sm font-medium">
                Cancel
            </button>
            <button type="button" id="deleteConfirmProceed" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors">
                Delete
            </button>
        </div>

    </div>
</div>
