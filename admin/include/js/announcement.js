// Toaster Settings
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "2000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

// Time format convert karne ka function
function formatTime(timeStr) {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    
    // Agar date YYYY-MM-DD format mein hai to directly parse karo
    if (dateStr.includes('-')) {
        const [year, month, day] = dateStr.split('-');
        return day + '/' + month + '/' + year;
    }
    
    // Agar date MM/DD/YYYY format mein hai to convert karo
    const date = new Date(dateStr);
    return ('0' + date.getDate()).slice(-2) + '/' +
           ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
           date.getFullYear();
}

function safeDate(str) {
    if (!str) return null;
    return new Date(str.replace(/-/g, '/'));
}

function isDateInRange(today, start, end) {
    const t = today.toISOString().split('T')[0];
    const s = start ? start.toISOString().split('T')[0] : null;
    const e = end ? end.toISOString().split('T')[0] : null;
    return (!s || t >= s) && (!e || t <= e);
}

function formatDateToYMD(date) {
    if (!date) return '';
    const d = new Date(date);
    const month = '' + (d.getMonth() + 1);
    const day = '' + d.getDate();
    const year = d.getFullYear();
    return [year, month.padStart(2, '0'), day.padStart(2, '0')].join('-');
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

function getYMD(dateStr) {
    if (!dateStr) return '';
    return dateStr.split('T')[0].split(' ')[0];
}

// Toast function
function showAnnouncementToast(msg, type) {
    var toastEl = document.getElementById('announcementToast');
    var toastMsg = document.getElementById('announcementToastMsg');
    toastMsg.textContent = msg;
    toastEl.classList.remove('text-bg-success', 'text-bg-danger');
    toastEl.classList.add(type === 'danger' ? 'text-bg-danger' : 'text-bg-success');
    var toast = new bootstrap.Toast(toastEl, { delay: 2000 });
    toast.show();
}

// Announcements Management JavaScript
$(document).ready(function() {
    // Load announcements on page load
    loadAnnouncements();

    // Function to load announcements
    function loadAnnouncements() {
        $.ajax({
            url: 'include/api/announcement.php',
            type: 'GET',
            success: function(response) {
                const timeline = $('#announcementTimeline');
                timeline.html('');

                if (response.success && response.data && response.data.length > 0) {
                    timeline.removeClass('no-announcement');
                    response.data.forEach(function(announcement) {
                        const today = new Date();
                        const todayStr = formatDateToYMD(today);
                        const startStr = formatDateToYMD(announcement.start_date);
                        const endStr = formatDateToYMD(announcement.end_date);
                        let statusBadge = '';
                        if (startStr && endStr) {
                            if (todayStr >= startStr && todayStr <= endStr) {
                                statusBadge = '<span class="badge rounded-pill bg-success-soft text-success ms-2">Active</span>';
                            } else if (todayStr < startStr) {
                                statusBadge = '<span class="badge rounded-pill bg-warning-soft text-warning ms-2">Scheduled</span>';
                            } else if (todayStr > endStr) {
                                statusBadge = '<span class="badge rounded-pill bg-danger-soft text-danger ms-2">Expired</span>';
                            }
                        } else if (startStr) {
                            if (todayStr === startStr) {
                                statusBadge = '<span class="badge rounded-pill bg-success-soft text-success ms-2">Active</span>';
                            } else if (todayStr < startStr) {
                                statusBadge = '<span class="badge rounded-pill bg-warning-soft text-warning ms-2">Scheduled</span>';
                            }
                        }
                        if (announcement.status === 'active') {
                            const [date, time] = announcement.created_at.split(' ');
                            const formattedDate = formatDate(date);
                            const formattedTime = formatTime(time);
                            const item = `
                                <div class="announcement-item">
                                    <div class="announcement-actions float-end">
                                        <a href="#" class="text-primary edit-btn" data-id="${announcement.announcement_id}" title="Edit" data-bs-toggle="modal" data-bs-target="#editAnnouncementModal"><i class="fas fa-edit"></i></a>
                                        <a href="#" class="text-danger delete-btn" data-id="${announcement.announcement_id}" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                    <div class="announcement-icon theme-icon">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div class="announcement-content-wrapper">
                                        <div class="announcement-time">
                                            <span class="announcement-date theme-badge">${formattedDate}</span>
                                            <i class="far fa-clock"></i> ${formattedTime}
                                        </div>
                                        <h3 class="announcement-title">${announcement.title} ${statusBadge}</h3>
                                        <div class="announcement-content">
                                            ${formatContent(announcement.content)}
                                        </div>
                                        <div class="announcement-footer">
                                            <span class="announcement-category">
                                                <i class="fas fa-tag"></i> Announcement
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            timeline.append(item);
                        }
                    });
                } else {
                    timeline.addClass('no-announcement');
                    timeline.html(`
                        <div class="no-announcement-banner" style="margin: 50px auto; max-width: 500px; background: #fff; border-radius: 18px; padding: 3rem 2rem; text-align: center;">
                            <img src='../assets/images/announcement.png' alt='No Announcement' style='width: 90px; margin-bottom: 18px; opacity: 0.8;'>
                            <div style="font-size: 1.5rem; color: var(--topbar-bg-light); font-weight: 700; margin-bottom: 8px;">No Announcements Created Yet!</div>
                            <div style="font-size:14px;font-weight: 500;" class="text-muted">You haven't added any announcements. Create one to keep your team informed.</div>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                showAnnouncementToast('Error loading announcements', 'danger');
            }
        });
    }

    // Save new announcement
    $('#saveAnnouncement').on('click', function(e) {
        e.preventDefault(); // Add this line to prevent default form submission
        
        const title = $('#announcementTitle').val();
        // Quill content (HTML)
        const content = (window.addAnnouncementQuill && window.addAnnouncementQuill.root.innerHTML) || '';
        const start_date = $('#announcementStartDate').val();
        const end_date = $('#announcementEndDate').val();

        if (!title || !content) {
            showAnnouncementToast('Please fill all required fields', 'danger');
            return;
        }

        $.ajax({
            url: 'include/api/announcement.php',
            type: 'POST',
            data: JSON.stringify({ 
                title, 
                content, 
                start_date, 
                end_date 
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    showAnnouncementToast('Announcement added successfully', 'success');
                    $('#addAnnouncementModal').modal('hide');
                    loadAnnouncements();
                    // Clear form
                    $('#announcementTitle').val('');
                    if (window.addAnnouncementQuill) window.addAnnouncementQuill.setContents([]);
                    $('#announcementStartDate').val('');
                    $('#announcementEndDate').val('');
                } else {
                    showAnnouncementToast(response.message || 'Error adding announcement', 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAnnouncementToast('Error adding announcement', 'danger');
            }
        });
    });

    // Edit announcement (populate modal)
    $(document).on('click', '.edit-btn', function() {
        const announcementId = $(this).data('id');
        $.ajax({
            url: `include/api/announcement.php?announcement_id=${announcementId}`,
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    const announcement = response.data[0];
                    $('#editAnnouncementModal #announcementTitle').val(announcement.title);
                    if (window.editAnnouncementQuill) window.editAnnouncementQuill.root.innerHTML = announcement.content || '';
                    $('#editAnnouncementModal #announcementStartDate').val(getYMD(announcement.start_date));
                    $('#editAnnouncementModal #announcementEndDate').val(getYMD(announcement.end_date));
                    $('#editAnnouncementModal').data('id', announcement.announcement_id);
                } else {
                    showAnnouncementToast('Error loading announcement details', 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAnnouncementToast('Error loading announcement details', 'danger');
            }
        });
    });

    // Update announcement
    $('#updateAnnouncement').on('click', function(e) {
        e.preventDefault();
        const announcement_id = $('#editAnnouncementModal').data('id');
        const title = $('#editAnnouncementModal #announcementTitle').val();
        const content = (window.editAnnouncementQuill && window.editAnnouncementQuill.root.innerHTML) || '';
        const start_date = $('#editAnnouncementModal #announcementStartDate').val();
        const end_date = $('#editAnnouncementModal #announcementEndDate').val();

        if (!title || !content) {
            showAnnouncementToast('Please fill all required fields', 'danger');
            return;
        }

        $.ajax({
            url: 'include/api/announcement.php',
            type: 'PUT',
            data: JSON.stringify({ 
                announcement_id, 
                title, 
                content, 
                start_date, 
                end_date 
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    showAnnouncementToast('Announcement updated successfully', 'success');
                    $('#editAnnouncementModal').modal('hide');
                    loadAnnouncements();
                } else {
                    showAnnouncementToast(response.message || 'Error updating announcement', 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAnnouncementToast('Error updating announcement', 'danger');
            }
        });
    });

    // Delete announcement
    $(document).on('click', '.delete-btn', function() {
        const announcementId = $(this).data('id');
        $.ajax({
            url: 'include/api/announcement.php',
            type: 'POST',
            data: { announcement_id: announcementId, action: 'delete' },
            success: function(response) {
                if (response.success) {
                    showAnnouncementToast('Announcement deleted successfully', 'danger');
                    loadAnnouncements();
                } else {
                    showAnnouncementToast(response.message || 'Error deleting announcement', 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAnnouncementToast('Error deleting announcement', 'danger');
            }
        });
    });

    // Search functionality
    $('#searchBtn').on('click', function() {
        var searchTerm = $(this).closest('.filter-form').find('input[type="text"]').val().toLowerCase();
        filterAnnouncements(searchTerm);
    });

    $('.filter-form input[type="text"]').on('keyup', function(e) {
        if (e.key === 'Enter') {
            $('#searchBtn').click();
        }
    });

    function filterAnnouncements(searchTerm) {
        $('.announcement-item').each(function() {
            var title = $(this).find('.announcement-title').text().toLowerCase();
            var content = $(this).find('.announcement-content').text().toLowerCase();
            if (title.includes(searchTerm) || content.includes(searchTerm) || searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        if ($('.announcement-item:visible').length === 0) {
            $('#noResults').show();
        } else {
            $('#noResults').hide();
        }
    }
});