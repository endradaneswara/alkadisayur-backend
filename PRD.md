# Product Requirements Document (PRD)

## AlkadiSayur — Platform E-Commerce Toko Sayur dan Kelontong

### 1. Informasi Produk

| Informasi | Keterangan |
| --- | --- |
| Nama produk | AlkadiSayur |
| Jenis produk | Website e-commerce |
| Frontend | Next.js dengan App Router dan routing berbasis folder |
| Backend | Laravel 13, REST API |
| Bahasa backend | PHP 8.4 |
| Database | MySQL |
| Autentikasi API | Token akses Laravel Sanctum |
| Login pihak ketiga | Google OAuth melalui Laravel Socialite |
| Target pengguna | Customer, admin cabang, dan super admin |

Backend Laravel yang ada menjadi sumber data dan otorisasi. Frontend Next.js adalah aplikasi terpisah yang akan mengonsumsi endpoint JSON backend melalui `/api`. Halaman dan route UI tidak menggantikan route API tersebut.

---

## 2. Latar Belakang

Toko Alkadi menjual kebutuhan sehari-hari, termasuk sayuran, buah, bumbu dapur, bahan pokok, produk hewani, makanan dan minuman, serta frozen food. AlkadiSayur mendigitalisasi katalog, keranjang, pemesanan, pembayaran, stok, dan pengelolaan toko.

Backend telah menyediakan API untuk alur utama e-commerce dan administrasi. PRD ini mencatat kemampuan backend yang sudah tersedia sekaligus kebutuhan frontend Next.js yang perlu dibangun. Fitur yang belum didukung API ditandai sebagai kebutuhan pengembangan, bukan dianggap sudah berjalan.

---

## 3. Tujuan Produk

### 3.1 Customer

* Membuat akun dan masuk dengan akun AlkadiSayur atau Google.
* Melihat produk, kategori, harga, ketersediaan, dan lokasi toko.
* Mengelola keranjang dan alamat.
* Memesan barang dengan metode pesan antar atau ambil di toko.
* Mencatat pembayaran dan memantau status pesanan.
* Melihat riwayat pesanan, profil, dan notifikasi.
* Memberikan satu ulasan untuk toko dan mengelola ulasan tersebut.

### 3.2 Admin Cabang

* Melihat dan memproses pesanan untuk lokasi cabangnya.
* Memperbarui status pembayaran.
* Melihat laporan penjualan dan notifikasi yang menjadi hak akses akunnya.

### 3.3 Super Admin

* Mengelola produk, kategori, lokasi, dan role pengguna.
* Memantau dashboard dan seluruh data pesanan/penjualan.
* Mengimpor dan mengekspor data produk.

---

## 4. Role dan Hak Akses

### 4.1 Customer

Customer dapat registrasi, login/logout, mengelola profil dan kata sandi, melihat katalog, mengelola alamat dan keranjang, membuat dan membatalkan pesanan yang masih dapat dibatalkan, membuat pembayaran, melihat riwayatnya, serta mengelola ulasan toko miliknya.

### 4.2 Admin

Admin cabang dapat melihat pesanan, mengubah status pesanan, memperbarui status pembayaran, serta melihat laporan penjualan dan notifikasi. Data operasional dibatasi ke pesanan/laporan dari `lokasi_id` akun admin. Admin tanpa lokasi cabang tidak memperoleh data pesanan cabang.

Admin tidak memiliki akses super admin untuk dashboard umum, CRUD produk/kategori/lokasi, manajemen role pengguna, atau import/export produk.

### 4.3 Super Admin

Super admin memiliki akses pengelolaan lintas lokasi, termasuk dashboard, produk, kategori, lokasi, role pengguna, laporan penjualan, dan import/export produk.

Registrasi publik selalu membuat akun dengan role customer. Perubahan role dilakukan melalui pengelolaan pengguna oleh super admin.

---

## 5. Arsitektur Frontend Next.js

### 5.1 Routing Berbasis Folder

Frontend menggunakan Next.js App Router. Setiap halaman dibuat sebagai `page.tsx` di dalam folder `app/`; folder bertingkat membentuk URL, sedangkan `layout.tsx` dapat dipakai untuk layout bersama. Route groups seperti `(store)` atau `(admin)` boleh digunakan untuk mengelompokkan layout tanpa menambahkan segmen URL.

