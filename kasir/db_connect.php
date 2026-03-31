<?php
// db_connect.php

$servername = "localhost"; // Biasanya localhost
$username = "root";       // Ganti dengan username database Anda
$password = "";           // Ganti dengan password database Anda
$dbname = "rain_pos";   // Nama database yang akan kita buat

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Berhenti dan tampilkan pesan error jika koneksi gagal
    die("Koneksi gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil"; // Uncomment ini untuk tes koneksi
?>