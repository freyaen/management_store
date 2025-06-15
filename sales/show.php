<?php
include __DIR__  . '/../config/middleware.php';
include '../layouts/head.php';
include '../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data sales
$sale = mysqli_query($conn, "SELECT * FROM sales WHERE id = $id");
$sale = mysqli_fetch_assoc($sale);

if (!$sale) {
    header("Location: index.php");
    exit;
}

// Ambil detail produk sales
$sale_details = mysqli_query($conn, "
    SELECT sd.*, p.name, p.code, p.sale_price, (sd.qty * sd.price) AS total_price 
    FROM sales_details sd
    JOIN products p ON sd.product_id = p.id
    WHERE sd.sales_id = $id
");

// Hitung total harga
$total_price = 0;
while ($detail = mysqli_fetch_assoc($sale_details)) {
    $total_price += $detail['total_price'];
}
// Reset pointer
mysqli_data_seek($sale_details, 0);
?>

<div class="layout-wrapper">
    <?php include '../layouts/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../layouts/navbar.php'; ?>

        <div class="content-body">
            <div class="content">
                <div class="page-header">
                    <div>
                        <h3>Sale Detail</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title m-0"><?= htmlspecialchars($sale['code']) ?></h5>
                        </div>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success mb-4">
                                <?= htmlspecialchars($_GET['success']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Sale Date:</strong> 
                                    <?= date('d-m-Y H:i', strtotime($sale['sales_date'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Sold Products</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($detail = mysqli_fetch_assoc($sale_details)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($detail['code']) ?></td>
                                            <td><?= htmlspecialchars($detail['name']) ?></td>
                                            <td><?= $detail['qty'] ?></td>
                                            <td>Rp<?= number_format($detail['price'], 0, ',', '.') ?></td>
                                            <td>Rp<?= number_format($detail['total_price'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th>Rp<?= number_format($total_price, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../layouts/footer.php'; ?>
        </div>
    </div>
</div>

<?php include '../layouts/tail.php'; ?>