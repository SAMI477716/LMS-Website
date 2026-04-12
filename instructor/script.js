// script.js

// Global data stores
let studentsData = [];
let coursesData = [];
let gradesData = [];
let currentTheme = 'light';
let currentLayout = 'comfortable';

// Initialize the application
document.addEventListener('DOMContentLoaded', function () {
    // Initialize sample data
    initializeSampleData();

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add click handlers to nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const page = this.dataset.page;
            if (page) {
                showPage(page);
            }
        });
    });

    // Show dashboard by default
    showPage('dashboard');

    // Add interactive features
    addInteractiveFeatures();

    // Load saved settings
    loadSavedSettings();
});

// Initialize sample data
function initializeSampleData() {
    // Students data
    studentsData = [
        { id: 'ST001', name: 'Samuel', batch: 'Batch 1', email: 'samiuel@email.com', enrollmentDate: '2025-09-01' },
        { id: 'ST002', name: 'Tsion', batch: 'Batch 1', email: 'tsion@email.com', enrollmentDate: '2025-08-15' },
        { id: 'ST003', name: 'Mikyas', batch: 'Batch 2', email: 'mikyas@email.com', enrollmentDate: '2024-01-10' },
        { id: 'ST004', name: 'Eldana', batch: 'Batch 2', email: 'eldana@email.com', enrollmentDate: '2024-01-15' },
        { id: 'ST005', name: 'Yohannes', batch: 'Batch 1', email: 'yohannes@email.com', enrollmentDate: '2025-08-20' },
        { id: 'ST006', name: 'Biruk', batch: 'Batch 2', email: 'biruk@email.com', enrollmentDate: '2024-02-01' },
        { id: 'ST007', name: 'Lidiya', batch: 'Batch 2', email: 'lidiya@email.com', enrollmentDate: '2024-01-20' },
        { id: 'ST008', name: 'Abel', batch: 'Batch 1', email: 'abel@email.com', enrollmentDate: '2025-09-05' }
    ];

    // Courses data
    coursesData = [
        {
            id: 'CS101',
            name: 'GitHub Basics for Beginners',
            code: 'GH101',
            students: ['ST001', 'ST002', 'ST005', 'ST008'],
            visibility: 'public',
            enrollment: 'open'
        },
        {
            id: 'CS102',
            name: 'LinkedIn Essentials for Beginners',
            code: 'LI101',
            students: ['ST001', 'ST002', 'ST003', 'ST004'],
            visibility: 'public',
            enrollment: 'open'
        },
        {
            id: 'CS103',
            name: 'Canva Design Basics for Beginners',
            code: 'CD101',
            students: ['ST003', 'ST004', 'ST006', 'ST007'],
            visibility: 'private',
            enrollment: 'invite'
        },
        {
            id: 'CS104',
            name: 'Getting Started with Google Docs',
            code: 'GD101',
            students: ['ST001', 'ST005', 'ST006', 'ST008'],
            visibility: 'public',
            enrollment: 'open'
        },

    ];

    // Grades data
    gradesData = [
        { studentId: 'ST001', studentName: 'Samuel', course: 'Getting Started with Google Docs', grade: 95, courseId: 'CS105' },
        { studentId: 'ST003', studentName: 'Mikyas', course: 'LinkedIn Essentials for Beginners', grade: 90, courseId: 'CS105' },
        { studentId: 'ST005', studentName: 'Yohannes', course: 'Getting Started with Google Docs', grade: 85, courseId: 'CS105' },
        { studentId: 'ST002', studentName: 'Tsion', course: 'GitHub Basics for Beginners', grade: 88, courseId: 'CS101' },
        { studentId: 'ST005', studentName: 'Yohannes', course: 'GitHub Basics for Beginners', grade: 67, courseId: 'CS101' },
        { studentId: 'ST008', studentName: 'Abel', course: 'LinkedIn Essentials for Beginners', grade: 78, courseId: 'CS101' },
        { studentId: 'ST003', studentName: 'Mikyas', course: 'Canva Design Basics for Beginners', grade: 78, courseId: 'CS103' },
        { studentId: 'ST006', studentName: 'Biruk', course: 'Canva Design Basics for Beginners', grade: 95, courseId: 'CS103' }
    ];
}

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

        // Load page-specific data
        switch (pageId) {
            case 'students':
                displayStudentsByBatch();
                break;
            case 'courses':
                displayCoursesWithStudents();
                break;
            case 'grades':
                displayGradesRanking();
                break;
        }
    }

    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.dataset.page === pageId) {
            link.classList.add('active');
        }
    });
}

