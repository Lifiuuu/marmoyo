# Marmoyo Smart-Dine: Sistem Self-Ordering Berbasis AI Kedai Mie Marmoyo

Sistem pemesanan mandiri (*self-ordering*) berbasis web responsif yang dirancang untuk mengeliminasi antrean kasir, mempercepat turnaround meja, dan mengotomasi pencatatan keuangan di **Kedai Mie Marmoyo** (berdasarkan studi *Design Thinking Kelompok 5*, D3/Sarjana Terapan Teknik Informatika, Universitas Airlangga).

Aplikasi dibangun menggunakan framework **Laravel 12 (PHP 8.2+)** dengan arsitektur bersih berstandar industri (*enterprise clean architecture*), siap integrasi penuh ke **Supabase** (PostgreSQL, Supabase Storage, & Supabase Realtime).

---

## 👥 Tiga Aktor / Peran Pengguna (Sesuai SRS)

### 1. Pelanggan (Mobile Web via QR Meja)
- **Akses Cepat Tanpa Unduh Aplikasi**: Memindai stiker QR Code meja (URL bertanda tangan kriptografis) untuk langsung membuka katalog Meja 01 s/d Meja 12.
- **Katalog Menu & Info Tingkat Pedas**: Penandaan cabai eksplisit (*Level 1 Santai*, *Level 2 Sedang*, *Level 3 Pedas Nampol*, *Level 4-5 Super Pedas Rawit Setan*), jenis mie (Keriting/Lebar), ekstra topping kenyang, dan catatan koki (*"Jangan terlalu berminyak"*).
- **Smart-Dine AI Engine**: Rekomendasi otomatis minuman dingin pereda pedas (*Es Teh Manis Jumbo*, *Es Jeruk Peras*) dan dimsum penetral (*Udang Keju*, *Dimsum Pangsit*) jika pelanggan memilih menu pedas tinggi.
- **Dua Metode Pembayaran**:
  - **QRIS Instan**: QR dinamis dengan batas waktu 5 menit dan verifikasi otomatis langsung masuk ke dapur.
  - **Bayar Tunai di Kasir**: Menerbitkan kode antrean transaksi `TUNAI-xxxx` ber-barcode. Pesanan ditahan hingga divalidasi kasir.
- **Live Tracking & Struk Digital**: Memantau pengerjaan dapur (*Live Cook* estimasi 10-15 menit), unduh struk digital, dan tombol darurat panggil pelayan meja.

### 2. Kasir / Staf Kedai (POS Terminal)
- **Live Order Stream**: Memantau antrean pesanan aktif kedai (*Pesanan Masuk*, *Sedang Dimasak*, *Siap Saji*) lengkap dengan timer durasi tunggu.
- **Validasi Tunai Cepat (<15 Detik)**: Scan barcode / ketik kode `TUNAI-xxxx`, pilihan tombol nominal cepat (Uang Pas, Rp20rb, Rp50rb, Rp100rb), kalkulator kembalian otomatis, dan konfirmasi 1-klik yang langsung mengirim tiket ke dapur.
- **Format Cetak Thermal ESC/POS (58mm/80mm)**: Pratinjau dan cetak tiket dapur dengan nomor meja tegas dan catatan koki tebal.
- **Rekonsiliasi Kas / Tutup Shift**: Pencocokan uang fisik kasir dengan catatan penerimaan sistem (target selisih: **Rp0**).

### 3. Admin / Pemilik Kedai (Owner Dashboard)
- **Executive Analytics**: Metrik omzet harian-bulanan, total transaksi, rating kepuasan (9.4/10), breakdown digital vs tunai (88% QRIS vs 12% Tunai).
- **Grafik Tren Penjualan Per Jam**: Analisis jam sibuk sore-malam (*peak rush hours 17.00 - 21.00 WIB*).
- **Buku Mutasi Kas & Audit Selisih**: Laporan rekonsiliasi kasir harian untuk audit uang fisik vs sistem.
- **Katalog Menu Dinamis & Sakelar Stok**: CRUD menu, penentuan harga, level pedas, dan *1-click stock toggle* untuk mengubah menu menjadi "Habis" secara instan.
- **Generator QR Code Meja**: Tambah nomor meja kedai dan cetak lembar stiker QR Code meja siap pakai.
- **Pengaturan Hardware**: Konfigurasi ukuran printer thermal dapur (58mm atau 80mm) dan status koneksi Supabase.

