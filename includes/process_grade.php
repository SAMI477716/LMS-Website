<?php
require_once __DIR__ . '/session.php';
enforce_session_timeout('../Login Page 2.0/index.html');
include('db_connection.php');

// Security: only instructors can submit grades
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    die("Unauthorized access. You must be an instructor to perform this action.");
}

if (isset($_POST['submit_grade'])) {
<<<<<<< HEAD
    // 2. Capture data from the form
    $student_id    = (int) ($_POST['student_id'] ?? 0);
    $course_name   = trim((string) ($_POST['course_name'] ?? ''));
    $raw_score     = (float) ($_POST['score'] ?? 0);
    $assessment    = trim((string) ($_POST['assessment_type'] ?? ''));
    $assignment_id = isset($_POST['assignment_id']) ? (int) $_POST['assignment_id'] : null;

    // Optional: If you want to handle "Out of 20" vs "Out of 100"
    // We assume the input is already a percentage based on your modal, 
    // but you can add a 'max_score' input to the modal and calculate:
    // $score = ($raw_score / $max_score) * 100;
    $score = $raw_score;
    if ($score > 100) {
        header("Location: ../instructor/index.php?grade_status=limit_exceeded");
        exit();
    }
    if ($score < 0) {
        $score = 0;
    }
=======
    $instructor_id = (int)($_SESSION['user_id'] ?? 0);

    $student_id    = (int)($_POST['student_id'] ?? 0);
    $assignment_id = (int)($_POST['assignment_id'] ?? 0);
    $course_name   = trim((string)($_POST['course_name'] ?? ''));
    $raw_score     = (float)($_POST['score'] ?? 0);
    $assessment    = trim((string)($_POST['assessment_type'] ?? ''));
>>>>>>> 1f8c8ca (Updated instructor Dashboard)

    if ($student_id < 1 || $assignment_id < 1 || $course_name === '' || $assessment === '') {
        header("Location: ../instructor/index.php?grade_status=invalid");
        exit();
    }

    if ($raw_score > 100) {
        header("Location: ../instructor/index.php?grade_status=limit_exceeded");
        exit();
    }

    $score = max(0, $raw_score);

    // Validate student exists and role=student
    $student_check = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'student' LIMIT 1");
    $student_check->bind_param("i", $student_id);
    $student_check->execute();
    $student_ok = $student_check->get_result()->fetch_assoc();
    $student_check->close();

    if (!$student_ok) {
        header("Location: ../instructor/index.php?grade_status=invalid_student");
        exit();
    }

    // Validate assignment belongs to instructor-owned course and fetch canonical course_name
    $assignment_check = $conn->prepare("
        SELECT a.id, c.course_name
        FROM assignments a
        INNER JOIN courses c ON c.id = a.course_id
        WHERE a.id = ? AND c.user_id = ?
        LIMIT 1
    ");
    $assignment_check->bind_param("ii", $assignment_id, $instructor_id);
    $assignment_check->execute();
    $assignment_row = $assignment_check->get_result()->fetch_assoc();
    $assignment_check->close();

    if (!$assignment_row) {
        header("Location: ../instructor/index.php?grade_status=unauthorized_assignment");
        exit();
    }

    $resolved_course_name = (string)$assignment_row['course_name'];

    // Insert grade with assignment_id (schema supports this)
    $stmt = $conn->prepare("
        INSERT INTO grades (user_id, assignment_id, course_name, score)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iisd", $student_id, $assignment_id, $resolved_course_name, $score);

    if ($stmt->execute()) {
        // Optional: keep course progress update for student-course rows only if they exist.
        // We do NOT create rows with invalid status like 'On Track'.
        $progress_stmt = $conn->prepare("
            UPDATE courses
            SET progress = LEAST(100, progress + 5), last_activity = CURRENT_TIMESTAMP
            WHERE user_id = ? AND course_name = ?
        ");
        $progress_stmt->bind_param("is", $student_id, $resolved_course_name);
        $progress_stmt->execute();
        $progress_stmt->close();

<<<<<<< HEAD
        // 6. UPDATE COURSE STATUS & PROGRESS
        // We update the courses table so the dashboard bars move
        $update_course = $conn->prepare("UPDATE courses SET progress = ?, last_activity = CURRENT_TIMESTAMP WHERE user_id = ? AND course_name = ?");
        $update_course->bind_param("iis", $new_progress, $student_id, $course_name);
        $update_course->execute();

        // 7. Redirect back with success
=======
>>>>>>> 1f8c8ca (Updated instructor Dashboard)
        header("Location: ../instructor/index.php?grade_status=updated");
        exit();
    } else {
        echo "Error saving grade: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>