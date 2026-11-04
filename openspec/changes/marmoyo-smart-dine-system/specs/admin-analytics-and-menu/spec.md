# Specification: Admin Analytics and Menu Management

## ADDED Requirements

### Requirement: Dashboard Analitik Eksekutif Pemilik
Sistem SHALL menyediakan dashboard analitik performa bisnis kedai yang menyajikan ringkasan metrik omzet harian hingga bulanan, total volume transaksi, rata-rata penilaian kepuasan pelanggan, breakdown persentase metode pembayaran (QRIS vs Tunai), grafik tren penjualan per jam (mengidentifikasi lonjakan jam sibuk sore-malam), serta daftar peringkat menu terlaris (*Top Selling Menus*).

#### Scenario: Pemilik membuka dashboard analitik penjualan
- **WHEN** pemilik kedai (owner) mengakses halaman ringkasan bisnis di dashboard admin
- **THEN** sistem menampilkan kartu metrik total omzet hari ini (misal: Rp4.820.000), 184 transaksi, rasio 88% QRIS vs 12% Tunai, grafik penjualan per jam, dan daftar menu terlaris

---

### Requirement: Rekonsiliasi Kas Harian dan Pencegahan Selisih Fisik
Sistem SHALL menyediakan buku catatan mutasi kasir dan penutupan shift (*Cash Reconciliation / Settlement*) harian yang mencatat modal awal laci (*starting float*), total penjualan tunai tercatat, total penjualan QRIS, input uang fisik hasil hitung kasir pada akhir hari, serta mendeteksi selisih secara otomatis.

#### Scenario: Kasir melakukan tutup kasir akhir shift
- **WHEN** kasir mengakhiri shift harian dan memasukkan jumlah uang fisik yang ada di laci kasir
- **THEN** sistem mencocokkan uang fisik dengan total penerimaan transaksi tunai sistem, memverifikasi selisih adalah Rp0 (klop), dan menyimpan log rekonsiliasi yang dapat diaudit oleh pemilik

---

### Requirement: Manajemen Katalog Menu Dinamis dan Toggle Ketersediaan Stok
Sistem SHALL menyediakan modul pengelolaan katalog menu (tambah, ubah, hapus), pengaturan harga, pengelompokan kategori, penetapan tingkat kepedasan, penandaan status menu terlaris (*bestseller*), serta tombol sakelar ketersediaan stok (*stock toggle*) 1-klik yang langsung memperbarui ketersediaan menu di ponsel pelanggan.

#### Scenario: Admin menonaktifkan menu yang stok bahannya habis
- **WHEN** staf dapur melaporkan stok Udang Keju habis dan admin menggeser toggle ketersediaan menjadi "Habis"
- **THEN** sistem secara instan menampilkan label "Habis" pada item Udang Keju di katalog digital pelanggan dan menonaktifkan tombol pemesanan item tersebut

---

### Requirement: Generator QR Code Meja Terenkripsi dan Manajemen Perangkat POS
Sistem SHALL menyediakan modul konfigurasi nomor meja kedai dengan kemampuan membuat token kriptografi aman untuk setiap meja, mencetak lembar QR Code meja siap tempel, serta mengatur preferensi perangkat keras kasir dan printer dapur (tipe printer thermal 58mm atau 80mm).

#### Scenario: Admin mencetak QR Code untuk meja baru
- **WHEN** admin menambahkan meja baru (misal: Meja 13) dan menekan tombol "Generate & Unduh QR Meja"
- **THEN** sistem menghasilkan berkas QR Code siap cetak dengan tautan terenkripsi unik yang terhubung secara spesifik ke Meja 13

