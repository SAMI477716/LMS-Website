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
    $assessment = $_POST['assessment_type'];
    
    // Capture the assignment_id (Make sure your modal has <select name="assignment_id">)
    // If you don't have a dropdown for this yet, we can default it to NULL or a specific ID
    $assignment_id = isset($_POST['assignment_id']) ? $_POST['assignment_id'] : null;

    // 3. AUTO-ENROLLMENT LOGIC
    // Check if the student is already "enrolled" in this course
    $check_enroll = $conn->prepare("SELECT id FROM courses WHERE user_id = ? AND course_name = ?");
    $check_enroll->bind_param("is", $student_id, $course_name);
    $check_enroll->execute();
    $enroll_result = $check_enroll->get_result();

    if ($enroll_result->num_rows == 0) {
        // If not enrolled, create the course record so it shows up on their dashboard
        $enroll_stmt = $conn->prepare("INSERT INTO courses (user_id, course_name, progress, status) VALUES (?, ?, 100, 'completed')");
        $enroll_stmt->bind_param("is", $student_id, $course_name);
        $enroll_stmt->execute();
        $enroll_stmt->close();
    }

    // 4. Insert the Grade with the new assignment_id column
    $stmt = $conn->prepare("INSERT INTO grades (user_id, assignment_id, course_name, score) VALUES (?, ?, ?, ?)");
    
    // "iisd" = Integer (student), Integer (assignment), String (course), Double/Integer (score)
    $stmt->bind_param("iisd", $student_id, $assignment_id, $course_name, $score);

    if ($stmt->execute()) {
        // 5. Redirect back with success
        header("Location: ../instructor/index.php?status=success");
        exit();
    } else {
        echo "Error saving grade: " . $conn->error;
    }

    $stmt->close();
    $check_enroll->close();
    $conn->close();
}
?>