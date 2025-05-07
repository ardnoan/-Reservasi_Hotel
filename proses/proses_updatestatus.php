<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: ../views/dashboard.php?error=invalid_params");
    exit;
}

$id_reservasi = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validasi status
$valid_statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header("Location: ../views/dashboard.php?error=invalid_status");
    exit;
}

// Verify reservation exists and get current status
$check_query = mysqli_query($conn, "SELECT r.id_reservasi, r.status, p.status_pembayaran 
                                    FROM tabel_reservasi r 
                                    LEFT JOIN tabel_pembayaran p ON r.id_reservasi = p.id_reservasi 
                                    WHERE r.id_reservasi = $id_reservasi");

if (!$check_query || mysqli_num_rows($check_query) == 0) {
    header("Location: ../views/dashboard.php?error=reservation_not_found");
    exit;
}

$reservation_data = mysqli_fetch_assoc($check_query);
$current_status = $reservation_data['status'];
$payment_status = $reservation_data['status_pembayaran'];

// Validasi alur status (prevent bypass)
$valid_transition = true;
$error_message = "";

// Aturan transisi status
switch ($status) {
    case 'confirmed':
        // Hanya bisa confirm jika status saat ini pending
        if ($current_status != 'pending') {
            $valid_transition = false;
            $error_message = "Reservasi harus berstatus 'pending' untuk dapat dikonfirmasi";
        }
        // Dan pembayaran harus sudah success
        if ($payment_status != 'success') {
            $valid_transition = false;
            $error_message = "Pembayaran harus diselesaikan terlebih dahulu sebelum konfirmasi";
        }
        break;
        
    case 'checked_in':
        // Hanya bisa check-in jika status saat ini confirmed
        if ($current_status != 'confirmed') {
            $valid_transition = false;
            $error_message = "Reservasi harus berstatus 'confirmed' untuk dapat melakukan check-in";
        }
        // Dan pembayaran harus sudah success
        if ($payment_status != 'success') {
            $valid_transition = false;
            $error_message = "Pembayaran harus diselesaikan terlebih dahulu sebelum check-in";
        }
        break;
        
    case 'checked_out':
        // Hanya bisa check-out jika status saat ini checked_in
        if ($current_status != 'checked_in') {
            $valid_transition = false;
            $error_message = "Reservasi harus berstatus 'checked_in' untuk dapat melakukan check-out";
        }
        // Dan pembayaran harus sudah success
        if ($payment_status != 'success') {
            $valid_transition = false;
            $error_message = "Pembayaran harus diselesaikan terlebih dahulu sebelum check-out";
        }
        break;
        
    case 'cancelled':
        // Bisa cancel jika belum checked_out
        if ($current_status == 'checked_out') {
            $valid_transition = false;
            $error_message = "Reservasi yang sudah check-out tidak dapat dibatalkan";
        }
        break;
}

// Jika transisi tidak valid, redirect dengan pesan error
if (!$valid_transition) {
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/dashboard.php';
    header("Location: $redirect?error=invalid_transition&message=" . urlencode($error_message));
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Update status reservasi
    $query_update = mysqli_query($conn, "UPDATE tabel_reservasi SET status = '$status' WHERE id_reservasi = $id_reservasi");
    
    if (!$query_update) {
        throw new Exception("Failed to update reservation status: " . mysqli_error($conn));
    }

    // Jika status dibatalkan, update status pembayaran jadi failed (kecuali sudah success/failed)
    if ($status == 'cancelled') {
        $update_pembayaran = mysqli_query($conn, "
            UPDATE tabel_pembayaran
            SET status_pembayaran = 'failed'
            WHERE id_reservasi = $id_reservasi AND status_pembayaran NOT IN ('success','failed')
        ");
        // Tidak perlu throw error jika tidak ada baris yang diupdate
    }
   
    // Update status kamar jika diperlukan
    if ($status == 'cancelled') {
        // Jika dibatalkan, kembalikan status kamar menjadi tersedia
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        if (!$query_detail) {
            throw new Exception("Failed to fetch room details: " . mysqli_error($conn));
        }
        
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            $id_kamar = $detail['id_kamar'];
            $update_kamar = mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = $id_kamar");
            if (!$update_kamar) {
                throw new Exception("Failed to update room status: " . mysqli_error($conn));
            }
        }
    } elseif ($status == 'checked_in') {
        // Jika check-in, update status kamar menjadi 'ditempati'
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        if (!$query_detail) {
            throw new Exception("Failed to fetch room details: " . mysqli_error($conn));
        }
        
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            $id_kamar = $detail['id_kamar'];
            $update_kamar = mysqli_query($conn, "UPDATE tabel_kamar SET status = 'ditempati' WHERE id_kamar = $id_kamar");
            if (!$update_kamar) {
                throw new Exception("Failed to update room status: " . mysqli_error($conn));
            }
        }
    } elseif ($status == 'checked_out') {
        // Jika check-out, update status kamar menjadi 'tersedia'
        $query_detail = mysqli_query($conn, "SELECT id_kamar FROM tabel_detail_reservasi WHERE id_reservasi = $id_reservasi");
        if (!$query_detail) {
            throw new Exception("Failed to fetch room details: " . mysqli_error($conn));
        }
        
        while ($detail = mysqli_fetch_assoc($query_detail)) {
            $id_kamar = $detail['id_kamar'];
            $update_kamar = mysqli_query($conn, "UPDATE tabel_kamar SET status = 'tersedia' WHERE id_kamar = $id_kamar");
            if (!$update_kamar) {
                throw new Exception("Failed to update room status: " . mysqli_error($conn));
            }
        }
    }
    
    // Catat log aktivitas
    $aktivitas = "Mengubah status reservasi #$id_reservasi menjadi " . ucfirst($status);
    $id_user = $_SESSION['user_id'];
    $log_query = mysqli_query($conn, "INSERT INTO tabel_log (id_user, aktivitas, created_at) VALUES ($id_user, '$aktivitas', NOW())");
    
    // Commit transaksi
    mysqli_commit($conn);
   
    // Redirect ke halaman sebelumnya dengan pesan sukses
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/dashboard.php';
    header("Location: $redirect?success=status_updated");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($conn);
    
    // Log error
    error_log("Reservation status update error: " . $e->getMessage());
    
    // Redirect dengan pesan error
    header("Location: ../views/dashboard.php?error=database&message=" . urlencode($e->getMessage()));
    exit;
}
?>