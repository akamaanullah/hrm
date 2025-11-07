$(document).ready(function() {
    // Initialize date range with current month
    initializeDateRange();

    // Load all dashboard data
    loadUserDashboard();
    loadAttendanceAnalytics();
    loadSalaryTrend();
    loadLeaveAnalytics();
    loadWorkHoursAnalysis();
    loadNotifications();

    // Add event listener for date filter
    document.getElementById('applyDateFilter').addEventListener('click', function() {
        applyDateFilter();
    });

    // Add click event listeners for dashboard cards
    addCardClickListeners();
});

// Initialize date range (empty by default)
function initializeDateRange() {
    // Leave date fields empty - user will select manually
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
}

// Apply date filter
function applyDateFilter() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!startDate || !endDate) {
        console.error('Please select both start and end dates');
        return;
    }

    if (startDate > endDate) {
        console.error('Start date cannot be after end date');
        return;
    }


    // Store selected dates globally
    window.selectedStartDate = startDate;
    window.selectedEndDate = endDate;

    // Reload data with new date range
    loadUserDashboard(startDate, endDate);
    loadAttendanceAnalytics(startDate, endDate);
    loadWorkHoursAnalysis(startDate, endDate);
    loadLeaveAnalytics(startDate, endDate);
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}



// Load user dashboard data
function loadUserDashboard(startDate = null, endDate = null) {
    let url = 'include/api/dashboard.php';
    const params = new URLSearchParams();

    if (startDate && endDate) {
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    }

    if (params.toString()) {
        url += '?' + params.toString();
    }

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            if (data.success) {
                updateDashboardUI(data.data);
            } else {
                setDefaultValues();
            }
        })
        .catch(error => {
            setDefaultValues();
        });
}

// Update dashboard UI with data
function updateDashboardUI(data) {
    // Update Today's Status - check if date range is selected
    const startDate = window.selectedStartDate;
    const endDate = window.selectedEndDate;

    if (startDate && endDate) {
        // Date range is selected - show range summary
        if (data.attendance_count !== undefined && data.month_total) {
            const percentage = Math.round((data.attendance_count / data.month_total) * 100);
            const statusElement = document.getElementById('todayStatus');
            statusElement.innerHTML = `<span class="attendance-status status-present">${percentage}%</span>`;

            const dateRange = `${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}`;
            document.getElementById('checkInTime').textContent = `Period: ${dateRange}`;
        } else {
            // No data for selected range
            const statusElement = document.getElementById('todayStatus');
            statusElement.innerHTML = `<span class="attendance-status status-no-attendance">--</span>`;
            document.getElementById('checkInTime').textContent = 'No data for selected period';
        }
    } else {
        // No date range selected - show today's status
        if (data.today_attendance) {
            const statusElement = document.getElementById('todayStatus');
            const status = data.today_attendance.status || 'Not Marked';

            // Create status badge with icon
            let statusHtml = '';
            let statusColor = '#6b7280'; // Default gray

            if (data.today_attendance.is_overnight) {
                statusHtml = '<i class="fas fa-moon me-2"></i>Overnight Shift';
                statusColor = '#8b5cf6'; // Purple
            } else if (status.toLowerCase() === 'present') {
                statusHtml = '<i class="fas fa-check-circle me-2"></i>Present';
                statusColor = '#10b981'; // Green
            } else if (status.toLowerCase() === 'late') {
                statusHtml = '<i class="fas fa-clock me-2"></i>Late';
                statusColor = '#f59e0b'; // Orange
            } else if (status.toLowerCase() === 'half-day') {
                statusHtml = '<i class="fas fa-hourglass-half me-2"></i>Half Day';
                statusColor = '#3b82f6'; // Blue
            } else if (status.toLowerCase() === 'absent') {
                statusHtml = '<i class="fas fa-times-circle me-2"></i>Absent';
                statusColor = '#ef4444'; // Red
            } else {
                statusHtml = '<i class="fas fa-question-circle me-2"></i>' + status;
                statusColor = '#6b7280'; // Gray
            }

            // Create badge styling using CSS classes
            let badgeClass = 'attendance-status ';

            if (data.today_attendance.is_overnight) {
                badgeClass += 'status-overnight';
            } else if (status.toLowerCase() === 'present') {
                badgeClass += 'status-present';
            } else if (status.toLowerCase() === 'late') {
                badgeClass += 'status-late';
            } else if (status.toLowerCase() === 'half-day') {
                badgeClass += 'status-half-day';
            } else if (status.toLowerCase() === 'absent') {
                badgeClass += 'status-absent';
            } else {
                badgeClass += 'status-no-attendance';
            }

            statusElement.innerHTML = `<span class="${badgeClass}">${statusHtml}</span>`;

            document.getElementById('checkInTime').textContent = `Check-in: ${data.today_attendance.check_in || '--:--'}`;
        }
    }

    // Update Monthly Attendance
    if (data.attendance_count !== undefined && data.month_total) {
        const percentage = Math.round((data.attendance_count / data.month_total) * 100);
        document.getElementById('monthAttendancePercent').textContent = `${percentage}%`;
        document.getElementById('monthPresent').textContent = `Present: ${data.attendance_count}`;
        document.getElementById('monthTotal').textContent = `Total: ${data.month_total}`;
    }

    // Update Total Leave (Approved Leaves with Leave Types and Days)
    if (data.leaves_remaining) {
        const totalApproved = data.leaves_remaining.total || 0;
        const totalDays = data.leaves_remaining.total_days || 0;
        document.getElementById('leaveBalance').textContent = totalDays;

        // Create leave types breakdown with days
        let leaveTypesText = '';
        if (data.leaves_remaining.breakdown && data.leaves_remaining.breakdown.length > 0) {
            const leaveTypes = data.leaves_remaining.breakdown.map(item =>
                `${item.type_name}: ${item.days} days`
            );
            leaveTypesText = leaveTypes.join(' | ');
        } else {
            leaveTypesText = 'No approved leaves';
        }

        // Update the details section
        document.getElementById('annualLeaves').textContent = leaveTypesText;
        document.getElementById('sickLeaves').style.display = 'none'; // Hide the second line
    } else {
        // Set default values if no leave data
        document.getElementById('leaveBalance').textContent = '0';
        document.getElementById('annualLeaves').textContent = 'No approved leaves';
        document.getElementById('sickLeaves').style.display = 'none';
    }

    // Update Last Salary
    if (data.user_info) {
        const deptEl = document.getElementById('userDepartment');
        const desigEl = document.getElementById('userDesignation');
        if (deptEl) deptEl.textContent = data.user_info.department || '--';
        if (desigEl) desigEl.textContent = `Job Title: ${data.user_info.designation || '--'}`;
    }
}

