<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "127.0.0.1:3307";
$username = "root";
$password = "";
$dbname = "lms_db";

// 1) Connect to MySQL server
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected to MySQL server successfully!<br>";

// 2) Create database
if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
    die("Error creating database: " . $conn->error);
}
echo "Database '$dbname' ready.<br>";

// 3) Select database
$conn->select_db($dbname);

// 4) Create tables
$tables = [

    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'instructor') NOT NULL,
        batch VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "courses" => "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        progress INT DEFAULT 0,
        status ENUM('active', 'archived') DEFAULT 'active',
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "assignments" => "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        due_date DATE,
        status ENUM('pending', 'submitted', 'graded') DEFAULT 'pending',
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "grades" => "CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        assignment_id INT NULL,
        course_name VARCHAR(255),
        score DECIMAL(5,2) NOT NULL,
        date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE SET NULL
    ) ENGINE=InnoDB"
];

// Create tables
foreach ($tables as $name => $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table '$name' is ready.<br>";
    } else {
        echo "Error creating table '$name': " . $conn->error . "<br>";
    }
}

// ✅ Helper function to check column existence
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// 5) Safe migrations (ONLY if needed)

// users.batch
if (!columnExists($conn, 'users', 'batch')) {
    $conn->query("ALTER TABLE users ADD COLUMN batch VARCHAR(50) NULL");
}

// grades.assignment_id
if (!columnExists($conn, 'grades', 'assignment_id')) {
    $conn->query("ALTER TABLE grades ADD COLUMN assignment_id INT NULL");
}

// Modify safely
$conn->query("ALTER TABLE grades MODIFY score DECIMAL(5,2) NOT NULL");
$conn->query("ALTER TABLE courses MODIFY status ENUM('active', 'archived') DEFAULT 'active'");

// Add FK only if not exists
$fk_check = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'grades' 
    AND CONSTRAINT_NAME = 'fk_grades_assignment'
");

if ($fk_check->num_rows == 0) {
    $conn->query("
        ALTER TABLE grades
        ADD CONSTRAINT fk_grades_assignment
        FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE SET NULL
    ");
}

// Normalize data
$conn->query("UPDATE courses SET status='active' 
              WHERE status IS NULL OR status NOT IN ('active', 'archived')");

// 6) Seed users
$pass = password_hash("password123", PASSWORD_DEFAULT);
$check_users = $conn->query("SELECT id FROM users LIMIT 1");

if ($check_users && $check_users->num_rows == 0) {
    $conn->query("INSERT INTO users (username, email, password, role, batch) VALUES
    ('tsi', 'tsi@lms.com', '$pass', 'student', 'Batch 1'),
    ('tebarek', 'tebarek@lms.com', '$pass', 'instructor', NULL)");
    echo "Sample users created.<br>";
}

// 7) Seed courses
$check_courses = $conn->query("SELECT id FROM courses LIMIT 1");

if ($check_courses && $check_courses->num_rows == 0) {
    $res = $conn->query("SELECT id FROM users WHERE username='tebarek' LIMIT 1");
    $instr = $res ? $res->fetch_assoc() : null;

    if ($instr) {
        $iid = (int)$instr['id'];

        $conn->query("INSERT INTO courses (user_id, course_name, progress, status) VALUES
        ($iid, 'PHP Backend Development', 60, 'active'),
        ($iid, 'Database Management Systems', 35, 'active')");

        $cid = $conn->insert_id;

        $conn->query("INSERT INTO assignments (course_id, title, due_date, status) VALUES
        ($cid, 'Final PHP Project', '2026-12-01', 'pending')");

        echo "Sample instructor course data created.<br>";
    }
}

echo "<strong>Database setup complete!</strong>";
$conn->close();
?>