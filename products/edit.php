<?php 
include __DIR__  . '/../config/middleware.php';
include 'layouts/head.php';
include '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID not found");
}

// Ambil data produk berdasarkan ID
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found");
}

// Ambil data kategori
$categories = mysqli_query($conn, "SELECT id, name FROM categories");

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'];
    $code = $_POST['code'];
    $name = $_POST['name'];
    $qty = $_POST['qty'];
    $buy_price = $_POST['buy_price'];
    $sale_price = $_POST['sale_price'];
    $expired_date = $_POST['expired_date'];
    $old_image = $product['image'];
    $new_image_name = $old_image;

    // Cek apakah ada file diupload
    if ($_FILES['image']['name']) {
        $image_name = $_FILES['image']['name'];
        $image_tmp  = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
        }

        if ($image_size > 2 * 1024 * 1024) {
            $errors[] = "Image size must be less than 2MB.";
        }

        $target_dir = __DIR__ . '/../assets/images/products/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Simpan dengan nama unik
        $new_image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '', $image_name);
        $image_path = $target_dir . $new_image_name;

        if (empty($errors) && move_uploaded_file($image_tmp, $image_path)) {
            if ($old_image && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET category_id=?, code=?, image=?, name=?, qty=?, buy_price=?, sale_price=?, expired_date=? WHERE id=?");
        $stmt->bind_param("isssiiisi", $category_id, $code, $new_image_name, $name, $qty, $buy_price, $sale_price, $expired_date, $id);
        
        if ($stmt->execute()) {
            header("Location: index.php?success=Product updated successfully");
            exit;
        } else {
            $errors[] = "Update failed: " . $stmt->error;
        }
    }
}
?>

<div class="layout-wrapper">
    <?php include '../layouts/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../layouts/navbar.php'; ?>
        <div class="content-body">
            <div class="content">
                <div class="page-header"><h3>Edit Product</h3></div>
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger"><?= implode('<br>', $errors); ?></div>
                        <?php endif; ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Code</label>
                                <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($product['code']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Image</label><br>
                                <img src="<?= getDomainUrl() . 'assets/images/products/' . $product['image'] ?>" width="80" style="border-radius:4px;"><br><br>
                                <input type="file" name="image" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Quantity</label>
                                <input type="number" name="qty" class="form-control" value="<?= $product['qty'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Buy Price</label>
                                <input type="number" name="buy_price" class="form-control" value="<?= $product['buy_price'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Sale Price</label>
                                <input type="number" name="sale_price" class="form-control" value="<?= $product['sale_price'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Expired Date</label>
                                <input type="datetime-local" name="expired_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($product['expired_date'])) ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php include '../layouts/footer.php'; ?>
        </div>
    </div>
</div>

<?php include '../layouts/tail.php'; ?>
