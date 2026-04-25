<?php
require_once __DIR__ . '/session.php';
enforce_session_timeout('../Login Page 2.0/index.html');
include('db_connection.php');

if (isset($_POST['add_task'])) {
    $title = trim((string) ($_POST['title'] ?? ''));
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $due_date = trim((string) ($_POST['due_date'] ?? ''));

    if ($title === '' || $course_id < 1 || $due_date === '') {
        header('Location: ../instructor/index.php?task_status=invalid');
        exit();
    }

    $check = $conn->prepare('SELECT id FROM courses WHERE id = ? LIMIT 1');
    $check->bind_param('i', $course_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();
    if (!$exists) {
        header('Location: ../instructor/index.php?task_status=invalid');
        exit();
    }

    // We set status to 'pending' by default; user_id left NULL (class-wide task).
    $stmt = $conn->prepare("INSERT INTO assignments (title, course_id, due_date, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param('sis', $title, $course_id, $due_date);
    
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