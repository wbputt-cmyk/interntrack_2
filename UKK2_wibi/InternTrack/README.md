# InternTrack

Dashboard monitoring kegiatan Praktik Kerja Lapangan (PKL) berbasis PHP dan MySQL. Aplikasi menampilkan ringkasan siswa, perusahaan, penempatan, jurnal, absensi, dan monitoring yang dibaca langsung dari database.

## Kebutuhan

- PHP 8.0 atau lebih baru dengan ekstensi `pdo_mysql`
- MySQL 8.0 atau MariaDB yang mendukung InnoDB dan `utf8mb4`

## Menyiapkan database

Import `database.sql` melalui phpMyAdmin atau MySQL CLI. Skrip membuat database `interntrack` beserta delapan tabel yang saling berelasi.

Untuk mengisi semua tabel dengan data contoh, jalankan perintah berikut dari folder aplikasi:

```sh
php seed.php
```

Seeder aman dijalankan lebih dari sekali dan tidak menghapus data yang sudah ada. Akun demo siswa: `siswa1`, `siswa2`, atau `siswa3`, dengan password `siswa123`. Login admin tersedia di `admin_login.php`, dengan username `admin` dan password `admin12345`.

Dashboard dan halaman pengelolaan hanya dapat dibuka setelah admin login. Siswa baru dapat ditambahkan dari menu **Data Siswa** setelah admin masuk. Username, email, dan password awal diperlukan agar siswa dapat masuk melalui tombol **Login siswa**. Siswa yang masuk dapat melihat penempatan PKL miliknya, mengisi jurnal dan absensi, serta melihat catatan yang telah dibuat. Setiap siswa hanya dapat mengakses data berdasarkan penempatan miliknya.

## Menjalankan aplikasi

Atur variabel lingkungan koneksi sebelum menjalankan PHP:

| Variabel | Default | Keterangan |
| --- | --- | --- |
| `DB_HOST` | `127.0.0.1` | Alamat server MySQL |
| `DB_NAME` | `interntrack` | Nama database |
| `DB_USER` | `root` | Pengguna database |
| `DB_PASSWORD` | kosong | Kata sandi database |

Kemudian dari folder aplikasi jalankan:

```sh
php -S 127.0.0.1:8000
```

Buka `http://127.0.0.1:8000` di browser. Untuk XAMPP, letakkan folder `InternTrack` di dalam `htdocs`, lalu buka `http://localhost/InternTrack`.

## Struktur database

- `users`: akun dan peran pengguna.
- `siswa`: identitas siswa, opsional terhubung ke akun.
- `perusahaan`: data mitra tempat PKL.
- `pembimbing`: pembimbing perusahaan.
- `penempatan`: relasi siswa, perusahaan, pembimbing, dan periode PKL.
- `jurnal`: catatan kegiatan harian per penempatan.
- `absensi`: kehadiran harian per penempatan.
- `monitoring`: catatan monitoring per penempatan dan petugas.

Semua halaman data memiliki formulir sederhana untuk menambah data; pengubahan dan penghapusan data belum disertakan. Halaman daftar menampilkan maksimal 100 data terbaru/relevan. Ganti password demo sebelum memakai aplikasi dengan data sungguhan.