// Set default values when API fails
function setDefaultValues() {
    document.getElementById('todayStatus').textContent = 'Not Marked';
    document.getElementById('checkInTime').textContent = 'Check-in: --:--';
    document.getElementById('monthAttendancePercent').textContent = '0%';
    document.getElementById('monthPresent').textContent = 'Present: 0';
    document.getElementById('monthTotal').textContent = 'Total: 0';
    document.getElementById('leaveBalance').textContent = '0';
    document.getElementById('annualLeaves').textContent = 'Annual: 0';
    document.getElementById('sickLeaves').textContent = 'Sick: 0';
    document.getElementById('lastSalaryAmount').textContent = '--';
    document.getElementById('lastSalaryMonth').textContent = 'Month: --';
}

// Load Attendance Analytics
function loadAttendanceAnalytics(startDate = null, endDate = null) {
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

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                createAttendanceDonutChart(data.data);
                updateAttendanceStats(data.data);
            }
        })
        .catch(error => {
            createSampleAttendanceChart();
        });
}

// Create Attendance Donut Chart
function createAttendanceDonutChart(attendanceData) {
    const statusCounts = {
        present: 0,
        absent: 0,
        late: 0,
        'half-day': 0
    };

    attendanceData.forEach(record => {
        if (statusCounts.hasOwnProperty(record.status)) {
            statusCounts[record.status]++;
        }
    });

    const chartData = [{
            name: 'Present',
            y: statusCounts.present,
            color: '#10b981'
        },
        {
            name: 'Absent',
            y: statusCounts.absent,
            color: '#ef4444'
        },
        {
            name: 'Late',
            y: statusCounts.late,
            color: '#f59e0b'
        },
        {
            name: 'Half-day',
            y: statusCounts['half-day'],
            color: '#3b82f6'
        }
    ];

    Highcharts.chart('attendanceDonutChart', {
        chart: {
            type: 'pie',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        plotOptions: {
            pie: {
                innerSize: '75%',
                dataLabels: {
                    enabled: false
                },
                borderWidth: 0,
                shadow: false
            }
        },
        series: [{
            name: 'Attendance',
            data: chartData
        }],
        credits: {
            enabled: false
        }
    });
}

// Update Attendance Stats Box
function updateAttendanceStats(attendanceData) {
    const statusCounts = {
        present: 0,
        absent: 0,
        late: 0,
        'half-day': 0
    };

    attendanceData.forEach(record => {
        if (statusCounts.hasOwnProperty(record.status)) {
            statusCounts[record.status]++;
        }
    });

    const total = Object.values(statusCounts).reduce((a, b) => a + b, 0);
    const presentPercentage = total > 0 ? Math.round((statusCounts.present / total) * 100) : 0;

    document.getElementById('attendanceStatsBox').innerHTML = `
        <div class="row g-1">
            <div class="col-6 col-md-3">
                <div class="attendance-stat-card" data-status="present" style="background: linear-gradient(135deg, #10b981, #059669); padding: 0.8rem; border-radius: 10px; color: white; text-align: center; cursor: pointer; transition: transform 0.2s ease;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">${statusCounts.present}</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Present</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">${presentPercentage}% of total</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="attendance-stat-card" data-status="absent" style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 0.8rem; border-radius: 10px; color: white; text-align: center; cursor: pointer; transition: transform 0.2s ease;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">${statusCounts.absent}</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Absent</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">No check-in</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="attendance-stat-card" data-status="late" style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 0.8rem; border-radius: 10px; color: white; text-align: center; cursor: pointer; transition: transform 0.2s ease;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">${statusCounts.late}</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Late</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">After time</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="attendance-stat-card" data-status="half-day" style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 0.8rem; border-radius: 10px; color: white; text-align: center; cursor: pointer; transition: transform 0.2s ease;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">${statusCounts['half-day']}</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Half-Days</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">Partial</div>
                </div>
            </div>
        </div>
    `;

    // Add click event handlers for attendance stat cards
    document.querySelectorAll('.attendance-stat-card').forEach(card => {
        card.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            showAttendanceByStatus(status);
        });

        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Date range functionality
$(document).ready(function() {
    // Toggle date range selector
    $('#dateRangeBtn').click(function() {
        $('#dateRangeSelector').slideToggle();
    });

    // Apply date range
    $('#applyDateRange').click(function() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        if (!startDate || !endDate) {
            showToast('Please select both start and end dates', 'warning');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            showToast('Start date cannot be after end date', 'error');
            return;
        }

        // Store selected dates globally
        window.selectedStartDate = startDate;
        window.selectedEndDate = endDate;

        // Reload attendance analytics with new date range
        loadAttendanceAnalytics(startDate, endDate);

        // Hide date range selector
        $('#dateRangeSelector').slideUp();

        showToast('Date range applied successfully', 'success');
    });

    // Clear date range
    $('#clearDateRange').click(function() {
        $('#startDate').val('');
        $('#endDate').val('');
        window.selectedStartDate = null;
        window.selectedEndDate = null;

        // Reload attendance analytics for current month
        loadAttendanceAnalytics();

        // Hide date range selector
        $('#dateRangeSelector').slideUp();

        showToast('Date range cleared', 'info');
    });
});


// Toast notification function
function showToast(message, type = 'info') {
    // Remove any existing toasts first to prevent duplicates
    $('.toast').remove();
    $('.alert').remove();
    $('[role="alert"]').remove();

    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
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

// Show attendance by status in modal
function showAttendanceByStatus(status) {
    // Get current date range (current month or selected range)
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;

    // Check if date range is selected
    const startDate = window.selectedStartDate || `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`;
    const endDate = window.selectedEndDate || new Date(currentYear, currentMonth, 0).toISOString().split('T')[0];

    // Fetch attendance data for the status
    $.ajax({
        url: 'include/api/userattendance.php',
        type: 'GET',
        data: {
            action: 'get_by_status',
            status: status,
            start_date: startDate,
            end_date: endDate
        },
        success: function(response) {
            if (response.success) {
                showAttendanceModal(status, response.data, startDate, endDate);
            } else {
                showToast('No attendance records found for ' + status, 'info');
            }
        },
        error: function() {
            showToast('Error loading attendance data', 'error');
        }
    });
}

// Show attendance modal
function showAttendanceModal(status, data, startDate, endDate) {
    const statusColors = {
        'present': '#10b981',
        'absent': '#ef4444',
        'late': '#f59e0b',
        'half-day': '#3b82f6'
    };

    const statusLabels = {
        'present': 'Present',
        'absent': 'Absent',
        'late': 'Late',
        'half-day': 'Half-Days'
    };

    const color = statusColors[status] || '#6b7280';
    const label = statusLabels[status] || status;

    let tableRows = '';
    if (data && data.length > 0) {
        data.forEach(record => {
            const date = record.workday ? new Date(record.workday).toLocaleDateString() : '-';
            const checkIn = record.check_in_formatted || '-';
            const checkOut = record.check_out_formatted || '-';
            const workingHours = record.working_hrs || '-';

            tableRows += `
                <tr>
                    <td>${date}</td>
                    <td>${checkIn}</td>
                    <td>${checkOut}</td>
                    <td>${workingHours}</td>
                    <td><span class="badge" style="background-color: ${color}; color: white;">${label}</span></td>
                </tr>
            `;
        });
    } else {
        tableRows = '<tr><td colspan="5" class="text-center">No records found</td></tr>';
    }

    const modalHtml = `
        <div class="modal fade" id="attendanceStatusModal" tabindex="-1" aria-labelledby="attendanceStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: var(--topbar-bg-light); color: white;">
                        <h5 class="modal-title" id="attendanceStatusModalLabel">
                            <i class="fas fa-calendar-check me-2"></i>
                            ${label} Attendance Records
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Date Range:</strong> ${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}
                        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Working Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                                    ${tableRows}
                </tbody>
            </table>
        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    $('#attendanceStatusModal').remove();

    // Add modal to body
    $('body').append(modalHtml);

    // Show modal
    $('#attendanceStatusModal').modal('show');

    // Remove modal from DOM when hidden
    $('#attendanceStatusModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Create Sample Attendance Chart
function createSampleAttendanceChart() {
    const sampleData = [{
            name: 'Present',
            y: 18,
            color: '#10b981'
        },
        {
            name: 'Absent',
            y: 2,
            color: '#ef4444'
        },
        {
            name: 'Late',
            y: 3,
            color: '#f59e0b'
        },
        {
            name: 'Half-day',
            y: 1,
            color: '#3b82f6'
        }
    ];

    Highcharts.chart('attendanceDonutChart', {
        chart: {
            type: 'pie',
            backgroundColor: 'transparent'
        },
        title: {
            text: 'Sample Data (API Error)',
            style: {
                fontSize: '14px',
                color: '#6b7280'
            }
        },
        plotOptions: {
            pie: {
                innerSize: '75%',
                dataLabels: {
                    enabled: false
                },
                borderWidth: 0,
                shadow: false
            }
        },
        series: [{
            name: 'Attendance',
            data: sampleData
        }],
        credits: {
            enabled: false
        }
    });

    document.getElementById('attendanceStatsBox').innerHTML = `
        <div class="row g-1">
            <div class="col-6 col-md-3">
                <div style="background: linear-gradient(135deg, #10b981, #059669); padding: 0.8rem; border-radius: 10px; color: white; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">18</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Present Days</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">75% of total</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 0.8rem; border-radius: 10px; color: white; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">2</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Absent Days</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">No check-in</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 0.8rem; border-radius: 10px; color: white; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">3</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Late Days</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">After time</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 0.8rem; border-radius: 10px; color: white; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem;">1</div>
                    <div style="font-size: 0.8rem; font-weight: 600; margin-bottom: 0.2rem;">Half Days</div>
                    <div style="font-size: 0.7rem; opacity: 0.9;">Partial</div>
                </div>
            </div>
        </div>
    `;
}

// Load Salary Trend
function loadSalaryTrend() {
    fetch('include/api/user-payroll-salary.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                createSalaryTrendChart(data);
            } else {
                showNoSalaryData();
            }
        })
        .catch(error => {
            showNoSalaryData();
        });
}

// Create Salary Trend Chart
function createSalaryTrendChart(payrollData) {
    const months = [];
    const salaries = [];

    const recentData = payrollData.slice(0, 6).reverse();

    recentData.forEach(item => {
        const date = new Date(item.payment_date);
        const monthName = date.toLocaleDateString('en-US', {
            month: 'short'
        });
        const year = date.getFullYear();
        months.push(`${monthName} ${year}`);
        salaries.push(parseFloat(item.net_salary));
    });

    // If only a single data point, render a compact column chart to avoid an empty-looking line
    if (salaries.length <= 1) {
        Highcharts.chart('salaryTrendChart', {
            chart: {
                type: 'column',
                backgroundColor: 'transparent'
            },
            title: {
                text: null
            },
            xAxis: {
                categories: months,
                title: {
                    text: 'Month'
                },
                labels: {
                    style: {
                        fontSize: '12px',
                        color: '#6b7280'
                    }
                },
                lineColor: '#e5e7eb',
                tickColor: '#e5e7eb'
            },
            yAxis: {
                title: {
                    text: 'Salary (Rs.)'
                },
                labels: {
                    style: {
                        fontSize: '12px',
                        color: '#6b7280'
                    }
                },
                gridLineColor: '#f3f4f6',
                lineColor: '#e5e7eb',
                min: 0,
                max: Math.ceil((salaries[0] || 0) * 1.2)
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.98)',
                borderColor: '#e5e7eb',
                borderRadius: 12,
                shadow: true,
                formatter: function() {
                    return `<span style="font-size: 14px; font-weight: 700; color: #1f2937;">${this.x}</span><br/>
                            <span style="color: #00bfa5; font-weight: 600;">●</span> Salary: <b>Rs. ${this.y.toLocaleString()}</b>`;
                }
            },
            plotOptions: {
                column: {
                    borderRadius: 6,
                    pointPadding: 0.1,
                    groupPadding: 0.25,
                    color: '#00bfa5',
                    dataLabels: {
                        enabled: true,
                        style: {
                            textOutline: 'none',
                            fontWeight: '600',
                            color: '#1f2937'
                        },
                        formatter: function() {
                            return `Rs. ${this.y.toLocaleString()}`;
                        }
                    }
                }
            },
            series: [{
                name: 'Net Salary',
                data: salaries
            }],
            credits: {
                enabled: false
            }
        });
        return;
    }

    // Default: line chart when we have multiple months
    Highcharts.chart('salaryTrendChart', {
        chart: {
            type: 'line',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: months,
            title: {
                text: 'Month'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            lineColor: '#e5e7eb',
            tickColor: '#e5e7eb'
        },
        yAxis: {
            title: {
                text: 'Salary (Rs.)'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            gridLineColor: '#f3f4f6',
            lineColor: '#e5e7eb'
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            borderColor: '#e5e7eb',
            borderRadius: 12,
            shadow: true,
            formatter: function() {
                return `<span style=\"font-size: 14px; font-weight: 700; color: #1f2937;\">${this.x}</span><br/>
                        <span style=\"color: #00bfa5; font-weight: 600;\">●</span> Salary: <b>Rs. ${this.y.toLocaleString()}</b>`;
            }
        },
        plotOptions: {
            line: {
                marker: {
                    enabled: true,
                    radius: 6,
                    lineWidth: 3,
                    lineColor: '#ffffff',
                    fillColor: '#00bfa5'
                },
                lineWidth: 3,
                color: '#00bfa5'
            }
        },
        series: [{
            name: 'Net Salary',
            data: salaries
        }],
        credits: {
            enabled: false
        }
    });
}

