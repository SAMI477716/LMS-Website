<?php
session_start();
include('db_connection.php');

// 1. Security: Only let instructors post grades
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    die("Unauthorized access. You must be an instructor to perform this action.");
}

if (isset($_POST['submit_grade'])) {
    // 2. Capture data from the form
    $student_id = $_POST['student_id'];
    $course_name = $_POST['course_name'];
    $score = $_POST['score'];
    // Note: We'll store assessment_type in a variable, 
    // but ensure your grades table has a column for it if you want to save it!
    $assessment = $_POST['assessment_type'];

    // 3. Use a Prepared Statement for security
    $stmt = $conn->prepare("INSERT INTO grades (user_id, course_name, score) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $student_id, $course_name, $score);

    if ($stmt->execute()) {
        // 4. Redirect back to the dashboard with a success message
        header("Location: ../instructor/index.php?status=success");
        exit();
    } else {
        echo "Error saving grade: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>