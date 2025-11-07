// Real-time polling setup
let pollingInterval;
let isPollingActive = false;

// Global variables for date range
let selectedStartDate = null;
let selectedEndDate = null;

// Start real-time polling
function startPolling() {
    if (isPollingActive) return;

    isPollingActive = true;
    pollingInterval = setInterval(function() {
        // Only poll if page is visible and not in modal
        if (!document.hidden && !$('.modal.show').length) {
            loadAttendanceDataSilently();
        }
    }, 30000); // Poll every 30 seconds

}

// Stop real-time polling
function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    isPollingActive = false;
}

// Silent attendance load
window.loadAttendanceDataSilently = function() {

    // Use selected date range if available, otherwise use current month
    if (selectedStartDate && selectedEndDate) {
        loadAttendanceData(selectedStartDate, selectedEndDate);
    } else {
        loadAttendanceData();
    }
}

// Date range functionality
$(document).ready(function() {
    // Get employee joining date and set min date for date inputs
    $.ajax({
        url: 'include/api/userattendance.php',
        type: 'GET',
        data: {
            action: 'get_employee_info'
        },
        success: function(response) {
            if (response.success && response.employee && response.employee.joining_date) {
                window.employeeJoiningDate = response.employee.joining_date;
                // Set min date for date inputs
                $('#startDate, #endDate').attr('min', response.employee.joining_date);
            }
        }
    });

    // Apply date range filter
    $('#applyDateFilter').click(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        if (!startDate || !endDate) {
            showAttendanceToast('Please select both start and end dates', 'warning');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            showAttendanceToast('Start date cannot be after end date', 'error');
            return;
        }

        // Check if dates are before joining date
        if (window.employeeJoiningDate) {
            const joiningDate = new Date(window.employeeJoiningDate);
            const selectedStart = new Date(startDate);
            const selectedEnd = new Date(endDate);

            if (selectedStart < joiningDate || selectedEnd < joiningDate) {
                showAttendanceToast('Selected date range cannot be before your joining date', 'error');
                return;
            }
        }

        // Store selected dates globally
        selectedStartDate = startDate;
        selectedEndDate = endDate;

        // Reload attendance data with new date range
        loadAttendanceData(startDate, endDate);

        showAttendanceToast('Date range applied successfully', 'success');
    });

    // Clear date range filter
    $('#clearDateFilter').click(function() {
        $('#startDate').val('');
        $('#endDate').val('');
        selectedStartDate = null;
        selectedEndDate = null;

        // Reload attendance data for current month
        loadAttendanceData();

        showAttendanceToast('Date range cleared', 'info');
    });

    // Load initial data (current month)
    loadAttendanceData();
});

// Load attendance data with date range
function loadAttendanceData(startDate = null, endDate = null) {
    let url = 'include/api/userattendance.php';
    const params = new URLSearchParams();

    if (startDate && endDate) {
        // Use date range
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    } else {
        // Use current month (default)
        const currentMonth = new Date().getMonth() + 1;
        const currentYear = new Date().getFullYear();
        params.append('year', currentYear);
        params.append('month', currentMonth);
    }

    url += '?' + params.toString();

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderAttendanceList(response.data);
            } else {
                showAttendanceToast('No attendance records found', 'info');
                $('#attendanceGrid').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; color: #00bfa5;"></i>
                        <h5 class="mt-3" style="color: #00bfa5;">No Attendance Found</h5>
                        <p class="text-muted">No attendance records found for the selected date range.</p>
                    </div>
                `);
            }
        },
        error: function() {
            showAttendanceToast('Error loading attendance data', 'error');
        }
    });
}

// Toast function
function showAttendanceToast(msg, type = 'success') {
    // Remove any existing toasts first to prevent duplicates
    $('.toast').remove();
    $('.alert').remove();
    $('[role="alert"]').remove();

    // Disable topbar toast system to prevent duplicates
    // if (typeof showTopbarToast === 'function') {
    //     try {
    //         const success = showTopbarToast(msg, type);
    //         if (success) {
    //             return;
    //         }
    //     } catch (error) {
    //         console.log('showTopbarToast failed, using fallback:', error);
    //         // Continue to fallback toast creation
    //     }
    // }

    // Create toast with different types
    const toastClass = type === 'error' ? 'text-bg-danger' :
        type === 'warning' ? 'text-bg-warning' :
        type === 'info' ? 'text-bg-info' : 'text-bg-success';

    const toastHtml = `
        <div class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${msg}
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

    // Fallback to attendance page toast
    var toastEl = document.getElementById('attendanceToast');
    var toastMsg = document.getElementById('attendanceToastMsg');
    if (toastEl && toastMsg) {
        toastMsg.textContent = msg;
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(type === 'danger' ? 'text-bg-danger' : 'text-bg-success');
        var fallbackToast = new bootstrap.Toast(toastEl, {
            delay: 2000
        });
        fallbackToast.show();
    }
}

function formatTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    let hours = date.getHours();
    let minutes = date.getMinutes();
    let ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    return hours + ':' + minutes + ' ' + ampm;
}

function renderAttendanceList(attendanceData) {
    var grid = $('#attendanceGrid');
    grid.empty();

    if (!attendanceData || attendanceData.length === 0) {
        grid.attr('style', 'display:flex;justify-content:center;align-items:center;min-height:60vh;');
        grid.append(`
            <div class="empty-state-card text-center p-5" style="max-width:500px;width:100%;">
                <div style="font-size:48px;color:#10bfae;margin-bottom:16px;">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h4 style="color: #009688;font-weight:600;">No Attendance Found</h4>
                <p style="color:#666;">
                    No attendance records found for this month.
                    Use the Attendance Actions button above to check in.
                </p>
            </div>
        `);
        return;
    } else {
        grid.removeAttr('style');
    }

    // Filter: har workday ki sirf ek row (preferably non-absent)
    const dateMap = {};
    attendanceData.forEach(function(attendance) {
        const date = attendance.workday;
        // Agar pehle se absent hai aur ab non-absent aa rahi hai, to overwrite karo
        if (!dateMap[date] || (dateMap[date].status === 'absent' && attendance.status !== 'absent')) {
            dateMap[date] = attendance;
        }
    });
    const filteredData = Object.values(dateMap);

    // Sirf weekdays (Monday=1, ..., Friday=5) ke cards show karo
    // const weekdaysData = filteredData.filter(attendance => {
    //     if (!attendance.workday) return false;
    //     const [year, month, day] = attendance.workday.split('-');
    //     const dateObj = new Date(year, month - 1, day);
    //     const dayOfWeek = dateObj.getDay(); // Sunday=0, Monday=1, ..., Saturday=6
    //     return dayOfWeek >= 1 && dayOfWeek <= 5;
    // });
    const weekdaysData = filteredData; // Ab sab din show honge

    weekdaysData.forEach(function(attendance) {
        let statusClass = '';
        let status = attendance.status ? attendance.status.charAt(0).toUpperCase() + attendance.status.slice(1) : '-';
        if (attendance.status === 'absent') {
            status = 'Absent';
            statusClass = 'status-absent';
        } else if (!attendance.check_out) {
            status = 'Present';
            statusClass = 'status-present';
        } else {
            if (attendance.status === 'present') statusClass = 'status-present';
            else if (attendance.status === 'late') statusClass = 'status-late';
            else if (attendance.status === 'half-day') statusClass = 'status-half-day';
            else if (attendance.status === 'early-leave') statusClass = 'status-early-leave';
            else statusClass = 'status-absent';
        }

        let checkIn = attendance.check_in_formatted ? attendance.check_in_formatted : formatTime(attendance.check_in);
        let checkOut = attendance.check_out_formatted ? attendance.check_out_formatted : formatTime(attendance.check_out);
        let workingHrs = attendance.working_hrs ? attendance.working_hrs : '-';
        let reason = attendance.reason ? attendance.reason : '';
        let displayDate = '-';
        if (attendance.workday) {
            const [year, month, day] = attendance.workday.split('-');
            const dateObj = new Date(year, month - 1, day); // JS months are 0-indexed
            const dayName = dateObj.toLocaleDateString('en-US', {
                weekday: 'long'
            });
            displayDate = `${dayName}, ${day}-${month}-${year}`;
        }

        let msgTime = attendance.msg_time ? attendance.msg_time : '';
        // Format msg_time
        let msgTimeFormatted = '';
        if (msgTime) {
            const dt = new Date(msgTime.replace(' ', 'T'));
            const day = dt.getDate().toString().padStart(2, '0');
            const month = (dt.getMonth() + 1).toString().padStart(2, '0');
            const year = dt.getFullYear();
            let hours = dt.getHours();
            let minutes = dt.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            msgTimeFormatted = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
        }
        // Reason display logic
        let reasonDisplay = '';
        let reasonInputBlock = '';
        if (reason) {
            reasonDisplay = `<div class='attendance-reason-text' id='reason_text_${attendance.attendance_id}' style='margin-top:8px; display:flex; align-items:center; gap:10px;'>
                <span style='font-weight:600; color:#555;'>Message:</span>
                <span style='color:#222;'>${reason}</span>
                ${msgTimeFormatted ? `<span style='font-size:0.90em; color:#10bfae; margin-left:4px;'>${msgTimeFormatted}</span>` : ''}
                <button class='btn btn-sm btn-outline-primary edit-icon-btn- reason-edit-btn ms-auto' data-attendance-id='${attendance.attendance_id}' title='Edit'><i class='fas fa-pen-to-square'></i></button>
            </div>`;
            reasonInputBlock = `<div class='attendance-reason-input' id='reason_input_block_${attendance.attendance_id}' style='display:none;'>
                <div class="d-flex align-items-center gap-2">
                    <input type='text' class='form-control reason-input' id='reason_input_${attendance.attendance_id}' value="${reason.replace(/"/g, '&quot;') || ''}" placeholder='Send a message' style='flex:1;' />
                    <button class='send-reason-btn ms-2' data-attendance-id='${attendance.attendance_id}' title='Send'><i class='fas fa-paper-plane'></i></button>
                </div>
            </div>`;
        } else {
            reasonDisplay = '';
            reasonInputBlock = `<div class='attendance-reason-input' id='reason_input_block_${attendance.attendance_id}'>
                <div class="d-flex align-items-center gap-2">
                    <input type='text' class='form-control reason-input' id='reason_input_${attendance.attendance_id}' value="" placeholder='Send a message' style='flex:1;' />
                    <button class='send-reason-btn ms-2' data-attendance-id='${attendance.attendance_id}' title='Send'><i class='fas fa-paper-plane'></i></button>
                </div>
            </div>`;
        }

        grid.append(`
            <div class="attendance-card attendance-card-clickable" data-attendance='${JSON.stringify(attendance)}'>
                <div class="attendance-header">
                    <div class="attendance-date">
                        <i class="fas fa-calendar-alt"></i>
                        <span>${displayDate}</span>
                    </div>
                    <span class="attendance-status ${statusClass}">${status}</span>
                </div>
                <div class="attendance-time">
                    <div class="time-block">
                        <div class="time-label">
                            <i class="fas fa-sign-in-alt"></i>
                            Check In
                        </div>
                        <div class="time-value">${checkIn}</div>
                    </div>
                    <div class="time-block">
                        <div class="time-label">
                            <i class="fas fa-sign-out-alt"></i>
                            Check Out
                        </div>
                        <div class="time-value">${checkOut}</div>
                    </div>
                </div>
                <div class="attendance-duration">
                    <div class="duration-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span>Working Hours: ${workingHrs}</span>
                    <button class="break-records-btn" data-attendance-id="${attendance.attendance_id}" title="View Break Records" style="margin-left: auto;">
                        <i class="fa-solid fa-utensils"></i>
                    </button>
                </div>
            </div>
        `);
    });

    // Card click event to open modal
    $('.attendance-card-clickable').off('click').on('click', function(e) {
        // Don't open modal if break button was clicked
        if ($(e.target).closest('.break-records-btn').length) {
            return;
        }
        const attendance = $(this).data('attendance');
        showAttendanceBootstrapModal(attendance);
    });

    // Break records button click event
    $('.break-records-btn').off('click').on('click', function(e) {
        e.stopPropagation(); // Prevent card click event
        const attendanceId = $(this).data('attendance-id');
        showBreakRecordsModal(attendanceId);
    });

    // Save button click handler
    $('.send-reason-btn').off('click').on('click', function() {
        var attendanceId = $(this).data('attendance-id');
        var reasonVal = $(`#reason_input_${attendanceId}`).val();
        if (!reasonVal || reasonVal.trim() === '') {
            showAttendanceToast('Please enter a message before sending.', 'danger');
            $(`#reason_input_${attendanceId}`).focus();
            return;
        }
        updateReason(attendanceId, reasonVal);
    });

    // Edit button click handler
    $('.reason-edit-btn').off('click').on('click', function() {
        var attendanceId = $(this).data('attendance-id');
        $(`#reason_text_${attendanceId}`).hide();
        $(`#reason_input_block_${attendanceId}`).show();
        $(`#reason_input_${attendanceId}`).focus();
    });
}