// Students Dashboard Functions
function displayStudentsByBatch() {
    const container = document.getElementById('students-by-batch');
    if (!container) return;

    // Group students by batch
    const batches = {};
    studentsData.forEach(student => {
        if (!batches[student.batch]) {
            batches[student.batch] = [];
        }
        batches[student.batch].push(student);
    });

    let html = '';

    // Sort batches by year (descending)
    Object.keys(batches).sort().reverse().forEach(batch => {
        const students = batches[batch];

        html += `
            <div class="col-12 mb-4">
                <div class="batch-card">
                    <div class="batch-header">
                        <h5>${batch}</h5>
                        <div class="batch-stats">
                            <span><i class="bi bi-people-fill me-1"></i>${students.length} Students</span>
                            <span><i class="bi bi-calendar me-1"></i>Active</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="student-list">
        `;

        students.sort((a, b) => a.name.localeCompare(b.name)).forEach(student => {
            html += `
                <li class="student-list-item" onclick="viewStudentDetails('${student.id}')">
                    <div class="d-flex align-items-center">
                        <div class="student-avatar-small">
                            ${student.name.charAt(0)}
                        </div>
                        <div class="student-info">
                            <h6>${student.name}</h6>
                            <small>${student.id} | ${student.email}</small>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-light text-dark">Enrolled: ${formatDate(student.enrollmentDate)}</span>
                        </div>
                    </div>
                </li>
            `;
        });

        html += `
                        </ul>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Courses Dashboard Functions
function displayCoursesWithStudents() {
    const container = document.getElementById('courses-with-students');
    if (!container) return;

    let html = '';

    coursesData.forEach(course => {
        // Get enrolled students for this course
        const enrolledStudents = studentsData.filter(student =>
            course.students.includes(student.id)
        );

        html += `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="course-enrollment-card">
                    <div class="course-header">
                        <h5 class="mb-1">${course.name}</h5>
                        <div class="course-code">${course.code}</div>
                        <span class="enrollment-badge mt-2">
                            <i class="bi bi-${course.visibility === 'public' ? 'globe' : 'lock'} me-1"></i>
                            ${course.visibility.charAt(0).toUpperCase() + course.visibility.slice(1)}
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">
                            <i class="bi bi-people-fill me-2"></i>
                            Enrolled Students (${enrolledStudents.length})
                        </h6>
                        <div class="enrolled-students-list">
        `;

        enrolledStudents.sort((a, b) => a.name.localeCompare(b.name)).forEach(student => {
            const isOnline = Math.random() > 0.5; // Simulated online status
            html += `
                <div class="enrolled-student-item" onclick="viewStudentDetails('${student.id}')">
                    <div class="student-indicator ${isOnline ? 'online' : 'offline'}"></div>
                    <div class="flex-grow-1">
                        <strong>${student.name}</strong>
                        <br>
                        <small class="text-muted">${student.id}</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            `;
        });

        html += `
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Grades Dashboard Functions
function displayGradesRanking(filterCourse = 'all') {
    const tableBody = document.getElementById('grades-table-body');
    if (!tableBody) return;

    let filteredGrades = [...gradesData];

    if (filterCourse !== 'all') {
        const courseMap = {
            'github': 'CS101',
            'linkedin': 'CS102',
            'canva': 'CS103',
            'docs': 'CS104',
            'datastructures': 'CS105'
        };
        const courseId = courseMap[filterCourse];
        if (courseId) {
            filteredGrades = filteredGrades.filter(g => g.courseId === courseId);
        }
    }

    // Sort by grade descending
    filteredGrades.sort((a, b) => b.grade - a.grade);

    let html = '';
    filteredGrades.forEach((grade, index) => {
        const rank = index + 1;
        let rankClass = '';
        if (rank === 1) rankClass = 'rank-1';
        else if (rank === 2) rankClass = 'rank-2';
        else if (rank === 3) rankClass = 'rank-3';

        let gradeClass = '';
        if (grade.grade >= 90) gradeClass = 'grade-a';
        else if (grade.grade >= 80) gradeClass = 'grade-b';
        else if (grade.grade >= 70) gradeClass = 'grade-c';
        else gradeClass = 'grade-d';

        html += `
            <tr onclick="viewStudentDetails('${grade.studentId}')" style="cursor: pointer;">
                <td>
                    <div class="rank-badge ${rankClass}">${rank}</div>
                </td>
                <td><strong>${grade.studentName}</strong></td>
                <td>${grade.course}</td>
                <td><span class="grade-badge ${gradeClass}">${grade.grade}%</span></td>
                <td>
                    <span class="badge ${grade.grade >= 60 ? 'bg-success' : 'bg-danger'}">
                        ${grade.grade >= 60 ? 'Passing' : 'Failing'}
                    </span>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

// Filter grades by course
function filterGradesByCourse(course) {
    displayGradesRanking(course);
}

// Settings Functions
function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('profilePreview').src = e.target.result;

            // Also update sidebar profile image
            const sidebarProfile = document.querySelector('.profile-thumbnail');
            if (sidebarProfile) {
                sidebarProfile.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function saveProfileSettings() {
    const name = document.getElementById('profileName').value;
    const email = document.getElementById('profileEmail').value;
    const bio = document.getElementById('profileBio').value;

    // Update sidebar
    const sidebarName = document.querySelector('.profile-info h6');
    const sidebarEmail = document.querySelector('.profile-info small');
    const sidebarBio = document.querySelector('.profile-info p');

    if (sidebarName) sidebarName.textContent = name;
    if (sidebarEmail) sidebarEmail.textContent = email;
    if (sidebarBio) sidebarBio.innerHTML = `<i class="bi bi-chat-quote"></i> ${bio}`;

    // Update main header
    const instructorName = document.querySelector('.instructor-avatar + h6');
    if (instructorName) instructorName.textContent = name;

    showNotification('Profile updated successfully!', 'success');
}

function changePassword() {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;

    // Simple validation
    if (!current || !newPass || !confirm) {
        showNotification('Please fill in all fields', 'error');
        return;
    }

    if (newPass !== confirm) {
        document.getElementById('confirmPassword').classList.add('is-invalid');
        return;
    }

    if (newPass.length < 8) {
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }

    // Check password strength
    const strength = checkPasswordStrength(newPass);
    if (strength < 60) {
        showNotification('Please choose a stronger password', 'warning');
        return;
    }

    // Simulate password change
    showNotification('Password changed successfully!', 'success');
    document.getElementById('passwordSettingsForm').reset();
}

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]+/)) strength += 25;
    if (password.match(/[A-Z]+/)) strength += 25;
    if (password.match(/[0-9]+/)) strength += 25;
    if (password.match(/[$@#&!]+/)) strength += 25;

    return Math.min(strength, 100);
}

// Update password strength indicator
document.getElementById('newPassword')?.addEventListener('input', function (e) {
    const strength = checkPasswordStrength(e.target.value);
    const progressBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');

    if (progressBar) {
        progressBar.style.width = strength + '%';

        if (strength < 40) {
            progressBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Weak password';
        } else if (strength < 70) {
            progressBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Medium password';
        } else {
            progressBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Strong password';
        }
    }
});

// Password match validation
document.getElementById('confirmPassword')?.addEventListener('input', function (e) {
    const newPass = document.getElementById('newPassword').value;
    if (e.target.value !== newPass) {
        e.target.classList.add('is-invalid');
    } else {
        e.target.classList.remove('is-invalid');
    }
});

function saveCourseSettings() {
    const visibility = document.querySelector('input[name="courseVisibility"]:checked').id;
    const enrollment = document.querySelector('input[name="enrollmentOption"]:checked').id;

    // Update all courses with default settings
    coursesData.forEach(course => {
        course.visibility = visibility === 'visibilityPublic' ? 'public' : 'private';
        course.enrollment = enrollment === 'enrollmentOpen' ? 'open' : 'invite';
    });

    showNotification('Course settings saved!', 'success');
}

function saveNotificationSettings() {
    const emailSubmissions = document.getElementById('emailSubmissions').checked;
    const emailMessages = document.getElementById('emailMessages').checked;
    const emailGrades = document.getElementById('emailGrades').checked;
    const emailAnnouncements = document.getElementById('emailAnnouncements').checked;

    // Save to localStorage
    const settings = {
        emailSubmissions,
        emailMessages,
        emailGrades,
        emailAnnouncements
    };
    localStorage.setItem('notificationSettings', JSON.stringify(settings));

    showNotification('Notification preferences saved!', 'success');
}

function selectLayout(layout) {
    currentLayout = layout;

    // Update UI
    document.querySelectorAll('.layout-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');

    // Apply layout changes
    document.body.classList.remove('layout-compact', 'layout-spacious');
    if (layout !== 'comfortable') {
        document.body.classList.add(`layout-${layout}`);
    }
}

function selectTheme(theme) {
    currentTheme = theme;

    // Update UI
    document.querySelectorAll('.theme-option').forEach(opt => {
        opt.classList.remove('selected');
    });
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

function setAccentColor(color) {
    document.documentElement.style.setProperty('--primary-color', `var(--${color})`);
    localStorage.setItem('accentColor', color);
}

function saveLayoutSettings() {
    showNotification('Layout settings saved!', 'success');
}

function saveAppearanceSettings() {
    showNotification('Appearance settings applied!', 'success');
}

// Load saved settings from localStorage
function loadSavedSettings() {
    // Load theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        selectTheme(savedTheme);
        // Update UI selection
        document.querySelectorAll('.theme-option').forEach(opt => {
            if (opt.getAttribute('onclick').includes(savedTheme)) {
                opt.classList.add('selected');
            }
        });
    }

    // Load accent color
    const savedColor = localStorage.getItem('accentColor');
    if (savedColor) {
        setAccentColor(savedColor);
    }

    // Load notification settings
    const savedNotifications = localStorage.getItem('notificationSettings');
    if (savedNotifications) {
        const settings = JSON.parse(savedNotifications);
        document.getElementById('emailSubmissions').checked = settings.emailSubmissions;
        document.getElementById('emailMessages').checked = settings.emailMessages;
        document.getElementById('emailGrades').checked = settings.emailGrades;
        document.getElementById('emailAnnouncements').checked = settings.emailAnnouncements;
    }
}

// Helper Functions
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function showNotification(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
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

function exportStudentList() {
    // Create CSV content
    let csv = 'Student ID,Name,Batch,Email,Enrollment Date\n';
    studentsData.forEach(student => {
        csv += `${student.id},${student.name},${student.batch},${student.email},${student.enrollmentDate}\n`;
    });

    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'student_list.csv';
    a.click();

    showNotification('Student list exported!', 'success');
}

function showAddCourseModal() {
    const modal = new bootstrap.Modal(document.getElementById('addCourseModal'));
    modal.show();
}

function addNewCourse() {
    const name = document.getElementById('courseName').value;
    const code = document.getElementById('courseCode').value;
    const description = document.getElementById('courseDescription').value;
    const visibility = document.getElementById('courseVisibility').value;

    if (!name || !code) {
        showNotification('Please fill in required fields', 'error');
        return;
    }

    // Create new course
    const newCourse = {
        id: 'CS' + (coursesData.length + 100),
        name: name,
        code: code,
        description: description,
        visibility: visibility,
        enrollment: 'open',
        students: []
    };

    coursesData.push(newCourse);

    // Close modal and refresh display
    const modal = bootstrap.Modal.getInstance(document.getElementById('addCourseModal'));
    modal.hide();
    document.getElementById('addCourseForm').reset();

    displayCoursesWithStudents();
    showNotification('Course added successfully!', 'success');
}

// View student details (existing function with enhancements)
function viewStudentDetails(studentId) {
    showPage('student');

    // Enhanced student data lookup
    const student = studentsData.find(s => s.id === studentId);
    if (!student) return;

    // Get student's courses and grades
    const studentCourses = coursesData.filter(course =>
        course.students.includes(studentId)
    );

    const studentGrades = gradesData.filter(grade =>
        grade.studentId === studentId
    );

    displayStudentDetails(student, studentCourses, studentGrades);
}

// Enhanced display student details
function displayStudentDetails(student, courses, grades) {
    const container = document.getElementById('student-details-container');

    // Build course progress HTML
    let coursesHTML = '';
    courses.forEach(course => {
        const courseGrade = grades.find(g => g.courseId === course.id);
        const grade = courseGrade ? courseGrade.grade : 'N/A';
        const status = grade !== 'N/A' && grade >= 60 ? 'Completed' : 'In Progress';
        const statusClass = status === 'Completed' ? 'status-completed' : 'status-progress';

        coursesHTML += `
            <div class="course-progress-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">${course.name}</h6>
                    <span class="status-badge ${statusClass}">${status}</span>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Grade</small>
                        <h5>${grade}%</h5>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Course Code</small>
                        <h6>${course.code}</h6>
                    </div>
                </div>
            </div>
        `;
    });

    // Build complete HTML
    container.innerHTML = `
        <div class="student-profile-header text-center">
            <div class="student-avatar-large mx-auto">
                ${student.name.charAt(0)}
            </div>
            <h3>${student.name}</h3>
            <p class="mb-1">${student.batch}</p>
            <p class="mb-0"><i class="bi bi-envelope me-2"></i>${student.email}</p>
            <p class="mb-0"><i class="bi bi-card-text me-2"></i>ID: ${student.id}</p>
            <p class="mb-0"><i class="bi bi-calendar me-2"></i>Enrolled: ${formatDate(student.enrollmentDate)}</p>
        </div>
        
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Course Progress</h5>
                    </div>
                    <div class="card-body">
                        ${coursesHTML || '<p class="text-muted text-center py-4">No courses enrolled</p>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Submit grade from modal (enhanced)
function submitGrade() {
    const studentName = document.getElementById('studentName').value;
    const courseName = document.getElementById('courseName').value;
    const gradeValue = document.getElementById('gradeValue').value;
    const assessmentType = document.getElementById('assessmentType').value;

    // Simple validation
    if (!studentName || !courseName || !gradeValue || !assessmentType) {
        showNotification('Please fill in all fields', 'error');
        return;
    }

    if (gradeValue < 0 || gradeValue > 100) {
        showNotification('Grade must be between 0 and 100', 'error');
        return;
    }

    // Find student and course
    const student = studentsData.find(s => s.name === studentName);
    const course = coursesData.find(c => c.name === courseName);

    if (student && course) {
        // Add grade to gradesData
        gradesData.push({
            studentId: student.id,
            studentName: student.name,
            course: courseName,
            grade: parseInt(gradeValue),
            courseId: course.id
        });
    }

    // Simulate form submission
    showNotification(`Grade submitted successfully!`, 'success');

    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('gradeModal'));
    modal.hide();
    document.getElementById('gradeForm').reset();

    // Refresh grades if on grades page
    if (document.getElementById('grades-page').classList.contains('active')) {
        displayGradesRanking();
    }
}

// Add interactive features (enhanced)
function addInteractiveFeatures() {
    // Add hover effects to cards
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transition = 'all 0.3s';
        });
    });

    // Add keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Ctrl + D for dashboard
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            showPage('dashboard');
        }
        // Ctrl + S for students
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            showPage('students');
        }
        // Ctrl + C for courses
        if (e.ctrlKey && e.key === 'c') {
            e.preventDefault();
            showPage('courses');
        }
        // Ctrl + G for grades
        if (e.ctrlKey && e.key === 'g') {
            e.preventDefault();
            showPage('grades');
        }
    });
}