// Show No Salary Data Message
function showNoSalaryData() {
    document.getElementById('salaryTrendChart').innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem; opacity: 0.7;">
                <i class="fas fa-chart-line"></i>
            </div>
            <h5 style="color: #1f2937; font-weight: 700; margin-bottom: 0.5rem; font-size: 1.2rem;">No Salary Data Available</h5>
            <p style="font-size: 0.9rem; line-height: 1.5; max-width: 300px;">
                Salary trend will appear here once payroll data is available.
            </p>
        </div>
    `;
}

// Create Sample Salary Chart (keeping for reference)
function createSampleSalaryChart() {
    const sampleMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const sampleSalaries = [45000, 46000, 45000, 47000, 48000, 49000];

    Highcharts.chart('salaryTrendChart', {
        chart: {
            type: 'line',
            backgroundColor: 'transparent'
        },
        title: {
            text: 'Sample Salary Data',
            style: {
                fontSize: '16px',
                color: '#6b7280'
            }
        },
        xAxis: {
            categories: sampleMonths,
            title: {
                text: 'Month'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            lineColor: '#e5e7eb',
            tickColor: '#e5e7eb'
        },
        yAxis: {
            title: {
                text: 'Salary (Rs.)'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            gridLineColor: '#f3f4f6',
            lineColor: '#e5e7eb'
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            borderColor: '#e5e7eb',
            borderRadius: 12,
            shadow: true,
            formatter: function() {
                return `<span style="font-size: 14px; font-weight: 700; color: #1f2937;">${this.x}</span><br/>
                        <span style="color: #00bfa5; font-weight: 600;">●</span> Salary: <b>Rs. ${this.y.toLocaleString()}</b>`;
            }
        },
        plotOptions: {
            line: {
                marker: {
                    enabled: true,
                    radius: 6,
                    lineWidth: 3,
                    lineColor: '#ffffff',
                    fillColor: '#00bfa5'
                },
                lineWidth: 3,
                color: '#00bfa5'
            }
        },
        series: [{
            name: 'Net Salary',
            data: sampleSalaries
        }],
        credits: {
            enabled: false
        }
    });
}

// Load Leave Analytics
function loadLeaveAnalytics(startDate = null, endDate = null) {
    let url = 'include/api/leave_history.php';
    const params = new URLSearchParams();

    if (startDate && endDate) {
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    }

    if (params.toString()) {
        url += '?' + params.toString();
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                createLeaveAnalyticsChart(data.data);
            } else {
                showNoLeaveData();
            }
        })
        .catch(error => {
            showNoLeaveData();
        });
} // Create Leave Analytics Chart
function createLeaveAnalyticsChart(leaveData) {
    const statusCounts = {
        pending: 0,
        approved: 0,
        rejected: 0
    };

    leaveData.forEach(leave => {
        if (statusCounts.hasOwnProperty(leave.status)) {
            statusCounts[leave.status]++;
        }
    });

    Highcharts.chart('leaveAnalyticsChart', {
        chart: {
            type: 'column',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: ['Pending', 'Approved', 'Rejected'],
            title: {
                text: 'Leave Status'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            lineColor: '#e5e7eb',
            tickColor: '#e5e7eb'
        },
        yAxis: {
            title: {
                text: 'Number of Leaves'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            gridLineColor: '#f3f4f6',
            lineColor: '#e5e7eb'
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            borderColor: '#e5e7eb',
            borderRadius: 12,
            shadow: true,
            formatter: function() {
                return `<span style="font-size: 14px; font-weight: 700; color: #1f2937;">${this.x}</span><br/>
                        <span style="color: ${this.color}; font-weight: 600;">●</span> Count: <b>${this.y}</b>`;
            }
        },
        plotOptions: {
            column: {
                borderRadius: 6,
                pointPadding: 0.1,
                groupPadding: 0.1,
                borderWidth: 0
            }
        },
        series: [{
            name: 'Leaves',
            data: [{
                    y: statusCounts.pending,
                    color: '#f59e0b'
                },
                {
                    y: statusCounts.approved,
                    color: '#10b981'
                },
                {
                    y: statusCounts.rejected,
                    color: '#ef4444'
                }
            ]
        }],
        credits: {
            enabled: false
        }
    });
}

// Show No Leave Data Message
function showNoLeaveData() {
    document.getElementById('leaveAnalyticsChart').innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 2rem; color: #6b7280;">
            <div style="font-size: 4rem; color: #9ca3af; margin-bottom: 1rem; opacity: 0.7;">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h5 style="color: #1f2937; font-weight: 700; margin-bottom: 0.5rem; font-size: 1.2rem;">No Leave Data Available</h5>
            <p style="font-size: 0.9rem; line-height: 1.5; max-width: 300px;">
                Leave usage analytics will appear here once you apply for leaves.
            </p>
        </div>
    `;
}