Struktur awal yang disarankan:

```text
app/
  (store)/
    page.tsx
    produk/
      page.tsx
      [id]/
        page.tsx
    kategori/
      [id]/
        page.tsx
    keranjang/
      page.tsx
    checkout/
      page.tsx
    pesanan/
      page.tsx
      [id]/
        page.tsx
    akun/
      masuk/
        page.tsx
      daftar/
        page.tsx
      lupa-kata-sandi/
        page.tsx
      reset-kata-sandi/
        page.tsx
      profil/
        page.tsx
      alamat/
        page.tsx
  (admin)/
    admin/
      page.tsx
      pesanan/
        page.tsx
      penjualan/
        page.tsx
      notifikasi/
        page.tsx
      produk/
        page.tsx
      kategori/
        page.tsx
      lokasi/
        page.tsx
      pengguna/
        page.tsx
      import-produk/
        page.tsx
```

Daftar ini merupakan rancangan route UI, bukan route yang sudah ada di backend. Halaman admin hanya ditampilkan sesuai role; backend tetap menjadi otoritas final untuk validasi token dan izin akses.

### 5.2 Integrasi API

* Base URL backend diatur melalui environment variable frontend; jangan menanam URL server di banyak komponen.
* Request ke endpoint terproteksi mengirim token akses Sanctum melalui header `Authorization` dan menangani respons 401/403 dengan benar.
* Pertahankan pemisahan komponen tampilan dengan modul/helper integrasi API agar format respons, error validasi, dan pengiriman token konsisten.
* Untuk endpoint unduh/unggah, tangani file binary atau multipart sesuai respons endpoint, bukan sebagai JSON biasa.
* Halaman menampilkan state loading, kosong, berhasil, dan error. Pesan validasi backend ditampilkan pada field/form yang relevan.
* Token dan data sesi harus disimpan serta digunakan dengan cara yang tidak mengekspos kredensial kepada pihak yang tidak berwenang.

---

## 6. Fitur dan Perilaku Sistem

### 6.1 Autentikasi, Akun, dan Profil

API menyediakan registrasi, login, logout, login Google OAuth, pengambilan/pembaruan profil, pembaruan kata sandi, serta alur lupa dan reset kata sandi. Login menerima nama atau email beserta kata sandi. Akun hasil registrasi publik berperan sebagai customer.

Frontend menyediakan halaman daftar, masuk, lupa/reset kata sandi, callback/kelanjutan login Google, profil, dan ubah kata sandi. Semua halaman terproteksi harus menangani token tidak valid atau kedaluwarsa tanpa menampilkan data privat.

### 6.2 Halaman Utama dan Katalog

Customer dapat melihat katalog produk, detail produk, kategori, dan daftar produk suatu kategori. Data produk dapat berisi kategori, kode item, barcode, SKU, nama, merek, stok, rak, tipe item, harga beli/jual, keterangan, foto, serta status tersedia/tidak tersedia. UI customer hanya menampilkan informasi yang sesuai untuk pembeli dan tidak membocorkan data internal seperti harga beli.

**Batasan API saat ini:** endpoint katalog memerlukan autentikasi dan memakai pagination (20 item per halaman). Endpoint produk/kategori belum menyediakan pencarian nama atau filter katalog umum. Kebutuhan pencarian dan filter di UI memerlukan dukungan query API sebelum dapat digunakan secara konsisten pada keseluruhan katalog; jangan hanya memfilter item pada halaman pagination yang sedang terbuka.

### 6.3 Kategori dan Lokasi Toko

Customer dapat melihat kategori dan lokasi toko. Lokasi berisi nama, koordinat, deskripsi, serta biaya pengiriman. Super admin dapat membuat, memperbarui, dan menghapus kategori/lokasi. Lokasi yang dihapus secara lunak tidak boleh ditawarkan sebagai pilihan checkout.

### 6.4 Keranjang Belanja

Customer dapat melihat keranjang, menambah barang, mengubah jumlah, menghapus barang, dan mengosongkan keranjang. Harga pada keranjang ditampilkan sebagai informasi sebelum checkout; backend menghitung ulang nilai pesanan dari harga produk saat pesanan dibuat.

### 6.5 Alamat

