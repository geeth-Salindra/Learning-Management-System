<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: index.php");
    exit();
}
include("db.php");

// Fetch quick stats
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM Student")->fetch_assoc()['total'] ?? 0;
$totalLecturers = $conn->query("SELECT COUNT(*) AS total FROM Lecturer")->fetch_assoc()['total'] ?? 0;
$totalBatches = $conn->query("SELECT COUNT(*) AS total FROM Batch")->fetch_assoc()['total'] ?? 0;
$totalCourses = $conn->query("SELECT COUNT(*) AS total FROM Course")->fetch_assoc()['total'] ?? 0;

// Handle student search
$search = trim($_GET['q'] ?? '');
$sqlStudents = "SELECT s.StudentID, s.Name, s.NIC, u.Email, b.BatchName
                FROM Student s
                JOIN UserAccount u ON s.UserID = u.UserID
                LEFT JOIN Batch b ON s.BatchID = b.BatchID";
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $sqlStudents .= " WHERE s.Name LIKE '%$safeSearch%' OR s.NIC LIKE '%$safeSearch%' OR u.Email LIKE '%$safeSearch%'";
}
$studentResults = $conn->query($sqlStudents);

// Handle lecturer search
$lsearch = trim($_GET['lq'] ?? '');
$sqlLecturers = "SELECT l.LecturerID, l.Name, l.NIC, u.Email
                 FROM Lecturer l
                 JOIN UserAccount u ON l.UserID = u.UserID";
if ($lsearch !== '') {
    $safeLSearch = $conn->real_escape_string($lsearch);
    $sqlLecturers .= " WHERE l.Name LIKE '%$safeLSearch%' OR l.NIC LIKE '%$safeLSearch%' OR u.Email LIKE '%$safeLSearch%'";
}
$lecturerResults = $conn->query($sqlLecturers);

// Handle course search
$csearch = trim($_GET['cq'] ?? '');
$sqlCourses = "SELECT c.CourseID, c.CourseName, b.BatchName, l.Name AS LecturerName
               FROM Course c
               LEFT JOIN Batch b ON c.BatchID = b.BatchID
               LEFT JOIN Lecturer l ON c.LecturerID = l.LecturerID";
if ($csearch !== '') {
    $safeCSearch = $conn->real_escape_string($csearch);
    $sqlCourses .= " WHERE c.CourseName LIKE '%$safeCSearch%'";
}
$courseResults = $conn->query($sqlCourses);

// Handle batch search
$bsearch = trim($_GET['bq'] ?? '');
$sqlBatches = "SELECT BatchID, BatchCode, BatchName, BatchYear, BatchType, Description
               FROM Batch";
if ($bsearch !== '') {
    $safeBSearch = $conn->real_escape_string($bsearch);
    $sqlBatches .= " WHERE BatchName LIKE '%$safeBSearch%' OR BatchYear LIKE '%$safeBSearch%' OR BatchCode LIKE '%$safeBSearch%' OR Description LIKE '%$safeBSearch%'";
}
$sqlBatches .= " ORDER BY BatchYear DESC, BatchName ASC";
$batchResults = $conn->query($sqlBatches);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LMS - Admin Dashboard</title>
  <link rel="stylesheet" href="admin_dashboard.css">
  <link rel="stylesheet" href="admin_sidebar.css">
  <link rel="stylesheet" href="admin_header.css">
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

    /* Password field with toggle eye */
    .password-container {
      position: relative;
      display: inline-block;
      width: 100%;
    }
    
    .password-field {
      width: 96%;
      padding: 8px 40px 8px 8px;
      border: 1px solid #ddd;
      border-radius: 1px;
      font-size: 14px;
    }
    
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #666;
      font-size: 16px;
      padding: 0;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .password-toggle:hover {
      color: #333;
    }
    
    .password-toggle:focus {
      outline: none;
    }

  </style>
