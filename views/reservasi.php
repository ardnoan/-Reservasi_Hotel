<?php
session_start();
require '../koneksi.php';

// Cek apakah ada id_jenis yang dipilih
$id_jenis = isset($_GET['id_jenis']) ? $_GET['id_jenis'] : '';

// Ambil data jenis kamar
if (!empty($id_jenis)) {
    $query_jenis = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar WHERE id_jenis = $id_jenis");
    $jenis_kamar = mysqli_fetch_assoc($query_jenis);
} else {
    $query_jenis = mysqli_query($conn, "SELECT * FROM tabel_jenis_kamar ORDER BY harga ASC");
}

// Hari ini sebagai tanggal minimal check-in
$today = date('Y-m-d');
// Tanggal besok sebagai tanggal minimal check-out
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - Hotel Reservation System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        
        <h2>Formulir Reservasi</h2>
        
        <?php if (isset($_GET['error'])): ?>
        <div class="alert">
            <?php
            if ($_GET['error'] == 'empty') {
                echo "Semua field harus diisi!";
            } elseif ($_GET['error'] == 'date') {
                echo "Tanggal check-out harus setelah tanggal check-in!";
            } elseif ($_GET['error'] == 'kamar') {
                echo "Kamar tidak tersedia untuk tanggal yang dipilih!";
            } else {
                echo "Terjadi kesalahan, silahkan coba lagi.";
            }
            ?>
        </div>
        <?php endif; ?>
        
        <div class="form-container">
            <?php if (!empty($id_jenis) && $jenis_kamar): ?>
            <div class="selected-room">
                <h3>Kamar yang Dipilih</h3>
                <p><strong>Tipe Kamar:</strong> <?= $jenis_kamar['nama_jenis'] ?></p>
                <p><strong>Harga:</strong> Rp <?= number_format($jenis_kamar['harga'], 0, ',', '.') ?> / malam</p>
                <p><strong>Kapasitas:</strong> <?= $jenis_kamar['kapasitas'] ?> orang</p>
                <p><strong>Fasilitas:</strong> <?= $jenis_kamar['fasilitas'] ?></p>
            </div>
            <?php endif; ?>
            
            <form action="../proses/proses_reservasi.php" method="post">
                <?php if (!empty($id_jenis)): ?>
                <input type="hidden" name="id_jenis" value="<?= $id_jenis ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="nama_tamu">Nama Lengkap</label>
                            <input type="text" id="nama_tamu" name="nama_tamu" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="no_telepon">Nomor Telepon</label>
                            <input type="text" id="no_telepon" name="no_telepon" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="no_identitas">Nomor Identitas (KTP/SIM/Passport)</label>
                            <input type="text" id="no_identitas" name="no_identitas" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_identitas">Jenis Identitas</label>
                            <select id="jenis_identitas" name="jenis_identitas" required>
                                <option value="">Pilih</option>
                                <option value="KTP">KTP</option>
                                <option value="SIM">SIM</option>
                                <option value="Passport">Passport</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" required></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="tanggal_checkin">Tanggal Check-in</label>
                            <input type="date" id="tanggal_checkin" name="tanggal_checkin" min="<?= $today ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_checkout">Tanggal Check-out</label>
                            <input type="date" id="tanggal_checkout" name="tanggal_checkout" min="<?= $tomorrow ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="jumlah_tamu">Jumlah Tamu</label>
                            <input type="number" id="jumlah_tamu" name="jumlah_tamu" min="1" max="10" value="1" required>
                        </div>
                        
                        <?php if (empty($id_jenis)): ?>
                        <div class="form-group">
                            <label for="id_jenis">Tipe Kamar</label>
                            <select id="id_jenis" name="id_jenis" required>
                                <option value="">Pilih Tipe Kamar</option>
                                <?php while($row = mysqli_fetch_assoc($query_jenis)): ?>
                                <option value="<?= $row['id_jenis'] ?>"><?= $row['nama_jenis'] ?> - Rp <?= number_format($row['harga'], 0, ',', '.') ?> / malam</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="catatan">Catatan Tambahan</label>
                            <textarea id="catatan" name="catatan"></textarea>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn">Pesan Sekarang</button>
            </form>
        </div>
    </div>
    
    <script>
        // Fungsi untuk memastikan tanggal checkout setelah tanggal checkin
        document.getElementById('tanggal_checkin').addEventListener('change', function() {
            const checkin = this.value;
            const checkout = document.getElementById('tanggal_checkout');
            checkout.min = new Date(new Date(checkin).getTime() + 86400000).toISOString().split('T')[0];
            
            if (checkout.value && new Date(checkout.value) <= new Date(checkin)) {
                checkout.value = checkout.min;
            }
        });
    </script>
</body>
</html>