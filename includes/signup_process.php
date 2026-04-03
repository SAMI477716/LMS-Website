<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lms_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    // Securely hash the password
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if username or email already exists
    $check_sql = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check_sql->bind_param("ss", $user, $email);
    $check_sql->execute();
    $result = $check_sql->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Username or Email already exists!'); window.history.back();</script>";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $user, $email, $pass, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Registration Successful! Please Login.'); window.location.href='../Login Page 2.0/index.html';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>