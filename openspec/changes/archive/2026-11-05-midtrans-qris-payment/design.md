## Context

Marmoyo Smart-Dine adalah aplikasi F&B interaktif berbasis Laravel 12. Pada flow pemesanan saat ini, pelanggan dapat memilih metode `qris` atau `cash`. Namun, layanan `QrisPaymentService` saat ini hanya menghasilkan string QRIS simulasi (mock EMVCo string) dan membutuhkan verifikasi manual/simulasi tombol. 

Untuk membawa sistem ini ke tahap siap produksi (production-ready) dan memudahkan pengujian sandbox yang realistis, Marmoyo memerlukan integrasi resmi dengan payment gateway Midtrans melalui Core API (QRIS).

## Goals / Non-Goals

**Goals:**
- Mengintegrasikan Midtrans Core API `/v2/charge` dengan tipe pembayaran `qris` (GoPay / AirPay QRIS).
- Menyimpan detail respon Midtrans (`transaction_id`, `qr_string`, `qris_url`, `expires_at`) ke database.
- Menyediakan endpoint webhook `/api/midtrans/notification` untuk menerima HTTP POST callback dari Midtrans dengan validasi keamanan `signature_key` (SHA-512).
- Mengupdate status `payments` dan `orders` secara otomatis dan idempoten begitu status `settlement` atau `capture` diterima.
- Menyediakan endpoint fallback polling status pembayaran `/customer/payment/status/{orderNumber}` yang memverifikasi status langsung ke Midtrans API `/v2/{order_id}/status`.
- Memperbarui tampilan UI `customer/payment.blade.php` agar menampilkan QR code dinamis Midtrans dengan countdown waktu dan auto-redirect ke halaman status saat lunas.

**Non-Goals:**
- Integrasi metode pembayaran Midtrans di luar QRIS (seperti Virtual Account, Kartu Kredit, atau Paylater).
- Penggunaan Midtrans Snap modal popup (sistem memilih Core API headless agar UI pembayaran tetap sesuai desain elegan Marmoyo Smart-Dine).

## Decisions

### 1. Midtrans Core API (Headless QRIS) vs Snap Popup
- **Pilihan**: Menggunakan Midtrans Core API (`payment_type: qris`).
- **Alasan**: Pelanggan tetap berada dalam halaman web app Marmoyo di meja tanpa redirect ke halaman lain atau memunculkan popup pihak ketiga yang merusak konsistensi tema dan UX.
- **Alternatif yang ditolak**: Midtrans Snap Popup UI, karena membatasi kustomisasi tata letak antarmuka kasir dan meja.

### 2. Laravel HTTP Client Native vs SDK `midtrans/midtrans-php`
- **Pilihan**: Menggunakan `Illuminate\Support\Facades\Http` dengan Basic Auth (`base64_encode(SERVER_KEY . ':')`).
- **Alasan**: Menghindari dependensi eksternal tambahan, kompatibilitas 100% dengan PHP 8.2 & Laravel 12, penanganan timeout yang presisi, dan kemudahan dalam penulisan unit/feature test via `Http::fake()`.
- **Alternatif yang ditolak**: Package `midtrans/midtrans-php` yang memiliki codebase legacy dan static configuration global.

### 3. Keamanan Webhook dengan Validasi Signature SHA-512
- **Pilihan**: Memverifikasi formula hash: `hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey)`.
- **Alasan**: Mencegah serangan pemalsuan notifikasi pembayaran (payment spoofing).
- **Pengecualian CSRF**: Route `/api/midtrans/notification` dikecualikan dari middleware `VerifyCsrfToken` di `bootstrap/app.php` karena dipanggil langsung oleh server Midtrans.

### 4. Penanganan Lingkungan Lokal (Local Development) & Fallback Polling
- **Pilihan**: Frontend customer melakukan polling berkala (setiap 3-5 detik) ke endpoint backend `/customer/payment/status/{orderNumber}`.
- **Alasan**: Di lingkungan lokal (seperti Laragon) tanpa tunneling Ngrok/webhook live, polling ini dapat bertindak sebagai fallback dengan langsung memeriksa status transaksi ke API Midtrans jika status lokal masih `pending`.

## Risks / Trade-offs

- **[Webhook Terlambat atau Gagal Kirim dari Midtrans]** → **Mitigasi**: Fallback polling otomatis dari frontend customer yang memicu pembaruan status ke Midtrans API secara on-demand.
- **[Webhook Terkirim Lebih dari Sekali (Duplikasi Callback)]** → **Mitigasi**: Logika idempoten pada `MidtransWebhookController`; jika status pembayaran di database sudah `success`, webhook duplikat diabaikan dengan return HTTP 200 OK.
- **[Kredensial Server Key Bocor]** → **Mitigasi**: Kredensial hanya disimpan di `.env` (`MIDTRANS_SERVER_KEY`) dan dipanggil via `config/midtrans.php`. Server Key tidak pernah dikirim ke frontend.

