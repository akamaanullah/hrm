<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav class="sidebar" id="mainSidebar">
    <!-- Main Navigation -->
    <div class="nav-section">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard Overview</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>"
                    href="attendance.php">
                    <i class="fas fa-fingerprint"></i>
                    <span>Attendance & Time</span>
                </a>
            </li>
            <li class="nav-item drop dropdown-container">
                <a class="nav-link <?php echo (in_array($current_page, ['Applied-Leave.php', 'leave-requests.php'])) ? 'active' : ''; ?>"
                    data-bs-toggle="collapse"
                    href="#leaveSubmenu"
                    role="button"
                    aria-expanded="false"
                    aria-controls="leaveSubmenu">
                    <i class="fas fa-calendar-check"></i>
                    <span>Leave Management</span>
                    <span class="badge bg-warning rounded-pill ms-auto" id="leaveRequestsBadge" style="display:none;"></span>
                    <i class="fas fa-chevron-down ms-2 dropdown-icon"></i>
                </a>
                <div class="collapse submenu" id="leaveSubmenu">
                    <ul class="nav flex-column">
                    <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'leave-requests.php') ? 'active' : ''; ?>" href="leave-requests.php">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Leave Requests</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'Applied-Leave.php') ? 'active' : ''; ?>" href="Applied-Leave.php">
                                <i class="fas fa-file-signature"></i>
                                <span>Leave History</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'payroll.php') ? 'active' : ''; ?>" href="payroll.php">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Payroll & Salary</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'all-employee.php') ? 'active' : ''; ?>" href="all-employee.php">
                    <i class="fas fa-user-tie"></i>
                    <span>All Employees</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'admin-annoucement.php') ? 'active' : ''; ?>" href="admin-annoucement.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcement</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="badge bg-danger rounded-pill ms-auto" id="messagesBadge" style="display:none;"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'joining-form.php') ? 'active' : ''; ?>" href="joining-form.php">
                    <i class="fas fa-user-plus"></i>
                    <span>Joining Form</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
