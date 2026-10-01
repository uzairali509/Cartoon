<?php

$host = "localhost";
$username = "root";
$password = "uzairmaster509";
$database = "cartoon";

// Database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Optional: UTF-8 support
mysqli_set_charset($conn, "utf8mb4");

?>