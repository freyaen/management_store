<?php
// Skip authentication for login page
$currentFile = basename($_SERVER['SCRIPT_NAME']);
if ($currentFile === 'login.php') return;

// Existing authentication logic
if (!isset($_COOKIE['user_id'])) {
    header("Location: " . getDomainUrl() . "login.php");
    exit;
}
?>