<?php
require_once __DIR__ . '/../includes/session.php';
enforce_session_timeout('../Login Page 2.0/index.html');
include('../includes/db_connection.php');

// 1. Security Check: If not logged in OR not an instructor, redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../Login Page 2.0/index.html");
    exit();
}

// 2. Fetch Instructor Data
$user_id = (int) $_SESSION['user_id'];
$instructor_name = (string) $_SESSION['username'];

function fetch_all_rows(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $rows = [];
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return $rows;
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
    }

    $stmt->close();
    return $rows;
}

function fetch_one_row(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $rows = fetch_all_rows($conn, $sql, $types, $params);
    return $rows[0] ?? [];
}

$profile = fetch_one_row(
    $conn,
    "SELECT username, email FROM users WHERE id = ? AND role = 'instructor' LIMIT 1",
    'i',
    [$user_id]
);

$instructor_email = (string) ($profile['email'] ?? 'instructor@lms.com');

$students_row = fetch_one_row(
    $conn,
    "SELECT COUNT(*) AS total_students FROM users WHERE role = 'student'"
);
$total_students = (int) ($students_row['total_students'] ?? 0);

$summary = fetch_one_row(
    $conn,
    "SELECT
        COUNT(DISTINCT c.id) AS total_courses,
        SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END) AS active_courses
     FROM courses c
     WHERE c.user_id = ?",
    'i',
    [$user_id]
);

$total_courses = (int) ($summary['total_courses'] ?? 0);
$active_courses = (int) ($summary['active_courses'] ?? 0);

$pending_grades_row = fetch_one_row(
    $conn,
    "SELECT COUNT(*) AS pending_grades
     FROM assignments a
     INNER JOIN courses c ON c.id = a.course_id
     WHERE c.user_id = ? AND a.status <> 'graded'",
    'i',
    [$user_id]
);
$pending_grades = (int) ($pending_grades_row['pending_grades'] ?? 0);

$courses = fetch_all_rows(
    $conn,
    "SELECT id, course_name, progress, status
     FROM courses
     WHERE user_id = ?
     ORDER BY course_name ASC",
    'i',
    [$user_id]
);

$assignment_list = fetch_all_rows(
    $conn,
    "SELECT a.id, a.title, a.due_date, a.status, c.course_name
     FROM assignments a
     INNER JOIN courses c ON c.id = a.course_id
     WHERE c.user_id = ?
     ORDER BY a.due_date DESC",
    'i',
    [$user_id]
);

$all_students = fetch_all_rows(
    $conn,
    "SELECT id, username, batch FROM users WHERE role = 'student' ORDER BY batch ASC, username ASC"
);

$student_rows = fetch_all_rows(
    $conn,
    "SELECT u.id, u.username, u.batch,
            COALESCE(ROUND(AVG(g.score), 0), 0) AS avg_score,
            MAX(g.date_posted) AS last_activity
     FROM users u
     LEFT JOIN grades g ON g.user_id = u.id
     WHERE u.role = 'student'
     GROUP BY u.id, u.username, u.batch
     ORDER BY avg_score DESC, u.username ASC
     LIMIT 6"
);

