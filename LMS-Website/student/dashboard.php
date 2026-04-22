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
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="card-subtitle text-muted">Courses Enrolled</h6>
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
          <div class="d-flex justify-content-between align-items-center">
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
          <div class="d-flex justify-content-between align-items-center">
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
            <?php if (!empty($course_progress_list)) : ?>
              <?php foreach ($course_progress_list as $course) : ?>
                <?php
                $progress_pct = min(100, max(0, (int) $course['progress']));
                $bar_color = ($progress_pct >= 80) ? 'bg-success' : 'bg-info';
                $last_active = date("M d, Y", strtotime((string) $course['last_activity']));
                ?>
                <div class="col-md-6">
                  <div class="course-item mb-4">
                    <h6 class="text-dark fw-semibold"><?php echo $course['course_name']; ?></h6>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                      <div class="progress-bar <?php echo $bar_color; ?> progress-bar-striped progress-bar-animated"
                           role="progressbar"
                           style="width: <?php echo $progress_pct; ?>%">
                        <?php echo $progress_pct; ?>% Complete
                      </div>
                    </div>
                    <small class="text-muted">
                      <i class="bi bi-clock-history me-1"></i>
                      Last activity: <?php echo $last_active; ?>
                    </small>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else : ?>
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
          <?php if (!empty($recent_activity)) : ?>
            <?php foreach ($recent_activity as $activity) : ?>
              <?php
              $activity_type = (string) ($activity['activity_type'] ?? '');
              $icon_class = 'bi-clock-history';
              $icon_bg = 'bg-secondary';
              if ($activity_type === 'Grade Posted') {
                  $icon_class = 'bi-check-circle';
                  $icon_bg = 'bg-success';
              } elseif ($activity_type === 'Assignment Pending') {
                  $icon_class = 'bi-file-earmark-text';
                  $icon_bg = 'bg-primary';
              } elseif ($activity_type === 'Course Activity') {
                  $icon_class = 'bi-journal-text';
                  $icon_bg = 'bg-info';
              }
              ?>
              <div class="assignment-item d-flex align-items-center">
                <div class="assignment-icon <?php echo $icon_bg; ?> me-3">
                  <i class="bi <?php echo $icon_class; ?> text-white"></i>
                </div>
                <div class="flex-grow-1">
                  <strong><?php echo htmlspecialchars($activity_type); ?></strong>
                  <div class="text-muted small"><?php echo htmlspecialchars((string) ($activity['activity_title'] ?? '')); ?></div>
                </div>
                <small class="text-secondary"><?php echo htmlspecialchars((string) ($activity['time_ago'] ?? 'Just now')); ?></small>
              </div>
            <?php endforeach; ?>
          <?php else : ?>
            <div class="p-4 text-center text-muted">No recent activity found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0">Upcoming Deadlines</h5>
        </div>
        <div class="card-body p-0">
          <?php if (!empty($upcoming_deadlines)) : ?>
            <?php foreach ($upcoming_deadlines as $deadline) : ?>
              <?php
              $due_ts = strtotime((string) ($deadline['due_date'] ?? ''));
              $seconds_left = ($due_ts !== false) ? ($due_ts - time()) : 0;
              $is_urgent = $seconds_left > 0 && $seconds_left <= (48 * 60 * 60);
              $days_left = ($due_ts !== false) ? (int) ceil($seconds_left / 86400) : 0;
              $relative_due = ($days_left <= 0) ? 'Due today' : 'Due in ' . $days_left . ' day' . ($days_left !== 1 ? 's' : '');
              ?>
              <div class="assignment-item d-flex align-items-center">
                <div class="assignment-icon <?php echo $is_urgent ? 'bg-danger' : 'bg-primary'; ?> me-3">
                  <i class="bi bi-exclamation-triangle text-white"></i>
                </div>
                <div class="flex-grow-1">
                  <strong><?php echo htmlspecialchars((string) ($deadline['title'] ?? '')); ?></strong>
                  <div class="text-muted small">
                    <?php echo htmlspecialchars((string) ($deadline['course_name'] ?? '')); ?> · <?php echo htmlspecialchars($relative_due); ?>
                  </div>
                </div>
                <span class="badge <?php echo $is_urgent ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                  <?php echo $is_urgent ? 'Urgent' : 'Upcoming'; ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php else : ?>
            <div class="p-4 text-center text-muted">No upcoming deadlines found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
