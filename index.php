<?php
session_start();
if (isset($_SESSION['role'])) {
    // Already logged in → redirect
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
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LMS - Login</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
    <section class="login-container">
      <h2>Login</h2>

      <form action="login.php" method="POST" id="loginForm" novalidate>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required>

        <label for="password">Password</label>
        <div style="position: relative;">
            <input type="password" name="password" id="password" placeholder="Enter your password" required minlength="6">
            <button type="button" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 16px; padding: 0; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;" onclick="togglePassword('password')">
                <i class="bi bi-eye" id="password-icon"></i>
            </button>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
          <div class="error-message" style="color: #ff6b6b; font-size: 14px; margin-top: 5px; margin-bottom: 15px; text-align: center;">
            <?php if ($_GET['error'] === 'wrong_password'): ?>
              <i class="bi bi-exclamation-triangle"></i> Wrong password. Please try again.
            <?php elseif ($_GET['error'] === 'no_account'): ?>
              <i class="bi bi-exclamation-triangle"></i> No account found with this email.
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <button type="submit">Login</button>
      </form>

      <p class="note">Lecturers/Students will receive a temporary password for first-time login and will be required to change it.</p>
    </section>
  </main>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> LMS | Research Prototype</p>
  </footer>

  <script src="script.js"></script>
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
  </script>
</body>
</html>
