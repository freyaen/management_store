<?php 
include __DIR__  . '/..//config/middleware.php';
include '../layouts/head.php';
include '../config/database.php';

$errors = [];
$products = mysqli_query($conn, "SELECT id, name, code, qty, sale_price FROM products");

// Fungsi generate kode sales otomatis
function generateSalesCode($conn) {
    // Hitung jumlah sales di tahun ini
    $currentYear = date('Y');
    $query = "SELECT COUNT(*) as total FROM sales WHERE YEAR(sales_date) = $currentYear";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $sequence = $row['total'] + 1;
    
    // Format: SAL/tanggal/bulan/tahun/nomor urut
    return 'SAL/' . date('d') . '/' . date('m') . '/' . date('Y') . '/' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = generateSalesCode($conn);
    
    // Validasi
    if (empty($code)) $errors[] = "Sales code is required";
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Mulai transaksi
        $conn->begin_transaction();
        
        try {
            // Simpan data sales
            $stmt = $conn->prepare("INSERT INTO sales (code) VALUES (?)");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $sales_id = $conn->insert_id;
            
            // Simpan detail produk
            $total_price = 0;
            foreach ($_POST['product_id'] as $index => $product_id) {
                $qty = $_POST['qty'][$index];
                if ($qty > 0) {
                    // Ambil harga produk
                    $product_query = mysqli_query($conn, "SELECT sale_price FROM products WHERE id = $product_id");
                    $product = mysqli_fetch_assoc($product_query);
                    $price = $product['sale_price'];
                    
                    $stmt = $conn->prepare("INSERT INTO sales_details (sales_id, product_id, qty, price) 
                                        VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiid", $sales_id, $product_id, $qty, $price);
                    $stmt->execute();
                    
                    // Kurangi stok produk
                    $conn->query("UPDATE products SET qty = qty - $qty WHERE id = $product_id");
                    
                    $total_price += ($price * $qty);
                }
            }
            
            // Commit transaksi
            $conn->commit();
            
            header("Location: index.php?success=Sale created successfully");
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
                        <h3>Sales</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Create Sale</h5>

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?= implode('<br>', $errors); ?>
                        </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Code</label>
                                    <input type="text" class="form-control" value="<?= generateSalesCode($conn) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Date</label>
                                    <input type="text" class="form-control" readonly value="<?= date('d-m-Y H:i') ?>">
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h5 class="mb-3">Sold Products</h5>

                                <div id="products-container">
                                    <div class="row product-row mb-2">
                                        <div class="col-md-4">
                                            <label>Product</label>
                                            <select name="product_id[]" class="form-control product-select" required
                                                onchange="updatePrice(this)">
                                                <option value="">Select Product</option>
                                                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                                <option value="<?= $product['id'] ?>"
                                                    data-price="<?= $product['sale_price'] ?>"
                                                    data-stock="<?= $product['qty'] ?>">
                                                    <?= htmlspecialchars($product['code'] . ' - ' . $product['name']) ?>
                                                    (Stock: <?= $product['qty'] ?>)
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Quantity</label>
                                            <input type="number" name="qty[]" class="form-control qty-input" min="1"
                                                value="1" required oninput="calculateTotal(this)">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Price</label>
                                            <input type="number" class="form-control price-input" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Total</label>
                                            <input type="number" class="form-control total-input" readonly>
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

                                <h5>Grand Total: <span id="grand-total">Rp 0</span> </h5>
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
            newRow.querySelector('.qty-input').value = 1;
            newRow.querySelector('.price-input').value = '';
            newRow.querySelector('.total-input').value = '';

            // Show remove button
            newRow.querySelector('.remove-product').style.display = 'block';

            container.appendChild(newRow);

            // Update grand total
            updateGrandTotal();
        });

        // Remove product row
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-product')) {
                if (document.querySelectorAll('.product-row').length > 1) {
                    e.target.closest('.product-row').remove();
                    updateGrandTotal();
                }
            }
        });

        // Initialize prices for existing rows
        document.querySelectorAll('.product-select').forEach(select => {
            if (select.value) {
                updatePrice(select);
            }
        });
    });

    function updatePrice(selectElement) {
        const row = selectElement.closest('.product-row');
        const priceInput = row.querySelector('.price-input');
        const totalInput = row.querySelector('.total-input');
        const qtyInput = row.querySelector('.qty-input');

        if (selectElement.value) {
            const price = selectElement.options[selectElement.selectedIndex].dataset.price;
            priceInput.value = price;
            calculateTotal(qtyInput);
        } else {
            priceInput.value = '';
            totalInput.value = '';
        }
    }

    function calculateTotal(inputElement) {
        const row = inputElement.closest('.product-row');
        const priceInput = row.querySelector('.price-input');
        const totalInput = row.querySelector('.total-input');
        const qty = inputElement.value;

        if (priceInput.value && qty) {
            const price = priceInput.value;
            const total = price * qty;
            totalInput.value = total;
        } else {
            totalInput.value = '';
        }

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let grandTotal = 0;

        document.querySelectorAll('.total-input').forEach(input => {
            if (input.value) {
                grandTotal += parseInt(input.value);
            }
        });

        document.getElementById('grand-total').textContent = 'Rp ' + grandTotal;
    }
</script>

<?php include '../layouts/tail.php'; ?>