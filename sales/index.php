<?php
include '../layouts/head.php';
include '../config/database.php';

// Fungsi untuk menghitung statistik penjualan berdasarkan rentang waktu
function getSalesStatistics($conn, $startDate, $endDate) {
    $query = "
        SELECT 
            COUNT(s.id) AS total_transactions,
            COALESCE(SUM(sd.qty * p.sale_price), 0) AS total_revenue,
            COALESCE(SUM(sd.qty), 0) AS total_items_sold
        FROM sales s
        LEFT JOIN sales_details sd ON s.id = sd.sales_id
        LEFT JOIN products p ON sd.product_id = p.id
        WHERE s.sales_date BETWEEN '$startDate' AND '$endDate 23:59:59'
    ";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Tangkap parameter filter
$filter = $_GET['filter'] ?? 'monthly';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Hitung tanggal berdasarkan filter yang dipilih
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$weekStart = date('Y-m-d', strtotime('monday this week'));
$monthStart = date('Y-m-01');
$yearStart = date('Y-01-01');

// Set rentang tanggal berdasarkan filter
switch ($filter) {
    case 'daily':
        $startDate = $today;
        $endDate = $today;
        break;
    case 'yesterday':
        $startDate = $yesterday;
        $endDate = $yesterday;
        break;
    case 'weekly':
        $startDate = $weekStart;
        $endDate = $today;
        break;
    case 'monthly':
        $startDate = $monthStart;
        $endDate = $today;
        break;
    case 'yearly':
        $startDate = $yearStart;
        $endDate = $today;
        break;
    case 'custom':
        // Gunakan tanggal yang dipilih user
        break;
    default:
        $filter = 'monthly';
        $startDate = $monthStart;
        $endDate = $today;
}

// Ambil statistik penjualan
$stats = getSalesStatistics($conn, $startDate, $endDate);

// Ambil data sales dengan filter
$whereClause = "WHERE s.sales_date BETWEEN '$startDate' AND '$endDate 23:59:59'";
$salesQuery = "
    SELECT s.*, 
           COUNT(sd.id) AS total_items,
           COALESCE(SUM(sd.qty * p.sale_price), 0) AS total_price
    FROM sales s
    LEFT JOIN sales_details sd ON s.id = sd.sales_id
    LEFT JOIN products p ON sd.product_id = p.id
    $whereClause
    GROUP BY s.id
    ORDER BY s.sales_date DESC
";

$sales = mysqli_query($conn, $salesQuery);
?>


<style>
    .card.border-success, .card.border-primary, .card.border-info, .card.border-warning {
        border-top: 4px solid !important;
        border-color: #5066E1 !important;
    }
    .bg-success-light {
        background-color: rgb(40, 199, 111, 0.15) !important;
    }
    .bg-primary-light {
        background-color: rgb(89, 105, 255, 0.15) !important;
    }
    .bg-info-light {
        background-color: rgb(45, 202, 234, 0.15) !important;
    }
    .bg-warning-light {
        background-color: rgb(255, 159, 67, 0.15) !important;
    }
</style>


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

                <!-- Sales Report Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title m-0">Sales Report</h5>
                            <div>
                                <form method="GET" class="d-flex align-items-center">
                                    <div class="mx-2">
                                        <label class="form-label">Period</label>
                                        <select name="filter" class="form-control" onchange="this.form.submit()">
                                            <option value="daily" <?= $filter === 'daily' ? 'selected' : '' ?>>Today</option>
                                            <option value="yesterday" <?= $filter === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                                            <option value="weekly" <?= $filter === 'weekly' ? 'selected' : '' ?>>This Week</option>
                                            <option value="monthly" <?= $filter === 'monthly' ? 'selected' : '' ?>>This Month</option>
                                            <option value="yearly" <?= $filter === 'yearly' ? 'selected' : '' ?>>This Year</option>
                                            <option value="custom" <?= $filter === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                                        </select>
                                    </div>
                                    
                                    <?php if ($filter === 'custom'): ?>
                                    <div class="d-flex mx-2">
                                        <div class="mx-2">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                                        </div>
                                        <div class="mx-2">
                                            <label class="form-label">End Date</label>
                                            <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                                        </div>
                                        <div class="d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary">Apply</button>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Total Revenue Card -->
                            <div class="col-md-3">
                                <div class="card border-success mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-muted">Total Revenue</h6>
                                                <h4 class="card-text fw-bold text-success">
                                                    Rp<?= number_format($stats['total_revenue'], 0, ',', '.') ?>
                                                </h4>
                                            </div>
                                            <div class="bg-success p-3 rounded-circle">
                                                <i class="bx bx-dollar fs-2 text-white"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-success-light text-success">
                                                <i class="bx bx-trending-up"></i> 
                                                <?= date('d M', strtotime($startDate)) ?> - <?= date('d M', strtotime($endDate)) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transactions Card -->
                            <div class="col-md-3">
                                <div class="card border-primary mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-muted">Transactions</h6>
                                                <h4 class="card-text fw-bold text-primary">
                                                    <?= number_format($stats['total_transactions'], 0, ',', '.') ?>
                                                </h4>
                                            </div>
                                            <div class="bg-primary p-3 rounded-circle">
                                                <i class="bx bx-receipt fs-2 text-white"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-primary-light text-primary">
                                                <i class="bx bx-trending-up"></i> 
                                                <?= $filter === 'daily' ? 'Today' : ucfirst($filter) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Sold Card -->
                            <div class="col-md-3">
                                <div class="card border-info mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-muted">Items Sold</h6>
                                                <h4 class="card-text fw-bold text-info">
                                                    <?= number_format($stats['total_items_sold'], 0, ',', '.') ?>
                                                </h4>
                                            </div>
                                            <div class="bg-info p-3 rounded-circle">
                                                <i class="bx bx-package fs-2 text-white"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-info-light text-info">
                                                <i class="bx bx-trending-up"></i> 
                                                <?= $filter === 'daily' ? 'Today' : ucfirst($filter) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Avg. Transaction Card -->
                            <div class="col-md-3">
                                <div class="card border-warning mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title text-muted">Avg. Transaction</h6>
                                                <h4 class="card-text fw-bold text-warning">
                                                    Rp<?= number_format($stats['total_transactions'] > 0 ? ($stats['total_revenue'] / $stats['total_transactions']) : 0, 0, ',', '.') ?>
                                                </h4>
                                            </div>
                                            <div class="bg-warning p-3 rounded-circle">
                                                <i class="bx bx-calculator fs-2 text-white"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-warning-light text-warning">
                                                <i class="bx bx-trending-up"></i> 
                                                <?= $filter === 'daily' ? 'Today' : ucfirst($filter) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Sales Report Card -->

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