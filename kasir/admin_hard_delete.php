<?php
// admin_hard_delete.php (Digunakan untuk Hapus Permanen Produk Berdasarkan ID)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid atau ID produk tidak ditemukan.']);
    exit;
}

$id = (int)$data['id'];

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID produk tidak valid.']);
    exit;
}

// =========================================================
// LOGIKA HAPUS PERMANEN (DELETE FROM) BERDASARKAN ID
// =========================================================

// OPTIONAL: Anda bisa menambahkan WHERE is_active = FALSE di sini untuk keamanan ekstra,
// agar hanya produk non-aktif yang bisa dihapus.
$stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND is_active = FALSE");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $deleted_rows = $stmt->affected_rows;
    
    if ($deleted_rows > 0) {
        echo json_encode([
            'status' => 'success', 
            'message' => "Berhasil menghapus 1 produk secara permanen dari database."
        ]);
    } else {
        // Ini akan muncul jika produk tidak ditemukan ATAU statusnya masih AKTIF
        echo json_encode([
            'status' => 'error', 
            'message' => "Produk tidak dihapus. Pastikan produk tersebut NON-AKTIF sebelum menghapus permanen."
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus permanen dari database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>