$recent_activity = fetch_all_rows(
    $conn,
    "(SELECT
        'submission' AS activity_type,
        CONCAT(u.username, ' - ', a.title) AS activity_text,
        g.date_posted AS activity_at
      FROM grades g
      INNER JOIN users u ON u.id = g.user_id
      LEFT JOIN assignments a ON a.id = g.assignment_id
      ORDER BY g.date_posted DESC
      LIMIT 5)
     UNION ALL
     (SELECT
        'assignment' AS activity_type,
        CONCAT('Assignment created - ', a.title) AS activity_text,
        CAST(a.due_date AS DATETIME) AS activity_at
      FROM assignments a
      INNER JOIN courses c ON c.id = a.course_id
      WHERE c.user_id = ?
      ORDER BY a.due_date DESC
      LIMIT 5)
     ORDER BY activity_at DESC
     LIMIT 5",
    'i',
    [$user_id]
);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LMS Dashboard - Instructor View</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Bootstrap Icon -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    />
    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <!-- Main Container -->
    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
          <div class="sidebar-wrapper">
            <div class="text-center mb-4">
              <h1 class="text-white mt-2">LMS</h1>
            </div>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link active" href="#" data-page="dashboard">
                  <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="students">
                  <i class="bi bi-people me-2"></i>Students
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="courses">
                  <i class="bi bi-book me-2"></i>Courses
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="grades">
                  <i class="bi bi-trophy me-2"></i>Grades
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="reports">
                  <i class="bi bi-graph-up me-2"></i>Reports
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="settings">
                  <i class="bi bi-gear me-2"></i>Settings
                </a>
              </li>
            </ul>

            <!-- Instructor Profile Section (Bottom of Sidebar) -->
            <div class="instructor-profile-sidebar">
              <div class="d-flex align-items-center">
                <img
                  src="https://via.placeholder.com/50"
                  alt="Profile"
                  class="profile-thumbnail rounded-circle me-2"
                />
                <div class="profile-info">
                  <h6 class="text-white mb-0"><?php echo htmlspecialchars($instructor_name); ?></h6>
                  <small class="text-secondary"><?php echo htmlspecialchars($instructor_email); ?></small>
                  <p class="text-secondary small mb-0 mt-1">
                    <i class="bi bi-chat-quote"></i> Passionate educator
                  </p>
                </div>
              </div>
            </div>
          </div>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
          <!-- Dynamic Content Pages -->
          <div id="dashboard-page" class="page active">
            <!-- Dashboard Header -->
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Instructor Dashboard</h1>
              <div class="calendar-box">
                <input type="date" id="calendar" />
              </div>
              <div class="btn-toolbar mb-2 mb-md-0 gap-2">

              <?php if (isset($_GET['task_status']) && $_GET['task_status'] == 'added'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> The new assignment has been published to students.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
    <button type="button" class="btn btn-dark mb-4" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
    <i class="bi bi-plus-circle me-2"></i> Create New Assignment
    </button>
    
         
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary ms-2"
                  data-bs-toggle="modal"
                  data-bs-target="#gradeModal"
                >
                  <i class="bi bi-plus-circle me-1"></i>Add Manual Grade
                </button>
              </div>
            </div>

            <!-- Welcome Banner -->
            <div class="alert alert-primary welcome-banner mb-4">
              <h4 class="alert-heading">Welcome back, <?php echo htmlspecialchars($instructor_name); ?>!</h4>
              <p class="mb-0">
                Here's what's happening with your students today.
              </p>
            </div>

            <div class="modal fade" id="addAssignmentModal" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">New Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <form action="../includes/process_assignment.php" method="POST">
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Assignment Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. PHP CRUD Project" required>
                      </div>

          <div class="mb-3">
            <label class="form-label">Course</label>
            <select class="form-select" name="course_id" required>
              <option value="">Select Course</option>
              <?php
              // Fetch courses from your database
              $course_query = "SELECT id, course_name FROM courses GROUP BY course_name";
              $course_result = $conn->query($course_query);
              while($row = $course_result->fetch_assoc()) {
                  echo "<option value='{$row['id']}'>{$row['course_name']}</option>";
              }
              ?>
            </select>
          </div>

                      <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" name="add_task" class="btn btn-primary">Save Assignment</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="card-subtitle text-muted">Total Students</h6>
                        <h2 class="card-title mb-0"><?php echo $total_students; ?></h2>
                      </div>
                      <div class="stat-icon bg-primary">
                        <i class="bi bi-people-fill"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="card-subtitle text-muted">My Courses</h6>
                        <h2 class="card-title mb-0"><?php echo $total_courses; ?> courses</h2>
                        <small class="text-success">Instructor-owned</small>
                      </div>
                      <div class="stat-icon bg-success">
                        <i class="bi bi-person-workspace"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="card-subtitle text-muted">Published Courses</h6>
                        <h2 class="card-title mb-0"><?php echo $active_courses; ?> active</h2>
                        <small class="text-warning">Live in dashboard</small>
                      </div>
                      <div class="stat-icon bg-warning">
                        <i class="bi bi-person-workspace"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="card-subtitle text-muted">Active Courses</h6>
                        <h2 class="card-title mb-0"><?php echo $active_courses; ?></h2>
                        <small class="text-danger"><?php echo $pending_grades; ?> Pending Grades</small>
                      </div>
                      <div class="stat-icon bg-danger">
                        <i class="bi bi-book-fill"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Course Progress Section -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Course Completion Progress</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                          <?php $progress = max(0, min(100, (int) ($course['progress'] ?? 0))); ?>
                          <div class="col-md-6">
                            <div class="course-item mb-3">
                              <h6><?php echo htmlspecialchars((string) $course['course_name']); ?></h6>
                              <div class="row">
                                <div class="col-12">
                                  <small>Status: <?php echo htmlspecialchars((string) ($course['status'] ?? 'active')); ?></small>
                                  <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%">
                                      <?php echo $progress; ?>%
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="col-12">
                          <div class="alert alert-light border mb-0">No courses found for this instructor yet.</div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card shadow-sm mt-4">
              <div class="card-header bg-white">
                <h5 class="mb-0">Existing Assignments</h5>
              </div>
              <div class="card-body">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Title</th>
                      <th>Course</th>
                      <th>Due Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($assignment_list as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['title']); ?></td>
                      <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                      <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                      <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Student Progress Overview and Recent Activity -->
            <div class="row">
              <div class="col-md-7">
                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Student Progress Overview</h5>
                  </div>
                  <div class="card-body">
                    <div class="student-progress-list">
                      <?php if (!empty($student_rows)): ?>
                        <?php foreach ($student_rows as $student): ?>
                          <?php
                            $avg = (int) ($student['avg_score'] ?? 0);
                            $badge = $avg >= 85 ? 'bg-success' : ($avg >= 70 ? 'bg-warning' : 'bg-danger');
                            $lastSeen = !empty($student['last_activity']) ? date('M d, Y', strtotime((string) $student['last_activity'])) : 'No activity';
                            $batch = !empty($student['batch']) ? (string) $student['batch'] : 'No Batch';
                          ?>
                          <div
                            class="student-item mb-3 p-2 border-bottom"
                            onclick="viewStudentDetails('ST<?php echo (int) $student['id']; ?>')"
                          >
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                <strong><?php echo htmlspecialchars((string) $student['username']); ?></strong>
                                <div class="text-muted small"><?php echo htmlspecialchars($batch); ?></div>
                              </div>
                              <div class="text-end">
                                <span class="badge <?php echo $badge; ?>"><?php echo $avg; ?>%</span>
                                <small class="text-muted d-block"><?php echo htmlspecialchars($lastSeen); ?></small>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="student-item p-2">
                          <div class="text-muted small">No student data available.</div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-5">
                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                  </div>
                  <div class="card-body">
                    <div class="activity-list">
                      <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $index => $activity): ?>
                          <?php
                            $isSubmission = ($activity['activity_type'] ?? '') === 'submission';
                            $iconClass = $isSubmission ? 'bi-file-text' : 'bi-folder';
                            $bgClass = $isSubmission ? 'bg-primary' : 'bg-warning';
                            $title = $isSubmission ? 'New submission' : 'Assignment update';
                            $activityAt = !empty($activity['activity_at']) ? date('M d, Y', strtotime((string) $activity['activity_at'])) : 'Just now';
                            $borderClass = $index < (count($recent_activity) - 1) ? 'mb-3 pb-2 border-bottom' : '';
                          ?>
                          <div class="activity-item <?php echo $borderClass; ?>">
                            <div class="d-flex align-items-center">
                              <div class="activity-icon <?php echo $bgClass; ?> me-3">
                                <i class="bi <?php echo $iconClass; ?> text-white"></i>
                              </div>
                              <div>
                                <strong><?php echo $title; ?></strong>
                                <div class="text-muted small">
                                  <?php echo htmlspecialchars((string) ($activity['activity_text'] ?? 'No recent activity details')); ?>
                                </div>
                                <small class="text-secondary"><?php echo htmlspecialchars($activityAt); ?></small>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="activity-item">
                          <div class="d-flex align-items-center">
                            <div class="activity-icon bg-secondary me-3">
                              <i class="bi bi-info-circle text-white"></i>
                            </div>
                            <div>
                              <strong>No recent activity</strong>
                              <div class="text-muted small">New grade posts and assignments will appear here.</div>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Student Details Page (initially hidden) -->
          <div id="student-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Student Details</h1>
              <button
                class="btn btn-outline-secondary"
                onclick="showPage('dashboard')"
              >
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
              </button>
            </div>

            <!-- Student Details Content -->
            <div id="student-details-container">
              <!-- Will be populated by JavaScript -->
            </div>
            <!-- Google Forms Assessments Section - FOR THIS STUDENT -->
            <div class="row mt-4">
              <div class="col-12">
                <div class="card">
                  <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                      <i class="bi bi-google me-2"></i>Google Forms Assessments
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showAddGoogleFormForStudent()">
                      <i class="bi bi-plus-circle me-1"></i>Assign Form
                    </button>
                  </div>
                  <div class="card-body">
                    <div id="student-google-forms-list" class="row">
                      <!-- Student-specific Google Forms will be loaded here -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Students Dashboard Page -->
          <div id="students-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Students Dashboard</h1>
              <div class="btn-toolbar mb-2 mb-md-0">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-primary"
                  onclick="exportStudentList()"
                >
                  <i class="bi bi-download me-1"></i>Export List
                </button>
              </div>
            </div>

            <!-- Students by Batch -->
            <div id="students-by-batch" class="row">
              <!-- Will be populated by JavaScript -->
            </div>
          </div>

          <!-- Courses Dashboard Page -->
          <div id="courses-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Courses Dashboard</h1>
              <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                onclick="showAddCourseModal()"
              >
                <i class="bi bi-plus-circle me-1"></i>Add Course
              </button>
            </div>

            <!-- Courses with Enrolled Students -->
            <div id="courses-with-students" class="row">
              <!-- Will be populated by JavaScript -->
            </div>
          </div>

          <!-- Grades Dashboard Page -->
          <div id="grades-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Grades Dashboard</h1>
              <div class="btn-group">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary"
                  onclick="filterGradesByCourse('all')"
                >
                  All Courses
                </button>
                <?php foreach ($courses as $course): ?>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary"
                  onclick="filterGradesByCourse('<?php echo strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $course['course_name'])); ?>')"
                >
                  <?php echo htmlspecialchars((string) $course['course_name']); ?>
                </button>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Rankings Table -->
            <div class="card">
              <div class="card-header bg-white">
                <h5 class="mb-0">Student Performance Rankings</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table
                    class="table table-hover rankings-table"
                    id="grades-ranking-table"
                  >
                    <thead>
                      <tr>
                        <th>Rank</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Grade</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody id="grades-table-body">
                      <!-- Will be populated by JavaScript -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Reports Dashboard Page -->
          <div id="reports-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Reports & Analytics</h1>
              <div class="btn-toolbar gap-2">
                <button type="button" class="btn btn-sm btn-success" onclick="exportToCSV()">
                  <i class="bi bi-file-spreadsheet me-1"></i>Export CSV
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="exportToPDF()">
                  <i class="bi bi-file-pdf me-1"></i>Export PDF
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="printReport()">
                  <i class="bi bi-printer me-1"></i>Print
                </button>
              </div>
            </div>

            <!-- Report Filters -->
            <div class="card mb-4">
              <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Report Filters</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-select" id="reportType" onchange="updateReportPreview()">
                      <option value="students">Student Report</option>
                      <option value="grades">Grade Report</option>
                      <option value="courses">Course Report</option>
                      <option value="performance">Performance Summary</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label class="form-label">Batch</label>
                    <select class="form-select" id="reportBatch" onchange="updateReportPreview()">
                      <option value="all">All Batches</option>
                      <option value="Batch 1">Batch 1</option>
                      <option value="Batch 2">Batch 2</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label class="form-label">Date Range</label>
                    <select class="form-select" id="reportDateRange" onchange="updateReportPreview()">
                      <option value="all">All Time</option>
                      <option value="week">Last 7 Days</option>
                      <option value="month">Last 30 Days</option>
                      <option value="semester">This Semester</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" id="reportSort" onchange="updateReportPreview()">
                      <option value="name">Name (A-Z)</option>
                      <option value="grade">Grade (High to Low)</option>
                      <option value="date">Date (Newest)</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Course Filter</label>
                    <select class="form-select" id="reportCourse" onchange="updateReportPreview()">
                      <option value="all">All Courses</option>
                      <?php foreach ($courses as $course): ?>
                      <option value="<?php echo htmlspecialchars((string) $course['course_name']); ?>"><?php echo htmlspecialchars((string) $course['course_name']); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Grade Range (Min)</label>
                    <input type="range" class="form-range" id="gradeMin" min="0" max="100" value="0" oninput="updateGradeMinValue(this.value)">
                    <span id="gradeMinValue" class="small">0%</span>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Grade Range (Max)</label>
                    <input type="range" class="form-range" id="gradeMax" min="0" max="100" value="100" oninput="updateGradeMaxValue(this.value)">
                    <span id="gradeMaxValue" class="small">100%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Report Preview -->
            <div class="card">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Report Preview</h5>
                <div class="text-muted small" id="reportStats"></div>
              </div>
              <div class="card-body">
                <div id="reportPreviewContainer" style="max-height: 500px; overflow-y: auto;">
                  <!-- Report content will be dynamically loaded here -->
                </div>
              </div>
            </div>
          </div>

          <!-- Settings Dashboard Page -->
          <div id="settings-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Settings</h1>
            </div>

            <!-- Settings Tabs -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link active"
                  id="profile-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#profile"
                  type="button"
                  role="tab"
                >
                  Profile
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  id="password-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#password"
                  type="button"
                  role="tab"
                >
                  Password
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  id="courses-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#courses-settings"
                  type="button"
                  role="tab"
                >
                  Courses
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  id="notifications-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#notifications"
                  type="button"
                  role="tab"
                >
                  Notifications
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  id="layout-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#layout"
                  type="button"
                  role="tab"
                >
                  Layout
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link"
                  id="appearance-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#appearance"
                  type="button"
                  role="tab"
                >
                  Appearance
                </button>
              </li>
            </ul>

            <!-- Settings Tab Content -->
            <div class="tab-content" id="settingsTabContent">
              <!-- Profile Settings -->
              <div
                class="tab-pane fade show active"
                id="profile"
                role="tabpanel"
              >
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Profile Settings</h5>
                    <form id="profileSettingsForm">
                      <div class="row">
                        <div class="col-md-3 text-center mb-4">
                          <div class="profile-picture-upload">
                            <img
                              src="https://via.placeholder.com/150"
                              alt="Profile"
                              class="rounded-circle mb-3"
                              id="profilePreview"
                              style="
                                width: 150px;
                                height: 150px;
                                object-fit: cover;
                              "
                            />
                            <div>
                              <label
                                for="profilePicture"
                                class="btn btn-outline-primary btn-sm"
                              >
                                <i class="bi bi-camera me-1"></i>Upload Photo
                              </label>
                              <input
                                type="file"
                                class="d-none"
                                id="profilePicture"
                                accept="image/*"
                                onchange="previewProfilePicture(this)"
                              />
                            </div>
                          </div>
                        </div>
                        <div class="col-md-9">
                          <div class="mb-3">
                            <label for="profileName" class="form-label"
                              >Full Name</label
                            >
                            <input
                              type="text"
                              class="form-control"
                              id="profileName"
                              value="<?php echo htmlspecialchars($instructor_name); ?>"
                            />
                          </div>
                          <div class="mb-3">
                            <label for="profileEmail" class="form-label"
                              >Email Address</label
                            >
                            <input
                              type="email"
                              class="form-control"
                              id="profileEmail"
                              value="<?php echo htmlspecialchars($instructor_email); ?>"
                            />
                          </div>
                          <div class="mb-3">
                            <label for="profileBio" class="form-label"
                              >Short Bio</label
                            >
                            <textarea
                              class="form-control"
                              id="profileBio"
                              rows="3"
                            >Passionate educator with 5+ years of experience in web development and design.</textarea>
                          </div>
                          <button
                            type="button"
                            class="btn btn-primary"
                            onclick="saveProfileSettings()"
                          >
                            Save Changes
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Password Settings -->
              <div class="tab-pane fade" id="password" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Change Password</h5>
                    <form id="passwordSettingsForm">
                      <div class="mb-3">
                        <label for="currentPassword" class="form-label"
                          >Current Password</label
                        >
                        <input
                          type="password"
                          class="form-control"
                          id="currentPassword"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="newPassword" class="form-label"
                          >New Password</label
                        >
                        <input
                          type="password"
                          class="form-control"
                          id="newPassword"
                          required
                        />
                        <div class="password-strength mt-2">
                          <div class="progress" style="height: 5px">
                            <div
                              class="progress-bar"
                              id="passwordStrength"
                              style="width: 0%"
                            ></div>
                          </div>
                          <small class="text-muted" id="passwordStrengthText"
                            >Enter a password</small
                          >
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="confirmPassword" class="form-label"
                          >Confirm New Password</label
                        >
                        <input
                          type="password"
                          class="form-control"
                          id="confirmPassword"
                          required
                        />
                        <div
                          class="invalid-feedback"
                          id="passwordMatchFeedback"
                        >
                          Passwords do not match
                        </div>
                      </div>
                      <button
                        type="button"
                        class="btn btn-primary"
                        onclick="changePassword()"
                      >
                        Update Password
                      </button>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Course Settings -->
              <div class="tab-pane fade" id="courses-settings" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Course Settings</h5>

                    <!-- Course Visibility -->
                    <div class="mb-4">
                      <h6>Default Course Visibility</h6>
                      <div class="form-check">
                        <input
                          class="form-check-input"
                          type="radio"
                          name="courseVisibility"
                          id="visibilityPublic"
                          checked
                        />
                        <label class="form-check-label" for="visibilityPublic">
                          <i class="bi bi-globe me-1"></i>Public - Anyone can
                          view
                        </label>
                      </div>
                      <div class="form-check">
                        <input
                          class="form-check-input"
                          type="radio"
                          name="courseVisibility"
                          id="visibilityPrivate"
                        />
                        <label class="form-check-label" for="visibilityPrivate">
                          <i class="bi bi-lock me-1"></i>Private - Only enrolled
                          students
                        </label>
                      </div>
                    </div>

                    <!-- Enrollment Options -->
                    <div class="mb-4">
                      <h6>Default Enrollment Options</h6>
                      <div class="form-check">
                        <input
                          class="form-check-input"
                          type="radio"
                          name="enrollmentOption"
                          id="enrollmentOpen"
                          checked
                        />
                        <label class="form-check-label" for="enrollmentOpen">
                          <i class="bi bi-door-open me-1"></i>Open Enrollment -
                          Anyone can join
                        </label>
                      </div>
                      <div class="form-check">
                        <input
                          class="form-check-input"
                          type="radio"
                          name="enrollmentOption"
                          id="enrollmentInvite"
                        />
                        <label class="form-check-label" for="enrollmentInvite">
                          <i class="bi bi-envelope me-1"></i>Invite Only -
                          Requires approval
                        </label>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="saveCourseSettings()"
                    >
                      Save Settings
                    </button>
                  </div>
                </div>
              </div>

              <!-- Notification Settings -->
              <div class="tab-pane fade" id="notifications" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Notification Preferences</h5>

                    <div class="mb-4">
                      <div class="form-check form-switch mb-3">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="emailSubmissions"
                          checked
                        />
                        <label class="form-check-label" for="emailSubmissions">
                          <strong
                            >Email notifications for new submissions</strong
                          >
                          <p class="text-muted small mb-0">
                            Get notified when students submit assignments
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch mb-3">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="emailMessages"
                          checked
                        />
                        <label class="form-check-label" for="emailMessages">
                          <strong>Email notifications for new messages</strong>
                          <p class="text-muted small mb-0">
                            Get notified when you receive new messages
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch mb-3">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="emailGrades"
                        />
                        <label class="form-check-label" for="emailGrades">
                          <strong>Grade updates</strong>
                          <p class="text-muted small mb-0">
                            Get notified when grades are posted or updated
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="emailAnnouncements"
                          checked
                        />
                        <label
                          class="form-check-label"
                          for="emailAnnouncements"
                        >
                          <strong>Course announcements</strong>
                          <p class="text-muted small mb-0">
                            Receive important course announcements
                          </p>
                        </label>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="saveNotificationSettings()"
                    >
                      Save Preferences
                    </button>
                  </div>
                </div>
              </div>

              <!-- Layout Settings -->
              <div class="tab-pane fade" id="layout" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Account</h5>

                    <!-- Active Sessions -->
                    <div class="mb-4">
                      <h6>Active Sessions</h6>
                      <div class="list-group">
                        <div
                          class="list-group-item d-flex justify-content-between align-items-center"
                        >
                          <div>
                            <i class="bi bi-laptop me-2"></i>
                            <strong>Current Session</strong>
                            <br />
                            <small class="text-muted"
                              >Chrome on Windows • 192.168.1.100</small
                            >
                          </div>
                          <span class="badge bg-success">Active Now</span>
                        </div>
                        <div
                          class="list-group-item d-flex justify-content-between align-items-center"
                        >
                          <div>
                            <i class="bi bi-phone me-2"></i>
                            <strong>Mobile Device</strong>
                            <br />
                            <small class="text-muted"
                              >Safari on iOS • 192.168.1.101</small
                            >
                          </div>
                          <button class="btn btn-sm btn-outline-danger">
                            Sign Out
                          </button>
                        </div>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="saveLayoutSettings()"
                    >
                      Save Layout
                    </button>
                  </div>
                </div>
              </div>

              <!-- Appearance Settings -->
              <div class="tab-pane fade" id="appearance" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Appearance</h5>

                    <!-- Theme Options -->
                    <div class="mb-4">
                      <h6>Theme</h6>
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <div
                            class="theme-option card text-center p-3 selected"
                            onclick="selectTheme('light')"
                          >
                            <i
                              class="bi bi-brightness-high-fill fs-1 mb-2 text-warning"
                            ></i>
                            <h6>Light Mode</h6>
                            <small class="text-muted"
                              >Default light theme</small
                            >
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div
                            class="theme-option card text-center p-3"
                            onclick="selectTheme('dark')"
                          >
                            <i
                              class="bi bi-moon-stars-fill fs-1 mb-2 text-primary"
                            ></i>
                            <h6>Dark Mode</h6>
                            <small class="text-muted"
                              >Easy on the eyes at night</small
                            >
                          </div>
                        </div>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="saveAppearanceSettings()"
                    >
                      Apply Theme
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
    
