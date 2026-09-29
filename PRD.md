# Product Requirements Document (PRD)

## AlkadiSayur — Platform E-Commerce Toko Sayur dan Kelontong

### 1. Informasi Produk

**Nama Produk:** AlkadiSayur
**Jenis Produk:** Website E-Commerce
**Platform:** Web
**Backend:** Laravel
**Database:** MySQL
**Autentikasi:** Laravel Sanctum dan GoogleOauth
**Target Pengguna:** Customer dan Admin

---

## 2. Latar Belakang

Toko Alkadi menjual berbagai kebutuhan sehari-hari, mulai dari sayuran, buah, bumbu dapur, bahan pokok, produk hewani, makanan dan minuman, hingga frozen food. Proses pembelian yang masih mengharuskan pelanggan datang langsung ke toko dapat menyulitkan pelanggan dalam mengetahui produk, harga, dan ketersediaan barang yang tersedia.

AlkadiSayur dikembangkan sebagai website e-commerce untuk mempermudah pelanggan dalam melihat katalog produk dan melakukan pembelian secara online. Selain itu, sistem digunakan untuk mendigitalisasi informasi produk, proses pemesanan, pembayaran, pengelolaan stok, dan data penjualan sehingga pengelolaan toko dapat dilakukan secara lebih terstruktur.

---

## 3. Tujuan Produk

### Tujuan untuk Customer

* Mempermudah pelanggan melihat katalog produk secara online.
* Mempermudah pencarian dan pemilihan produk.
* Memungkinkan pelanggan melakukan pemesanan tanpa datang langsung ke toko.
* Menyediakan pilihan pengiriman atau pengambilan langsung di toko.
* Memungkinkan pelanggan melihat status dan riwayat pesanan.
* Menyediakan informasi lokasi toko.
* Memberikan kesempatan kepada pelanggan untuk memberikan ulasan terhadap toko.

### Tujuan untuk Admin

* Mempermudah pengelolaan produk dan kategori.
* Mempermudah pengelolaan stok barang.
* Mempermudah pengelolaan pesanan pelanggan.
* Memantau transaksi dan pembayaran.
* Melihat rekap penjualan.
* Mengelola lokasi toko.
* Memasukkan data produk secara massal melalui file Excel.
* Mengekspor data produk dan penjualan untuk kebutuhan pengelolaan data.

---

# 4. Role Pengguna

## 4.1 Customer

Customer dapat:

* Registrasi akun.
* Login dan logout.
* Melihat katalog produk.
* Mencari dan memfilter produk.
* Menambahkan produk ke keranjang.
* Mengatur jumlah barang.
* Mengelola alamat.
* Melakukan checkout.
* Memilih metode pengambilan pesanan.
* Melakukan pembayaran.
* Melihat status pesanan.
* Melihat riwayat pesanan.
* Memberikan ulasan toko.

## 4.2 Admin

Admin dapat:

* Login ke sistem.
* Mengakses dashboard admin.
* Mengelola kategori.
* Mengelola produk.
* Mengelola stok.
* Mengelola pesanan.
* Mengelola pembayaran.
* Mengelola lokasi toko.
* Melihat rekap penjualan.
* Import data produk.
* Export data produk.
* Export data penjualan.
* Melihat notifikasi pesanan baru.

---

# 5. Fitur Utama

## 5.1 Autentikasi & Role-Based Access

Sistem menyediakan registrasi, login, dan logout menggunakan Laravel Sanctum dan GoogleOauth. Halaman yang membutuhkan autentikasi hanya dapat diakses oleh pengguna dengan token yang valid, sedangkan hak akses dibedakan berdasarkan role customer dan admin.

---

## 5.2 Halaman Utama & Katalog Produk

Customer dapat melihat halaman utama dan daftar produk yang tersedia pada toko. Informasi produk meliputi nama produk, kategori, harga, stok, foto, merek, dan informasi tambahan lainnya.

---

## 5.3 Kategori Produk

Produk dikelompokkan berdasarkan kategori untuk mempermudah customer menemukan barang yang dibutuhkan.

Kategori awal meliputi:

* Sayuran
* Buah
* Bumbu Dapur
* Bahan Pokok
* Hewani
* Makanan & Minuman
* Frozen Food
* Lainnya

Admin dapat mengelola kategori produk.

---

## 5.4 Pencarian & Filter Produk

Customer dapat mencari produk berdasarkan nama dan menggunakan filter berdasarkan kategori atau kriteria produk tertentu untuk mempercepat proses pencarian.

---

## 5.5 Keranjang Belanja

