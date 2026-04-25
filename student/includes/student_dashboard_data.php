<?php
declare(strict_types=1);

$user_id = (int) $_SESSION['user_id'];
$user_name = (string) ($_SESSION['username'] ?? '');
$database_extension_skeleton = get_database_extension_skeleton();

/**
 * @param array<int, mixed> $params
 * @return array<string, mixed>
 */
function prepared_fetch_one(mysqli $conn, string $sql, string $types = '', array $params = [], array $fallback = []): array
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
        return $fallback;
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log('Execute failed: ' . $stmt->error . ' | SQL: ' . $sql);
        $stmt->close();
        return $fallback;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return is_array($row) ? $row : $fallback;
}

/**
 * @param array<int, mixed> $params
 * @return array<int, array<string, mixed>>
 */
function prepared_fetch_all(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $rows = [];
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
        return $rows;
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log('Execute failed: ' . $stmt->error . ' | SQL: ' . $sql);
        $stmt->close();
        return $rows;
    }
    $result = $stmt->get_result();
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $stmt->close();

    return $rows;
}

function format_time_ago(string $date_time): string
{
    $timestamp = strtotime($date_time);
    if ($timestamp === false) {
        return 'Just now';
    }
    $diff = max(1, time() - $timestamp);
    if ($diff < 60) {
        return $diff . ' second' . ($diff !== 1 ? 's' : '') . ' ago';
    }
    if ($diff < 3600) {
        $mins = (int) floor($diff / 60);
        return $mins . ' minute' . ($mins !== 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hour' . ($hours !== 1 ? 's' : '') . ' ago';
    }
    $days = (int) floor($diff / 86400);
    return $days . ' day' . ($days !== 1 ? 's' : '') . ' ago';
}

$enrolled_row = prepared_fetch_one(
    $conn,
    'SELECT COUNT(*) AS total_enrolled FROM courses WHERE user_id = ?',
    'i',
    [$user_id],
    ['total_enrolled' => 0]
);
$total_courses = (int) ($enrolled_row['total_enrolled'] ?? 0);

$in_progress_row = prepared_fetch_one(
    $conn,
    "SELECT COUNT(*) AS in_progress
     FROM courses
     WHERE user_id = ?
       AND (status IS NULL OR status <> 'completed')",
    'i',
    [$user_id],
    ['in_progress' => 0]
);
$in_progress = (int) ($in_progress_row['in_progress'] ?? 0);

$graded_row = prepared_fetch_one(
    $conn,
    "SELECT COUNT(DISTINCT c.id) AS courses_with_grades
     FROM courses c
     INNER JOIN grades g ON g.user_id = c.user_id
        AND TRIM(g.course_name) = TRIM(c.course_name)
     WHERE c.user_id = ?",
    'i',
    [$user_id],
    ['courses_with_grades' => 0]
);
$courses_done = (int) ($graded_row['courses_with_grades'] ?? 0);
$enrollment_percent = ($total_courses > 0) ? (int) round(($courses_done / $total_courses) * 100) : 0;

$grade_row = prepared_fetch_one(
    $conn,
    'SELECT AVG(score) AS avg_score FROM grades WHERE user_id = ?',
    'i',
    [$user_id],
    ['avg_score' => 0]
);
$display_grade = !empty($grade_row['avg_score']) ? round((float) $grade_row['avg_score'], 1) : 0;
$grade_status = get_status_details((float) $display_grade);
if ($total_courses === 0) {
    $grade_label = 'Not Available';
    $grade_color = 'text-muted';
} else {
    $grade_label = $grade_status['text'];
    $grade_color = $grade_status['text_color'];
}

$student_profile = prepared_fetch_one(
    $conn,
    'SELECT id, username AS full_name, batch AS batch_number, email FROM users WHERE id = ? LIMIT 1',
    'i',
    [$user_id],
    ['id' => $user_id, 'full_name' => $user_name, 'batch_number' => '', 'email' => 'N/A']
);
$student_profile['full_name'] = trim((string) ($student_profile['full_name'] ?? $user_name));
$student_profile['batch_number'] = trim((string) ($student_profile['batch_number'] ?? ''));
$student_profile['batch_display'] = ($student_profile['batch_number'] !== '') ? $student_profile['batch_number'] : 'No Batch Assigned';
$student_profile['profile_initial'] = strtoupper(substr($student_profile['full_name'] !== '' ? $student_profile['full_name'] : $user_name, 0, 1));

// Assignments are stored with course_id pointing at a template/instructor course row.
$course_match = 'TRIM(c_enrolled.course_name) = TRIM(c_template.course_name)';

$pending_tasks_row = prepared_fetch_one(
    $conn,
    "SELECT COUNT(*) AS pending
     FROM assignments a
     INNER JOIN courses c_template ON c_template.id = a.course_id
     INNER JOIN courses c_enrolled ON c_enrolled.user_id = ? AND $course_match
     LEFT JOIN grades g ON a.id = g.assignment_id AND g.user_id = ?
     WHERE g.id IS NULL",
    'ii',
    [$user_id, $user_id],
    ['pending' => 0]
);
$pending_tasks = (int) ($pending_tasks_row['pending'] ?? 0);
$credits_earned = $courses_done * 3;

$course_progress_list = prepared_fetch_all(
    $conn,
    'SELECT course_name, progress, last_activity FROM courses WHERE user_id = ?',
    'i',
    [$user_id]
);

$upcoming_assignments = prepared_fetch_all(
    $conn,
    "SELECT a.title, a.due_date, c_enrolled.course_name
     FROM assignments a
     INNER JOIN courses c_template ON c_template.id = a.course_id
     INNER JOIN courses c_enrolled ON c_enrolled.user_id = ? AND $course_match
     LEFT JOIN grades g ON a.id = g.assignment_id AND g.user_id = ?
     WHERE g.id IS NULL
     ORDER BY a.due_date ASC",
    'ii',
    [$user_id, $user_id]
);

$upcoming_deadlines = prepared_fetch_all(
    $conn,
    "SELECT a.title, a.due_date, c_enrolled.course_name
     FROM assignments a
     INNER JOIN courses c_template ON c_template.id = a.course_id
     INNER JOIN courses c_enrolled ON c_enrolled.user_id = ? AND $course_match
     LEFT JOIN grades g ON a.id = g.assignment_id AND g.user_id = ?
     WHERE g.id IS NULL AND a.due_date >= CURDATE()
     ORDER BY a.due_date ASC
     LIMIT 5",
    'ii',
    [$user_id, $user_id]
);

$graded_assignments = prepared_fetch_all(
    $conn,
    "SELECT a.title, a.due_date, c_enrolled.course_name, g.score, g.date_posted
     FROM assignments a
     INNER JOIN courses c_template ON c_template.id = a.course_id
     INNER JOIN courses c_enrolled ON c_enrolled.user_id = ? AND $course_match
     INNER JOIN grades g ON a.id = g.assignment_id AND g.user_id = ?
     ORDER BY g.date_posted DESC",
    'ii',
    [$user_id, $user_id]
);

$pending_tasks = count($upcoming_assignments);

$course_grades = prepared_fetch_all(
    $conn,
    "SELECT c.course_name, c.progress, ROUND(AVG(g.score), 1) AS score
     FROM courses c
     LEFT JOIN grades g ON g.user_id = c.user_id
       AND TRIM(g.course_name) = TRIM(c.course_name)
     WHERE c.user_id = ?
     GROUP BY c.id, c.course_name, c.progress",
    'i',
    [$user_id]
);

$recent_activity = prepared_fetch_all(
    $conn,
    "SELECT activity_type, activity_title, activity_at
     FROM (
       SELECT 'Grade Posted' AS activity_type,
              CONCAT(TRIM(g.course_name), ': ', g.score, '%') AS activity_title,
              g.date_posted AS activity_at
       FROM grades g
       WHERE g.user_id = ?

       UNION ALL

       SELECT 'Assignment Pending' AS activity_type,
              CONCAT(a.title, ' (', TRIM(c_enrolled.course_name), ')') AS activity_title,
              CAST(a.due_date AS DATETIME) AS activity_at
       FROM assignments a
       INNER JOIN courses c_template ON c_template.id = a.course_id
       INNER JOIN courses c_enrolled ON c_enrolled.user_id = ? AND $course_match
       LEFT JOIN grades g ON a.id = g.assignment_id AND g.user_id = ?
       WHERE g.id IS NULL

       UNION ALL

       SELECT 'Course Activity' AS activity_type,
              CONCAT(TRIM(c.course_name), ' progress updated') AS activity_title,
              c.last_activity AS activity_at
       FROM courses c
       WHERE c.user_id = ? AND c.last_activity IS NOT NULL
     ) AS activity_feed
     ORDER BY activity_at DESC
     LIMIT 5",
    'iiii',
    [$user_id, $user_id, $user_id, $user_id]
);

foreach ($recent_activity as &$activity) {
    $activity['time_ago'] = format_time_ago((string) ($activity['activity_at'] ?? 'now'));
}
unset($activity);
