<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="bi bi-shield-check logo-icon"></i>
            <span class="logo-text">Lecturer Panel</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="#" onclick="showSection('stats')" class="nav-link" data-section="stats">
                    <i class="bi bi-speedometer2 nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" onclick="showSection('courses')" class="nav-link" data-section="courses">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-text">My Courses</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" onclick="showSection('materials')" class="nav-link" data-section="materials">
                    <i class="bi bi-mortarboard nav-icon"></i>
                    <span class="nav-text">Materials</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" onclick="showSection('announcements')" class="nav-link" data-section="announcements">
                    <i class="bi bi-person-badge nav-icon"></i>
                    <span class="nav-text">Announcements</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($_SESSION['email'] ?? 'Lecturer') ?></div>
                <div class="user-role">Lecturer</div>
            </div>
        </div>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<script>
// Toggle sidebar function
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.querySelector('.main-content');
    const hamburgerToggle = document.getElementById('hamburgerToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth > 768) {
        // Desktop behavior
        if (sidebar.classList.contains('collapsed')) {
            // Show sidebar
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('collapsed');
            hamburgerToggle.classList.add('open');
        } else {
            // Hide sidebar
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            hamburgerToggle.classList.remove('open');
        }
    } else {
        // Mobile behavior
        if (sidebar.classList.contains('open')) {
            // Hide sidebar
            sidebar.classList.remove('open');
            mainContent.classList.remove('expanded');
            hamburgerToggle.classList.remove('open');
            overlay.classList.remove('show');
        } else {
            // Show sidebar
            sidebar.classList.add('open');
            mainContent.classList.add('expanded');
            hamburgerToggle.classList.add('open');
            overlay.classList.add('show');
        }
    }
    
    // Update hamburger icon with smooth transition
    const hamburgerIcon = hamburgerToggle.querySelector('.hamburger-icon');
    const isOpen = (window.innerWidth > 768) ? !sidebar.classList.contains('collapsed') : sidebar.classList.contains('open');
    
    if (isOpen) {
        hamburgerIcon.className = 'bi bi-x hamburger-icon';
        hamburgerToggle.setAttribute('aria-expanded', 'true');
        hamburgerToggle.setAttribute('aria-label', 'Close navigation menu');
    } else {
        hamburgerIcon.className = 'bi bi-list hamburger-icon';
        hamburgerToggle.setAttribute('aria-expanded', 'false');
        hamburgerToggle.setAttribute('aria-label', 'Open navigation menu');
    }
}

// Set active navigation link
function setActiveNav(sectionId) {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Initialize active state based on URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('q')) {
        setActiveNav('students');
    } else if (urlParams.has('lq')) {
        setActiveNav('lecturers');
    } else if (urlParams.has('cq')) {
        setActiveNav('courses');
    } else {
        setActiveNav('stats');
    }
    
    // Initialize sidebar state - open by default on desktop, hidden on mobile
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.querySelector('.main-content');
    const hamburgerToggle = document.getElementById('hamburgerToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth > 768) {
        // Desktop: sidebar visible by default
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
        overlay.classList.remove('show');
        hamburgerToggle.classList.add('open');
        hamburgerToggle.querySelector('.hamburger-icon').className = 'bi bi-x hamburger-icon';
        hamburgerToggle.setAttribute('aria-expanded', 'true');
        hamburgerToggle.setAttribute('aria-label', 'Close navigation menu');
    } else {
        // Mobile: sidebar hidden by default
        sidebar.classList.remove('open');
        mainContent.classList.remove('expanded');
        overlay.classList.remove('show');
        hamburgerToggle.classList.remove('open');
        hamburgerToggle.querySelector('.hamburger-icon').className = 'bi bi-list hamburger-icon';
        hamburgerToggle.setAttribute('aria-expanded', 'false');
        hamburgerToggle.setAttribute('aria-label', 'Open navigation menu');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth > 768) {
        // Desktop: hide overlay
        overlay.classList.remove('show');
    } else {
        // Mobile: show overlay if sidebar is open
        if (sidebar.classList.contains('open')) {
            overlay.classList.add('show');
        }
    }
});

// Keyboard support
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar.classList.contains('open')) {
            toggleSidebar();
        }
    }
});
</script>


<script>
function setActiveNav(sectionId) {
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeLink) activeLink.classList.add('active');
}
</script>
