<?php
// File: config.php
// Konfigurasi koneksi database untuk Warung Digital

$host = "localhost";
$username = "root";
$password = "";
$database = "warung_digital";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8");
?>