<?php
session_start();
include("db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

// Check if this is a first-time login
if (!isset($_SESSION['first_login']) || $_SESSION['first_login'] != 1) {
    // Redirect to appropriate dashboard
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'lecturer') {
        header("Location: lecturer_dashboard.php");
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: student_dashboard.php");
    }
    exit;
}

$error_message = '';
$success_message = '';

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT Password FROM UserAccount WHERE UserID = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($current_password, $user['Password'])) {
                // Current password is correct, update to new password
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
                
                $update_stmt = $conn->prepare("UPDATE UserAccount SET Password = ?, first_login = 0 WHERE UserID = ?");
                $update_stmt->bind_param("si", $hashed_new_password, $_SESSION['user_id']);
                
                if ($update_stmt->execute()) {
                    // Password updated successfully
                    $success_message = "Password updated successfully! You will be redirected to login page.";
                    
                    // Destroy session and redirect to login after 3 seconds
                    session_destroy();
                    header("refresh:3;url=index.php");
                } else {
                    $error_message = "Failed to update password. Please try again.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        } else {
            $error_message = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password - LMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #002147, #0056b3);
            color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Header */
        header {
            background: #001f3f;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
        }
        
        header .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        header .logo img {
            height: 50px;
        }
        
        header nav a {
            margin: 0 10px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        
        header nav a:hover {
            text-decoration: underline;
        }
        
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .password-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .password-header h2 {
            color: #fff;
            margin: 0;
            font-size: 24px;
        }
        
        .password-header p {
            color: #ddd;
            margin: 10px 0 0 0;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin: 10px 0 5px;
            font-size: 14px;
            color: #fff;
        }
        
        .password-field {
            width: 100%;
            padding: 10px 40px 10px 10px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            background: #f9f9f9;
            color: #333;
            transition: background-color 0.3s;
            box-sizing: border-box;
        }
        
        .password-field:focus {
            outline: none;
            background: #fff;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
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
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #0056b3;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #003d80;
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: rgba(248, 215, 218, 0.9);
            color: #721c24;
            border: 1px solid rgba(245, 198, 203, 0.5);
        }
        
        .alert-success {
            background-color: rgba(212, 237, 218, 0.9);
            color: #155724;
            border: 1px solid rgba(195, 230, 203, 0.5);
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .user-info strong {
            color: #fff;
        }
        
        .user-info small {
            color: #ddd;
        }
        
        footer {
            background: #001f3f;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <h1>Learning Management System</h1>
        </div>
        <nav>
            <a href="#">Home</a>
            <a href="#">About</a>
            <a href="#">Guide</a>
            <a href="#">Support</a>
            <a href="#">Exams</a>
        </nav>
    </header>

    <main>
        <div class="password-container">
        <div class="password-header">
            <h2><i class="bi bi-shield-lock"></i> Update Password</h2>
            <p>This is your first login. Please update your password to continue.</p>
        </div>
        
        <div class="user-info">
            <strong>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>!</strong><br>
            <small>Role: <?= ucfirst($_SESSION['role'] ?? 'User') ?></small>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php else: ?>
            <form method="POST" action="update_password.php">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div style="position: relative;">
                        <input type="password" name="current_password" id="current_password" class="password-field" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                            <i class="bi bi-eye" id="current_password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" id="new_password" class="password-field" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="bi bi-eye" id="new_password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div style="position: relative;">
                        <input type="password" name="confirm_password" id="confirm_password" class="password-field" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="bi bi-eye" id="confirm_password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="bi bi-key"></i> Update Password
                </button>
            </form>
        <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> LMS | Research Prototype</p>
    </footer>
    
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
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
        
        // Auto-focus on current password field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('current_password').focus();
        });
    </script>
</body>
</html>
