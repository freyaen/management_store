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
    // Hapus detail produk
    $stmt = $conn->prepare("DELETE FROM return_details WHERE return_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Hapus return
    $stmt = $conn->prepare("DELETE FROM returns WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Commit transaksi
    $conn->commit();
    
    header("Location: index.php?success=Return deleted successfully");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $conn->rollback();
    header("Location: index.php?error=Failed to delete return: " . urlencode($e->getMessage()));
    exit;
}