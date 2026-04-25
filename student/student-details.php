<!-- Student Details Page - Add this before Settings Page -->
<div id="student-details-page" class="page">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Details</h1>
    <button class="btn btn-outline-secondary" onclick="showPage('dashboard')">
      <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
    </button>
  </div>

  <!-- Student Profile -->
  <div id="student-profile-container">
    <div class="student-profile-header text-center">
      <div class="student-avatar-large mx-auto mb-3">
        <?php echo htmlspecialchars((string) ($student_profile['profile_initial'] ?? 'S')); ?>
      </div>
      <h3><?php echo htmlspecialchars((string) ($student_profile['full_name'] ?? ($_SESSION['username'] ?? 'Student'))); ?></h3>
      <p class="mb-1"><?php echo htmlspecialchars((string) ($student_profile['batch_display'] ?? 'No Batch Assigned')); ?></p>
      <p class="mb-0">
        <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars((string) ($student_profile['email'] ?? 'N/A')); ?>
      </p>
    </div>
  </div>

  <!-- Google Forms Assessments Section -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0"><i class="bi bi-google me-2"></i>My Google Forms Assessments</h5>
        </div>
        <div class="card-body">
          <div id="student-google-forms-list" class="row"></div>
        </div>
      </div>
    </div>
  </div>
</div>
