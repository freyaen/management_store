<?php ob_start(); // Start output buffering at very top

// Skip authentication for login page
$currentFile = basename($_SERVER['SCRIPT_NAME']);
if ($currentFile === 'login.php') {
    ob_end_flush(); // Clean buffer before exit
    exit;
}

if (!isset($_COOKIE['user_id'])) {
    header("Location: " . getDomainUrl() . "login.php");
    ob_end_clean(); // Discard any output
    exit;
}
ob_end_flush(); // Release buffer content
?>