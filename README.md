# Kopi Singgah

Aplikasi web kedai kopi berbasis PHP dan MySQL. Pengunjung dapat melihat produk, membuat akun, memasukkan produk ke keranjang, dan mengelola transaksi. Aplikasi juga menyediakan halaman admin untuk mengelola produk, pelanggan, transaksi, dan laporan.

## Teknologi

- PHP 8.x
- MySQL 8.x
- Bootstrap, Tailwind CSS, dan aset frontend lokal/CDN

## Menjalankan secara lokal

1. Pasang PHP dan MySQL menggunakan Laragon, XAMPP, atau lingkungan setara.
2. Buat database dan tabel dengan mengimpor [`database/db_toko_schema.sql`](database/db_toko_schema.sql) melalui phpMyAdmin atau MySQL CLI. Skrip membuat database `db_toko`.
3. Atur host, username, password, dan nama database lokal di [`koneksi.php`](koneksi.php). Jangan simpan kredensial produksi di repository.
4. Jalankan Apache dan MySQL, lalu buka aplikasi melalui alamat lokal Laragon/XAMPP, atau arahkan document root server PHP ke folder proyek ini.
5. Skema hanya membuat struktur tabel dan trigger; skema ini tidak mengisi data produk atau akun. Tambahkan data awal melalui aplikasi atau SQL lokal sebelum menggunakan fitur yang memerlukannya.

Contoh impor melalui MySQL CLI:

```bash
mysql -u root -p < database/db_toko_schema.sql
```

## Struktur database

Skema berisi sembilan tabel:

| Tabel | Kegunaan |
|---|---|
| `barang` | Data barang dan stok untuk alur pembelian |
| `pembelian` | Catatan pembelian barang |
| `pembayaran` | Jumlah pembayaran |
| `log_pembelian` | Log operasi |
| `tb_user` | Akun admin dan pelanggan |
| `tb_kategori` | Kategori produk |
| `tb_produk` | Katalog produk dan stok |
| `tb_transaksi` | Transaksi dan informasi pengiriman |
| `tb_detail` | Rincian produk per transaksi |

Skema juga menyertakan trigger untuk pencatatan log, perhitungan pembayaran, bonus pembelian, dan pengurangan stok.

## Keamanan dan data

- File skema hanya berisi definisi database, bukan data akun, pelanggan, pembayaran, atau transaksi.
- Jangan commit dump database berisi data asli, kredensial, atau bukti pembayaran.
- Direktori `uploads` dapat berisi bukti pembayaran. Jangan publikasikan isinya; simpan di luar repository atau pastikan direktori tersebut diabaikan oleh Git.
- Untuk kebutuhan demonstrasi ini, password disimpan sebagai teks biasa. Ini tidak aman untuk penggunaan nyata; jangan gunakan password asli atau deploy aplikasi dengan pendekatan ini.
