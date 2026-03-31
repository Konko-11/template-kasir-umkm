<?php
// admin_delete_product.php (Digunakan untuk Toggle Status Aktif)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 1. Hubungkan ke Database
include 'db_connect.php';

// Mendapatkan data JSON dari frontend
$data = json_decode(file_get_contents("php://input"), true);

// =========================================================
// PERBAIKAN UTAMA: Pengecekan data yang lebih kuat
// =========================================================
if (!isset($data['id']) || !isset($data['is_active'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID produk atau status baru (is_active) tidak ditemukan.']);
    exit;
}

$id = (int)$data['id'];
// Pastikan ID adalah angka positif
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID produk tidak valid.']);
    exit;
}

// Konversi status boolean dari JavaScript ke integer (1 atau 0) untuk MySQL
$is_active = $data['is_active'] ? 1 : 0; 


// =========================================================
// LOGIKA UBAH STATUS PRODUK (Non-aktifkan/Aktifkan)
// =========================================================

$stmt = $conn->prepare("UPDATE products SET is_active = ? WHERE id = ?");
$stmt->bind_param("ii", $is_active, $id);

if ($stmt->execute()) {
    // Cek apakah ada baris yang terpengaruh
    if ($stmt->affected_rows > 0) {
        $status_text = $is_active ? 'diaktifkan' : 'dinon-aktifkan';
        echo json_encode(['status' => 'success', 'message' => "Produk berhasil $status_text!"]);
    } else {
        // Ini terjadi jika status produk sudah sama dengan status yang dikirim
        echo json_encode(['status' => 'success', 'message' => 'Status produk sudah sama, tidak ada perubahan.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah status di database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>