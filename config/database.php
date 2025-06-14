<?php
$host = "mysql.railway.internal";
$user = "root";
$pass = "ECSUEfPJSTyGRjeIXyRQkcSligREPJmY";
$db   = "railway";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>
