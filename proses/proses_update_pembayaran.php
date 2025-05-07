<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../views/login.php");
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    header("Location: ../views/dashboard.php?error=invalid_params");
    exit;
}

$id_pembayaran = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validasi status
if (!in_array($status, ['success', 'failed'])) {
    header("Location: ../views/dashboard.php?error=invalid_status");
    exit;
}

// Ambil data pembayaran dan reservasi terkait
$query_pembayaran = mysqli_query($conn, "
    SELECT p.*, r.status as reservation_status 
    FROM tabel_pembayaran p
    JOIN tabel_reservasi r ON p.id_reservasi = r.id_reservasi
    WHERE p.id_pembayaran = $id_pembayaran
");

if (!$query_pembayaran || mysqli_num_rows($query_pembayaran) == 0) {
    header("Location: ../views/dashboard.php?error=payment_not_found");
    exit;
}

$pembayaran = mysqli_fetch_assoc($query_pembayaran);
$id_reservasi = $pembayaran['id_reservasi'];
$current_payment_status = $pembayaran['status_pembayaran'];
$reservation_status = $pembayaran['reservation_status'];

// Validasi alur status (prevent bypass)
$valid_transition = true;
$error_message = "";

// Cek apakah pembayaran sudah selesai (tidak bisa diubah lagi)
if ($current_payment_status == 'success') {
    $valid_transition = false;
    $error_message = "Pembayaran sudah berhasil, tidak dapat diubah kembali";
}

// Cek apakah reservasi sudah dibatalkan
if ($reservation_status == 'cancelled') {
    $valid_transition = false;
    $error_message = "Tidak dapat memproses pembayaran untuk reservasi yang telah dibatalkan";
}

// Jika transisi tidak valid, redirect dengan pesan error
if (!$valid_transition) {
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../views/detail_reservasi.php?id='.$id_reservasi;
    header("Location: $redirect?error=invalid_transition&message=" . urlencode($error_message));
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Update status pembayaran
    $update_pembayaran = mysqli_query($conn, "
        UPDATE tabel_pembayaran
        SET status_pembayaran = '$status',
            tanggal_pembayaran = NOW()
        WHERE id_pembayaran = $id_pembayaran
    ");

    if (!$update_pembayaran) {
        throw new Exception("Gagal memperbarui status pembayaran: " . mysqli_error($conn));
    }

    // Jika status pembayaran berhasil (success), update status reservasi jadi confirmed
    if ($status == 'success' && $reservation_status == 'pending') {
        $update_reservasi = mysqli_query($conn, "
            UPDATE tabel_reservasi
            SET status = 'confirmed'
            WHERE id_reservasi = $id_reservasi
        ");
        if (!$update_reservasi) {
            throw new Exception("Gagal memperbarui status reservasi: " . mysqli_error($conn));
        }
    }

    // Jika status pembayaran failed, update status reservasi jadi cancelled (kecuali sudah cancelled)
    if ($status == 'failed' && $reservation_status != 'cancelled') {
        $update_reservasi = mysqli_query($conn, "
            UPDATE tabel_reservasi
            SET status = 'cancelled'
            WHERE id_reservasi = $id_reservasi
        ");
        if (!$update_reservasi) {
            throw new Exception("Gagal membatalkan reservasi: " . mysqli_error($conn));
        }
    }

    // Catat log aktivitas
    $aktivitas = "Memperbarui status pembayaran #$id_pembayaran menjadi " . ($status == 'success' ? 'Berhasil' : 'Gagal');
    $id_user = $_SESSION['user_id'];
    $log_query = mysqli_query($conn, "
        INSERT INTO tabel_log (id_user, aktivitas, created_at)
        VALUES ($id_user, '$aktivitas', NOW())
    ");

    if (!$log_query) {
        throw new Exception("Gagal mencatat log aktivitas: " . mysqli_error($conn));
    }

    // Commit transaksi
    mysqli_commit($conn);

    // Redirect kembali ke halaman detail reservasi
    header("Location: ../views/detail_reservasi.php?id=$id_reservasi&success=payment_updated");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($conn);
    
    // Log error
    error_log("Payment update error: " . $e->getMessage());
    
    // Redirect dengan pesan error
    header("Location: ../views/detail_reservasi.php?id=$id_reservasi&error=database&message=" . urlencode($e->getMessage()));
    exit;
}
?>