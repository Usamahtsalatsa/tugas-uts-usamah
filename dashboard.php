<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    header("Location: login.php");
    exit();
}

// Data statistik
$total_barang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang"))['total'];
$total_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) as total FROM barang"))['total'];
$total_nilai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(harga * stok) as total FROM barang"))['total'];
$barang_habis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE stok <= 5"))['total'];

// Data untuk grafik stok
$query_chart = "SELECT nama_barang, stok FROM barang ORDER BY id_barang";
$result_chart = mysqli_query($koneksi, $query_chart);
$nama_barang_arr = [];
$stok_arr = [];

while ($row = mysqli_fetch_assoc($result_chart)) {
    $nama_barang_arr[] = $row['nama_barang'];
    $stok_arr[] = $row['stok'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Sistem Inventori Raja Dekor</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
        .sidebar a.active {
            background: #1abc9c;
        }
        .card-stats {
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="text-center text-white py-4">
                    <h4>📦 RAJA DEKOR</h4>
                    <small>Inventori System</small>
                    <hr class="bg-white">
                </div>
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="index.php">📦 Data Barang</a>
                <a href="tambah.php">➕ Tambah Barang</a>
                <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h2>
                    <div class="text-end">
                        <small class="text-muted">Level: <?php echo $_SESSION['level']; ?></small><br>
                        <small><?php echo date('l, d F Y'); ?></small>
                    </div>
                </div>

                <!-- Statistik Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card card-stats bg-primary text-white">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Total Barang</h5>
                                    <h2 class="mb-0"><?php echo $total_barang; ?></h2>
                                </div>
                                <div class="icon-circle">📦</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-stats bg-success text-white">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Total Stok</h5>
                                    <h2 class="mb-0"><?php echo $total_stok; ?></h2>
                                </div>
                                <div class="icon-circle">📊</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-stats bg-info text-white">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Nilai Inventori</h5>
                                    <h5 class="mb-0">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></h5>
                                </div>
                                <div class="icon-circle">💰</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
    <div class="card card-stats bg-warning text-white" 
         style="cursor: pointer; position: relative;"
         onclick="window.location.href='index.php?stok_menipis=1'"
         onmouseenter="showTooltip(event, this)"
         onmouseleave="hideTooltip()">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Stok Menipis</h5>
                <h2 class="mb-0"><?php echo $barang_habis; ?></h2>
            </div>
            <div class="icon-circle">⚠️</div>
        </div>
    </div>
</div>

<!-- Tooltip Container -->
<div id="stokTooltip" style="display:none; position:fixed; background:#2c3e50; color:#fff; padding:12px 16px; border-radius:10px; font-size:13px; z-index:9999; box-shadow:0 4px 15px rgba(0,0,0,0.3); pointer-events:none; min-width:200px; border-left: 4px solid #ffc107;">
    <div style="font-weight:bold; margin-bottom:8px;">⚠️ BARANG STOK MENIPIS</div>
    <div id="tooltipContent">Loading...</div>
</div>

                <!-- Grafik Stok -->
                <div class="card mt-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">📊 Grafik Stok Barang</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="stokChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Daftar Barang & Stok Habis -->
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">📋 Daftar Barang</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php
                                    $top = mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang ORDER BY id_barang DESC LIMIT 5");
                                    while($row = mysqli_fetch_assoc($top)) {
                                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                                ' . $row['nama_barang'] . '
                                                <span class="badge bg-primary rounded-pill">Stok: ' . $row['stok'] . '</span>
                                              </li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">⚠️ Stok Hampir Habis (≤5)</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <?php
                                    $habis = mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang WHERE stok <= 5");
                                    if(mysqli_num_rows($habis) > 0) {
                                        while($row = mysqli_fetch_assoc($habis)) {
                                            echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                                    ' . $row['nama_barang'] . '
                                                    <span class="badge bg-danger rounded-pill">Sisa: ' . $row['stok'] . '</span>
                                                  </li>';
                                        }
                                    } else {
                                        echo '<li class="list-group-item text-success">✅ Semua stok aman</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('stokChart').getContext('2d');
        const stokChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($nama_barang_arr); ?>,
                datasets: [{
                    label: 'Stok Barang',
                    data: <?php echo json_encode($stok_arr); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Stok'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Nama Barang'
                        }
                    }
                }
            }
        });
        // Data stok menipis dari database
const stokMenipisData = <?php
    $query_habis_detail = "SELECT nama_barang, stok FROM barang WHERE stok <= 5 ORDER BY stok ASC";
    $result_habis_detail = mysqli_query($koneksi, $query_habis_detail);
    $data_habis = [];
    while($row = mysqli_fetch_assoc($result_habis_detail)) {
        $data_habis[] = $row;
    }
    echo json_encode($data_habis);
?>;

function showTooltip(event, element) {
    const tooltip = document.getElementById('stokTooltip');
    const contentDiv = document.getElementById('tooltipContent');
    
    // Isi tooltip dengan daftar barang
    if (stokMenipisData.length > 0) {
        let html = '';
        stokMenipisData.forEach(item => {
            // Tentukan warna badge berdasarkan stok
            let badgeColor = item.stok <= 2 ? '#dc3545' : '#ffc107';
            html += `<div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span>📦 ${item.nama_barang}</span>
                        <span style="background: ${badgeColor}; padding: 0px 8px; border-radius: 20px; font-size: 11px; font-weight: bold;">Sisa: ${item.stok}</span>
                     </div>`;
        });
        contentDiv.innerHTML = html;
    } else {
        contentDiv.innerHTML = '<div style="color: #a5d6a7;">✅ Semua stok aman</div>';
    }
    
    tooltip.style.display = 'block';
    
    // Posisikan tooltip di dekat cursor
    tooltip.style.left = (event.pageX + 15) + 'px';
    tooltip.style.top = (event.pageY - 40) + 'px';
}

function hideTooltip() {
    document.getElementById('stokTooltip').style.display = 'none';
}

// Update posisi tooltip saat mouse bergerak
document.addEventListener('mousemove', function(event) {
    const tooltip = document.getElementById('stokTooltip');
    if (tooltip.style.display === 'block') {
        tooltip.style.left = (event.pageX + 15) + 'px';
        tooltip.style.top = (event.pageY - 40) + 'px';
    }
});
    </script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>