// Create Sample Leave Chart (keeping for reference)
function createSampleLeaveChart() {
    Highcharts.chart('leaveAnalyticsChart', {
        chart: {
            type: 'column',
            backgroundColor: 'transparent'
        },
        title: {
            text: 'Sample Leave Data',
            style: {
                fontSize: '16px',
                color: '#6b7280'
            }
        },
        xAxis: {
            categories: ['Pending', 'Approved', 'Rejected'],
            title: {
                text: 'Leave Status'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            lineColor: '#e5e7eb',
            tickColor: '#e5e7eb'
        },
        yAxis: {
            title: {
                text: 'Number of Leaves'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            gridLineColor: '#f3f4f6',
            lineColor: '#e5e7eb'
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            borderColor: '#e5e7eb',
            borderRadius: 12,
            shadow: true,
            formatter: function() {
                return `<span style="font-size: 14px; font-weight: 700; color: #1f2937;">${this.x}</span><br/>
                        <span style="color: ${this.color}; font-weight: 600;">●</span> Count: <b>${this.y}</b>`;
            }
        },
        plotOptions: {
            column: {
                borderRadius: 6,
                pointPadding: 0.1,
                groupPadding: 0.1,
                borderWidth: 0
            }
        },
        series: [{
            name: 'Leaves',
            data: [{
                    y: 2,
                    color: '#f59e0b'
                },
                {
                    y: 8,
                    color: '#10b981'
                },
                {
                    y: 1,
                    color: '#ef4444'
                }
            ]
        }],
        credits: {
            enabled: false
        }
    });
}


// Load Work Hours Analysis
function loadWorkHoursAnalysis(startDate = null, endDate = null) {
    let url = 'include/api/userattendance.php';
    const params = new URLSearchParams();

    if (startDate && endDate) {
        // Use date range
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    } else {
        // Use current month (default) - fetch full month data
        const currentMonth = new Date().getMonth() + 1;
        const currentYear = new Date().getFullYear();

        // Get first and last day of current month
        const firstDay = new Date(currentYear, currentMonth - 1, 1);
        const lastDay = new Date(currentYear, currentMonth, 0);

        params.append('start_date', firstDay.toISOString().split('T')[0]);
        params.append('end_date', lastDay.toISOString().split('T')[0]);
    }

    url += '?' + params.toString();

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                createWorkHoursChart(data.data, startDate, endDate);
            } else {
                createSampleWorkHoursChart();
            }
        })
        .catch(error => {
            createSampleWorkHoursChart();
        });
}

