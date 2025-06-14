<?php
include '../layouts/head.php';
include '../config/database.php';

$errors = [];
$products = mysqli_query($conn, "SELECT id, name, code FROM products");

// Fungsi generate kode return otomatis
function generateReturnCode($conn) {
    // Hitung jumlah return di tahun ini
    $currentYear = date('Y');
    $query = "SELECT COUNT(*) as total FROM returns WHERE YEAR(return_date) = $currentYear";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $sequence = $row['total'] + 1;
    
    // Format: RET/tanggal/bulan/tahun/nomor urut
    return 'RET/' . date('d') . '/' . date('m') . '/' . date('Y') . '/' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = generateReturnCode($conn);
    
    // Validasi
    if (empty($code)) $errors[] = "Return code is required";
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Mulai transaksi
        $conn->begin_transaction();
        
        try {
            // Simpan data return
            $stmt = $conn->prepare("INSERT INTO returns (code) VALUES (?)");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $return_id = $conn->insert_id;
            
            // Simpan detail produk
            foreach ($_POST['product_id'] as $index => $product_id) {
                $qty = $_POST['qty'][$index];
                $return_reason = $_POST['return_reason'][$index];
                $return_reason_other = $_POST['return_reason_other'][$index] ?? null;
                
                // Handle file upload
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
                
                $stmt = $conn->prepare("INSERT INTO return_details 
                    (return_id, product_id, image, qty, return_reason, return_reason_other) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisiss", $return_id, $product_id, $unique_name, $qty, $return_reason, $return_reason_other);
                $stmt->execute();
            }
            
            // Commit transaksi
            $conn->commit();
            
            header("Location: index.php?success=Return created successfully");
            exit;
        } catch (Exception $e) {
            // Rollback transaksi jika ada error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
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
                        <h3>Returns</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Create Return</h5>

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?= implode('<br>', $errors); ?>
                        </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Code</label>
                                    <input type="text" class="form-control" value="<?= generateReturnCode($conn) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Date</label>
                                    <input type="text" class="form-control" readonly value="<?= date('d-m-Y H:i') ?>">
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h5 class="mb-3">Return Products</h5>

                                <div id="products-container">
                                    <div class="row product-row mb-4 border p-3">
                                        <div class="col-md-4 mb-3">
                                            <label>Product</label>
                                            <select name="product_id[]" class="form-control product-select" required>
                                                <option value="">Select Product</option>
                                                <?php while ($product = mysqli_fetch_assoc($products)): ?>
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

            // Clear selected product
            newRow.querySelector('.product-select').selectedIndex = 0;
            newRow.querySelector('input[name="qty[]"]').value = 1;
            newRow.querySelector('select[name="return_reason[]"]').selectedIndex = 0;
            newRow.querySelector('.reason-other-container').style.display = 'none';
            newRow.querySelector('input[name="return_reason_other[]"]').value = '';
            newRow.querySelector('input[type="file"]').value = '';

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