Customer dapat menambahkan produk ke keranjang, mengubah jumlah barang, menghapus barang, serta melihat total harga sebelum melanjutkan ke proses checkout.

---

## 5.6 Checkout & Pemesanan

Customer dapat melakukan checkout terhadap produk yang terdapat di keranjang. Customer dapat menentukan alamat, lokasi toko, metode pengambilan, dan catatan pesanan.

Terdapat dua metode pengambilan:

* **Pesan antar** — pesanan dikirim ke alamat customer.
* **Ambil di toko** — customer mengambil pesanan secara langsung pada lokasi toko yang dipilih.

---

## 5.7 Manajemen Alamat

Customer dapat menyimpan dan mengelola beberapa alamat yang digunakan untuk proses pemesanan dan pengiriman.

---

## 5.8 Pembayaran

Sistem mencatat pembayaran berdasarkan pesanan customer dengan metode:

* QRIS
* Transfer
* COD

Setiap pembayaran memiliki status:

* Pending
* Berhasil
* Gagal
* Expired

Data transaksi pembayaran dikaitkan dengan pesanan.

---

## 5.9 Status & Riwayat Pesanan

Customer dapat melihat perkembangan pesanan berdasarkan status:

* Pending
* Diproses
* Siap Diambil
* Dikirim
* Selesai
* Dibatalkan

Customer juga dapat melihat daftar pesanan yang pernah dilakukan.

---

## 5.10 Manajemen Produk & Stok

Admin dapat menambah, mengubah, melihat, dan menghapus produk. Admin juga dapat mengelola jumlah stok serta status ketersediaan produk.

Data produk meliputi kode item, barcode, SKU, nama produk, kategori, merek, stok, rak, tipe item, harga beli, harga jual, keterangan, foto, dan status.

---

## 5.11 Manajemen Pesanan

Admin dapat melihat dan mengelola pesanan yang masuk. Admin dapat memperbarui status pesanan sesuai dengan proses pemenuhan pesanan.

Pesanan juga membedakan antara metode **pesan antar** dan **ambil di toko**.

---

## 5.12 Dashboard Admin

Dashboard menyediakan ringkasan informasi toko untuk membantu admin memantau aktivitas sistem.

Informasi yang dapat ditampilkan antara lain:

* Jumlah produk
* Jumlah stok
* Jumlah pesanan
* Pesanan baru
* Pesanan selesai
* Total penjualan
* Ringkasan penjualan
* Produk dengan stok rendah

---

## 5.13 Rekap Penjualan

Admin dapat melihat rekap penjualan berdasarkan data transaksi yang telah selesai. Data dapat digunakan untuk mengetahui jumlah transaksi dan nilai penjualan dalam periode tertentu.

---

## 5.14 Lokasi Toko

Admin dapat mengelola lokasi cabang toko yang ditampilkan pada halaman About Us. Setiap lokasi dapat menyimpan nama toko, koordinat latitude dan longitude, serta deskripsi.

Fitur ini digunakan untuk menampilkan informasi lokasi toko kepada customer dan mendukung pilihan lokasi saat customer memilih metode ambil di toko.

---

## 5.15 Ulasan Toko

Customer dapat memberikan ulasan terhadap pelayanan toko secara keseluruhan melalui rating dan komentar. Ulasan tidak diberikan untuk masing-masing produk.

---

## 5.16 Notifikasi Pesanan

Sistem memberikan notifikasi kepada admin ketika terdapat pesanan baru yang masuk. Notifikasi memiliki informasi judul, pesan, pesanan terkait, serta status sudah atau belum dibaca.

---

## 5.17 Import & Export Data Produk dan Penjualan

Admin dapat melakukan import data produk menggunakan file Excel sehingga data produk dalam jumlah banyak dapat dimasukkan secara sekaligus tanpa harus menambahkan produk satu per satu.

Admin juga dapat melakukan export data produk dan data penjualan untuk kebutuhan pengelolaan, pelaporan, dan pengolahan data lebih lanjut.

---

# 6. Alur Customer

```text
Registrasi / Login
        ↓
Halaman Utama
        ↓
Katalog Produk
        ↓
Cari / Filter Produk
        ↓
Pilih Produk
        ↓
Tambah ke Keranjang
        ↓
Keranjang
        ↓
Checkout
        ↓
Pilih Alamat
        ↓
Pilih Metode
 ┌───────────────┐
 │               │
Dikirim      Ambil di Toko
 │               │
Alamat        Lokasi Toko
 │               │
 └───────┬───────┘
         ↓
     Pembayaran
         ↓
    Pesanan Diproses
         ↓
   Dikirim / Siap Diambil
         ↓
       Selesai
         ↓
     Ulasan Toko
```

