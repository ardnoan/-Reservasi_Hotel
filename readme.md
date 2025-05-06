# Hotel Reservation System

Proyek ini adalah sistem reservasi hotel berbasis web yang memungkinkan pengguna untuk memesan kamar, mengelola reservasi, dan mengelola data hotel.

## Struktur Folder

Berikut adalah struktur folder dan deskripsi masing-masing folder/file:

### Root Folder
- **index.php**  
  Halaman utama website yang menampilkan informasi hotel dan daftar kamar yang tersedia.

- **koneksi.php**  
  File untuk mengatur koneksi ke database.

- **logout.php**  
  File untuk menangani proses logout pengguna.

### Folder `assets/`
Berisi file statis seperti gambar dan JavaScript.
- **images/**  
  Folder untuk menyimpan gambar yang digunakan di website.
- **js/**  
  Folder untuk menyimpan file JavaScript.

### Folder `components/`
Berisi komponen yang dapat digunakan kembali di berbagai halaman.
- **navbar.php**  
  Komponen untuk menampilkan navigasi utama.
- **footer.php**  
  Komponen untuk menampilkan footer.
- **alert.php**  
  Komponen untuk menampilkan pesan notifikasi.
- **room_card.php**  
  Komponen untuk menampilkan informasi kamar dalam bentuk kartu.

### Folder `css/`
Berisi file CSS untuk styling.
- **style.css**  
  File utama untuk styling seluruh halaman website.

### Folder `db/`
Berisi file terkait database.
- **databases.sql**  
  File SQL untuk membuat dan mengisi database dengan data awal.

### Folder `proses/`
Berisi file PHP untuk menangani proses backend.
- **proses_delete_kamar.php**  
  Menghapus data kamar.
- **proses_delete_reservasi.php**  
  Menghapus data reservasi.
- **proses_delete_user.php**  
  Menghapus data pengguna.
- **proses_edit_user.php**  
  Memperbarui data pengguna.
- **proses_reservasi.php**  
  Menangani proses reservasi kamar.
- **proses_reset_password.php**  
  Mengatur ulang kata sandi pengguna.
- **proses_tambah_kamar.php**  
  Menambahkan data kamar baru.
- **proses_tambah_user.php**  
  Menambahkan data pengguna baru.
- **proses_update_kamar.php**  
  Memperbarui data kamar.
- **proses_update_pembayaran.php**  
  Memperbarui status pembayaran.
- **proses_update_status_kamar.php**  
  Memperbarui status kamar.
- **proses_update_status_reservasi.php**  
  Memperbarui status reservasi.

### Folder `views/`
Berisi file PHP untuk halaman frontend.
- **cek_reservasi.php**  
  Halaman untuk memeriksa status reservasi berdasarkan kode booking.
- **dashboard.php**  
  Halaman dashboard untuk admin/staff.
- **kamar.php**  
  Halaman daftar kamar hotel.
- **login.php**  
  Halaman login untuk admin/staff.
- **manage_kamar.php**  
  Halaman untuk mengelola data kamar.
- **manage_reservasi.php**  
  Halaman untuk mengelola data reservasi.
- **manage_users.php**  
  Halaman untuk mengelola data pengguna.
- **reservasi.php**  
  Halaman formulir reservasi kamar.
- **reservasi_sukses.php**  
  Halaman konfirmasi setelah reservasi berhasil.
- **tambah_kamar.php**  
  Halaman untuk menambahkan kamar baru.
- **tambah_users.php**  
  Halaman untuk menambahkan pengguna baru.
- **edit_kamar.php**  
  Halaman untuk mengedit data kamar.
- **edit_users.php**  
  Halaman untuk mengedit data pengguna.
- **detail_reservasi.php**  
  Halaman untuk melihat detail reservasi.

## Cara Menggunakan
1. Pastikan Anda memiliki server lokal seperti XAMPP.
2. Import file `db/databases.sql` ke database MySQL Anda.
3. Konfigurasi file `koneksi.php` sesuai dengan pengaturan database Anda.
4. Jalankan proyek melalui browser dengan mengakses `http://localhost/ReversiHotel`.

## Fitur Utama
- Reservasi kamar hotel.
- Manajemen data kamar, reservasi, dan pengguna.
- Sistem login untuk admin dan staff.
- Tampilan responsif dan user-friendly.

## Lisensi
Proyek ini dibuat untuk keperluan pembelajaran dan tidak memiliki lisensi resmi.