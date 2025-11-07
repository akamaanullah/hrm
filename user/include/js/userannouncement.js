// User Announcements JavaScript
$(document).ready(function () {
    // Page load pe sirf current user ke liye announcements ko read mark karo
    $.ajax({
        url: 'include/api/mark-as-read.php',
        type: 'POST',
        data: { type: 'announcement_all' },
        success: function (response) {
            // Badge ko update karo after marking as read
            if (typeof updateAnnouncementBadge === 'function') {
                updateAnnouncementBadge();
            }
            // Load announcements
            loadAnnouncements();
        },
        error: function() {
            // If mark as read fails, still load announcements
            loadAnnouncements();
        }
    });

    // Load announcements on page load
    loadAnnouncements();

    // Set up polling every 5 seconds
    setInterval(loadAnnouncements, 5000);

    // Function to load announcements
    function loadAnnouncements() {
        $.ajax({
            url: 'include/api/userannouncements.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                var timeline = $('#announcementTimeline');

                timeline.empty();
                timeline.removeClass('no-announcement');
                if (response.success && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const [date, time] = item.created_at.split(' ');
                        const formattedDate = formatDate(date);
                        const formattedTime = formatTime(item.created_at);

                        timeline.append(`
                            <div class="announcement-item">
                                <div class="announcement-icon theme-icon">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="announcement-content-wrapper">
                                    <div class="announcement-time">
                                        <span class="announcement-date theme-badge">${formattedDate}</span>
                                        <i class="far fa-clock"></i> ${formattedTime}
                                    </div>
                                    <h3 class="announcement-title">${item.title}</h3>
                                    <div class="announcement-content">
                                        ${formatContent(item.content)}
                                    </div>
                                    <div class="announcement-footer">
                                        <span class="announcement-category"><i class="fas fa-tag"></i> Announcement</span>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    timeline.addClass('no-announcement');
                    timeline.append(`
                        <div class="no-announcement-banner" style="margin: 50px auto; max-width: 500px; background: #fff; border-radius: 18px; padding: 3rem 2rem; text-align: center;">
                            <img src='../assets/images/announcement.png' alt='No Announcement' style='width: 90px; margin-bottom: 18px; opacity: 0.8;'>
                            <div style="font-size: 1.5rem; color: #009688; font-weight: 600; margin-bottom: 8px;">No Announcements Available!</div>
                            <p style="color:#666;">There are currently no announcements shared by the admin. Please check back later.</p>
                        </div>
                    `);
                }
            }
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';

        // Agar date YYYY-MM-DD format mein hai to directly parse karo
        if (dateStr.includes('-') && dateStr.split('-')[0].length === 4) {
            const [year, month, day] = dateStr.split('-');
            return day + '/' + month + '/' + year;
        }

        // Agar date MM/DD/YYYY format mein hai to convert karo
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) {
            return dateStr;
        }
        return ('0' + d.getDate()).slice(-2) + '/' +
            ('0' + (d.getMonth() + 1)).slice(-2) + '/' +
            d.getFullYear();
    }
    function formatTime(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    // Function to format content with proper line breaks and formatting
    function formatContent(content) {
        if (!content) return '';
        
        // Convert line breaks to <br> tags
        let formattedContent = content.replace(/\n/g, '<br>');
        
        // Convert double line breaks to paragraph breaks
        formattedContent = formattedContent.replace(/<br><br>/g, '</p><p>');
        
        // Wrap in paragraph tags
        formattedContent = '<p>' + formattedContent + '</p>';
        
        // Handle lists (lines starting with - or * or numbers)
        formattedContent = formattedContent.replace(/<p>([-*]\s.*?)<\/p>/g, '<ul><li>$1</li></ul>');
        formattedContent = formattedContent.replace(/<p>(\d+\.\s.*?)<\/p>/g, '<ol><li>$1</li></ol>');
        
        // Clean up any empty paragraphs
        formattedContent = formattedContent.replace(/<p><\/p>/g, '');
        
        return formattedContent;
    }

    // Mark announcement as read function
    function markAnnouncementRead(announcementId) {
        $.ajax({
            url: 'include/api/mark-as-read.php',
            type: 'POST',
            data: { type: 'announcement', announcement_id: announcementId },
            success: function (response) {
                // Optionally reload announcements or update UI
            }
        });
    }

    // Get priority color for badge
    function getPriorityColor(priority) {
        switch (priority) {
            case 'high': return 'danger';
            case 'medium': return 'warning';
            case 'low': return 'info';
            default: return 'secondary';
        }
    }

});

function toggleDropdown() {
    var dropdown = document.getElementById('userDropdown');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
    }
}