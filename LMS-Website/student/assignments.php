<!-- Assignments Page (data from student_dashboard_data.php) -->
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
          <?php if (!empty($upcoming_assignments)) : ?>
            <?php foreach ($upcoming_assignments as $pending_row) : ?>
              <?php
              $due_ts = strtotime((string) $pending_row['due_date']);
              $is_overdue = $due_ts !== false && $due_ts < time();
              ?>
              <div
                class="assignment-item d-flex justify-content-between align-items-center"
              >
                <div>
                  <h6 class="mb-1"><?php echo htmlspecialchars((string) $pending_row['title']); ?></h6>
                  <small class="text-muted"
                    ><?php echo htmlspecialchars((string) $pending_row['course_name']); ?>
                    <?php if ($due_ts !== false) : ?>
                      · Due <?php echo date('M j, Y', $due_ts); ?>
                    <?php endif; ?></small
                  >
                </div>
                <?php if ($is_overdue) : ?>
                  <span class="badge bg-danger">Overdue</span>
                <?php else : ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else : ?>
            <div class="p-4 text-center text-muted">
              <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
              No pending assignments for your enrolled courses.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0">Completed Assignments</h5>
        </div>
        <div class="card-body p-0">
          <?php if (!empty($graded_assignments)) : ?>
            <?php foreach ($graded_assignments as $done_row) : ?>
              <div
                class="assignment-item d-flex justify-content-between align-items-center"
              >
                <div>
                  <h6 class="mb-1"><?php echo htmlspecialchars((string) $done_row['title']); ?></h6>
                  <small class="text-muted"
                    ><?php echo htmlspecialchars((string) $done_row['course_name']); ?>
                    · Grade: <?php echo (int) $done_row['score']; ?>%
                    <?php if (!empty($done_row['date_posted'])) : ?>
                      · <?php echo date('M j, Y', strtotime((string) $done_row['date_posted'])); ?>
                    <?php endif; ?></small
                  >
                </div>
                <i class="bi bi-check-circle-fill text-success"></i>
              </div>
            <?php endforeach; ?>
          <?php else : ?>
            <div class="p-4 text-center text-muted">No graded assignments yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
