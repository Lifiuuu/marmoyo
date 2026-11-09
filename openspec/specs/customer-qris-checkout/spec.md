# customer-qris-checkout Specification

## Purpose
TBD - created by archiving change midtrans-qris-payment. Update Purpose after archive.
## Requirements
### Requirement: Tampilan QRIS Midtrans Pelanggan
Sistem SHALL menampilkan gambar QR code QRIS resmi dari Midtrans kepada pelanggan pada halaman pembayaran pesanan.

#### Scenario: Pelanggan membuka halaman pembayaran QRIS
- **WHEN** pelanggan diarahkan ke halaman `/customer/payment/{orderNumber}` dengan metode 'qris'
- **THEN** sistem menampilkan QR code yang dihasilkan dari `qr_string` atau image URL resmi Midtrans
- **THEN** sistem menampilkan nominal total yang harus dibayar dan hitung mundur waktu kadaluarsa (countdown timer)

### Requirement: Auto-Polling dan Redirect Setelah Lunas
Halaman pembayaran pelanggan SHALL secara otomatis memeriksa status pelunasan transaksi secara berkala.

#### Scenario: Pembayaran sukses terverifikasi
- **WHEN** pembayaran diselesaikan oleh pelanggan via dompet digital / m-banking dan status berubah menjadi `success`
- **THEN** polling di halaman pelanggan mendeteksi perubahan status dan langsung mengarahkan (redirect) pelanggan ke halaman `/customer/order/{orderNumber}` dengan pesan sukses

#### Scenario: Waktu pembayaran QRIS habis
- **WHEN** batas waktu pembayaran habis sebelum pelanggan menyelesaikan transfer
- **THEN** sistem menampilkan status kadaluarsa pada antarmuka dan menyediakan tombol untuk beralih ke pembayaran tunai (Cash di Kasir) atau generate QRIS ulang