// Export functions for global use
window.showPage = showPage;
window.viewStudentDetails = viewStudentDetails;
window.submitGrade = submitGrade;
window.viewGoogleForm = viewGoogleForm;
window.previewAssessment = previewAssessment;
window.filterGradesByCourse = filterGradesByCourse;
window.exportStudentList = exportStudentList;
window.showAddCourseModal = showAddCourseModal;
window.addNewCourse = addNewCourse;
window.previewProfilePicture = previewProfilePicture;
window.saveProfileSettings = saveProfileSettings;
window.changePassword = changePassword;
window.saveCourseSettings = saveCourseSettings;
window.saveNotificationSettings = saveNotificationSettings;
window.selectLayout = selectLayout;
window.selectTheme = selectTheme;
window.setAccentColor = setAccentColor;
window.saveLayoutSettings = saveLayoutSettings;
window.saveAppearanceSettings = saveAppearanceSettings;


const today = new Date().toISOString().split("T")[0];
document.getElementById("calendar").value = today;

// ============ GOOGLE FORMS INTEGRATION ============

// Store Google Forms data with student assignments
let googleFormsData = [];
let formSubmissions = {};

// Current student being viewed
let currentViewingStudentId = null;

// Initialize Google Forms
function initGoogleForms() {
    loadGoogleFormsFromStorage();
    loadSubmissionsFromStorage();
}

