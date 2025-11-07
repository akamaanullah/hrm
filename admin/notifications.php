<?php include "header.php" ?>
<?php include "top-bar.php" ?>
<?php include "sidebar.php" ?>
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
            <h4 class="mb-0" style="color: #1f2937; font-size: 1.75rem;">           
                Notifications
            </h4>
            <div class="d-flex gap-2">
                <button id="markAllRead" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-check-double me-1"></i>Mark All Read
                </button>
            </div>
        </div>
        <!-- Messages List -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; overflow: hidden;">
                    <div class="card-header" style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <h5 class="card-title mb-0 text-black" style="font-weight: 700; font-size: 1.25rem;">
                            <i class="fas fa-bell me-2"></i>
                            Recent Notifications
                        </h5>
                        <p class="text-mute-50 mb-0 mt-1" style="font-size: 0.9rem;">Employee attendance notifications and messages</p>
                    </div>
                    <div class="card-body p-0">
                        <div id="messagesContainer" style="max-height: 680px; overflow-y: auto;">
                            <!-- Messages will be loaded here -->
                            <div class="text-center py-5">
                                <div class="spinner-border" style="color: #00bfa5;" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading messages...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
$(document).ready(function() {
    // Load messages on page load
    loadMessages();
    // Refresh button
    $('#refreshNotifications').click(function() {
        loadMessages();
    });
    // Mark all as read
    $('#markAllRead').click(function() {
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Marking...');
        
        $.ajax({
            url: 'include/api/notifications.php?action=mark_all_read',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide badge immediately (optimistic update)
                    if (typeof window.hideMessagesBadge === 'function') {
                        window.hideMessagesBadge();
                    } else {
                        $('#messagesBadge').hide();
                    }
                    
                    // Show success message
                    showToast('Success', response.message, 'success');
                    
                    // Reload messages
                    loadMessages();
                    
                    // Update badge count to ensure accuracy
                    setTimeout(function() {
                        updateMessagesBadge();
                    }, 500);
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function() {
                showToast('Error', 'Failed to mark messages as read', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).html(originalText);
            }
        });
    }); 
    // Auto refresh every 30 seconds
    setInterval(function() {
        loadMessages();
    }, 30000);
    
    // Toast notification function
    function showToast(title, message, type = 'info') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
        }
        
        // Add toast to container
        $('#toastContainer').append(toastHtml);
        
        // Show the last added toast
        const toastElement = $('#toastContainer .toast').last();
        const toast = new bootstrap.Toast(toastElement[0]);
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});

// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + lastName).replace(/\s+/g, ' ').trim();
}

function loadMessages() {
    $.ajax({
        url: 'include/api/notifications.php?action=list',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayMessages(response.data);
            } else {
                $('#messagesContainer').html('<div class="text-center py-5"><p class="text-muted">No messages found</p></div>');
            }
        },
        error: function() {
            $('#messagesContainer').html('<div class="text-center py-5"><p class="text-danger">Error loading messages</p></div>');
        }
    });
}
function displayMessages(messages) {
    if (messages.length === 0) {
        $('#messagesContainer').html(`
            <div class="text-center py-5" style="background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 16px; margin: 2rem;">
                <div class="empty-state-icon mb-4" style="width: 80px; height: 80px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 8px 32px rgba(0, 191, 165, 0.3);">
                    <i class="fas fa-bell-slash text-white" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-2" style="color: #6b7280; font-weight: 600;">No Messages Found</h5>
                <p class="text-muted mb-0" style="color: #9ca3af;">No attendance messages have been received yet.</p>
            </div>
        `);
        return;
    }
    let html = '';
    messages.forEach(function(message) {
        const msgTime = new Date(message.msg_time);
        const attendanceDate = new Date(message.check_in);
        const timeAgo = getTimeAgo(msgTime);
        const statusColor = getStatusColor(message.status);
        // Format attendance date (just date)
        const attendanceDateStr = attendanceDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric'
        });
        // Format message time (date and time)
        const messageTimeStr = msgTime.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        html += `
            <div class="message-item border-bottom" style="cursor: pointer;" onclick="redirectToAttendance('${message.check_in}', '${message.emp_id}')">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <!-- Employee Info Section -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(0, 191, 165, 0.3);">
                                    ${message.emp_id || 'N/A'}
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <h6 class="mb-0" style="color: #1f2937; font-weight: 700; font-size: 1.1rem;">${createFullName(message.first_name || '', message.middle_name || '', message.last_name || '') || 'Unknown Employee'}</h6>
                                        <small class="text-muted d-flex align-items-center" style="font-size: 0.85rem;">
                                            <i class="fas fa-clock me-1" style="color: #6b7280;"></i>${timeAgo}
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-briefcase me-1"></i>${message.designation || 'N/A'}
                                        </span>
                                        <span class="badge" style="background: #f0f9ff; color: #0369a1; font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-building me-1"></i>${message.department || 'No Department'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Message Content -->
                            <div class="message-content mb-3" style="background: #f8fafc; padding: 1rem; border-left: 4px solid #00bfa5;">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-quote-left me-2" style="color: #00bfa5; margin-top: 0.2rem;"></i>
                                    <p class="mb-0" style="color: #101316; line-height: 1.6; font-style: italic;">"${message.reason}"</p>
                                </div>
                            </div>                           
                            <!-- Date and Time Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="info-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.75rem;">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-wrapper me-3" style="width: 35px; height: 35px; background: linear-gradient(135deg, #00bfa5, #02d6ba); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-check text-white" style="font-size: 0.9rem;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Attendance Date</small>
                                                <span class="fw-bold" style="color: #1f2937; font-size: 0.9rem;">${attendanceDateStr}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.75rem;">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-wrapper me-3" style="width: 35px; height: 35px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-comment-dots text-white" style="font-size: 0.9rem;"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Message Time</small>
                                                <span class="fw-bold" style="color: #1f2937; font-size: 0.9rem;">${messageTimeStr}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#messagesContainer').html(html);
}
function getStatusColor(status) {
    switch(status?.toLowerCase()) {
        case 'present': return '#10b981';
        case 'late': return '#f59e0b';
        case 'absent': return '#ef4444';
        case 'half-day': return '#8b5cf6';
        default: return '#6b7280';
    }
}
function getStatusIcon(status) {
    switch(status?.toLowerCase()) {
        case 'present': return 'check-circle';
        case 'late': return 'clock';
        case 'absent': return 'times-circle';
        case 'half-day': return 'clock-half';
        default: return 'question-circle';
    }
}
function getTimeAgo(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 60) {
        return `${minutes} minutes ago`;
    } else if (hours < 24) {
        return `${hours} hours ago`;
    } else {
        return `${days} days ago`;
    }
}
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Function to redirect to attendance page with specific date and employee
function redirectToAttendance(checkInDate, empId) {
    // Convert check-in date to YYYY-MM-DD format for URL
    const date = new Date(checkInDate);
    const formattedDate = date.toISOString().split('T')[0]; // YYYY-MM-DD format
    
    // Redirect to attendance page with date, employee parameters and open_modal flag
    window.location.href = `attendance.php?date=${formattedDate}&emp_id=${empId}&open_modal=true`;
}  
  
</script>
<?php include "footer.php" ?>