// Create Work Hours Chart
function createWorkHoursChart(attendanceData, startDate = null, endDate = null) {
    const workHours = [];
    const dates = [];

    // Determine date range
    let start, end;

    if (startDate && endDate) {
        // Use custom date range
        start = new Date(startDate);
        end = new Date(endDate);
    } else {
        // Use current month (default)
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();
        start = new Date(currentYear, currentMonth, 1);
        end = new Date(currentYear, currentMonth + 1, 0);
    }

    // Create array for all dates in range
    const dateRangeData = {};

    // Initialize all dates with 0 hours
    const current = new Date(start);
    while (current <= end) {
        const dateKey = current.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
        dateRangeData[dateKey] = 0;
        current.setDate(current.getDate() + 1);
    }

    // Process attendance data
    attendanceData.forEach(record => {
        if (record.check_in && record.check_out && record.status !== 'absent') {
            const checkIn = new Date(record.check_in);
            const checkOut = new Date(record.check_out);

            // Calculate hours properly
            let hours = (checkOut - checkIn) / (1000 * 60 * 60);

            // Handle overnight shifts - if negative, add 24 hours
            if (hours < 0) {
                hours += 24;
            }

            // Only add valid hours (between 0 and 24)
            if (hours >= 0 && hours <= 24) {
                // Use check-in date for chart display (shift start date)
                const workDate = new Date(record.check_in);
                const dateKey = workDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });

                // Update date range data if within range
                if (dateRangeData.hasOwnProperty(dateKey)) {
                    dateRangeData[dateKey] = Math.round(hours * 10) / 10;
                }

            }
        }
    });

    // Convert date range data to arrays for chart
    Object.keys(dateRangeData).forEach(dateKey => {
        dates.push(dateKey);
        workHours.push(dateRangeData[dateKey]);
    });


    if (workHours.length === 0) {
        createSampleWorkHoursChart();
        return;
    }

    // Create chart title based on date range
    let chartTitle = 'Work Hours Analysis';
    if (startDate && endDate) {
        const startFormatted = new Date(startDate).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
        const endFormatted = new Date(endDate).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
        chartTitle = `Work Hours Analysis (${startFormatted} - ${endFormatted})`;
    }

    Highcharts.chart('workHoursChart', {
        chart: {
            type: 'area',
            backgroundColor: 'transparent'
        },
        title: {
            text: chartTitle,
            style: {
                fontSize: '16px',
                color: '#1f2937',
                fontWeight: '600'
            }
        },
        xAxis: {
            categories: dates,
            title: {
                text: 'Date',
                style: {
                    fontSize: '12px',
                    color: '#6b7280',
                    fontWeight: '500'
                }
            },
            labels: {
                style: {
                    fontSize: '11px',
                    color: '#6b7280'
                },
                rotation: -45 // Rotate labels for better readability
            },
            lineColor: '#e5e7eb',
            tickColor: '#e5e7eb'
        },
        yAxis: {
            title: {
                text: 'Work Hours',
                style: {
                    fontSize: '12px',
                    color: '#6b7280',
                    fontWeight: '500'
                }
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            gridLineColor: '#f3f4f6',
            lineColor: '#e5e7eb',
            min: 0, // Prevent negative values on Y-axis
            max: 24 // Maximum 24 hours
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            borderColor: '#e5e7eb',
            borderRadius: 12,
            shadow: true,
            formatter: function() {
                const hours = this.y;
                const date = this.x;

                // Convert decimal hours to hours and minutes
                const wholeHours = Math.floor(hours);
                const minutes = Math.round((hours - wholeHours) * 60);

                let timeDisplay;
                if (wholeHours === 0) {
                    timeDisplay = `${minutes} minutes`;
                } else if (minutes === 0) {
                    timeDisplay = `${wholeHours} hours`;
                } else {
                    timeDisplay = `${wholeHours} hours ${minutes} minutes`;
                }

                return `<div style="padding: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color:#414141; font-size: 14px;">Hours:</span>
                        <span style="color: #414141; font-weight: 500; font-size: 14px;">${timeDisplay}</span>
                    </div>
                </div>`;
            }
        },
        plotOptions: {
            area: {
                fillOpacity: 0.3,
                marker: {
                    enabled: true,
                    radius: 3,
                    lineWidth: 2,
                    lineColor: '#ffffff',
                    fillColor: '#8b5cf6'
                },
                lineWidth: 2,
                color: '#8b5cf6'
            }
        },
        series: [{
            name: 'Work Hours',
            data: workHours,
            color: '#8b5cf6'
        }],
        credits: {
            enabled: false
        }
    });
}

