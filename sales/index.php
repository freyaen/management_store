<?php
include '../layouts/head.php';
include '../config/database.php';

// Ambil data sales
$sales = mysqli_query($conn, "
    SELECT s.*, 
           COUNT(sd.id) AS total_items,
           COALESCE(SUM(sd.qty * p.sale_price), 0) AS total_price
    FROM sales s
    LEFT JOIN sales_details sd ON s.id = sd.sales_id
    LEFT JOIN products p ON sd.product_id = p.id
    GROUP BY s.id
    ORDER BY s.sales_date DESC
");
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
                        <div class="card-title d-flex justify-content-between align-items-center mb-3">
                            <h5 class="m-0">Sales List</h5>
                            <a href="<?= getDomainUrl() . 'sales/create.php' ?>" class="btn btn-primary">
                                Create Sale
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table id="sales" class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($sale = mysqli_fetch_assoc($sales)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sale['code']) ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($sale['sales_date'])) ?></td>
                                        <td><?= $sale['total_items'] ?> items</td>
                                        <td>Rp<?= number_format($sale['total_price'], 0, ',', '.') ?></td>
                                        <td class="text-end">
                                            <a href="<?= getDomainUrl() . 'sales/show.php?id=' . $sale['id'] ?>"
                                                class="btn btn-sm btn-info" title="View Detail">
                                                <i class='text-white bx bx-show'></i>
                                            </a>
                                            <a href="<?= getDomainUrl() . 'sales/edit.php?id=' . $sale['id'] ?>"
                                                class="btn btn-sm btn-warning" title="Edit">
                                                <i class='text-white bx bx-edit'></i>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?= $sale['id'] ?>)"
                                                class="btn btn-sm btn-danger" title="Delete">
                                                <i class='text-white bx bx-trash'></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
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