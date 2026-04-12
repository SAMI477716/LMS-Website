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
    echo "Database 'lms_db' ready.<br>";
} else {
    die("Error creating database: " . $conn->error . "<br>");
}

// 3. Select the database
$conn->select_db("lms_db");

// 4. Create Tables
$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'instructor') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "courses" => "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        progress INT DEFAULT 0,
        status ENUM('active', 'archived') DEFAULT 'active',
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",

    "assignments" => "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        due_date DATE,
        status ENUM('pending', 'submitted', 'graded') DEFAULT 'pending',
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )",

    "grades" => "CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_name VARCHAR(255),
        score INT NOT NULL,
        date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $name => $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table '$name' is ready.<br>";
    } else {
        echo "Error creating table '$name': " . $conn->error . "<br>";
    }
}

// 5. Insert Sample Data
$pass = password_hash("password123", PASSWORD_DEFAULT);

// Check if users exist, if not, add them
$check_users = $conn->query("SELECT id FROM users LIMIT 1");
if ($check_users->num_rows == 0) {
    $insert_users = "INSERT INTO users (username, email, password, role) VALUES 
    ('tsi', 'tsi@lms.com', '$pass', 'student'),
    ('tebarek', 'tebarek@lms.com', '$pass', 'instructor')";
    $conn->query($insert_users);
    echo "Sample users created.<br>";
}

// Check if courses exist, if not, add samples for 'tsi' (student)
$check_courses = $conn->query("SELECT id FROM courses LIMIT 1");
if ($check_courses->num_rows == 0) {
    // Get the ID of the student 'tsi'
    $res = $conn->query("SELECT id FROM users WHERE username='tsi' LIMIT 1");
    $user = $res->fetch_assoc();
    $sid = $user['id'];

    // Add sample courses
    $insert_courses = "INSERT INTO courses (user_id, course_name, progress) VALUES 
    ($sid, 'PHP Backend Development', 75),
    ($sid, 'Database Management Systems', 45),
    ($sid, 'UI/UX Design Principles', 90)";
    $conn->query($insert_courses);

    // Get the ID of the first course to add an assignment
    $cid = $conn->insert_id;
    $insert_assign = "INSERT INTO assignments (course_id, title, due_date, status) VALUES 
    ($cid, 'Final PHP Project', '2025-12-01', 'pending')";
    $conn->query($insert_assign);

    echo "Sample course data linked to student 'tsi'.<br>";
}

echo "<strong>Database setup complete!</strong>";
$conn->close();
?>