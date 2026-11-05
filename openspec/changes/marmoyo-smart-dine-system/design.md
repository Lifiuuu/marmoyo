# Technical Design: Marmoyo Smart-Dine System

## Context

Kedai Mie Marmoyo adalah unit UMKM kuliner yang menghadapi lonjakan antrean fisik pada jam makan sibuk (17.00 - 21.00 WIB) dengan waktu tunggu pelanggan hingga 30 menit. Berdasarkan studi *Design Thinking Kelompok 5*, antrean panjang di depan meja kasir menjadi *single point of failure* yang mempersempit ruangan, memicu komplain kesalahan pesanan, menurunkan omzet karena pelanggan batal membeli, dan menyulitkan rekapitulasi keuangan kasir setiap malam.

Proyek ini membangun arsitektur sistem penuh **Marmoyo Smart-Dine** berbasis Laravel (PHP 8.2+) dengan kesiapan penuh integrasi ekosistem Supabase (PostgreSQL, Supabase Storage untuk media aset, Supabase Realtime/Webhook untuk sinkronisasi pesanan dapur dan status pelanggan).

Sistem melayani 3 aktor utama:
1. **Pelanggan**: Mengakses web aplikasi secara mandiri (*self-ordering*) melalui scan QR Code di meja tanpa registrasi/unduh aplikasi, memesan dengan bantuan rekomendasi AI, memilih pembayaran QRIS atau Tunai, serta melacak status pesanan secara *live*.
2. **Kasir / Staf Kedai**: Mengoperasikan POS kasir responsif untuk memvalidasi pembayaran tunai kilat, mencetak struk kasir & tiket dapur, serta memantau antrean pesanan aktif (*Live Cook*).
3. **Admin / Pemilik Kedai (Owner)**: Mengakses dashboard analitik penjualan (omzet harian-bulanan, rasio pembayaran, tren jam sibuk), rekapitulasi mutasi kas harian tanpa selisih, mengelola menu & varian stok, serta mencetak QR Code meja berenkripsi.

```mermaid
graph TD
    subgraph Pelanggan ["Pelanggan (Mobile Web via QR Meja)"]
        A[Scan QR Meja] --> B[Katalog Menu & AI Recommendation]
        B --> C[Keranjang & Customization Level Pedas]
        C --> D{Pilih Metode Bayar}
        D -->|QRIS Dinamis| E[QRIS Instant + Auto Verification]
        D -->|Tunai| F[Kode Bayar TUNAI-xxxx + Barcode]
        E --> G[Live Tracking Pesanan & Struk Digital]
        F --> G
    end

    subgraph Kasir ["Kasir / Staf (POS & Live Order)"]
        F -.->|Pelanggan Tunjukkan Kode| H[Scan Barcode / Input Kode Tunai]
        H --> I[Validasi Tunai + Kalkulator Kembalian]
        I --> J[Kirim Pesanan ke Dapur & Cetak Struk]
        G <.-.->|Update Status Menunggu / Selesai| K[Live Order Queue Board]
    end

    subgraph Dapur ["Dapur & Koki"]
        J --> L[Printer Thermal ESC/POS & Kitchen Display]
        L --> M[Masak Makanan Sesuai Catatan & Level Pedas]
    end

    subgraph Admin ["Admin / Owner (Executive Dashboard)"]
        N[Pantau Omzet & Laporan Real-Time]
        O[Rekonsiliasi Kas Tunai vs Sistem]
        P[Manajemen Menu & Stok Dinamis]
        Q[Generate QR Code Meja Terenkripsi]
    end

    subgraph CoreBackend ["Laravel Core Backend & Supabase Ready"]
        E --> R[Payment Service / Mock Gateway]
        I --> R
        R --> S[(Supabase PostgreSQL)]
        S --> T[Supabase Storage: Foto Menu & Struk]
        S --> U[Supabase Realtime / Event Stream]
        U -.-> K
        U -.-> G
    end
```

---

