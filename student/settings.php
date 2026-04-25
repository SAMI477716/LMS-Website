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
