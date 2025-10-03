<?php
session_start();
if ($_SESSION['role'] !== "lecturer") { exit("Unauthorized"); }
include("db.php");

$userID = $_SESSION['user_id'];
$lecturerID = $conn->query("SELECT LecturerID FROM Lecturer WHERE UserID=$userID")->fetch_assoc()['LecturerID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseID = $_POST['courseID'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO Announcement (Title, CourseID, LecturerID) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $message, $courseID, $lecturerID);
    $stmt->execute();
}
header("Location: lecturer_dashboard.php#announcements");
exit;
?>
