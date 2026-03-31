<?php
// admin_get_products.php

header('Content-Type: application/json');
include 'db_connect.php'; // Pastikan file ini ada dan konfigurasinya benar

$sql = "SELECT id, name, price, stock, is_active FROM products ORDER BY id DESC";
$result = $conn->query($sql);

$products = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['id'] = (int)$row['id']; // <-- PERBAIKAN PENTING: ID dijadikan integer
        $row['price'] = (int)$row['price'];
        $row['stock'] = (int)$row['stock'];
        $row['is_active'] = (bool)$row['is_active'];
        $products[] = $row;
    }
}

$conn->close();
echo json_encode(['status' => 'success', 'data' => $products]);
?>