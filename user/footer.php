<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.7/build/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="include/js/userattendance.js"></script>
<script>
    function toggleDropdown() {
        var dropdown = document.getElementById('userDropdown');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
        }
    }
    // Announcement Notification System for User Side
    // Keep track of previous announcement count
    let previousAnnouncementCount = 0;
    let isFirstLoad = true;

    function updateAnnouncementBadge() {
        $.ajax({
            url: 'include/api/userannouncements.php?action=count_unread',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const badge = $('#announcementBadge');
                if (response.success) {
                    const currentCount = response.count;
                    if (currentCount > 0) {
                        badge.text(currentCount).show();
                        // Sirf new announcements ke liye notification aur sound (first load nahi)
                        if (!isFirstLoad && currentCount > previousAnnouncementCount) {
                            showAnnouncementNotification('New Announcement!', `You have ${currentCount} new announcement(s).`);
                            playAnnouncementSound();
                        }
                    } else {
                        badge.hide();
                    }
                    // Update previous count for next comparison
                    previousAnnouncementCount = currentCount;
                    isFirstLoad = false;
                }
            },
            error: function() {
                // Fallback: hide badge if API fails
                $('#announcementBadge').hide();
            }
        });
    }

    function playAnnouncementSound() {
        const audio = new Audio('../assets/sounds/new-notification.mp3');
        audio.play().catch(function(error) {
            console.log('Sound play nahi ho saka:', error);
        });
    }

    function showAnnouncementNotification(title, body) {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                new Notification(title, {
                    body: body,
                    icon: '../assets/images/LOGO.png'
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        new Notification(title, {
                            body: body,
                            icon: '../assets/images/LOGO.png'
                        });
                    }
                });
            }
        }
    }
    // Page load pe notification permission request
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    // Initial badge update
    updateAnnouncementBadge();
    // Har 30 seconds mein check karo
    setInterval(updateAnnouncementBadge, 30000);
    // Tab change pe check karo
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateAnnouncementBadge();
        }
    });
</script>
<!-- PWA Service Worker Registration -->
<script src="../assets/js/pwa-register.js"></script>
</body>

</html>