---

# 7. Alur Admin

```text
Login Admin
     ↓
Dashboard
     ↓
 ┌───┼────────┬──────────┐
 ↓   ↓        ↓          ↓
Produk Stok  Pesanan  Penjualan
 ↓   ↓        ↓          ↓
Import       Kelola    Rekap
Export       Status
     ↓
Lokasi Toko
     ↓
Notifikasi Pesanan
```

---

# 8. Data Utama Sistem

Database utama yang digunakan dalam sistem meliputi:

| Tabel          | Fungsi                                  |
| -------------- | --------------------------------------- |
| users          | Menyimpan data customer dan admin       |
| kategori       | Menyimpan kategori produk               |
| barang         | Menyimpan data produk                   |
| lokasi         | Menyimpan lokasi toko                   |
| alamat         | Menyimpan alamat customer               |
| keranjang      | Menyimpan keranjang customer            |
| keranjang_item | Menyimpan produk dalam keranjang        |
| pesanan        | Menyimpan data transaksi pemesanan      |
| pesanan_item   | Menyimpan detail produk dalam pesanan   |
| pembayaran     | Menyimpan data pembayaran               |
| ulasan         | Menyimpan ulasan customer terhadap toko |
| notifikasi     | Menyimpan notifikasi pesanan            |

---

# 9. Status Pesanan

| Status       | Deskripsi                               |
| ------------ | --------------------------------------- |
| Pending      | Pesanan baru dibuat dan menunggu proses |
| Diproses     | Pesanan sedang disiapkan oleh toko      |
| Siap Diambil | Pesanan siap diambil customer           |
| Dikirim      | Pesanan sedang dalam proses pengiriman  |
| Selesai      | Pesanan telah selesai                   |
| Dibatalkan   | Pesanan dibatalkan                      |

---

# 10. Non-Functional Requirements

### Performance

* Halaman katalog harus dapat menampilkan produk dengan waktu respons yang wajar.
* Query produk, pesanan, dan transaksi perlu menggunakan pagination untuk data dalam jumlah besar.
* Sistem harus menghindari pengambilan seluruh data sekaligus ketika jumlah data meningkat.

### Security

* Password pengguna harus disimpan dalam bentuk hash.
* Endpoint yang membutuhkan autentikasi harus dilindungi Laravel Sanctum.
* Hak akses admin dan customer harus dibatasi berdasarkan role.
* Validasi input harus dilakukan pada request yang diterima server.

### Scalability

* Struktur database menggunakan relasi antar tabel agar data dapat dikembangkan.
* Produk, kategori, pesanan, dan transaksi dipisahkan ke dalam tabel masing-masing.
* Sistem mendukung penambahan lokasi toko baru tanpa mengubah struktur database.
* Import data memungkinkan penambahan produk dalam jumlah besar.

### Maintainability

* Backend menggunakan struktur Laravel Model, Migration, Factory, Seeder, Controller, Request, dan Resource.
* Relasi antar model menggunakan Eloquent.
* Validasi data dipisahkan dari logic controller menggunakan Form Request jika diperlukan.

---

# 11. MVP

Versi awal sistem diprioritaskan pada fitur berikut:

1. Autentikasi & Role-Based Access
2. Katalog Produk
3. Kategori Produk
4. Pencarian & Filter Produk
5. Keranjang Belanja
6. Checkout & Pemesanan
7. Manajemen Alamat
8. Pembayaran
9. Status & Riwayat Pesanan
10. Manajemen Produk & Stok
11. Manajemen Pesanan
12. Dashboard Admin
13. Rekap Penjualan
14. Lokasi Toko
15. Ulasan Toko
16. Notifikasi Pesanan
17. Import & Export Data Produk dan Penjualan

---

# 12. Indikator Keberhasilan

Sistem dianggap memenuhi kebutuhan utama apabila:

* Customer dapat membuat akun dan login.
* Customer dapat melihat dan mencari produk.
* Customer dapat menambahkan produk ke keranjang.
* Customer dapat melakukan checkout.
* Customer dapat memilih pesan antar atau ambil di toko.
* Sistem dapat mencatat pembayaran.
* Customer dapat melihat status dan riwayat pesanan.
* Admin dapat mengelola produk dan stok.
* Admin dapat mengelola pesanan.
* Admin dapat melihat rekap penjualan.
* Admin dapat menambahkan lokasi toko.
* Customer dapat memberikan ulasan toko.
* Admin menerima notifikasi ketika terdapat pesanan baru.
* Admin dapat melakukan import produk melalui Excel.
* Admin dapat melakukan export data produk dan penjualan.
