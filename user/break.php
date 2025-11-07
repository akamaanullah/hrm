<?php include 'session_check.php'; ?>
<?php include 'header.php'; ?>
<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="page-title mb-0">
                                Break Time Management
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="date" class="form-control form-control-sm" id="startDate" placeholder="Start Date">
                                </div>
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="date" class="form-control form-control-sm" id="endDate" placeholder="End Date">
                                </div>
                                <button class="btn btn-primary btn-sm" id="applyDateFilter">
                                    Filter
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="clearDateFilter" style="background: #6c757d; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: transform 0.2s ease;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Break Control Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="break-status mb-3">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 3rem; color: #00bfa5 !important"></i>
                            <h5 class="mt-3" id="breakStatusText">Ready to take a break?</h5>
                            <p class="text-muted" id="breakStatusSubtext">Click below to start your break time</p>
                        </div>
                        
                        <div class="break-controls">
                             <button id="startBreakBtn" class="btn btn-success btn-lg px-4 py-2">
                                <i class="fas fa-play me-2"></i>Start Break
                             </button>
                             <button id="endBreakBtn" class="btn btn-danger btn-lg px-4 py-2" style="display: none;">
                                <i class="fas fa-stop me-2"></i>End Break
                             </button>
                        </div>
                        
                        <div id="breakTimer" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                 <div class="row">
                                    <div class="col-md-6">
                                        <strong>Break Duration:</strong> <span id="currentDuration">0 minutes</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <strong>Started: </strong> <span id="breakStartTime">-</span>
                                        </small>
                                    </div>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Break History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2" style="color: var(--button-color);"></i>Recent Break History
                                </h5>
</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="breakHistoryTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Break Started</th>
                                        <th>Break Ended</th>
                                        <th>Duration</th>
                                        <th>Attendance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="breakHistoryBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading break history...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Break Success Modal -->
