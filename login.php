<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT UA.UserID, UA.Email, UA.Password, UA.first_login, UR.RoleName
            FROM UserAccount UA
            INNER JOIN UserRole UR ON UA.RoleID = UR.RoleID
            WHERE UA.Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        //  Secure password verification
        if (password_verify($password, $row['Password'])) {
            $_SESSION['role'] = strtolower($row['RoleName']);
            $_SESSION['email'] = $row['Email'];
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['first_login'] = $row['first_login'];
            
            // Get user name for display
            $userName = '';
            if ($_SESSION['role'] === 'student') {
                $nameStmt = $conn->prepare("SELECT Name FROM Student WHERE UserID = ?");
                $nameStmt->bind_param("i", $_SESSION['user_id']);
                $nameStmt->execute();
                $nameResult = $nameStmt->get_result();
                if ($nameResult->num_rows > 0) {
                    $userName = $nameResult->fetch_assoc()['Name'];
                }
            } elseif ($_SESSION['role'] === 'lecturer') {
                $nameStmt = $conn->prepare("SELECT Name FROM Lecturer WHERE UserID = ?");
                $nameStmt->bind_param("i", $_SESSION['user_id']);
                $nameStmt->execute();
                $nameResult = $nameStmt->get_result();
                if ($nameResult->num_rows > 0) {
                    $userName = $nameResult->fetch_assoc()['Name'];
                }
            } elseif ($_SESSION['role'] === 'admin') {
                $userName = 'Administrator';
            }
            
            $_SESSION['name'] = $userName;

            // Check if this is first login
            if ($row['first_login'] == 1) {
                // Redirect to password update page
                header("Location: update_password.php");
                exit();
            } else {
                // Redirect based on role
                switch ($_SESSION['role']) {
                    case "admin":
                        header("Location: admin_dashboard.php");
                        exit();
                    case "lecturer":
                        header("Location: lecturer_dashboard.php");
                        exit();
                    case "student":
                        header("Location: student_dashboard.php");
                        exit();
                    default:
                        echo " Unknown role. Contact administrator.";
                }
            }
        } else {
            header("Location: index.php?error=wrong_password");
            exit();
        }
    } else {
        header("Location: index.php?error=no_account");
        exit();
    }
}
?>