Customer dapat membuat, melihat, mengubah, dan menghapus beberapa alamat. Data alamat terdiri dari nama alamat, nama penerima, nomor telepon, dan alamat lengkap. Alamat harus dipilih untuk metode pesan antar; metode ambil di toko tidak memerlukan alamat pengiriman.

### 6.6 Checkout dan Pesanan

Pesanan dibuat dari isi keranjang yang tidak kosong. Customer wajib memilih lokasi toko dan metode `pesan_antar` atau `ambil_ditoko`; metode pesan antar juga wajib memiliki alamat milik customer. Biaya pengiriman dihitung dari biaya lokasi yang dipilih untuk pesan antar dan bernilai nol untuk ambil di toko.

Backend memvalidasi ketersediaan dan stok dalam proses checkout, menyimpan detail harga/nama barang pada item pesanan, mengurangi stok, lalu mengosongkan keranjang setelah pesanan berhasil dibuat. Customer dapat melihat daftar/detail pesanan miliknya. Pembatalan oleh customer hanya diperbolehkan saat status masih `pending` atau `diproses`; stok dikembalikan ketika pesanan dibatalkan.

### 6.7 Pembayaran

Customer dapat membuat satu catatan pembayaran untuk satu pesanan dengan metode `QRIS`, `Transfer`, atau `COD`. Data pembayaran mencakup jumlah, ID transaksi, tanggal, dan status. Status pembayaran yang didukung: `pending`, `berhasil`, `gagal`, dan `expired`.

API menyediakan pencatatan pembayaran dan pembaruan status oleh admin/super admin. Integrasi payment gateway atau konfirmasi otomatis pembayaran tidak termasuk kemampuan yang terverifikasi di backend saat ini; status pada frontend harus mengikuti data backend, bukan menganggap transaksi berhasil hanya karena customer memilih metode.

### 6.8 Status dan Riwayat Pesanan

Status yang didukung backend:

| Status | Keterangan |
| --- | --- |
| `pending` | Pesanan baru dibuat |
| `diproses` | Pesanan sedang disiapkan |
| `dikirim` | Pesanan berstatus dalam proses pengantaran/pemenuhan berikutnya |
| `selesai` | Pesanan selesai |
| `dibatalkan` | Pesanan dibatalkan |

Transisi status oleh admin adalah `pending` ke `diproses` atau `dibatalkan`, `diproses` ke `dikirim` atau `dibatalkan`, dan `dikirim` ke `selesai`. Status `selesai` dan `dibatalkan` bersifat final. Backend belum memiliki status khusus `siap_diambil`; UI tidak boleh mengirim status tersebut. Untuk pesanan ambil di toko, tampilkan metode dan instruksi pengambilan tanpa mengarang status backend baru.

### 6.9 Pengelolaan Produk dan Stok

Super admin dapat melihat, menambah, mengubah, dan menghapus produk beserta kategori dan atribut barang. Admin cabang tidak memiliki endpoint CRUD produk khusus. Stok berubah ketika checkout berhasil dan dipulihkan ketika pesanan dibatalkan; perubahan stok manual dapat dilakukan melalui pembaruan produk yang diizinkan untuk super admin.

### 6.10 Dashboard

Dashboard tersedia untuk super admin. Ringkasan backend mencakup jumlah produk, kategori, pengguna, admin, customer, total pesanan, jumlah pesanan per status, total nilai pesanan berstatus selesai termasuk biaya pengiriman, jumlah produk dengan stok `<= 3`, dan jumlah produk berstatus tidak tersedia.

Tidak semua angka tersebut merupakan jumlah stok agregat; frontend harus menggunakan nama/arti field dari respons API dan tidak menafsirkannya sebagai total unit stok.

### 6.11 Laporan Penjualan

Admin cabang dan super admin dapat melihat daftar/detail transaksi selesai serta ringkasan penjualan. Daftar dapat difilter menurut tanggal mulai dan tanggal selesai, dipaginasi 20 item, dan untuk admin cabang dibatasi ke lokasi akun. Ringkasan menyediakan total transaksi, nilai penjualan, biaya pengiriman, dan total pendapatan.

**Batasan API saat ini:** laporan penjualan tersedia untuk dibaca sebagai JSON; endpoint export penjualan belum tersedia. Fitur export penjualan pada frontend memerlukan endpoint backend baru.

### 6.12 Ulasan Toko

