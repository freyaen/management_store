<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php'; // Jika menggunakan Composer untuk TCPDF

use TCPDF as TCPDF;

$id = $_GET['id'] ?? null;

if (!$id) {
    die('Invalid request ID');
}

// Ambil data request
$request = mysqli_query($conn, "SELECT * FROM requests WHERE id = $id");
$request = mysqli_fetch_assoc($request);

if (!$request) {
    die('Request not found');
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

// Buat instance PDF baru
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set dokumen meta-informasi
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistem Manajemen Toko');
$pdf->SetTitle('Invoice #' . $request['code']);
$pdf->SetSubject('Invoice Permintaan');

// Set margin
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Konten invoice
$html = '
<style>
    .header { text-align: center; margin-bottom: 20px; }
    .title { font-size: 20px; font-weight: bold; }
    .subtitle { font-size: 16px; margin-bottom: 10px; }
    .info-table { margin-bottom: 20px; }
    .info-table td { padding: 5px; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .table th { background-color: #f2f2f2; padding: 8px; text-align: left; border: 1px solid #ddd; }
    .table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    .total-row { font-weight: bold; }
    .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
</style>

<div class="header">
    <div class="title">INVOICE PERMINTAAN</div>
    <div class="subtitle">#'.$request['code'].'</div>
</div>

<table class="info-table">
    <tr>
        <td width="50%">
            <strong>Tanggal Permintaan:</strong> '.date('d F Y H:i', strtotime($request['request_date'])).'<br>
            <strong>Status:</strong> '.$statusText[$request['request_status']].'<br>
            <strong>Status Pembayaran:</strong> '.$paymentText[$request['payment_status']].'
        </td>
        <td width="50%" style="text-align: right;">
            <strong>Dikeluarkan oleh:</strong><br>
            Sistem Manajemen Toko<br>
            '.date('d F Y').'
        </td>
    </tr>
</table>

<table class="table">
    <thead>
        <tr>
            <th width="10%">Kode</th>
            <th width="40%">Nama Produk</th>
            <th width="15%">Harga</th>
            <th width="10%">Qty</th>
            <th width="25%">Total</th>
        </tr>
    </thead>
    <tbody>';

while ($detail = mysqli_fetch_assoc($request_details)) {
    $html .= '
        <tr>
            <td>'.$detail['code'].'</td>
            <td>'.$detail['name'].'</td>
            <td>Rp '.number_format($detail['price'], 0, ',', '.').'</td>
            <td>'.$detail['qty'].'</td>
            <td>Rp '.number_format($detail['total_price'], 0, ',', '.').'</td>
        </tr>';
}

$html .= '
        <tr class="total-row">
            <td colspan="4" style="text-align: right;">TOTAL:</td>
            <td>Rp '.number_format($total_price, 0, ',', '.').'</td>
        </tr>
    </tbody>
</table>

<div class="footer">
    Invoice ini dibuat secara otomatis oleh Sistem Manajemen Toko<br>
    Dicetak pada: '.date('d F Y H:i:s').'
</div>';

// Output konten HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('invoice_'.$request['code'].'.pdf', 'I');
?>