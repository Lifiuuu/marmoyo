# Marmoyo Smart-Dine: Sistem POS & Self-Ordering Cerdas Berbasis AI

Sistem kasir (POS) dan pemesanan mandiri (*self-ordering*) terintegrasi yang dirancang untuk mengeliminasi antrean kasir, mempercepat perputaran meja (*turnaround*), dan mengotomasi pencatatan keuangan kedai secara real-time (dikembangkan berdasarkan studi *Design Thinking Kelompok 5*, Universitas Airlangga).

Aplikasi dibangun menggunakan framework **Laravel 12 (PHP 8.2+)** dengan arsitektur bersih berstandar industri (*enterprise clean architecture*), basis data cloud **Supabase PostgreSQL**, serta siap di-deploy secara *serverless* ke **Vercel** dengan pipeline CI/CD **GitHub Actions**.

---

## 👥 Tiga Aktor & Modul Utama Sistem

### 1. Pelanggan (Mobile Web Self-Ordering via QR Meja)
- **Akses Instan Tanpa Unduh Aplikasi**: Memindai stiker QR Code meja (URL bertanda tangan kriptografis) untuk langsung membuka katalog Meja 01 s/d Meja 12.
- **Katalog Menu & Info Tingkat Pedas**: Penandaan cabai eksplisit (*Level 1 Santai*, *Level 2 Sedang*, *Level 3 Pedas Nampol*, *Level 4-5 Super Pedas Rawit Setan*), jenis mie (Keriting/Lebar), ekstra topping, dan catatan khusus pesanan.
- **Smart-Dine AI Engine**: Rekomendasi otomatis minuman dingin pereda pedas (*Es Teh Manis Jumbo*, *Es Jeruk Peras*) dan dimsum penetral (*Udang Keju*, *Dimsum Pangsit*) jika pelanggan memilih menu pedas tinggi.
- **Badge Bestseller Otomatis**: Label *BESTSELLER* dihitung 100% otomatis berdasarkan data volume penjualan riil tertinggi per kategori menu.
- **Dua Metode Pembayaran**:
  - **QRIS Instan**: QR dinamis EMVCo dengan batas waktu 5 menit dan verifikasi otomatis langsung masuk ke antrean kedai.
  - **Bayar Tunai di Kasir**: Menerbitkan kode antrean transaksi `TUNAI-xxxx` ber-barcode. Pesanan ditahan hingga divalidasi kasir.
- **Live Tracking & Struk Digital**: Memantau pengerjaan dapur (estimasi 10–15 menit), unduh struk digital, dan tombol darurat panggil pelayan.

### 2. Kasir / Staf Kedai (POS Terminal)
- **Live Order Stream**: Memantau antrean pesanan aktif kedai (*Pesanan Masuk*, *Sedang Dimasak*, *Siap Saji*) lengkap dengan penghitung durasi tunggu.
- **Validasi Tunai Cepat (<15 Detik)**: Scan barcode menggunakan webcam / ketik kode `TUNAI-xxxx`, pilihan tombol nominal cepat (Uang Pas, Rp20rb, Rp50rb, Rp100rb), kalkulator kembalian otomatis, dan konfirmasi 1-klik.
- **Input Pesanan Take Away (Bungkus)**: Modul khusus kasir (`/cashier/take-away`) untuk melayani pelanggan bungkus/bawa pulang dengan kalkulator biaya kemasan (*packaging fee*) otomatis dan dukungan pembayaran tunai/QRIS.
- **Format Cetak Struk Kasir Thermal ESC/POS (58mm/80mm)**: Satu tombol cetak struk resmi lengkap dengan identitas kedai, rincian pesanan, PB1, biaya bungkus, dan pesan kaki penutup.
- **Rekonsiliasi Kas / Tutup Shift**: Pencocokan uang fisik kasir dengan catatan penerimaan sistem (target selisih: **Rp0**).

### 3. Admin / Pemilik Kedai (Owner Executive Dashboard)
- **Executive Analytics**: Metrik omzet harian-bulanan, total transaksi, rating kepuasan (9.4/10), dan breakdown digital vs tunai (88% QRIS vs 12% Tunai).
- **Grafik Tren Penjualan Per Jam**: Analisis jam sibuk sore-malam (*peak rush hours 17.00 - 21.00 WIB*).
- **Buku Mutasi Kas & Audit Selisih**: Laporan rekapitulasi rekonsiliasi kasir harian untuk audit uang fisik vs pencatatan sistem.
- **Katalog Menu Dinamis & Sakelar Stok**: CRUD menu, penentuan harga, level pedas, dan *1-click stock toggle* untuk mengubah menu menjadi "Habis" secara instan.
- **Generator QR Code Meja**: Tambah nomor meja kedai dan cetak lembar stiker QR Code meja siap tempel A4.
- **Kelola Staf / Pengguna**: Manajemen pengguna internal untuk menambah staf kasir baru, memperbarui peran, dan reset kata sandi (dilengkapi proteksi anti-hapus akun sendiri & anti-hapus admin terakhir).
- **Pengaturan Kedai & Format Struk**: Kelola identitas usaha (Nama Kedai, Tagline, Alamat, Kontak WA, Pesan Kaki Struk), tarif biaya bungkus (Take Away), persentase pajak PB1 (10%), diskon promo, serta **Simulator Struk Thermal 58mm Live Preview**.

