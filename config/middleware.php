<?php
// Skip authentication for login page
$currentFile = basename($_SERVER['SCRIPT_NAME']);
if ($currentFile === 'login.php') {
    exit;
}

// Validate session existence
if (!isset($_COOKIE['user_id'])) {
    // Ensure no output before header
    if (headers_sent()) {
        die("Redirect failed - headers already sent");
    }
    
    // Use absolute URL for redirect
    $loginUrl = getDomainUrl() . "login.php";
    header("Location: $loginUrl");
    exit;
}

// Validate session integrity (add this)
$user_id = $_COOKIE['user_id'];
if (!is_numeric($user_id)) {
    setcookie('user_id', '', time() - 3600, '/');
    header("Location: " . getDomainUrl() . "login.php?error=invalid_session");
    exit;
}