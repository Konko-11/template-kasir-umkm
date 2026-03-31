<?php
// admin_add_stock.php (Tambah Stok Cepat)

header('Content-Type: application/json');
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id']) || empty($data['add_qty'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID produk atau jumlah stok tidak valid.']);
    exit;
}

$id = (int)$data['id'];
$add_qty = (int)$data['add_qty'];

if ($add_qty <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Jumlah stok yang ditambahkan harus lebih dari 0.']);
    exit;
}

// Logika: Tambah stok dan otomatis set is_active = TRUE jika stok > 0
$stmt = $conn->prepare(
    "UPDATE products 
     SET stock = stock + ?, 
         is_active = TRUE 
     WHERE id = ?"
);
$stmt->bind_param("ii", $add_qty, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Stok berhasil ditambahkan dan produk diaktifkan kembali!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menambah stok: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>