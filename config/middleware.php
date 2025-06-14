<?php
// Cek apakah user sudah login menggunakan cookie
if (!isset($_COOKIE['user_id'])) {
    // Jika tidak, redirect ke login
    $route =  "Location:" . getDomainUrl() . "login.php";
    header($route);
    exit;
}