Customer dapat membuat, melihat, memperbarui, dan menghapus ulasan toko yang berisi rating dan komentar opsional. Satu akun hanya dapat memiliki satu ulasan aktif. Ulasan merupakan ulasan toko, bukan ulasan produk.

### 6.13 Notifikasi

API menyediakan daftar/detail notifikasi per pengguna, menandai satu atau semua notifikasi sebagai dibaca, dan menghapus notifikasi. Notifikasi menyimpan judul, pesan, referensi pesanan, dan waktu dibaca. Frontend hanya menampilkan notifikasi milik pengguna yang sedang masuk.

### 6.14 Import dan Export Produk

Fitur data produk tersedia untuk super admin:

* Mengunduh template import.
* Mengunggah berkas `.xlsx`, `.xls`, atau `.csv` dengan ukuran maksimum 10 MB.
* Meninjau validasi dan perubahan sebelum konfirmasi import.
* Mengonfirmasi sinkronisasi menggunakan hash pratinjau yang diberikan API.
* Mengunduh export produk dalam format `.xlsx`.

Import dapat membuat/memperbarui produk berdasarkan kode item dan menyinkronkan nilai stok dari file. Sel kosong pada kolom opsional dapat mengosongkan nilai yang tersimpan; UI harus menjelaskan dampak tersebut sebelum konfirmasi. Bila data berubah setelah pratinjau, frontend meminta pengguna membuat pratinjau baru.

---

## 7. Alur Pengguna

### 7.1 Customer

```text
Daftar / Masuk / Login Google
            ↓
Beranda → Katalog → Detail Produk
                         ↓
                      Keranjang
                         ↓
                       Checkout
                         ↓
            Pilih lokasi dan metode
                 ┌───────┴────────┐
             Pesan antar       Ambil di toko
                 ↓                  ↓
          Pilih alamat       Konfirmasi lokasi
                 └───────┬────────┘
                         ↓
                 Buat pesanan
                         ↓
              Catat pembayaran
                         ↓
            Pantau status pesanan
                         ↓
                Selesai → Ulasan
```

### 7.2 Admin Cabang

```text
Masuk → Pesanan cabang → Detail pesanan → Perbarui status
      → Pembayaran → Perbarui status pembayaran
      → Penjualan → Filter tanggal / ringkasan
      → Notifikasi
```

### 7.3 Super Admin

```text
Masuk → Dashboard
      → Produk / Kategori / Lokasi
      → Pesanan / Pembayaran / Penjualan
      → Pengguna dan role
      → Template / Pratinjau / Konfirmasi import / Export produk
```

---

## 8. Route Frontend yang Dibutuhkan

| Route UI Next.js | Tujuan |
| --- | --- |
| `/` | Beranda |
| `/produk` | Katalog produk |
| `/produk/[id]` | Detail produk |
| `/kategori/[id]` | Produk per kategori |
| `/keranjang` | Keranjang customer |
| `/checkout` | Pilihan lokasi, metode, dan alamat |
| `/pesanan` | Riwayat customer |
| `/pesanan/[id]` | Detail/status pesanan |
| `/akun/masuk` | Login |
| `/akun/daftar` | Registrasi |
| `/akun/lupa-kata-sandi` | Meminta reset kata sandi |
| `/akun/reset-kata-sandi` | Reset kata sandi |
| `/akun/profil` | Profil dan kata sandi |
| `/akun/alamat` | Daftar dan pengelolaan alamat |
| `/admin` | Dashboard super admin |
| `/admin/pesanan` | Pesanan yang sesuai cakupan role |
| `/admin/penjualan` | Laporan penjualan |
| `/admin/notifikasi` | Notifikasi |
| `/admin/produk` | Pengelolaan produk super admin |
| `/admin/kategori` | Pengelolaan kategori super admin |
| `/admin/lokasi` | Pengelolaan lokasi super admin |
| `/admin/pengguna` | Pengelolaan pengguna/role super admin |
| `/admin/import-produk` | Pratinjau, konfirmasi import, dan export produk |

---

## 9. Data Utama