// Create Sample Work Hours Chart
function createSampleWorkHoursChart() {
    const sampleDates = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    const sampleHours = [8.5, 7.8, 8.2, 8.0, 7.5];

    Highcharts.chart('workHoursChart', {
        chart: {
            type: 'area',
            backgroundColor: 'transparent'
        },
        title: {
            text: 'Sample Work Hours',
            style: {
                fontSize: '16px',
                color: '#6b7280'
            }
        },
        xAxis: {
            categories: sampleDates,
            title: {
                text: 'Date'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            lineColor: '#e5e7eb',
            tickColor: '#e5e7eb'
        },
        yAxis: {
            title: {
                text: 'Work Hours'
            },
            labels: {
                style: {
                    fontSize: '12px',
                    color: '#6b7280'
                }
            },
            gridLineColor: '#f3f4f6',
            lineColor: '#e5e7eb'
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.98)',
            borderColor: '#e5e7eb',
            borderRadius: 12,
            shadow: true,
            formatter: function() {
                return `<span style="font-size: 14px; font-weight: 700; color: #1f2937;">${this.x}</span><br/>
                        <span style="color: #8b5cf6; font-weight: 600;">●</span> Hours: <b>${this.y}</b>`;
            }
        },
        plotOptions: {
            area: {
                fillOpacity: 0.3,
                marker: {
                    enabled: true,
                    radius: 4,
                    lineWidth: 2,
                    lineColor: '#ffffff',
                    fillColor: '#8b5cf6'
                },
                lineWidth: 2,
                color: '#8b5cf6'
            }
        },
        series: [{
            name: 'Work Hours',
            data: sampleHours
        }],
        credits: {
            enabled: false
        }
    });
}

// Load Recent Activities
function loadRecentActivities() {
    const activities = [{
            type: 'attendance',
            message: 'Checked in at 9:15 AM',
            time: '2 hours ago',
            icon: 'fas fa-clock',
            color: '#00bfa5'
        },
        {
            type: 'leave',
            message: 'Leave request approved for Annual Leave',
            time: '1 day ago',
            icon: 'fas fa-calendar-check',
            color: '#10b981'
        },
        {
            type: 'salary',
            message: 'Salary credited for December 2024',
            time: '3 days ago',
            icon: 'fas fa-money-bill-wave',
            color: '#f59e0b'
        },
        {
            type: 'attendance',
            message: 'Checked out at 6:30 PM',
            time: '1 day ago',
            icon: 'fas fa-clock',
            color: '#00bfa5'
        },
        {
            type: 'announcement',
            message: 'New company policy announced',
            time: '2 days ago',
            icon: 'fas fa-bullhorn',
            color: '#8b5cf6'
        }
    ];

    let activitiesHtml = '';
    activities.forEach(activity => {
        activitiesHtml += `
            <div style="display: flex; align-items: center; padding: 1rem; border-bottom: 1px solid #f3f4f6;">
                <div style="width: 40px; height: 40px; background: ${activity.color}15; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                    <i class="${activity.icon}" style="color: ${activity.color}; font-size: 1rem;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #2d3436; margin-bottom: 0.25rem;">${activity.message}</div>
                    <div style="font-size: 0.85rem; color: #6b7280;">${activity.time}</div>
                </div>
            </div>
        `;
    });

    document.getElementById('recentActivitiesBox').innerHTML = activitiesHtml;
}

// Load Notifications
function loadNotifications() {
    fetch('include/api/userannouncements.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                createNotificationsList(data.data);
            } else {
                renderNoNotifications();
            }
        })
        .catch(error => {
            renderNoNotifications();
        });
}

// Create Notifications List
function createNotificationsList(announcements) {
    let notificationsHtml = '';
    const recentAnnouncements = announcements.slice(0, 5);

    recentAnnouncements.forEach(announcement => {
        const date = new Date(announcement.created_at);
        const timeAgo = getTimeAgo(date);

        notificationsHtml += `
            <div style="padding: 1rem; border-bottom: 1px solid #f3f4f6;">
                <div style="font-weight: 600; color: #2d3436; margin-bottom: 0.5rem; font-size: 0.9rem;">${announcement.title}</div>
                <div style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.5rem;">${announcement.content?.substring(0, 60)}${announcement.content?.length > 60 ? '...' : ''}</div>
                <div style="font-size: 0.75rem; color: #9ca3af;">${timeAgo}</div>
            </div>
        `;
    });

    document.getElementById('notificationsBox').innerHTML = notificationsHtml;
}

