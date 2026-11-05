<!-- ==================== FAST CASH VALIDATION MODAL WITH CAMERA SCANNER ==================== -->
<div id="cash-modal" class="fixed inset-0 bg-black/80 backdrop-blur-xs z-50 hidden flex items-center justify-center p-2.5 sm:p-4 md:p-6 overflow-y-auto">
    <div class="bg-stone-900 border border-stone-800 rounded-2xl sm:rounded-3xl max-w-sm sm:max-w-md md:max-w-lg w-full p-3.5 sm:p-5 md:p-6 shadow-2xl text-stone-100 space-y-3 sm:space-y-4 max-h-[94vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-2 sm:pb-3 border-b border-stone-800">
            <div>
                <h3 class="font-extrabold text-white text-xs sm:text-sm md:text-base">Validasi Pembayaran Tunai</h3>
                <p class="text-[9px] sm:text-[10px] md:text-xs text-stone-400">Pencocokan Uang Pembayaran Pelanggan</p>
            </div>
            <button type="button" onclick="closeCashValidationModal()" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-stone-800 text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm transition active:scale-95">&times;</button>
        </div>

        <form action="{{ route('cashier.validate-cash') }}" method="POST" class="space-y-3 sm:space-y-4" id="cash-validation-form">
            @csrf

            <!-- Kode Antrean Tunai Input with Camera Scanner Button -->
            <div class="space-y-1 sm:space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-[11px] sm:text-xs font-bold text-stone-300">Scan QR / Input Kode / Meja</label>
                    <span class="text-[9px] sm:text-[10px] text-stone-500 hidden xs:inline">TUNAI-0824, MRM-0824, atau 08</span>
                </div>
                <div class="flex items-center space-x-1.5 sm:space-x-2">
                    <input type="text" name="payment_code" id="modal-payment-code" placeholder="Scan QR / ketik kode / meja" required oninput="handleManualInput(this.value)" class="flex-1 bg-stone-950 border border-stone-700 rounded-xl px-2.5 sm:px-3 py-2 sm:py-2.5 font-mono text-xs sm:text-sm text-white uppercase tracking-wider outline-none focus:border-red-500 transition min-w-0">
                    
                    <!-- Tombol Buka Kamera Scanner (Responsive text on mobile & tablet) -->
                    <button type="button" onclick="toggleCameraScanner()" id="btn-camera-toggle" class="bg-red-600 hover:bg-red-700 text-white px-2.5 sm:px-3.5 py-2 sm:py-2.5 rounded-xl font-bold text-[11px] sm:text-xs flex items-center space-x-1 sm:space-x-1.5 shadow transition active:scale-95 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span id="btn-camera-text" class="hidden xs:inline">Scan Kamera</span>
                        <span class="xs:hidden">Scan</span>
                    </button>
                </div>
            </div>

            <!-- Camera Scanner Viewport: Responsive 1:1 Square Frame on HP & Tablet -->
            <div id="camera-scanner-wrapper" class="hidden bg-stone-950 border border-stone-700 rounded-2xl p-2.5 sm:p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] sm:text-[11px] font-bold text-amber-400 flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <span>Arahkan kamera ke QR Code meja / HP pelanggan:</span>
                    </span>
                    <button type="button" onclick="stopCameraScanner()" class="text-stone-400 hover:text-white text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded bg-stone-800 transition">
                        Tutup &times;
                    </button>
                </div>
                
                <!-- Fluid 1:1 Square Scanner Viewport matching printed QR sticker -->
                <div class="relative w-full max-w-[200px] xs:max-w-[220px] sm:max-w-[260px] md:max-w-[280px] aspect-square mx-auto rounded-2xl overflow-hidden border-2 border-red-500/80 bg-black shadow-2xl flex items-center justify-center">
                    <!-- Scanner Viewport DOM for Html5Qrcode -->
                    <div id="qr-camera-viewport" class="w-full h-full object-cover"></div>
                    
                    <!-- Viewfinder Overlay: 4 Corner target guides matching printed QR Code -->
                    <div class="absolute inset-3 sm:inset-4 pointer-events-none border border-white/20 rounded-xl">
                        <!-- Top-Left Corner -->
                        <div class="absolute -top-0.5 -left-0.5 w-5 h-5 sm:w-6 sm:h-6 border-t-3 sm:border-t-4 border-l-3 sm:border-l-4 border-red-500 rounded-tl-lg"></div>
                        <!-- Top-Right Corner -->
                        <div class="absolute -top-0.5 -right-0.5 w-5 h-5 sm:w-6 sm:h-6 border-t-3 sm:border-t-4 border-r-3 sm:border-r-4 border-red-500 rounded-tr-lg"></div>
                        <!-- Bottom-Left Corner -->
                        <div class="absolute -bottom-0.5 -left-0.5 w-5 h-5 sm:w-6 sm:h-6 border-b-3 sm:border-b-4 border-l-3 sm:border-l-4 border-red-500 rounded-bl-lg"></div>
                        <!-- Bottom-Right Corner -->
                        <div class="absolute -bottom-0.5 -right-0.5 w-5 h-5 sm:w-6 sm:h-6 border-b-3 sm:border-b-4 border-r-3 sm:border-r-4 border-red-500 rounded-br-lg"></div>
                        <!-- Laser Sweep Animation Line -->
                        <div class="w-full h-0.5 bg-red-500 shadow-[0_0_10px_#ef4444] animate-pulse my-auto absolute inset-x-0 top-1/2 -translate-y-1/2"></div>
                    </div>
                </div>
                <p id="camera-status-text" class="text-[9px] sm:text-[10px] text-stone-400 text-center font-medium">Menyiapkan kamera...</p>
            </div>

            <!-- Order Match Card Details -->
            <div id="modal-order-card" class="bg-stone-950 p-2.5 sm:p-3.5 rounded-2xl border border-stone-800 space-y-1 sm:space-y-1.5 transition">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-stone-400">Total Tagihan:</span>
                    <span id="modal-bill-display" class="text-sm sm:text-base md:text-lg font-black text-red-500">Rp 0</span>
                </div>
                <div id="modal-order-meta" class="hidden pt-1.5 border-t border-stone-800/80 text-[10px] sm:text-[11px] text-stone-300 flex items-center justify-between">
                    <div class="truncate mr-2">
                        <span id="modal-order-table" class="font-bold text-white">Meja --</span>
                        <span id="modal-order-customer" class="text-stone-400 ml-1 truncate">Pelanggan</span>
                    </div>
                    <span id="modal-order-badge" class="bg-emerald-950 text-emerald-400 border border-emerald-800/60 font-bold px-2 py-0.5 rounded text-[9px] sm:text-[10px] shrink-0">
                        ✓ Cocok
                    </span>
                </div>
                <p id="modal-order-hint" class="text-[9px] sm:text-[10px] text-stone-500 text-center pt-0.5">
                    Masukkan kode antrean atau scan QR untuk memunculkan tagihan otomatis.
                </p>
            </div>

            <!-- Quick Nominal Tender Buttons (Page 30 SRS) - Responsive 4 Column Grid -->
            <div class="space-y-1 sm:space-y-1.5">
                <label class="block text-[11px] sm:text-xs font-bold text-stone-300">Pilihan Nominal Cepat Kasir</label>
                <div class="grid grid-cols-4 gap-1 sm:gap-2 text-xs">
                    <button type="button" onclick="setCashReceived('exact')" class="bg-stone-800 hover:bg-stone-700 active:bg-stone-600 py-1.5 sm:py-2 rounded-xl font-bold text-stone-200 text-[10px] sm:text-xs transition truncate">Uang Pas</button>
                    <button type="button" onclick="setCashReceived(20000)" class="bg-stone-800 hover:bg-stone-700 active:bg-stone-600 py-1.5 sm:py-2 rounded-xl font-bold text-stone-200 text-[10px] sm:text-xs transition">20.000</button>
                    <button type="button" onclick="setCashReceived(50000)" class="bg-stone-800 hover:bg-stone-700 active:bg-stone-600 py-1.5 sm:py-2 rounded-xl font-bold text-stone-200 text-[10px] sm:text-xs transition">50.000</button>
                    <button type="button" onclick="setCashReceived(100000)" class="bg-stone-800 hover:bg-stone-700 active:bg-stone-600 py-1.5 sm:py-2 rounded-xl font-bold text-stone-200 text-[10px] sm:text-xs transition">100.000</button>
                </div>
            </div>

            <!-- Uang Fisik Diterima -->
            <div class="space-y-1">
                <label class="block text-[11px] sm:text-xs font-bold text-stone-300">Nominal Uang Diterima (Rp)</label>
                <input type="number" name="cash_received" id="modal-cash-received" oninput="calculateChange()" required min="0" placeholder="50000" class="w-full bg-stone-950 border border-stone-700 rounded-xl px-2.5 sm:px-3 py-1.5 sm:py-2.5 text-xs sm:text-sm md:text-base font-mono font-bold text-white outline-none focus:border-emerald-500">
            </div>

            <!-- Kembalian Otomatis Display (Page 30 SRS) -->
            <div class="bg-emerald-950/60 border border-emerald-800/80 p-2.5 sm:p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-emerald-300">Kembalian:</span>
                    <p class="text-[8px] sm:text-[10px] text-emerald-400">Terhitung otomatis oleh POS</p>
                </div>
                <span id="modal-change-display" class="text-sm sm:text-base md:text-lg font-black text-emerald-300">Rp 0</span>
            </div>

            <div class="flex items-center space-x-2 text-[11px] sm:text-xs text-stone-400">
                <input type="checkbox" id="auto-print-check" checked class="rounded bg-stone-800 border-stone-700 text-red-600 focus:ring-0">
                <label for="auto-print-check">Cetak struk kasir otomatis</label>
            </div>

            <button type="submit" id="btn-submit-cash-modal" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-black py-2.5 sm:py-3 rounded-xl transition shadow-lg active:scale-95 flex items-center justify-center space-x-2">
                <span>Konfirmasi Pembayaran</span>
            </button>
        </form>
    </div>
