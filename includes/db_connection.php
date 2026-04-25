<?php
$servername = "127.0.0.1:3307";
$username = "root";
$password = "";
$dbname = "lms_db";

// Create connection
$conn =new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    die("Error setting charset: " . $conn->error);
}
?>