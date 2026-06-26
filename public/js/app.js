/* ============================================================
   SubsTrack — app.js
   Client-side interactions: nav menu, password toggle,
   search filter, donut chart, monthly cost preview,
   delete confirmation, form loading states
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    // ----------------------------------------------------------
    // 1. User dropdown menu
    // ----------------------------------------------------------
    const menuBtn  = document.getElementById('userMenuBtn');
    const menuEl   = document.getElementById('userMenu');

    if (menuBtn && menuEl) {
        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            menuEl.classList.toggle('hidden');
        });

        menuEl.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        document.addEventListener('click', () => {
            menuEl.classList.add('hidden');
        });
    }



    // ----------------------------------------------------------
    // 2. Password toggle (eye icon)
    // ----------------------------------------------------------
    const toggleBtn = document.getElementById('togglePassword');
    const pwField   = document.getElementById('password');

    if (toggleBtn && pwField) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = pwField.type === 'password';
            pwField.type   = isHidden ? 'text' : 'password';
            const eyeIcon  = document.getElementById('eyeIcon');
            if (eyeIcon) {
                eyeIcon.innerHTML = isHidden
                    ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97
                            9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242
                            4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0
                            0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0
                            01-4.132 5.411m0 0L21 21"/>`
                    : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274
                            4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
            }
        });
    }

    // ----------------------------------------------------------
    // 3. Password strength meter (register page)
    // ----------------------------------------------------------
    const regPwField    = document.getElementById('password');
    const strengthBar   = document.getElementById('strengthBar');
    const strengthText  = document.getElementById('strengthText');
    const confirmField  = document.getElementById('confirm');
    const matchText     = document.getElementById('matchText');

    function evaluateStrength(pw) {
        let score = 0;
        if (pw.length >= 8)                    score++;
        if (/[A-Z]/.test(pw))                  score++;
        if (/[0-9]/.test(pw))                  score++;
        if (/[^A-Za-z0-9]/.test(pw))           score++;
        return score;
    }

    if (regPwField && strengthBar) {
        regPwField.addEventListener('input', () => {
            const pw    = regPwField.value;
            const score = evaluateStrength(pw);
            const widths = ['0%', '25%', '50%', '75%', '100%'];
            const colors = ['', '#ef4444', '#f59e0b', '#84cc16', '#22c55e'];
            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

            strengthBar.style.width = widths[score];
            strengthBar.style.background = colors[score] || 'transparent';
            if (strengthText) {
                strengthText.textContent = pw.length
                    ? `Password strength: ${labels[score]}`
                    : 'Min. 8 chars · 1 uppercase · 1 number';
                strengthText.style.color = colors[score] || '#6b7280';
            }
        });
    }

    if (confirmField && matchText && regPwField) {
        function checkMatch() {
            const match = regPwField.value === confirmField.value && confirmField.value !== '';
            matchText.classList.toggle('hidden', confirmField.value === '');
            matchText.textContent = match ? '✓ Passwords match'        : '✗ Passwords do not match';
            matchText.style.color = match ? 'rgb(74, 222, 128)'        : 'rgb(248, 113, 113)';
        }
        confirmField.addEventListener('input', checkMatch);
        regPwField.addEventListener('input',   checkMatch);
    }

    // ----------------------------------------------------------
    // 4. Subscription list search filter
    // ----------------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    const subCards    = document.querySelectorAll('.sub-card');

    if (searchInput && subCards.length) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase().trim();
            subCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                card.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    // ----------------------------------------------------------
    // 5. Category Donut Chart (Dashboard)
    // ----------------------------------------------------------
    const canvas = document.getElementById('categoryChart');
    if (canvas && window.CHART_DATA) {
        const { labels, values } = window.CHART_DATA;

        if (labels.length === 0) {
            canvas.style.display = 'none';
        } else {
            const COLORS = [
                '#6366f1', '#8b5cf6', '#a855f7', '#ec4899',
                '#06b6d4', '#10b981', '#f59e0b', '#ef4444',
                '#84cc16', '#0ea5e9',
            ];

            const total = values.reduce((a, b) => a + b, 0);
            const ctx   = canvas.getContext('2d');
            const W     = canvas.width;
            const H     = canvas.height;
            const cx    = W / 2;
            const cy    = H / 2;
            const R     = Math.min(W, H) / 2 - 10;
            const INNER = R * 0.55;

            let startAngle = -Math.PI / 2;

            ctx.clearRect(0, 0, W, H);

            values.forEach((val, i) => {
                const slice = (val / total) * 2 * Math.PI;
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.arc(cx, cy, R, startAngle, startAngle + slice);
                ctx.closePath();
                ctx.fillStyle = COLORS[i % COLORS.length];
                ctx.fill();
                startAngle += slice;
            });

            // Inner hole
            ctx.beginPath();
            ctx.arc(cx, cy, INNER, 0, 2 * Math.PI);
            ctx.fillStyle = '#111118';
            ctx.fill();

            // Center text
            ctx.fillStyle = '#ffffff';
            ctx.font      = 'bold 13px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('₱' + total.toFixed(0) + '/mo', cx, cy);

            // Legend
            const legend = document.getElementById('chartLegend');
            if (legend) {
                legend.innerHTML = labels.map((label, i) => `
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                  style="background:${COLORS[i % COLORS.length]}"></span>
                            <span class="text-gray-300 truncate">${label}</span>
                        </div>
                        <span class="text-white font-semibold flex-shrink-0">₱${values[i].toFixed(2)}</span>
                    </div>
                `).join('');
            }
        }
    }

    // ----------------------------------------------------------
    // 6. Live Monthly Cost Preview (subscription form)
    // ----------------------------------------------------------
    const costInput    = document.getElementById('cost');
    const cycleSelect  = document.getElementById('billing_cycle');
    const costPreview  = document.getElementById('costPreview');
    const costDisplay  = document.getElementById('monthlyCostDisplay');

    const CYCLE_DIVISORS = {
        monthly: 1, quarterly: 3, 'semi-annual': 6, annual: 12,
    };

    function updateCostPreview() {
        const cost   = parseFloat(costInput?.value) || 0;
        const cycle  = cycleSelect?.value || 'monthly';
        const monthly = cost / (CYCLE_DIVISORS[cycle] || 1);

        if (costPreview && costDisplay) {
            if (cost > 0) {
                costDisplay.textContent = '₱' + monthly.toFixed(2);
                costPreview.classList.remove('hidden');
            } else {
                costPreview.classList.add('hidden');
            }
        }
    }

    costInput?.addEventListener('input', updateCostPreview);
    cycleSelect?.addEventListener('change', updateCostPreview);
    updateCostPreview(); // init on edit page

    // ----------------------------------------------------------
    // 7. Custom Delete Confirmation Modal
    // ----------------------------------------------------------
    const deleteModal = document.getElementById('deleteConfirmModal');
    const deleteCard  = document.getElementById('deleteConfirmCard');
    const cancelBtn   = document.getElementById('deleteConfirmCancel');
    const proceedBtn  = document.getElementById('deleteConfirmProceed');
    const messageEl   = document.getElementById('deleteConfirmMessage');

    let pendingButton = null;

    function showDeleteModal(btn) {
        pendingButton = btn;
        const form = btn.closest('form.delete-confirm-form');
        const msg  = form ? form.getAttribute('data-confirm') : null;
        if (messageEl) {
            messageEl.innerHTML = msg || 'Are you sure you want to permanently delete this item? This action cannot be undone.';
        }
        if (deleteModal && deleteCard) {
            deleteModal.classList.remove('opacity-0', 'pointer-events-none');
            // Trigger reflow so transition plays
            void deleteModal.offsetHeight;
            deleteCard.classList.remove('scale-95', 'opacity-0');
        }
    }

    function hideDeleteModal() {
        pendingButton = null;
        if (deleteModal && deleteCard) {
            deleteCard.classList.add('scale-95', 'opacity-0');
            deleteModal.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    // Intercept clicks on submit buttons inside delete-confirm-form forms
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('form.delete-confirm-form button[type="submit"]');
        if (!btn) return;
        // If already confirmed, let it proceed
        if (btn.dataset.confirmed === 'true') {
            delete btn.dataset.confirmed;
            return;
        }
        e.preventDefault();
        showDeleteModal(btn);
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', hideDeleteModal);
    }

    if (proceedBtn) {
        proceedBtn.addEventListener('click', () => {
            if (pendingButton) {
                // Mark as confirmed so the next click goes through
                pendingButton.dataset.confirmed = 'true';
                pendingButton.click();
            }
            hideDeleteModal();
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) hideDeleteModal();
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideDeleteModal();
    });

    // ----------------------------------------------------------
    // 8. Form submit loading state
    // ----------------------------------------------------------
    const loginForm    = document.getElementById('loginForm');
    const loginSubmit  = document.getElementById('loginSubmit');

    if (loginForm && loginSubmit) {
        loginForm.addEventListener('submit', () => {
            loginSubmit.querySelector('.btn-text')?.classList.add('hidden');
            loginSubmit.querySelector('.btn-loading')?.classList.remove('hidden');
            loginSubmit.disabled = true;
        });
    }

    // ----------------------------------------------------------
    // 9. Auto-dismiss flash messages after 5 seconds
    // ----------------------------------------------------------
    const flashes = document.querySelectorAll('.flash-message');
    flashes.forEach(el => {
        if (el.closest('form')) return; // skip form error lists
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s ease';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

    // ----------------------------------------------------------
    // 10. Admin User Table Sorting
    // ----------------------------------------------------------
    const tableBody = document.querySelector('table tbody');
    const headers = document.querySelectorAll('.sort-header');

    if (tableBody && headers.length) {
        // Track the current sorting state
        let activeColumn = null;
        let activeDirection = null;

        // Default sorting specifications:
        // - Name & Role: Default first click: Ascending (A-Z)
        // - Registration Date & Subscriptions Count: Default first click: Descending
        const defaultDirections = {
            name: 'asc',
            role: 'asc',
            registrationDate: 'desc',
            subscriptionsCount: 'desc'
        };

        // DOM data-attribute mappings for fast lookups
        const attributeMap = {
            name: 'data-name',
            role: 'data-role',
            registrationDate: 'data-date',
            subscriptionsCount: 'data-subs'
        };

        // Cache Intl.Collator for maximum string comparison performance
        const stringCollator = new Intl.Collator(undefined, {
            sensitivity: 'base',
            numeric: true
        });

        // Set up click listeners on column headers
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');

                // Determine new sorting state
                if (activeColumn !== column) {
                    activeColumn = column;
                    activeDirection = defaultDirections[column];
                } else {
                    activeDirection = activeDirection === 'asc' ? 'desc' : 'asc';
                }

                // Update UI chevrons
                updateHeaderIndicators(header, activeDirection);

                // Sort the table rows
                sortRows(column, activeDirection);
            });
        });

        function updateHeaderIndicators(clickedHeader, direction) {
            headers.forEach(h => {
                const icon = h.querySelector('.sort-icon');
                if (!icon) return;

                if (h === clickedHeader) {
                    // Make active icon fully visible
                    icon.classList.remove('opacity-0', 'opacity-50', 'group-hover:opacity-50');
                    icon.classList.add('opacity-100', 'text-brand-400');
                    
                    // Rotate the chevron to indicate ascending vs descending
                    if (direction === 'asc') {
                        icon.classList.add('rotate-180');
                    } else {
                        icon.classList.remove('rotate-180');
                    }
                } else {
                    // Reset inactive header icons
                    icon.classList.remove('opacity-100', 'text-brand-400', 'rotate-180');
                    icon.classList.add('opacity-0', 'group-hover:opacity-50');
                }
            });
        }

        function sortRows(column, direction) {
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            const multiplier = direction === 'asc' ? 1 : -1;

            rows.sort((rowA, rowB) => {
                const valA = rowA.getAttribute(attributeMap[column]);
                const valB = rowB.getAttribute(attributeMap[column]);

                // Gracefully handle missing/null values by pushing them to the bottom of the table
                const isAMissing = valA === null || valA === undefined || valA === '';
                const isBMissing = valB === null || valB === undefined || valB === '';

                if (isAMissing && isAMissing === isBMissing) return 0;
                if (isAMissing) return 1;
                if (isBMissing) return -1;

                let comparison = 0;

                switch (column) {
                    case 'name':
                    case 'role':
                        comparison = stringCollator.compare(valA, valB);
                        break;
                    case 'registrationDate':
                        const timeA = Date.parse(valA);
                        const timeB = Date.parse(valB);
                        const cleanA = isNaN(timeA) ? 0 : timeA;
                        const cleanB = isNaN(timeB) ? 0 : timeB;
                        comparison = cleanA - cleanB;
                        break;
                    case 'subscriptionsCount':
                        comparison = (parseInt(valA, 10) || 0) - (parseInt(valB, 10) || 0);
                        break;
                    default:
                        comparison = 0;
                }

                return comparison * multiplier;
            });

            // Re-append sorted rows to update DOM in one layout recalculation pass
            const fragment = document.createDocumentFragment();
            rows.forEach(row => fragment.appendChild(row));
            tableBody.appendChild(fragment);
        }
    }

});
