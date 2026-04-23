<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $tanggal_masuk = mysqli_real_escape_string($koneksi, $_POST['tanggal_masuk']);
    $created_by = $_SESSION['username'];
    
    $gambar = 'default.jpg';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && $_FILES['gambar']['size'] > 0) {
        $target_dir = "uploads/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $gambar = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $gambar;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imageFileType, $allowed_types)) {
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = 'default.jpg';
                $pesan = "Gagal mengupload gambar.";
            }
        } else {
            $gambar = 'default.jpg';
            $pesan = "Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
    }
    
    $query = "INSERT INTO barang (nama_barang, harga, stok, tanggal_masuk, gambar, created_by) 
              VALUES ('$nama_barang', $harga, $stok, '$tanggal_masuk', '$gambar', '$created_by')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?sukses=tambah");
        exit();
    } else {
        $pesan = "Gagal menambah data: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang | Sistem Inventori Raja Dekor</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #34495e;
            padding-left: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 sidebar">
                <div class="text-center text-white py-4">
                    <h4>📦 RAJA DEKOR</h4>
                    <small>Inventori System</small>
                    <hr class="bg-white">
                </div>
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="index.php">📦 Data Barang</a>
                <a href="tambah.php" class="active">➕ Tambah Barang</a>
                <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
            </div>

            <div class="col-md-10 p-4">
                <div class="card shadow" style="max-width: 700px; margin: 0 auto;">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">➕ Tambah Barang</h4>
                        <small>Sistem Inventori - Raja Dekor</small>
                    </div>
                    <div class="card-body">
                        <?php if ($pesan): ?>
                            <div class="alert alert-danger"><?php echo $pesan; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Gambar Produk</label>
                                <input type="file" name="gambar" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF, WEBP</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stok" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">💾 Simpan</button>
                            <a href="index.php" class="btn btn-secondary">🔙 Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>