// Create Sample Notifications
function renderNoNotifications() {
    const html = `
        <div style="padding: 2rem; text-align: center; color: #6b7280;">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; border-radius:50%; background:#f3f4f6; margin-bottom:0.75rem;">
                <i class="fas fa-bell-slash" style="color:#9ca3af; font-size:1.1rem;"></i>
            </div>
            <div style="font-weight:600; color:#2d3436; margin-bottom:0.25rem;">No announcements</div>
            <div style="font-size:0.85rem;">No Announcement Avaiable</div>
        </div>
    `;
    document.getElementById('notificationsBox').innerHTML = html;
}

// Helper function to get time ago
function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    return `${Math.floor(diffInSeconds / 2592000)} months ago`;
}

// Add click event listeners for dashboard cards
function addCardClickListeners() {
    // Today's Status Card
    document.getElementById('todayStatusCard').addEventListener('click', function() {
        showTodayStatusModal();
    });

    // Monthly Attendance Card
    document.getElementById('monthlyAttendanceCard').addEventListener('click', function() {
        showMonthlyAttendanceModal();
    });

    // Total Leave Card
    document.getElementById('totalLeaveCard').addEventListener('click', function() {
        showTotalLeaveModal();
    });

    // Department Card
    document.getElementById('departmentCard').addEventListener('click', function() {
        showDepartmentModal();
    });
}

// Show Today's Status Modal
function showTodayStatusModal() {
    // Always show today's data only, regardless of date range selection
    const today = new Date().toISOString().split('T')[0];
    const dateLabel = 'Today';

    fetch(`include/api/userattendance.php?date=${today}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                showTodayStatusModalContent(data.data, dateLabel);
            } else {
                showTodayStatusModalContent([], dateLabel);
            }
        })
        .catch(error => {
            showTodayStatusModalContent([], dateLabel);
        });
}

// Show Today's Status Modal Content
function showTodayStatusModalContent(attendanceData, dateLabel) {
    let content = '';

    if (attendanceData && attendanceData.length > 0) {
        const attendance = attendanceData[0]; // Always get first record (today's data)
        const checkIn = attendance.check_in_formatted || '--:--';
        const checkOut = attendance.check_out_formatted || '--:--';
        const workingHours = attendance.working_hrs || '--';
        const status = attendance.status || 'Not Marked';

        let statusBadge = '';
        switch (status.toLowerCase()) {
            case 'present':
                statusBadge = '<span class="badge" style="background-color: #10b981; color: white;">Present</span>';
                break;
            case 'late':
                statusBadge = '<span class="badge" style="background-color: #f59e0b; color: white;">Late</span>';
                break;
            case 'half-day':
                statusBadge = '<span class="badge" style="background-color: #3b82f6; color: white;">Half Day</span>';
                break;
            case 'absent':
                statusBadge = '<span class="badge" style="background-color: #ef4444; color: white;">Absent</span>';
                break;
            default:
                statusBadge = '<span class="badge" style="background-color: #6b7280; color: white;">Not Marked</span>';
        }

        content = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Check-in Time:</label>
                        <div class="p-2 bg-light rounded">${checkIn}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Check-out Time:</label>
                        <div class="p-2 bg-light rounded">${checkOut}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Working Hours:</label>
                        <div class="p-2 bg-light rounded">${workingHours}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status:</label>
                        <div class="p-2">${statusBadge}</div>
                    </div>
                </div>
            </div>
        `;
    } else {
        content = `
            <div class="text-center py-4">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Attendance Record</h5>
                <p class="text-muted">No attendance has been marked for today.</p>
            </div>
        `;
    }

    showModal('Today\'s Attendance Details', content, dateLabel);
}

// Show Monthly Attendance Modal
function showMonthlyAttendanceModal() {
    // Check if date range is selected, otherwise use current month
    const startDate = window.selectedStartDate;
    const endDate = window.selectedEndDate;

    let url = 'include/api/userattendance.php';
    let dateLabel = '';

    if (startDate && endDate) {
        // Use selected date range
        url += `?start_date=${startDate}&end_date=${endDate}`;
        dateLabel = `${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}`;
    } else {
        // Use current month
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        url += `?year=${currentYear}&month=${currentMonth}`;
        const monthName = new Date(currentYear, currentMonth - 1).toLocaleDateString('en-US', {
            month: 'long'
        });
        dateLabel = `${monthName} ${currentYear}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                showMonthlyAttendanceModalContent(data.data, dateLabel);
            } else {
                showMonthlyAttendanceModalContent([], dateLabel);
            }
        })
        .catch(error => {
            showMonthlyAttendanceModalContent([], dateLabel);
        });
}

// Show Monthly Attendance Modal Content
function showMonthlyAttendanceModalContent(attendanceData, dateLabel) {
    let tableRows = '';
    if (attendanceData && attendanceData.length > 0) {
        attendanceData.forEach(record => {
            const date = record.workday ? new Date(record.workday).toLocaleDateString() : '-';
            const checkIn = record.check_in_formatted || '-';
            const checkOut = record.check_out_formatted || '-';
            const workingHours = record.working_hrs || '-';
            const status = record.status || 'Not Marked';

            let statusBadge = '';
            switch (status.toLowerCase()) {
                case 'present':
                    statusBadge = '<span class="badge" style="background-color: #10b981; color: white;">Present</span>';
                    break;
                case 'late':
                    statusBadge = '<span class="badge" style="background-color: #f59e0b; color: white;">Late</span>';
                    break;
                case 'half-day':
                    statusBadge = '<span class="badge" style="background-color: #3b82f6; color: white;">Half Day</span>';
                    break;
                case 'absent':
                    statusBadge = '<span class="badge" style="background-color: #ef4444; color: white;">Absent</span>';
                    break;
                default:
                    statusBadge = '<span class="badge" style="background-color: #6b7280; color: white;">Not Marked</span>';
            }

            tableRows += `
                <tr>
                    <td>${date}</td>
                    <td>${checkIn}</td>
                    <td>${checkOut}</td>
                    <td>${workingHours}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    } else {
        tableRows = '<tr><td colspan="5" class="text-center text-muted">No attendance records found for the selected period</td></tr>';
    }

    const content = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Working Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        </div>
    `;

    const modalTitle = window.selectedStartDate && window.selectedEndDate ?
        'Attendance Details' : 'Monthly Attendance Details';

    showModal(modalTitle, content, dateLabel);
}

// Show Total Leave Modal
function showTotalLeaveModal() {
    // Check if date range is selected, otherwise use current month
    const startDate = window.selectedStartDate;
    const endDate = window.selectedEndDate;

    let url = 'include/api/leave_history.php';
    let dateLabel = '';

    if (startDate && endDate) {
        // Use selected date range
        url += `?start_date=${startDate}&end_date=${endDate}`;
        dateLabel = `${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}`;
    } else {
        // Use current month
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        url += `?year=${currentYear}&month=${currentMonth}`;
        const monthName = new Date(currentYear, currentMonth - 1).toLocaleDateString('en-US', {
            month: 'long'
        });
        dateLabel = `${monthName} ${currentYear}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                showTotalLeaveModalContent(data.data, dateLabel);
            } else {
                showTotalLeaveModalContent([], dateLabel);
            }
        })
        .catch(error => {
            showTotalLeaveModalContent([], dateLabel);
        });
}

