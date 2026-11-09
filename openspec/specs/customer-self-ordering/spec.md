# customer-self-ordering Specification

## Purpose
TBD - created by archiving change marmoyo-smart-dine-system. Update Purpose after archive.
## Requirements
### Requirement: Akses Menu Digital Berbasis QR Code Meja
Sistem SHALL menyediakan antarmuka web pemesanan mandiri yang dapat dibuka langsung oleh browser smartphone pelanggan melalui pemindaian QR Code meja tanpa kewajiban registrasi atau unduh aplikasi. Sesi meja MUST divalidasi menggunakan cryptographic token terdaftar untuk mengikat transaksi dengan nomor meja kedai.

#### Scenario: Pelanggan memindai QR Code meja yang valid
- **WHEN** pelanggan memindai QR code meja fisik (contoh: Meja 08) dengan kamera ponsel
- **THEN** sistem membuka halaman katalog digital Kedai Mie Marmoyo dengan header identitas Meja 08 aktif dan status sesi tersimpan di peramban

#### Scenario: Pelanggan mengakses URL dengan nomor meja tanpa token valid
- **WHEN** pengguna mencoba mengakses URL nomor meja secara manual tanpa cryptographic token yang sah
- **THEN** sistem menolak akses, menampilkan pesan error peringatan, dan mengarahkan pengguna untuk memindai QR Code resmi yang tertera di meja

---

### Requirement: Eksplorasi Menu dan Informasi Tingkat Kepedasan
Sistem SHALL menampilkan katalog menu terstruktur berdasarkan kategori (Aneka Mie Marmoyo, Sidesh & Dimsum Gurih, Minuman Segar Pereda Pedas). Setiap item menu berkarakter pedas MUST menampilkan label tingkat pedas (badge cabai/level) dan deskripsi cita rasa secara eksplisit sebelum pelanggan melakukan pemesanan.

#### Scenario: Pelanggan melihat indikator kepedasan pada menu
- **WHEN** pelanggan membuka daftar menu Aneka Mie Marmoyo
- **THEN** sistem menampilkan badge tingkat kepedasan (contoh: Level 5 Pedas Nampol) pada item Mie Kuah Mala lengkap dengan foto, estimasi harga Rp15.000, dan tombol kustomisasi

#### Scenario: Pelanggan menyaring menu berdasarkan kategori
- **WHEN** pelanggan mengetuk tab kategori "Sidesh & Dimsum Gurih"
- **THEN** sistem memfilter tampilan hanya menampilkan daftar dimsum seperti Udang Keju, Udang Rambutan, Dimsum Ayam Pangsit, dan Ceker Pedas

---

### Requirement: Kustomisasi Menu, Varian, dan Catatan Koki
Sistem SHALL menyediakan modal kustomisasi ketika item menu dipilih, mencakup opsi pemilihan level pedas (Level 1 Ringan, Level 2 Sedang, Level 3 Pedas Nampol, Level 4-5 Super Pedas Rawit Setan dengan biaya tambahan), jenis mie (Mie Keriting Kenyal atau Mie Lebar Lembut), ekstra topping (Pangsit Goreng Krispi, Telur Mata Sapi, Ekstra Daging Ayam Charsiu), serta kolom catatan khusus untuk koki dapur.

#### Scenario: Pelanggan memilih varian dan topping tambahan
- **WHEN** pelanggan memilih menu Mie Kuah Mala, memilih Level 3, jenis Mie Keriting, mencentang ekstra Topping Pangsit Goreng Krispi (+Rp3.000), dan menulis catatan "Jangan terlalu berminyak"
- **THEN** sistem memperbarui kalkulasi harga subtotal item secara realtime dan menyimpannya ke dalam keranjang pesanan meja

---

### Requirement: AI Smart Recommendation dan Cross-Selling Engine
Sistem SHALL menganalisis isi keranjang belanja pelanggan secara real-time dan memberikan rekomendasi pendamping (*smart pairing*). Jika keranjang memuat menu pedas level tinggi, sistem MUST merekomendasikan minuman dingin pereda pedas (Es Teh Manis Jumbo, Es Jeruk Peras) dan cemilan penetral (Udang Keju Crispy) dengan tombol "Tambah Cepat" (1-click add).

#### Scenario: Pelanggan memesan makanan pedas dan menerima rekomendasi minuman pereda pedas
- **WHEN** pelanggan menambahkan Mie Kuah Mala Level 5 ke keranjang belanja
- **THEN** sistem memunculkan banner "Rekomendasi Spesial AI: Pelanggan yang pesan Mie Mala biasanya menambah minuman dingin & dimsum ini biar makin mantap!" yang menawarkan Es Teh Jumbo (Rp3.000) dan Dimsum Pangsit (Rp10.000)

#### Scenario: Pelanggan menambahkan item rekomendasi AI ke keranjang
- **WHEN** pelanggan menekan tombol "+ Tambah" pada kartu rekomendasi AI Es Teh Jumbo
- **THEN** item Es Teh Jumbo langsung masuk ke daftar keranjang belanja tanpa keluar dari halaman saat ini

---

### Requirement: Manajemen Keranjang Pesanan dan Voucher Promo
Sistem SHALL menyediakan halaman rincian keranjang pesanan meja yang menampilkan daftar menu terpilih, kuantitas, catatan koki per item, catatan umum dapur, validasi kode voucher promo (contoh: "MAKANHEMAT" diskon Rp3.000), rincian pajak resto (PB1 10%), serta total pembayaran akhir.

#### Scenario: Pelanggan menerapkan kode promo diskon yang valid
- **WHEN** pelanggan memasukkan voucher promo "MAKANHEMAT" pada keranjang belanja senilai Rp25.000
- **THEN** sistem mengaplikasikan potongan diskon Rp3.000 dan memperbarui total pembayaran menjadi Rp22.000

---

### Requirement: Pelacakan Status Pesanan Real-Time dan Panggilan Pelayan
Sistem SHALL menyediakan layar pelacakan status pesanan langsung (*Live Tracking*) setelah pesanan terkonfirmasi, menampilkan tahap pengerjaan (Menunggu Pembayaran -> Diteruskan ke Dapur/Live Cook -> Siap Saji -> Selesai), estimasi waktu penyajian, struk digital yang dapat diunduh, serta tombol darurat "Panggil Pelayan Meja".

#### Scenario: Pelanggan memantau pesanan yang sedang dimasak
- **WHEN** pembayaran pesanan telah terverifikasi
- **THEN** sistem menampilkan badge hijau "Diteruskan ke Dapur Koki!", status "Live Cook", dan estimasi waktu penyajian 10-15 menit

#### Scenario: Pelanggan memanggil pelayan dari meja
- **WHEN** pelanggan menekan tombol "Butuh sendok atau bantuan? Panggil Pelayan Meja"
- **THEN** sistem mengirimkan sinyal notifikasi darurat ke layar POS kasir dengan rincian nomor meja pemanggil

