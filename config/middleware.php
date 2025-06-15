<?php
if (!isset($_COOKIE['user_id'])) {
    // Determine protocol (http/https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    // Get server host
    $host = $_SERVER['HTTP_HOST'];
    
    // Construct absolute URL
    $redirect_url = "{$protocol}://{$host}/login.php";
    
    // Redirect and exit
    header("Location: $redirect_url");
    exit;
}