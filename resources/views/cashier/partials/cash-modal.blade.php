<!-- ==================== FAST CASH VALIDATION MODAL WITH CAMERA SCANNER ==================== -->
<div id="cash-modal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-stone-900 border border-stone-800 rounded-3xl max-w-md w-full p-6 shadow-2xl text-stone-100 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-stone-800">
            <div>
                <h3 class="font-extrabold text-white text-base">Validasi Pembayaran Tunai</h3>
                <p class="text-xs text-stone-400">Pencocokan Uang Pembayaran Pelanggan</p>
            </div>
            <button type="button" onclick="closeCashValidationModal()" class="w-8 h-8 rounded-full bg-stone-800 text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm">&times;</button>
        </div>

        <form action="{{ route('cashier.validate-cash') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Kode Antrean Tunai Input with Camera Scanner Button -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-stone-300">Scan QR / Input Kode Bayar</label>
                    <span class="text-[10px] text-stone-500">Contoh: TUNAI-0824</span>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="text" name="payment_code" id="modal-payment-code" placeholder="TUNAI-0824" required oninput="lookupPaymentCode(this.value)" class="flex-1 bg-stone-950 border border-stone-700 rounded-xl px-3 py-2.5 font-mono text-sm text-white uppercase tracking-wider outline-none focus:border-red-500 transition">
                    
                    <!-- Tombol Buka Kamera Scanner -->
                    <button type="button" onclick="toggleCameraScanner()" id="btn-camera-toggle" class="bg-red-600 hover:bg-red-700 text-white px-3.5 py-2.5 rounded-xl font-bold text-xs flex items-center space-x-1.5 shadow transition active:scale-95 shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span id="btn-camera-text">Scan Kamera</span>
                    </button>
                </div>
            </div>

            <!-- Camera Scanner Viewport (Hidden until "Scan Kamera" clicked) -->
            <div id="camera-scanner-wrapper" class="hidden bg-stone-950 border border-stone-700 rounded-2xl p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-400 flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <span>Arahkan kamera ke QR Code di layar HP Pelanggan:</span>
                    </span>
                    <button type="button" onclick="stopCameraScanner()" class="text-stone-400 hover:text-white text-xs font-bold px-2 py-0.5 rounded bg-stone-800">
                        Tutup Kamera &times;
                    </button>
                </div>
                <div id="qr-camera-viewport" class="w-full rounded-xl overflow-hidden border border-stone-800 bg-black min-h-[180px] max-h-[220px]"></div>
                <p id="camera-status-text" class="text-[10px] text-stone-500 text-center">Menyiapkan modul kamera...</p>
            </div>

            <!-- Total Tagihan Display -->
            <div class="bg-stone-950 p-3.5 rounded-xl border border-stone-800 flex items-center justify-between">
                <span class="text-xs text-stone-400">Total Tagihan:</span>
                <span id="modal-bill-display" class="text-base font-black text-red-500">Rp 0</span>
            </div>

            <!-- Quick Nominal Tender Buttons (Page 30 SRS) -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Pilihan Nominal Cepat Kasir</label>
                <div class="grid grid-cols-4 gap-2 text-xs">
                    <button type="button" onclick="setCashReceived('exact')" class="bg-stone-800 hover:bg-stone-700 py-2 rounded-lg font-bold text-stone-200">Uang Pas</button>
                    <button type="button" onclick="setCashReceived(20000)" class="bg-stone-800 hover:bg-stone-700 py-2 rounded-lg font-bold text-stone-200">20.000</button>
                    <button type="button" onclick="setCashReceived(50000)" class="bg-stone-800 hover:bg-stone-700 py-2 rounded-lg font-bold text-stone-200">50.000</button>
                    <button type="button" onclick="setCashReceived(100000)" class="bg-stone-800 hover:bg-stone-700 py-2 rounded-lg font-bold text-stone-200">100.000</button>
                </div>
            </div>

            <!-- Uang Fisik Diterima -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-stone-300">Nominal Uang Diterima (Rp)</label>
                <input type="number" name="cash_received" id="modal-cash-received" oninput="calculateChange()" required min="0" placeholder="50000" class="w-full bg-stone-950 border border-stone-700 rounded-xl px-3 py-2.5 text-base font-mono font-bold text-white outline-none focus:border-emerald-500">
            </div>

            <!-- Kembalian Otomatis Display (Page 30 SRS) -->
            <div class="bg-emerald-950/60 border border-emerald-800/80 p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-300">Kembalian Pelanggan:</span>
                    <p class="text-[10px] text-emerald-400">Terhitung otomatis oleh POS</p>
                </div>
                <span id="modal-change-display" class="text-lg font-black text-emerald-300">Rp 0</span>
            </div>

            <div class="flex items-center space-x-2 text-xs text-stone-400">
                <input type="checkbox" id="auto-print-check" checked class="rounded bg-stone-800 border-stone-700 text-red-600 focus:ring-0">
                <label for="auto-print-check">Cetak struk kasir otomatis</label>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black py-3 rounded-xl transition shadow-lg active:scale-95 flex items-center justify-center space-x-2">
                <span>Konfirmasi Pembayaran</span>
            </button>
        </form>
    </div>
