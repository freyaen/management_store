<?php
include '../layouts/head.php';
include '../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data return
$return = mysqli_query($conn, "SELECT * FROM returns WHERE id = $id");
$return = mysqli_fetch_assoc($return);

if (!$return) {
    header("Location: index.php");
    exit;
}

// Ambil detail produk return
$return_details = mysqli_query($conn, "
    SELECT rd.*, p.name, p.code 
    FROM return_details rd
    JOIN products p ON rd.product_id = p.id
    WHERE rd.return_id = $id
");

// Status mapping
$statusText = [
    'menunggu' => 'Menunggu',
    'disetujui' => 'Disetujui',
    'ditolak' => 'Ditolak'
];

// Proses update status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'setujui') {
        // Mulai transaksi
        $conn->begin_transaction();
        
        try {
            // Ubah status menjadi disetujui
            $stmt = $conn->prepare("UPDATE returns SET status = 'disetujui' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Tambahkan stok produk
            $details = mysqli_query($conn, "SELECT * FROM return_details WHERE return_id = $id");
            while ($detail = mysqli_fetch_assoc($details)) {
                $conn->query("UPDATE products SET qty = qty - {$detail['qty']} WHERE id = {$detail['product_id']}");
            }
            
            // Commit transaksi
            $conn->commit();
            
            header("Location: show.php?id=$id&success=Return approved and stock added successfully");
            exit;
        } catch (Exception $e) {
            // Rollback transaksi jika ada error
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    } 
    elseif ($action === 'tolak') {
        $reject_reason = $_POST['reject_reason'] ?? '';
        
        // Ubah status menjadi ditolak dengan alasan penolakan
        $stmt = $conn->prepare("UPDATE returns SET status = 'ditolak', reject_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reject_reason, $id);
        $stmt->execute();
        
        header("Location: show.php?id=$id&success=Return rejected successfully");
        exit;
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
                        <h3>Return Detail</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title m-0"><?= htmlspecialchars($return['code']) ?></h5>
                            <div>
                                <strong>Date:</strong> 
                                <?= date('d-m-Y H:i', strtotime($return['return_date'])) ?>
                            </div>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>Status:</strong> 
                                    <span class="text-white badge 
                                        <?= $return['status'] == 'menunggu' ? 'bg-warning' : '' ?>
                                        <?= $return['status'] == 'disetujui' ? 'bg-success' : '' ?>
                                        <?= $return['status'] == 'ditolak' ? 'bg-danger' : '' ?>">
                                        <?= $statusText[$return['status']] ?>
                                    </span>
                                </div>
                                <?php if ($return['reject_reason']): ?>
                                    <div class="mb-2">
                                        <strong>Reject Reason:</strong> 
                                        <?= htmlspecialchars($return['reject_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Return Products</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Return Reason</th>
                                        <th>Evidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($detail = mysqli_fetch_assoc($return_details)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($detail['code']) ?></td>
                                            <td><?= htmlspecialchars($detail['name']) ?></td>
                                            <td><?= $detail['qty'] ?></td>
                                            <td>
                                                <?php 
                                                    $reasonText = [
                                                        'rusak' => 'Rusak',
                                                        'salah kirim' => 'Salah Kirim',
                                                        'kedaluarsa' => 'Kedaluarsa',
                                                        'lainnya' => 'Lainnya: ' . ($detail['return_reason_other'] ?? '')
                                                    ];
                                                    echo $reasonText[$detail['return_reason']];
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($detail['image']): ?>
                                                    <img src="<?= getDomainUrl() . 'assets/images/returns/' . $detail['image'] ?>" 
                                                         alt="Return Evidence" width="100">
                                                <?php else: ?>
                                                    No image
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Action buttons based on status -->
                        <?php if ($return['status'] === 'menunggu'): ?>
                        <div class="border-top pt-4">
                            <div class="row">
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
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php include '../layouts/footer.php'; ?>
        </div>
    </div>
</div>

<?php include '../layouts/tail.php'; ?>