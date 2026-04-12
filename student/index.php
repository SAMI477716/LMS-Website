<?php
session_start();
include('../includes/db_connection.php'); 

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../Login Page 2.0/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];

// Calculate Average Grade for the banner
$grade_q = "SELECT AVG(score) as avg_score FROM grades WHERE user_id = $user_id";
$grade_r = $conn->query($grade_q);
$grade_row = $grade_r->fetch_assoc();
$display_grade = $grade_row['avg_score'] ? round($grade_row['avg_score'], 1) : 0;

// Set dynamic labels
$grade_label = ($display_grade >= 75) ? "Great job!" : "Keep pushing!";
$grade_color = ($display_grade >= 50) ? "text-success" : "text-danger";

/*2. GRADE LOGIC
$grade_query = "SELECT AVG(score) as avg_score FROM grades WHERE user_id = $user_id";
$grade_result = $conn->query($grade_query);
$grade_row = $grade_result->fetch_assoc();

$display_grade = $grade_row['avg_score'] ? round($grade_row['avg_score'], 1) : 0;

$grade_label = "Keep it up!";
$grade_color = "text-success";*/

if ($display_grade < 50) {
    $grade_label = "Needs improvement";
    $grade_color = "text-danger";
} elseif ($display_grade < 75) {
    $grade_label = "Good progress";
    $grade_color = "text-warning";
} 

// 3. CREDIT LOGIC (MOVED OUTSIDE BRACKETS)
// This must be outside the if/else so it runs for every student
$total_courses_query = "SELECT COUNT(*) as count FROM courses WHERE user_id = $user_id";
$total_result = $conn->query($total_courses_query);
$total_row = $total_result->fetch_assoc();
$credits_earned = $total_row['count'] * 4; 

// 4. PENDING TASKS LOGIC
$tasks_query = "SELECT COUNT(*) as pending_count FROM assignments WHERE status = 'pending' AND user_id = $user_id";
$tasks_result = $conn->query($tasks_query);
$tasks_row = $tasks_result->fetch_assoc();
$pending_tasks = $tasks_row['pending_count'] ?? 0;
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LMS Dashboard - Student View</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Bootstrap Icons -->
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
              <p class="text-secondary small">Student Portal</p>
            </div>

            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link active" href="#" data-page="dashboard">
                  <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="assignments">
                  <i class="bi bi-pencil-square me-2"></i>Assignments
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="grades">
                  <i class="bi bi-trophy me-2"></i>My Grades
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-page="settings">
                  <i class="bi bi-gear me-2"></i>Settings
                </a>
              </li>
            </ul>

            <!-- Student Profile Section (Bottom of Sidebar) -->
            <div class="student-profile-sidebar">
  <div class="d-flex align-items-center">
    <img
      src="https://via.placeholder.com/50/4e73df/ffffff?text=<?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>"
      alt="Profile"
      class="profile-thumbnail rounded-circle me-2"
    />
    <div class="profile-info">
      <h6 class="text-white mb-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
      <p class="text-secondary small mb-0 mt-1">
        <i class="bi bi-mortarboard"></i> Student · Active
      </p>
    </div>
  </div>
</div>
          </div>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
          <!-- Dashboard Page -->
          <div id="dashboard-page" class="page active">
            <!-- Dashboard Header -->
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">Student Dashboard</h1>
              <div class="calendar-box">
                <input type="date" id="calendar" />
              </div>
            </div>

            <!-- Welcome Banner -->
            <div class="alert alert-primary welcome-banner mb-4">
  <h4 class="alert-heading">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
  <p class="mb-0">
    Here's what's happening with your learning progress today.
  </p>
