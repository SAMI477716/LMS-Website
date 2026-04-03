<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lms_db";

// Create OO connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from the form names
    $user_input = $_POST['username'];
    $pass_input = $_POST['password'];

    // Prepared statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $user_input, $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Verify the hashed password from your setup_db.php
        if (password_verify($pass_input, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect out of 'includes' into the dashboard folders
            if ($user['role'] === 'student') {
                header("Location: ../student/index.php");
            } else {
                header("Location: ../instructor/index.html");
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='../Login Page 2.0/index.html';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='../Login Page 2.0/index.html';</script>";
    }
    $stmt->close();
}
$conn->close();
?>