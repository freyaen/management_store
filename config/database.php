<?php
$route = $_SERVER['HTTP_HOST'] ?? 'localhost';
if(str_contains($route, 'localhost')){
  $host = "mysql.railway.internal";
  $user = "root";
  $pass = "ECSUEfPJSTyGRjeIXyRQkcSligREPJmY";
  $db   = "railway";
} else {
  $host = "localhost";
  $user = "root";
  $pass = "";
  $db   = "management_store";
}


$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>
