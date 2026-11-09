# Proposal: Marmoyo Smart-Dine System

## Why

Kedai Mie Marmoyo mengalami masalah antrean kasir membludak terutama pada jam sibuk (sore hingga malam) dengan waktu tunggu hingga 30 menit, kapasitas kedai yang sempit menjadi sesak, risiko human error pencatatan manual pesanan dan rekap omzet harian, serta tingginya angka calon pelanggan yang batal membeli. 

Implementasi sistem self-ordering web berbasis QR Code meja dengan rekomendasi AI, integrasi pembayaran ganda (QRIS otomatis dan tunai tervalidasi kasir), otomasi cetak dapur, serta dashboard rekap omzet otomatis untuk kasir dan pemilik (admin) sangat mendesak untuk memangkas waktu tunggu, menghilangkan bottleneck di kasir, mencegah pesanan terlewat, dan memastikan keselarasan pembukuan uang fisik dengan laporan omzet.

## What Changes

Membangun sistem web fullstack enterprise-grade "Marmoyo Smart-Dine" menggunakan framework Laravel yang dirancang siap integrasi Supabase (PostgreSQL, Supabase Storage untuk media aset, Supabase Realtime / Webhook untuk event pesanan dapur):

- **Pelanggan (Guest via Meja QR Code)**:
  - Akses web app instan tanpa unduh aplikasi melalui QR Code meja berenkripsi token.
  - Navigasi katalog menu interaktif dengan filter kategori, badge tingkat kepedasan, dan kustomisasi menu (level pedas 1-5, jenis mie keriting/lebar, ekstra topping, catatan koki).
  - AI Smart Recommendation & Upselling (menu terlaris, rekomendasi pendamping pereda pedas/es teh jumbo, bundling dimsum).
  - Manajemen keranjang belanja, voucher diskon (e.g. MAKANHEMAT), rincian pajak resto (PB1 10%), dan bebas biaya layanan.
  - Metode pembayaran QRIS dinamis dengan countdown timer & verifikasi otomatis, atau opsi Bayar Tunai di Kasir dengan penerbitan kode antrean transaksi (`TUNAI-xxxx`) ber-barcode/QR.
  - Live Tracking status pesanan (*Menunggu Bayar* -> *Diteruskan ke Dapur/Live Cook* -> *Siap Saji* -> *Selesai*), unduh struk digital, dan fitur tombol darurat panggil pelayan meja.

- **Kasir (Staf Kedai)**:
  - Layar POS & Live Order Queue terpusat untuk memantau pesanan masuk secara real-time.
  - Panel validasi pembayaran tunai kilat: scan barcode / ketik kode `TUNAI-xxxx`, quick button nominal uang diterima, kalkulator kembalian otomatis, dan validasi 1-klik yang otomatis mengubah status serta meneruskan pesanan ke dapur.
  - Dispatch cetak otomatis pesanan ke printer thermal dapur (format ESC/POS Kitchen Ticket) dan cetak struk kasir.
  - Pembaruan status pesanan real-time ke pelanggan (*menunggu* -> *selesai*).

- **Pemilik (Admin / Owner)**:
  - Dashboard eksekutif analitik bisnis: metrik omzet harian/mingguan/bulanan, total transaksi, rata-rata rating, rasio pembayaran QRIS vs Tunai.
  - Grafik tren penjualan per jam (peak hours sore-malam) dan peringkat menu terlaris (*Top Menu*).
  - Laporan rekonsiliasi kas harian (mencocokkan penerimaan fisik kasir dengan pencatatan sistem tanpa selisih).
  - Manajemen katalog menu dinamis (CRUD menu, level pedas, kustomisasi varian, topping, foto menu, dan toggle stok real-time).
  - Generator QR Code Meja (manajemen nomor meja, generate signed token meja, unduh batch QR meja).
  - Pengaturan perangkat POS, Kitchen Display System (KDS), settlement kasir, dan konfigurasi integrasi Supabase.

- **Fondasi Arsitektur Backend & Database**:
  - Konfigurasi arsitektur Laravel clean architecture (Actions, Services, Data Transfer Objects, Form Requests).
  - Database schema siap PostgreSQL / Supabase, seeder menu otentik sesuai dokumen SRS Marmoyo, migration lengkap, dan adapter Supabase Service Layer (DB, Auth, Storage, Realtime).

## Capabilities

### New Capabilities
- `customer-self-ordering`: Antarmuka pelanggan berbasis web QR Code meja, penjelajahan katalog menu dengan penanda tingkat pedas & kustomisasi varian, keranjang belanja, AI recommendation engine (cross-selling & menu terlaris), live tracking status pesanan, dan tombol panggil pelayan.
- `payment-and-billing`: Alur pembayaran ganda mencakup simulasi/integrasi QRIS dinamis dengan countdown verifikasi otomatis, alur pembayaran tunai berbasis token `TUNAI-xxxx`, perhitungan pajak restoran PB1 10%, diskon promo, dan struk digital.
- `kitchen-pos-management`: Dashboard POS kasir, live order stream, pemindai/validasi tunai kilat dengan kalkulator kembalian, pembaruan status pengerjaan koki, dan dispatch perintah cetak thermal dapur (ESC/POS).
- `admin-analytics-and-menu`: Dashboard analitik eksekutif pemilik, visualisasi tren omzet per jam & rasio metode pembayaran, mutasi rekonsiliasi kas fisik, CRUD katalog menu & toggle stok, generator QR code meja ber-token, serta pengaturan perangkat POS/KDS.
- `auth-and-access-control`: Sistem otentikasi dan otorisasi multi-role (Admin/Owner, Kasir, dan Pelanggan/Meja Session) dengan role middleware, audit trail aktivitas transaksi, dan struktur adapter siap Supabase.

### Modified Capabilities

<!-- None: Inisiasi greenfield proyek Marmoyo Smart-Dine -->

## Impact

- **Backend**: Aplikasi Laravel (PHP 8.2+) dengan struktur database teroptimasi PostgreSQL (Supabase compatible), Service/Action pattern, dan event broadcasting untuk notifikasi real-time.
- **Frontend**: Responsive UI (Mobile-first untuk Pelanggan Meja, Tablet/Desktop optimized untuk POS Kasir & Admin Dashboard) dengan Tailwind CSS & interactive reactive components.
- **Database & Supabase**: Schema tabel relasional PostgreSQL (users, roles, tables, categories, menus, menu_variants, menu_toppings, orders, order_items, payments, ai_recommendation_logs, cash_reconciliations). Penyediaan environment Supabase credentials (`SUPABASE_URL`, `SUPABASE_KEY`, `SUPABASE_DB_HOST`).
- **Hardware Integration**: Dukungan modul cetak printer thermal (Kitchen Ticket & Struk Pelanggan) melalui Web ESC/POS API atau kitchen display endpoint.
- **Operasional Kedai**: Mengeliminasi bottleneck kasir, mempercepat turnaround meja, dan menjamin kesesuaian uang kasir dengan laporan harian.

