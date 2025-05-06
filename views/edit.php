<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

require '../koneksi.php';

// Validasi id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id=$id");

// Cek apakah data ada
if (mysqli_num_rows($data) == 0) {
    header("Location: dashboard.php");
    exit;
}

$row = mysqli_fetch_assoc($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa - Sistem Manajemen Data Mahasiswa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">Sistem Manajemen Data Mahasiswa</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
        
        <h2>Edit Data Mahasiswa</h2>
        
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert">
                <?php 
                    if ($_GET['error'] == 'empty') {
                        echo "Semua field harus diisi!";
                    } elseif ($_GET['error'] == 'nim') {
                        echo "NIM sudah digunakan oleh mahasiswa lain!";
                    } else {
                        echo "Terjadi kesalahan, silahkan coba lagi.";
                    }
                ?>
            </div>
        <?php } ?>
        
        <form action="../proses/proses_edit.php" method="post">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($row['nama']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="nim">NIM</label>
                <input type="text" id="nim" name="nim" value="<?= htmlspecialchars($row['nim']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="jurusan">Jurusan</label>
                <input type="text" id="jurusan" name="jurusan" value="<?= htmlspecialchars($row['jurusan']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="angkatan">Angkatan</label>
                <input type="number" id="angkatan" name="angkatan" min="1990" max="2030" value="<?= htmlspecialchars($row['angkatan']) ?>" required>
            </div>
            
            <button type="submit">Update</button>
            <a href="dashboard.php" style="margin-left: 10px;">Batal</a>
        </form>
    </div>
</body>
</html>
