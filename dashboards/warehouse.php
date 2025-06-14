<?php
// Query untuk data returns
$returnQuery = "SELECT 
                r.id, 
                r.code, 
                r.return_date, 
                r.status, 
                COUNT(rd.id) AS total_items,
                COALESCE(SUM(rd.qty * p.sale_price), 0) AS total_value
            FROM returns r
            LEFT JOIN return_details rd ON r.id = rd.return_id
            LEFT JOIN products p ON rd.product_id = p.id
            GROUP BY r.id
            ORDER BY r.return_date DESC";
$returns = mysqli_query($conn, $returnQuery);

// Query untuk data requests
$requestQuery = "SELECT 
                r.id, 
                r.code, 
                r.request_date, 
                r.request_status, 
                r.payment_status,
                COUNT(rd.id) AS total_items,
                COALESCE(SUM(rd.qty * rd.price), 0) AS total_price
            FROM requests r
            LEFT JOIN request_details rd ON r.id = rd.request_id
            GROUP BY r.id
            ORDER BY r.request_date DESC";
$requests = mysqli_query($conn, $requestQuery);

// Statistik untuk dashboard
$stats = [
    'requests' => [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0
    ],
    'returns' => [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0
    ]
];

// Hitung statistik requests
$requestStats = mysqli_query($conn, "SELECT request_status, COUNT(*) as count FROM requests GROUP BY request_status");
while ($row = mysqli_fetch_assoc($requestStats)) {
    $stats['requests']['total'] += $row['count'];
    if ($row['request_status'] == 'menunggu') $stats['requests']['pending'] = $row['count'];
    if ($row['request_status'] == 'disetujui') $stats['requests']['approved'] = $row['count'];
    if ($row['request_status'] == 'ditolak') $stats['requests']['rejected'] = $row['count'];
}

// Hitung statistik returns
$returnStats = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM returns GROUP BY status");
while ($row = mysqli_fetch_assoc($returnStats)) {
    $stats['returns']['total'] += $row['count'];
    if ($row['status'] == 'menunggu') $stats['returns']['pending'] = $row['count'];
    if ($row['status'] == 'disetujui') $stats['returns']['approved'] = $row['count'];
    if ($row['status'] == 'ditolak') $stats['returns']['rejected'] = $row['count'];
}
?>

<!-- Statistik Utama -->
<div class="row mb-2">
    <div class="col-md-4">
        <div class="card border-left-primary shadow ">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Total Requests</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['requests']['total'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-left-success shadow ">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Approved Requests</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['requests']['approved'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-left-danger shadow ">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                    Total Returns</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['returns']['total'] ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Request Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <div class="text-lg text-primary"><?= $stats['requests']['pending'] ?></div>
                        <div class="text-muted">Pending</div>
                    </div>
                    <div>
                        <div class="text-lg text-success"><?= $stats['requests']['approved'] ?></div>
                        <div class="text-muted">Approved</div>
                    </div>
                    <div>
                        <div class="text-lg text-danger"><?= $stats['requests']['rejected'] ?></div>
                        <div class="text-muted">Rejected</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h6 class="m-0 font-weight-bold">Return Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <div class="text-lg text-warning"><?= $stats['returns']['pending'] ?></div>
                        <div class="text-muted">Pending</div>
                    </div>
                    <div>
                        <div class="text-lg text-success"><?= $stats['returns']['approved'] ?></div>
                        <div class="text-muted">Approved</div>
                    </div>
                    <div>
                        <div class="text-lg text-danger"><?= $stats['returns']['rejected'] ?></div>
                        <div class="text-muted">Rejected</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Requests Terbaru -->
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="m-0 font-weight-bold">Recent Requests</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = mysqli_fetch_assoc($requests)): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['code']) ?></td>
                        <td><?= date('d M Y', strtotime($request['request_date'])) ?></td>
                        <td><?= $request['total_items'] ?> items</td>
                        <td>Rp<?= number_format($request['total_price'], 0, ',', '.') ?></td>
                        <td>
                            <?php 
                                $statusClass = [
                                    'menunggu' => 'warning',
                                    'disetujui' => 'success',
                                    'ditolak' => 'danger',
                                    'dikirim' => 'info',
                                    'selesai' => 'primary'
                                ];
                                $statusText = [
                                    'menunggu' => 'Menunggu',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    'dikirim' => 'Dikirim',
                                    'selesai' => 'Selesai'
                                ];
                            ?>
                            <span class="badge bg-<?= $statusClass[$request['request_status']] ?>">
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
                            <span class="badge bg-<?= $paymentClass[$request['payment_status']] ?>">
                                <?= $paymentText[$request['payment_status']] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabel Returns Terbaru -->
<div class="card shadow">
    <div class="card-header bg-danger text-white">
        <h6 class="m-0 font-weight-bold">Recent Returns</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($return = mysqli_fetch_assoc($returns)): ?>
                    <tr>
                        <td><?= htmlspecialchars($return['code']) ?></td>
                        <td><?= date('d M Y', strtotime($return['return_date'])) ?></td>
                        <td><?= $return['total_items'] ?> items</td>
                        <td>Rp<?= number_format($return['total_value'], 0, ',', '.') ?></td>
                        <td>
                            <?php 
                                $statusClass = [
                                    'menunggu' => 'warning',
                                    'disetujui' => 'success',
                                    'ditolak' => 'danger'
                                ];
                                $statusText = [
                                    'menunggu' => 'Menunggu',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak'
                                ];
                            ?>
                            <span class="badge bg-<?= $statusClass[$return['status']] ?>">
                                <?= $statusText[$return['status']] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>