// Page navigation functionality
function showPage(pageId) {
    // Hide all pages
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    
    // Show selected page
    const pageElement = document.getElementById(pageId + '-page');
    if (pageElement) {
        pageElement.classList.add('active');
    }
    
    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.dataset.page === pageId) {
            link.classList.add('active');
        }
    });
}

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            if (page) {
                showPage(page);
            }
        });
    });
    
    // Set today's date in calendar
    const today = new Date().toISOString().split("T")[0];
    const calendar = document.getElementById("calendar");
    if (calendar) {
        calendar.value = today;
    }
    
    // Load saved theme
    loadSavedSettings();
});

// Profile picture preview
function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const profilePreview = document.getElementById('profilePreview');
            if (profilePreview) {
                profilePreview.src = e.target.result;
            }
            
            // Also update sidebar profile image
            const sidebarProfile = document.querySelector('.profile-thumbnail');
            if (sidebarProfile) {
                sidebarProfile.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Save profile settings
function saveProfileSettings() {
    const name = document.getElementById('profileName')?.value;
    const email = document.getElementById('profileEmail')?.value;
    
    // Update sidebar
    const sidebarName = document.querySelector('.profile-info h6');
    const sidebarEmail = document.querySelector('.profile-info small');
    
    if (sidebarName && name) sidebarName.textContent = name;
    if (sidebarEmail && email) sidebarEmail.textContent = email;
    
    showNotification('Profile updated successfully!', 'success');
}

// Save notification settings
function saveNotificationSettings() {
    showNotification('Notification preferences saved!', 'success');
}

// Save privacy settings
function savePrivacySettings() {
    showNotification('Privacy settings saved!', 'success');
}

// Select theme
function selectTheme(theme) {
    // Remove selected class from all theme options
    document.querySelectorAll('.theme-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    event.currentTarget.classList.add('selected');
    
    // Apply theme
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
    
    // Save preference
    localStorage.setItem('theme', theme);
}

// Save appearance settings
function saveAppearanceSettings() {
    showNotification('Appearance settings applied!', 'success');
}

// Load saved settings from localStorage
function loadSavedSettings() {
    // Load theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            // Update UI selection if theme options exist
            document.querySelectorAll('.theme-option').forEach(opt => {
                if (opt.getAttribute('onclick')?.includes('dark')) {
                    opt.classList.add('selected');
                }
            });
        }
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Export functions for global use
window.showPage = showPage;
window.previewProfilePicture = previewProfilePicture;
window.saveProfileSettings = saveProfileSettings;
window.saveNotificationSettings = saveNotificationSettings;
window.savePrivacySettings = savePrivacySettings;
window.selectTheme = selectTheme;
window.saveAppearanceSettings = saveAppearanceSettings;
// ============ GOOGLE FORMS FOR STUDENT PAGE ============

// Student data (current logged in student)
const currentStudent = {
    id: 'ST002',
    name: 'Tsi',
    batch: 'Batch 1',
    email: 'tsi@lms.com'
};

let studentGoogleForms = [];
let studentFormSubmissions = {};

// Initialize Google Forms for student
function initStudentGoogleForms() {
    // Load forms assigned to this student from localStorage (sent by instructor)
    const allForms = localStorage.getItem('googleFormsData');
    if (allForms) {
        const forms = JSON.parse(allForms);
        studentGoogleForms = forms.filter(f => f.assignedTo === currentStudent.id);
    }

    // Load submissions
    const submissions = localStorage.getItem('formSubmissions');
    if (submissions) {
        studentFormSubmissions = JSON.parse(submissions);
    }

    displayStudentForms();
}

// Display forms for student
function displayStudentForms() {
    const container = document.getElementById('student-google-forms-list');
    if (!container) return;

    if (studentGoogleForms.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="bi bi-google fs-1 text-muted"></i>
                <p class="text-muted mt-2">No Google Forms assigned yet.</p>
            </div>
        `;
        return;
    }

    let html = '';
    studentGoogleForms.forEach(form => {
        const submitted = studentFormSubmissions[form.id]?.studentId === currentStudent.id;
        const dueDate = form.dueDate ? new Date(form.dueDate) : null;
        const isOverdue = dueDate && dueDate < new Date();

        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="google-form-card" onclick="openStudentGoogleForm('${form.id}')">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="mb-1">${escapeHtml(form.title)}</h6>
                        <span class="${submitted ? 'form-submitted-badge' : 'form-pending-badge'}">
                            ${submitted ? '✓ Completed' : 'Pending'}
                        </span>
                    </div>
                    <small class="text-muted">${form.course}</small>
                    <p class="small mt-2 mb-1">${escapeHtml(form.description) || 'No description'}</p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small><i class="bi bi-star me-1"></i>${form.points} pts</small>
                        ${dueDate ? `<small><i class="bi bi-calendar me-1"></i>Due: ${dueDate.toLocaleDateString()}</small>` : ''}
                    </div>
                    ${submitted ? `
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> Submitted on ${new Date(studentFormSubmissions[form.id].submittedAt).toLocaleDateString()}
                            </small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Open Google Form
function openStudentGoogleForm(formId) {
    const form = studentGoogleForms.find(f => f.id === formId);
    if (!form) return;

    document.getElementById('viewFormTitle').innerText = form.title;
    document.getElementById('googleFormIframe').src = form.embedUrl;
    window.currentStudentFormId = formId;

    new bootstrap.Modal(document.getElementById('viewGoogleFormModal')).show();
}

// Submit Google Form response
function submitGoogleFormResponse() {
    const formId = window.currentStudentFormId;
    const form = studentGoogleForms.find(f => f.id === formId);

    if (!form) return;

    // Check if already submitted
    if (studentFormSubmissions[formId]?.studentId === currentStudent.id) {
        showNotification('You have already submitted this form!', 'warning');
        bootstrap.Modal.getInstance(document.getElementById('viewGoogleFormModal')).hide();
        return;
    }

    // Record submission
    studentFormSubmissions[formId] = {
        studentId: currentStudent.id,
        studentName: currentStudent.name,
        submittedAt: new Date().toISOString(),
        grade: form.points,
        formTitle: form.title
    };

    // Save to localStorage
    localStorage.setItem('formSubmissions', JSON.stringify(studentFormSubmissions));

    // Also add to grades in localStorage
    const existingGrades = localStorage.getItem('studentGrades');
    let grades = existingGrades ? JSON.parse(existingGrades) : [];
    grades.push({
        course: form.course,
        grade: form.points,
        assessment: form.title,
        date: new Date().toISOString()
    });
    localStorage.setItem('studentGrades', JSON.stringify(grades));

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('viewGoogleFormModal')).hide();

    // Refresh display
    displayStudentForms();

    showNotification(`Form "${form.title}" submitted! You earned ${form.points} points.`, 'success');
}

// Show student profile
function showMyProfile() {
    showPage('student-details');

    const container = document.getElementById('student-profile-container');
    container.innerHTML = `
        <div class="student-profile-header text-center">
            <div class="student-avatar-large mx-auto mb-3">
                ${currentStudent.name.charAt(0)}
            </div>
            <h3>${currentStudent.name}</h3>
            <p class="mb-1">${currentStudent.batch}</p>
            <p class="mb-0"><i class="bi bi-envelope me-2"></i>${currentStudent.email}</p>
        </div>
    `;
}

// Helper function
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions global
window.openStudentGoogleForm = openStudentGoogleForm;
window.submitGoogleFormResponse = submitGoogleFormResponse;
window.showMyProfile = showMyProfile;

// Initialize when page loads
initStudentGoogleForms();
