<?php
date_default_timezone_set('Asia/Manila');

if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'sdo_systems_db';
} else {
    $host = 'sql100.infinityfree.com';
    $username = 'if0_41607869';
    $password = 'Ph4qbR9D4kiUT';
    $database = 'if0_41607869_sdo_systems_db';
}

$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

mysqli_query($conn, "SET time_zone = '+08:00'");
?>