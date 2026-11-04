# Implementation Tasks: Marmoyo Smart-Dine System

## 1. Setup Kerangka Kerja & Arsitektur Supabase-Ready

- [x] 1.1 Inisialisasi struktur proyek Laravel 11/12 dengan konfigurasi database PostgreSQL (kompatibel Supabase)
- [x] 1.2 Buat file konfigurasi `config/supabase.php` dan service abstraction `App\Services\Supabase\SupabaseClient` (DB, Storage, Auth, Realtime)
- [x] 1.3 Setup Tailwind CSS, komponen UI mobile-first untuk Pelanggan Meja, serta layout responsif untuk POS Kasir dan Admin

## 2. Database Schema, Migrations, and Authentic Seeders

- [x] 2.1 Buat migrasi tabel `users` dengan field role (`admin`, `kasir`), password terenkripsi, dan seeder akun kasir & owner
- [x] 2.2 Buat migrasi tabel `restaurant_tables` (id, table_number, qr_token unik, status meja) dan seeder Meja 01 s/d Meja 12
- [x] 2.3 Buat migrasi tabel `categories`, `menus`, `menu_variants`, dan `menu_toppings` dengan indeks performa
- [x] 2.4 Buat database seeder katalog menu otentik Kedai Mie Marmoyo (Mie Kuah Mala, Mie Charsiu, Mie Tomyam, Mie Gurih Level 1-5, Udang Keju, Dimsum Pangsit, Es Teh Manis Jumbo, Ceker Pedas)
- [x] 2.5 Buat migrasi tabel `orders`, `order_items`, dan `payments` dengan relasi foreign key terintegritas
- [x] 2.6 Buat migrasi tabel `cash_reconciliations` (rekap uang fisik kasir) dan `service_calls` (panggilan pelayan meja)

## 3. Core Domain Services, Business Actions, and AI Engine

- [x] 3.1 Implementasi `TableSessionService` untuk validasi tanda tangan kriptografi QR token meja dan manajemen sesi pelanggan
- [x] 3.2 Implementasi `AiMenuRecommendationService` untuk smart pairing menu pedas dengan minuman dingin dan rekomendasi menu terlaris
- [x] 3.3 Implementasi `CreateOrderAction` yang menghitung subtotal, varian level pedas, topping ekstra, voucher diskon "MAKANHEMAT", dan pajak PB1 10%
- [x] 3.4 Implementasi `QrisPaymentService` dengan generator payload dinamis, timer countdown kedaluwarsa 5 menit, dan handler webhook
- [x] 3.5 Implementasi `ValidateCashPaymentAction` dengan kalkulator kembalian otomatis dan konfirmasi penerimaan kasir
- [x] 3.6 Implementasi `KitchenPrintFormatter` untuk memformat tiket dapur printer thermal berstandar ESC/POS (58mm dan 80mm)
- [x] 3.7 Implementasi `SupabaseStorageService` dengan fallback local disk untuk manajemen berkas foto menu dan arsip struk digital

## 4. Antarmuka Pelanggan (Customer Mobile Web via QR Code)

- [x] 4.1 Buat rute dan halaman landing self-ordering meja berbasis QR token validation
- [x] 4.2 Buat antarmuka katalog menu interaktif dengan navigasi kategori, foto resolusi tinggi, dan badge level pedas
- [x] 4.3 Buat modal detail menu interaktif: pilihan level kepedasan (Level 1-5), pilihan jenis mie, ekstra topping, dan catatan khusus koki
- [x] 4.4 Buat banner dinamis "Rekomendasi Spesial AI" di keranjang pesanan dengan tombol 1-klik tambah item pereda pedas
- [x] 4.5 Buat antarmuka keranjang belanja (*Cart*) dengan rincian biaya transparan (subtotal, diskon voucher, pajak PB1, bebas biaya layanan)
- [x] 4.6 Buat antarmuka pembayaran QRIS dengan timer countdown, QR dinamis, tombol simpan QR, dan tombol verifikasi pembayaran
- [x] 4.7 Buat antarmuka pembayaran tunai dengan penerbitan kode transaksi `TUNAI-xxxx`, barcode/QR, dan instruksi 3 langkah bayar di kasir
- [x] 4.8 Buat antarmuka pelacakan status pesanan real-time (*Live Tracking* Dapur), unduh struk digital, dan tombol panggil pelayan meja

## 5. Antarmuka POS Kasir & Live Order Board

- [x] 5.1 Buat antarmuka Live Order Queue di kasir dengan pembagian kolom status (Pesanan Masuk, Sedang Dimasak, Siap Saji) dan counter omzet
- [x] 5.2 Buat modal scan / input manual kode antrean transaksi `TUNAI-xxxx`
- [x] 5.3 Implementasi panel pembayaran kasir dengan kalkulator kembalian otomatis dan tombol nominal cepat (Uang Pas, Rp20.000, Rp50.000, Rp100.000)
- [x] 5.4 Buat aksi konfirmasi pembayaran tunai yang secara instan meneruskan pesanan ke antrean dapur dan memicu cetak struk
- [x] 5.5 Buat fitur pembaruan status pengerjaan pesanan (*cooking* -> *ready* -> *served*) serta penanganan notifikasi panggilan meja
- [x] 5.6 Buat utilitas pratinjau dan cetak tiket dapur thermal ESC/POS

## 6. Antarmuka Admin & Owner Executive Dashboard

- [x] 6.1 Buat halaman ringkasan eksekutif bisnis: metrik omzet hari ini, total transaksi, rating kepuasan, dan rasio pembayaran (QRIS vs Tunai)
- [x] 6.2 Buat grafik tren volume transaksi per jam operasional untuk analisis jam sibuk sore-malam (17.00 - 21.00 WIB)
- [x] 6.3 Buat modul rekonsiliasi kas harian (*cash closing ledger*) untuk pencocokan fisik uang laci dengan laporan sistem (target selisih Rp0)
- [x] 6.4 Buat modul pengelolaan katalog menu (CRUD menu, kategori, level pedas, kustomisasi varian) dan sakelar toggle stok habis/tersedia
- [x] 6.5 Buat modul konfigurasi meja kedai dan generator QR Code meja ber-token siap cetak
- [x] 6.6 Buat halaman pengaturan perangkat keras POS, printer dapur (58mm/80mm), dan antarmuka konfigurasi koneksi Supabase

## 7. Kontrol Akses Multi-Role, Security, and Final Verification

- [x] 7.1 Implementasi middleware proteksi rute untuk 3 peran (Admin, Kasir, Pelanggan Meja)
- [x] 7.2 Implementasi audit trail untuk setiap aksi konfirmasi pembayaran kasir dan penutupan shift
- [x] 7.3 Uji coba skenario end-to-end: Scan Meja -> Kustomisasi Pedas -> Rekomendasi AI -> QRIS / Tunai Kasir -> Cetak Dapur -> Rekap Omzet
- [x] 7.4 Siapkan panduan konfigurasi lingkungan Supabase (`SUPABASE_URL`, `SUPABASE_KEY`, migrasi PostgreSQL) di file README/.env.example

