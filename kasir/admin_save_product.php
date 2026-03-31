<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 1. Hubungkan ke Database
include 'db_connect.php';

// Mendapatkan data JSON dari frontend (JavaScript)
$data = json_decode(file_get_contents("php://input"), true);

// Memastikan data dasar tersedia
if (empty($data['action']) || empty($data['name']) || !isset($data['price'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit;
}

$action = $data['action'];
$name = $conn->real_escape_string($data['name']);
$price = (int)$data['price'];
$stock = isset($data['stock']) ? (int)$data['stock'] : 0;
$id = isset($data['id']) ? (int)$data['id'] : null;

// Validasi dasar
if ($price < 0 || $stock < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Harga dan Stok tidak boleh negatif.']);
    exit;
}


if ($action === 'add') {
    // Logika INSERT (Tambah)
    $stmt = $conn->prepare("INSERT INTO products (name, price, stock, is_active) VALUES (?, ?, ?, TRUE)");
    $stmt->bind_param("sii", $name, $price, $stock);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Produk baru berhasil ditambahkan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambah produk: ' . $stmt->error]);
    }
    $stmt->close();

} elseif ($action === 'edit' && $id) {
    // =========================================================
    // B. LOGIKA UBAH PRODUK (UPDATE)
    // =========================================================
    // Query sudah benar: UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?");
    $stmt->bind_param("siii", $name, $price, $stock, $id);

    if ($stmt->execute()) {
        // Cek apakah ada perubahan
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Produk berhasil diubah!']);
        } else {
            // Ini BUKAN error, hanya tidak ada data yang berbeda dari sebelumnya
            echo json_encode(['status' => 'success', 'message' => 'Produk diubah, tetapi tidak ada nilai yang berbeda.']);
        }
    } else {
        // PERBAIKAN UTAMA: Tampilkan error SQL yang sebenarnya
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah produk. Detail Error SQL: ' . $stmt->error]);
    }
    $stmt->close();
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid atau ID tidak ditemukan untuk pengubahan.']);
}

$conn->close();
?>