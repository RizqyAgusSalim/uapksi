<?php
require_once 'config.php';

// Ambil data produk dari database
$query_produk = "SELECT * FROM Produk WHERE stok > 0 ORDER BY nama_produk";
$result_produk = mysqli_query($conn, $query_produk);

// Proses pemesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_pesanan'])) {
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $produk_items = json_decode($_POST['produk_items'], true);
    $total_bayar = $_POST['total_bayar'];
    $tanggal = date('Y-m-d H:i:s');
    
    // Cek apakah pelanggan sudah ada berdasarkan email
    $check_pelanggan = "SELECT id_pelanggan FROM Pelanggan WHERE email = '$email'";
    $result_check = mysqli_query($conn, $check_pelanggan);
    
    if (mysqli_num_rows($result_check) > 0) {
        $row = mysqli_fetch_assoc($result_check);
        $id_pelanggan = $row['id_pelanggan'];
    } else {
        // Insert pelanggan baru
        $insert_pelanggan = "INSERT INTO Pelanggan (nama_pelanggan, email, telepon) VALUES ('$nama_pelanggan', '$email', '$telepon')";
        mysqli_query($conn, $insert_pelanggan);
        $id_pelanggan = mysqli_insert_id($conn);
    }
    
    // Insert pesanan
    $insert_pesanan = "INSERT INTO Pesanan (id_pelanggan, tanggal_pesanan, total_harga) VALUES ($id_pelanggan, '$tanggal', $total_bayar)";
    
    if (mysqli_query($conn, $insert_pesanan)) {
        $id_pesanan = mysqli_insert_id($conn);
        
        // Insert detail pesanan dan update stok
        foreach ($produk_items as $item) {
            $id_produk = $item['id'];
            $jumlah = $item['qty'];
            $subtotal = $item['subtotal'];
            
            $insert_detail = "INSERT INTO DetailPesanan (id_pesanan, id_produk, jumlah, subtotal) 
                             VALUES ($id_pesanan, $id_produk, $jumlah, $subtotal)";
            mysqli_query($conn, $insert_detail);
            
            // Update stok
            $update_stok = "UPDATE Produk SET stok = stok - $jumlah WHERE id_produk = $id_produk";
            mysqli_query($conn, $update_stok);
        }
        
        $order_number = str_pad($id_pesanan, 6, '0', STR_PAD_LEFT);
        echo "<script>
            alert('Pesanan berhasil! Nomor Pesanan Anda: #$order_number\\nTotal: Rp " . number_format($total_bayar, 0, ',', '.') . "\\n\\nTerima kasih telah berbelanja!');
            window.location.href='shop.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Digital - Belanja Online</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 28px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn-admin-access {
            background: #fff;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
            text-decoration: none;
        }
        
        .btn-admin-access:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
            background: #667eea;
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }
        
        .cart-icon:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .cart-count {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95), rgba(118, 75, 162, 0.95));
            color: white;
            padding: 80px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-banner h2 {
            font-size: 56px;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-banner p {
            font-size: 24px;
            opacity: 0.95;
            margin-bottom: 30px;
        }
        
        .hero-features {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            background: rgba(255,255,255,0.2);
            padding: 12px 24px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        
        .feature-icon {
            font-size: 24px;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Search & Filter */
        .controls {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .search-box {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-box input {
            flex: 1;
            padding: 15px 25px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 16px;
            transition: 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .section-title {
            font-size: 32px;
            color: white;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.3s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.3);
        }
        
        .product-image {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 24px;
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .product-stock {
            color: #888;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .btn-add {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn-add:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-add:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Admin Login Modal */
        .admin-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .admin-modal.active {
            display: flex;
        }
        
        .admin-modal-content {
            background: white;
            border-radius: 20px;
            max-width: 450px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .admin-modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-modal-header h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .admin-modal-body {
            padding: 35px;
        }
        
        .login-form {
            display: grid;
            gap: 20px;
        }
        
        .form-group {
            display: grid;
            gap: 8px;
        }
        
        .form-group label {
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-cancel {
            padding: 12px;
            background: #f1f3f5;
            color: #666;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn-cancel:hover {
            background: #e9ecef;
        }
        
        .login-hint {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-top: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        /* Cart Modal */
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .cart-modal.active {
            display: flex;
        }
        
        .cart-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .cart-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 20px 20px 0 0;
        }
        
        .cart-header h2 {
            font-size: 24px;
        }
        
        .close-cart {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .close-cart:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .cart-items {
            padding: 25px;
        }
        
        .cart-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .cart-item-image {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: #667eea;
            font-weight: 600;
        }
        
        .qty-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qty-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .qty-btn:hover {
            background: #5568d3;
        }
        
        .qty-number {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        
        .btn-remove {
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .cart-empty {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .cart-empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .cart-summary {
            padding: 25px;
            border-top: 2px solid #e0e0e0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .summary-row.total {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            padding-top: 15px;
            border-top: 2px solid #667eea;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
        }
        
        /* Checkout Form */
        .checkout-form {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .no-products-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <span style="font-size: 36px;">🏪</span>
                <h1>Warung Digital</h1>
            </div>
            <div class="header-actions">
                <a href="#" class="btn-admin-access" onclick="openAdminModal(); return false;">
                    <span>🔐</span>
                    <span>Login Admin</span>
                </a>
                <div class="cart-icon" onclick="toggleCart()">
                    <span style="font-size: 24px;">🛒</span>
                    <span>Keranjang</span>
                    <span class="cart-count" id="cart-count">0</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content">
            <h2>Selamat Datang! 👋</h2>
            <p>Belanja kebutuhan sehari-hari dengan mudah dan cepat</p>
            <div class="hero-features">
                <div class="feature-item">
                    <span class="feature-icon">🚀</span>
                    <span>Proses Cepat</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">💳</span>
                    <span>Pembayaran Mudah</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📦</span>
                    <span>Produk Berkualitas</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <h2 class="section-title">🛍️ Produk Kami</h2>
        
        <!-- Search & Filter -->
        <div class="controls">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="🔍 Cari produk yang Anda butuhkan..." onkeyup="searchProduct()">
            </div>
        </div>
        
        <!-- Product Grid -->
        <div class="product-grid" id="product-grid">
            <?php 
            if (mysqli_num_rows($result_produk) > 0):
                while($produk = mysqli_fetch_assoc($result_produk)): 
            ?>
                <div class="product-card" data-name="<?= strtolower($produk['nama_produk']) ?>">
                    <div class="product-image">📦</div>
                    <div class="product-info">
                        <div class="product-name"><?= $produk['nama_produk'] ?></div>
                        <div class="product-price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
                        <div class="product-stock">Stok tersedia: <?= $produk['stok'] ?></div>
                        <button class="btn-add" 
                                onclick="addToCart(<?= $produk['id_produk'] ?>, '<?= addslashes($produk['nama_produk']) ?>', <?= $produk['harga'] ?>, <?= $produk['stok'] ?>)"
                                <?= $produk['stok'] <= 0 ? 'disabled' : '' ?>>
                            <?= $produk['stok'] <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang' ?>
                        </button>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="no-products">
                    <div class="no-products-icon">📭</div>
                    <h3>Produk tidak tersedia</h3>
                    <p>Maaf, saat ini belum ada produk yang tersedia</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Admin Login Modal -->
    <div class="admin-modal" id="admin-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2>🔐 Login Admin</h2>
                <p>Masuk ke Dashboard Admin</p>
            </div>
            <div class="admin-modal-body">
                <form class="login-form" onsubmit="return adminLogin(event)">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="admin-username" placeholder="Masukkan username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="admin-password" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" class="btn-login">Masuk ke Dashboard</button>
                    <button type="button" class="btn-cancel" onclick="closeAdminModal()">Batal</button>
                </form>
                <div class="login-hint">
                    💡 Default: Username: <strong>admin</strong> | Password: <strong>admin123</strong>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Modal -->
    <div class="cart-modal" id="cart-modal">
        <div class="cart-content">
            <div class="cart-header">
                <h2>🛒 Keranjang Belanja</h2>
                <button class="close-cart" onclick="toggleCart()">×</button>
            </div>
            
            <div class="cart-items" id="cart-items-container">
                <div class="cart-empty">
                    <div class="cart-empty-icon">🛒</div>
                    <h3>Keranjang masih kosong</h3>
                    <p>Yuk mulai belanja!</p>
                </div>
            </div>
            
            <div class="cart-summary" id="cart-summary" style="display: none;">
                <div id="checkout-section" style="display: none;">
                    <h3 style="margin-bottom: 15px; color: #333;">Data Pembeli</h3>
                    <div class="checkout-form">
                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" id="nama-pelanggan" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" id="email-pelanggan" placeholder="contoh@email.com" required>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="tel" id="telepon-pelanggan" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal-display">Rp 0</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total-display">Rp 0</span>
                </div>
                
                <button class="btn-checkout" id="btn-checkout" onclick="showCheckoutForm()">
                    Lanjutkan
                </button>
                <button class="btn-checkout" id="btn-process" onclick="processOrder()" style="display: none;">
                    Proses Pesanan
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let cart = [];
        let checkoutMode = false;
        
        // Admin Login Function
        function openAdminModal() {
            document.getElementById('admin-modal').classList.add('active');
        }
        
        function closeAdminModal() {
            document.getElementById('admin-modal').classList.remove('active');
            document.getElementById('admin-username').value = '';
            document.getElementById('admin-password').value = '';
        }
        
        function adminLogin(event) {
            event.preventDefault();
            
            const username = document.getElementById('admin-username').value;
            const password = document.getElementById('admin-password').value;
            
            // Simple authentication (ganti dengan sistem yang lebih aman di production)
            if (username === 'admin' && password === 'admin123') {
                alert('✓ Login berhasil! Mengalihkan ke dashboard...');
                window.location.href = 'index.php';
            } else {
                alert('✗ Username atau password salah!');
            }
            
            return false;
        }
        
        // Close modals when clicking outside
        document.getElementById('admin-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAdminModal();
            }
        });
        
        // Cart Functions
        function addToCart(id, nama, harga, stok) {
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.qty < stok) {
                    existingItem.qty++;
                    existingItem.subtotal = existingItem.qty * existingItem.harga;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            } else {
                cart.push({
                    id: id,
                    nama: nama,
                    harga: harga,
                    qty: 1,
                    stok: stok,
                    subtotal: harga
                });
            }
            
            updateCart();
            
            // Show notification
            const notification = document.createElement('div');
            notification.textContent = '✓ Ditambahkan ke keranjang';
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: #4caf50;
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                z-index: 2000;
                animation: slideIn 0.3s ease;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }
        
        function updateQty(id, action) {
            const item = cart.find(item => item.id === id);
            
            if (action === 'plus') {
                if (item.qty < item.stok) {
                    item.qty++;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            } else if (action === 'minus') {
                item.qty--;
                if (item.qty === 0) {
                    cart = cart.filter(i => i.id !== id);
                }
            }
            
            if (item && item.qty > 0) {
                item.subtotal = item.qty * item.harga;
            }
            
            updateCart();
        }
        
        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCart();
        }
        
        function updateCart() {
            const container = document.getElementById('cart-items-container');
            const summary = document.getElementById('cart-summary');
            const count = document.getElementById('cart-count');
            
            count.textContent = cart.reduce((sum, item) => sum + item.qty, 0);
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <div class="cart-empty-icon">🛒</div>
                        <h3>Keranjang masih kosong</h3>
                        <p>Yuk mulai belanja!</p>
                    </div>
                `;
                summary.style.display = 'none';
                checkoutMode = false;
                return;
            }
            
            let html = '';
            let total = 0;
            
            cart.forEach(item => {
                total += item.subtotal;
                html += `
                    <div class="cart-item">
                        <div class="cart-item-image">📦</div>
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.nama}</div>
                            <div class="cart-item-price">Rp ${item.harga.toLocaleString('id-ID')}</div>
                        </div>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="updateQty(${item.id}, 'minus')">-</button>
                            <span class="qty-number">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty(${item.id}, 'plus')">+</button>
                            <button class="btn-remove" onclick="removeFromCart(${item.id})">🗑️</button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            summary.style.display = 'block';
            document.getElementById('subtotal-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('total-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        function toggleCart() {
            const modal = document.getElementById('cart-modal');
            modal.classList.toggle('active');
            
            // Reset checkout mode when closing
            if (!modal.classList.contains('active')) {
                checkoutMode = false;
                document.getElementById('checkout-section').style.display = 'none';
                document.getElementById('btn-checkout').style.display = 'block';
                document.getElementById('btn-process').style.display = 'none';
            }
        }
        
        function showCheckoutForm() {
            checkoutMode = true;
            document.getElementById('checkout-section').style.display = 'block';
            document.getElementById('btn-checkout').style.display = 'none';
            document.getElementById('btn-process').style.display = 'block';
        }
        
        function processOrder() {
            const nama = document.getElementById('nama-pelanggan').value.trim();
            const email = document.getElementById('email-pelanggan').value.trim();
            const telepon = document.getElementById('telepon-pelanggan').value.trim();
            
            if (!nama || !email) {
                alert('Nama dan email wajib diisi!');
                return;
            }
            
            if (!email.includes('@')) {
                alert('Format email tidak valid!');
                return;
            }
            
            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }
            
            const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const inputs = [
                {name: 'nama_pelanggan', value: nama},
                {name: 'email', value: email},
                {name: 'telepon', value: telepon},
                {name: 'produk_items', value: JSON.stringify(cart)},
                {name: 'total_bayar', value: total},
                {name: 'proses_pesanan', value: '1'}
            ];
            
            inputs.forEach(input => {
                const el = document.createElement('input');
                el.type = 'hidden';
                el.name = input.name;
                el.value = input.value;
                form.appendChild(el);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function searchProduct() {
            const input = document.getElementById('search-input').value.toLowerCase();
            const products = document.querySelectorAll('.product-card');
            
            products.forEach(product => {
                const name = product.dataset.name;
                if (name.includes(input)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }
        
        // Close cart modal when clicking outside
        document.getElementById('cart-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleCart();
            }
        });
    </script>
</body>
</html>