function updateReason(attendanceId, reason, closeModal = false) {
    // Use central handler for reason update
    markAttendance({
        action: 'update_reason',
        attendance_id: attendanceId,
        reason: reason
    }, function(response) {
        if (response.success) {
            showAttendanceToast('Message/Reason updated!', 'success');
            // Immediate update if trigger_update is true
            if (response.trigger_update) {
                loadAttendanceDataSilently();
            } else {
                loadAttendanceData();
            }
            if (closeModal) {
                $('#attendanceDetailsModal').modal('hide');
            }
        } else {
            showAttendanceToast(response.message || 'Error updating reason', 'danger');
        }
    });
}

// Add event listeners when document is ready
$(document).ready(function() {
    // Check-in button click handler
    $('#checkInBtn').click(function(e) {
        e.preventDefault();
        checkIn();
    });

    // Check-out button click handler
    $('#checkOutBtn').click(function(e) {
        e.preventDefault();
        checkOut();
    });

    // Month picker ko default current month pe set karo
    var now = new Date();
    var month = (now.getMonth() + 1).toString().padStart(2, '0');
    var year = now.getFullYear();
    // Month picker removed - using date range instead
    loadAttendanceData();

    // Start real-time polling
    startPolling();

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });

    // Handle modal open/close
    $(document).on('show.bs.modal', function() {
        stopPolling();
    });

    $(document).on('hidden.bs.modal', function() {
        startPolling();
    });
});

