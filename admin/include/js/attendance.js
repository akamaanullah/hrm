// Inject Edit Attendance Modal HTML at the very top so it always exists
(function() {
    if (!document.getElementById('editAttendanceModal')) {
        const modalHTML = `
        <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="editAttendanceForm">
                  <div class="mb-2">
                    <label for="editCheckIn" class="form-label">Check In</label>
                    <input type="time" class="form-control" id="editCheckIn" name="check_in">
                  </div>
                  <div class="mb-2">
                    <label for="editCheckOut" class="form-label">Check Out</label>
                    <input type="time" class="form-control" id="editCheckOut" name="check_out">
                  </div>
                  <input type="hidden" id="editAttendanceDate">
                  <input type="hidden" id="editAttendanceEmpId">
                </form>
              </div>
              <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveAttendanceEdit">Save</button>
              </div>
            </div>
          </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
})();

// Attendance Management JavaScript
// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

$(document).ready(function() {
    // Load departments for filter
    loadDepartments();

    // Load attendance on page load (only once)
    loadAttendance();

    // Check URL parameters for auto-opening modal (from notifications)
    const urlParams = new URLSearchParams(window.location.search);
    const openModal = urlParams.get('open_modal');
    const date = urlParams.get('date');
    const empId = urlParams.get('emp_id');

    if (openModal === 'true' && date && empId) {
        // Wait for attendance data to load, then open monthly report modal
        setTimeout(function() {

            // Get employee name from attendance table (if available)
            let employeeName = 'Employee';

            // Try to find employee name from the loaded attendance data
            $('#attendanceTable tbody tr').each(function() {
                const rowEmpId = $(this).find('td:first').text().trim();
                if (rowEmpId == empId) {
                    employeeName = $(this).find('td:eq(1)').text().trim();
                    return false; // break loop
                }
            });

            // Open monthly report modal
            viewMonthlyReport(empId, employeeName);

            // Clean URL (remove parameters without reloading page)
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }, 1500); // 1.5 second delay to ensure data is loaded
    }

    // Real-time updates via WebSockets (polling removed)
    // WebSocket connection will be handled separately

    // Silent attendance load (without destroying DataTable)
    window.loadAttendanceSilently = function(selectedDate = null) {

        let data = {};
        if (selectedDate) {
            data.date = selectedDate;
        }

        $.ajax({
            url: 'include/api/attendance.php',
            type: 'GET',
            data: data,
            success: function(response) {
                if (response.success) {
                    updateAttendanceTableSilently(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Silent attendance error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                // Silent error handling
            }
        });
    }

    // Update table silently without destroying DataTable
    window.updateAttendanceTableSilently = function(data) {
        const table = $('#attendanceTable').DataTable();
        if (!table) return;

        // Clear existing data
        table.clear();

        // Add new data
        data.forEach(function(record) {
            const formattedCheckIn = formatTime12(record.check_in);
            const formattedCheckOut = formatTime12(record.check_out);
            let formattedDate = '-';
            if (record.check_in) {
                formattedDate = formatDateDMY(record.check_in);
            } else if (record.date) {
                formattedDate = formatDateDMY(record.date);
            }

            let shiftInfo = '-';
            if (record.shift_name) {
                let start = record.shift_start_time ? formatTime12(record.shift_start_time) : '';
                let end = record.shift_end_time ? formatTime12(record.shift_end_time) : '';
                shiftInfo = `${record.shift_name} (${start} - ${end})`;
            }

            let status = record.status || 'Absent';
            let statusClass = record.status ? record.status.toLowerCase() : 'absent';

            table.row.add([
                record.emp_id,
                createFullName(record.first_name, record.middle_name, record.last_name) || '-',
                record.dept_name || 'Not Assigned',
                shiftInfo,
                formattedDate,
                (record.status === 'Absent' || !record.check_in || record.check_in === '00:00:00' || record.check_in.endsWith('00:00:00')) ? '-' : formattedCheckIn,
                (record.status === 'Absent' || !record.check_out || record.check_out === '00:00:00' || record.check_out.endsWith('00:00:00')) ? '-' : formattedCheckOut,
                record.working_hrs || '-',
                `<span class="attendance-status status-${statusClass}">${status}</span>`,
                `<button class="btn btn-sm btn-primary btn-view-report" onclick="viewMonthlyReport(${record.emp_id}, '${createFullName(record.first_name, record.middle_name, record.last_name) || 'Employee'}')">
                    <i class="fas fa-calendar-alt me-1"></i>View Report
                </button>`
            ]);
        });

        // Redraw table
        table.draw();
    }

    // WebSocket connection will be initialized here
    // Real-time updates will be handled via WebSocket events

    // Function to load departments
    function loadDepartments() {
        $.ajax({
            url: '../admin/include/api/department.php',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const departmentFilter = $('#departmentFilter');
                    departmentFilter.empty();
                    departmentFilter.append('<option value="">All Departments</option>');

                    response.data.forEach(function(dept) {
                        departmentFilter.append(`<option value="${dept.dept_name}">${dept.dept_name}</option>`);
                    });
                }
            }
        });
    }

    // Function to load attendance
    function loadAttendance(selectedDate = null) {
        let url = 'include/api/attendance.php';
        let data = {};

        // If date filter is selected, get attendance for that specific date
        if (selectedDate) {
            data.date = selectedDate;
        } else {}


        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function(response) {
                if (response.success) {

                    // DataTable destroy if already initialized
                    if ($.fn.DataTable.isDataTable('#attendanceTable')) {
                        $('#attendanceTable').DataTable().destroy();
                    }

                    const tbody = $('#attendanceTable tbody');
                    tbody.empty();


                    response.data.forEach(function(record, index) {

                        // Format check_in and check_out
                        const formattedCheckIn = formatTime12(record.check_in);
                        const formattedCheckOut = formatTime12(record.check_out);
                        // Format date (from check_in or record.date)
                        let formattedDate = '-';
                        if (record.check_in) {
                            formattedDate = formatDateDMY(record.check_in);
                        } else if (record.date) {
                            formattedDate = formatDateDMY(record.date);
                        }

                        // Determine status - if no attendance record, show "Absent"
                        let status = record.status || 'Absent';
                        let statusClass = record.status ? record.status.toLowerCase() : 'absent';


                        // Shift info
                        let shiftInfo = '-';
                        if (record.shift_name) {
                            let start = record.shift_start_time ? formatTime12(record.shift_start_time) : '';
                            let end = record.shift_end_time ? formatTime12(record.shift_end_time) : '';
                            shiftInfo = `${record.shift_name} (${start} - ${end})`;
                        }

                        tbody.append(`
                            <tr>
                                <td>${record.emp_id}</td>
                                <td>${createFullName(record.first_name, record.middle_name, record.last_name) || '-'}</td>
                                <td>${record.dept_name || 'Not Assigned'}</td>
                                <td>${shiftInfo}</td>
                                <td>${formattedDate}</td>
                                <td>${(record.status === 'Absent' || !record.check_in || record.check_in === '00:00:00' || record.check_in.endsWith('00:00:00')) ? '-' : formatTime12(record.check_in)}</td>
                                <td>${(record.status === 'Absent' || !record.check_out || record.check_out === '00:00:00' || record.check_out.endsWith('00:00:00')) ? '-' : formatTime12(record.check_out)}</td>
                                <td>${record.working_hrs || '-'}</td>
                                <td><span class="attendance-status status-${statusClass}">${status}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-view-report" 
                                            onclick="viewMonthlyReport(${record.emp_id}, '${createFullName(record.first_name, record.middle_name, record.last_name) || 'Employee'}')">
                                        <i class="fas fa-calendar-alt me-1"></i>View Report
                                    </button>
                                </td>
                            </tr>
                        `);
                    });

                    // Initialize DataTable
                    var table = $('#attendanceTable').DataTable({
                        dom: 'Blrtip',
                        buttons: [{
                            extend: 'collection',
                            text: '<i class="fas fa-download me-1"></i> Export',
                            className: 'btn btn-light text-secondary',
                            buttons: [{
                                    extend: 'excel',
                                    text: '<i class="fas fa-file-excel me-2 text-success"></i>Export to Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: '<i class="fas fa-file-csv me-2 text-primary"></i>Export to CSV',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                                    }
                                }
                            ]
                        }],
                        order: [
                            [4, 'desc']
                        ], // Date column descending (latest first) - changed from 3 to 4
                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, "All"]
                        ],
                        language: {
                            search: "Search:",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            lengthMenu: "Show _MENU_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });

                    // Move export buttons to container
                    table.buttons().container().appendTo('#exportButtonContainer');

                    // Custom filter for date range
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            var start = $('#startDate').val();
                            var end = $('#endDate').val();
                            var date = data[3]; // 4th column (index 3) is date

                            if (!start && !end) {
                                return true;
                            }

                            if (date && date !== '-') {
                                var dateVal = new Date(date);
                                var startVal = start ? new Date(start) : null;
                                var endVal = end ? new Date(end) : null;

                                if (
                                    (!startVal || dateVal >= startVal) &&
                                    (!endVal || dateVal <= endVal)
                                ) {
                                    return true;
                                }
                            }
                            return false;
                        }
                    );

                    // Bind filters
                    function applyFilters() {
                        var empId = $('#empIdFilter').val();
                        var name = $('#nameFilter').val();
                        var department = $('#departmentFilter').val();
                        var status = $('#statusFilter').val();

                        table.column(0).search(empId)
                            .column(1).search(name)
                            .column(2).search(department)
                            .column(8).search(status)
                            .draw();
                    }

                    // Real-time filter changes
                    $('#empIdFilter, #nameFilter').on('input', applyFilters);
                    $('#departmentFilter, #statusFilter').on('change', applyFilters);

                    // Date filter - reload data when date changes
                    $('#dateFilter').on('change', function() {
                        const selectedDate = $(this).val();

                        // Prevent multiple rapid calls
                        if (window.dateFilterTimeout) {
                            clearTimeout(window.dateFilterTimeout);
                        }

                        window.dateFilterTimeout = setTimeout(function() {
                            if (selectedDate) {
                                loadAttendance(selectedDate);
                            } else {
                                loadAttendance(); // Load all data if no date selected
                            }
                        }, 300); // 300ms delay to prevent rapid calls
                    });

                    // Handle form reset to clear filters
                    $('#attendanceFilterForm').on('reset', function() {
                        setTimeout(function() {
                            // Clear date filter and reload all data
                            $('#dateFilter').val('');
                            loadAttendance();
                        }, 0);
                    });

                    // Remove submit event for real-time filtering
                    $('#attendanceFilterForm').on('submit', function(e) {
                        e.preventDefault();
                    });
                }
            }
        });
    }

    // Save new attendance record
    $('#saveAttendance').click(function() {
        const formData = {
            action: 'admin_mark_attendance',
            emp_id: $('select[name="emp_id"]').val(),
            date: $('input[name="date"]').val(),
            check_in: $('input[name="check_in"]').val(),
            check_out: $('input[name="check_out"]').val(),
            status: $('select[name="status"]').val()
        };

        // Use central handler for attendance marking
        markAttendanceAdmin(formData, function(response) {
            if (response.success) {
                $('#addAttendanceModal').modal('hide');
                loadAttendance();
                alert('Attendance record added successfully');
            } else {
                alert('Error adding attendance record: ' + response.message);
            }
        });
    });

    // Export buttons render hone ke baad Auto Attendance ka button add karo
    setTimeout(function() {
        if ($('#autoAttendanceBtn').length === 0) {
            $('#exportButtonContainer').prepend(`
                <button id="autoAttendanceBtn" class="btn btn-success me-2" style="border-radius: 6px; font-weight: 500;">
                    <i class="fas fa-magic me-1"></i> Auto Attendance
                </button>
            `);
        }
    }, 500);

    // Button click event
    $(document).on('click', '#autoAttendanceBtn', function() {
        // Yahan apni functionality ya modal open karwana ho to code likh sakte hain
    });
});

// Global function to view monthly report
function viewMonthlyReport(empId, empName) {
    // Pehle employee ki joining date fetch karo
    $.ajax({
        url: 'include/api/employee.php',
        type: 'GET',
        success: function(employees) {
            const employee = employees.find(emp => emp.emp_id == empId);
            if (employee && employee.joining_date) {
                window.employeeJoiningDate = employee.joining_date;
            } else {
                window.employeeJoiningDate = null;
            }

            // Ab attendance API se shift info le aao
            $.ajax({
                url: 'include/api/attendance.php',
                type: 'GET',
                data: {
                    emp_id: empId,
                    year: new Date().getFullYear(),
                    month: new Date().getMonth() + 1
                },
                success: function(response) {
                    let shiftInfo = '';
                    if (response.success && response.data && response.data.length > 0) {
                        const rec = response.data[0];
                        if (rec.shift_name) {
                            let start = rec.shift_start_time ? formatTime12(rec.shift_start_time) : '';
                            let end = rec.shift_end_time ? formatTime12(rec.shift_end_time) : '';
                            shiftInfo = ` | Shift: ${rec.shift_name} (${start} - ${end})`;
                        }
                    }
                    $('#monthlyReportModalLabel').text(`Monthly Attendance Report - ${empName} (ID: ${empId})${shiftInfo}`);
                    $('#monthlyReportModal').modal('show');
                    // Initialize month calendar
                    initializeMonthCalendar(empId);
                }
            });
        }
    });
}

// Initialize month calendar
function initializeMonthCalendar(empId) {
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth();

    // Set current month and year
    window.currentDisplayMonth = currentMonth;
    window.currentDisplayYear = currentYear;
    window.currentEmpId = empId;

    // Get employee joining date and set min date for date inputs
    $.ajax({
        url: 'include/api/attendance.php',
        type: 'GET',
        data: {
            emp_id: empId,
            action: 'get_employee_info'
        },
        success: function(response) {
            if (response.success && response.employee && response.employee.joining_date) {
                window.employeeJoiningDate = response.employee.joining_date;
                // Set min date for date inputs
                $('#filterStartDate, #filterEndDate').attr('min', response.employee.joining_date);
            }
        }
    });

    // Check if previous button should be disabled based on joining date
    let previousDisabled = false;
    if (window.employeeJoiningDate) {
        const joiningDate = new Date(window.employeeJoiningDate);
        const currentDisplayDate = new Date(window.currentDisplayYear, window.currentDisplayMonth, 1);

        // Disable previous if current month is same as joining month or before
        previousDisabled = (currentDisplayDate.getFullYear() === joiningDate.getFullYear() &&
            currentDisplayDate.getMonth() <= joiningDate.getMonth());
    }

    // Create calendar HTML
    let calendarHTML = `
        <div class="row mb-3">
            <div class="col-6">
                <button class="btn btn-sm ${previousDisabled ? 'btn-outline-secondary' : 'btn-outline-primary'}" 
                        onclick="changeMonth(-1)" 
                        ${previousDisabled ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(1)">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div id="monthDisplay" class="text-center mb-3">
            <h5 id="currentMonthYear"></h5>
        </div>
        <div id="monthGrid"></div>
    `;

    $('#monthCalendar').html(calendarHTML);

    // Set current month and year
    window.currentDisplayMonth = currentMonth;
    window.currentDisplayYear = currentYear;
    window.currentEmpId = empId;

    renderMonthCalendar();
}

// Change month
function changeMonth(direction) {
    // Check if trying to go to previous month before joining date
    if (direction === -1 && window.employeeJoiningDate) {
        const joiningDate = new Date(window.employeeJoiningDate);
        const newMonth = window.currentDisplayMonth - 1;
        const newYear = window.currentDisplayYear;

        // If going to previous year
        if (newMonth < 0) {
            const prevYear = newYear - 1;
            const prevMonth = 11;

            // Check if previous year/month is before joining date
            if (prevYear < joiningDate.getFullYear() ||
                (prevYear === joiningDate.getFullYear() && prevMonth < joiningDate.getMonth())) {
                return; // Don't allow navigation
            }
        } else {
            // Check if previous month is before joining date
            if (newYear < joiningDate.getFullYear() ||
                (newYear === joiningDate.getFullYear() && newMonth < joiningDate.getMonth())) {
                return; // Don't allow navigation
            }
        }
    }

    window.currentDisplayMonth += direction;

    if (window.currentDisplayMonth > 11) {
        window.currentDisplayMonth = 0;
        window.currentDisplayYear++;
    } else if (window.currentDisplayMonth < 0) {
        window.currentDisplayMonth = 11;
        window.currentDisplayYear--;
    }

    renderMonthCalendar();
}

// Render month calendar
function renderMonthCalendar() {
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    $('#currentMonthYear').text(`${monthNames[window.currentDisplayMonth]} ${window.currentDisplayYear}`);

    const firstDay = new Date(window.currentDisplayYear, window.currentDisplayMonth, 1);
    const lastDay = new Date(window.currentDisplayYear, window.currentDisplayMonth + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDay = firstDay.getDay();

    let gridHTML = '<div class="row">';

    // Day headers
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayNames.forEach(day => {
        gridHTML += `<div class="col calendar-day-header">${day}</div>`;
    });
    gridHTML += '</div>';

    // Calendar days
    let dayCount = 1;
    let weekCount = 0;

    for (let i = 0; i < 6; i++) {
        gridHTML += '<div class="row">';
        for (let j = 0; j < 7; j++) {
            if ((i === 0 && j < startingDay) || dayCount > daysInMonth) {
                gridHTML += '<div class="col calendar-day empty"></div>';
            } else {
                const dateStr = `${window.currentDisplayYear}-${String(window.currentDisplayMonth + 1).padStart(2, '0')}-${String(dayCount).padStart(2, '0')}`;

                // Check if date is before joining date
                let isDisabled = false;
                if (window.employeeJoiningDate) {
                    const currentDate = new Date(window.currentDisplayYear, window.currentDisplayMonth, dayCount);
                    const joiningDate = new Date(window.employeeJoiningDate);

                    // Set time to 00:00:00 for both dates to avoid time zone issues
                    currentDate.setHours(0, 0, 0, 0);
                    joiningDate.setHours(0, 0, 0, 0);

                    // Disable only dates before joining date, not the joining date itself
                    isDisabled = currentDate < joiningDate;
                }

                const disabledClass = isDisabled ? ' disabled-date' : '';
                const disabledAttr = isDisabled ? ' style="opacity: 0.3; cursor: not-allowed;"' : '';
                const clickHandler = isDisabled ? '' : `onclick="selectDate('${dateStr}')"`;

                gridHTML += `
                    <div class="col calendar-day${disabledClass}" ${clickHandler} data-date="${dateStr}"${disabledAttr}>
                        ${dayCount}
                    </div>
                `;
                dayCount++;
            }
        }
        gridHTML += '</div>';
        if (dayCount > daysInMonth) break;
    }

    $('#monthGrid').html(gridHTML);

    // Update previous button state
    updatePreviousButtonState();

    // Load attendance data for the month
    loadMonthlyAttendance(window.currentEmpId, window.currentDisplayYear, window.currentDisplayMonth + 1);
}

// Update previous button state based on joining date
function updatePreviousButtonState() {
    if (window.employeeJoiningDate) {
        const joiningDate = new Date(window.employeeJoiningDate);
        const currentDisplayDate = new Date(window.currentDisplayYear, window.currentDisplayMonth, 1);

        // Disable previous if current month is same as joining month or before
        const shouldDisable = (currentDisplayDate.getFullYear() === joiningDate.getFullYear() &&
            currentDisplayDate.getMonth() <= joiningDate.getMonth());

        const prevButton = $('button[onclick="changeMonth(-1)"]');
        if (shouldDisable) {
            prevButton.addClass('btn-outline-secondary').removeClass('btn-outline-primary').prop('disabled', true);
        } else {
            prevButton.addClass('btn-outline-primary').removeClass('btn-outline-secondary').prop('disabled', false);
        }
    }
}

// Load monthly attendance data
function loadMonthlyAttendance(empId, year, month) {
    $.ajax({
        url: 'include/api/attendance.php',
        type: 'GET',
        data: {
            emp_id: empId,
            year: year,
            month: month
        },
        success: function(response) {
            if (response.success) {
                updateCalendarWithAttendance(response.data);
                generateMonthlySummary(response.data, year, month);
            }
        }
    });
}

// Update calendar with attendance data
function updateCalendarWithAttendance(attendanceData) {
    attendanceData.forEach(record => {
        const dateStr = record.date;
        const dayElement = $(`.calendar-day[data-date="${dateStr}"]`);

        if (dayElement.length) {
            dayElement.removeClass('has-attendance no-attendance late-attendance half-day-attendance');
            if (record.status && record.status.toLowerCase() === 'present') {
                dayElement.addClass('has-attendance');
                dayElement.attr('title', `Present - Check In: ${record.check_in}`);
            } else if (record.status && record.status.toLowerCase() === 'absent') {
                dayElement.addClass('no-attendance');
                dayElement.attr('title', 'Absent');
            } else if (record.status && record.status.toLowerCase() === 'late') {
                dayElement.addClass('late-attendance');
                dayElement.attr('title', `Late - Check In: ${record.check_in}`);
            } else if (record.status && record.status.toLowerCase() === 'half-day') {
                dayElement.addClass('half-day-attendance');
                dayElement.attr('title', `Half Day - Check In: ${record.check_in}`);
            } else {
                dayElement.addClass('has-attendance');
                dayElement.attr('title', `${record.status || 'Unknown'} - Check In: ${record.check_in}`);
            }
        }
    });
}

// Generate monthly summary
function generateMonthlySummary(attendanceData, year, month) {
    $("#attendanceSummaryArea").html(''); // Clear summary area
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const totalDays = new Date(year, month, 0).getDate();
    // User logic: present = present + late + halfday
    const presentDays = attendanceData.filter(record =>
        record.status && (
            record.status.toLowerCase() === 'present' ||
            record.status.toLowerCase() === 'late' ||
            record.status.toLowerCase() === 'half-day'
        )
    ).length;
    const absentDays = attendanceData.filter(record =>
        record.status && record.status.toLowerCase() === 'absent'
    ).length;
    const lateDays = attendanceData.filter(record =>
        record.status && record.status.toLowerCase() === 'late'
    ).length;
    const halfDays = attendanceData.filter(record =>
        record.status && record.status.toLowerCase() === 'half-day'
    ).length;

    // 2x2 grid + progress bar below
    const summaryGridHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card bg-success text-white summary-card">
                    <div class="card-body text-center">
                        <h4>${presentDays}</h4>
                        <small>Present Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-danger text-white summary-card">
                    <div class="card-body text-center">
                        <h4>${absentDays}</h4>
                        <small>Absent Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-warning text-white summary-card">
                    <div class="card-body text-center">
                        <h4>${lateDays}</h4>
                        <small>Late Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info text-white summary-card">
                    <div class="card-body text-center">
                        <h4>${halfDays}</h4>
                        <small>Half Days</small>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="progress attendance-progress">
                    <div class="progress-bar bg-success" style="width: ${(presentDays/totalDays)*100}%"></div>
                    <div class="progress-bar bg-danger" style="width: ${(absentDays/totalDays)*100}%"></div>
                    <div class="progress-bar bg-warning" style="width: ${(lateDays/totalDays)*100}%"></div>
                    <div class="progress-bar bg-info" style="width: ${(halfDays/totalDays)*100}%"></div>
                </div>
                <small class="text-muted">Attendance Rate: ${((presentDays/totalDays)*100).toFixed(1)}%</small>
            </div>
        </div>
    `;
    $("#attendanceSummaryArea").html(summaryGridHTML); // Inject new cards

    // Generate detailed table
    generateMonthlyTable(attendanceData);
}

// Helper function to format date as DD/MM/YYYY
function formatDateDMY(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

// Helper function to format time as 12-hour with AM/PM
function formatTime12(timeStr) {
    if (!timeStr || timeStr === '-') return '-';
    // If timeStr is full datetime, extract time part
    let t = timeStr;
    if (t.includes(' ')) t = t.split(' ')[1];
    if (!/^[0-9]{2}:[0-9]{2}/.test(t)) return timeStr;
    let [h, m, s] = t.split(':');
    h = parseInt(h);
    const ampm = h >= 12 ? 'pm' : 'am';
    h = h % 12;
    if (h === 0) h = 12;
    return `${h}:${m} ${ampm}`;
}

// Update generateMonthlyTable to use new formatters
function generateMonthlyTable(attendanceData) {
    // Filter: har date ki sirs ek (DB wali) row rakho, dummy absent row na ho
    const dateMap = {};
    const filteredData = [];
    attendanceData.forEach(record => {
        // Dummy absent row ki pehchan: status 'Absent' aur check_in '00:00:00'
        if (!dateMap[record.date] || (dateMap[record.date].status === 'Absent' && dateMap[record.date].check_in && dateMap[record.date].check_in.endsWith('00:00:00'))) {
            dateMap[record.date] = record;
        }
    });
    for (const d in dateMap) {
        filteredData.push(dateMap[d]);
    }
    // Ab filteredData ko render karo
    let tableHTML = `
        <table class="table table-sm monthly-report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Working Hours</th>
                    <th>Status</th>
                    <th>Message</th>
                    <th>Message Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    `;
    filteredData.forEach(record => {
        // Check if record date is before joining date
        const isBeforeJoiningDate = window.employeeJoiningDate && new Date(record.date) < new Date(window.employeeJoiningDate);

        let shiftInfo = '-';
        if (record.shift_name) {
            let start = record.shift_start_time ? formatTime12(record.shift_start_time) : '';
            let end = record.shift_end_time ? formatTime12(record.shift_end_time) : '';
            shiftInfo = `${record.shift_name} (${start} - ${end})`;
        }
        // Format message
        let msgCell = '-';
        if (record.reason) {
            let words = record.reason.split(' ');
            let shortMsg = words.length > 2 ? words.slice(0, 2).join(' ') + '...' : record.reason;
            msgCell = `<span>${shortMsg}</span>`;
        }
        // Format message time
        let msgTimeCell = '-';
        if (record.msg_time) {
            msgTimeCell = `${formatDateDMY(record.msg_time)} ${formatTime12(record.msg_time)}`;
        }

        // Add disabled styling for records before joining date
        const rowClass = isBeforeJoiningDate ? 'table-secondary' : '';
        const disabledStyle = isBeforeJoiningDate ? 'style="opacity: 0.5; color: #6c757d;"' : '';

        tableHTML += `
            <tr class="${rowClass}" ${disabledStyle}>
                <td>${formatDateDMY(record.date)}</td>
                <td>${shiftInfo}</td>
                <td>${(record.status === 'absent' || !record.check_in || record.check_in === '00:00:00' || record.check_in.endsWith('00:00:00')) ? '-' : formatTime12(record.check_in)}</td>
                <td>${(record.status === 'absent' || !record.check_out || record.check_out === '00:00:00' || record.check_out.endsWith('00:00:00')) ? '-' : formatTime12(record.check_out)}</td>
                <td>${record.working_hrs || '-'}</td>
                <td><span class="attendance-status status-${record.status ? record.status.toLowerCase() : ''}">${record.status || '-'}</span></td>
                <td>${msgCell}</td>
                <td>${msgTimeCell}</td>
                <td>
                    ${isBeforeJoiningDate ? 
                        '<span class="text-muted small">Before Joining</span>' :
                        `<button class="btn btn-sm btn-outline-primary" onclick="viewDailyDetail('${record.date}', ${window.currentEmpId})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="editAttendanceRecord('${record.date}', ${window.currentEmpId})" title="Edit">
                            <i class="far fa-edit"></i>
                        </button>`
                    }
                </td>
            </tr>
        `;
    });
    tableHTML += '</tbody></table>';
    $('#monthlyReportTable').html(tableHTML);
}

// Select date from calendar
function selectDate(dateStr) {
    viewDailyDetail(dateStr, window.currentEmpId);
}

// View daily attendance detail
function viewDailyDetail(date, empId) {
    $.ajax({
        url: 'include/api/attendance.php',
        type: 'GET',
        data: {
            emp_id: empId,
            date: date
        },
        success: function(response) {
            if (response.success && response.data) {
                const record = response.data;

                // Generate break details HTML
                let breakDetailsHTML = '';
                if (record.breaks && record.breaks.length > 0) {
                    breakDetailsHTML = `
                        <div class="break-section mb-4">
                            <h6 class="mb-3">
                                <i class="fa-solid fa-utensils me-2" style="color: var(--button-color);"></i>Break Details
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Started</th>
                                            <th>Ended</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    record.breaks.forEach(function(breakItem) {
                        const startTime = new Date(breakItem.break_start).toLocaleTimeString('en-US', {
                            hour12: true
                        });
                        const endTime = breakItem.break_end ? new Date(breakItem.break_end).toLocaleTimeString('en-US', {
                            hour12: true
                        }) : 'In Progress';
                        const statusBadge = breakItem.status === 'active' ?
                            '<span class="badge bg-warning">Active</span>' :
                            '<span class="badge" style="background: var(--button-color);">Completed</span>';

                        breakDetailsHTML += `
                            <tr>
                                <td>${startTime}</td>
                                <td>${endTime}</td>
                                <td>${breakItem.break_duration || 'In Progress'}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    });

                    breakDetailsHTML += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="break-summary bg-light p-3 rounded">
                                <div class="row text-center">
                                    <div class="col-6">
                                    <strong><small>Total Breaks</small></strong> :
                                        ${record.total_breaks}<br>
                                    </div>
                                    <div class="col-6">
                                    <strong><small >Total Time</small></strong> : 
                                       ${record.total_break_time || '0 minutes'}<br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    breakDetailsHTML = `
                        <div class="break-section mb-4">
                            <h6 class="mb-3">
                                <i class="fa-solid fa-utensils me-2" style="color: var(--button-color);"></i>Break Details
                            </h6>
                            <div class="text-center py-3 text-muted">
                                <i class="fa-solid fa-utensils mb-2" style="font-size: 1.5rem; opacity: 0.5; color: var(--button-color);"></i>
                                <p class="mb-0">No breaks taken on this day.</p>
                            </div>
                        </div>
                    `;
                }

                const detailHTML = `

                    <!-- Attendance Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar me-2" style="color: var(--button-color);"></i><strong> Date : </strong> <span class="detail-value">${formatDateDMY(record.date)}</span>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-info-circle me-2" style="color: var(--button-color);"></i><strong> Status : </strong> <span class="detail-value">${record.status || 'Unknown'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-clock me-2" style="color: var(--button-color);"></i><strong> Check In : </strong> <span class="detail-value">${(record.status === 'absent' || !record.check_in || record.check_in === '00:00:00' || record.check_in.endsWith('00:00:00')) ? '-' : formatTime12(record.check_in)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-sign-out-alt me-2" style="color: var(--button-color);"></i><strong> Check Out : </strong> <span class="detail-value">${(record.status === 'absent' || !record.check_out || record.check_out === '00:00:00' || record.check_out.endsWith('00:00:00')) ? '-' : formatTime12(record.check_out)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-hourglass-half me-2" style="color: var(--button-color);"></i><strong> Working Hours : </strong> <span class="detail-value">${record.working_hrs || '-'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${breakDetailsHTML}

                    <!-- Message -->
                    ${record.reason || record.msg_time ? `
                    <div class="message-section">
                        <div class="detail-item position-relative">
                            ${record.msg_time ? `
                            <div class="position-absolute top-0 end-0 text-dark small" style="padding: 0.5rem;">
                                <i class="fas fa-clock me-1" style="color: var(--button-color);"></i>${formatDateDMY(record.msg_time)} ${formatTime12(record.msg_time)}
                            </div>
                            ` : ''}
                            <div class="detail-label mt-4">
                                <i class="fas fa-comment me-2" style="color: var(--button-color);"></i><strong> Message : </strong> <span class="detail-value">${record.reason ? record.reason : '-'}</span>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                `;

                $('#dailyDetailContent').html(detailHTML);
                $('#dailyDetailModal').modal('show');
            } else {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Record',
                        text: 'No attendance record found for this date.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                    });
                } else {
                    alert('No attendance record found for this date.');
                }
            }
        }
    });
}

// Toast utility function
function showToast(message, type = 'danger') {
    let toastId = 'customToast';
    let toastContainer = $('#' + toastId);
    if (toastContainer.length === 0) {
        $('body').append(`
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-4" role="alert" aria-live="assertive" aria-atomic="true" style="z-index:9999; min-width:220px;">
                <div class="d-flex">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        toastContainer = $('#' + toastId);
    }
    toastContainer.find('.toast-body').text(message);
    toastContainer.removeClass('text-bg-danger text-bg-success text-bg-warning text-bg-info').addClass('text-bg-' + type);
    toastContainer.removeClass('bottom-0').addClass('top-0');
    const toast = new bootstrap.Toast(toastContainer[0]);
    toast.show();
}

// Date range filter event
$(document).on('click', '#applyDateRangeFilter', function() {
    const empId = window.currentEmpId;
    const startDate = $('#filterStartDate').val();
    const endDate = $('#filterEndDate').val();
    if (!startDate || !endDate) {
        showToast('Please select both Start and End date!', 'danger');
        return;
    }

    // Check if dates are before joining date
    if (window.employeeJoiningDate) {
        const joiningDate = new Date(window.employeeJoiningDate);
        const startDateObj = new Date(startDate);
        const endDateObj = new Date(endDate);

        if (startDateObj < joiningDate) {
            showToast('Start date cannot be before joining date!', 'danger');
            return;
        }
        if (endDateObj < joiningDate) {
            showToast('End date cannot be before joining date!', 'danger');
            return;
        }
    }
    // Always send startDate and endDate as YYYY-MM-DD
    $.ajax({
        url: 'include/api/attendance.php',
        type: 'GET',
        data: {
            emp_id: empId,
            start_date: startDate,
            end_date: endDate
        },
        success: function(response) {
            if (response.success) {
                // Console debugging for missing dates
                const presentDates = new Set(response.data.map(r => r.date));
                let current = new Date(startDate);
                const end = new Date(endDate);
                let missingDates = [];
                while (current <= end) {
                    const yyyy = current.getFullYear();
                    const mm = String(current.getMonth() + 1).padStart(2, '0');
                    const dd = String(current.getDate()).padStart(2, '0');
                    const dateStr = `${yyyy}-${mm}-${dd}`;
                    if (!presentDates.has(dateStr)) {
                        missingDates.push(dateStr);
                    }
                    current.setDate(current.getDate() + 1);
                }
                if (missingDates.length > 0) {} else {}
                // Use selected start and end date for summary
                generateMonthlySummary(response.data, startDate.split('-')[0], startDate.split('-')[1]);
            } else {
                $('#monthlyReportTable').html('<div class="alert alert-warning">Koi record nahi mila!</div>');
            }
        }
    });
});

// Clear date range filter event
$(document).on('click', '#clearDateRangeFilter', function() {
    $('#filterStartDate').val('');
    $('#filterEndDate').val('');
    // Reload default month data
    if (window.currentEmpId && window.currentDisplayYear && window.currentDisplayMonth !== undefined) {
        loadMonthlyAttendance(window.currentEmpId, window.currentDisplayYear, window.currentDisplayMonth + 1);
    }
});

// Date input validation for joining date
$(document).on('change', '#filterStartDate, #filterEndDate', function() {
    if (window.employeeJoiningDate) {
        const joiningDate = new Date(window.employeeJoiningDate);
        const selectedDate = new Date($(this).val());

        if (selectedDate < joiningDate) {
            showToast('Date cannot be before joining date!', 'danger');
            $(this).val('');
            return;
        }
    }
});

window.editAttendanceRecord = function(date, empId) {
    // Date ko YYYY-MM-DD format mein rakhna zaroori hai
    function toYMD(dateStr) {
        if (dateStr.includes('/')) {
            const [d, m, y] = dateStr.split('/');
            return `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
        }
        return dateStr;
    }
    const ymdDate = toYMD(date);

    // Modal fields ko pehle blank karo
    document.getElementById('editAttendanceDate').value = date;
    document.getElementById('editAttendanceEmpId').value = empId;
    document.getElementById('editCheckIn').value = '';
    document.getElementById('editCheckOut').value = '';

    // API se attendance record lao
    $.ajax({
        url: 'include/api/attendance.php',
        type: 'GET',
        data: {
            emp_id: empId,
            date: ymdDate
        },
        success: function(response) {
            if (response.success && response.data) {
                // Time ko input type="time" format (HH:mm) mein set karo
                let checkIn = '';
                let checkOut = '';
                if (response.data.check_in && response.data.check_in !== '00:00:00') {
                    let t = response.data.check_in;
                    if (t.length > 5 && t.includes(' ')) t = t.substring(11, 16); // "YYYY-MM-DD HH:mm:ss" -> "HH:mm"
                    else if (t.length > 5) t = t.substring(0, 5);
                    checkIn = t;
                }
                if (response.data.check_out && response.data.check_out !== '00:00:00') {
                    let t = response.data.check_out;
                    if (t.length > 5 && t.includes(' ')) t = t.substring(11, 16);
                    else if (t.length > 5) t = t.substring(0, 5);
                    checkOut = t;
                }
                document.getElementById('editCheckIn').value = checkIn;
                document.getElementById('editCheckOut').value = checkOut;
            }
            // Modal show karo (hamesha)
            const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
            modal.show();
        },
        error: function() {
            // Agar API fail ho jaye to bhi modal show karo (blank fields)
            const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
            modal.show();
        }
    });
}

// Save button logic (ek hi dafa bind ho)
if (!window._editAttendanceSaveHandler) {
    window._editAttendanceSaveHandler = true;
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'saveAttendanceEdit') {
            const date = document.getElementById('editAttendanceDate').value;
            const empId = document.getElementById('editAttendanceEmpId').value;
            const checkIn = document.getElementById('editCheckIn').value;
            const checkOut = document.getElementById('editCheckOut').value;

            // Validation: check-in/check-out blank ya 00:00 na ho
            if (!checkIn || !checkOut || checkIn === "00:00" || checkOut === "00:00") {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Input',
                        text: 'Check-in and Check-out time is required and cannot be 00:00. Please enter a valid time.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                    });
                } else {
                    // Fallback to alert
                    alert("Check-in and Check-out time is required and cannot be 00:00. Please enter a valid time.");
                }
                return;
            }

            // Use central handler for attendance edit
            markAttendanceAdmin({
                action: 'admin_edit_attendance',
                date: date,
                emp_id: empId,
                check_in: checkIn,
                check_out: checkOut
            }, function(data) {
                if (data.success) {
                    // Modal close
                    bootstrap.Modal.getInstance(document.getElementById('editAttendanceModal')).hide();
                    // WebSocket will handle real-time updates
                    loadMonthlyAttendance(window.currentEmpId, window.currentDisplayYear, window.currentDisplayMonth + 1);
                } else {
                    alert(data.message || 'Update failed!');
                }
            });
        }
    });
}

// Mark Attendance button logic (modal ke liye)
$(document).on('click', '#markAttendanceBtn', function() {
    var date = $('#autoAttendanceDate').val();
    if (!date) {
        Swal.fire({
            icon: 'warning',
            title: 'Date Required',
            text: 'Please select a date!'
        });
        return;
    }
    var checked = $('.autoEmpCheckbox:checked');
    if (checked.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Select Employee',
            text: 'Please select at least one employee!'
        });
        return;
    }
    var empIds = checked.map(function() {
        return $(this).val();
    }).get();

    // Use central handler for bulk attendance marking
    markAttendanceAdmin({
        action: 'bulk_mark_attendance',
        emp_ids: empIds,
        date: date,
        status: 'Present'
    }, function(data) {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('autoAttendanceModal')).hide();
            // WebSocket will handle real-time updates
            if (typeof loadAttendance === 'function') loadAttendance();
            let msg = '';
            if (typeof data.marked !== 'undefined' && typeof data.skipped !== 'undefined') {
                msg = `Attendance marked for <b>${data.marked}</b> employees.<br>`;
                if (data.skipped > 0) {
                    msg += `<span style="color:#888;">${data.skipped} already marked for this date.</span>`;
                }
            } else {
                msg = 'Attendance marked successfully!';
            }
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: msg,
                timer: 2200,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Attendance mark nahi hui!'
            });
        }
    });
});

// Centralized attendance handler call
function markAttendanceAdmin(data, callback) {
    $.ajax({
        url: 'include/api/attendance_handler.php',
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

// --- Auto Attendance Modal JS (moved from attendance.php) ---
function loadAutoDepartments() {
    fetch('include/api/department.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                var deptSelect = document.getElementById('autoDepartment');
                deptSelect.innerHTML = '<option value="">All Departments</option>';
                data.data.forEach(function(dept) {
                    var opt = document.createElement('option');
                    opt.value = dept.dept_name;
                    opt.textContent = dept.dept_name;
                    deptSelect.appendChild(opt);
                });
            }
        });
}

function loadAutoEmployees() {
    fetch('include/api/employee.php')
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data)) {
                // Filter active employees only
                const activeEmployees = data.filter(emp =>
                    (emp.status === 'active' || emp.status === null || emp.status === undefined) &&
                    (emp.is_deleted === 0 || emp.is_deleted === null || emp.is_deleted === undefined)
                );
                window._allAutoEmployees = activeEmployees;
                renderAutoEmployeeTable(activeEmployees);
            }
        });
}

function renderAutoEmployeeTable(employees) {
    var tbody = document.querySelector('#autoEmployeeTable tbody');
    tbody.innerHTML = '';
    employees.forEach(function(emp) {
        var tr = document.createElement('tr');
        tr.innerHTML = `<td><input type='checkbox' class='autoEmpCheckbox' value='${emp.emp_id}'></td>
                        <td>${emp.emp_id}</td>
                        <td>${createFullName(emp.first_name, emp.middle_name, emp.last_name)}</td>
                        <td>${emp.department || 'Not Assigned'}</td>`;
        tbody.appendChild(tr);
    });
}

if (!window._autoCheckAllHandler) {
    window._autoCheckAllHandler = true;
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'autoCheckAll') {
            var all = document.querySelectorAll('.autoEmpCheckbox');
            all.forEach(cb => cb.checked = e.target.checked);
        }
    });
}

function openAutoAttendanceModal() {
    loadAutoDepartments();
    loadAutoEmployees();
    var modal = new bootstrap.Modal(document.getElementById('autoAttendanceModal'));
    modal.show();
}
if (document.getElementById('autoAttendanceBtn')) {
    document.getElementById('autoAttendanceBtn').addEventListener('click', openAutoAttendanceModal);
}

function filterAutoEmployeeTable() {
    var dept = document.getElementById('autoDepartment').value.toLowerCase();
    var empId = document.getElementById('autoEmpId').value.toLowerCase();
    var empName = document.getElementById('autoEmpName').value.toLowerCase();
    var filtered = window._allAutoEmployees.filter(function(emp) {
        var matchDept = !dept || (emp.department && emp.department.toLowerCase() === dept);
        var matchId = !empId || (emp.emp_id && emp.emp_id.toString().toLowerCase().includes(empId));
        var matchName = !empName || (emp.first_name && (emp.first_name.toLowerCase().includes(empName) || (emp.middle_name && emp.middle_name.toLowerCase().includes(empName)) || (emp.last_name && emp.last_name.toLowerCase().includes(empName))));
        return matchDept && matchId && matchName;
    });
    renderAutoEmployeeTable(filtered);
}

if (document.getElementById('autoEmpId')) document.getElementById('autoEmpId').addEventListener('input', filterAutoEmployeeTable);
if (document.getElementById('autoEmpName')) document.getElementById('autoEmpName').addEventListener('input', filterAutoEmployeeTable);
if (document.getElementById('autoDepartment')) document.getElementById('autoDepartment').addEventListener('change', filterAutoEmployeeTable);

if (document.getElementById('markAttendanceBtn')) {
    document.getElementById('markAttendanceBtn').addEventListener('click', function() {
        var date = document.getElementById('autoAttendanceDate').value;
        if (!date) {
            Swal.fire({
                icon: 'warning',
                title: 'Date Required',
                text: 'Please select a date!'
            });
            return;
        }
        var checked = document.querySelectorAll('.autoEmpCheckbox:checked');
        if (checked.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Employee',
                text: 'Please select at least one employee!'
            });
            return;
        }
        var empIds = Array.from(checked).map(cb => cb.value);

        fetch('include/api/attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'bulk_mark_attendance',
                    emp_ids: empIds,
                    date: date,
                    status: 'Present'
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.text().then(text => {
                    if (!text) {
                        throw new Error('Empty response from server');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e, 'Response text:', text);
                        throw new Error('Invalid JSON response');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('autoAttendanceModal')).hide();
                    // WebSocket will handle real-time updates
                    if (typeof loadAttendance === 'function') loadAttendance();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance marked successfully!',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Attendance mark nahi hui!'
                    });
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Network error: ' + error.message
                });
            });
    });
}
// --- End Auto Attendance Modal JS ---
// sidebar mn attendance page pr leave dropdown ko close karna
// Fix dropdown issue specifically for attendance page
document.addEventListener('DOMContentLoaded', function() {
    // Force close leave management dropdown on attendance page
    const leaveSubmenu = document.getElementById('leaveSubmenu');
    const leaveDropdownTrigger = document.querySelector('[aria-controls="leaveSubmenu"]');

    if (leaveSubmenu && leaveDropdownTrigger) {
        // Force close the dropdown initially
        leaveSubmenu.classList.remove('show');
        leaveDropdownTrigger.setAttribute('aria-expanded', 'false');

        // Let Bootstrap handle the collapse functionality
        // Remove our custom event listener and let Bootstrap's data-bs-toggle work
        leaveDropdownTrigger.removeAttribute('data-bs-toggle');
        leaveDropdownTrigger.removeAttribute('href');

        // Add our own toggle functionality
        leaveDropdownTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                leaveSubmenu.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
            } else {
                leaveSubmenu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    }
});