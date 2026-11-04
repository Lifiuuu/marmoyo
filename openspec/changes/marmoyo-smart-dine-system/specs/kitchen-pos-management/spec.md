# Specification: Kitchen and POS Management

## ADDED Requirements

### Requirement: Papan Antrean Pesanan Kasir (Live Order Queue)
Sistem SHALL menyediakan antarmuka POS kasir dan tampilan dapur yang menampilkan seluruh pesanan meja aktif secara real-time, dikelompokkan berdasarkan kartu status (Pesanan Masuk, Sedang Dimasak / Live Cook, Siap Saji, dan Selesai) lengkap dengan penghitung total omzet hari ini dan indikator durasi antrean.

#### Scenario: Kasir memantau pesanan aktif kedai
- **WHEN** pesanan baru masuk dari Meja 08
- **THEN** kartu pesanan Meja 08 muncul pada tab "Live Order" dengan rincian menu, badge status metode pembayaran, dan timer waktu tunggu pesanan

---

### Requirement: Validasi Pembayaran Tunai Cepat dengan Kalkulator Kembalian
Sistem SHALL menyediakan panel modal validasi pembayaran tunai kilat yang dapat diakses melalui pemindaian barcode pelanggan atau input manual kode antrean `TUNAI-xxxx`. Panel MUST menyediakan tombol nominal uang cepat (Uang Pas, Rp20.000, Rp50.000, Rp100.000), menghitung kembalian uang fisik secara otomatis, dan mengonfirmasi pembayaran dalam 1 klik.

#### Scenario: Kasir memvalidasi pembayaran tunai pelanggan
- **WHEN** kasir memindai barcode kode `TUNAI-0824` milik Meja 08 senilai Rp22.000 dan memilih tombol cepat uang diterima Rp50.000
- **THEN** sistem secara otomatis menghitung kembalian Rp28.000, mengaktifkan opsi cetak otomatis, dan setelah tombol "Konfirmasi & Cetak Struk" ditekan, status pesanan berubah menjadi lunas (`paid`) serta tiket otomatis diteruskan ke koki dapur

---

### Requirement: Pembaruan Status Pengerjaan Pesanan
Sistem SHALL menyediakan aksi bagi kasir atau staf kedai untuk memperbarui status pesanan dari "Sedang Dimasak" menjadi "Siap Saji" dan "Selesai" setelah pesanan diantarkan ke meja pelanggan. Pembaruan status MUST disinkronkan secara langsung ke layar pelacakan pelanggan.

#### Scenario: Staf menandai makanan telah siap disajikan
- **WHEN** koki menyelesaikan proses memasak dan staf menekan tombol status "Siap Saji" pada kartu Meja 08
- **THEN** status pesanan Meja 08 diperbarui di POS kasir dan layar smartphone pelanggan Meja 08 berubah menjadi status "Pesanan Siap Saji / Diantar ke Meja Anda"

---

### Requirement: Otomasi Cetak Tiket Printer Thermal Dapur (ESC/POS)
Sistem SHALL menyediakan modul pencetakan otomatis berstandar thermal ESC/POS (58mm/80mm) untuk tiket dapur saat pesanan terkonfirmasi lunas. Tiket dapur MUST mencetak nomor meja dengan ukuran font besar dan tegas, nomor pesanan, waktu transaksi, varian menu (level pedas & jenis mie), serta catatan koki secara eksplisit.

#### Scenario: Tiket dapur dicetak otomatis saat pesanan lunas
- **WHEN** pesanan Meja 08 terkonfirmasi lunas (baik via QRIS maupun validasi kasir)
- **THEN** sistem mengirimkan format cetak thermal dapur yang mencantumkan header "KEDAI MIE MARMOYO - TIKET DAPUR", label tebal "MEJA 08", rincian "1x Mie Kuah Mala (Level 5 Pedas Nampol, Catatan: Jangan terlalu berminyak)", dan "1x Udang Keju (Catatan: Goreng garing)"

