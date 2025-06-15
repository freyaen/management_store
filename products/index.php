<?php
include __DIR__  . '/../config/middleware.php';
include '../layouts/head.php';
include '../config/database.php';

// Ambil data produk + kategori
$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="layout-wrapper">
    <?php include '../layouts/header.php'; ?>

    <div class="content-wrapper">
        <?php include '../layouts/navbar.php'; ?>

        <div class="content-body">
            <div class="content ">
                <div class="page-header">
                    <div>
                        <h3>Products</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center mb-3">
                            <h5 class="m-0">Product List</h5>
                            <a href="<?= getDomainUrl() . 'products/create.php' ?>" class="btn btn-primary">Add
                                Product</a>
                        </div>

                        <div class="table-responsive">
                            <table id="products" class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Category</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Buy Price</th>
                                        <th>Sale Price</th>
                                        <th>Input Date</th>
                                        <th>Expired Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['code']) ?></td>
                                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                                        <td>
                                            <img src="<?= getDomainUrl() . 'assets/images/products/' . htmlspecialchars($row['image']) ?>"
                                                alt="Product Image" width="50" height="50"
                                                style="object-fit:cover;border-radius:4px;">
                                        </td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td>
                                            <p class="m-0"><?= $row['qty'] ?></p>
                                            <?php if ($row['qty'] < 5): ?>
                                                <span class="badge bg-danger ms-2">Segera pesan ke gudang</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>Rp<?= number_format($row['buy_price'], 0, ',', '.') ?></td>
                                        <td>Rp<?= number_format($row['sale_price'], 0, ',', '.') ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($row['input_date'])) ?>
                                        </td>
                                        <td>
                                            <p class="m-0"><?= date('d-m-Y H:i', strtotime($row['expired_date'])); ?></p>
                                            <?php
                                            $expiredDate = new DateTime($row['expired_date']);
                                            $today = new DateTime();
                                            $interval = $today->diff($expiredDate);
                                            $daysLeft = (int)$interval->format('%r%a');

                                            if ($daysLeft <= 0): ?>
                                                <span class="text-white badge bg-danger ms-2">Sudah Kadaluarsa</span>
                                            <?php elseif ($daysLeft <= 30 && $daysLeft >= 0): ?>
                                                <span class="text-white badge bg-warning ms-2">Kadaluarsa dalam <?= $daysLeft ?> hari</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= getDomainUrl() . 'products/edit.php?id=' . $row['id'] ?>"
                                                class="btn btn-sm btn-warning" title="Edit">
                                                <i class='text-white bx bx-edit'></i>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-danger" title="Delete">
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