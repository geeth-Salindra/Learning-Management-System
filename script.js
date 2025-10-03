/*document.addEventListener("DOMContentLoaded", function() {
  const form = document.getElementById("loginForm");
  
  form.addEventListener("submit", function(event) {
    const role = document.getElementById("role").value;
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!role || !email || !password) {
      alert("Please fill in all fields.");
      event.preventDefault();
    }
  });
});*/
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("loginForm");

  form.addEventListener("submit", function (event) {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    // Email validation (basic regex)
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!email || !emailPattern.test(email)) {
      alert("Please enter a valid email address.");
      event.preventDefault();
      return;
    }

    // Password validation
    if (!password || password.length < 6) {
      alert("Password must be at least 6 characters long.");
      event.preventDefault();
      return;
    }
  });
});
