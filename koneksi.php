<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_hotel";

// Koneksi ke MySQL
$conn = mysqli_connect($host, $user, $pass);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Cek apakah database sudah ada
$db_check = mysqli_query($conn, "SHOW DATABASES LIKE '$db'");
if (mysqli_num_rows($db_check) == 0) {
    // Buat database jika belum ada
    mysqli_query($conn, "CREATE DATABASE $db");
}

// Pilih database
mysqli_select_db($conn, $db);

// Cek dan buat tabel-tabel yang diperlukan
// Tabel users
$tabel_users = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($tabel_users) == 0) {
    $query_users = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        role ENUM('admin', 'staff') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $query_users);
    
    // Insert default admin user
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role) VALUES 
    ('admin', '$default_password', 'Administrator', 'admin'),
    ('staff', '$default_password', 'Staff Hotel', 'staff')");
}

// Tabel tamu
$tabel_tamu = mysqli_query($conn, "SHOW TABLES LIKE 'tabel_tamu'");
if (mysqli_num_rows($tabel_tamu) == 0) {
    $query_tamu = "CREATE TABLE tabel_tamu (
        id_tamu INT AUTO_INCREMENT PRIMARY KEY,
        nama_tamu VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        no_telepon VARCHAR(20) NOT NULL,
        alamat TEXT,
        no_identitas VARCHAR(30) NOT NULL,
        jenis_identitas ENUM('KTP', 'SIM', 'Passport') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $query_tamu);
}

// Tabel jenis kamar
$tabel_jenis_kamar = mysqli_query($conn, "SHOW TABLES LIKE 'tabel_jenis_kamar'");
if (mysqli_num_rows($tabel_jenis_kamar) == 0) {
    $query_jenis_kamar = "CREATE TABLE tabel_jenis_kamar (
        id_jenis INT AUTO_INCREMENT PRIMARY KEY,
        nama_jenis VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        harga DECIMAL(10,2) NOT NULL,
        kapasitas INT NOT NULL,
        fasilitas TEXT,
        gambar VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $query_jenis_kamar);
    
    // Insert data contoh
    mysqli_query($conn, "INSERT INTO tabel_jenis_kamar (nama_jenis, deskripsi, harga, kapasitas, fasilitas, gambar) VALUES
    ('Standard Room', 'Kamar standar dengan fasilitas dasar yang nyaman', 500000, 2, 'AC, TV, Kamar Mandi Dalam, WiFi', 'standard.jpg'),
    ('Deluxe Room', 'Kamar dengan ukuran lebih besar dan fasilitas tambahan', 800000, 2, 'AC, TV 42 inch, Kamar Mandi Dalam dengan Bath Tub, WiFi, Minibar', 'deluxe.jpg'),
    ('Family Room', 'Kamar untuk keluarga dengan ruang yang luas', 1200000, 4, 'AC, TV 50 inch, Kamar Mandi Dalam dengan Bath Tub, WiFi, Minibar, Ruang Keluarga', 'family.jpg'),
    ('Executive Suite', 'Suite mewah dengan pemandangan terbaik dan fasilitas terlengkap', 2000000, 2, 'AC, Smart TV 55 inch, Kamar Mandi Premium dengan Jacuzzi, WiFi, Minibar, Ruang Tamu, Dapur Kecil', 'suite.jpg')");
}

// Tabel kamar
$tabel_kamar = mysqli_query($conn, "SHOW TABLES LIKE 'tabel_kamar'");
if (mysqli_num_rows($tabel_kamar) == 0) {
    $query_kamar = "CREATE TABLE tabel_kamar (
        id_kamar INT AUTO_INCREMENT PRIMARY KEY,
        nomor_kamar VARCHAR(10) NOT NULL UNIQUE,
        id_jenis INT NOT NULL,
        status ENUM('tersedia', 'terisi', 'pemeliharaan') NOT NULL DEFAULT 'tersedia',
        lantai INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_jenis) REFERENCES tabel_jenis_kamar(id_jenis) ON DELETE CASCADE
    )";
    mysqli_query($conn, $query_kamar);
    
    // Insert data contoh
    mysqli_query($conn, "INSERT INTO tabel_kamar (nomor_kamar, id_jenis, status, lantai) VALUES
    ('101', 1, 'tersedia', 1),
    ('102', 1, 'tersedia', 1),
    ('103', 1, 'tersedia', 1),
    ('201', 2, 'tersedia', 2),
    ('202', 2, 'tersedia', 2),
    ('301', 3, 'tersedia', 3),
    ('401', 4, 'tersedia', 4)");
}

// Tabel reservasi
$tabel_reservasi = mysqli_query($conn, "SHOW TABLES LIKE 'tabel_reservasi'");
if (mysqli_num_rows($tabel_reservasi) == 0) {
    $query_reservasi = "CREATE TABLE tabel_reservasi (
        id_reservasi INT AUTO_INCREMENT PRIMARY KEY,
        kode_booking VARCHAR(20) NOT NULL UNIQUE,
        id_tamu INT NOT NULL,
        tanggal_checkin DATE NOT NULL,
        tanggal_checkout DATE NOT NULL,
        jumlah_tamu INT NOT NULL,
        total_harga DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') NOT NULL DEFAULT 'pending',
        catatan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_tamu) REFERENCES tabel_tamu(id_tamu) ON DELETE CASCADE
    )";
    mysqli_query($conn, $query_reservasi);
}

// Tabel detail reservasi
$tabel_detail_reservasi = mysqli_query($conn, "SHOW TABLES LIKE 'tabel_detail_reservasi'");
if (mysqli_num_rows($tabel_detail_reservasi) == 0) {
    $query_detail_reservasi = "CREATE TABLE tabel_detail_reservasi (
        id_detail INT AUTO_INCREMENT PRIMARY KEY,
        id_reservasi INT NOT NULL,
        id_kamar INT NOT NULL,
        harga DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_reservasi) REFERENCES tabel_reservasi(id_reservasi) ON DELETE CASCADE,
        FOREIGN KEY (id_kamar) REFERENCES tabel_kamar(id_kamar) ON DELETE CASCADE
    )";
    mysqli_query($conn, $query_detail_reservasi);
}

// Tabel pembayaran
$tabel_pembayaran = mysqli_query($conn, "SHOW TABLES LIKE 'tabel_pembayaran'");
if (mysqli_num_rows($tabel_pembayaran) == 0) {
    $query_pembayaran = "CREATE TABLE tabel_pembayaran (
        id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
        id_reservasi INT NOT NULL,
        jumlah DECIMAL(10,2) NOT NULL,
        metode_pembayaran ENUM('tunai', 'kartu_kredit', 'transfer', 'debit') NOT NULL,
        status_pembayaran ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
        tanggal_pembayaran DATETIME NOT NULL,
        bukti_pembayaran VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_reservasi) REFERENCES tabel_reservasi(id_reservasi) ON DELETE CASCADE
    )";
    mysqli_query($conn, $query_pembayaran);
}