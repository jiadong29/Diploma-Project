<?php

$host = "localhost";
$user = "root";
$password = ""; // Database connection
$database = "Online_Bill_Payment_System";

$conn = mysqli_connect($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>