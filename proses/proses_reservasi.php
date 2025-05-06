<?php
session_start();
require '../koneksi.php';

// Validasi input
if (
    empty($_POST['nama_tamu']) || 
    empty($_POST['email']) || 
    empty($_POST['no_telepon']) || 
    empty($_POST['no_identitas']) || 
    empty($_POST['jenis_identitas']) || 
    empty($_POST['alamat']) || 
    empty($_POST['tanggal_checkin']) || 
    empty($_POST['tanggal_checkout']) || 
    empty($_POST['jumlah_tamu']) || 
    empty($_POST['id_jenis'])
) {
    header("Location: ../views/reservasi.php?error=empty");
    exit;
}

// Ambil data dari form
$nama_tamu = mysqli_real_escape_string($conn, $_POST['nama_tamu']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
$no_identitas = mysqli_real_escape_string($conn, $_POST['no_identitas']);
$jenis_identitas = mysqli_real_escape_string($conn, $_POST['jenis_identitas']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
$tanggal_checkin = $_POST['tanggal_checkin'];
$tanggal_checkout = $_POST['tanggal_checkout'];
$jumlah_tamu = (int)$_POST['jumlah_tamu'];
$id_jenis = (int)$_POST['id_jenis'];
$catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';

// Validasi tanggal
if (strtotime($tanggal_checkout) <= strtotime($tanggal_checkin)) {
    header("Location: ../views/reservasi.php?error=date");
    exit;
}

// Hitung jumlah hari
$checkin = new DateTime($tanggal_checkin);
$checkout = new DateTime($tanggal_checkout);
$interval = $checkin->diff($checkout);
$jumlah_hari = $interval->days;

// Ambil data jenis kamar untuk mendapatkan harga
$query_jenis = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar WHERE id_jenis = $id_jenis");
$jenis_kamar = mysqli_fetch_assoc($query_jenis);
$harga_per_malam = $jenis_kamar['harga'];

// Hitung total harga
$total_harga = $harga_per_malam * $jumlah_hari;

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Simpan data tamu
    $query_tamu = mysqli_query($conn, "INSERT INTO tabel_tamu (nama_tamu, email, no_telepon, alamat, jenis_identitas, no_identitas) 
                                      VALUES ('$nama_tamu', '$email', '$no_telepon', '$alamat', '$jenis_identitas', '$no_identitas')");
    $id_tamu = mysqli_insert_id($conn);
    
    // Generate kode booking
    $kode_booking = 'HRS-' . date('Ymd') . rand(1000, 9999);
    
    // Simpan data reservasi
    $query_reservasi = mysqli_query($conn, "INSERT INTO tabel_reservasi (id_tamu, kode_booking, tanggal_checkin, tanggal_checkout, jumlah_tamu, total_harga, status, catatan) 
                                          VALUES ($id_tamu, '$kode_booking', '$tanggal_checkin', '$tanggal_checkout', $jumlah_tamu, $total_harga, 'pending', '$catatan')");
    $id_reservasi = mysqli_insert_id($conn);
    
    // Cari kamar yang tersedia untuk jenis kamar yang dipilih
    $query_kamar = mysqli_query($conn, "SELECT * FROM tabel_kamar WHERE id_jenis = $id_jenis AND status = 'tersedia' LIMIT 1");
    
    if (mysqli_num_rows($query_kamar) > 0) {
        $kamar = mysqli_fetch_assoc($query_kamar);
        $id_kamar = $kamar['id_kamar'];
        
        // Simpan detail reservasi
        $query_detail = mysqli_query($conn, "INSERT INTO tabel_detail_reservasi (id_reservasi, id_kamar) VALUES ($id_reservasi, $id_kamar)");
        
        // Update status kamar menjadi 'dipesan'
        $query_update_kamar = mysqli_query($conn, "UPDATE tabel_kamar SET status = 'dipesan' WHERE id_kamar = $id_kamar");
        
        // Simpan data pembayaran
        $query_pembayaran = mysqli_query($conn, "INSERT INTO tabel_pembayaran (id_reservasi, jumlah, metode_pembayaran, status_pembayaran, tanggal_pembayaran) 
                                              VALUES ($id_reservasi, $total_harga, 'transfer_bank', 'pending', NOW())");
        
        // Commit transaksi
        mysqli_commit($conn);
        
        // Redirect ke halaman sukses
        header("Location: ../views/reservasi_sukses.php?kode=$kode_booking");
        exit;
    } else {
        // Rollback transaksi jika tidak ada kamar tersedia
        mysqli_rollback($conn);
        header("Location: ../views/reservasi.php?error=kamar");
        exit;
    }
} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($conn);
    header("Location: ../views/reservasi.php?error=database");
    exit;
}