<?php
// checkout.php - Diperbarui untuk menerima detail pembayaran baru

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Asumsi file db_connect.php ada di direktori yang sama
include 'db_connect.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['cart']) || !isset($data['total_amount']) || !isset($data['paid_amount'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data transaksi atau pembayaran tidak lengkap.']);
    exit;
}

$cart = $data['cart'];
$total_amount = (float)($data['total_amount'] ?? 0); 
$paid_amount = (int)($data['paid_amount'] ?? 0); 
$change_amount = (int)($data['change_amount'] ?? 0); 

// ✅ PENTING: Pastikan metode pembayaran selalu memiliki nilai, default ke CASH.
// Nilai akan menjadi string seperti 'CASH', 'QRIS', 'TRANSFER', dll.
$payment_method = $data['payment_method'] ?? 'CASH'; 

// Data untuk tabel transactions
$subtotal = $total_amount; // Anggap subtotal sama dengan total_amount (tanpa diskon)
$discount_amount = 0; 
$transaction_code = 'TRX-' . time(); 

$conn->begin_transaction(); // Mulai Transaksi Database

try {
    // 1. INSERT ke tabel transactions (Header)
    // Catatan: Asumsi transaction_date diisi otomatis oleh timestamp DEFAULT current_timestamp()
    $stmt_header = $conn->prepare("INSERT INTO transactions (transaction_code, subtotal_before_discount, discount_amount, total_amount, payment_method, paid_amount, change_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Asumsi: subtotal_before_discount, discount_amount, total_amount, paid_amount, change_amount adalah INTEGER (i) atau string (s) untuk payment_method
    // Jika kolom subtotal_before_discount, total_amount adalah INT, gunakan 'iiisi'
    $stmt_header->bind_param("siiisii", 
        $transaction_code, 
        $subtotal, 
        $discount_amount, 
        $total_amount, 
        $payment_method, // ✅ Variabel ini menyimpan nilai string yang benar
        $paid_amount, 
        $change_amount
    );

    if (!$stmt_header->execute()) {
        throw new Exception("Gagal mencatat header transaksi: " . $stmt_header->error);
    }
    $transaction_id = $stmt_header->insert_id;
    $stmt_header->close();


    // 2. Loop Cart, INSERT ke transaction_details & UPDATE products stock
    $stmt_detail = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price_per_unit, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    // Asumsi: stock, total_sold, is_active, id, stock di produk adalah INT (i)
    $stmt_update = $conn->prepare(
        "UPDATE products 
         SET stock = stock - ?, 
             total_sold = total_sold + ?,
             is_active = CASE WHEN stock - ? <= 0 THEN FALSE ELSE is_active END
         WHERE id = ? AND stock >= ?"
    );

    foreach ($cart as $item) {
        $product_id = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price_per_unit = (int)$item['price'];
        $subtotal_detail = $qty * $price_per_unit;

        // A. INSERT transaction_details
        $stmt_detail->bind_param("iiiii", $transaction_id, $product_id, $qty, $price_per_unit, $subtotal_detail);
        if (!$stmt_detail->execute()) {
            throw new Exception("Gagal mencatat detail item ID $product_id: " . $stmt_detail->error);
        }

        // B. UPDATE products (Stok, total_sold, dan is_active)
        $stmt_update->bind_param("iiiii", $qty, $qty, $qty, $product_id, $qty);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Gagal mengupdate stok produk ID $product_id: " . $stmt_update->error);
        }
        
        // Cek apakah ada baris yang terpengaruh (untuk memastikan stok cukup)
        if ($conn->affected_rows === 0) {
             throw new Exception("Stok produk ID $product_id tidak cukup saat proses checkout. Transaksi dibatalkan.");
        }
    }
    
    $stmt_detail->close();
    $stmt_update->close();

    $conn->commit(); // Commit jika semua berhasil
    echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil dicatat dan stok diupdate.', 'transaction_id' => $transaction_id]);

} catch (Exception $e) {
    $conn->rollback(); // Rollback jika ada error
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Transaksi gagal diproses: ' . $e->getMessage()]);
}

$conn->close();
?>