9<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<style>
</style>
<nav class="sidebar" id="mainSidebar">
    <!-- Main Navigation -->
    <div class="nav-section">
        <ul class="nav flex-column gap-1">
                    <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'userdashboard.php') ? 'active' : ''; ?>"
                    href="userdashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'userattendance.php') ? 'active' : ''; ?>"
                    href="userattendance.php">
                    <i class="fas fa-fingerprint"></i>
                    <span>Daily Attendance </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'break.php') ? 'active' : ''; ?>"
                    href="break.php">
                    <i class="fas fa-pause-circle"></i>
                    <span>Break Time</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'leave-history.php') ? 'active' : ''; ?>"
                    href="leave-history.php">
                    <i class="fas fa-history"></i>
                    <span>Leave History</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'user-payroll-salary.php') ? 'active' : ''; ?>"
                    href="user-payroll-salary.php">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Payroll & Salary</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'userannouncement.php') ? 'active' : ''; ?>"
                    href="userannouncement.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcement</span>
                    <span class="badge bg-warning rounded-pill ms-2" id="announcementBadge" style="display:none;"></span>
                </a>
            </li>
        </ul>
</div>
</nav>