## Goals / Non-Goals

### Goals
- **Eliminasi Antrean Kasir**: Memindahkan 80%+ interaksi pemilihan menu dan pemesanan ke smartphone pelanggan melalui QR Code meja.
- **Dukungan Pembayaran Hibrida**: Menyediakan alur QRIS dinamis (verifikasi otomatis) dan alur pembayaran tunai yang aman (kasir memvalidasi kode antrean sebelum pesanan masuk antrean masak).
- **Akurasi & Integritas Pesanan Dapur**: Meneruskan rincian varian menu (level pedas 1-5, jenis mie keriting/lebar, ekstra topping, catatan koki) langsung ke tiket dapur thermal ESC/POS tanpa risiko salah catat.
- **AI Recommendation Engine Ringan**: Merekomendasikan menu terlaris (*best-sellers*) dan melakukan *cross-selling* otomatis pereda pedas (misalnya menyarankan Es Teh Jumbo / Dimsum Udang Keju saat pelanggan memilih mie level pedas tinggi).
- **Akuntansi Kasir Akurat**: Menghasilkan rekap kas harian (*cash drawer reconciliation*) yang mencocokkan uang fisik kasir dengan catatan transaksi tunai dan QRIS secara otomatis tanpa kalkulasi manual.
- **Kesiapan Supabase**: Seluruh skema database PostgreSQL, layer service Supabase (Auth, Storage, Realtime, DB connection pooling) disiapkan dengan struktur *modular & clean code* yang dapat diaktifkan hanya dengan mengisi kredensial `.env`.

### Non-Goals
- Aplikasi native Android/iOS (sistem diakses 100% via web browser responsif untuk menghindari friksi unduh aplikasi bagi pelanggan).
- Integrasi multi-cabang/multi-outlet (fokus MVP pada 1 outlet Kedai Mie Marmoyo).
- Sistem manajemen stok inventaris bahan mentah rumit / supplier procurement (hanya toggle ketersediaan menu habis/tersedia di level kasir & admin).
- Program kartu membership poin berbasis SMS OTP (cukup nama pelanggan opsional di keranjang pemesanan).

---

## Decisions

### 1. Arsitektur Framework & Kesiapan Supabase

- **Pilihan**: Laravel 11/12 dengan PostgreSQL connection layer (Supabase compatibility), arsitektur Clean Action-Domain-Responder (ADR) / Service Pattern.
- **Alasan**:
  - Laravel memiliki ekosistem ORM (Eloquent), Form Validation, Middleware, dan Database Migration yang sangat tangguh untuk menangani transaksi keuangan POS dan status pesanan.
  - Driver `pgsql` bawaan Laravel dapat langsung dihubungkan ke connection pooler Supabase (port 6543 / 5432) tanpa modifikasi kode.
  - Pengaturan disiapkan modular: `config/supabase.php` dan `App\Services\Supabase\SupabaseClient` mengelola Supabase Storage API (bucket foto menu dan arsip struk digital) serta REST/Realtime hooks. Pengguna dapat dengan mudah memasukkan kredensial Supabase (`SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY`) di kemudian hari.
- **Alternatif yang Dipertimbangkan**:
  - *Full Client-side Supabase SPA (React/Next.js langsung ke Supabase)*: Ditolak karena logika validasi diskon, perhitungan kembalian kasir, pencetakan ESC/POS thermal printer, dan rekonsiliasi kas lebih aman dikontrol secara terpusat di server (*backend authoritative*) untuk mencegah kecurangan harga atau manipulasi data di sisi klien.

### 2. Mekanisme Meja QR Code & Keamanan Sesi Pelanggan