<div class="modal fade" id="gradeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Manual Grade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="../includes/process_grade.php" method="POST">
        <div class="modal-body">
          
          <div class="mb-3">
            <label class="form-label">Student (Name & Batch)</label>
            <select class="form-select" name="student_id" required>
              <option value="">Select student</option>
              <?php
              $student_query = "SELECT id, username, batch FROM users WHERE role = 'student' ORDER BY batch ASC, username ASC";
              $student_result = $conn->query($student_query);
              while($student = $student_result->fetch_assoc()) {
                  $batchInfo = !empty($student['batch']) ? " (" . $student['batch'] . ")" : " (No Batch)";
                  echo "<option value='{$student['id']}'>" . htmlspecialchars($student['username']) . $batchInfo . "</option>";
              }
              ?>
            </select>
          </div>

              <div class="mb-3">
                <label class="form-label">Specific Assignment</label>
                <select class="form-select" name="assignment_id" required>
                  <option value="">Select the task being graded</option>
                  <?php foreach ($assignment_list as $assign): ?>
                    <option value="<?php echo (int) $assign['id']; ?>"><?php echo htmlspecialchars((string) $assign['title']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Course Name</label>
                <select class="form-select" name="course_name" required>
                  <option value="">Select course</option>
                  <?php foreach ($courses as $course): ?>
                    <option value="<?php echo htmlspecialchars((string) $course['course_name']); ?>"><?php echo htmlspecialchars((string) $course['course_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

          <div class="mb-3">
            <label class="form-label">Grade (%)</label>
            <input type="number" name="score" class="form-control" min="0" max="100" required>
          </div>

              <div class="mb-3">
                <label class="form-label">Assessment Type</label>
                <input type="text" name="assessment_type" class="form-control" placeholder="e.g. Quiz, Final Project" required>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="submit_grade" class="btn btn-primary">Submit Grade</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add Course Modal (New) -->
    <div class="modal fade" id="addCourseModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New Course</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="addCourseForm">
              <div class="mb-3">
                <label for="courseName" class="form-label">Course Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="courseName"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="courseCode" class="form-label">Course Code</label>
                <input
                  type="text"
                  class="form-control"
                  id="courseCode"
                  placeholder="e.g., CS101"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="courseDescription" class="form-label"
                  >Description</label
                >
                <textarea
                  class="form-control"
                  id="courseDescription"
                  rows="3"
                ></textarea>
              </div>
              <div class="mb-3">
                <label for="courseVisibility" class="form-label"
                  >Visibility</label
                >
                <select class="form-select" id="courseVisibility">
                  <option value="public">Public</option>
                  <option value="private">Private</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="btn btn-primary"
              onclick="addNewCourse()"
            >
              Add Course
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      (function () {
        const form = document.getElementById('manualGradeForm');
        const scoreInput = document.getElementById('manualGradeScore');
        if (!form || !scoreInput) return;

        form.addEventListener('submit', function (event) {
          const score = Number(scoreInput.value);
          if (Number.isNaN(score) || score > 100) {
            event.preventDefault();
            alert('Grade cannot exceed the 100% limit.');
            scoreInput.focus();
          }
        });
      })();
    </script>
    <!-- Custom JS -->
    <script src="script.js"></script>
  </body>
</html>