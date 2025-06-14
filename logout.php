<?php
// Hapus semua cookie yang tersimpan di browser
if (!empty($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach ($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time() - 3600, '/');
        setcookie($name, '', time() - 3600, '/', $_SERVER['HTTP_HOST'], false, true); // httponly
    }
}

// Redirect ke halaman login
header("Location: login.php");
exit;