</div>

            <!-- Stats Cards -->
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div
                      class="d-flex justify-content-between align-items-center"
                    >
                      <div>
    <h6 class="card-subtitle text-muted">Courses Enrolled</h6>
    
    <?php
    // Count total courses for the logged-in student
    $user_id = $_SESSION['user_id'];
    $count_query = "SELECT COUNT(*) as total FROM courses WHERE user_id = $user_id";
    $count_result = $conn->query($count_query);
    $count_row = $count_result->fetch_assoc();
    $total_courses = $count_row['total'];

    // Count only active/in-progress courses
    $progress_query = "SELECT COUNT(*) as in_progress FROM courses WHERE user_id = $user_id AND progress > 0 AND progress < 100";
    $progress_result = $conn->query($progress_query);
    $progress_row = $progress_result->fetch_assoc();
    $in_progress = $progress_row['in_progress'];
    ?>

    

    <h2 class="card-title mb-0"><?php echo $total_courses; ?></h2>
    <small class="text-success"><?php echo $in_progress; ?> in progress</small>
</div>


                      <div class="stat-icon bg-primary">
                        <i class="bi bi-book-fill"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div
                      class="d-flex justify-content-between align-items-center"
                    >
                      <div>
    <h6 class="card-subtitle text-muted">Overall Grade</h6>
    
    <h2 class="card-title mb-0"><?php echo $display_grade; ?>%</h2>
    
    <small class="<?php echo $grade_color; ?>">
        <i class="bi bi-graph-up-arrow me-1"></i><?php echo $grade_label; ?>
    </small>
</div>
                      <div class="stat-icon bg-success">
                        <i class="bi bi-star-fill"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card stat-card">
                  <div class="card-body">
                    <div
                      class="d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="card-subtitle text-muted">Pending Tasks</h6>
                        <h2 class="card-title mb-0"><?php echo $pending_tasks; ?></h2>
