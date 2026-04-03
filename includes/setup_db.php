<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$servername = "localhost";
$username = "root";
$password = "";

// 1. Create connection to MySQL Server
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS lms_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// 3. Select the database to create tables
$conn->select_db("lms_db");

// 4. Create Users Table
$table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'instructor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($table_sql) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 5. Optional: Insert a test student and instructor (Password: password123)
// We use password_hash for security as per modern standards
$pass = password_hash("password123", PASSWORD_DEFAULT);

$check_empty = $conn->query("SELECT id FROM users LIMIT 1");
if ($check_empty->num_rows == 0) {
    $insert_sql = "INSERT INTO users (username, email, password, role) VALUES 
    ('tsi', 'tsi@lms.com', '$pass', 'student'),
    ('tebarek', 'tebarek@lms.com', '$pass', 'instructor')";
    
    if ($conn->query($insert_sql) === TRUE) {
        echo "Sample users added successfully!";
    }
}

$conn->close();
?>