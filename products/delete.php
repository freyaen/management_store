<?php
include __DIR__  . '/../config/middleware.php';
include '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Product ID is missing.");
}

// Ambil gambar lama untuk dihapus
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Hapus file gambar jika ada
$image_path = __DIR__ . '/../assets/images/products/' . $product['image'];
if (file_exists($image_path)) {
    unlink($image_path);
}

// Hapus data dari database
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php?success=Product deleted successfully");
exit;
?>