- **Pilihan**: URL Meja bertanda tangan kriptografis (*Signed Token URL*), misalnya `https://kedai.marmoyo.com/order?table=08&token=c4ca4238a0b923820dcc...` yang disimpan dalam cookie sesi pelanggan yang aman.
- **Alasan**:
  - Mencegah pelanggan yang tidak berada di kedai membuat pesanan palsu dengan menebak nomor meja (`?table=08`).
  - Meja token dapat di-refresh atau di-generate ulang dari Admin Dashboard jika QR fisik di kedai rusak atau diganti.
  - Sesi meja mengisolasi pesanan per meja sehingga pelanggan dapat menambahkan pesanan (*add-on*) tanpa tertukar dengan meja lain.
- **Alternatif yang Dipertimbangkan**:
  - *Wajib registrasi akun Google / SMS OTP untuk pelanggan*: Ditolak karena hasil riset *Design Thinking* menunjukkan pelanggan ingin proses cepat tanpa friksi formulir login.

### 3. Alur Pembayaran Ganda (QRIS vs Tunai) & Pencegahan Kebocoran Kas

- **Pilihan**:
  1. **QRIS Dinamis**: Sistem men-generate payload QRIS dengan batas waktu kedaluwarsa (misalnya 5 menit). Saat pembayaran terverifikasi via webhook/callback, status pesanan otomatis berubah menjadi `menunggu` dan langsung terkirim ke dapur.
  2. **Tunai di Kasir**: Sistem menerbitkan kode antrean pembayaran berformat `TUNAI-xxxx` lengkap dengan QR/Barcode di layar pelanggan. Pesanan berstatus `menunggu` (unpaid) dan **TIDAK** dikirim ke antrean live order sampai kasir memindai/memvalidasi uang fisik di POS.
- **Alasan**:
  - Menjawab kekhawatiran utama owner (*Wasis*) dan kasir (*Najwa*) mengenai selisih uang fisik dan risiko pesanan palsu (*ghost orders*).
  - Kasir POS dilengkapi kalkulator kembalian instan dengan tombol nominal cepat (Rp20.000, Rp50.000, Rp100.000, Uang Pas) yang memangkas waktu pelayanan kasir dari 10-15 menit menjadi di bawah 15 detik.

### 4. Engine Rekomendasi Menu Berbasis AI (Smart-Dine AI Engine)

- **Pilihan**: Arsitektur hybrid yang menggabungkan *Association Rule Cross-Selling (Market Basket Analysis)* dan *Spiciness Pairing Logic*:
  1. *Spiciness Pairing*: Saat item dengan tingkat kepedasan tinggi (Level 3-5 atau Mie Kuah Mala) dimasukkan ke keranjang, sistem otomatis merekomendasikan minuman dingin pereda pedas (e.g. *Es Teh Manis Jumbo*, *Es Jeruk Peras*) dan camilan penetral (e.g. *Udang Keju Crispy*, *Dimsum Pangsit*).
  2. *Top Sellers / Popular Items*: Menandai menu favorit berdasarkan data agregasi transaksi nyata.
  3. Modul dirancang dengan interface `AiRecommendationEngineInterface` di Laravel, sehingga dapat bekerja secara lokal menggunakan algoritma aturan matriks asosiasi, dan siap dihubungkan ke model LLM eksternal (misalnya Gemini API) melalui satu konfigurasi provider.
- **Alasan**:
  - Memberikan kecepatan rendering sub-100ms di HP pelanggan tanpa latensi jaringan eksternal yang dapat memperlambat checkout saat jam sibuk kedai.

### 5. Arsitektur Cetak Printer Dapur & Struk Pelanggan

- **Pilihan**: Modul `KitchenPrintFormatter` yang memproduksi format teks thermal terstandarisasi ESC/POS (58mm dan 80mm).
- **Alasan**:
  - Dapur kedai mie membutuhkan tiket fisik dengan teks jelas: Nomor Meja berukuran besar, Nomor Pesanan, Item menu beserta Level Pedas dan Catatan Koki (contoh: "*Jangan terlalu berminyak*", "*Goreng garing*").
  - Sistem menyediakan dua opsi cetak:
    1. *Direct Web Print / Raw Print*: Menggunakan browser print dialog terformat khusus struk thermal.
    2. *Network ESC/POS Socket / Webhook Service*: Siap mengirim raw bytes ke IP printer thermal dapur secara otomatis saat status pesanan menjadi `paid`.