// Load forms from localStorage
function loadGoogleFormsFromStorage() {
    const saved = localStorage.getItem('googleFormsData');
    if (saved) {
        googleFormsData = JSON.parse(saved);
    } else {
        // Sample Google Forms with student assignments
        googleFormsData = [
            {
                id: 'form1',
                title: 'GitHub Basics Quiz',
                course: 'GitHub Basics for Beginners',
                embedUrl: 'https://docs.google.com/forms/d/e/1FAIpQLSdummy1/viewform?embedded=true',
                description: 'Test your knowledge of Git commands and GitHub workflow',
                assignedTo: ['ST001', 'ST002', 'ST005', 'ST008'],
                dueDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
                points: 100,
                createdAt: new Date().toISOString()
            },
            {
                id: 'form2',
                title: 'Canva Design Assessment',
                course: 'Canva Design Basics for Beginners',
                embedUrl: 'https://docs.google.com/forms/d/e/1FAIpQLSdummy2/viewform?embedded=true',
                description: 'Showcase your Canva design skills',
                assignedTo: ['ST003', 'ST004', 'ST006', 'ST007'],
                dueDate: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString(),
                points: 100,
                createdAt: new Date().toISOString()
            },
            {
                id: 'form3',
                title: 'LinkedIn Profile Review',
                course: 'LinkedIn Essentials for Beginners',
                embedUrl: 'https://docs.google.com/forms/d/e/1FAIpQLSdummy3/viewform?embedded=true',
                description: 'Submit your LinkedIn profile for review',
                assignedTo: ['ST001', 'ST002', 'ST003', 'ST004'],
                dueDate: new Date(Date.now() + 10 * 24 * 60 * 60 * 1000).toISOString(),
                points: 50,
                createdAt: new Date().toISOString()
            }
        ];
        saveGoogleFormsToStorage();
    }
}

