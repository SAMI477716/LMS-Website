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
                      <?php if (!empty($course_grades)) : ?>
                          <?php foreach ($course_grades as $row) : ?>
                              <?php
                              $score = $row['score'] ? $row['score'] . "%" : "N/A";
                              $prog = min(100, max(0, (int) $row['progress']));
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
                          <?php endforeach; ?>
                      <?php else : ?>
                          <tr><td colspan="4" class="text-center">No grades found.</td></tr>
                      <?php endif; ?>
                  </tbody>
              </table>
          </div>
      </div>
  </div>
</div>
