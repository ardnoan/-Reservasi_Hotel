<?php
session_start();
require '../koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah user adalah admin
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Get jenis kamar for dropdown
$query_jenis = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar ORDER BY nama_jenis ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kamar - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../components/navbar.php'; ?>

        <div class="form-container">
            <h2>Tambah Kamar Baru</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php if ($_GET['error'] == 'empty_fields'): ?>
                        Semua field harus diisi.
                    <?php elseif ($_GET['error'] == 'nomor_exists'): ?>
                        Nomor kamar sudah digunakan.
                    <?php elseif ($_GET['error'] == 'failed'): ?>
                        Gagal menambahkan kamar. Silakan coba lagi.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="../proses/proses_tambah_kamar.php" method="POST">
                <div class="form-group">
                    <label for="nomor_kamar">Nomor Kamar</label>
                    <input type="text" id="nomor_kamar" name="nomor_kamar" required>
                </div>

                <div class="form-group">
                    <label for="id_jenis">Tipe Kamar</label>
                    <select id="id_jenis" name="id_jenis" required>
                        <option value="">-- Pilih Tipe Kamar --</option>
                        <?php while ($jenis = mysqli_fetch_assoc($query_jenis)): ?>
                            <option value="<?= $jenis['id_jenis'] ?>"><?= $jenis['nama_jenis'] ?> - Rp <?= number_format($jenis['harga'], 0, ',', '.') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lantai">Lantai</label>
                    <input type="number" id="lantai" name="lantai" min="1" value="1" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="perbaikan">Dalam Perbaikan</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="manage_kamar.php" class="btn">Batal</a>
                </div>
            </form>
        </div>

        <?php include_once '../components/footer.php'; ?>

    </div>
</body>

</html>