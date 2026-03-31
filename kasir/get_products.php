<?php
// get_products.php

header('Content-Type: application/json');
include 'db_connect.php'; // Hubungkan ke database

$sql = "SELECT id, name, price, stock FROM products WHERE is_active = TRUE ORDER BY name ASC";
$result = $conn->query($sql);

$products = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Pastikan harga adalah integer, bukan string
        $row['price'] = (int)$row['price']; 
        $row['stock'] = (int)$row['stock']; 
        $products[] = $row;
    }
}

// Tutup koneksi
$conn->close();

// Mengembalikan data dalam format JSON
echo json_encode($products);
?>