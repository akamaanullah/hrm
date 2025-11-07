<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="announcement-wrapper">
        <div class="announcement-header">
            <h1>Announcement</h1>
        </div>  
        <div class="announcement-timeline" id="announcementTimeline">
            <!-- Announcements will be loaded here by JS -->
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="include/js/userannouncement.js"></script>
<?php include 'footer.php'; ?>