function checkIn(reason = '') {
    // Use central handler for check-in
    markAttendance({
        action: 'check_in',
        reason: reason
    }, function(response) {
        if (response.success) {
            showAttendanceToast('Checked in successfully!', 'success');
            // Start working hours timer if function exists
            if (typeof startWorkingHoursTimer === 'function') {
                startWorkingHoursTimer(new Date());
            }
            // Immediate update if trigger_update is true
            if (response.trigger_update) {
                loadAttendanceDataSilently();
            } else {
                loadAttendanceData();
            }
        } else {
            showAttendanceToast(response.message || 'Error checking in', 'danger');
        }
    });
}

function checkOut() {
    // Use central handler for check-out
    markAttendance({
        action: 'check_out'
    }, function(response) {
        if (response.success) {
            showAttendanceToast('Checked out successfully!', 'success');
            // Stop working hours timer if function exists
            if (typeof stopWorkingHoursTimer === 'function') {
                stopWorkingHoursTimer();
            }
            // Immediate update if trigger_update is true
            if (response.trigger_update) {
                loadAttendanceDataSilently();
            } else {
                loadAttendanceData();
            }
        } else {
            showAttendanceToast(response.message || 'Error checking out', 'danger');
        }
    });
}

function formatAMPM(date) {
    let hours = date.getHours();
    let minutes = date.getMinutes();
    let ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    return hours + ':' + minutes + ' ' + ampm;
}

function getCurrentDate() {
    const today = new Date();
    return today.toLocaleDateString('en-US');
}

// Month picker removed - using date range instead


// Centralized attendance handler call
function markAttendance(data, callback) {
    $.ajax({
        url: '../admin/include/api/attendance_handler.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            callback(response);
        },
        error: function(xhr, status, error) {
            callback({
                success: false,
                message: 'Attendance handler error: ' + error
            });
        }
    });
}

