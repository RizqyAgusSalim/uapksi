<?php
require_once 'config.php';

// Ambil data produk dari database
$query_produk = "SELECT * FROM Produk ORDER BY nama_produk";
$result_produk = mysqli_query($conn, $query_produk);

// Ambil data pelanggan dari database
$query_pelanggan = "SELECT * FROM Pelanggan ORDER BY nama_pelanggan";
$result_pelanggan = mysqli_query($conn, $query_pelanggan);

// Proses penyimpanan transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_transaksi'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $produk_items = json_decode($_POST['produk_items'], true);
    $total_bayar = $_POST['total_bayar'];
    $tanggal = date('Y-m-d H:i:s');
    
    // Jika pelanggan baru
    if ($id_pelanggan == 'baru' && !empty($nama_pelanggan)) {
        $insert_pelanggan = "INSERT INTO pelanggan (nama_pelanggan, tanggal_daftar) VALUES ('$nama_pelanggan', '$tanggal')";
        mysqli_query($conn, $insert_pelanggan);
        $id_pelanggan = mysqli_insert_id($conn);
    }
    
    // Insert ke tabel pesanan
    $insert_pesanan = "INSERT INTO pesanan (id_pelanggan, tanggal_pesanan, total_harga) VALUES ($id_pelanggan, '$tanggal', $total_bayar)";
    
    if (mysqli_query($conn, $insert_pesanan)) {
        $id_pesanan = mysqli_insert_id($conn);
        
        // Insert detail pesanan
        foreach ($produk_items as $item) {
            $id_produk = $item['id'];
            $jumlah = $item['qty'];
            $harga = $item['harga'];
            $subtotal = $item['subtotal'];
            
            $insert_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga, subtotal) 
                            VALUES ($id_pesanan, $id_produk, $jumlah, $harga, $subtotal)";
            mysqli_query($conn, $insert_detail);
            
            // Update stok produk
            $update_stok = "UPDATE produk SET stok = stok - $jumlah WHERE id_produk = $id_produk";
            mysqli_query($conn, $update_stok);
        }
        
        echo "<script>alert('Transaksi berhasil disimpan!'); window.location.href='kasir.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Warung Digital</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 28px;
        }
        
        .header .btn-admin {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }
        
        .header .btn-admin:hover {
            background: #5568d3;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .left-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .right-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .pelanggan-form {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .produk-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .produk-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
        }
        
        .produk-card.selected {
            background: #f0f3ff;
            border-color: #667eea;
        }
        
        .produk-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .produk-nama {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .produk-harga {
            color: #667eea;
            font-weight: 700;
            font-size: 16px;
        }
        
        .produk-stok {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .keranjang-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .keranjang-info {
            flex: 1;
        }
        
        .keranjang-nama {
            font-weight: 600;
            color: #333;
        }
        
        .keranjang-harga {
            color: #667eea;
            font-size: 14px;
        }
        
        .qty-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qty-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s;
        }
        
        .qty-btn:hover {
            background: #5568d3;
        }
        
        .qty-display {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        
        .btn-remove {
            background: #ff4757;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .total-row.grand {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
        }
        
        .btn-bayar {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }
        
        .btn-bayar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-bayar:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .keranjang-kosong {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        
        #nama-pelanggan-baru {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏪 Warung Digital - Kasir</h1>
            <a href="index.php" class="btn-admin">Dashboard Admin</a>
        </div>
        
        <div class="main-content">
            <div class="left-section">
                <h2 class="section-title">Data Pelanggan</h2>
                <div class="pelanggan-form">
                    <div class="form-group">
                        <label>Pilih Pelanggan</label>
                        <select id="pilih-pelanggan">
                            <option value="">-- Pilih Pelanggan --</option>
                            <option value="baru">+ Pelanggan Baru</option>
                            <?php while($pelanggan = mysqli_fetch_assoc($result_pelanggan)): ?>
                                <option value="<?= $pelanggan['id_pelanggan'] ?>"><?= $pelanggan['nama_pelanggan'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="nama-pelanggan-baru">
                        <label>Nama Pelanggan Baru</label>
                        <input type="text" id="input-nama-baru" placeholder="Masukkan nama pelanggan">
                    </div>
                </div>
                
                <h2 class="section-title">Pilih Produk</h2>
                <div class="produk-grid">
                    <?php while($produk = mysqli_fetch_assoc($result_produk)): ?>
                        <div class="produk-card" 
                             data-id="<?= $produk['id_produk'] ?>"
                             data-nama="<?= $produk['nama_produk'] ?>"
                             data-harga="<?= $produk['harga'] ?>"
                             data-stok="<?= $produk['stok'] ?>"
                             onclick="tambahProduk(this)">
                            <div class="produk-icon">📦</div>
                            <div class="produk-nama"><?= $produk['nama_produk'] ?></div>
                            <div class="produk-harga">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
                            <div class="produk-stok">Stok: <?= $produk['stok'] ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="right-section">
                <h2 class="section-title">Keranjang Belanja</h2>
                <div id="keranjang-container">
                    <div class="keranjang-kosong">
                        <p>Keranjang masih kosong</p>
                        <p style="font-size: 50px;">🛒</p>
                    </div>
                </div>
                
                <div class="total-section" id="total-section" style="display: none;">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">Rp 0</span>
                    </div>
                    <div class="total-row grand">
                        <span>TOTAL:</span>
                        <span id="total">Rp 0</span>
                    </div>
                    <button class="btn-bayar" onclick="prosesTransaksi()">PROSES PEMBAYARAN</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let keranjang = [];
        
        // Toggle input nama pelanggan baru
        document.getElementById('pilih-pelanggan').addEventListener('change', function() {
            const namaBaru = document.getElementById('nama-pelanggan-baru');
            if (this.value === 'baru') {
                namaBaru.style.display = 'block';
            } else {
                namaBaru.style.display = 'none';
            }
        });
        
        function tambahProduk(element) {
            const id = element.dataset.id;
            const nama = element.dataset.nama;
            const harga = parseFloat(element.dataset.harga);
            const stok = parseInt(element.dataset.stok);
            
            // Cek apakah produk sudah ada di keranjang
            const index = keranjang.findIndex(item => item.id === id);
            
            if (index !== -1) {
                // Cek stok
                if (keranjang[index].qty < stok) {
                    keranjang[index].qty++;
                    keranjang[index].subtotal = keranjang[index].qty * keranjang[index].harga;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            } else {
                if (stok > 0) {
                    keranjang.push({
                        id: id,
                        nama: nama,
                        harga: harga,
                        qty: 1,
                        stok: stok,
                        subtotal: harga
                    });
                } else {
                    alert('Stok habis!');
                    return;
                }
            }
            
            updateKeranjang();
        }
        
        function updateQty(id, action) {
            const index = keranjang.findIndex(item => item.id === id);
            
            if (action === 'plus') {
                if (keranjang[index].qty < keranjang[index].stok) {
                    keranjang[index].qty++;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            } else if (action === 'minus') {
                keranjang[index].qty--;
                if (keranjang[index].qty === 0) {
                    keranjang.splice(index, 1);
                }
            }
            
            if (keranjang[index]) {
                keranjang[index].subtotal = keranjang[index].qty * keranjang[index].harga;
            }
            
            updateKeranjang();
        }
        
        function hapusItem(id) {
            keranjang = keranjang.filter(item => item.id !== id);
            updateKeranjang();
        }
        
        function updateKeranjang() {
            const container = document.getElementById('keranjang-container');
            const totalSection = document.getElementById('total-section');
            
            if (keranjang.length === 0) {
                container.innerHTML = '<div class="keranjang-kosong"><p>Keranjang masih kosong</p><p style="font-size: 50px;">🛒</p></div>';
                totalSection.style.display = 'none';
                return;
            }
            
            let html = '';
            let total = 0;
            
            keranjang.forEach(item => {
                total += item.subtotal;
                html += `
                    <div class="keranjang-item">
                        <div class="keranjang-info">
                            <div class="keranjang-nama">${item.nama}</div>
                            <div class="keranjang-harga">Rp ${item.harga.toLocaleString('id-ID')}</div>
                        </div>
                        <div class="qty-control">
                            <button class="qty-btn" onclick="updateQty('${item.id}', 'minus')">-</button>
                            <span class="qty-display">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty('${item.id}', 'plus')">+</button>
                            <button class="btn-remove" onclick="hapusItem('${item.id}')">🗑️</button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            totalSection.style.display = 'block';
            document.getElementById('subtotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        function prosesTransaksi() {
            const idPelanggan = document.getElementById('pilih-pelanggan').value;
            const namaBaru = document.getElementById('input-nama-baru').value;
            
            if (!idPelanggan) {
                alert('Pilih pelanggan terlebih dahulu!');
                return;
            }
            
            if (idPelanggan === 'baru' && !namaBaru) {
                alert('Masukkan nama pelanggan baru!');
                return;
            }
            
            if (keranjang.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }
            
            // Hitung total
            const total = keranjang.reduce((sum, item) => sum + item.subtotal, 0);
            
            // Buat form dan submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const inputPelanggan = document.createElement('input');
            inputPelanggan.type = 'hidden';
            inputPelanggan.name = 'id_pelanggan';
            inputPelanggan.value = idPelanggan;
            form.appendChild(inputPelanggan);
            
            if (idPelanggan === 'baru') {
                const inputNama = document.createElement('input');
                inputNama.type = 'hidden';
                inputNama.name = 'nama_pelanggan';
                inputNama.value = namaBaru;
                form.appendChild(inputNama);
            }
            
            const inputProduk = document.createElement('input');
            inputProduk.type = 'hidden';
            inputProduk.name = 'produk_items';
            inputProduk.value = JSON.stringify(keranjang);
            form.appendChild(inputProduk);
            
            const inputTotal = document.createElement('input');
            inputTotal.type = 'hidden';
            inputTotal.name = 'total_bayar';
            inputTotal.value = total;
            form.appendChild(inputTotal);
            
            const inputSubmit = document.createElement('input');
            inputSubmit.type = 'hidden';
            inputSubmit.name = 'simpan_transaksi';
            inputSubmit.value = '1';
            form.appendChild(inputSubmit);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>