---

## Database Schema (PostgreSQL / Supabase Native)

```mermaid
erDiagram
    USERS ||--o{ CASH_RECONCILIATIONS : conducts
    RESTAURANT_TABLES ||--o{ ORDERS : hosts
    CATEGORIES ||--o{ MENUS : contains
    MENUS ||--o{ MENU_VARIANTS : has
    MENUS ||--o{ MENU_TOPPINGS : allows
    ORDERS ||--o{ ORDER_ITEMS : contains
    ORDERS ||--o| PAYMENTS : records
    RESTAURANT_TABLES ||--o{ SERVICE_CALLS : generates

    USERS {
        uuid id PK
        string name
        string email UK
        string password
        enum role "admin, kasir"
        timestamp created_at
    }

    RESTAURANT_TABLES {
        uuid id PK
        string table_number UK
        string qr_token UK
        enum status "available, occupied"
        timestamp created_at
    }

    CATEGORIES {
        uuid id PK
        string name
        string slug UK
        string icon
        integer sort_order
    }

    MENUS {
        uuid id PK
        uuid category_id FK
        string name
        string slug UK
        text description
        decimal base_price
        string image_url
        boolean is_spicy
        integer default_spiciness_level
        boolean is_available
        boolean is_bestseller
    }

    MENU_VARIANTS {
        uuid id PK
        uuid menu_id FK
        string group_name "Tingkat Kepedasan, Jenis Mie"
        string name "Level 1, Mie Keriting"
        decimal additional_price
    }

    MENU_TOPPINGS {
        uuid id PK
        string name "Ekstra Pangsit, Telur Mata Sapi"
        decimal price
        boolean is_available
    }

    ORDERS {
        uuid id PK
        string order_number UK
        uuid table_id FK
        string customer_name
        decimal subtotal
        decimal discount_amount
        decimal tax_amount
        decimal final_amount
        enum status "menunggu, selesai, cancelled"
        enum payment_status "unpaid, paid, expired"
        enum payment_method "qris, cash"
        uuid cashier_id FK
        text kitchen_notes
        timestamp created_at
    }

    ORDER_ITEMS {
        uuid id PK
        uuid order_id FK
        uuid menu_id FK
        string menu_name
        decimal unit_price
        integer quantity
        decimal subtotal_price
        string spice_level
        string noodle_type
        json toppings_selected
        text special_notes
    }

    PAYMENTS {
        uuid id PK
        uuid order_id FK UK
        string payment_code UK
        enum method "qris, cash"
        decimal amount
        decimal cash_received
        decimal cash_change
        string qris_payload
        enum status "pending, success, failed, expired"
        timestamp paid_at
    }

    CASH_RECONCILIATIONS {
        uuid id PK
        uuid cashier_id FK
        date shift_date
        decimal starting_cash
        decimal total_cash_sales
        decimal total_qris_sales
        decimal actual_physical_cash
        decimal cash_difference
        enum status "open, closed"
    }

    SERVICE_CALLS {
        uuid id PK
        uuid table_id FK
        enum call_type "assistance, bill, utensil"
        enum status "pending, resolved"
        timestamp created_at
    }
```

---

## Risks / Trade-offs

- **[Risk 1: Fluktuasi Koneksi Internet Pelanggan di Kedai]**
  - *Mitigasi*:
    - Sesuai data SRS, kedai Mie Marmoyo telah menyediakan fasilitas Wi-Fi pelanggan.
    - Web app dirancang super ringan (zero heavy bundles, pure responsive CSS/Tailwind) dengan *local storage persistence* untuk keranjang pesanan. Jika halaman di-refresh, isi keranjang dan nomor meja tidak hilang.