</div>

<style>
    #qr-camera-viewport video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 0.875rem;
    }
    #qr-camera-viewport img[alt="Info icon"] {
        display: none !important;
    }
</style>

<script>
    let currentBillAmount = 0;
    let html5QrScanner = null;
    let isScanning = false;
    let lookupDebounceTimer = null;

    function openCashValidationModal(presetCode = '') {
        document.getElementById('modal-payment-code').value = presetCode;
        document.getElementById('modal-bill-display').innerText = 'Rp 0';
        document.getElementById('modal-cash-received').value = '';
        document.getElementById('modal-change-display').innerText = 'Rp 0';
        document.getElementById('modal-order-meta').classList.add('hidden');
        document.getElementById('modal-order-hint').classList.remove('hidden');
        currentBillAmount = 0;
        document.getElementById('cash-modal').classList.remove('hidden');

        if (presetCode) {
            lookupPaymentCode(presetCode);
        } else {
            setTimeout(() => document.getElementById('modal-payment-code').focus(), 100);
        }
    }

    function closeCashValidationModal() {
        stopCameraScanner();
        document.getElementById('cash-modal').classList.add('hidden');
    }

    function handleManualInput(val) {
        if (lookupDebounceTimer) clearTimeout(lookupDebounceTimer);
        lookupDebounceTimer = setTimeout(() => {
            lookupPaymentCode(val);
        }, 300);
    }

    // Toggle live camera scanner on/off
    function toggleCameraScanner() {
        if (isScanning) {
            stopCameraScanner();
        } else {
            startCameraScanner();
        }
    }

    function startCameraScanner() {
        const wrapper = document.getElementById('camera-scanner-wrapper');
        const statusText = document.getElementById('camera-status-text');
        const btnText = document.getElementById('btn-camera-text');

        wrapper.classList.remove('hidden');
        statusText.innerText = "Mengakses modul kamera...";
        btnText.innerText = "Tutup Kamera";
        isScanning = true;

        if (typeof Html5Qrcode === 'undefined') {
            statusText.innerText = "Modul scanner tidak termuat. Silakan ketik kode bayar atau nomor meja.";
            return;
        }

        if (html5QrScanner) {
            try { html5QrScanner.clear(); } catch (e) {}
        }

        html5QrScanner = new Html5Qrcode("qr-camera-viewport");

        const qrCodeSuccessCallback = (decodedText) => {
            if (!decodedText) return;
            const clean = parseScannedQr(decodedText);
            document.getElementById('modal-payment-code').value = clean;
            lookupPaymentCode(clean);
            stopCameraScanner();
        };

        // Aspect ratio 1.0 creates a perfect square viewfinder matching printed QR codes
        const scanConfig = {
            fps: 15,
            aspectRatio: 1.0,
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                const min = Math.min(viewfinderWidth, viewfinderHeight);
                const edge = Math.floor(min * 0.75);
                return { width: edge, height: edge };
            }
        };

        // Try environment camera (back), fallback to any available camera (laptop/webcam)
        html5QrScanner.start({ facingMode: "environment" }, scanConfig, qrCodeSuccessCallback)
            .then(() => {
                statusText.innerText = "Kamera aktif. Posisikan QR code di dalam kotak merah.";
            })
            .catch(err => {
                console.warn("Back camera not available, trying default camera:", err);
                Html5Qrcode.getCameras().then(cameras => {
                    if (cameras && cameras.length) {
                        return html5QrScanner.start(cameras[0].id, scanConfig, qrCodeSuccessCallback);
                    }
                    throw err;
                }).then(() => {
                    statusText.innerText = "Kamera aktif. Posisikan QR code di dalam kotak merah.";
                }).catch(cameraErr => {
                    console.error("Camera access failed:", cameraErr);
                    statusText.innerText = "Gagal mengakses kamera. Berikan izin browser atau masukkan kode secara manual.";
                });
            });
    }

    function stopCameraScanner() {
        if (html5QrScanner && isScanning) {
            html5QrScanner.stop().then(() => {
                html5QrScanner.clear();
                isScanning = false;
                document.getElementById('camera-scanner-wrapper').classList.add('hidden');
                document.getElementById('btn-camera-text').innerText = "Scan Kamera";
            }).catch(err => {
                console.error("Error stopping scanner:", err);
                isScanning = false;
                document.getElementById('camera-scanner-wrapper').classList.add('hidden');
                document.getElementById('btn-camera-text').innerText = "Scan Kamera";
            });
        } else {
            isScanning = false;
            document.getElementById('camera-scanner-wrapper').classList.add('hidden');
            document.getElementById('btn-camera-text').innerText = "Scan Kamera";
        }
    }

    // Smart Parser for scanned text: handles URLs, table params, TUNAI-, and MRM- prefixes
    function parseScannedQr(rawText) {
        if (!rawText) return '';
        let text = rawText.trim();

        // 1. Scanned printed table QR URL: http://.../scan?table=02&token=...
        if (text.includes('table=')) {
            try {
                const url = new URL(text);
                const table = url.searchParams.get('table');
                if (table) return 'MEJA ' + table;
            } catch (e) {
                const match = text.match(/table=([A-Za-z0-9_-]+)/);
                if (match) return 'MEJA ' + match[1];
            }
        }

        // 2. Scanned payment URL: http://.../payment/MRM-0824
        if (text.includes('/payment/')) {
            const parts = text.split('/payment/');
            if (parts[1]) {
                const code = parts[1].split('?')[0].split('/')[0];
                return code.toUpperCase();
            }
        }

        // 3. Plain TUNAI- or MRM- code
        if (text.toUpperCase().includes('TUNAI-')) {
            const match = text.match(/TUNAI-[A-Z0-9_-]+/i);
            if (match) return match[0].toUpperCase();
        }

        if (text.toUpperCase().includes('MRM-')) {
            const match = text.match(/MRM-[A-Z0-9_-]+/i);
            if (match) return match[0].toUpperCase();
        }

        return text.toUpperCase();
    }

    // Realtime Database & In-memory Cash Payment Lookup
    async function lookupPaymentCode(code) {
        const cleanCode = code.trim().toUpperCase();
        if (!cleanCode) {
            currentBillAmount = 0;
            document.getElementById('modal-bill-display').innerText = 'Rp 0';
            document.getElementById('modal-order-meta').classList.add('hidden');
            document.getElementById('modal-order-hint').classList.remove('hidden');
            calculateChange();
            return;
        }

        // First check in-memory cache if available
        if (typeof pendingOrdersMap !== 'undefined' && pendingOrdersMap[cleanCode]) {
            const item = pendingOrdersMap[cleanCode];
            applyFoundOrder({
                found: true,
                payment_code: cleanCode,
                table_number: item.table_number ? ('Meja ' + item.table_number) : 'Meja --',
                customer_name: 'Pelanggan ' + (item.table_number || ''),
                amount: typeof item === 'object' && item.amount ? item.amount : parseFloat(item),
                amount_formatted: 'Rp ' + (typeof item === 'object' && item.amount ? item.amount : parseFloat(item)).toLocaleString('id-ID'),
            });
            return;
        }

        // Live backend lookup via API (always up-to-date across all pages)
        try {
            const lookupUrl = `{{ route('cashier.lookup-payment') }}?query=${encodeURIComponent(cleanCode)}`;
            const response = await fetch(lookupUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.found) {
                    applyFoundOrder(data);
                } else {
                    document.getElementById('modal-order-meta').classList.add('hidden');
                    document.getElementById('modal-order-hint').classList.remove('hidden');
                    document.getElementById('modal-order-hint').innerText = `Pesanan '${cleanCode}' tidak ditemukan atau sudah lunas.`;
                }
            }
        } catch (e) {
            console.error('Error fetching live order lookup:', e);
        }
    }

    function applyFoundOrder(data) {
        currentBillAmount = parseFloat(data.amount) || 0;
        document.getElementById('modal-bill-display').innerText = data.amount_formatted;
        document.getElementById('modal-order-table').innerText = data.table_number;
        document.getElementById('modal-order-customer').innerText = `• ${data.customer_name}`;
        document.getElementById('modal-order-meta').classList.remove('hidden');
        document.getElementById('modal-order-hint').classList.add('hidden');
        
        // Update input field to genuine payment code if different
        if (data.payment_code) {
            document.getElementById('modal-payment-code').value = data.payment_code;
        }

        calculateChange();
    }

    function setCashReceived(val) {
        const input = document.getElementById('modal-cash-received');
        if (val === 'exact') {
            input.value = currentBillAmount;
        } else {
            input.value = val;
        }
        calculateChange();
    }

    function calculateChange() {
        const received = parseFloat(document.getElementById('modal-cash-received').value || 0);
        const change = received - currentBillAmount;
        const display = document.getElementById('modal-change-display');

        if (currentBillAmount <= 0) {
            display.innerText = 'Rp 0';
            display.className = 'text-base sm:text-lg font-black text-stone-400';
            return;
        }

        if (change >= 0) {
            display.innerText = 'Rp ' + change.toLocaleString('id-ID');
            display.className = 'text-base sm:text-lg font-black text-emerald-300';
        } else {
            display.innerText = '-Rp ' + Math.abs(change).toLocaleString('id-ID') + ' (Kurang)';
            display.className = 'text-xs sm:text-sm font-black text-rose-400';
        }
    }
</script>
