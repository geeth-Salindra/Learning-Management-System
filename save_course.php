<?php
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['courseName'] ?? '');
    $batch = !empty($_POST['BatchID']) ? (int)$_POST['BatchID'] : NULL;
    $lecturer = !empty($_POST['LecturerID']) ? (int)$_POST['LecturerID'] : NULL;

    if ($name === '' || strlen($name) > 100) {
        header("Location: admin_dashboard.php?error=invalid_name#courses");
        exit;
    }
    
    // Validate batch and lecturer selection (matches frontend)
    if (empty($batch)) {
        header("Location: admin_dashboard.php?error=no_batch#courses");
        exit;
    }
    
    if (empty($lecturer)) {
        header("Location: admin_dashboard.php?error=no_lecturer#courses");
        exit;
    }

    // Check for duplicate course name (same course name cannot exist in system)
    $checkDuplicate = $conn->prepare("SELECT CourseID FROM Course WHERE CourseName = ?");
    $checkDuplicate->bind_param("s", $name);
    $checkDuplicate->execute();
    $duplicateResult = $checkDuplicate->get_result();
    
    if ($duplicateResult->num_rows > 0) {
        header("Location: admin_dashboard.php?error=duplicate_course#courses");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Course (CourseName, BatchID, LecturerID) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $name, $batch, $lecturer);
    $stmt->execute();
}
header("Location: admin_dashboard.php#courses");
exit;
