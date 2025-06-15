<?php
include __DIR__  . '/../config/middleware.php';
include '../layouts/head.php';
include '../config/database.php';

// Ambil data returns
$returns = mysqli_query($conn, "
    SELECT r.*, 
           COUNT(rd.id) AS total_items
    FROM returns r
    LEFT JOIN return_details rd ON r.id = rd.return_id
    GROUP BY r.id
    ORDER BY r.return_date DESC
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
                        <h3>Returns</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center mb-3">
                            <h5 class="m-0">Return List</h5>
                            <?php if($userRole == 2): ?>
                            <a href="<?= getDomainUrl() . 'returns/create.php' ?>" class="btn btn-primary">
                                Create Return
                            </a>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive">
                            <table id="returns" class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($return = mysqli_fetch_assoc($returns)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($return['code']) ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($return['return_date'])) ?></td>
                                        <td><?= $return['total_items'] ?> items</td>
                                        <td>
                                            <?php 
                                                $statusClass = [
                                                    'menunggu' => 'warning',
                                                    'ditolak' => 'danger',
                                                    'disetujui' => 'success'
                                                ];
                                                $statusText = [
                                                    'menunggu' => 'Menunggu',
                                                    'ditolak' => 'Ditolak',
                                                    'disetujui' => 'Disetujui'
                                                ];
                                            ?>
                                            <span class="badge bg-<?= $statusClass[$return['status']] ?> text-white">
                                                <?= $statusText[$return['status']] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= getDomainUrl() . 'returns/show.php?id=' . $return['id'] ?>"
                                                class="btn btn-sm btn-info" title="View Detail">
                                                <i class='text-white bx bx-show'></i>
                                            </a>
                                            <?php if($userRole == 2 && $return['status'] == 'ditolak') :?>
                                            <a href="<?= getDomainUrl() . 'returns/edit.php?id=' . $return['id'] ?>"
                                                class="btn btn-sm btn-warning" title="Edit">
                                                <i class='text-white bx bx-edit'></i>
                                            </a>
                                            <a href="#" onclick="confirmDelete(<?= $return['id'] ?>)"
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