<small class="text-warning"><?php echo $pending_tasks; ?> due soon</small>
                      </div>
                      <div class="stat-icon bg-warning">
                        <i class="bi bi-hourglass-split"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">My Course Progress</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    // Fetch all courses for the logged-in student
                    $progress_query = "SELECT course_name, progress, last_activity FROM courses WHERE user_id = $user_id";
                    $progress_result = $conn->query($progress_query);

                    if ($progress_result->num_rows > 0):
                        while($course = $progress_result->fetch_assoc()):
                            // Determine bar color based on progress percentage
                            $bar_color = ($course['progress'] >= 80) ? 'bg-success' : 'bg-info';
                            
                            // Format the last activity date
                            $last_active = date("M d, Y", strtotime($course['last_activity']));
                    ?>
                    <div class="col-md-6">
                        <div class="course-item mb-4">
                            <h6 class="text-dark fw-semibold"><?php echo $course['course_name']; ?></h6>
                            <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                                <div class="progress-bar <?php echo $bar_color; ?> progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: <?php echo $course['progress']; ?>%">
                                    <?php echo $course['progress']; ?>% Complete
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock-history me-1"></i>
                                Last activity: <?php echo $last_active; ?>
                            </small>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <div class="col-12 text-center py-4">
                        <p class="text-muted">You are not enrolled in any courses yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Recent Activity and Upcoming -->
            <div class="row">
              <div class="col-md-7">
                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                  </div>
                  <div class="card-body p-0">
                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-primary me-3">
                        <i class="bi bi-file-text text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>Assignment Submitted</strong>
                        <div class="text-muted small">
                          Canva Design Assignment
                        </div>
                      </div>
                      <small class="text-secondary">2 hours ago</small>
                    </div>

                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-success me-3">
                        <i class="bi bi-check-circle text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>Quiz Completed</strong>
                        <div class="text-muted small">
                          GitHub Basics - Module 3 Quiz
                        </div>
                      </div>
                      <small class="text-secondary">5 hours ago</small>
                    </div>

                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-info me-3">
                        <i class="bi bi-journal-text text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>Grade Posted</strong>
                        <div class="text-muted small">
                          Google Docs Assessment: 92%
                        </div>
                      </div>
                      <small class="text-secondary">1 day ago</small>
                    </div>

                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-warning me-3">
                        <i class="bi bi-play-circle text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>Lecture Watched</strong>
                        <div class="text-muted small">
                          LinkedIn Profile Optimization
                        </div>
                      </div>
                      <small class="text-secondary">2 days ago</small>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-5">
                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Upcoming Deadlines</h5>
                  </div>
                  <div class="card-body p-0">
                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-danger me-3">
                        <i class="bi bi-exclamation-triangle text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>GitHub Project</strong>
                        <div class="text-muted small">Due in 2 days</div>
                      </div>
                      <span class="badge bg-warning">Urgent</span>
                    </div>

                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-primary me-3">
                        <i class="bi bi-file-earmark-text text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>LinkedIn Assignment</strong>
                        <div class="text-muted small">Due in 5 days</div>
                      </div>
                      <span class="badge bg-secondary">Upcoming</span>
                    </div>

                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-info me-3">
                        <i class="bi bi-question-circle text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>Canva Quiz</strong>
                        <div class="text-muted small">Due in 1 week</div>
                      </div>
                      <span class="badge bg-secondary">Upcoming</span>
                    </div>

                    <div class="assignment-item d-flex align-items-center">
                      <div class="assignment-icon bg-success me-3">
                        <i class="bi bi-file-text text-white"></i>
                      </div>
                      <div class="flex-grow-1">
                        <strong>Google Docs Essay</strong>
                        <div class="text-muted small">Due in 10 days</div>
                      </div>
                      <span class="badge bg-secondary">Upcoming</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Assignments Page -->
          <div id="assignments-page" class="page">
            <div
              class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
            >
              <h1 class="h2">My Assignments</h1>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="card mb-4">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Pending Assignments</h5>
                  </div>
                  <div class="card-body p-0">
                    <div
                      class="assignment-item d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="mb-1">GitHub Repository Setup</h6>
                        <small class="text-muted"
                          >GitHub Basics · Due in 2 days</small
                        >
                      </div>
                      <span class="badge bg-danger">Urgent</span>
                    </div>

                    <div
                      class="assignment-item d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="mb-1">LinkedIn Profile Optimization</h6>
                        <small class="text-muted"
                          >LinkedIn Essentials · Due in 5 days</small
                        >
                      </div>
                      <span class="badge bg-warning">This week</span>
                    </div>

                    <div
                      class="assignment-item d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="mb-1">Canva Portfolio Design</h6>
                        <small class="text-muted"
                          >Canva Design · Due in 1 week</small
                        >
                      </div>
                      <span class="badge bg-info">Upcoming</span>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header bg-white">
                    <h5 class="mb-0">Completed Assignments</h5>
                  </div>
                  <div class="card-body p-0">
                    <div
                      class="assignment-item d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="mb-1">GitHub Basics Quiz 1</h6>
                        <small class="text-muted"
                          >Grade: 92% · Submitted 2 days ago</small
                        >
                      </div>
                      <i class="bi bi-check-circle-fill text-success"></i>
                    </div>

                    <div
                      class="assignment-item d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="mb-1">Google Docs Formatting Exercise</h6>
                        <small class="text-muted"
                          >Grade: 88% · Submitted 1 week ago</small
                        >
                      </div>
                      <i class="bi bi-check-circle-fill text-success"></i>
                    </div>

                    <div
                      class="assignment-item d-flex justify-content-between align-items-center"
                    >
                      <div>
                        <h6 class="mb-1">Canva Introduction Project</h6>
                        <small class="text-muted"
                          >Grade: 95% · Submitted 2 weeks ago</small
                        >
                      </div>
                      <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div id="grades-page" class="page">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">My Grades</h1>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Overall GPA</h6>
                    <h2 class="display-4 text-primary"><?php echo round(($display_grade / 100) * 4, 1); ?></h2>
                    <small class="text-success">Based on current average</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Average Grade</h6>
                    <h2 class="display-4 text-success"><?php echo $display_grade; ?>%</h2>
                    <small class="<?php echo $grade_color; ?>"><?php echo $grade_label; ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Credits Earned</h6>
                    <h2 class="display-4 text-info"><?php echo $credits_earned; ?></h2>
