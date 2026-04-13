<?php
session_start();
include('db_connection.php');

// 1. Security: Only let instructors post grades
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    die("Unauthorized access. You must be an instructor to perform this action.");
}

if (isset($_POST['submit_grade'])) {
    // 2. Capture data from the form
    $student_id    = $_POST['student_id'];
    $course_name   = $_POST['course_name'];
    $raw_score     = $_POST['score'];
    $assessment    = $_POST['assessment_type'];
    $assignment_id = isset($_POST['assignment_id']) ? $_POST['assignment_id'] : null;

    // Optional: If you want to handle "Out of 20" vs "Out of 100"
    // We assume the input is already a percentage based on your modal, 
    // but you can add a 'max_score' input to the modal and calculate:
    // $score = ($raw_score / $max_score) * 100;
    $score = $raw_score; 

    // 3. AUTO-ENROLLMENT & PROGRESS LOGIC
    // Check if the student is already "enrolled" in this course
    $check_enroll = $conn->prepare("SELECT id FROM courses WHERE user_id = ? AND course_name = ?");
    $check_enroll->bind_param("is", $student_id, $course_name);
    $check_enroll->execute();
    $enroll_result = $check_enroll->get_result();

    if ($enroll_result->num_rows == 0) {
        // First time enrollment: initial progress based on 1st assignment
        // If there's only 1 assignment total, it will be 100%
        $enroll_stmt = $conn->prepare("INSERT INTO courses (user_id, course_name, progress, status, last_activity) VALUES (?, ?, 0, 'On Track', CURRENT_TIMESTAMP)");
        $enroll_stmt->bind_param("is", $student_id, $course_name);
        $enroll_stmt->execute();
        $enroll_stmt->close();
    }

    // 4. Insert the Grade
    $stmt = $conn->prepare("INSERT INTO grades (user_id, assignment_id, course_name, score) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $student_id, $assignment_id, $course_name, $score);

    if ($stmt->execute()) {
        
        // 5. DYNAMIC PROGRESS CALCULATION
        // Calculate: (Graded Assignments / Total Assignments for this course) * 100
        $calc_query = "SELECT 
            (SELECT COUNT(*) FROM assignments WHERE course_id = (SELECT id FROM courses WHERE course_name = ? LIMIT 1)) as total_tasks,
            (SELECT COUNT(*) FROM grades WHERE user_id = ? AND course_name = ?) as completed_tasks";
        
        $calc_stmt = $conn->prepare($calc_query);
        $calc_stmt->bind_param("sis", $course_name, $student_id, $course_name);
        $calc_stmt->execute();
        $res = $calc_stmt->get_result()->fetch_assoc();
        
        $total_tasks = $res['total_tasks'] > 0 ? $res['total_tasks'] : 1;
        $new_progress = round(($res['completed_tasks'] / $total_tasks) * 100);

        // 6. UPDATE COURSE STATUS & PROGRESS
        // We update the courses table so the dashboard bars move
        $update_course = $conn->prepare("UPDATE courses SET progress = ?, last_activity = CURRENT_TIMESTAMP WHERE user_id = ? AND course_name = ?");
        $update_course->bind_param("iis", $new_progress, $student_id, $course_name);
        $update_course->execute();

        // 7. Redirect back with success
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