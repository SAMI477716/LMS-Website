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
        <a class="nav-link" href="#" data-page="student-details" onclick="showMyProfile()">
          <i class="bi bi-person-circle me-2"></i> Profile
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