<small class="text-muted">of 36 total</small>
                    <small class="text-muted">of 36 total</small>
                </div>
                
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Course Grades</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch courses and their corresponding grades from the database
                        $query = "SELECT c.course_name, c.progress, g.score 
                                  FROM courses c 
                                  LEFT JOIN grades g ON c.course_name = g.course_name 
                                  WHERE c.user_id = $user_id";
                        $result = $conn->query($query);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $score = $row['score'] ? $row['score'] . "%" : "N/A";
                                $prog = $row['progress'];
                                ?>
                                <tr>
                                    <td><?php echo $row['course_name']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $score; ?></span></td>
                                    <td><span class="badge bg-info">In Progress</span></td>
                                    <td>
                                        <div class="progress" style="height: 20px">
                                            <div class="progress-bar bg-success" style="width: <?php echo $prog; ?>%">
                                                <?php echo $prog; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No grades found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

          <!-- Settings Page -->
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
                  id="privacy-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#privacy"
                  type="button"
                  role="tab"
                >
                  Privacy
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
                              src="https://via.placeholder.com/150/4e73df/ffffff?text=T"
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
                              value="Tsi"
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
                              value="tsi@lms.com"
                            />
                          </div>
                          <div class="mb-3">
                            <label for="profileBio" class="form-label"
                              >About Me</label
                            >
                            <textarea
                              class="form-control"
                              id="profileBio"
                              rows="3"
                            >
Learning web development and design. Passionate about creating beautiful interfaces.</textarea
                            >
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
                          id="emailAssignments"
                          checked
                        />
                        <label class="form-check-label" for="emailAssignments">
                          <strong>Assignment reminders</strong>
                          <p class="text-muted small mb-0">
                            Get notified about upcoming deadlines
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch mb-3">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="emailGrades"
                          checked
                        />
                        <label class="form-check-label" for="emailGrades">
                          <strong>Grade updates</strong>
                          <p class="text-muted small mb-0">
                            When new grades are posted
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch mb-3">
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
                            Important updates from instructors
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="emailFeedback"
                        />
                        <label class="form-check-label" for="emailFeedback">
                          <strong>Feedback on assignments</strong>
                          <p class="text-muted small mb-0">
                            When instructors leave comments
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

              <!-- Privacy Settings -->
              <div class="tab-pane fade" id="privacy" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Privacy Settings</h5>

                    <div class="mb-4">
                      <div class="form-check form-switch mb-3">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="profileVisibility"
                          checked
                        />
                        <label class="form-check-label" for="profileVisibility">
                          <strong>Profile visibility</strong>
                          <p class="text-muted small mb-0">
                            Allow other students to see my profile
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch mb-3">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="progressSharing"
                          checked
                        />
                        <label class="form-check-label" for="progressSharing">
                          <strong>Share progress</strong>
                          <p class="text-muted small mb-0">
                            Let instructors see my learning analytics
                          </p>
                        </label>
                      </div>

                      <div class="form-check form-switch">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="activityStatus"
                          checked
                        />
                        <label class="form-check-label" for="activityStatus">
                          <strong>Show activity status</strong>
                          <p class="text-muted small mb-0">
                            Display when I'm online to classmates
                          </p>
                        </label>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="savePrivacySettings()"
                    >
                      Save Privacy Settings
                    </button>
                  </div>
                </div>
              </div>

              <!-- Appearance Settings -->
              <div class="tab-pane fade" id="appearance" role="tabpanel">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-4">Appearance</h5>

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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="script.js"></script>
  </body>
</html>
