<?php
// File: sales_report.php
// TUGAS 4: Total Pengeluaran Pelanggan
// Menampilkan ranking pelanggan berdasarkan total pengeluaran

include 'config.php';

// Query untuk mendapatkan total pengeluaran per pelanggan
$sql = "SELECT 
            Pelanggan.id_pelanggan, 
            Pelanggan.nama_pelanggan, 
            Pelanggan.email,
            Pelanggan.telepon,
            COUNT(Pesanan.id_pesanan) AS total_pesanan,
            COALESCE(SUM(Pesanan.total_harga), 0) AS total_pengeluaran
        FROM Pelanggan
        LEFT JOIN Pesanan ON Pelanggan.id_pelanggan = Pesanan.id_pelanggan
        GROUP BY Pelanggan.id_pelanggan
        ORDER BY total_pengeluaran DESC";

$result = $conn->query($sql);

// Hitung total keseluruhan penjualan
$total_penjualan = 0;
$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_penjualan += $row['total_pengeluaran'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Warung Digital</title>
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
            text-align: center;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 1.1em;
            text-align: center;
        }
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .summary-box h2 {
            font-size: 2.5em;
            margin-bottom: 5px;
        }
        .summary-box p {
            font-size: 1.1em;
            opacity: 0.9;
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
            background: #667eea;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        tr:hover {
            background: #f7fafc;
        }
        .rank {
            background: #fbbf24;
            color: white;
            padding: 8px 12px;
            border-radius: 50%;
            font-weight: bold;
            display: inline-block;
            min-width: 35px;
            text-align: center;
        }
        .top-customer {
            background: #fef3c7;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .stat-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 1.1em;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 1.8em;
            font-weight: bold;
            color: #2d3748;
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
        
        <h1>💰 Laporan Total Pengeluaran Pelanggan</h1>
        <p class="subtitle">Ranking pelanggan berdasarkan total belanja di Warung Digital</p>
        
        <div class="summary-box">
            <h2>Rp <?= number_format($total_penjualan, 0, ',', '.') ?></h2>
            <p>Total Penjualan Warung Digital</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Rank</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Total Pesanan</th>
                    <th>Total Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach($data as $row): 
                    $class = $rank <= 3 ? 'top-customer' : '';
                ?>
                <tr class="<?= $class ?>">
                    <td><span class="rank"><?= $rank++ ?></span></td>
                    <td><strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong></td>
                    <td><?= htmlspecialchars($row['email']) ?: '-' ?></td>
                    <td><?= htmlspecialchars($row['telepon']) ?: '-' ?></td>
                    <td><?= $row['total_pesanan'] ?> pesanan</td>
                    <td><strong>Rp <?= number_format($row['total_pengeluaran'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📊 Total Pelanggan</h3>
                <div class="value"><?= count($data) ?></div>
            </div>
            <div class="stat-card">
                <h3>💵 Total Penjualan</h3>
                <div class="value">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <h3>📈 Rata-rata Belanja</h3>
                <div class="value">Rp <?= count($data) > 0 ? number_format($total_penjualan/count($data), 0, ',', '.') : 0 ?></div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>