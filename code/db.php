<?php
$host = "localhost";
$user = "root";
$password = "root";
$dbname = "CityBridge2.1";
$port=8889;

$conn = new mysqli($host, $user, $password, $dbname,$port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>