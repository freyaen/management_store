<?php
include '../layouts/head.php';
include '../config/database.php';

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
    SELECT rd.*, p.name, p.code, p.sale_price, (rd.qty * rd.price) AS total_price 
    FROM request_details rd
    JOIN products p ON rd.product_id = p.id
    WHERE rd.request_id = $id
");

// Hitung total harga
$total_price = 0;
while ($detail = mysqli_fetch_assoc($request_details)) {
    $total_price += $detail['total_price'];
}
// Reset pointer
mysqli_data_seek($request_details, 0);

// Status mapping
$statusText = [
    'menunggu' => 'Menunggu',
    'disetujui' => 'Disetujui',
    'ditolak' => 'Ditolak',
    'dikirim' => 'Dikirim',
    'selesai' => 'Selesai'
];

$paymentText = [
    'belum dibayar' => 'Belum Dibayar',
    'sudah dibayar' => 'Sudah Dibayar'
];

// Proses update status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        switch ($action) {
            case 'setujui':
                if ($request['request_status'] === 'menunggu') {
                    $conn->query("UPDATE requests SET request_status = 'disetujui' WHERE id = $id");
                }
                break;
                
            case 'tolak':
                if ($request['request_status'] === 'menunggu') {
                    $reject_reason = $_POST['reject_reason'] ?? '';
                    $stmt = $conn->prepare("UPDATE requests SET request_status = 'ditolak', reject_reason = ? WHERE id = $id");
                    $stmt->bind_param("s", $reject_reason);
                    $stmt->execute();
                }
                break;
                
            case 'pembayaran':
                if ($request['request_status'] === 'disetujui') {
                    $payment_status = $_POST['payment_status'] === 'sudah dibayar' ? 'sudah dibayar' : 'belum dibayar';
                    $conn->query("UPDATE requests SET payment_status = '$payment_status' WHERE id = $id");
                }
                break;
                
            case 'kirim':
                if ($request['request_status'] === 'disetujui' && $request['payment_status'] === 'sudah dibayar') {
                    $conn->query("UPDATE requests SET request_status = 'dikirim' WHERE id = $id");
                }
                break;
                
            case 'selesai':
                if ($request['request_status'] === 'dikirim') {
                    // Update status request
                    $conn->query("UPDATE requests SET request_status = 'selesai' WHERE id = $id");
                    
                    // Tambahkan stok produk
                    $details = mysqli_query($conn, "SELECT * FROM request_details WHERE request_id = $id");
                    while ($detail = mysqli_fetch_assoc($details)) {
                        $conn->query("UPDATE products SET qty = qty + {$detail['qty']} WHERE id = {$detail['product_id']}");
                    }
                }
                break;
        }
        
        // Commit transaksi
        $conn->commit();
        header("Location: show.php?id=$id&success=Status updated successfully");
        exit;
    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
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
                        <h3>Request Detail</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="card-title mb-3 d-flex justify-content-between align-items-center">
                            <h5><?= htmlspecialchars($request['code']) ?></h5>
                            <div>
                                <strong>Date:</strong>
                                <?= date('d-m-Y H:i', strtotime($request['request_date'])) ?>
                            </div>
                        </div>

                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger mb-4">
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex mb-4 justify-content-between">
                            <div>
                                <strong>Status:</strong>
                                <span class="text-white badge 
                                        <?= $request['request_status'] == 'menunggu' ? 'bg-warning' : '' ?>
                                        <?= $request['request_status'] == 'disetujui' ? 'bg-success' : '' ?>
                                        <?= $request['request_status'] == 'ditolak' ? 'bg-danger' : '' ?>
                                        <?= $request['request_status'] == 'dikirim' ? 'bg-info' : '' ?>
                                        <?= $request['request_status'] == 'selesai' ? 'bg-success' : '' ?>">
                                    <?= $statusText[$request['request_status']] ?>
                                </span>
                            </div>
                            <div>
                                <div class="mb-2">
                                    <strong>Payment Status:</strong>
                                    <span
                                        class="text-white badge 
                                        <?= $request['payment_status'] == 'belum dibayar' ? 'bg-danger' : 'bg-success' ?>">
                                        <?= $paymentText[$request['payment_status']] ?>
                                    </span>
                                </div>
                                <?php if ($request['reject_reason']): ?>
                                <div class="mb-2">
                                    <strong>Reject Reason:</strong>
                                    <?= htmlspecialchars($request['reject_reason']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="mb-3">Requested Products</h5>
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
                                    <?php while ($detail = mysqli_fetch_assoc($request_details)): ?>
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

                        <!-- Action buttons based on status -->
                        <div class="border-top pt-4">
                            <div class="row">
                                <?php if ($userRole == 1 && $request['request_status'] === 'menunggu'): ?>
                                <div class="col-md-6 mb-3">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="setujui">
                                        <button type="submit" class="btn btn-success">
                                            Setujui
                                        </button>
                                    </form>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="tolak">
                                        <div class="input-group">
                                            <input type="text" name="reject_reason" class="form-control"
                                                placeholder="Alasan penolakan" required>
                                            <button type="submit" class="btn btn-danger">
                                                Tolak
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>

                                <?php if ($userRole == 2 && $request['request_status'] === 'disetujui' && $request['payment_status'] === 'belum dibayar'): ?>
                                <div class="col-md-6 mb-3">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="pembayaran">
                                        <div class="input-group">
                                            <input type="hidden" name="payment_status" value="sudah dibayar">
                                            <button type="submit" class="btn btn-primary">
                                                Bayar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>

                                <?php if ($userRole == 1 && $request['request_status'] === 'disetujui' && $request['payment_status'] === 'sudah dibayar'): ?>
                                <div class="col-md-6 mb-3">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="kirim">
                                        <button type="submit" class="btn btn-info">
                                            Kirim
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>

                                <?php if ($userRole == 2 && $request['request_status'] === 'dikirim'): ?>
                                <div class="col-md-6 mb-3">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="selesai">
                                        <button type="submit" class="btn btn-success">
                                            Selesai
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../layouts/footer.php'; ?>
        </div>
    </div>
</div>

<?php include '../layouts/tail.php'; ?>