<div class="modal fade" id="breakSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 id="breakSuccessTitle">Break Started!</h5>
                <p id="breakSuccessMessage" class="text-muted">Your break time has been recorded.</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    let breakTimer;
    let isBreakActive = false;
    let lastBreakUpdate = 0;
    let ajaxPollingInterval;
    
    // Global variables for date range
    let selectedStartDate = null;
    let selectedEndDate = null;
    
    // Load initial data
    loadAttendanceStatus();
    loadBreakStatus();
    loadBreakHistory();
    
    // Start AJAX polling for real-time updates
    startAjaxPolling();
    
    // Date range filter functionality (header)
    $('#applyDateFilter').click(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        if (!startDate || !endDate) {
            showBreakToast('Please select both start and end dates', 'error');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            showBreakToast('Start date cannot be after end date', 'error');
            return;
        }

        // Store selected dates globally
        selectedStartDate = startDate;
        selectedEndDate = endDate;

        // Reload break history with new date range
        loadBreakHistory();

        showBreakToast('Date range applied successfully', 'success');
    });

    // Clear date range filter (header)
    $('#clearDateFilter').click(function() {
        $('#startDate').val('');
        $('#endDate').val('');
        selectedStartDate = null;
        selectedEndDate = null;

        // Reload break history for all data
        loadBreakHistory();

        showBreakToast('Date range cleared', 'info');
    });

    // Date range filter functionality (break history card)
    $('#applyDateFilter2').click(function() {
        const startDate = $('#startDate2').val();
        const endDate = $('#endDate2').val();

        if (!startDate || !endDate) {
            showBreakToast('Please select both start and end dates', 'error');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            showBreakToast('Start date cannot be after end date', 'error');
            return;
        }

        // Store selected dates globally
        selectedStartDate = startDate;
        selectedEndDate = endDate;

        // Reload break history with new date range
        loadBreakHistory();

        showBreakToast('Date range applied successfully', 'success');
    });

    // Clear date range filter (break history card)
    $('#clearDateFilter2').click(function() {
        $('#startDate2').val('');
        $('#endDate2').val('');
        selectedStartDate = null;
        selectedEndDate = null;

        // Reload break history for all data
        loadBreakHistory();

        showBreakToast('Date range cleared', 'info');
    });
    
    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, reduce polling frequency
            stopAjaxPolling();
        } else {
            // Page is visible, resume normal polling
            startAjaxPolling();
            // Immediately update data when page becomes visible
            pollForUpdates();
        }
    });
    
    // Stop polling when page is unloaded
    $(window).on('beforeunload', function() {
        stopAjaxPolling();
    });
    
    // Start Break Button
    $('#startBreakBtn').click(function() {
        startBreak();
    });
    
    // End Break Button
    $('#endBreakBtn').click(function() {
        endBreak();
    });
    
    function startBreak() {
        // Check if button is disabled
        if ($('#startBreakBtn').prop('disabled')) {
            showBreakToast('Please check-in first before taking a break.', 'error');
            return;
        }
        
        $.ajax({
            url: 'include/api/break.php',
            type: 'POST',
            data: { action: 'start_break' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    isBreakActive = true;
                    updateBreakUI(true);
                    showBreakSuccess('Break Started!', 'Your break time has been recorded.');
                    startBreakTimer();
                    // Immediate updates
                    loadBreakStatus();
                    loadBreakHistory();
                } else {
                    showBreakToast(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error starting break. Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                    if (response.debug) {
                        console.log('Debug info:', response.debug);
                    }
                } catch (e) {
                    console.log('Raw error:', xhr.responseText);
                }
                showBreakToast(errorMessage, 'error');
            }
        });
    }
    
    function endBreak() {
        $.ajax({
            url: 'include/api/break.php',
            type: 'POST',
            data: { action: 'end_break' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    isBreakActive = false;
                    updateBreakUI(false);
                    clearInterval(breakTimer);
                    showBreakSuccess('Break Ended!', `Duration: ${response.break_duration}`);
                    // Immediate updates
                    loadBreakStatus();
                    loadBreakHistory();
                    loadAttendanceStatus();
                } else {
                    // Show detailed error message
                    let errorMsg = response.message || 'Error ending break. Please try again.';
                    if (response.debug) {
                        console.error('Break End Error:', response.debug);
                    }
                    showBreakToast(errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                showBreakToast('Network error. Please check your connection and try again.', 'error');
            }
        });
    }
    
    function loadAttendanceStatus() {
        $.ajax({
            url: 'include/api/break.php',
            type: 'GET',
            data: { action: 'get_attendance_status' },
            dataType: 'json',
            timeout: 3000,
            success: function(response) {
                if (response.success) {
                    updateBreakButtonState(response.can_take_break, response.message);
                }
            },
            error: function() {
                // Silently handle errors for polling
                console.log('Attendance status update failed - will retry');
            }
        });
    }
    
    function loadBreakStatus() {
        $.ajax({
            url: 'include/api/break.php',
            type: 'GET',
            data: { action: 'get_active_break' },
            dataType: 'json',
            timeout: 3000,
            success: function(response) {
                if (response.success && response.has_active_break) {
                    isBreakActive = true;
                    updateBreakUI(true);
                    
                    // Update break start time display with attendance date
                    if (response.break_start) {
                        let displayDate = 'No Attendance Date';
                        if (response.attendance_info && response.attendance_info.check_in) {
                            displayDate = new Date(response.attendance_info.check_in).toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            });
                        }
                        const displayTime = new Date(response.break_start).toLocaleTimeString('en-US', {hour12: true});
                        $('#breakStartTime').text(`${displayDate}, ${displayTime}`);
                    }
                    
                    // Update current duration
                    if (response.current_duration) {
                        $('#currentDuration').text(response.current_duration);
                    }
                    
                    startBreakTimer();
                } else {
                    isBreakActive = false;
                    updateBreakUI(false);
                }
            },
            error: function() {
                // Silently handle errors for polling
                console.log('Break status update failed - will retry');
            }
        });
    }
    
    function loadBreakHistory() {
        let url = 'include/api/break.php';
        const params = new URLSearchParams();
        params.append('action', 'get_break_history');

        // Add date range parameters if selected
        if (selectedStartDate && selectedEndDate) {
            params.append('start_date', selectedStartDate);
            params.append('end_date', selectedEndDate);
        }

        url += '?' + params.toString();

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            timeout: 3000,
            success: function(response) {
                if (response.success) {
                    displayBreakHistory(response.breaks);
                }
            },
            error: function() {
                // Silently handle errors for polling
                console.log('Break history update failed - will retry');
            }
        });
    }
    
    function updateBreakButtonState(canTakeBreak, message) {
        if (canTakeBreak) {
            $('#startBreakBtn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
            $('#breakStatusSubtext').text('Click below to start your break time');
        } else {
            $('#startBreakBtn').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
            $('#breakStatusSubtext').text(message);
        }
    }
    
    function updateBreakUI(isActive) {
        if (isActive) {
            $('#breakStatusText').text('Break in Progress');
            $('#breakStatusSubtext').text('Enjoy your break time!');
            $('#startBreakBtn').hide();
            $('#endBreakBtn').show();
            $('#breakTimer').show();
        } else {
            $('#breakStatusText').text('Ready to take a break?');
            $('#startBreakBtn').show();
            $('#endBreakBtn').hide();
            $('#breakTimer').hide();
            // Reload attendance status to update button state
            loadAttendanceStatus();
        }
    }
    
    function startBreakTimer() {
        // Client-side timer for real-time display (no API calls)
        breakTimer = setInterval(function() {
            if (isBreakActive) {
                updateClientSideDuration();
            }
        }, 500); // Update every 0.5 seconds for smoother display
        
        // Separate timer for server sync (more frequent)
        setInterval(function() {
            if (isBreakActive) {
                updateCurrentDuration(); // Server sync every 5 seconds
            }
        }, 5000); // Sync with server every 5 seconds for faster updates
        
        // Check attendance status every 10 seconds
        setInterval(function() {
            if (!isBreakActive) {
                loadAttendanceStatus();
            }
        }, 10000); // Check attendance status every 10 seconds for faster updates
    }
    
    function startAjaxPolling() {
        // Clear any existing polling
        if (ajaxPollingInterval) {
            clearInterval(ajaxPollingInterval);
        }
        
        // Start polling every 2 seconds for faster real-time updates
        ajaxPollingInterval = setInterval(function() {
            // Only poll if page is visible
            if (!document.hidden) {
                pollForUpdates();
            }
        }, 2000); // Poll every 2 seconds for faster updates
    }
    
    function stopAjaxPolling() {
        if (ajaxPollingInterval) {
            clearInterval(ajaxPollingInterval);
            ajaxPollingInterval = null;
        }
    }
    
    function checkAutoEndBreak() {
        // Only check if there's an active break
        if (isBreakActive) {
            $.ajax({
                url: 'include/api/break.php',
                type: 'GET',
                data: { action: 'auto_end_break_on_checkout' },
                dataType: 'json',
                timeout: 3000,
                success: function(response) {
                    if (response.success && response.auto_ended) {
                        // Break was auto-ended
                        isBreakActive = false;
                        updateBreakUI(false);
                        clearInterval(breakTimer);
                        showBreakToast('Break automatically ended due to check-out.', 'info');
                        // Immediate updates
                        loadBreakStatus();
                        loadBreakHistory();
                        loadAttendanceStatus();
                    }
                },
                error: function() {
                    // Silently handle errors for polling
                    console.log('Auto-end break check failed - will retry');
                }
            });
        }
    }
    
    function pollForUpdates() {
        // Check for auto-end break on checkout
        checkAutoEndBreak();
        
        // Poll attendance status
        if (!isBreakActive) {
            loadAttendanceStatus();
        }
        
        // Poll break status
        loadBreakStatus();
        
        // Poll break history (more frequently for faster updates)
        if (Math.random() < 0.5) { // 50% chance to update history
            loadBreakHistory();
        }
    }
    
    function updateClientSideDuration() {
        // Get break start time from the displayed time
        const startTimeText = $('#breakStartTime').text();
        if (startTimeText && startTimeText !== '-') {
            // Extract time part from the displayed text (format: "date, time")
            const timePart = startTimeText.split(', ')[1];
            if (timePart) {
                // Create a date object for today with the extracted time
                const today = new Date();
                const [time, period] = timePart.split(' ');
                const [hours, minutes, seconds] = time.split(':');
                
                let hour24 = parseInt(hours);
                if (period === 'PM' && hour24 !== 12) {
                    hour24 += 12;
                } else if (period === 'AM' && hour24 === 12) {
                    hour24 = 0;
                }
                
                const startTime = new Date(today.getFullYear(), today.getMonth(), today.getDate(), hour24, parseInt(minutes), parseInt(seconds));
                const now = new Date();
                const diffMs = now - startTime;
                
                const durationHours = Math.floor(diffMs / (1000 * 60 * 60));
                const durationMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                const durationSeconds = Math.floor((diffMs % (1000 * 60)) / 1000);
                
                let durationStr = '';
                if (durationHours > 0) {
                    durationStr = durationHours + ' hour' + (durationHours > 1 ? 's' : '') + ' ' + durationMinutes + ' minute' + (durationMinutes !== 1 ? 's' : '');
                } else if (durationMinutes > 0) {
                    durationStr = durationMinutes + ' minute' + (durationMinutes > 1 ? 's' : '') + ' ' + durationSeconds + ' second' + (durationSeconds !== 1 ? 's' : '');
                } else {
                    durationStr = durationSeconds + ' second' + (durationSeconds !== 1 ? 's' : '');
                }
                
                $('#currentDuration').text(durationStr);
            }
        }
    }
    
    function updateCurrentDuration() {
        // Only make API call if we don't have recent data
        if (lastBreakUpdate && (Date.now() - lastBreakUpdate < 3000)) {
            return; // Skip if updated within last 3 seconds
        }
        
        $.ajax({
            url: 'include/api/break.php',
            type: 'GET',
            data: { action: 'get_active_break' },
            dataType: 'json',
            timeout: 3000, // 5 second timeout
            success: function(response) {
                if (response.success && response.has_active_break) {
                    $('#currentDuration').text(response.current_duration || '0 minutes');
                    lastBreakUpdate = Date.now();
                }
            },
            error: function() {
                // Silently handle errors to prevent console spam
                console.log('Break duration update failed - will retry');
            }
        });
    }
    
    function displayBreakHistory(breaks) {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#breakHistoryTable')) {
            $('#breakHistoryTable').DataTable().destroy();
        }
        
        const tbody = $('#breakHistoryBody');
        tbody.empty();
        
        if (breaks.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="fas fa-clock me-2"></i>No break history found
                    </td>
                </tr>
            `);
            return;
        }
        
        breaks.forEach(function(breakItem) {
            // Separate date and time
            const startDateTime = new Date(breakItem.break_start);
            const endDateTime = breakItem.break_end ? new Date(breakItem.break_end) : null;
            
            // Format time only (e.g., "9:39:19 PM")
            const startTime = startDateTime.toLocaleTimeString('en-US', {hour12: true});
            const endTime = endDateTime ? endDateTime.toLocaleTimeString('en-US', {hour12: true}) : 'In Progress';
            
            const duration = breakItem.break_duration || 'In Progress';
            const statusBadge = breakItem.status === 'active' ? 
                '<span class="badge bg-danger">Active</span>' : 
                '<span class="badge bg-success">Completed</span>';
            
            // Get attendance date for Date column
            let attendanceDate = 'No Date';
            if (breakItem.check_in) {
                const attendanceDateTime = new Date(breakItem.check_in);
                attendanceDate = attendanceDateTime.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
            
            // Attendance information
            let attendanceInfo = 'No Attendance';
            if (breakItem.attendance_id && breakItem.check_in) {
                const checkInTime = new Date(breakItem.check_in).toLocaleString('en-US', {hour12: true});
                const attendanceStatus = breakItem.attendance_status || 'Unknown';
                attendanceInfo = `<small>Check-in: ${checkInTime}<br>Status: ${attendanceStatus}</small>`;
            }
            
            tbody.append(`
                <tr>
                    <td>${attendanceDate}</td>
                    <td>${startTime}</td>
                    <td>${endTime}</td>
                    <td>${duration}</td>
                    <td>${attendanceInfo}</td>
                    <td>${statusBadge}</td>
                </tr>
            `);
        });
        
        // Initialize DataTable with pagination and show entries (no search)
        $('#breakHistoryTable').DataTable({
            "order": [[0, "desc"]], // Sort by Date (newest first)
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "searching": false, // Disable search functionality
            "language": {
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                },
                "zeroRecords": "No records found"
            },
            "responsive": true,
            "autoWidth": false
        });
    }
    
    function showBreakSuccess(title, message) {
        $('#breakSuccessTitle').text(title);
        $('#breakSuccessMessage').text(message);
        $('#breakSuccessModal').modal('show');
    }
    
    function showBreakToast(message, type) {
        // Remove any existing toasts first to prevent duplicates
        $('.alert').remove();
        $('.toast').remove();
        $('[role="alert"]').remove();
        
        // Disable external toast system to prevent duplicates
        // if (typeof showToast === 'function') {
        //     try {
        //         showToast(message, type === 'error');
        //         return;
        //     } catch (error) {
        //         console.log('showToast failed, using fallback:', error);
        //         // Continue to fallback toast creation
        //     }
        // }
        
        // Create custom toast directly
            // Create custom toast
            let toastClass, title;
            if (type === 'error') {
                toastClass = 'alert-danger';
                title = 'Error!';
            } else if (type === 'info') {
                toastClass = 'alert-info';
                title = 'Info!';
            } else {
                toastClass = 'alert-success';
                title = 'Success!';
            }
            
            const toastHtml = `
                <div class="alert ${toastClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <strong>${title}</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('body').append(toastHtml);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    }
);
</script>
