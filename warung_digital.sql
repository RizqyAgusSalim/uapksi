-- Database: warung_digital
-- Dibuat untuk sistem penjualan Warung Digital

CREATE DATABASE IF NOT EXISTS warung_digital;
USE warung_digital;

-- =============================================
-- TUGAS 1: DESAIN TABEL
-- =============================================

-- Tabel Produk
CREATE TABLE Produk (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    nama_produk VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0
);

-- Tabel Pelanggan
CREATE TABLE Pelanggan (
    id_pelanggan INT PRIMARY KEY AUTO_INCREMENT,
    nama_pelanggan VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telepon VARCHAR(15)
);

-- Tabel Pesanan
CREATE TABLE Pesanan (
    id_pesanan INT PRIMARY KEY AUTO_INCREMENT,
    id_pelanggan INT NOT NULL,
    tanggal_pesanan DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_harga DECIMAL(10,2),
    FOREIGN KEY (id_pelanggan) REFERENCES Pelanggan(id_pelanggan)
);

-- Tabel DetailPesanan
CREATE TABLE DetailPesanan (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_pesanan INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(10,2),
    FOREIGN KEY (id_pesanan) REFERENCES Pesanan(id_pesanan),
    FOREIGN KEY (id_produk) REFERENCES Produk(id_produk)
);

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Insert Produk
INSERT INTO Produk (nama_produk, harga, stok) VALUES
('Indomie Goreng', 3500, 100),
('Teh Botol Sosro', 5000, 50),
('Aqua 600ml', 3000, 80),
('Chitato', 10000, 30),
('Oreo', 8500, 25),
('Susu Ultra Milk', 12000, 40),
('Roti Tawar', 15000, 20),
('Kopi Kapal Api', 2000, 150),
('Mie Sedaap', 3500, 90);

-- Insert Pelanggan
INSERT INTO Pelanggan (nama_pelanggan, email, telepon) VALUES
('Budi Santoso', 'budi@email.com', '081234567890'),
('Siti Nurhaliza', 'siti@email.com', '082345678901'),
('Ahmad Dhani', 'ahmad@email.com', '083456789012'),
('Dewi Lestari', 'dewi@email.com', '084567890123'),
('Rudi Hartono', 'rudi@email.com', '085678901234');

-- Insert Pesanan
INSERT INTO Pesanan (id_pelanggan, tanggal_pesanan, total_harga) VALUES
(1, '2024-10-01 10:30:00', 21000),
(2, '2024-10-01 14:15:00', 45500),
(1, '2024-10-02 09:00:00', 18500),
(3, '2024-10-02 16:45:00', 30000),
(2, '2024-10-03 11:20:00', 27000),
(1, '2024-10-04 08:15:00', 35000),
(4, '2024-10-04 13:30:00', 52000);

-- Insert DetailPesanan
INSERT INTO DetailPesanan (id_pesanan, id_produk, jumlah, subtotal) VALUES
-- Pesanan 1 (Budi)
(1, 1, 2, 7000),
(1, 2, 2, 10000),
(1, 3, 1, 3000),
-- Pesanan 2 (Siti)
(2, 4, 2, 20000),
(2, 5, 3, 25500),
-- Pesanan 3 (Budi)
(3, 1, 3, 10500),
(3, 2, 1, 5000),
(3, 3, 1, 3000),
-- Pesanan 4 (Ahmad)
(4, 6, 2, 24000),
(4, 3, 2, 6000),
-- Pesanan 5 (Siti)
(5, 4, 1, 10000),
(5, 1, 5, 17000),
-- Pesanan 6 (Budi)
(6, 5, 4, 34000),
(6, 8, 5, 10000),
-- Pesanan 7 (Dewi)
(7, 6, 3, 36000),
(7, 4, 1, 10000),
(7, 3, 2, 6000);