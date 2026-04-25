<?php
// ... standard db connection ...

if(isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $course_id = $_POST['course_id']; // From a dropdown of courses
    $due_date = $_POST['due_date'];

    $stmt = $conn->prepare("INSERT INTO assignments (title, course_id, due_date, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("sis", $title, $course_id, $due_date);
    
    if($stmt->execute()) {
        echo "Assignment added successfully!";
    }
}
?>

<form method="POST">
    <input type="text" name="title" placeholder="Assignment Title" required>
    <select name="course_id">
        <option value="1">GitHub Basics</option>
    </select>
    <input type="date" name="due_date" required>
    <button type="submit" name="add_task">Create Assignment</button>
</form>