<?php
session_start();
if ($_SESSION['role'] !== "lecturer") { exit("Unauthorized"); }
include("db.php");

$userID = $_SESSION['user_id'];
$lecturerID = $conn->query("SELECT LecturerID FROM Lecturer WHERE UserID=$userID")->fetch_assoc()['LecturerID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseID = $_POST['courseID'];
    $file = $_FILES['materialFile'];

    $allowedTypes = ['ppt','pptx','pdf','doc','docx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowedTypes)) {
        $filename = time() . "_" . basename($file['name']);
        $target = "uploads/" . $filename;
        move_uploaded_file($file['tmp_name'], $target);

        // Save in DB
        $fileType = strtoupper($ext);
        if ($fileType == "PPTX") $fileType = "PPT";
        if ($fileType == "DOCX") $fileType = "WORD";
        if ($fileType == "DOC") $fileType = "WORD";

        $stmt = $conn->prepare("INSERT INTO LectureMaterial (FileName, FileType, CourseID, LecturerID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $filename, $fileType, $courseID, $lecturerID);
        $stmt->execute();
    }
}
header("Location: lecturer_dashboard.php#materials");
exit;
?>
