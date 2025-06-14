<?php
include '../layouts/head.php';
include '../config/database.php';

// Ambil data kategori untuk select option
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

    // Upload file
    $image_name = $_FILES['image']['name'];
    $image_tmp  = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $file_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    $target_dir = __DIR__ . '/../assets/images/products/';
    
    // Cek apakah direktori upload ada, jika tidak buat
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Validasi ekstensi file
    if (!in_array($file_ext, $allowed_ext)) {
        $errors[] = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
    }

    // Validasi ukuran maksimal 2MB
    if ($image_size > 2 * 1024 * 1024) {
        $errors[] = "Image size must be less than 2MB.";
    }

    // Simpan dengan nama unik agar tidak tertimpa
    $unique_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '', $image_name);
    $image_path = $target_dir . $unique_name;

    if (empty($errors) && !move_uploaded_file($image_tmp, $image_path)) {
        $errors[] = "Failed to upload image.";
    }

    // Insert data jika tidak ada error
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (category_id, code, image, name, qty, buy_price, sale_price, expired_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiiis", $category_id, $code, $unique_name, $name, $qty, $buy_price, $sale_price, $expired_date);

        if ($stmt->execute()) {
            header("Location: index.php?success=Product created successfully");
            exit;
        } else {
            $errors[] = "Insert failed: " . $stmt->error;
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
                <div class="page-header">
                    <div>
                        <h3>Products</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Create Product</h5>
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?= implode('<br>', $errors); ?>
                        </div>
                        <?php endif; ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Code</label>
                                <input type="text" name="code" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Image</label>
                                <input type="file" name="image" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Quantity</label>
                                <input type="number" name="qty" class="form-control" value="0" required>
                            </div>
                            <div class="mb-3">
                                <label>Buy Price</label>
                                <input type="number" name="buy_price" class="form-control" value="0" required>
                            </div>
                            <div class="mb-3">
                                <label>Sale Price</label>
                                <input type="number" name="sale_price" class="form-control" value="0" required>
                            </div>
                            <div class="mb-3">
                                <label>Expired Date</label>
                                <input type="datetime-local" name="expired_date" class="form-control" required>
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
