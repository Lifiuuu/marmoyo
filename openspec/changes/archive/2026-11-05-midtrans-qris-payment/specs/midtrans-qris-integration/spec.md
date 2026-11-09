## ADDED Requirements

### Requirement: Midtrans QRIS Charge Generation
Sistem SHALL menyediakan layanan untuk membuat transaksi charge QRIS dinamis ke Midtrans Core API menggunakan kredensial yang terkonfigurasi.

#### Scenario: Berhasil membuat charge QRIS ke Midtrans
- **WHEN** pesanan baru dibuat dengan metode pembayaran 'qris'
- **THEN** sistem mengirimkan request HTTP POST ke endpoint Midtrans Core API `/v2/charge` dengan tipe `qris` dan acquirer `gopay`
- **THEN** sistem menerima respon yang berisi `transaction_id`, `qr_string`, dan link URL image QR code
- **THEN** sistem menyimpan detail tersebut ke tabel `payments` dengan status `pending`

#### Scenario: Gagal terhubung ke Midtrans API
- **WHEN** request ke Midtrans mengalami timeout atau mengembalikan response error non-200
- **THEN** sistem mencatat log error dan melemparkan exception yang informatif ke pengguna tanpa membuat status pembayaran invalid

### Requirement: Midtrans Webhook Notification Handling
Sistem SHALL menyediakan endpoint webhook publik `/api/midtrans/notification` untuk menerima callback notifikasi status transaksi dari Midtrans.

#### Scenario: Notifikasi settlement pembayaran valid
- **WHEN** Midtrans mengirimkan HTTP POST notifikasi dengan `transaction_status` bernilai `settlement` atau `capture`
- **THEN** sistem memverifikasi `signature_key` dengan formula SHA-512 `hash('sha512', order_id + status_code + gross_amount + ServerKey)`
- **THEN** sistem mengubah status payment menjadi `success`, mencatat `paid_at`, dan mengubah status order menjadi `paid`
- **THEN** sistem mengembalikan HTTP response status 200 OK ke server Midtrans

#### Scenario: Notifikasi dengan signature tidak valid (spoofing)
- **WHEN** notifikasi diterima dengan `signature_key` yang tidak cocok dengan hash lokal
- **THEN** sistem menolak payload tersebut dengan response HTTP 403 / 400 dan tidak memperbarui status pembayaran

#### Scenario: Notifikasi kadaluarsa (expired) atau gagal
- **WHEN** Midtrans mengirimkan status `expire` atau `cancel`
- **THEN** sistem mengubah status payment menjadi `expired` atau `failed` dan mengubah status order menjadi `cancelled` atau `expired`

### Requirement: Midtrans Transaction Status Checking
Sistem SHALL menyediakan fungsionalitas untuk mengecek status transaksi langsung ke Midtrans API `/v2/{order_id}/status`.

#### Scenario: Sinkronisasi manual atau fallback polling
- **WHEN** endpoint status pembayaran dicek dan status lokal masih `pending`
- **THEN** sistem dapat melakukan query status langsung ke Midtrans API untuk memperbarui status lokal secara tepat waktu

