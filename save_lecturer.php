<?php
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['lecturerName'] ?? '');
    $nic = trim($_POST['lecturerNIC'] ?? '');
    $email = trim($_POST['lecturerEmail'] ?? '');
    $password = $_POST['lecturerPassword'] ?? '';

    if ($name === '' || strlen($name) > 100) {
        header("Location: admin_dashboard.php?error=invalid_name#lecturers");
        exit;
    }
    if (!preg_match('/^[0-9Vv]{10,12}$/', $nic)) {
        header("Location: admin_dashboard.php?error=invalid_nic#lecturers");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: admin_dashboard.php?error=invalid_email#lecturers");
        exit;
    }
    if (strlen($password) < 6) {
        header("Location: admin_dashboard.php?error=password_short#lecturers");
        exit;
    }

    // Check for duplicate email
    $checkEmail = $conn->prepare("SELECT UserID FROM UserAccount WHERE Email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $emailResult = $checkEmail->get_result();
    
    if ($emailResult->num_rows > 0) {
        header("Location: admin_dashboard.php?error=duplicate_email#lecturers");
        exit;
    }

    // Check for duplicate NIC
    $checkNIC = $conn->prepare("SELECT LecturerID FROM Lecturer WHERE NIC = ?");
    $checkNIC->bind_param("s", $nic);
    $checkNIC->execute();
    $nicResult = $checkNIC->get_result();
    
    if ($nicResult->num_rows > 0) {
        header("Location: admin_dashboard.php?error=duplicate_nic#lecturers");
        exit;
    }

    // Create user account with provided password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $roleID = $conn->query("SELECT RoleID FROM UserRole WHERE RoleName='lecturer'")->fetch_assoc()['RoleID'];

    $stmt = $conn->prepare("INSERT INTO UserAccount (Email, Password, RoleID, first_login) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("ssi", $email, $hashedPassword, $roleID);
    $stmt->execute();
    $userID = $stmt->insert_id;

    // Insert lecturer profile
    $stmt = $conn->prepare("INSERT INTO Lecturer (UserID, Name, NIC) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userID, $name, $nic);
    $stmt->execute();
}
header("Location: admin_dashboard.php#lecturers");
exit;