<script>
    // Function to request notification permission
    function requestNotificationPermission() {
        if ('Notification' in window) {
            Notification.requestPermission().then(function(permission) {
            });
        }
    }
    // Function to show toast message
    function showToast(message, isSuccess) {
        // Create toast if it doesn't exist
        if ($('#notificationToast').length === 0) {
            $('body').append(`
            <div class="toast-container position-fixed top-0 end-0 p-3">
                <div id="notificationToast" class="toast" role="alert">
                    <div class="toast-header">
                        <strong class="me-auto">Notification</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body" id="notificationToastBody"></div>
                </div>
            </div>
        `);
        }
        $('#notificationToastBody').text(message);
        const toast = new bootstrap.Toast(document.getElementById('notificationToast'));
        toast.show();
    }
    // Function to show browser notification
    function showBrowserNotification(title, body, icon = null) {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                new Notification(title, {
                    body: body,
                    icon: icon || '../assets/images/LOGO.png'
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        new Notification(title, {
                            body: body,
                            icon: icon || '../assets/images/LOGO.png'
                        });
                    }
                });
            }
        }
    }
    // Update badge on page load
    $(document).ready(function() {
        // Request notification permission on page load
        requestNotificationPermission();
        
        // Check localStorage for badge state first
        const storedBadgeVisible = localStorage.getItem('messagesBadgeVisible');
        const storedBadgeCount = localStorage.getItem('messagesBadgeCount');
        
        if (storedBadgeVisible === 'false' || storedBadgeCount === '0') {
            // Hide badge immediately if it was marked as hidden
            $('#messagesBadge').hide();
            // Also set the badge text to empty to ensure CSS rules work
            $('#messagesBadge').text('');
        }
        
        // Set initial count without playing sound
        updateLeaveRequestsBadge();
        updateMessagesBadge();
        // Update badge every 30 seconds
        setInterval(updateLeaveRequestsBadge, 30000);
        setInterval(updateMessagesBadge, 30000);
        // Update badge when page becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateLeaveRequestsBadge();
                updateMessagesBadge();
            }
        });
    });
    // Global flag to track if this is the first load
    let isFirstLoad = true;
    // Function to update leave requests badge count
    function updateLeaveRequestsBadge() {
        $.ajax({
            url: 'include/api/leave-requests.php?action=count',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const badge = $('#leaveRequestsBadge');
                    const count = response.count;
                    const previousCount = parseInt(badge.data('previous-count') || 0);
                    if (count > 0) {
                        badge.text(count).show();
                        // Play sound and show notification ONLY if count increased (new request)
                        // AND we haven't already played sound for this count
                        if (!isFirstLoad && count > previousCount) {
                            const lastPlayedLeaveCount = parseInt(localStorage.getItem('lastPlayedLeaveCount') || 0);
                            
                            // Only play sound if this is a genuinely new leave request count
                            if (count > lastPlayedLeaveCount) {
                                playNotificationSound();
                                showBrowserNotification(
                                    'New Leave Request!',
                                    `You have ${count} pending leave request${count > 1 ? 's' : ''} to review.`,
                                    '../assets/images/LOGO.png'
                                );
                                
                                // Store the count for which we played sound
                                localStorage.setItem('lastPlayedLeaveCount', count);
                            }
                        }
                    } else {
                        badge.hide();
                        // Reset the played count when no leave requests
                        localStorage.removeItem('lastPlayedLeaveCount');
                    }
                    badge.data('previous-count', count);

                    // Mark first load as complete after first update
                    if (isFirstLoad) {
                        isFirstLoad = false;
                    }
                }
            },
            error: function() {
            }
        });
    }
    // Function to update messages badge count (global function)
    window.updateMessagesBadge = function() {
        $.ajax({
            url: 'include/api/notifications.php?action=count',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const badge = $('#messagesBadge');
                    const count = response.count;
                    const previousCount = parseInt(badge.data('previous-count') || 0);
                    
                    
                    // Store badge state in localStorage
                    localStorage.setItem('messagesBadgeCount', count);
                    localStorage.setItem('messagesBadgeVisible', count > 0);
                    
                    if (count > 0) {
                        badge.text(count).show();
                        
                        // Play sound and show notification ONLY if count increased (new message)
                        // AND we haven't already played sound for this count
                        if (!isFirstLoad && count > previousCount) {
                            const lastPlayedCount = parseInt(localStorage.getItem('lastPlayedNotificationCount') || 0);
                            
                            // Only play sound if this is a genuinely new message count
                            if (count > lastPlayedCount) {
                                playNotificationSound();
                                showBrowserNotification(
                                    'New Attendance Message!',
                                    `You have ${count} new attendance message${count > 1 ? 's' : ''} to review.`,
                                    '../assets/images/LOGO.png'
                                );
                                
                                // Store the count for which we played sound
                                localStorage.setItem('lastPlayedNotificationCount', count);
                            }
                        }
                    } else {
                        badge.hide();
                        // Reset the played count when no messages
                        localStorage.removeItem('lastPlayedNotificationCount');
                    }
                    badge.data('previous-count', count);
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // Function to hide messages badge (called from notifications page)
    window.hideMessagesBadge = function() {
        const badge = $('#messagesBadge');
        badge.hide();
        badge.text(''); // Clear badge text to ensure CSS rules work
        localStorage.setItem('messagesBadgeVisible', false);
        localStorage.setItem('messagesBadgeCount', 0);
        // Reset the played count when messages are marked as read
        localStorage.removeItem('lastPlayedNotificationCount');
    }

    // Function to play notification sound
    function playNotificationSound() {
        const audio = new Audio('../assets/sounds/new-notification.mp3');

        audio.addEventListener('canplaythrough', function() {
        });

        audio.addEventListener('error', function(e) {
        });

        audio.play().then(function() {
        }).catch(function(e) {
        });
    }

    // Disable click sounds on sidebar navigation links
    $(document).ready(function() {
        // Add CSS to disable any click sounds
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .sidebar .nav-link {
                    -webkit-tap-highlight-color: transparent !important;
                    -webkit-touch-callout: none !important;
                    -webkit-user-select: none !important;
                    -khtml-user-select: none !important;
                    -moz-user-select: none !important;
                    -ms-user-select: none !important;
                    user-select: none !important;
                }
                .sidebar .nav-link:focus {
                    outline: none !important;
                    box-shadow: none !important;
                }
                .sidebar .nav-link:active {
                    transform: none !important;
                }
            `)
            .appendTo('head');
        
        // Disable any audio context that might be playing sounds
        $('.sidebar .nav-link').on('click', function(e) {
            // Stop any playing audio
            const audioElements = document.querySelectorAll('audio');
            audioElements.forEach(audio => {
                if (!audio.src.includes('new-notification.mp3')) {
                    audio.pause();
                    audio.currentTime = 0;
                }
            });
        });
    });
</script>