// Example usage (replace old insert/update attendance AJAX calls with this):
// markAttendance({emp_id: ..., date: ..., check_in: ..., check_out: ..., status: ..., reason: ...}, function(res) { ... });

// Bootstrap modal show function
function showAttendanceBootstrapModal(attendance) {
    // Format date, time, etc.
    let displayDate = '-';
    if (attendance.workday) {
        const [year, month, day] = attendance.workday.split('-');
        const dateObj = new Date(year, month - 1, day);
        const dayName = dateObj.toLocaleDateString('en-US', {
            weekday: 'long'
        });
        displayDate = `${dayName}, ${day}-${month}-${year}`;
    }
    let msgTimeFormatted = '';
    if (attendance.msg_time) {
        const dt = new Date(attendance.msg_time.replace(' ', 'T'));
        const day = dt.getDate().toString().padStart(2, '0');
        const month = (dt.getMonth() + 1).toString().padStart(2, '0');
        const year = dt.getFullYear();
        let hours = dt.getHours();
        let minutes = dt.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        msgTimeFormatted = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
    }
    // Modal body HTML
    const modalBodyHtml = `
      <!-- Attendance Details -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="detail-item">
            <div class="detail-label">
              <i class="fas fa-calendar me-2" style="color: var(--button-color);"></i><strong> Date : </strong> <span class="detail-value">${displayDate}</span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-item">
            <div class="detail-label">
              <i class="fas fa-info-circle me-2" style="color: var(--button-color);"></i><strong> Status : </strong> <span class="detail-value">${attendance.status ? attendance.status.charAt(0).toUpperCase() + attendance.status.slice(1) : '-'}</span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-item">
            <div class="detail-label">
              <i class="fas fa-clock me-2" style="color: var(--button-color);"></i><strong> Check In : </strong> <span class="detail-value">${attendance.check_in_formatted || '-'}</span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-item">
            <div class="detail-label">
              <i class="fas fa-sign-out-alt me-2" style="color: var(--button-color);"></i> <strong> Check Out : </strong> <span class="detail-value">${attendance.check_out_formatted || '-'}</span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-item">
            <div class="detail-label">
              <i class="fas fa-hourglass-half me-2" style="color: var(--button-color);"></i><strong> Working Hours : </strong> <span class="detail-value">${attendance.working_hrs || '-'}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Message Section -->
      <div class="message-section">
        <div class="detail-item position-relative">
          ${msgTimeFormatted ? `
          <div class="position-absolute top-0 end-0 text-dark small" style="padding: 0.5rem;">
            <i class="fas fa-clock me-1" style="color: var(--button-color);"></i>${msgTimeFormatted}
          </div>
          ` : ''}
          <div class="detail-label mt-4">
            <i class="fas fa-comment me-2" style="color: var(--button-color);"></i><strong> Message : </strong> <span class="detail-value">${attendance.reason || '-'}</span>
            <button class='btn btn-sm btn-outline-primary reason-edit-btn ms-2' data-attendance-id='${attendance.attendance_id}' title='Edit'><i class='fas fa-pen-to-square'></i></button>
          </div>
        </div>
        <div class='attendance-reason-input' id='reason_input_block_${attendance.attendance_id}' style='display:none;'>
          <div class="d-flex align-items-center gap-2">
              <input type='text' class='form-control reason-input' id='reason_input_${attendance.attendance_id}' value="${attendance.reason ? attendance.reason.replace(/"/g, '&quot;') : ''}" placeholder='Send a message' style='flex:1;' />
              <button class='send-reason-btn ms-2' data-attendance-id='${attendance.attendance_id}' title='Send'><i class='fas fa-paper-plane'></i></button>
          </div>
        </div>
      </div>
    `;
    $('#attendanceDetailsModalBody').html(modalBodyHtml);
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('attendanceDetailsModal'));
    modal.show();
    // Edit button event
    $('.reason-edit-btn').off('click').on('click', function(e) {
        e.stopPropagation();
        var attendanceId = $(this).data('attendance-id');
        $(`#reason_input_block_${attendanceId}`).show();
        $(this).hide();
        $(`#reason_input_${attendanceId}`).focus();
    });
    // Send button event
    $('.send-reason-btn').off('click').on('click', function(e) {
        e.stopPropagation();
        var attendanceId = $(this).data('attendance-id');
        var reasonVal = $(`#reason_input_${attendanceId}`).val();
        if (!reasonVal || reasonVal.trim() === '') {
            showAttendanceToast('Please enter a message before sending.', 'danger');
            $(`#reason_input_${attendanceId}`).focus();
            return;
        }
        updateReason(attendanceId, reasonVal, true);
    });
}

