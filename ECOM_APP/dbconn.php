<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbuser = "root";
$dbpass = "";   
$dbname = "ecom_app_db";

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if(!$conn){
    die("Problem in connection: " . mysqli_connect_error());
}

echo "Connection done successfully";
mysqli_close($conn);
?>