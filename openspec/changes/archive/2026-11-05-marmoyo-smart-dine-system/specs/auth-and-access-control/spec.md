# Specification: Authentication and Access Control

## ADDED Requirements

### Requirement: Otorisasi Berbasis Peran (Three-Role RBAC)
Sistem SHALL menerapkan kontrol akses berbasis peran (*Role-Based Access Control*) yang memisahkan hak akses secara ketat antara 3 entitas pengguna: Pelanggan (Guest via Meja Sesi), Kasir (Operator POS & Operasional), dan Admin/Owner (Pemilik dengan hak akses penuh).

#### Scenario: Kasir mencoba mengakses menu pengaturan sensitif admin
- **WHEN** pengguna yang masuk dengan peran Kasir mencoba membuka halaman manajemen akun atau pengaturan sistem
- **THEN** sistem menolak akses dengan respons HTTP 403 Forbidden dan mencatat insiden keamanan ke dalam audit log

#### Scenario: Pelanggan mencoba memanggil endpoint POS kasir
- **WHEN** pelanggan mencoba mengirimkan permintaan HTTP ke endpoint konfirmasi pembayaran tunai kasir
- **THEN** sistem menolak permintaan karena tidak memiliki otentikasi peran staf kasir

---

### Requirement: Manajemen Sesi Pelanggan Meja yang Aman
Sistem SHALL mengisolasi sesi pelanggan menggunakan cookie bertanda tangan kriptografis berdasarkan token QR meja yang valid. Sesi meja MUST membatasi akses pelanggan hanya pada pesanan milik meja bersangkutan dan mencegah manipulasi pesanan meja lain.

#### Scenario: Pelanggan melihat riwayat pesanan aktif mejanya
- **WHEN** pelanggan Meja 08 membuka halaman pelacakan pesanan
- **THEN** sistem hanya menyajikan riwayat dan status pesanan milik Meja 08 tanpa mengekspos data pesanan meja lain

---

### Requirement: Kesiapan Integrasi Supabase Auth dan Audit Trail
Sistem SHALL menyediakan layer otentikasi yang mendukung multi-driver: otentikasi sesi standar Laravel untuk operasional lokal cepat, dan adapter otentikasi Supabase Auth (JWT/Bearer token) yang siap diaktifkan ketika variabel lingkungan Supabase dikonfigurasi di kemudian hari. Setiap tindakan keuangan sensitif (validasi uang tunai, pembatalan pesanan, penyesuaian kasir) MUST dicatat dalam audit trail.

#### Scenario: Kasir mengonfirmasi transaksi pembayaran tunai
- **WHEN** kasir mengonfirmasi pembayaran tunai untuk pesanan `#MRM-0824`
- **THEN** sistem mencatat identitas user kasir, timestamp, nomor pesanan, dan nominal uang diterima ke dalam tabel audit trail transaksi