- **[Risk 2: Pelanggan Memesan Tunai Tapi Tidak Mau ke Kasir (Ghost Order)]**
  - *Mitigasi*:
    - Pesanan tunai tidak pernah dikirim ke printer dapur sampai kasir mengklik tombol "Validasi Pembayaran".
    - Jika dalam 15 menit kode `TUNAI-xxxx` tidak divalidasi kasir, sistem menandai pesanan sebagai `expired`/batal otomatis untuk membebaskan antrean meja.
- **[Risk 3: Koki Dapur Melewatkan Pesanan Saat Sedang Sibuk]**
  - *Mitigasi*:
    - Selain cetak tiket printer thermal otomatis, kasir dan koki memiliki tampilan *Kitchen Display System (KDS) / Live Order Board* dengan kartu pesanan yang berkedip dan memiliki indikator waktu tunggu (*timer countdown* hijau/kuning/merah).
- **[Risk 4: Ketergantungan Konfigurasi Supabase yang Baru Dilakukan di Akhir]**
  - *Mitigasi*:
    - Menggunakan standar Laravel Database Migration & Eloquent ORM murni yang 100% kompatibel dengan PostgreSQL Supabase dan SQLite/MySQL untuk pengujian lokal.
    - Semua interaksi spesifik Supabase diisolasi ke dalam Service Abstraction (`SupabaseStorageService`, `SupabaseRealtimeBroadcaster`, `SupabaseAuthService`). Sebelum konfigurasi Supabase dipasang pengguna, sistem otomatis menggunakan *graceful fallback* (Local Storage, Laravel Database session, dan Server-Sent Events / Polling interval).

---

## Migration Plan

1. **Fase 1: Fondasi Backend & Database Migrations**
   - Inisialisasi struktur modul Laravel, migration seluruh tabel (PostgreSQL/Supabase compatible), dan database seeder dengan data menu otentik Kedai Mie Marmoyo (Mie Kuah Mala, Aneka Mie, Dimsum, Minuman, Level Pedas, Topping, dan Nomor Meja 01 s/d 12).
2. **Fase 2: Core Domain Services & Form Requests**
   - Pembuatan Action classes: `CreateOrderAction`, `ProcessQrisPaymentAction`, `ValidateCashPaymentAction`, `ReconcileCashDrawerAction`, serta Rule Engine `AiMenuRecommendationService`.
3. **Fase 3: Antarmuka Pelanggan (Mobile Self-Ordering)**
   - Implementasi alur QR meja, penjelajahan menu, kustomisasi tingkat pedas, rekomendasi AI bundle, checkout, pemilihan metode pembayaran, dan pelacakan live status pesanan.
4. **Fase 4: Antarmuka Kasir (POS & Live Order Queue)**
   - Dashboard kasir untuk validasi tunai cepat, kalkulator kembalian, trigger cetak printer dapur, dan perubahan status pesanan.
5. **Fase 5: Antarmuka Admin / Owner & Reporting**
   - Dashboard analitik omzet, grafik tren penjualan per jam, rekonsiliasi kas harian, CRUD menu & varian stok, serta generator QR meja.
6. **Fase 6: Penyiapan Integrasi Supabase (.env & Service Wrapper)**
   - Penyiapan file template `.env.example`, panduan konfigurasi Supabase URL, Anon Key, DB Connection String, dan Supabase Storage bucket policy.

---

## Open Questions

- *Printer Thermal Protocol*: Apakah printer dapur menggunakan koneksi USB / Bluetooth langsung ke perangkat kasir (menggunakan format web printing dialog), atau printer jaringan TCP/IP (port 9100)?
  - *Solusi Saat Ini*: Sistem menyediakan *thermal-ready HTML/ESC-POS print template* via browser print API dan menyiapkan soket printer payload service.
- *Verifikasi QRIS*: Sebelum akun payment gateway resmi (Midtrans / Xendit / Supabase Edge Functions) aktif, sistem menyediakan tombol simulasi "Saya Sudah Membayar" untuk pengujian alur verifikasi instan.

