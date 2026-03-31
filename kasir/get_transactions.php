<?php
// get_transactions.php - Mengambil riwayat transaksi (FIXED)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

include 'db_connect.php'; 

// Cek jika koneksi database gagal dari db_connect.php
if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi ke database tidak valid atau gagal.']);
    exit;
}

// Query untuk mengambil data header transaksi dan menghitung total item
$sql = "
    SELECT
        t.id,
        t.transaction_code,
        t.total_amount,
        t.payment_method,
        t.transaction_date,  -- DIGANTI: sebelumnya t.created_at
        t.paid_amount,
        t.change_amount,
        SUM(td.quantity) AS total_items
    FROM
        transactions t
    JOIN
        transaction_details td ON t.id = td.transaction_id
    GROUP BY
        t.id, t.transaction_code, t.total_amount, t.payment_method, t.transaction_date, t.paid_amount, t.change_amount
    ORDER BY
        t.transaction_date DESC -- DIGANTI: sebelumnya t.created_at
";

$result = $conn->query($sql);

if ($result) {
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        // Konversi tipe data ke integer/float
        $row['total_amount'] = (int)$row['total_amount'];
        $row['paid_amount'] = (int)$row['paid_amount'];
        $row['change_amount'] = (int)$row['change_amount'];
        // Kunci di $row sekarang menggunakan 'transaction_date'
        $transactions[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $transactions]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query database gagal: ' . $conn->error]);
}

$conn->close();
?>