| Tabel | Fungsi |
| --- | --- |
| `users` | Akun, role, lokasi admin, dan informasi profil |
| `kategori` | Kategori produk |
| `barang` | Data produk dan stok |
| `lokasi` | Cabang toko, koordinat, dan biaya pengiriman |
| `alamat` | Alamat customer |
| `keranjang` | Keranjang milik pengguna |
| `keranjang_item` | Produk dan jumlah pada keranjang |
| `pesanan` | Transaksi pesanan, metode, lokasi, dan status |
| `pesanan_item` | Salinan nama/harga serta jumlah produk dalam pesanan |
| `pembayaran` | Metode, jumlah, ID transaksi, dan status pembayaran |
| `ulasan` | Rating dan komentar toko dari pengguna |
| `notifikasi` | Notifikasi milik pengguna yang terhubung ke pesanan |
| `personal_access_tokens` | Token API Sanctum |

---

## 10. Kebutuhan Non-Fungsional

### Performa

* Gunakan pagination API yang tersedia; endpoint daftar utama mengembalikan maksimum 20 data per halaman.
* Tampilkan kontrol pagination dan state loading/empty yang jelas.
* Jangan menganggap filter lokal pada satu halaman sebagai pencarian seluruh katalog.

### Keamanan dan Akses

* Gunakan HTTPS pada deployment.
* Kirim token Sanctum hanya untuk API terproteksi dan jangan mengeksposnya di URL atau log.
* Validasi akses role di backend tetap wajib; menyembunyikan menu frontend bukan pengganti otorisasi API.
* Jangan menampilkan data internal produk seperti harga beli kepada customer.
* Data pesanan, alamat, pembayaran, dan notifikasi hanya boleh ditampilkan kepada pemilik atau role yang berwenang.

### Maintainability

* Gunakan App Router dan struktur folder `app/` sebagai sumber routing halaman Next.js.
* Pisahkan layout storefront dan admin, serta gunakan komponen bersama untuk form, tabel, pagination, pesan error, dan status.
* Jaga kontrak nama field/status backend; normalisasi hanya untuk tampilan, jangan mengirim nilai enum buatan frontend.
* Dokumentasikan variabel environment frontend untuk base URL API tanpa memasukkan secret ke repository.

---

## 11. Cakupan Implementasi dan Kesenjangan

### Sudah didukung backend

Autentikasi dan profil, katalog/kategori/lokasi terproteksi, keranjang, alamat, checkout dan pembatalan, pencatatan pembayaran, status/riwayat pesanan, role admin dan super admin, pengelolaan produk/kategori/lokasi, dashboard, laporan penjualan JSON, ulasan toko, manajemen notifikasi, serta import pratinjau/konfirmasi dan export produk.

### Perlu direncanakan atau dikembangkan

1. Frontend Next.js dengan App Router dan seluruh route UI pada bagian 8.
2. Pencarian nama dan filter produk melalui parameter API agar mencakup seluruh katalog.
3. Endpoint export laporan penjualan bila fitur unduh laporan dibutuhkan.
4. Status khusus pemenuhan ambil di toko bila bisnis memerlukan status “siap diambil”; backend saat ini belum mendukungnya.
5. Integrasi payment gateway/konfirmasi otomatis bila diperlukan; backend saat ini hanya mencatat metode dan status pembayaran.
6. Pastikan proses pembentukan/pengiriman notifikasi pesanan terhubung dengan alur pesanan bila notifikasi otomatis diperlukan.

---

## 12. Kriteria Penerimaan Frontend

* Route halaman mengikuti struktur folder Next.js App Router.
* Customer dapat masuk/daftar, melihat katalog terproteksi, mengelola keranjang/alamat, checkout dengan metode yang valid, dan melihat pesanan miliknya.
* Checkout menampilkan biaya pengiriman sesuai lokasi, memvalidasi pilihan alamat, dan menangani keranjang kosong atau stok tidak cukup.
* UI hanya mengirim nilai metode/status yang didukung API dan menampilkan status pesanan aktual.
* Admin cabang hanya melihat data yang diberikan backend untuk lokasinya; super admin mendapatkan halaman pengelolaan yang sesuai.
* Form pembayaran tidak menampilkan keberhasilan transaksi sebelum status API menyatakan `berhasil`.
* Import produk memiliki pratinjau dan konfirmasi eksplisit serta memberi peringatan bahwa kolom opsional kosong dapat menghapus nilai lama.
* Setiap halaman menangani kondisi belum login, tidak berizin, data kosong, loading, dan error API.
* Kebutuhan pencarian seluruh katalog dan export penjualan tidak dinyatakan selesai sampai endpoint pendukung tersedia.
