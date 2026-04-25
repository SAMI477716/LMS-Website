<?php
require_once __DIR__ . '/session.php';
enforce_session_timeout('../Login Page 2.0/index.html');
include('db_connection.php');

<<<<<<< HEAD
if (isset($_POST['add_task'])) {
    $title = trim((string) ($_POST['title'] ?? ''));
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $due_date = trim((string) ($_POST['due_date'] ?? ''));

=======
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    die("Unauthorized access.");
}

if (isset($_POST['add_task'])) {
    $title = trim((string)($_POST['title'] ?? ''));
    $course_id = (int)($_POST['course_id'] ?? 0);
    $due_date = trim((string)($_POST['due_date'] ?? ''));
    $instructor_id = (int)($_SESSION['user_id'] ?? 0);

>>>>>>> 1f8c8ca (Updated instructor Dashboard)
    if ($title === '' || $course_id < 1 || $due_date === '') {
        header('Location: ../instructor/index.php?task_status=invalid');
        exit();
    }

<<<<<<< HEAD
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
=======
    // Security check: selected course must belong to logged-in instructor
    $check = $conn->prepare('SELECT id FROM courses WHERE id = ? AND user_id = ? LIMIT 1');
    $check->bind_param('ii', $course_id, $instructor_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$exists) {
        header('Location: ../instructor/index.php?task_status=unauthorized_course');
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO assignments (title, course_id, due_date, status)
        VALUES (?, ?, ?, 'pending')
    ");
    $stmt->bind_param('sis', $title, $course_id, $due_date);

    if ($stmt->execute()) {
>>>>>>> 1f8c8ca (Updated instructor Dashboard)
        header("Location: ../instructor/index.php?task_status=added");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>