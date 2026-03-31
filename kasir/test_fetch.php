<?php
// test_fetch.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Coba dapatkan data JSON
$data = file_get_contents("php://input");

if ($data === false) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal membaca input JSON.']);
} elseif (empty($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Input JSON kosong.']);
} else {
    // Tampilkan data yang diterima server
    echo json_encode(['status' => 'success', 'message' => 'Data diterima server.', 'data_received' => json_decode($data)]);
}
?>