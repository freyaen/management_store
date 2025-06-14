<?php
include '../config/database.php';

// Ambil ID dari URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Kembalikan stok produk
    $details = mysqli_query($conn, "SELECT * FROM sales_details WHERE sales_id = $id");
    while ($detail = mysqli_fetch_assoc($details)) {
        $conn->query("UPDATE products SET qty = qty + {$detail['qty']} WHERE id = {$detail['product_id']}");
    }
    
    // Hapus detail produk
    $stmt = $conn->prepare("DELETE FROM sales_details WHERE sales_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Hapus sales
    $stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Commit transaksi
    $conn->commit();
    
    header("Location: index.php?success=Sale deleted successfully");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $conn->rollback();
    header("Location: index.php?error=Failed to delete sale: " . urlencode($e->getMessage()));
    exit;
}