</div>

<script>
    let currentBillAmount = 0;
    let html5QrScanner = null;
    let isScanning = false;

    function openCashValidationModal() {
        document.getElementById('modal-payment-code').value = '';
        currentBillAmount = 0;
        document.getElementById('modal-bill-display').innerText = 'Rp 0';
        document.getElementById('modal-cash-received').value = '';
        document.getElementById('modal-change-display').innerText = 'Rp 0';
        document.getElementById('cash-modal').classList.remove('hidden');
    }

    function openCashValidationWithData(code, amount) {
        document.getElementById('modal-payment-code').value = code;
        currentBillAmount = parseFloat(amount || 0);
        document.getElementById('modal-bill-display').innerText = 'Rp ' + currentBillAmount.toLocaleString('id-ID');
        document.getElementById('modal-cash-received').value = '';
        document.getElementById('modal-change-display').innerText = 'Rp 0';
        document.getElementById('cash-modal').classList.remove('hidden');
    }

    function closeCashValidationModal() {
        stopCameraScanner();
        document.getElementById('cash-modal').classList.add('hidden');
    }

    function toggleCameraScanner() {
        if (isScanning) {
            stopCameraScanner();
        } else {
            startCameraScanner();
        }
    }

    function startCameraScanner() {
        const wrapper = document.getElementById('camera-scanner-wrapper');
        wrapper.classList.remove('hidden');
        document.getElementById('btn-camera-text').innerText = 'Tutup Kamera';
        document.getElementById('camera-status-text').innerText = 'Mengaktifkan sensor kamera...';

        if (typeof Html5Qrcode === 'undefined') {
            document.getElementById('camera-status-text').innerText = 'Modul scanner sedang memuat, silakan coba 2 detik lagi...';
            return;
        }

        if (!html5QrScanner) {
            html5QrScanner = new Html5Qrcode("qr-camera-viewport");
        }

        const config = { fps: 10, qrbox: { width: 220, height: 220 } };

        html5QrScanner.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                onQrCodeScanned(decodedText);
            },
            (errorMessage) => {
                // Ignore frame parse errors
            }
        ).then(() => {
            isScanning = true;
            document.getElementById('camera-status-text').innerText = 'Kamera aktif. Dekatkan ke barcode di HP pelanggan.';
        }).catch(err => {
            console.error("Camera access error:", err);
            document.getElementById('camera-status-text').innerText = 'Kamera tidak dapat diakses (Pastikan izin webcam diberikan pada browser).';
        });
    }

    function stopCameraScanner() {
        if (html5QrScanner && isScanning) {
            html5QrScanner.stop().then(() => {
                isScanning = false;
                document.getElementById('camera-scanner-wrapper').classList.add('hidden');
                document.getElementById('btn-camera-text').innerText = 'Scan Kamera';
            }).catch(err => {
                console.error("Error stopping camera:", err);
                document.getElementById('camera-scanner-wrapper').classList.add('hidden');
                isScanning = false;
                document.getElementById('btn-camera-text').innerText = 'Scan Kamera';
            });
        } else {
            document.getElementById('camera-scanner-wrapper').classList.add('hidden');
            document.getElementById('btn-camera-text').innerText = 'Scan Kamera';
            isScanning = false;
        }
    }

    function onQrCodeScanned(qrCodeText) {
        stopCameraScanner();

        // Audio feedback beep
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.2;
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) {}

        const cleanCode = qrCodeText.trim().toUpperCase();
        const input = document.getElementById('modal-payment-code');
        input.value = cleanCode;

        // Visual highlight feedback
        input.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-950/40');
        setTimeout(() => {
            input.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-950/40');
        }, 2000);

        lookupPaymentCode(cleanCode);
    }

    function lookupPaymentCode(code) {
        code = (code || '').trim().toUpperCase();
        if (typeof pendingOrdersMap !== 'undefined' && pendingOrdersMap[code]) {
            currentBillAmount = pendingOrdersMap[code].amount;
            document.getElementById('modal-bill-display').innerText = 'Rp ' + currentBillAmount.toLocaleString('id-ID');
            calculateChange();
        }
    }

    function setCashReceived(val) {
        if (val === 'exact') {
            document.getElementById('modal-cash-received').value = currentBillAmount;
        } else {
            document.getElementById('modal-cash-received').value = val;
        }
        calculateChange();
    }

    function calculateChange() {
        const received = parseFloat(document.getElementById('modal-cash-received').value || 0);
        const change = received - currentBillAmount;
        const display = document.getElementById('modal-change-display');

        if (change >= 0) {
            display.innerText = 'Rp ' + change.toLocaleString('id-ID');
            display.className = 'text-lg font-black text-emerald-300';
        } else {
            display.innerText = '-Rp ' + Math.abs(change).toLocaleString('id-ID') + ' (Kurang)';
            display.className = 'text-xs font-black text-rose-400';
        }
    }
</script>