---

## 🔑 Akun Bawaan (Default Credentials)

| Peran | Alamat Email | Kata Sandi | Halaman Utama |
| :--- | :--- | :--- | :--- |
| **Admin (Owner)** | `owner@marmoyo.com` | `password123` | `/admin/dashboard` |
| **Kasir (Staf)** | `kasir@marmoyo.com` | `password123` | `/cashier/live-orders` |
| **Pelanggan Meja 08** | *(Tanpa Login / Scan QR Meja)* | - | `/menu` |

---

## 🚀 Panduan Menjalankan Sistem Secara Lokal

### Kebutuhan Sistem
- PHP >= 8.2 (dengan ekstensi `pdo_pgsql` atau `pdo_sqlite`)
- Composer
- Node.js >= 18 & npm

### Langkah Instalasi
1. **Clone Repository & Masuk Direktori**:
   ```bash
   git clone https://github.com/Lifiuuu/marmoyo.git
   cd marmoyo
   ```

2. **Salin File Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Install Dependensi PHP & Frontend**:
   ```bash
   composer install
   npm install
   npm run build
   ```

4. **Jalankan Migrasi Database & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di: `http://127.0.0.1:8000`

---

## ☁️ Deployment Serverless ke Vercel & CI/CD Pipeline

Proyek telah dioptimalkan khusus untuk lingkungan **Vercel Serverless**:
- **Entrypoint Serverless (`api/index.php`)**: Mengalihkan seluruh direktori penyimpanan dan kompilasi Blade view ke `/tmp/storage` yang *writable*.
- **Relokasi Cache Bootstrap**: Konfigurasi `APP_PACKAGES_CACHE`, `APP_SERVICES_CACHE`, `APP_CONFIG_CACHE`, dan `APP_ROUTES_CACHE` diarahkan ke `/tmp` guna mencegah error filesystem read-only.
- **Reverse Proxy Trust**: `$middleware->trustProxies(at: '*')` aktif di `bootstrap/app.php` sehingga protokol HTTPS dan domain Vercel terdeteksi otomatis pada pembuatan QR Code meja.
- **GitHub Actions (`.github/workflows/deploy.yml`)**: Menjalankan pengujian otomatis setiap kali ada `git push` ke branch `main`, dan otomatis melakukan deployment produksi ke Vercel jika semua test lulus.

### Environment Variables di Vercel Dashboard
Tambahkan variabel berikut pada menu **Project Settings > Environment Variables** di Vercel:

```env
APP_NAME="Marmoyo Smart-Dine"
APP_ENV=production
APP_KEY=base64:NG+khS82AflhQf6IDZJIJ1/kb7bblafu0taUhx10cnA=
APP_DEBUG=false
APP_URL=https://marmoyo-smart-dine.vercel.app

LOG_CHANNEL=stderr
LOG_LEVEL=error

# Supabase Cloud Database Pooler
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.ptkqtntsxwfarbhdvcmz
DB_PASSWORD=marmoyo2026
DB_SSLMODE=prefer

# Supabase API & Storage
SUPABASE_URL=https://ptkqtntsxwfarbhdvcmz.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_STORAGE_BUCKET=marmoyo-assets

# Session & Cache Terdistribusi
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
FILESYSTEM_DISK=public
```

---

## 🧪 Pengujian Otomatis (Automated Test Suite)

Sistem dilengkapi automated test menyeluruh yang memvalidasi integritas alur bisnis:
```bash
php artisan test
```

### Cakupan Pengujian (14 Test Cases, 109 Assertions):
1. **Self-Ordering Pelanggan**: Scan QR meja valid, pemilihan menu, penambahan keranjang dengan level pedas & topping.
2. **Pembayaran Digital & Tunai**: Pembuatan QRIS dinamis, verifikasi instan, penerbitan kode tunai, dan validasi kasir.
3. **POS Kasir Operasional**: Pemantauan antrean pesanan, sakelar ketersediaan stok menu, dan pemrosesan pesanan Take Away (bungkus).
4. **Keamanan & Peran (RBAC)**: Proteksi akses route admin dari kasir, hak akses tutup shift / rekonsiliasi kas.
5. **Manajemen Staf**: CRUD staf kasir internal, proteksi anti-hapus diri sendiri, dan proteksi admin terakhir.
6. **Pengaturan Kedai & Format Struk**: Pembaruan identitas kedai, penyesuaian biaya kemasan, pajak PB1, dan footer struk thermal.
