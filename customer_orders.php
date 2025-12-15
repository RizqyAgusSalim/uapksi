<?php
// File: customer_orders.php
// TUGAS 2: Pesanan Pelanggan Spesifik (INNER JOIN)
// Menampilkan semua pesanan pelanggan termasuk detail produk dan kuantitas

include 'config.php';

// Query INNER JOIN
$sql = "SELECT 
            Pelanggan.nama_pelanggan, 
            Pesanan.id_pesanan, 
            Pesanan.tanggal_pesanan, 
            Produk.nama_produk, 
            DetailPesanan.jumlah, 
            DetailPesanan.subtotal, 
            Pesanan.total_harga
        FROM Pesanan
        INNER JOIN Pelanggan ON Pesanan.id_pelanggan = Pelanggan.id_pelanggan
        INNER JOIN DetailPesanan ON Pesanan.id_pesanan = DetailPesanan.id_pesanan
        INNER JOIN Produk ON DetailPesanan.id_produk = Produk.id_produk
        ORDER BY Pesanan.tanggal_pesanan DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan Pelanggan - Warung Digital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        tr:hover {
            background: #f7fafc;
        }
        .total-row {
            background: #fef3c7;
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Kembali ke Menu</a>
        
        <h1>📋 Daftar Pesanan Pelanggan</h1>
        <p class="subtitle">Menampilkan semua pesanan dengan detail produk dan kuantitas (INNER JOIN)</p>
        
        <table>
            <thead>
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>ID Pesanan</th>
                    <th>Tanggal Pesanan</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Total Pesanan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                    <td>#<?= $row['id_pesanan'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesanan'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td><?= $row['jumlah'] ?> pcs</td>
                    <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                    <td><strong>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php 
                    endwhile;
                } else {
                ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #718096;">
                        Belum ada data pesanan
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>