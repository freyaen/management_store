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
    // Hapus detail produk terlebih dahulu karena constraint foreign key
    $stmt = $conn->prepare("DELETE FROM request_details WHERE request_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Hapus request
    $stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Commit transaksi
    $conn->commit();
    
    header("Location: index.php?success=Request deleted successfully");
    exit;
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $conn->rollback();
    header("Location: index.php?error=Failed to delete request: " . urlencode($e->getMessage()));
    exit;
}