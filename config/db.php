<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "sdo_systems_db";

if ($_SERVER['SERVER_NAME'] != "localhost") {
    $host = "sqlXXX.epizy.com";
    $username = "epiz_xxxxxx";
    $password = "yourpassword";
    $database = "epiz_xxxxxx_sdo";
}

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>