// Function to show break records modal
function showBreakRecordsModal(attendanceId) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('breakRecordsModal'));
    modal.show();
    
    // Load break records
    loadBreakRecords(attendanceId);
}

// Function to load break records for specific attendance
function loadBreakRecords(attendanceId) {
    $.ajax({
        url: 'include/api/break.php',
        type: 'GET',
        data: { 
            action: 'get_break_by_attendance',
            attendance_id: attendanceId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.breaks && response.breaks.length > 0) {
                displayBreakRecordsInModal(response.breaks);
            } else {
                $('#breakRecordsModalBody').html(`
                    <div class="text-center py-4">
                        <i class="fa-solid fa-utensils text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Break Records</h5>
                        <p class="text-muted">No break records found for this attendance.</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#breakRecordsModalBody').html(`
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Error Loading Break Records</h5>
                    <p>Please try again later.</p>
                </div>
            `);
        }
    });
}

// Function to display break records in modal
function displayBreakRecordsInModal(breaks) {
    let html = `
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white;">
                    <tr>
                        <th>#</th>
                        <th>Break Started</th>
                        <th>Break Ended</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    let totalSeconds = 0;
    
    breaks.forEach(function(breakRecord, index) {
        const startDateTime = new Date(breakRecord.break_start);
        const startTime = startDateTime.toLocaleTimeString('en-US', {hour12: true});
        
        let endTime = '-';
        if (breakRecord.break_end) {
            const endDateTime = new Date(breakRecord.break_end);
            endTime = endDateTime.toLocaleTimeString('en-US', {hour12: true});
        }
        
        const duration = breakRecord.break_duration || '-';
        
        // Calculate total break time in seconds
        if (breakRecord.status === 'completed' && breakRecord.break_start && breakRecord.break_end) {
            const start = new Date(breakRecord.break_start);
            const end = new Date(breakRecord.break_end);
            const durationInSeconds = Math.floor((end - start) / 1000);
            totalSeconds += durationInSeconds;
        }
        
        let statusBadge = '';
        if (breakRecord.status === 'active') {
            statusBadge = '<span class="badge bg-danger">Active</span>';
        } else {
            statusBadge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Completed</span>';
        }
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${startTime}</td>
                <td>${endTime}</td>
                <td>${duration}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });
    
    // Format total break time
    const totalHours = Math.floor(totalSeconds / 3600);
    const totalMinutes = Math.floor((totalSeconds % 3600) / 60);
    const totalSecs = totalSeconds % 60;
    
    let totalBreakTimeStr = '';
    if (totalHours > 0) {
        totalBreakTimeStr = `${totalHours} hour${totalHours > 1 ? 's' : ''} ${totalMinutes} minute${totalMinutes !== 1 ? 's' : ''}`;
    } else if (totalMinutes > 0) {
        totalBreakTimeStr = `${totalMinutes} minute${totalMinutes > 1 ? 's' : ''} ${totalSecs} second${totalSecs !== 1 ? 's' : ''}`;
    } else {
        totalBreakTimeStr = `${totalSecs} second${totalSecs !== 1 ? 's' : ''}`;
    }
    
    html += `
                </tbody>
            </table>
        </div>
        <div class="mt-3 p-3" >
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0"><i class="fas fa-list-ol me-2" style="color: #00bfa5;"></i><strong>Total Breaks:</strong> ${breaks.length}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><i class="fas fa-clock me-2" style="color: #00bfa5;"></i><strong>Total Break Time:</strong> ${totalBreakTimeStr || '0 seconds'}</p>
                </div>
            </div>
        </div>
    `;
    
    $('#breakRecordsModalBody').html(html);
}