<?php
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['studentName'] ?? '');
    $nic = trim($_POST['studentNIC'] ?? '');
    $email = trim($_POST['studentEmail'] ?? '');
    $batch = !empty($_POST['BatchID']) ? (int)$_POST['BatchID'] : NULL;
    $password = $_POST['studentPassword'] ?? '';

    if ($name === '' || strlen($name) > 100) {
        header("Location: admin_dashboard.php?error=invalid_name#students");
        exit;
    }
    if (!preg_match('/^[0-9Vv]{10,12}$/', $nic)) {
        header("Location: admin_dashboard.php?error=invalid_nic#students");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: admin_dashboard.php?error=invalid_email#students");
        exit;
    }
    if (strlen($password) < 6) {
        header("Location: admin_dashboard.php?error=password_short#students");
        exit;
    }

    // Check for duplicate email
    $checkEmail = $conn->prepare("SELECT UserID FROM UserAccount WHERE Email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $emailResult = $checkEmail->get_result();
    
    if ($emailResult->num_rows > 0) {
        header("Location: admin_dashboard.php?error=duplicate_email#students");
        exit;
    }

    // Check for duplicate NIC
    $checkNIC = $conn->prepare("SELECT StudentID FROM Student WHERE NIC = ?");
    $checkNIC->bind_param("s", $nic);
    $checkNIC->execute();
    $nicResult = $checkNIC->get_result();
    
    if ($nicResult->num_rows > 0) {
        header("Location: admin_dashboard.php?error=duplicate_nic#students");
        exit;
    }

    // Create user account
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $roleID = $conn->query("SELECT RoleID FROM UserRole WHERE RoleName='student'")->fetch_assoc()['RoleID'];

    $stmt = $conn->prepare("INSERT INTO UserAccount (Email, Password, RoleID, first_login) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("ssi", $email, $hashedPassword, $roleID);
    $stmt->execute();
    $userID = $stmt->insert_id;

    // Insert student profile
    $stmt = $conn->prepare("INSERT INTO Student (UserID, Name, NIC, BatchID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $userID, $name, $nic, $batch);
    $stmt->execute();
}
header("Location: admin_dashboard.php#students");
exit;
