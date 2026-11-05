import './bootstrap';

/**
 * =========================================================================
 * MARMOYO SMART-DINE: RESILIENCE SHIELD & IDEMPOTENCY SYSTEM
 * - Anti double-click on transaction buttons across all roles
 * - Server latency lock with visual processing indicator
 * - Offline / Server down detection to prevent duplicate retry storm
 * =========================================================================
 */
(function () {
    // 1. Server & Network Connection Monitoring
    function updateConnectionStatus(isOnline) {
        let toast = document.getElementById('marmoyo-network-toast');
        if (!isOnline) {
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'marmoyo-network-toast';
                toast.className = 'fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 z-50 bg-rose-600 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 text-xs font-bold transition-all';
                toast.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-extrabold">Koneksi Server Terputus!</p>
                        <p class="text-[10px] text-rose-100 font-normal">Jangan mengirim ulang transaksi untuk mencegah duplikasi pesanan.</p>
                    </div>
                `;
                document.body.appendChild(toast);
            }
            toast.style.display = 'flex';
        } else if (toast) {
            toast.style.display = 'none';
        }
    }

    window.addEventListener('offline', () => updateConnectionStatus(false));
    window.addEventListener('online', () => updateConnectionStatus(true));

    // 2. Global Anti-Double Submission Form Guard
    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // Offline guard: block transactions when connection drops
        if (!navigator.onLine) {
            e.preventDefault();
            alert('Koneksi internet atau server sedang terputus. Mohon periksa jaringan Anda sebelum melanjutkan transaksi.');
            return false;
        }

        // Double submit protection
        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        form.dataset.submitting = 'true';

        // Disable submit button(s) and show loading indicator
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            if (btn.tagName === 'BUTTON' && !btn.dataset.originalHtml) {
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses Transaksi...</span>
                `;
            }
        });

        // Resilience timeout: safety unlocking after 10s in case page does not navigate or server returns error
        setTimeout(() => {
            form.dataset.submitting = 'false';
            submitButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
                if (btn.dataset.originalHtml) {
                    btn.innerHTML = btn.dataset.originalHtml;
                }
            });
        }, 10000);
    }, false);
})();
