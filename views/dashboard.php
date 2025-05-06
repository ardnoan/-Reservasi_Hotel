<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require '../koneksi.php';
$data = mysqli_query($conn, "SELECT * FROM mahasiswa ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Manajemen Data Mahasiswa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">Sistem Manajemen Data Mahasiswa</div>
            <div class="nav-links">
                <a href="../logout.php">Logout</a>
            </div>
        </div>
        
        <h2>Data Mahasiswa</h2>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success') { ?>
            <div class="alert alert-success">
                <?php 
                    if (isset($_GET['action'])) {
                        if ($_GET['action'] == 'add') {
                            echo "Data mahasiswa berhasil ditambahkan!";
                        } elseif ($_GET['action'] == 'edit') {
                            echo "Data mahasiswa berhasil diupdate!";
                        } elseif ($_GET['action'] == 'delete') {
                            echo "Data mahasiswa berhasil dihapus!";
                        }
                    } 
                ?>
            </div>
        <?php } ?>
        
        <div style="margin: 20px 0; display: flex; justify-content: flex-end;">
            <a href="tambah.php" class="btn">+ Tambah Mahasiswa</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Jurusan</th>
                    <th>Angkatan</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                if (mysqli_num_rows($data) > 0):
                    while($row = mysqli_fetch_assoc($data)): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['nim']) ?></td>
                    <td><?= htmlspecialchars($row['jurusan']) ?></td>
                    <td><?= htmlspecialchars($row['angkatan']) ?></td>
                    <td style="display: flex; justify-content: center; gap: 5px;">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                        <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data mahasiswa</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
