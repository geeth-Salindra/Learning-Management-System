<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "lecturer") {
    header("Location: index.php");
    exit();
}
include("db.php");

// Get lecturer's user ID from session
$userID = $_SESSION['user_id'];

// Fetch LecturerID
$stmt = $conn->prepare("SELECT LecturerID FROM Lecturer WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$lecturerID = $res['LecturerID'];

// Fetch assigned courses
$sql = "SELECT c.CourseID, c.CourseName, b.BatchName 
        FROM Course c
        LEFT JOIN Batch b ON c.BatchID = b.BatchID
        WHERE c.LecturerID = ?";
$stmtCourses = $conn->prepare($sql);
$stmtCourses->bind_param("i", $lecturerID);
$stmtCourses->execute();
$courses = $stmtCourses->get_result();

// Fetch announcements
$sqlAnn = "SELECT a.Title, a.Date, c.CourseName
           FROM Announcement a
           JOIN Course c ON a.CourseID = c.CourseID
           WHERE a.LecturerID = ?
           ORDER BY a.Date DESC";
$stmtAnn = $conn->prepare($sqlAnn);
$stmtAnn->bind_param("i", $lecturerID);
$stmtAnn->execute();
$announcements = $stmtAnn->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Dashboard</title>
  <link rel="stylesheet" href="style_lecturer_dashboard.css">
  <link rel="stylesheet" href="lecturer_sidebar.css">
  <link rel="stylesheet" href="lecturer_header.css">
  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .sub-tabs { margin-top: 15px; display: flex; gap: 10px; }
    .sub-tabs button {
      padding: 8px 16px;
      cursor: pointer;
      border: none;
      background: #007bff;
      color: white;
      border-radius: 4px;
    }
    .sub-tabs button.active { background: #0056b3; }
    .sub-section { display: none; margin-top: 20px; }
    .sub-section.active { display: block; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f4f4f4; }
    .dashboard-section.hidden { display: none; }

    /* ===== Course Card Styles ===== */
    .course-cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 15px;
    }
    .course-card {
      background: #007bff;
      color: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      cursor: pointer;
      text-decoration: none;
      display: block;
    }
    .course-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.25);
      background: #0056b3;
    }
    .course-card h3 {
      margin: 0 0 10px;
      color: #fff;
    }
    .course-card p {
      margin: 0;
      font-size: 0.95rem;
      color: #e0e0e0;
    }
  </style>
</head>
<body>
  <?php include 'lecturer_sidebar.php'; ?>
  <?php include 'lecturer_header.php'; ?>

<main class="main-content">
  <!-- Dashboard Section -->
  <section id="stats" class="dashboard-section">
    <h2>Welcome to Lecturer Dashboard</h2>
    <p>Select an option from the sidebar.</p>

    <!-- My Courses inside Dashboard -->
    <h3 style="margin-top:20px;">My Courses</h3>
    <div class="course-cards">
      <?php if ($courses->num_rows > 0): 
        mysqli_data_seek($courses, 0); // reset pointer
        while ($row = $courses->fetch_assoc()): ?>
          <a class="course-card" href="course_details.php?courseID=<?= $row['CourseID'] ?>">
            <h3><?= htmlspecialchars($row['CourseName']) ?></h3>
            <p><strong>Batch:</strong> <?= htmlspecialchars($row['BatchName'] ?? '-') ?></p>
            <p><i class="bi bi-arrow-right-circle"></i> View Details</p>
          </a>
      <?php endwhile; else: ?>
          <p>No assigned courses.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Assigned Courses (standalone section still available) -->
  <section id="courses" class="dashboard-section hidden">
    <h2>Assigned Courses</h2>
    <div class="course-cards">
      <?php 
        $stmtCourses->execute();
        $coursesAgain = $stmtCourses->get_result();
        if ($coursesAgain->num_rows > 0): 
          while ($row = $coursesAgain->fetch_assoc()): ?>
            <a class="course-card" href="course_details.php?courseID=<?= $row['CourseID'] ?>">
              <h3><?= htmlspecialchars($row['CourseName']) ?></h3>
              <p><strong>Batch:</strong> <?= htmlspecialchars($row['BatchName'] ?? '-') ?></p>
              <p><i class="bi bi-arrow-right-circle"></i> View Details</p>
            </a>
        <?php endwhile; else: ?>
            <p>No assigned courses.</p>
        <?php endif; ?>
    </div>
  </section>

  <!-- Upload Lecture Materials -->
  <section id="materials" class="dashboard-section hidden">
    <h2>Upload Lecture Materials</h2>
    <form action="upload_material.php" method="POST" enctype="multipart/form-data">
      <select name="courseID" required>
        <option value="">Select Course</option>
        <?php
        $stmtCourses->execute();
        $courses2 = $stmtCourses->get_result();
        while ($c = $courses2->fetch_assoc()) {
            echo "<option value='{$c['CourseID']}'>{$c['CourseName']} ({$c['BatchName']})</option>";
        }
        ?>
      </select>
      <input type="file" name="materialFile" accept=".ppt,.pptx,.pdf,.doc,.docx" required>
      <button type="submit">Upload</button>
    </form>
  </section>

  <!-- Post Announcements -->
  <section id="announcements" class="dashboard-section hidden">
    <h2>Post Announcement</h2>
    <form action="post_announcement.php" method="POST">
      <select name="courseID" required>
        <option value="">Select Course</option>
        <?php
        $stmtCourses->execute();
        $courses3 = $stmtCourses->get_result();
        while ($c = $courses3->fetch_assoc()) {
            echo "<option value='{$c['CourseID']}'>{$c['CourseName']} ({$c['BatchName']})</option>";
        }
        ?>
      </select>
      <textarea name="message" placeholder="Enter your announcement" required></textarea>
      <button type="submit">Post</button>
    </form>

    <h3>My Announcements</h3>
    <table>
      <tr><th>Course</th><th>Message</th><th>Date</th></tr>
      <?php if ($announcements->num_rows > 0): 
        while ($a = $announcements->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($a['CourseName']) ?></td>
            <td><?= htmlspecialchars($a['Title']) ?></td>
            <td><?= $a['Date'] ?></td>
          </tr>
      <?php endwhile; else: ?>
          <tr><td colspan="3">No announcements yet.</td></tr>
      <?php endif; ?>
    </table>
  </section>
</main>

<script>
    function showSection(sectionId) {
      // Hide all sections
      document.querySelectorAll('.dashboard-section').forEach(section => {
        section.classList.add('hidden');
      });
      // Show selected
      const target = document.getElementById(sectionId);
      if (target) target.classList.remove('hidden');
      // Highlight active link
      setActiveNav(sectionId);
    }
</script>
</body>
</html>
