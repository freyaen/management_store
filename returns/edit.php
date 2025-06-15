<?php 
include __DIR__  . '/..//config/middleware.php';
include '../layouts/head.php';
include '../config/database.php';

$errors = [];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data return
$return = mysqli_query($conn, "SELECT * FROM returns WHERE id = $id");
$return = mysqli_fetch_assoc($return);

if (!$return) {
    header("Location: index.php");
    exit;
}

// Ambil detail produk return
$return_details = mysqli_query($conn, "
    SELECT rd.*, p.name, p.code 
    FROM return_details rd
    JOIN products p ON rd.product_id = p.id
    WHERE rd.return_id = $id
");

// Ambil semua produk
$products = mysqli_query($conn, "SELECT id, name, code FROM products");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus detail produk lama
        $conn->query("DELETE FROM return_details WHERE return_id = $id");
        $conn->query("UPDATE returns SET status = 'menunggu', reject_reason = null WHERE id = $id");
        
        // Simpan detail produk baru
        foreach ($_POST['product_id'] as $index => $product_id) {
            $qty = $_POST['qty'][$index];
            $return_reason = $_POST['return_reason'][$index];
            $return_reason_other = $_POST['return_reason_other'][$index] ?? null;
            
            // Handle file upload
            if (!empty($_FILES['image']['name'][$index])) {
                // Proses upload file baru
                $image_name = $_FILES['image']['name'][$index];
                $image_tmp = $_FILES['image']['tmp_name'][$index];
                $image_size = $_FILES['image']['size'][$index];
                $file_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
                
                $target_dir = __DIR__ . '/../assets/images/returns/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                $unique_name = time() . '_' . $index . '.' . $file_ext;
                $image_path = $target_dir . $unique_name;
                
                if (!in_array($file_ext, $allowed_ext)) {
                    throw new Exception("Only JPG, JPEG, PNG, and WEBP files are allowed for product image.");
                }
                
                if ($image_size > 2 * 1024 * 1024) {
                    throw new Exception("Image size must be less than 2MB for product image.");
                }
                
                if (!move_uploaded_file($image_tmp, $image_path)) {
                    throw new Exception("Failed to upload image for product.");
                }
            } else {
                // Gunakan gambar lama jika tidak ada upload baru
                $unique_name = $_POST['existing_image'][$index];
            }
            
            $stmt = $conn->prepare("INSERT INTO return_details 
                (return_id, product_id, image, qty, return_reason, return_reason_other) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisiss", $id, $product_id, $unique_name, $qty, $return_reason, $return_reason_other);
            $stmt->execute();
        }
        
        // Commit transaksi
        $conn->commit();
        
        header("Location: index.php?success=Return updated successfully");
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        $conn->rollback();
        $errors[] = "Error: " . $e->getMessage();
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
                        <h3>Returns</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Edit Return</h5>

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?= implode('<br>', $errors); ?>
                        </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Code</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($return['code']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Date</label>
                                    <input type="text" class="form-control" readonly 
                                           value="<?= date('d-m-Y H:i', strtotime($return['return_date'])) ?>">
                                </div>
                            </div>

                            <div class="row">
                                 <div class="col-md-6">
                                    <label>Status</label>
                                    <p class="form-control-static">
                                        <span class="badge 
                                            <?= $return['status'] == 'menunggu' ? 'bg-warning' : '' ?>
                                            <?= $return['status'] == 'disetujui' ? 'bg-primary' : '' ?>
                                            <?= $return['status'] == 'ditolak' ? 'bg-danger' : '' ?>">
                                            <?php 
                                                $statusText = [
                                                    'menunggu' => 'Menunggu',
                                                    'disetujui' => 'Disetujui',
                                                    'ditolak' => 'Ditolak',
                                                    'dikirim' => 'Dikirim',
                                                    'selesai' => 'Selesai'
                                                ];
                                                echo $statusText[$return['status']];
                                            ?>
                                        </span>
                                    </p>
                                </div>
                            </div>

                             <?php if($return['status'] == 'ditolak'): ?>
                            <div>
                                <label>Reject Reason</label>
                                <p class="form-control-static alert alert-warning"><?= $return['reject_reason'] ?></p>
                            </div>
                            <?php endif; ?>

                            <hr>

                            <div class="mb-4">
                                <h5 class="mb-3">Return Products</h5>

                                <div id="products-container">
                                    <?php if (mysqli_num_rows($return_details) > 0): ?>
                                        <?php while ($detail = mysqli_fetch_assoc($return_details)): ?>
                                            <div class="row product-row mb-4 border p-3">
                                                <div class="col-md-4 mb-3">
                                                    <label>Product</label>
                                                    <select name="product_id[]" class="form-control product-select" required>
                                                        <option value="">Select Product</option>
                                                        <?php 
                                                            mysqli_data_seek($products, 0);
                                                            while ($product = mysqli_fetch_assoc($products)): 
                                                        ?>
                                                            <option value="<?= $product['id'] ?>" 
                                                                <?= $detail['product_id'] == $product['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($product['code'] . ' - ' . $product['name']) ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label>Quantity</label>
                                                    <input type="number" name="qty[]" class="form-control" min="1"
                                                        value="<?= $detail['qty'] ?>" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label>Return Reason</label>
                                                    <select name="return_reason[]" class="form-control" required
                                                        onchange="toggleReasonOther(this)">
                                                        <option value="rusak" <?= $detail['return_reason'] == 'rusak' ? 'selected' : '' ?>>Rusak</option>
                                                        <option value="salah kirim" <?= $detail['return_reason'] == 'salah kirim' ? 'selected' : '' ?>>Salah Kirim</option>
                                                        <option value="kedaluarsa" <?= $detail['return_reason'] == 'kedaluarsa' ? 'selected' : '' ?>>Kedaluarsa</option>
                                                        <option value="lainnya" <?= $detail['return_reason'] == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                                    </select>
                                                    <div class="reason-other-container mt-2" 
                                                        style="<?= $detail['return_reason'] == 'lainnya' ? 'display: block;' : 'display: none;' ?>">
                                                        <label>Specify Reason</label>
                                                        <input type="text" name="return_reason_other[]" class="form-control"
                                                            value="<?= htmlspecialchars($detail['return_reason_other'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label>Image Evidence</label>
                                                    <input type="file" name="image[]" class="form-control">
                                                    <?php if ($detail['image']): ?>
                                                        <div class="mt-2 current-image">
                                                            <small>Current Image:</small>
                                                            <img src="<?= getDomainUrl() . 'assets/images/returns/' . $detail['image'] ?>" 
                                                                alt="Return Image" width="100" class="mt-1">
                                                            <input type="hidden" name="existing_image[]" value="<?= $detail['image'] ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-product">
                                                        <i class='bx bx-trash'></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="row product-row mb-4 border p-3">
                                            <div class="col-md-4 mb-3">
                                                <label>Product</label>
                                                <select name="product_id[]" class="form-control product-select" required>
                                                    <option value="">Select Product</option>
                                                    <?php 
                                                        mysqli_data_seek($products, 0);
                                                        while ($product = mysqli_fetch_assoc($products)): 
                                                    ?>
                                                        <option value="<?= $product['id'] ?>">
                                                            <?= htmlspecialchars($product['code'] . ' - ' . $product['name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label>Quantity</label>
                                                <input type="number" name="qty[]" class="form-control" min="1" value="1" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label>Return Reason</label>
                                                <select name="return_reason[]" class="form-control" required
                                                    onchange="toggleReasonOther(this)">
                                                    <option value="">Select Reason</option>
                                                    <option value="rusak">Rusak</option>
                                                    <option value="salah kirim">Salah Kirim</option>
                                                    <option value="kedaluarsa">Kedaluarsa</option>
                                                    <option value="lainnya">Lainnya</option>
                                                </select>
                                                <div class="reason-other-container mt-2" style="display: none;">
                                                    <label>Specify Reason</label>
                                                    <input type="text" name="return_reason_other[]" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label>Image Evidence</label>
                                                <input type="file" name="image[]" class="form-control" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-product"
                                                    style="display: none;">
                                                    <i class='bx bx-trash'></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <button type="button" id="add-product" class="btn btn-primary text-center mb-3">
                                    Add Product
                                </button>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Save
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <?php include '../layouts/footer.php'; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Add new product row
        document.getElementById('add-product').addEventListener('click', function () {
            const container = document.getElementById('products-container');
            const firstRow = container.querySelector('.product-row');
            const newRow = firstRow.cloneNode(true);
            const currentImage = newRow.querySelector('.current-image');
                if (currentImage) {
                    currentImage.remove(); // atau currentImage.parentNode.removeChild(currentImage);
                }

            // Reset dropdowns and inputs
            newRow.querySelector('.product-select').selectedIndex = 0;
            newRow.querySelector('input[name="qty[]"]').value = 1;
            newRow.querySelector('select[name="return_reason[]"]').selectedIndex = 0;
            newRow.querySelector('input[name="return_reason_other[]"]').value = '';
            newRow.querySelector('input[type="file"]').value = '';

            // Hide custom reason input
            newRow.querySelector('.reason-other-container').style.display = 'none';

            // Remove image preview if exists
            const preview = newRow.querySelector('.image-preview');
            if (preview) {
                preview.src = ''; // atau default image
                preview.style.display = 'none'; // hide element
            }

            // Optional: if using dropify or custom file uploader, reset it here

            // Show remove button
            newRow.querySelector('.remove-product').style.display = 'block';

            container.appendChild(newRow);
        });


        // Remove product row
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-product')) {
                if (document.querySelectorAll('.product-row').length > 1) {
                    e.target.closest('.product-row').remove();
                }
            }
        });
    });

    function toggleReasonOther(selectElement) {
        const row = selectElement.closest('.product-row');
        const reasonOtherContainer = row.querySelector('.reason-other-container');
        
        if (selectElement.value === 'lainnya') {
            reasonOtherContainer.style.display = 'block';
        } else {
            reasonOtherContainer.style.display = 'none';
        }
    }
</script>

<?php include '../layouts/tail.php'; ?>