// Show Total Leave Modal Content
function showTotalLeaveModalContent(leaveData, dateLabel) {
    let tableRows = '';
    if (leaveData && leaveData.length > 0) {
        leaveData.forEach(leave => {
            const startDate = leave.start_date ? new Date(leave.start_date).toLocaleDateString() : '-';
            const endDate = leave.end_date ? new Date(leave.end_date).toLocaleDateString() : '-';
            const leaveType = leave.type_name || '-';
            const days = leave.days || '-';
            const status = leave.status || 'pending';

            let statusBadge = '';
            switch (status.toLowerCase()) {
                case 'approved':
                    statusBadge = '<span class="badge" style="background-color: #10b981; color: white;">Approved</span>';
                    break;
                case 'rejected':
                    statusBadge = '<span class="badge" style="background-color: #ef4444; color: white;">Rejected</span>';
                    break;
                case 'pending':
                    statusBadge = '<span class="badge" style="background-color: #f59e0b; color: white;">Pending</span>';
                    break;
                default:
                    statusBadge = '<span class="badge" style="background-color: #6b7280; color: white;">Unknown</span>';
            }

            tableRows += `
                <tr>
                    <td>${startDate}</td>
                    <td>${endDate}</td>
                    <td>${leaveType}</td>
                    <td>${days}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    } else {
        tableRows = '<tr><td colspan="5" class="text-center text-muted">No leave records found for the selected period</td></tr>';
    }

    const content = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Leave Type</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        </div>
    `;

    const modalTitle = window.selectedStartDate && window.selectedEndDate ?
        'Leave Details' : 'Monthly Leave Details';

    showModal(modalTitle, content, dateLabel);
}

// Show Department Modal
function showDepartmentModal() {
    fetch('include/api/department_employees.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                showDepartmentModalContent(data.data);
            } else {
                showDepartmentModalContent([]);
            }
        })
        .catch(error => {
            showDepartmentModalContent([]);
        });
}

// Show Department Modal Content
function showDepartmentModalContent(employees) {
    let tableRows = '';
    if (employees && employees.length > 0) {
        employees.forEach(employee => {
            const name = employee.name || '-';
            const designation = employee.designation || '-';
            const email = employee.email || '-';
            const phone = employee.phone || '-';
            const status = employee.status || 'active';
            const isDepartmentHead = employee.is_department_head || false;

            let statusBadge = '';
            switch (status.toLowerCase()) {
                case 'active':
                    statusBadge = '<span class="badge" style="background-color: #10b981; color: white;">Active</span>';
                    break;
                case 'inactive':
                    statusBadge = '<span class="badge" style="background-color: #6b7280; color: white;">Inactive</span>';
                    break;
                default:
                    statusBadge = '<span class="badge" style="background-color: #6b7280; color: white;">Unknown</span>';
            }

            // Add department head badge if applicable
            const headBadge = isDepartmentHead ?
                '<span class="badge ms-2" style="background-color: #f59e0b; color: white; font-size: 0.75rem;">Department Head</span>' : '';

            tableRows += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            ${name}
                            ${headBadge}
                        </div>
                    </td>
                    <td>${designation}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    } else {
        tableRows = '<tr><td colspan="3" class="text-center text-muted">No employees found in your department</td></tr>';
    }

    const content = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Job Title</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
        </div>
    `;

    showModal('Department Employees', content, 'All employees in your department');
}

// Generic Modal Function
function showModal(title, content, subtitle = '') {
    const modalHtml = `
        <div class="modal fade" id="dashboardModal" tabindex="-1" aria-labelledby="dashboardModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white;">
                        <h5 class="modal-title" id="dashboardModalLabel">
                            <i class="fas fa-info-circle me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${subtitle ? `<div class="mb-3"><strong>${subtitle}</strong></div>` : ''}
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    $('#dashboardModal').remove();

    // Add modal to body
    $('body').append(modalHtml);

    // Show modal
    $('#dashboardModal').modal('show');

    // Remove modal from DOM when hidden
    $('#dashboardModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Enhanced Highcharts Global Options
Highcharts.setOptions({
    colors: ['#00bfa5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#3b82f6'],
    accessibility: {
        enabled: false
    },
    chart: {
        style: {
            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
        }
    },
    title: {
        style: {
            fontSize: '18px',
            fontWeight: '600',
            color: '#2d3436'
        }
    },
    xAxis: {
        lineColor: '#e5e7eb',
        tickColor: '#e5e7eb',
        labels: {
            style: {
                fontSize: '12px',
                color: '#6b7280'
            }
        }
    },
    yAxis: {
        gridLineColor: '#f3f4f6',
        lineColor: '#e5e7eb',
        labels: {
            style: {
                fontSize: '12px',
                color: '#6b7280'
            }
        }
    },
    tooltip: {
        backgroundColor: 'rgba(255, 255, 255, 0.95)',
        borderColor: '#e5e7eb',
        borderRadius: 8,
        shadow: true,
        style: {
            fontSize: '13px'
        }
    }
});