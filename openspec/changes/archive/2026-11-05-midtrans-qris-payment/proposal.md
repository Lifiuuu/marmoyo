## Why

Saat ini Marmoyo Smart-Dine masih menggunakan mock payload string lokal untuk simulasi QRIS tanpa integrasi payment gateway nyata. Agar restoran dapat menerima pembayaran digital secara otomatis dan terverifikasi secara real-time dari pelanggan meja tanpa kasir harus memeriksa mutasi manual, diperlukan integrasi resmi dengan payment gateway Midtrans menggunakan metode QRIS.

## What Changes

- **Konfigurasi Lingkungan Midtrans**: Menambahkan environment variable (`MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_MERCHANT_ID`, `MIDTRANS_IS_PRODUCTION`) di `.env` dan konfigurasi `config/midtrans.php`.
- **Integrasi Midtrans Core API**: Mengintegrasikan API charge QRIS Midtrans (Core API) untuk menghasilkan QRIS string dinamis, transaction ID, dan batas waktu pembayaran resmi dari Midtrans.
- **Webhook Notifikasi Midtrans**: Menyediakan route & controller HTTP webhook (`/api/midtrans/notification`) dengan verifikasi `signature_key` (SHA-512) untuk memperbarui status pesanan menjadi lunas (`paid`) dan mencatat waktu pembayaran (`paid_at`) secara otomatis saat pelanggan selesai memindai QRIS.
- **Pembaruan Database Pembayaran**: Menambahkan kolom pendukung pada tabel `payments` (misal: `transaction_id`, `qris_url`, `payment_gateway_response`) untuk audit trail transaksi Midtrans.
- **Tampilan Pembayaran QRIS Pelanggan**: Memperbarui view pembayaran customer (`customer/payment.blade.php`) agar menampilkan QR code dinamis Midtrans secara responsif dengan polling status otomatis via AJAX sehingga pelanggan langsung diarahkan ke halaman status pesanan saat pembayaran sukses.

## Capabilities

### New Capabilities
- `midtrans-qris-integration`: Layanan integrasi Midtrans Core API untuk create charge QRIS dinamis, query status transaksi, dan verifikasi payload webhook notification dengan enkripsi SHA-512.
- `customer-qris-checkout`: Alur antarmuka pelanggan untuk menampilkan QR code Midtrans, hitung mundur kadaluarsa (expiry), auto-polling status pembayaran, dan auto-redirect ketika lunas.

### Modified Capabilities
<!-- None -->

## Impact

- **Dependensi & Konfigurasi**: Package `midtrans/midtrans-php` atau HTTP client native Laravel, file konfigurasi `config/midtrans.php`, `.env` dan `.env.example`.
- **Database**: Migrasi baru untuk menambahkan kolom `transaction_id`, `qris_url` pada tabel `payments`.
- **Backend / Services**:
  - `App\Services\Payment\MidtransService` (baru).
  - Refactor `App\Services\Payment\QrisPaymentService` untuk menggunakan `MidtransService`.
  - `App\Http\Controllers\Payment\MidtransWebhookController` (baru).
- **Routes**: Menambahkan route webhook `/api/midtrans/notification` (dikecualikan dari CSRF token) dan endpoint status polling `/customer/payment/status/{orderNumber}`.
- **Frontend**: `resources/views/customer/payment.blade.php` diperbarui untuk rendering QR Code Midtrans dan listener status real-time.

