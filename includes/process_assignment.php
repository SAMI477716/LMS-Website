<?php
session_start();
include('db_connection.php');

if (isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $course_id = $_POST['course_id']; 
    $due_date = $_POST['due_date'];

    // We set status to 'pending' by default
    $stmt = $conn->prepare("INSERT INTO assignments (title, course_id, due_date, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("sis", $title, $course_id, $due_date);
    
    if($stmt->execute()) {
        // Redirect back to instructor dashboard with a success message
        header("Location: ../instructor/index.php?task_status=added");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>