<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

$query = "SELECT id_barang, nama_barang, harga, stok, tanggal_masuk FROM barang ORDER BY id_barang";
$result = mysqli_query($koneksi, $query);

$filename = "data_inventori_raja_dekor_" . date('Y-m-d') . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

echo "ID\tNama Barang\tHarga\tStok\tTanggal Masuk\n";

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['id_barang'] . "\t";
    echo $row['nama_barang'] . "\t";
    echo $row['harga'] . "\t";
    echo $row['stok'] . "\t";
    echo $row['tanggal_masuk'] . "\n";
}
exit;
?>