</head>
<body>
  <?php include 'admin_sidebar.php'; ?>
  <?php include 'admin_header.php'; ?>

  <main class="main-content">
    <!-- Quick Stats -->
    <section id="stats" class="dashboard-section">
      <h2>Quick Stats</h2>
      <div class="stats-container">
        <div class="card">Total Students: <span><?= $totalStudents ?></span></div>
        <div class="card">Total Lecturers: <span><?= $totalLecturers ?></span></div>
        <div class="card">Total Batches: <span><?= $totalBatches ?></span></div>
        <div class="card">Total Courses: <span><?= $totalCourses ?></span></div>
      </div>
    </section>

    <!-- Manage Batches -->
    <section id="batches" class="dashboard-section hidden">
      <h2>Manage Batches</h2>
      <div class="sub-tabs">
        <button type="button" onclick="showSubSection('addBatch')">Add Batch</button>
        <button type="button" class="active" onclick="showSubSection('searchBatchTab')">Search Batches</button>
      </div>
      <div id="addBatch" class="sub-section">
        <form method="POST" action="save_batch.php" id="addBatchForm">
          <input type="text" name="batchName" placeholder="Batch Name" required>
          <input type="number" name="batchYear" id="batchYear" placeholder="Batch Year (e.g., 2024)" min="2020" max="2030" required>
          <select name="batchType" id="batchType" required>
            <option value="">Select Batch Type</option>
            <option value="full time">Full Time</option>
            <option value="part time">Part Time</option>
          </select>
          <textarea name="batchDesc" placeholder="Description"></textarea>
          <button type="submit">Create Batch</button>
        </form>
      </div>
      <div id="searchBatchTab" class="sub-section active">
        <form method="GET" action="admin_dashboard.php">
          <input type="text" name="bq" placeholder="Search by Batch Name, Code, Year, or Description" value="<?= htmlspecialchars($bsearch) ?>">
          <button type="submit">Search</button>
        </form>
        <h3>Batch List</h3>
        <table>
          <tr><th>ID</th><th>Batch Code</th><th>Batch Name</th><th>Year</th><th>Type</th><th>Description</th></tr>
          <?php if ($batchResults && $batchResults->num_rows > 0): while ($row = $batchResults->fetch_assoc()): ?>
            <tr>
              <td><?= $row['BatchID'] ?></td>
              <td><strong><?= htmlspecialchars($row['BatchCode'] ?? 'N/A') ?></strong></td>
              <td><?= htmlspecialchars($row['BatchName']) ?></td>
              <td><?= htmlspecialchars($row['BatchYear'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars(ucfirst($row['BatchType'] ?? 'N/A')) ?></td>
              <td><?= htmlspecialchars($row['Description']) ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="6">No batches found.</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </section>

    <!-- Students -->
    <section id="students" class="dashboard-section hidden">
      <h2>Manage Students</h2>
      <div class="sub-tabs">
        <button type="button" onclick="showSubSection('addStudent')">Add Student</button>
        <button type="button" class="active" onclick="showSubSection('searchStudentTab')">Search Students</button>
      </div>
      <div id="addStudent" class="sub-section">
        <form method="POST" action="save_student.php">
          <input type="text" name="studentName" placeholder="Name" required>
          <input type="text" name="studentNIC" placeholder="NIC" required>
          <input type="email" name="studentEmail" placeholder="Email" required>
          <div class="password-container">
            <input type="password" name="studentPassword" class="password-field" placeholder="Password" required>
            <button type="button" class="password-toggle" onclick="togglePassword('studentPassword')">
              <i class="bi bi-eye" id="studentPassword-icon"></i>
            </button>
          </div>
          <select name="BatchID">
            <option value="">Select Batch</option>
            <?php $res = $conn->query("SELECT * FROM Batch");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['BatchID']}'>{$row['BatchCode']}</option>";
            } ?>
          </select>
          <button type="submit">Add Student</button>
        </form>
      </div>
      <div id="searchStudentTab" class="sub-section active">
        <form method="GET" action="admin_dashboard.php">
          <input type="text" name="q" placeholder="Search by Name, NIC, or Email" value="<?= htmlspecialchars($search) ?>">
          <button type="submit">Search</button>
        </form>
        <h3>Student List</h3>
        <table>
          <tr><th>ID</th><th>Name</th><th>NIC</th><th>Email</th><th>Batch</th></tr>
          <?php if ($studentResults && $studentResults->num_rows > 0): while ($row = $studentResults->fetch_assoc()): ?>
            <tr>
              <td><?= $row['StudentID'] ?></td>
              <td><?= htmlspecialchars($row['Name']) ?></td>
              <td><?= htmlspecialchars($row['NIC']) ?></td>
              <td><?= htmlspecialchars($row['Email']) ?></td>
              <td><?= htmlspecialchars($row['BatchName'] ?? '-') ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="5">No students found.</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </section>

    <!-- Lecturers -->
    <section id="lecturers" class="dashboard-section hidden">
      <h2>Manage Lecturers</h2>
      <div class="sub-tabs">
        <button type="button" onclick="showSubSection('addLecturer')">Add Lecturer</button>
        <button type="button" class="active" onclick="showSubSection('searchLecturerTab')">Search Lecturers</button>
      </div>
      <div id="addLecturer" class="sub-section">
        <form method="POST" action="save_lecturer.php">
          <input type="text" name="lecturerName" placeholder="Name" required>
          <input type="text" name="lecturerNIC" placeholder="NIC" required>
          <input type="email" name="lecturerEmail" placeholder="Email" required>
          <div class="password-container">
            <input type="password" name="lecturerPassword" class="password-field" placeholder="Password" required>
            <button type="button" class="password-toggle" onclick="togglePassword('lecturerPassword')">
              <i class="bi bi-eye" id="lecturerPassword-icon"></i>
            </button>
          </div>
          <button type="submit">Add Lecturer</button>
        </form>
      </div>
      <div id="searchLecturerTab" class="sub-section active">
        <form method="GET" action="admin_dashboard.php">
          <input type="text" name="lq" placeholder="Search by Name, NIC, or Email" value="<?= htmlspecialchars($lsearch) ?>">
          <button type="submit">Search</button>
        </form>
        <h3>Lecturer List</h3>
        <table>
          <tr><th>ID</th><th>Name</th><th>NIC</th><th>Email</th></tr>
          <?php if ($lecturerResults && $lecturerResults->num_rows > 0): while ($row = $lecturerResults->fetch_assoc()): ?>
            <tr>
              <td><?= $row['LecturerID'] ?></td>
              <td><?= htmlspecialchars($row['Name']) ?></td>
              <td><?= htmlspecialchars($row['NIC']) ?></td>
              <td><?= htmlspecialchars($row['Email']) ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="4">No lecturers found.</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </section>

    <!-- Courses -->
    <section id="courses" class="dashboard-section hidden">
      <h2>Manage Courses</h2>
      <div class="sub-tabs">
        <button type="button" onclick="showSubSection('addCourse')">Add Course</button>
        <button type="button" class="active" onclick="showSubSection('searchCourseTab')">Search Courses</button>
      </div>
      <div id="addCourse" class="sub-section">
        <form method="POST" action="save_course.php" id="addCourseForm">
          <input type="text" name="courseName" placeholder="Course Name" required>
          <select name="BatchID" id="batchSelect" required>
            <option value="">Select Batch</option>
            <?php $res = $conn->query("SELECT * FROM Batch");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['BatchID']}'>{$row['BatchCode']}</option>";
            } ?>
          </select>
          <select name="LecturerID" id="lecturerSelect" required>
            <option value="">Select Lecturer</option>
            <?php $res = $conn->query("SELECT * FROM Lecturer");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['LecturerID']}'>{$row['Name']}</option>";
            } ?>
          </select>
          <button type="submit">Create Course</button>
        </form>
      </div>
      <div id="searchCourseTab" class="sub-section active">
        <form method="GET" action="admin_dashboard.php">
          <input type="text" name="cq" placeholder="Search by Course Name" value="<?= htmlspecialchars($csearch) ?>">
          <button type="submit">Search</button>
        </form>
        <h3>Course List</h3>
        <table>
          <tr><th>ID</th><th>Course Name</th><th>Batch</th><th>Lecturer</th></tr>
          <?php if ($courseResults && $courseResults->num_rows > 0): while ($row = $courseResults->fetch_assoc()): ?>
            <tr>
              <td><?= $row['CourseID'] ?></td>
              <td><?= htmlspecialchars($row['CourseName']) ?></td>
              <td><?= htmlspecialchars($row['BatchName'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['LecturerName'] ?? '-') ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="4">No courses found.</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </section>
  </main>


  <script src="admin_dashboard.js"></script>
  <script>

    document.addEventListener("DOMContentLoaded", function () {
      console.log("Page loaded, current URL:", window.location.href); // Debug log
      const urlParams = new URLSearchParams(window.location.search);
      console.log("URL search params:", window.location.search); // Debug log
      console.log("URL hash:", window.location.hash); // Debug log
      
      if (urlParams.has("q")) { showSection('students'); showSubSection('searchStudentTab'); }
      if (urlParams.has("lq")) { showSection('lecturers'); showSubSection('searchLecturerTab'); }
      if (urlParams.has("cq")) { showSection('courses'); showSubSection('searchCourseTab'); }
      if (urlParams.has("bq")) { showSection('batches'); showSubSection('searchBatchTab'); }
      
      // Handle error messages
      console.log("Checking for error parameter..."); // Debug log
      
      // Check for error in URL parameters
      let error = null;
      if (urlParams.has("error")) {
        error = urlParams.get("error");
        console.log("Error parameter found in search params:", error); // Debug log
      }
      
      // Also check hash for error parameter (fallback)
      if (!error && window.location.hash.includes('error=')) {
        const hashParams = new URLSearchParams(window.location.hash.split('#')[1]);
        if (hashParams.has("error")) {
          error = hashParams.get("error");
          console.log("Error parameter found in hash:", error); // Debug log
        }
      }
      
      if (error) {
        
        let errorMessage = "";
        
        // Batch errors
        if (error === "duplicate") {
          errorMessage = "Error: A batch with the same name, year, and type already exists. Please choose different values.";
        }
        else if (error === "invalid_year") {
          errorMessage = "Error: Invalid batch year. Must be between 2020 and 2030.";
        }
        else if (error === "invalid_type") {
          errorMessage = "Error: Invalid batch type. Must be 'full time' or 'part time'.";
        }
        else if (error === "description_long") {
          errorMessage = "Error: Description too long. Maximum 255 characters allowed.";
        }
        
        // Course errors
        else if (error === "invalid_name") {
          errorMessage = "Error: Invalid course name. Please enter a valid course name.";
        }
        else if (error === "no_batch") {
          errorMessage = "Error: Please select a Batch.";
        }
        else if (error === "no_lecturer") {
          errorMessage = "Error: Please select a Lecturer.";
        }
        else if (error === "duplicate_course") {
          errorMessage = "Error: A course with this name already exists. Please choose a different course name.";
        }
        
        // Lecturer errors
        else if (error === "invalid_nic") {
          errorMessage = "Error: Invalid NIC format. Please enter a valid NIC (10-12 digits).";
        }
        else if (error === "invalid_email") {
          errorMessage = "Error: Invalid email format. Please enter a valid email address.";
        }
        else if (error === "duplicate_nic") {
          errorMessage = "Error: This NIC is already registered. Please use a different NIC.";
        }
        
        // Student errors
        else if (error === "password_short") {
          errorMessage = "Error: Password too short. Password must be at least 6 characters long.";
        }
        else if (error === "duplicate_email") {
          errorMessage = "Error: This email address is already registered. Please use a different email.";
        }
        else if (error === "duplicate_nic") {
          errorMessage = "Error: This NIC is already registered. Please use a different NIC.";
        }
        
        // Show alert if we have an error message
        if (errorMessage) {
          console.log("Showing alert:", errorMessage); // Debug log
          alert(errorMessage);
          
          // Clean up URL by removing error parameter but keeping hash
          const hash = window.location.hash;
          const newUrl = window.location.pathname + hash;
          window.history.replaceState({}, document.title, newUrl);
        } else {
          console.log("No error message found for:", error); // Debug log
        }
      } else {
        console.log("No error parameter found in URL"); // Debug log
      }

      // Add course form validation
      const addCourseForm = document.getElementById('addCourseForm');
      if (addCourseForm) {
        addCourseForm.addEventListener('submit', function(e) {
          const courseName = document.querySelector('input[name="courseName"]');
          const batchSelect = document.getElementById('batchSelect');
          const lecturerSelect = document.getElementById('lecturerSelect');
          
          // Validate course name (matches backend)
          if (!courseName.value.trim() || courseName.value.trim().length > 100) {
            alert('Error: Invalid course name. Please enter a valid course name.');
            e.preventDefault();
            return false;
          }
          
          if (batchSelect.value === '') {
            alert('Error: Please select a Batch.');
            e.preventDefault();
            return false;
          }
          
          if (lecturerSelect.value === '') {
            alert('Error: Please select a Lecturer.');
            e.preventDefault();
            return false;
          }
          
          // Check for potential duplicate course name (client-side warning)
          const existingCourseNames = <?php 
            $courseCheck = $conn->query("SELECT CourseName FROM Course");
            $courseNames = [];
            while ($row = $courseCheck->fetch_assoc()) {
              $courseNames[] = $row['CourseName'];
            }
            echo json_encode($courseNames);
          ?>;
          
          const isDuplicate = existingCourseNames.some(name => 
            name.toLowerCase() === courseName.value.trim().toLowerCase()
          );
          
          if (isDuplicate) {
            const confirmed = confirm('Warning: A course with this name already exists. Are you sure you want to proceed?');
            if (!confirmed) {
              e.preventDefault();
              return false;
            }
          }
        });
      }

      // Add batch form validation
      const addBatchForm = document.getElementById('addBatchForm');
      if (addBatchForm) {
        addBatchForm.addEventListener('submit', function(e) {
          const batchName = document.querySelector('input[name="batchName"]');
          const batchYear = document.getElementById('batchYear');
          const batchType = document.getElementById('batchType');
          
          // Validate batch name
          if (!batchName.value.trim() || batchName.value.trim().length > 100) {
            alert('Error: Invalid batch name. Please enter a valid batch name.');
            e.preventDefault();
            return false;
          }
          
          // Validate batch year
          if (!batchYear.value || batchYear.value.length !== 4) {
            alert('Error: Please enter a valid 4-digit year (e.g., 2024).');
            e.preventDefault();
            return false;
          }
          
          const year = parseInt(batchYear.value);
          if (year < 2020 || year > 2030) {
            alert('Error: Batch year must be between 2020 and 2030.');
            e.preventDefault();
            return false;
          }
          
          // Validate batch type
          if (batchType.value === '') {
            alert('Error: Please select a Batch Type.');
            e.preventDefault();
            return false;
          }
          
          // Check for potential duplicate (client-side warning)
          const existingBatches = <?php echo json_encode($batchResults ? $batchResults->fetch_all(MYSQLI_ASSOC) : []); ?>;
          const isDuplicate = existingBatches.some(batch => 
            batch.BatchName.toLowerCase() === batchName.value.trim().toLowerCase() &&
            batch.BatchYear == year &&
            batch.BatchType === batchType.value
          );
          
          if (isDuplicate) {
            const confirmed = confirm('Warning: A batch with the same name, year, and type already exists. Are you sure you want to proceed?');
            if (!confirmed) {
              e.preventDefault();
              return false;
            }
          }
        });
      }

      // Add lecturer form validation
      const addLecturerForm = document.querySelector('form[action="save_lecturer.php"]');
      if (addLecturerForm) {
        addLecturerForm.addEventListener('submit', function(e) {
          const lecturerName = document.querySelector('input[name="lecturerName"]');
          const lecturerNIC = document.querySelector('input[name="lecturerNIC"]');
          const lecturerEmail = document.querySelector('input[name="lecturerEmail"]');
          
          // Validate lecturer name (matches backend)
          if (!lecturerName.value.trim() || lecturerName.value.trim().length > 100) {
            alert('Error: Invalid name. Please enter a valid name.');
            e.preventDefault();
            return false;
          }
          
          // Validate NIC (matches backend)
          if (!lecturerNIC.value.trim() || !/^[0-9Vv]{10,12}$/.test(lecturerNIC.value.trim())) {
            alert('Error: Invalid NIC format. Please enter a valid NIC (10-12 digits).');
            e.preventDefault();
            return false;
          }
          
          // Validate email (matches backend)
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!lecturerEmail.value.trim() || !emailPattern.test(lecturerEmail.value.trim())) {
            alert('Error: Invalid email format. Please enter a valid email address.');
            e.preventDefault();
            return false;
          }
          
          // Check for duplicate email (client-side warning)
          const existingEmails = <?php 
            $emailCheck = $conn->query("SELECT Email FROM UserAccount");
            $emails = [];
            while ($row = $emailCheck->fetch_assoc()) {
              $emails[] = $row['Email'];
            }
            echo json_encode($emails);
          ?>;
          
          if (existingEmails.includes(lecturerEmail.value.trim().toLowerCase())) {
            const confirmed = confirm('Warning: This email address is already registered. Are you sure you want to proceed?');
            if (!confirmed) {
              e.preventDefault();
              return false;
            }
          }
          
          // Check for duplicate NIC (client-side warning)
          const existingLecturerNICs = <?php 
            $nicCheck = $conn->query("SELECT NIC FROM Lecturer");
            $nics = [];
            while ($row = $nicCheck->fetch_assoc()) {
              $nics[] = $row['NIC'];
            }
            echo json_encode($nics);
          ?>;
          
          if (existingLecturerNICs.includes(lecturerNIC.value.trim())) {
            const confirmed = confirm('Warning: This NIC is already registered. Are you sure you want to proceed?');
            if (!confirmed) {
              e.preventDefault();
              return false;
            }
          }
        });
      }

      // Add student form validation
      const addStudentForm = document.querySelector('form[action="save_student.php"]');
      if (addStudentForm) {
        addStudentForm.addEventListener('submit', function(e) {
          const studentName = document.querySelector('input[name="studentName"]');
          const studentNIC = document.querySelector('input[name="studentNIC"]');
          const studentEmail = document.querySelector('input[name="studentEmail"]');
          const studentPassword = document.querySelector('input[name="studentPassword"]');
          
          // Validate student name (matches backend)
          if (!studentName.value.trim() || studentName.value.trim().length > 100) {
            alert('Error: Invalid name. Please enter a valid name.');
            e.preventDefault();
            return false;
          }
          
          // Validate NIC (matches backend)
          if (!studentNIC.value.trim() || !/^[0-9Vv]{10,12}$/.test(studentNIC.value.trim())) {
            alert('Error: Invalid NIC format. Please enter a valid NIC (10-12 digits).');
            e.preventDefault();
            return false;
          }
          
          // Validate email (matches backend)
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!studentEmail.value.trim() || !emailPattern.test(studentEmail.value.trim())) {
            alert('Error: Invalid email format. Please enter a valid email address.');
            e.preventDefault();
            return false;
          }
          
          // Check for duplicate email (client-side warning)
          if (existingEmails.includes(studentEmail.value.trim().toLowerCase())) {
            const confirmed = confirm('Warning: This email address is already registered. Are you sure you want to proceed?');
            if (!confirmed) {
              e.preventDefault();
              return false;
            }
          }
          
          // Check for duplicate NIC (client-side warning)
          const existingStudentNICs = <?php 
            $nicCheck = $conn->query("SELECT NIC FROM Student");
            $nics = [];
            while ($row = $nicCheck->fetch_assoc()) {
              $nics[] = $row['NIC'];
            }
            echo json_encode($nics);
          ?>;
          
          if (existingStudentNICs.includes(studentNIC.value.trim())) {
            const confirmed = confirm('Warning: This NIC is already registered. Are you sure you want to proceed?');
            if (!confirmed) {
              e.preventDefault();
              return false;
            }
          }
          
          // Validate password (matches backend)
          if (!studentPassword.value || studentPassword.value.length < 6) {
            alert('Error: Password too short. Password must be at least 6 characters long.');
            e.preventDefault();
            return false;
          }
        });
      }
    });

    function showSubSection(id) {
      document.querySelectorAll('.sub-section').forEach(sec => sec.classList.remove('active'));
      document.querySelectorAll('.sub-tabs button').forEach(btn => btn.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      event.target.classList.add('active');
    }

    // Update the showSection function to work with the header component
    function showSection(sectionId) {
      // Hide all sections
      document.querySelectorAll('.dashboard-section').forEach(section => {
        section.classList.add('hidden');
      });
      
      // Show selected section
      const targetSection = document.getElementById(sectionId);
      if (targetSection) {
        targetSection.classList.remove('hidden');
      }
      
      // Update active navigation in header component
      setActiveNav(sectionId);
    }

    // Password toggle function
    function togglePassword(fieldId) {
      const passwordField = document.querySelector(`input[name="${fieldId}"]`);
      const toggleIcon = document.getElementById(`${fieldId}-icon`);
      
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
      } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
      }
    }
  </script>
</body>
</html>
