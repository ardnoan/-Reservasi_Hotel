# Sistem Manajemen Data Mahasiswa

Aplikasi manajemen data mahasiswa sederhana menggunakan PHP dan MySQL dengan fitur login dan register.

## Fitur

- Sistem login dan register dengan password hashing
- Autentikasi dan session management
- Dashboard admin
- CRUD data mahasiswa:
  - Create: Tambah data mahasiswa baru
  - Read: Lihat daftar mahasiswa
  - Update: Edit data mahasiswa
  - Delete: Hapus data mahasiswa
- Validasi input
- Tampilan minimalis dengan CSS

## Struktur Database

- Tabel `users`: Untuk menyimpan data user (admin)
  - id (Primary Key)
  - username
  - password (hashed)
  - created_at

- Tabel `mahasiswa`: Untuk menyimpan data mahasiswa
  - id (Primary Key)
  - nama
  - nim (Unique)
  - jurusan
  - angkatan
  - created_at

## Instalasi

1. Clone atau download repository ini
2. Simpan di direktori htdocs XAMPP (C:/xampp/htdocs/data_mahasiswa)
3. Buat database dan import file databases.sql di phpMyAdmin
4. Akses aplikasi melalui browser: http://localhost/data_mahasiswa

## Cara Penggunaan

1. Register akun baru
2. Login dengan akun yang telah didaftarkan
3. Gunakan menu di dashboard untuk mengelola data mahasiswa

## Persyaratan Sistem

- PHP 7.0 atau lebih tinggi
- MySQL/MariaDB
- Web server (XAMPP/WAMP/LAMP)

## Kredensial Default

(Tidak ada kredensial default, silakan register terlebih dahulu)"# -Reservasi_Hotel" 
