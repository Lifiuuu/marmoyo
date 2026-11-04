# Specification: Payment and Billing

## ADDED Requirements

### Requirement: Alur Pembayaran Digital QRIS Dinamis
Sistem SHALL menghasilkan QRIS dinamis unik untuk setiap pesanan dengan batas waktu kedaluwarsa pembayaran (countdown timer 5 menit) dan verifikasi otomatis tanpa mengharuskan pelanggan mengantre ke kasir.

#### Scenario: Pelanggan memilih metode pembayaran QRIS
- **WHEN** pelanggan memilih tab "QRIS Instan" di halaman metode pembayaran dan mengonfirmasi pesanan
- **THEN** sistem men-generate QR Code QRIS dinamis dengan ID Pesanan (misal: `#MRM-0824`), menampilkan timer countdown, tombol unduh QR, dan mendengarkan webhook konfirmasi pembayaran

#### Scenario: Pembayaran QRIS berhasil diverifikasi
- **WHEN** sistem menerima sinyal verifikasi pelunasan QRIS dari gateway/simulasi bank
- **THEN** sistem secara otomatis mengubah status pesanan menjadi `paid`, mengarahkan pelanggan ke layar sukses pembayaran, dan meneruskan tiket pesanan ke printer dapur

#### Scenario: Batas waktu pembayaran QRIS kedaluwarsa
- **WHEN** countdown timer pembayaran QRIS mencapai 00:00 tanpa adanya pembayaran
- **THEN** sistem mengubah status transaksi menjadi `expired`, menampilkan layar "Pembayaran Gagal / Kedaluwarsa", serta memberikan opsi "Buat Kode QRIS Baru" atau "Ganti ke Pembayaran Tunai di Kasir"

---

### Requirement: Alur Penerbitan Kode Pembayaran Tunai Kasir
Sistem SHALL menerbitkan kode antrean pembayaran tunai unik dengan format `TUNAI-xxxx` yang dilengkapi Barcode dan QR Code pada ponsel pelanggan jika metode "Bayar Tunai di Kasir" dipilih. Pesanan MUST tetap berstatus `pending_payment` dan TIDAK boleh diteruskan ke dapur sebelum kasir mengonfirmasi penerimaan uang fisik di sistem POS.

#### Scenario: Pelanggan memilih metode pembayaran tunai
- **WHEN** pelanggan memilih opsi "Bayar Tunai di Kasir" pada rincian pesanan Meja 08 senilai Rp22.000
- **THEN** sistem menampilkan layar "Menunggu Pembayaran di Kasir", mencantumkan kode antrean `TUNAI-0824`, menampilkan barcode transaksi, dan menginstruksikan pelanggan menuju meja kasir untuk pembayaran

---

### Requirement: Perhitungan Biaya, Pajak Restoran (PB1), dan Diskon
Sistem SHALL mengkalkulasi total transaksi secara transparan dan akurat yang terdiri dari subtotal item pesanan, pengurangan potongan voucher promosi, perhitungan pajak restoran PB1 sebesar 10% (termasuk/sesuai regulasi), dan penetapan biaya layanan self-order gratis (Rp0).

#### Scenario: Sistem menghitung ringkasan biaya checkout
- **WHEN** keranjang pesanan memuat menu senilai Rp25.000 dan kode promo diskon Rp3.000 aktif
- **THEN** sistem menampilkan Subtotal Rp25.000, Diskon Spesial -Rp3.000, Pajak Resto (PB1 10%) Sudah Termasuk, Biaya Layanan Self-Order GRATIS, dengan Total Pembayaran Rp22.000

---

### Requirement: Penerbitan dan Pengunduhan Struk Digital
Sistem SHALL menerbitkan struk digital resmi (*Digital Receipt*) setelah pesanan lunas, yang memuat nomor transaksi unik, tanggal & waktu pembayaran, nomor meja, rincian kuantitas & nama menu, catatan koki, subtotal, diskon, metode pembayaran, dan menyediakan tombol unduh berkas struk PDF/gambar.

#### Scenario: Pelanggan mengunduh struk pembayaran digital
- **WHEN** pelanggan menekan tombol "Unduh Bukti Pembayaran / Struk Digital" pada layar status sukses
- **THEN** sistem mengunduh berkas struk digital berformat rapi yang memuat rincian lengkap transaksi ID `#MRM-0824`

