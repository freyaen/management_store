<?php
include '../layouts/head.php';
include '../config/database.php';

$errors = [];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data request
$request = mysqli_query($conn, "SELECT * FROM requests WHERE id = $id");
$request = mysqli_fetch_assoc($request);

if (!$request) {
    header("Location: index.php");
    exit;
}

// Ambil detail produk request
$request_details = mysqli_query($conn, "
    SELECT rd.*, p.name, p.code 
    FROM request_details rd
    JOIN products p ON rd.product_id = p.id
    WHERE rd.request_id = $id
");

// Ambil semua produk
$products = mysqli_query($conn, "SELECT id, name, code, buy_price FROM products");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus detail produk lama
        $conn->query("DELETE FROM request_details WHERE request_id = $id");

        $conn->query("UPDATE requests SET request_status = 'menunggu' WHERE id = $id");
        
        // Simpan detail produk baru
        $total_price = 0;
        foreach ($_POST['product_id'] as $index => $product_id) {
            $qty = $_POST['qty'][$index];
            if ($qty > 0) {
                // Ambil harga produk
                $product_query = mysqli_query($conn, "SELECT buy_price FROM products WHERE id = $product_id");
                $product = mysqli_fetch_assoc($product_query);
                $price = $product['buy_price'];
                
                $stmt = $conn->prepare("INSERT INTO request_details (request_id, product_id, qty, price) 
                                        VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $id, $product_id, $qty, $price);
                $stmt->execute();
                
                $total_price += ($price * $qty);
            }
        }
        
        // Commit transaksi
        $conn->commit();
        
        header("Location: index.php?success=Request updated successfully");
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
                        <h3>Requests</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Edit Request</h5>

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3">
                            <?= implode('<br>', $errors); ?>
                        </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Code</label>
                                    <input type="text" class="form-control"
                                        value="<?= htmlspecialchars($request['code']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Date</label>
                                    <input type="text" class="form-control" readonly
                                        value="<?= date('d-m-Y H:i', strtotime($request['request_date'])) ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label>Status</label>
                                    <p class="form-control-static">
                                        <span class="badge 
                                            <?= $request['request_status'] == 'menunggu' ? 'bg-warning' : '' ?>
                                            <?= $request['request_status'] == 'disetujui' ? 'bg-primary' : '' ?>
                                            <?= $request['request_status'] == 'ditolak' ? 'bg-danger' : '' ?>
                                            <?= $request['request_status'] == 'dikirim' ? 'bg-info' : '' ?>
                                            <?= $request['request_status'] == 'selesai' ? 'bg-success' : '' ?>">
                                            <?php 
                                                $statusText = [
                                                    'menunggu' => 'Menunggu',
                                                    'disetujui' => 'Disetujui',
                                                    'ditolak' => 'Ditolak',
                                                    'dikirim' => 'Dikirim',
                                                    'selesai' => 'Selesai'
                                                ];
                                                echo $statusText[$request['request_status']];
                                            ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label>Payment Status</label>
                                    <p class="form-control-static">
                                        <span
                                            class="badge 
                                            <?= $request['payment_status'] == 'belum dibayar' ? 'bg-danger' : 'bg-success' ?>">
                                            <?php 
                                                $paymentText = [
                                                    'belum dibayar' => 'Belum Dibayar',
                                                    'sudah dibayar' => 'Sudah Dibayar'
                                                ];
                                                echo $paymentText[$request['payment_status']];
                                            ?>
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <?php if($request['request_status'] == 'ditolak'): ?>
                            <div>
                                <label>Reject Reason</label>
                                <p class="form-control-static alert alert-warning"><?= $request['reject_reason'] ?></p>
                            </div>
                            <?php endif; ?>

                            <hr>

                            <div class="mb-4 mt-3">
                                <h5 class="mb-3">Request Products</h5>

                                <div id="products-container">
                                    <?php if (mysqli_num_rows($request_details) > 0): ?>
                                    <?php while ($detail = mysqli_fetch_assoc($request_details)): ?>
                                    <div class="row product-row mb-2">
                                        <div class="col-md-4">
                                            <label>Product</label>
                                            <select name="product_id[]" class="form-control product-select" required
                                                onchange="updatePrice(this)">
                                                <option value="">Select Product</option>
                                                <?php 
                                                            mysqli_data_seek($products, 0);
                                                            while ($product = mysqli_fetch_assoc($products)): 
                                                        ?>
                                                <option value="<?= $product['id'] ?>"
                                                    <?= $detail['product_id'] == $product['id'] ? 'selected' : '' ?>
                                                    data-price="<?= $product['buy_price'] ?>">
                                                    <?= htmlspecialchars($product['code'] . ' - ' . $product['name']) ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Quantity</label>
                                            <input type="number" name="qty[]" class="form-control qty-input" min="1"
                                                value="<?= $detail['qty'] ?>" required oninput="calculateTotal(this)">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Price</label>
                                            <input type="number" class="form-control price-input"
                                                value="<?= $detail['price'] ?>" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Total</label>
                                            <input type="number" class="form-control total-input"
                                                value="<?= $detail['price'] * $detail['qty'] ?>" readonly>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-product">
                                                <i class='bx bx-trash'></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <div class="row product-row mb-2">
                                        <div class="col-md-4">
                                            <label>Product</label>
                                            <select name="product_id[]" class="form-control product-select" required
                                                onchange="updatePrice(this)">
                                                <option value="">Select Product</option>
                                                <?php 
                                                        mysqli_data_seek($products, 0);
                                                        while ($product = mysqli_fetch_assoc($products)): 
                                                    ?>
                                                <option value="<?= $product['id'] ?>"
                                                    data-price="<?= $product['buy_price'] ?>">
                                                    <?= htmlspecialchars($product['code'] . ' - ' . $product['name']) ?>
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
                                    <?php endif; ?>
                                </div>

                                <button type="button" id="add-product" class="btn btn-primary text-center mb-3">
                                    Add Product
                                </button>

                                <h5>Grand Total: <span id="grand-total">Rp. 0</span> </h5>
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

        // Calculate initial grand total
        updateGrandTotal();
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

        document.getElementById('grand-total').textContent = 'Rp. ' + grandTotal;
    }
</script>

<?php include '../layouts/tail.php'; ?>