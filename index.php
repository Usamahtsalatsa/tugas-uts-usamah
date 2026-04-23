<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $query = "SELECT * FROM barang WHERE nama_barang LIKE '%$keyword%' ORDER BY id_barang DESC";
} else {
    $query = "SELECT * FROM barang ORDER BY id_barang DESC";
}$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $query = "SELECT * FROM barang WHERE nama_barang LIKE '%$keyword%' ORDER BY id_barang DESC";
} elseif (isset($_GET['stok_menipis']) && $_GET['stok_menipis'] == 1) {
    // Filter untuk stok menipis (stok ≤ 5)
    $query = "SELECT * FROM barang WHERE stok <= 5 ORDER BY stok ASC";
} else {
    $query = "SELECT * FROM barang ORDER BY id_barang DESC";
}

$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang | Sistem Inventori Raja Dekor</title>
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
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
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
                <a href="index.php" class="active">📦 Data Barang</a>
                <a href="tambah.php">➕ Tambah Barang</a>
                <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
            </div>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>📋 Data Barang</h2>
                    <div>
                        <span class="badge bg-secondary"><?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>

                <?php if(isset($_GET['sukses'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        if($_GET['sukses'] == 'tambah') echo "✅ Data berhasil ditambahkan!";
                        if($_GET['sukses'] == 'edit') echo "✏️ Data berhasil diupdate!";
                        if($_GET['sukses'] == 'hapus') echo "🗑️ Data berhasil dihapus!";
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <?php if(isset($_GET['stok_menipis']) && $_GET['stok_menipis'] == 1): ?>
    <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
        <strong>⚠️ Mode Filter Aktif!</strong> Menampilkan barang dengan stok menipis (≤ 5).
        <a href="index.php" class="alert-link">Klik disini</a> untuk melihat semua barang.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
                    <div class="card-body">
                        <a href="tambah.php" class="btn btn-success mb-3">➕ Tambah Barang</a>
                        <a href="export_excel.php" class="btn btn-info mb-3 text-white">📥 Export Excel</a>
                        
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-8">
                                <input type="text" name="cari" class="form-control" placeholder="Cari nama barang..." value="<?php echo htmlspecialchars($keyword); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">🔍 Cari</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Gambar</th>
                                        <th>ID</th>
                                        <th>Nama Barang</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr <?php if($row['stok'] <= 5) echo 'class="table-danger"'; ?>>
                                                <td>
                                                    <img src="uploads/<?php echo !empty($row['gambar']) ? $row['gambar'] : 'default.jpg'; ?>" 
                                                         class="product-img" 
                                                         alt="<?php echo htmlspecialchars($row['nama_barang']); ?>"
                                                         onerror="this.src='uploads/default.jpg'">
                                                </td>
                                                <td><?php echo $row['id_barang']; ?></td>
                                                <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                                <td><?php echo $row['stok']; ?></td>
                                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_masuk'])); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $row['id_barang']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                                    <a href="hapus.php?id=<?php echo $row['id_barang']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">🗑️ Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada data barang</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>