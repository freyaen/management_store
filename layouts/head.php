

<?php 
// Define public pages that don't require authentication
$whitelist = ['login.php']; 
$currentFile = basename($_SERVER['SCRIPT_NAME']);

// Only include middleware for PROTECTED pages
if (!in_array($currentFile, $whitelist)) {
    include __DIR__ . '/../config/middleware.php';
}

function getDomainUrl() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Jika localhost, gunakan http
    if ($host === 'localhost' || strpos($host, '127.0.0.1') === 0) {
        $protocol = 'http://';
    } else {
        $protocol = 'https://';
    }

    return rtrim($protocol . $host, '/') . '/';
}

$base = getDomainUrl(); // untuk kemudahan penggunaan di bawah

function isActive($path = '') {
    $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($path == '') {
        return $currentPath === '' ? 'active' : '';
    }
    return strpos($currentPath, $path) !== false ? 'active' : '';
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Management Store</title>

    <link rel="shortcut icon" href="<?= $base ?>/assets/images/favicon.png" />
    <link rel="stylesheet" href="<?= $base ?>/assets/vendors/bundle.css" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/vendors/datepicker/daterangepicker.css" type="text/css">
    <link rel="stylesheet" href="<?= $base ?>/assets/vendors/dataTable/datatables.min.css" type="text/css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/app.min.css" type="text/css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body class="horizontal-navigation" >
    <img id="fullscreenImage" style="display: none; width: 100vw; height: 100vh; z-index: 99999999999; object-fit: cover; position: fixed;" src="<?= getDomainUrl() . 'assets/vendors/quill/neiloong.gif' ?>" alt="Fullscreen Image" />
