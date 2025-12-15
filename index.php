<?php
// File: index.php
// Menu utama Warung Digital

include 'config.php';

// Ambil statistik dasar
$total_produk = $conn->query("SELECT COUNT(*) as total FROM Produk")->fetch_assoc()['total'];
$total_pelanggan = $conn->query("SELECT COUNT(*) as total FROM Pelanggan")->fetch_assoc()['total'];
$total_pesanan = $conn->query("SELECT COUNT(*) as total FROM Pesanan")->fetch_assoc()['total'];
$total_penjualan = $conn->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM Pesanan")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Digital - Dashboard</title>
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
        }
        .header {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header p {
            color: #718096;
            font-size: 1.2em;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #718096;
            font-size: 1em;
        }
        
        /* Quick Access Section */
        .quick-access {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }
        .quick-access h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8em;
            text-align: center;
        }
        .quick-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .quick-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1em;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .quick-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.6);
        }
        .quick-btn.secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .quick-btn-icon {
            font-size: 1.5em;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .menu-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 5px solid #667eea;
        }
        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .menu-card h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        .menu-card p {
            color: #718096;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .menu-badge {
            display: inline-block;
            background: #fbbf24;
            color: #92400e;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        /* Section Headers */
        .section-header {
            background: white;
            border-radius: 12px;
            padding: 20px 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .section-header h3 {
            color: #667eea;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .footer p {
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏪 Warung Digital</h1>
            <p>Sistem Manajemen Penjualan Komprehensif</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?= $total_produk ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?= $total_pelanggan ?></div>
                <div class="stat-label">Total Pelanggan</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-value"><?= $total_pesanan ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value">Rp <?= number_format($total_penjualan, 0, ',', '.') ?></div>
                <div class="stat-label">Total Penjualan</div>
            </div>
        </div>

        <!-- Quick Access Buttons -->
        <div class="quick-access">
            <h2>⚡ Akses Cepat</h2>
            <div class="quick-buttons">
                <a href="kasir.php" class="quick-btn">
                    <span class="quick-btn-icon">💳</span>
                    <span>Kasir / POS</span>
                </a>
                <a href="shop.php" class="quick-btn secondary" target="_blank">
                    <span class="quick-btn-icon">🛒</span>
                    <span>Tampilan Pembeli</span>
                </a>
            </div>
        </div>

        <!-- Laporan Section -->
        <div class="section-header">
            <h3>📊 Laporan & Analisis</h3>
        </div>

        <div class="menu-grid">
            <a href="customer_orders.php" class="menu-card">
                <h2>📋 Pesanan Pelanggan</h2>
                <p>Lihat semua pesanan pelanggan termasuk detail produk dan kuantitas menggunakan INNER JOIN</p>
                <span class="menu-badge">TUGAS 2</span>
            </a>

            <a href="unsold_products.php" class="menu-card">
                <h2>📦 Produk Belum Terjual</h2>
                <p>Identifikasi produk yang belum pernah terjual menggunakan LEFT JOIN untuk strategi pemasaran</p>
                <span class="menu-badge">TUGAS 3</span>
            </a>

            <a href="sales_report.php" class="menu-card">
                <h2>💰 Laporan Penjualan</h2>
                <p>Ranking pelanggan berdasarkan total pengeluaran, diurutkan dari yang terbesar</p>
                <span class="menu-badge">TUGAS 4</span>
            </a>
        </div>

        <div class="footer">
            <p><strong>Warung Digital</strong> - Sistem Penjualan dengan Database MySQL & PHP</p>
            <p style="margin-top: 10px; font-size: 0.9em;">Database Schema: Produk | Pelanggan | Pesanan | DetailPesanan</p>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>