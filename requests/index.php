<?php 
include __DIR__  . '/..//config/middleware.php';
include '../layouts/head.php';
include '../config/database.php';

// Perbaikan query untuk menambahkan total harga
$requests = mysqli_query($conn, "
    SELECT r.*, 
           COUNT(rd.id) AS total_items,
           COALESCE(SUM(rd.qty * rd.price), 0) AS total_price
    FROM requests r
    LEFT JOIN request_details rd ON r.id = rd.request_id
    GROUP BY r.id
    ORDER BY r.request_date DESC
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
                        <h3>Requests</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center mb-3">
                            <h5 class="m-0">Request List</h5>
                            <a href="<?= getDomainUrl() . 'requests/create.php' ?>" class="btn btn-primary">
                                Create Request
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table id="requests" class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($request = mysqli_fetch_assoc($requests)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['code']) ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($request['request_date'])) ?></td>
                                        <td><?= $request['total_items'] ?> items</td>
                                        <td>Rp<?= number_format($request['total_price'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = [
                                                    'menunggu' => 'warning',
                                                    'disetujui' => 'success',
                                                    'ditolak' => 'danger',
                                                    'dikirim' => 'info',
                                                    'selesai' => 'success'
                                                ];
                                                $statusText = [
                                                    'menunggu' => 'Menunggu',
                                                    'disetujui' => 'Disetujui',
                                                    'ditolak' => 'Ditolak',
                                                    'dikirim' => 'Dikirim',
                                                    'selesai' => 'Selesai'
                                                ];
                                            ?>
                                            <span
                                                class="badge bg-<?= $statusClass[$request['request_status']] ?> text-white">
                                                <?= $statusText[$request['request_status']] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $paymentClass = [
                                                    'belum dibayar' => 'danger',
                                                    'sudah dibayar' => 'success'
                                                ];
                                                $paymentText = [
                                                    'belum dibayar' => 'Belum Dibayar',
                                                    'sudah dibayar' => 'Sudah Dibayar'
                                                ];
                                            ?>
                                            <span
                                                class="badge bg-<?= $paymentClass[$request['payment_status']] ?> text-white">
                                                <?= $paymentText[$request['payment_status']] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= getDomainUrl() . 'requests/show.php?id=' . $request['id'] ?>"
                                                class="btn btn-sm btn-info" title="View Detail">
                                                <i class='text-white bx bx-show'></i>
                                            </a>
                                            <?php if($userRole == 2 && $request['request_status'] == 'ditolak') :?>
                                            <a href="<?= getDomainUrl() . 'requests/edit.php?id=' . $request['id'] ?>"
                                                class="btn btn-sm btn-warning" title="Edit">
                                                <i class='text-white bx bx-edit'></i>
                                            </a>
                                            <a href="#"
                                                onclick="confirmDelete(<?= $request['id'] ?>, '<?= getDomainUrl() . 'requests/delete.php?id=' ?>')"
                                                class="btn btn-sm btn-danger" title="Delete">
                                                <i class='text-white bx bx-trash'></i>
                                            </a>
                                            <?php endif; ?>
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