// Load submissions from localStorage
function loadSubmissionsFromStorage() {
    const saved = localStorage.getItem('formSubmissions');
    if (saved) {
        formSubmissions = JSON.parse(saved);
    } else {
        formSubmissions = {};
        saveSubmissionsToStorage();
    }
}

// Save forms to localStorage
function saveGoogleFormsToStorage() {
    localStorage.setItem('googleFormsData', JSON.stringify(googleFormsData));
}

// Save submissions to localStorage
function saveSubmissionsToStorage() {
    localStorage.setItem('formSubmissions', JSON.stringify(formSubmissions));
}

// Display Google Forms for a specific student
function displayStudentGoogleForms(studentId) {
    const container = document.getElementById('student-google-forms-list');
    if (!container) return;

    // Get forms assigned to this student
    const studentForms = googleFormsData.filter(form =>
        form.assignedTo && form.assignedTo.includes(studentId)
    );

    if (studentForms.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="bi bi-google fs-1 text-muted"></i>
                <p class="text-muted mt-2">No Google Forms assigned to this student yet.</p>
                <button class="btn btn-sm btn-outline-primary" onclick="showAddGoogleFormForStudent()">
                    <i class="bi bi-plus-circle me-1"></i>Assign Form
                </button>
            </div>
        `;
        return;
    }

    let html = '';
    studentForms.forEach(form => {
        const dueDate = new Date(form.dueDate);
        const isOverdue = dueDate < new Date();
        const hasSubmitted = checkStudentSubmission(form.id, studentId);
        const submission = getStudentSubmission(form.id, studentId);

        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="google-form-card" onclick="viewStudentGoogleForm('${form.id}', '${studentId}')">
                    <div class="google-form-header">
                        <div class="google-form-icon">
                            <i class="bi bi-google"></i>
                        </div>
                        <div class="google-form-status">
                            ${hasSubmitted ?
                '<i class="bi bi-check-circle-fill text-success"></i> Completed' :
                '<i class="bi bi-hourglass-split"></i> Pending'}
                        </div>
                    </div>
                    <div class="google-form-body">
                        <h6>${escapeHtml(form.title)}</h6>
                        <small class="text-muted">${form.course}</small>
                        <p class="small mt-2">${escapeHtml(form.description) || 'No description'}</p>
                        ${hasSubmitted && submission ? `
                            <div class="alert alert-success alert-sm py-1 mb-2">
                                <small><i class="bi bi-trophy"></i> Grade: ${submission.grade}/${form.points} (${Math.round((submission.grade / form.points) * 100)}%)</small>
                            </div>
                        ` : ''}
                        <div class="form-meta d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-star me-1"></i>${form.points} pts
                            </span>
                            <span class="${isOverdue ? 'form-pending-badge' : 'form-due-badge'}">
                                <i class="bi bi-calendar me-1"></i>Due: ${dueDate.toLocaleDateString()}
                            </span>
                        </div>
                        ${hasSubmitted ? `
                            <div class="mt-2">
                                <span class="form-submitted-badge">
                                    <i class="bi bi-check-circle me-1"></i>Submitted on ${new Date(submission.submittedAt).toLocaleDateString()}
                                </span>
                            </div>
                        ` : `
                            <div class="mt-2">
                                <button class="btn btn-sm btn-primary w-100" onclick="event.stopPropagation(); viewStudentGoogleForm('${form.id}', '${studentId}')">
                                    <i class="bi bi-file-text me-1"></i>Take Assessment
                                </button>
                            </div>
                        `}
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Check if student has submitted a specific form
function checkStudentSubmission(formId, studentId) {
    return formSubmissions[formId] &&
        formSubmissions[formId].some(sub => sub.studentId === studentId);
}

// Get student's submission for a form
function getStudentSubmission(formId, studentId) {
    if (!formSubmissions[formId]) return null;
    return formSubmissions[formId].find(sub => sub.studentId === studentId);
}

// View Google Form for a specific student
function viewStudentGoogleForm(formId, studentId) {
    const form = googleFormsData.find(f => f.id === formId);
    if (!form) return;

    document.getElementById('viewFormTitle').textContent = form.title;
    document.getElementById('googleFormIframe').src = form.embedUrl;

    // Store current context
    window.currentGoogleFormId = formId;
    window.currentGoogleFormStudentId = studentId;
    window.currentGoogleFormPoints = form.points;

    const modal = new bootstrap.Modal(document.getElementById('viewGoogleFormModal'));
    modal.show();
}

// Show modal to assign Google Form to student
function showAddGoogleFormForStudent() {
    if (!currentViewingStudentId) {
        showNotification('Please select a student first', 'warning');
        return;
    }

    // Populate the form modal with student info
    const student = studentsData.find(s => s.id === currentViewingStudentId);
    if (student) {
        document.getElementById('assignFormStudentInfo').value = `${student.name} (${student.id})`;
        document.getElementById('assignFormStudentId').value = currentViewingStudentId;
    }

    const modal = new bootstrap.Modal(document.getElementById('assignGoogleFormModal'));
    modal.show();
}

// Assign Google Form to student
function assignGoogleFormToStudent() {
    const studentId = document.getElementById('assignFormStudentId').value;
    const formTitle = document.getElementById('assignFormTitle').value;
    const formCourse = document.getElementById('assignFormCourse').value;
    const formEmbedUrl = document.getElementById('assignFormEmbedUrl').value;
    const formDescription = document.getElementById('assignFormDescription').value;
    const formDueDate = document.getElementById('assignFormDueDate').value;
    const formPoints = document.getElementById('assignFormPoints').value;

    if (!formTitle || !formCourse || !formEmbedUrl) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }

    // Validate Google Form URL
    if (!formEmbedUrl.includes('docs.google.com/forms')) {
        showNotification('Please enter a valid Google Form URL', 'error');
        return;
    }

    const newForm = {
        id: 'form_' + Date.now(),
        title: formTitle,
        course: formCourse,
        embedUrl: formEmbedUrl,
        description: formDescription,
        assignedTo: [studentId],
        dueDate: formDueDate || new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
        points: parseInt(formPoints) || 100,
        createdAt: new Date().toISOString()
    };

    googleFormsData.push(newForm);
    saveGoogleFormsToStorage();

    // Refresh display for current student
    if (currentViewingStudentId) {
        displayStudentGoogleForms(currentViewingStudentId);
    }

    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('assignGoogleFormModal'));
    modal.hide();
    document.getElementById('assignGoogleForm').reset();

    showNotification(`Google Form "${formTitle}" assigned to student!`, 'success');
}

// Submit Google Form response for student
function submitGoogleFormResponse() {
    const formId = window.currentGoogleFormId;
    const studentId = window.currentGoogleFormStudentId;
    const form = googleFormsData.find(f => f.id === formId);

    if (!form || !studentId) return;

    // Check if already submitted
    if (checkStudentSubmission(formId, studentId)) {
        const allowResubmissions = localStorage.getItem('allowResubmissions') === 'true';
        if (!allowResubmissions) {
            showNotification('You have already submitted this form. Resubmissions are not allowed.', 'warning');
            return;
        }
    }

    // Record submission
    if (!formSubmissions[formId]) {
        formSubmissions[formId] = [];
    }

    const student = studentsData.find(s => s.id === studentId);
    const submission = {
        studentId: studentId,
        studentName: student ? student.name : 'Unknown',
        submittedAt: new Date().toISOString(),
        points: form.points,
        grade: form.points,
        formTitle: form.title
    };

    formSubmissions[formId].push(submission);
    saveSubmissionsToStorage();

    // Add to grades data
    gradesData.push({
        studentId: studentId,
        studentName: student ? student.name : 'Unknown',
        course: form.course,
        grade: form.points,
        courseId: 'form_' + formId,
        assessmentType: 'Google Form',
        assessmentName: form.title
    });

    // Animate success
    const modalContent = document.querySelector('#viewGoogleFormModal .modal-content');
    if (modalContent) {
        modalContent.classList.add('form-submitted-animation');
        setTimeout(() => {
            modalContent.classList.remove('form-submitted-animation');
        }, 500);
    }

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('viewGoogleFormModal'));
    if (modal) modal.hide();

    // Refresh displays
    if (currentViewingStudentId) {
        displayStudentGoogleForms(currentViewingStudentId);
    }
    if (document.getElementById('grades-page')?.classList.contains('active')) {
        displayGradesRanking();
    }

    showNotification(`Form "${form.title}" submitted successfully! Grade: ${form.points}/${form.points}`, 'success');
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make all functions globally available
window.initGoogleForms = initGoogleForms;
window.displayStudentGoogleForms = displayStudentGoogleForms;
window.viewStudentGoogleForm = viewStudentGoogleForm;
window.showAddGoogleFormForStudent = showAddGoogleFormForStudent;
window.assignGoogleFormToStudent = assignGoogleFormToStudent;
window.submitGoogleFormResponse = submitGoogleFormResponse;
