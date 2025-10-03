<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "student") {
    header("Location: index.php");
    exit();
}
include("db.php");

// Get student's user ID from session
$userID = $_SESSION['user_id'];

// Fetch StudentID
$stmt = $conn->prepare("SELECT StudentID FROM Student WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$studentID = $res['StudentID'];

// Fetch courses enrolled by this student
$sql = "SELECT c.CourseID, c.CourseName, b.BatchName 
        FROM Enrollment e
        JOIN Course c ON e.CourseID = c.CourseID
        LEFT JOIN Batch b ON c.BatchID = b.BatchID
        WHERE e.StudentID = ?";
$stmtCourses = $conn->prepare($sql);
$stmtCourses->bind_param("i", $studentID);
$stmtCourses->execute();
$courses = $stmtCourses->get_result();

// Fetch recent announcements relevant to the student (with course + batch + lecturer)
$sqlAnnDash = "
SELECT a.Title, a.Date, c.CourseName, b.BatchName, l.Name AS LecturerName
FROM Announcement AS a
INNER JOIN Course AS c ON a.CourseID = c.CourseID
INNER JOIN Enrollment AS e ON e.CourseID = c.CourseID
LEFT JOIN Batch b ON c.BatchID = b.BatchID
LEFT JOIN Lecturer l ON a.LecturerID = l.LecturerID
WHERE e.StudentID = ?
ORDER BY a.Date DESC
LIMIT 5";
$stmtAnnDash = $conn->prepare($sqlAnnDash);
$stmtAnnDash->bind_param("i", $studentID);
$stmtAnnDash->execute();
$recentAnnouncements = $stmtAnnDash->get_result();

// If a course is selected
$selectedCourseID = isset($_GET['courseID']) ? intval($_GET['courseID']) : null;
$materials = $announcements = null;

if ($selectedCourseID) {
    // Fetch lecture materials
    $sqlMat = "SELECT FileName, FileType, UploadDate, MaterialID 
               FROM LectureMaterial 
               WHERE CourseID = ?";
    $stmtMat = $conn->prepare($sqlMat);
    $stmtMat->bind_param("i", $selectedCourseID);
    $stmtMat->execute();
    $materials = $stmtMat->get_result();

    // Fetch announcements for the selected course
    $sqlAnn = "SELECT Title, Date FROM Announcement WHERE CourseID = ? ORDER BY Date DESC";
    $stmtAnn = $conn->prepare($sqlAnn);
    $stmtAnn->bind_param("i", $selectedCourseID);
    $stmtAnn->execute();
    $announcements = $stmtAnn->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>

  <link rel="stylesheet" href="style_student_dashboard.css">
  <link rel="stylesheet" href="student_sidebar.css">
  <link rel="stylesheet" href="student_header.css">

  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

  <?php include 'student_sidebar.php'; ?>
  <?php include 'student_header.php'; ?>

<main class="main-content">
  
  <!-- Dashboard (Now shows cards + recent announcements) -->
  <section id="dashboard" class="dashboard-section">
    <h2>Welcome to Your Dashboard</h2>
    <p>Quick overview of your learning activities.</p>
    
    <!-- Course Cards -->
    <div class="course-container">
      <?php if ($courses && $courses->num_rows > 0): 
        $courses->data_seek(0); // Reset pointer
        while ($row = $courses->fetch_assoc()): ?>
          <div class="course-card">
            <h3><?= htmlspecialchars($row['CourseName']) ?></h3>
            <p><strong>Batch:</strong> <?= htmlspecialchars($row['BatchName'] ?? '-') ?></p>
            <a href="?courseID=<?= $row['CourseID'] ?>" class="btn-view">View Details</a>
          </div>
      <?php endwhile; else: ?>
          <p>No enrolled courses.</p>
      <?php endif; ?>
    </div>

    <!-- Recent Announcements -->
    <h3 style="margin-top:30px;"> Recent Announcements</h3>
    <ul class="announcement-list">
      <?php if ($recentAnnouncements && $recentAnnouncements->num_rows > 0):
        while ($a = $recentAnnouncements->fetch_assoc()): ?>
          <li class="announcement-item">
            <span class="badge-course"><?= htmlspecialchars($a['CourseName']) ?></span>
            <?php if (!empty($a['BatchName'])): ?>
              <span class="badge-batch"><?= htmlspecialchars($a['BatchName']) ?></span>
            <?php endif; ?>
            <?php if (!empty($a['LecturerName'])): ?>
              <span class="badge-lecturer"> <?= htmlspecialchars($a['LecturerName']) ?></span>
            <?php endif; ?>
            <div class="announcement-text">
              <strong><?= htmlspecialchars($a['Title']) ?></strong> 
              <small>(<?= $a['Date'] ?>)</small>
            </div>
          </li>
      <?php endwhile; else: ?>
          <li>No announcements for your courses yet.</li>
      <?php endif; ?>
    </ul>
  </section>

  <!-- My Courses (kept same for sidebar link) -->
  <section id="courses" class="dashboard-section hidden">
    <h2>My Courses</h2>
    <div class="course-container">
      <?php 
      $stmtCourses->execute();
      $courses2 = $stmtCourses->get_result();
      if ($courses2 && $courses2->num_rows > 0): 
        while ($row = $courses2->fetch_assoc()): ?>
          <div class="course-card">
            <h3><?= htmlspecialchars($row['CourseName']) ?></h3>
            <p><strong>Batch:</strong> <?= htmlspecialchars($row['BatchName'] ?? '-') ?></p>
            <a href="?courseID=<?= $row['CourseID'] ?>" class="btn-view">View Details</a>
          </div>
      <?php endwhile; else: ?>
          <p>No enrolled courses.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Course Details -->
  <?php if ($selectedCourseID): ?>
  <section id="details" class="dashboard-section">
    <h2>Course Details</h2>
    <h3>Materials</h3>
    <ul>
      <?php if ($materials && $materials->num_rows > 0): 
        while ($m = $materials->fetch_assoc()): ?>
          <li>
            <?= htmlspecialchars($m['FileName']) ?> 
            (<?= htmlspecialchars($m['FileType']) ?>, <?= $m['UploadDate'] ?>)
            [<a href="download_material.php?id=<?= $m['MaterialID'] ?>">Download</a>]
          </li>
      <?php endwhile; else: ?>
          <li>No materials uploaded yet.</li>
      <?php endif; ?>
    </ul>

    <h3>Announcements</h3>
    <ul>
      <?php if ($announcements && $announcements->num_rows > 0): 
        while ($a = $announcements->fetch_assoc()): ?>
          <li>"<?= htmlspecialchars($a['Title']) ?>" (<?= $a['Date'] ?>)</li>
      <?php endwhile; else: ?>
          <li>No announcements yet.</li>
      <?php endif; ?>
    </ul>
  </section>
  <?php endif; ?>

  <!-- Calendar View -->
  <section id="announcements" class="dashboard-section hidden">
    <h2>Announcement Calendar</h2>
    <div class="calendar">
      <?php
      $month = date('m');
      $year = date('Y');
      $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

      echo "<table><tr>";
      $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
      foreach ($days as $d) echo "<th>$d</th>";
      echo "</tr><tr>";

      $firstDay = date('w', strtotime("$year-$month-01"));
      for ($i=0;$i<$firstDay;$i++) echo "<td></td>";

      for ($day=1;$day<=$daysInMonth;$day++) {
          $currentDate = "$year-$month-".str_pad($day,2,'0',STR_PAD_LEFT);
          echo "<td>$day</td>";
          if (date('w', strtotime($currentDate)) == 6) echo "</tr><tr>";
      }

      echo "</tr></table>";
      ?>
    </div>
  </section>
</main>

<script src="admin_dashboard.js"></script>
<script>
function showSection(sectionId) {
  document.querySelectorAll('.dashboard-section').forEach(section => {
    section.classList.add('hidden');
  });
  const targetSection = document.getElementById(sectionId);
  if (targetSection) {
    targetSection.classList.remove('hidden');
  }
  setActiveNav(sectionId);
}
</script>
</body>
</html>
