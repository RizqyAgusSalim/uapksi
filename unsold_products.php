<?php
// File: unsold_products.php
// TUGAS 3: Produk Belum Terjual (LEFT JOIN)
// Menampilkan produk yang belum pernah terjual

include 'config.php';

// Query LEFT JOIN
$sql = "SELECT 
            Produk.id_produk, 
            Produk.nama_produk, 
            Produk.harga, 
            Produk.stok
        FROM Produk
        LEFT JOIN DetailPesanan ON Produk.id_produk = DetailPesanan.id_produk
        WHERE DetailPesanan.id_produk IS NULL
        ORDER BY Produk.nama_produk";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Belum Terjual - Warung Digital</title>
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
            max-width: 1000px;
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
        .alert {
            background: #fef3c7;
            border-left: 4px solid #fbbf24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #92400e;
        }
        .alert strong {
            display: block;
            margin-bottom: 5px;
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
        .success-message {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #065f46;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Kembali ke Menu</a>
        
        <h1>📦 Produk Belum Terjual</h1>
        <p class="subtitle">Identifikasi produk yang belum pernah terjual menggunakan LEFT JOIN</p>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="alert">
            <strong>⚠️ Perhatian!</strong>
            Terdapat <?= $result->num_rows ?> produk yang belum pernah terjual. Pertimbangkan untuk melakukan strategi pemasaran atau promosi khusus.
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID Produk</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok Tersedia</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_produk'] ?></td>
                    <td><strong><?= htmlspecialchars($row['nama_produk']) ?></strong></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><?= $row['stok'] ?> unit</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <?php else: ?>
        <div class="success-message">
            <strong>✅ Luar Biasa!</strong><br>
            Semua produk sudah pernah terjual. Tidak ada produk yang tertinggal!
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>