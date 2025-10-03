<header class="admin-header">
    <div class="header-left">
        <button class="hamburger-toggle" id="hamburgerToggle" onclick="toggleSidebar()" 
                aria-label="Toggle navigation menu" 
                aria-expanded="false"
                tabindex="0">
            <i class="bi bi-list hamburger-icon"></i>
        </button>
        <div class="logo">
            <i class="bi bi-shield-check logo-icon"></i>
            <span class="logo-text">LMS Students</span>
        </div>
    </div>
    
    <div class="header-right">
        <div class="user-info">
            <i class="bi bi-person-circle user-icon"></i>
            <span class="user-name"><?= htmlspecialchars($_SESSION['email'] ?? 'Student') ?></span>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="bi bi-box-arrow-right logout-icon"></i>
            <span class="logout-text">Logout</span>
        </a>
    </div>
</header>

<script>
function setActiveNav(sectionId) {
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeLink) activeLink.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    setActiveNav('dashboard'); // default open Dashboard
    showSection('dashboard');
});
</script>