---

## 🚀 Panduan Menjalankan Sistem Secara Lokal

### Kebutuhan Sistem
- PHP >= 8.2 (dengan ekstensi pdo_sqlite / pdo_pgsql)
- Composer
- Node.js & npm

### Langkah Menjalankan
1. **Clone / Buka Direktori**:
   ```bash
   cd d:/laragon/www/marmoyo
   ```

2. **Jalankan Migrasi & Seeder Menu Otentik**:
   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Build Aset Frontend**:
   ```bash
   npm run build
   ```

4. **Jalankan Server Laravel**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di: `http://localhost:8000`

---

## 🔑 Akun Bawaan (Default Credentials)

| Peran | Email | Kata Sandi | Halaman Utama |
| :--- | :--- | :--- | :--- |
| **Admin (Owner Wasis)** | `owner@marmoyo.com` | `password123` | `http://localhost:8000/admin/dashboard` |
| **Kasir (Staf Najwa)** | `kasir@marmoyo.com` | `password123` | `http://localhost:8000/cashier/live-orders` |
| **Pelanggan (Meja 08)** | *(Tanpa Login / Scan Meja)* | - | `http://localhost:8000/menu` |

> **Catatan Praktis**: Halaman login (`/login`) juga menyediakan tombol jalan pintas **"Akses Instan Demo (1-Klik)"** untuk masuk sebagai Owner atau Kasir tanpa perlu mengetik ulang kata sandi.

---

## ⚡ Panduan Aktivasi Integrasi Supabase Cloud

Sistem dirancang modular dengan abstraksi `App\Services\Supabase\SupabaseClient` dan `SupabaseStorageService`. Ketika Anda siap menghubungkan proyek ke Supabase, lakukan 3 langkah mudah berikut:

### 1. Buat Proyek Supabase
- Buka [supabase.com](https://supabase.com) dan buat proyek baru.
- Buat sebuah Public Bucket di menu **Storage** dengan nama: `marmoyo-assets`.

### 2. Isi Kredensial di File `.env`
Buka file `.env` dan perbarui nilai berikut sesuai project dashboard Supabase Anda:
```env
# Ganti koneksi database dari sqlite ke pgsql Supabase
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.[YOUR_PROJECT_ID]
DB_PASSWORD=[YOUR_SUPABASE_DB_PASSWORD]

# Kredensial API & Storage Supabase
SUPABASE_URL=https://[YOUR_PROJECT_ID].supabase.co
SUPABASE_ANON_KEY=[YOUR_ANON_KEY]
SUPABASE_SERVICE_ROLE_KEY=[YOUR_SERVICE_ROLE_KEY]
SUPABASE_STORAGE_BUCKET=marmoyo-assets
```

### 3. Migrasi Skema ke Supabase PostgreSQL
Jalankan perintah berikut di terminal:
```bash
php artisan migrate --seed
```
Seluruh tabel, indeks foreign key, akun pengguna, 12 meja kedai, serta katalog menu otentik Kedai Mie Marmoyo akan langsung terpasang di database cloud Supabase Anda!

---

## 🧪 Pengujian Otomatis (Test Suite)

Untuk menjalankan automated feature test end-to-end:
```bash
php artisan test --filter=MarmoyoSmartDineTest
```
Pengujian memverifikasi alur:
1. Scan QR Meja & Buka Menu
2. Kustomisasi Level Pedas & Penambahan ke Keranjang
3. Checkout & Auto-Verifikasi QRIS Dinamis
4. Validasi Uang Tunai di Kasir POS dengan Kalkulator Kembalian
5. Aksesibilitas Executive Dashboard Pemilik
