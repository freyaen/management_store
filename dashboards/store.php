<?php
// Query for general statistics
$stats = [
    'products' => 0,
    'categories' => 0,
    'today_sales' => 0,
    'pending_requests' => 0
];

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
if ($row = mysqli_fetch_assoc($result)) $stats['products'] = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
if ($row = mysqli_fetch_assoc($result)) $stats['categories'] = $row['total'];

$today = date('Y-m-d');
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM sales WHERE DATE(sales_date) = '$today'");
if ($row = mysqli_fetch_assoc($result)) $stats['today_sales'] = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM requests WHERE request_status = 'menunggu'");
if ($row = mysqli_fetch_assoc($result)) $stats['pending_requests'] = $row['total'];
?>

<!-- Main Statistics -->
<div class="row mb-2">
    <div class="col-md-3">
        <div class="card border-left-primary shadow">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Total Products</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['products'] ?>

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-success shadow">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Categories</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['categories'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-info shadow">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                    Today's Sales</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['today_sales'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-warning shadow">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                    Pending Requests</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= $stats['pending_requests'] ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Top Stocked Products</h6>
            </div>
            <div class="card-body">
                <?php
                $query = "SELECT name, qty FROM products ORDER BY qty DESC LIMIT 5";
                $result = mysqli_query($conn, $query);
                ?>
                <ul class="list-group">
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($product['name']) ?>
                        <span class="badge bg-primary rounded-pill"><?= $product['qty'] ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h6 class="m-0 font-weight-bold">Low Stock Products</h6>
            </div>
            <div class="card-body">
                <?php
                $query = "SELECT name, qty FROM products WHERE qty < 10 ORDER BY qty ASC LIMIT 5";
                $result = mysqli_query($conn, $query);
                ?>
                <ul class="list-group">
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($product['name']) ?>
                        <span class="badge bg-danger rounded-pill"><?= $product['qty'] ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-info text-white">
        <h6 class="m-0 font-weight-bold">Latest Activities</h6>
    </div>
    <div class="card-body">
        <div class="list-group">
            <?php
            // Query latest activities
            $query = "(
                SELECT 'Request' as type, code, request_date as date 
                FROM requests 
                ORDER BY request_date DESC 
                LIMIT 5
            ) UNION (
                SELECT 'Sale' as type, code, sales_date as date 
                FROM sales 
                ORDER BY sales_date DESC 
                LIMIT 5
            ) UNION (
                SELECT 'Return' as type, code, return_date as date 
                FROM returns 
                ORDER BY return_date DESC 
                LIMIT 5
            ) ORDER BY date DESC LIMIT 5";

            $result = mysqli_query($conn, $query);

            while ($activity = mysqli_fetch_assoc($result)):
                $date = date('d M H:i', strtotime($activity['date']));
                $typeClass = [
                    'Request' => 'primary',
                    'Sale' => 'success',
                    'Return' => 'danger'
                ];
            ?>
            <a href="#" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        <span class="badge bg-<?= $typeClass[$activity['type']] ?> me-2">
                            <?= $activity['type'] ?>
                        </span>
                        <?= $activity['code'] ?>
                    </h6>
                    <small><?= $date ?></small>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>