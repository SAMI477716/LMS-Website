<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/student_dashboard_data.php';
include __DIR__ . '/header.php';
include __DIR__ . '/sidebar.php';
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
<?php
$student_pages = ['dashboard', 'assignments', 'grades', 'student-details', 'settings'];
foreach ($student_pages as $student_page) {
    include __DIR__ . '/' . $student_page . '.php';
}
?>
</main>
</div>
</div>
<div class="modal fade" id="viewGoogleFormModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewFormTitle">Google Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="googleFormIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" onclick="submitGoogleFormResponse()">
          <i class="bi bi-check-circle me-1"></i>Submit Form
        </button>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.studentProfileData = <?php echo json_encode([
    'id' => (string) ($student_profile['id'] ?? $_SESSION['user_id']),
    'name' => (string) ($student_profile['full_name'] ?? $_SESSION['username']),
    'batch' => (string) ($student_profile['batch_display'] ?? 'No Batch Assigned'),
    'email' => (string) ($student_profile['email'] ?? 'N/A'),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="script.js"></script>
</body>
</html>
