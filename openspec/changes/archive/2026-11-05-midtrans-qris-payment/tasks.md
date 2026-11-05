## 1. Environment & Konfigurasi

- [x] 1.1 Tambahkan variabel konfigurasi Midtrans (`MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_MERCHANT_ID`, `MIDTRANS_IS_PRODUCTION`) pada `.env` dan `.env.example`
- [x] 1.2 Buat file konfigurasi `config/midtrans.php` yang membaca variabel lingkungan di atas dengan default sandbox mode

## 2. Pembaruan Database

- [x] 2.1 Buat migrasi Laravel untuk menambahkan kolom `transaction_id`, `qris_url`, dan `payload_meta` pada tabel `payments`
- [x] 2.2 Jalankan migrasi `php artisan migrate`
- [x] 2.3 Perbarui `$fillable` dan `$casts` pada model `App\Models\Payment`

## 3. Implementasi Midtrans Service

- [x] 3.1 Buat class `App\Services\Payment\MidtransService` yang mengimplementasikan HTTP Client Laravel untuk memanggil Midtrans Core API `/v2/charge` dengan metode QRIS (GoPay/QRIS)
- [x] 3.2 Tambahkan method `verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool` pada `MidtransService` menggunakan hashing SHA-512
- [x] 3.3 Tambahkan method `getTransactionStatus(string $orderId): array` untuk query langsung ke `/v2/{order_id}/status`
- [x] 3.4 Refactor `App\Services\Payment\QrisPaymentService` agar memanggil `MidtransService` untuk men-generate charge dinamis dan mengupdate data pembayaran di tabel `payments`

## 4. Webhook Callback & Routing

- [x] 4.1 Buat controller `App\Http\Controllers\Payment\MidtransWebhookController` untuk menangani callback HTTP POST dari Midtrans
- [x] 4.2 Daftarkan route webhook `POST /api/midtrans/notification` dan kecualikan URL ini dari proteksi CSRF di `bootstrap/app.php`
- [x] 4.3 Implementasikan logika idempoten pada webhook: update status order & payment ke `paid`/`success` saat status `settlement` atau `capture`, dan trigger event print/dapur
- [x] 4.4 Buat endpoint `GET /customer/payment/status/{orderNumber}` untuk polling status pembayaran realtime dari sisi frontend

## 5. Antarmuka Pelanggan (Customer Frontend)

- [x] 5.1 Perbarui view `resources/views/customer/payment.blade.php` agar menampilkan QR code dinamis Midtrans (baik via SVG renderer dari `qr_string` atau `qris_url`)
- [x] 5.2 Implementasikan countdown timer waktu kadaluarsa (expiry) dinamis di halaman pembayaran
- [x] 5.3 Tambahkan script AJAX polling otomatis setiap 3 detik yang langsung me-redirect pelanggan ke `/customer/order/{orderNumber}` saat status lunas

## 6. Pengujian & Verifikasi

- [x] 6.1 Uji coba pembuatan pesanan baru dengan metode QRIS dan pastikan response Midtrans charge berhasil diterima
- [x] 6.2 Uji simulasi notifikasi webhook Midtrans (status settlement) menggunakan Postman / curl dan pastikan signature terverifikasi dan pesanan berubah menjadi `paid`
- [x] 6.3 Uji penolakan signature palsu pada webhook (HTTP 403 / 400)
- [x] 6.4 Uji flow auto-redirect di browser pelanggan saat QRIS selesai dibayar

