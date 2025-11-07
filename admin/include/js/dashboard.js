// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

$(document).ready(function() {
    // Load all dashboard data
    loadDashboardStats();
    loadSalaryDistribution();
    loadGenderDiversity();
    loadJoinExitTrend();

    loadLeaveRequests();

    loadAttendanceSummary();
    loadDepartmentWiseEmployees();
    loadJobTypeEmployees();

    // Add click handlers for stats cards
    addStatsCardClickHandlers();
});

// Add click handlers for stats cards
function addStatsCardClickHandlers() {
    // Handle clicks on stats cards
    $('.clickable-card').on('click', function() {
        const modalId = $(this).data('modal');
        if (modalId) {
            // Show the modal
            $('#' + modalId).modal('show');

            // Load data based on modal type
            loadModalData(modalId);
        }
    });
}

// Load data for modals
function loadModalData(modalId) {
    let modalType = '';
    let tableBodyId = '';

    switch (modalId) {
        case 'totalEmployeesModal':
            modalType = 'total_employees';
            tableBodyId = 'totalEmployeesTableBody';
            // Load departments for filter dropdown
            loadDepartmentsForFilter();
            break;
        case 'presentTodayModal':
            modalType = 'present_today';
            tableBodyId = 'presentTodayTableBody';
            // Load departments for present today filter dropdown
            loadDepartmentsForPresentFilter();
            break;
        case 'onLeaveModal':
            modalType = 'on_leave';
            tableBodyId = 'onLeaveTableBody';
            // Load departments for leave employees filter dropdown
            loadDepartmentsForLeaveFilter();
            break;
        case 'departmentsModal':
            modalType = 'departments';
            tableBodyId = 'departmentsTableBody';
            break;
        default:
            return;
    }

    // Show loading state
    $('#' + tableBodyId).html(`
        <tr>
            <td colspan="4" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading data...</p>
            </td>
        </tr>
    `);

    // Fetch data
    fetch(`include/api/dashboard.php?modal=${modalType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Store employees data for filtering
                if (modalType === 'total_employees') {
                    allEmployeesData = data.data;
                } else if (modalType === 'present_today') {
                    presentEmployeesData = data.data;
                } else if (modalType === 'on_leave') {
                    leaveEmployeesData = data.data;
                }
                populateModalTable(tableBodyId, data.data, modalType);
            } else {
                $('#' + tableBodyId).html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No data available
                        </td>
                    </tr>
                `);
            }
        })
        .catch(error => {
            $('#' + tableBodyId).html(`
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading data
                    </td>
                </tr>
            `);
        });
}

// Populate modal table with data
function populateModalTable(tableBodyId, data, modalType) {
    let html = '';

    if (data.length === 0) {
        html = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    No records found
                </td>
            </tr>
        `;
    } else {
        data.forEach(item => {
            switch (modalType) {
                case 'total_employees':
                    html += `
                        <tr>
                            <td>${createFullName(item.first_name, item.middle_name, item.last_name) || 'N/A'}</td>
                            <td>${item.email || 'N/A'}</td>
                            <td>${item.department || 'Not Assigned'}</td>
                        </tr>
                    `;
                    break;

                case 'present_today':
                    // Format check-in time to 12-hour format
                    let checkInTime = 'N/A';
                    if (item.check_in_time) {
                        try {
                            // Remove 'Z' to treat as local time instead of UTC
                            const time = new Date('1970-01-01T' + item.check_in_time);
                            checkInTime = time.toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                        } catch (e) {
                            checkInTime = item.check_in_time; // Fallback to original if parsing fails
                        }
                    }

                    html += `
                        <tr>
                            <td>${createFullName(item.first_name, item.middle_name, item.last_name) || 'N/A'}</td>
                            <td>${item.email || 'N/A'}</td>
                            <td>${item.department || 'Not Assigned'}</td>
                            <td>${checkInTime}</td>
                        </tr>
                    `;
                    break;

                case 'on_leave':
                    html += `
                        <tr>
                            <td>${createFullName(item.first_name, item.middle_name, item.last_name) || 'N/A'}</td>
                            <td>${item.email || 'N/A'}</td>
                            <td>${item.department || 'Not Assigned'}</td>
                            <td>${item.leave_period || 'N/A'}</td>
                            <td>${item.days || 0} days</td>
                        </tr>
                    `;
                    break;

                case 'departments':
                    const depHeadName = item.first_name && item.last_name ? createFullName(item.first_name, item.middle_name, item.last_name) : 'Not assigned';
                    const managerName = item.manager_first_name && item.manager_last_name ? createFullName(item.manager_first_name, item.manager_middle_name, item.manager_last_name) : 'Not assigned';
                    html += `
                        <tr>
                            <td>${item.dept_name || 'N/A'}</td>
                            <td>${item.employee_count || 0} employees</td>
                            <td>${managerName}</td>
                            <td>${depHeadName}</td>
                        </tr>
                    `;
                    break;
            }
        });
    }

    $('#' + tableBodyId).html(html);
}
// Load dashboard statistics
function loadDashboardStats() {
    fetch('include/api/dashboard.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set main counts
                if (data.total_employees !== undefined) {
                    document.getElementById('totalEmployeesCount').textContent = data.total_employees;
                } else {
                    document.getElementById('totalEmployeesCount').textContent = '-';
                }

                if (data.present_today !== undefined) {
                    document.getElementById('presentTodayCount').textContent = data.present_today;
                } else {
                    document.getElementById('presentTodayCount').textContent = '-';
                }

                if (data.on_leave !== undefined) {
                    document.getElementById('onLeaveCount').textContent = data.on_leave;
                } else {
                    document.getElementById('onLeaveCount').textContent = '-';
                }

                if (data.total_departments !== undefined) {
                    document.getElementById('departmentsCount').textContent = data.total_departments;
                } else {
                    document.getElementById('departmentsCount').textContent = '-';
                }
                // Calculate and display real percentages and trends
                if (data.total_employees && data.total_employees > 0) {
                    // Present Today Percentage
                    if (data.present_today !== undefined) {
                        const presentPercentage = Math.round((data.present_today / data.total_employees) * 100);
                        document.getElementById('presentTodayPercentage').textContent = `${presentPercentage}% attendance`;

                        // Set color based on percentage
                        const presentTrend = document.getElementById('presentTodayTrend');
                        if (presentPercentage >= 90) {
                            presentTrend.style.color = '#10b981'; // Green
                        } else if (presentPercentage >= 75) {
                            presentTrend.style.color = '#f59e0b'; // Orange
                        } else {
                            presentTrend.style.color = '#ef4444'; // Red
                        }
                    }
                    // On Leave Percentage
                    if (data.on_leave !== undefined) {
                        const leavePercentage = Math.round((data.on_leave / data.total_employees) * 100);
                        document.getElementById('onLeavePercentage').textContent = `${leavePercentage}% of total`;
                    }
                }
                // Calculate trends using real data from API
                if (data.total_employees) {
                    // Employee change this month
                    if (data.employee_change !== undefined) {
                        const changeText = data.employee_change > 0 ?
                            `+${data.employee_change}% this month` :
                            data.employee_change < 0 ?
                            `${data.employee_change}% this month` :
                            'No change this month';
                        document.getElementById('totalEmployeesChange').textContent = changeText;
                        // Set color based on change
                        const trendElement = document.getElementById('totalEmployeesTrend');
                        if (data.employee_change > 0) {
                            trendElement.style.color = '#10b981'; // Green for positive
                        } else if (data.employee_change < 0) {
                            trendElement.style.color = '#ef4444'; // Red for negative
                        } else {
                            trendElement.style.color = '#6b7280'; // Gray for no change
                        }
                    }
                    // Department change this year
                    if (data.department_change !== undefined) {
                        const deptText = data.department_change > 0 ?
                            `+${data.department_change} new this year` :
                            data.department_change < 0 ?
                            `${data.department_change} less this year` :
                            'No change this year';
                        document.getElementById('departmentsChange').textContent = deptText;
                        // Set color based on change
                        const deptTrendElement = document.getElementById('departmentsTrend');
                        if (data.department_change > 0) {
                            deptTrendElement.style.color = '#06b6d4'; // Blue for positive
                        } else if (data.department_change < 0) {
                            deptTrendElement.style.color = '#ef4444'; // Red for negative
                        } else {
                            deptTrendElement.style.color = '#6b7280'; // Gray for no change
                        }
                    }
                }
            }
        })
        .catch(() => {
            // Set error state
            document.getElementById('totalEmployeesCount').textContent = '-';
            document.getElementById('presentTodayCount').textContent = '-';
            document.getElementById('onLeaveCount').textContent = '-';
            document.getElementById('departmentsCount').textContent = '-';

            // Set error messages
            document.getElementById('totalEmployeesChange').textContent = 'Error loading data';
            document.getElementById('presentTodayPercentage').textContent = 'Error loading data';
            document.getElementById('onLeavePercentage').textContent = 'Error loading data';
            document.getElementById('departmentsChange').textContent = 'Error loading data';
        });
}
// Salary Distribution Chart
function loadSalaryDistribution() {
    fetch('include/api/salary-distribution.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.series && data.series.length > 0) {
                Highcharts.chart('salaryDeptChart', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '18px',
                            fontWeight: '600',
                            color: '#1f2937'
                        }
                    },
                    colors: ['#00bfa5', '#02d6ba', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
                    xAxis: {
                        categories: data.salary_ranges,
                        title: {
                            text: 'Salary Range',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
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
                        min: 0,
                        title: {
                            text: 'Number of Employees',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
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
                        shared: true,
                        valueSuffix: ' employees',
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        borderColor: '#e5e7eb',
                        borderRadius: 8,
                        shadow: true,
                        style: {
                            fontSize: '13px'
                        }
                    },
                    plotOptions: {
                        column: {
                            grouping: true,
                            shadow: false,
                            borderWidth: 0,
                            colorByPoint: false,
                            borderRadius: 6,
                            pointPadding: 0.1,
                            groupPadding: 0.1
                        }
                    },
                    series: data.series,
                    credits: {
                        enabled: false
                    }
                });
            } else {

                // Check if it's "No employees found" or empty data
                if (!data.success || !data.series || data.series.length === 0) {
                    // Show empty state instead of blank chart
                    const chartContainer = document.getElementById('salaryDeptChart');
                    if (chartContainer) {
                        chartContainer.innerHTML = `
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                                <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">No Salary Data</div>
                                <div style="font-size: 1rem; color: #6b7280;">No active employees with salary information found</div>
                                <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Add employees with salary details to see analytics</div>
                            </div>
                        `;
                    }
                    return; // Don't show blank chart
                }
            }
        })
        .catch(error => {
            // Show empty state on fetch error
            const chartContainer = document.getElementById('salaryDeptChart');
            if (chartContainer) {
                chartContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Error Loading Data</div>
                        <div style="font-size: 1rem; color: #6b7280;">Unable to load salary distribution data</div>
                        <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Please check your connection and try again</div>
                    </div>
                `;
            }
        });
}
// Gender Distribution Donut Chart
function loadGenderDiversity() {
    fetch('include/api/gender-distribution.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.total > 0) {
                // Restore container layout for data display
                const container = document.getElementById('genderDistributionContainer');
                container.style.gridTemplateColumns = '1fr 1fr';

                // Show stats box
                document.getElementById('genderStatsBox').style.display = 'flex';

                const malePercent = data.total ? ((data.male / data.total) * 100).toFixed(1) : 0;
                const femalePercent = data.total ? ((data.female / data.total) * 100).toFixed(1) : 0;

                // Donut Chart
                Highcharts.chart('genderDonutChart', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
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
                    colors: ['#3b82f6', '#ec4899'],
                    series: [{
                        name: 'Employees',
                        data: [{
                                name: 'Male',
                                y: data.male
                            },
                            {
                                name: 'Female',
                                y: data.female
                            }
                        ]
                    }],
                    credits: {
                        enabled: false
                    }
                });
                // Enhanced Stats Box
                document.getElementById('genderStatsBox').innerHTML = `
                    <div style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); padding: 1.5rem; border-radius: 12px; color: white; margin-bottom: 1rem; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);">
                        <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">${malePercent}%</div>
                        <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Male</div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Total: ${data.male} employees</div>
                    </div>
                    <div style="background: linear-gradient(135deg, #ec4899, #be185d); padding: 1.5rem; border-radius: 12px; color: white; box-shadow: 0 4px 6px -1px rgba(236, 72, 153, 0.1);">
                        <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">${femalePercent}%</div>
                        <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Female</div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Total: ${data.female} employees</div>
                    </div>
                `;
            } else {

                // Adjust container for empty state - full width
                const container = document.getElementById('genderDistributionContainer');
                container.style.gridTemplateColumns = '1fr';

                // Show single unified empty state spanning full width
                document.getElementById('genderDonutChart').innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; padding: 2rem; width: 100%;">
                        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">No Gender Data Available</div>
                        <div style="font-size: 1rem; color: #6b7280; margin-bottom: 0.5rem;">No active employees found for gender distribution</div>
                        <div style="font-size: 0.875rem; color: #9ca3af;">Add employees to see gender analytics and statistics</div>
                    </div>
                `;

                // Hide the stats box
                document.getElementById('genderStatsBox').style.display = 'none';
            }
        })
        .catch(error => {
            // Adjust container for error state - full width
            const container = document.getElementById('genderDistributionContainer');
            container.style.gridTemplateColumns = '1fr';

            // Show single unified error state spanning full width
            document.getElementById('genderDonutChart').innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0; padding: 2rem; width: 100%;">
                    <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Error Loading Data</div>
                    <div style="font-size: 1rem; color: #6b7280; margin-bottom: 0.5rem;">Unable to load gender distribution data</div>
                    <div style="font-size: 0.875rem; color: #9ca3af;">Please check your connection and try again</div>
                </div>
            `;

            // Hide the stats box
            document.getElementById('genderStatsBox').style.display = 'none';
        });
}
// Employee Join/Delete Trend Line Chart
function loadJoinExitTrend() {
    fetch('include/api/employee-join-delete-trend.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.months && data.months.length > 0) {
                Highcharts.chart('employeeLineChart', {
                    chart: {
                        type: 'line',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '18px',
                            fontWeight: '600',
                            color: '#1f2937'
                        }
                    },
                    xAxis: {
                        categories: data.months,
                        title: {
                            text: 'Month',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: '#6b7280'
                            },
                            rotation: -45
                        },
                        lineColor: '#e5e7eb',
                        tickColor: '#e5e7eb',
                        gridLineColor: '#f3f4f6'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Employees',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
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
                        style: {
                            fontSize: '13px',
                            fontWeight: '500'
                        },
                        headerFormat: '<span style="font-size: 14px; font-weight: 700; color: #1f2937;">{point.key}</span><br/>',
                        pointFormat: '<span style="color: {point.color}; font-weight: 600;">●</span> {series.name}: <b>{point.y}</b> employees<br/>'
                    },
                    plotOptions: {
                        line: {
                            marker: {
                                enabled: true,
                                radius: 4,
                                lineWidth: 2,
                                lineColor: '#ffffff',
                                fillColor: null
                            },
                            lineWidth: 1.5,
                            states: {
                                hover: {
                                    lineWidth: 2.5
                                }
                            }
                        }
                    },
                    series: [{
                            name: 'Joined',
                            data: data.joined,
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#10b981'],
                                    [1, '#059669']
                                ]
                            },
                            marker: {
                                fillColor: '#10b981',
                                lineColor: '#ffffff',
                                lineWidth: 3,
                                radius: 8,
                                symbol: 'circle'
                            }
                        },
                        {
                            name: 'Deleted',
                            data: data.deleted,
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#ef4444'],
                                    [1, '#dc2626']
                                ]
                            },
                            marker: {
                                fillColor: '#ef4444',
                                lineColor: '#ffffff',
                                lineWidth: 3,
                                radius: 8,
                                symbol: 'diamond'
                            }
                        }
                    ],
                    legend: {
                        enabled: true,
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '13px',
                            fontWeight: '600',
                            color: '#374151'
                        },
                        itemHoverStyle: {
                            color: '#1f2937'
                        },
                        itemDistance: 20
                    },
                    credits: {
                        enabled: false
                    }
                });
            } else {

                // Show empty state instead of blank chart
                const chartContainer = document.getElementById('employeeLineChart');
                if (chartContainer) {
                    chartContainer.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                            <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">No Activity Data</div>
                            <div style="font-size: 1rem; color: #6b7280;">No employee joining or exit activity found</div>
                            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Add employees to start tracking trends</div>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            // Show error state on fetch error
            const chartContainer = document.getElementById('employeeLineChart');
            if (chartContainer) {
                chartContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Error Loading Data</div>
                        <div style="font-size: 1rem; color: #6b7280;">Unable to load employee trend data</div>
                        <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Please check your connection and try again</div>
                    </div>
                `;
            }
        });
}
// Attendance Chart
function loadAttendanceSummary() {
    let attendanceChartInstance = null;
    fetch('include/api/attendance-area.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.series && data.series.length > 0) {
                if (attendanceChartInstance) attendanceChartInstance.destroy();
                // Enhanced area chart for attendance
                attendanceChartInstance = Highcharts.chart('attendanceChart', {
                    chart: {
                        type: 'area',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '18px',
                            fontWeight: '600',
                            color: '#1f2937'
                        }
                    },
                    xAxis: {
                        categories: data.days,
                        title: {
                            text: 'Day',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: '#6b7280'
                            }
                        },
                        lineColor: '#e5e7eb',
                        tickColor: '#e5e7eb',
                        gridLineColor: '#f3f4f6'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Employees',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
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
                        shared: true,
                        backgroundColor: 'rgba(255, 255, 255, 0.98)',
                        borderColor: '#e5e7eb',
                        borderRadius: 12,
                        shadow: true,
                        style: {
                            fontSize: '13px',
                            fontWeight: '500'
                        },
                        headerFormat: '<span style="font-size: 14px; font-weight: 700; color: #1f2937;">{point.key}</span><br/>',
                        pointFormat: '<span style="color: {point.color}; font-weight: 600;">●</span> {series.name}: <b>{point.y}</b> employees<br/>'
                    },
                    plotOptions: {
                        area: {
                            fillOpacity: 0.3,
                            marker: {
                                enabled: true,
                                radius: 4,
                                lineWidth: 2,
                                lineColor: '#ffffff',
                                symbol: 'circle'
                            },
                            lineWidth: 1.5,
                            states: {
                                hover: {
                                    lineWidth: 2.5,
                                    fillOpacity: 0.5
                                }
                            }
                        }
                    },
                    series: data.series,
                    legend: {
                        enabled: true,
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '13px',
                            fontWeight: '600',
                            color: '#374151'
                        },
                        itemHoverStyle: {
                            color: '#1f2937'
                        },
                        itemDistance: 15
                    },
                    credits: {
                        enabled: false
                    }
                });
            } else {

                // Check if it's "No active employees found" error
                if (data.error && data.error.includes('No active employees found')) {
                    // Show empty state instead of sample data
                    const chartContainer = document.getElementById('attendanceChart');
                    if (chartContainer) {
                        chartContainer.innerHTML = `
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                                <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">No Attendance Data</div>
                                <div style="font-size: 1rem; color: #6b7280;">No active employees found in the system</div>
                                <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Add employees to start tracking attendance</div>
                            </div>
                        `;
                    }
                    return; // Don't show sample data
                }

                // Show sample data if API fails for other reasons
                const sampleData = {
                    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    series: [{
                            name: 'Present',
                            data: [15, 18, 16, 19, 17, 8, 5],
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#10b981'],
                                    [1, '#059669']
                                ]
                            },
                            marker: {
                                fillColor: '#10b981',
                                lineColor: '#ffffff',
                                lineWidth: 3,
                                radius: 6,
                                symbol: 'circle'
                            }
                        },
                        {
                            name: 'Absent',
                            data: [3, 1, 2, 0, 1, 0, 0],
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#ef4444'],
                                    [1, '#dc2626']
                                ]
                            },
                            marker: {
                                fillColor: '#ef4444',
                                lineColor: '#ffffff',
                                lineWidth: 3,
                                radius: 6,
                                symbol: 'diamond'
                            }
                        },
                        {
                            name: 'Late',
                            data: [2, 1, 3, 1, 2, 0, 0],
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#f59e0b'],
                                    [1, '#d97706']
                                ]
                            },
                            marker: {
                                fillColor: '#f59e0b',
                                lineColor: '#ffffff',
                                lineWidth: 3,
                                radius: 6,
                                symbol: 'square'
                            }
                        },
                        {
                            name: 'Half-day',
                            data: [0, 0, 1, 0, 0, 0, 0],
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#3b82f6'],
                                    [1, '#2563eb']
                                ]
                            },
                            marker: {
                                fillColor: '#3b82f6',
                                lineColor: '#ffffff',
                                lineWidth: 3,
                                radius: 6,
                                symbol: 'triangle'
                            }
                        }
                    ]
                };
                attendanceChartInstance = Highcharts.chart('attendanceChart', {
                    chart: {
                        type: 'area',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
                    },
                    title: {
                        text: 'Sample Attendance Data (API Error)',
                        style: {
                            fontSize: '16px',
                            fontWeight: '600',
                            color: '#1f2937'
                        }
                    },
                    xAxis: {
                        categories: sampleData.days,
                        title: {
                            text: 'Day',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: '#6b7280'
                            }
                        },
                        lineColor: '#e5e7eb',
                        tickColor: '#e5e7eb',
                        gridLineColor: '#f3f4f6'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Employees',
                            style: {
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#6b7280'
                            }
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
                        shared: true,
                        backgroundColor: 'rgba(255, 255, 255, 0.98)',
                        borderColor: '#e5e7eb',
                        borderRadius: 12,
                        shadow: true,
                        style: {
                            fontSize: '13px',
                            fontWeight: '500'
                        },
                        headerFormat: '<span style="font-size: 14px; font-weight: 700; color: #1f2937;">{point.key}</span><br/>',
                        pointFormat: '<span style="color: {point.color}; font-weight: 600;">●</span> {series.name}: <b>{point.y}</b> employees<br/>'
                    },
                    plotOptions: {
                        area: {
                            fillOpacity: 0.3,
                            marker: {
                                enabled: true,
                                radius: 4,
                                lineWidth: 2,
                                lineColor: '#ffffff',
                                symbol: 'circle'
                            },
                            lineWidth: 1.5,
                            states: {
                                hover: {
                                    lineWidth: 2.5,
                                    fillOpacity: 0.5
                                }
                            }
                        }
                    },
                    series: sampleData.series,
                    legend: {
                        enabled: true,
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '13px',
                            fontWeight: '600',
                            color: '#374151'
                        },
                        itemHoverStyle: {
                            color: '#1f2937'
                        },
                        itemDistance: 15
                    },
                    credits: {
                        enabled: false
                    }
                });
            }
        })
        .catch(error => {
            // Show sample data on fetch error
            const sampleData = {
                days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                series: [{
                        name: 'Present',
                        data: [15, 18, 16, 19, 17, 8, 5],
                        color: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            },
                            stops: [
                                [0, '#10b981'],
                                [1, '#059669']
                            ]
                        },
                        marker: {
                            fillColor: '#10b981',
                            lineColor: '#ffffff',
                            lineWidth: 3,
                            radius: 6,
                            symbol: 'circle'
                        }
                    },
                    {
                        name: 'Absent',
                        data: [3, 1, 2, 0, 1, 0, 0],
                        color: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            },
                            stops: [
                                [0, '#ef4444'],
                                [1, '#dc2626']
                            ]
                        },
                        marker: {
                            fillColor: '#ef4444',
                            lineColor: '#ffffff',
                            lineWidth: 3,
                            radius: 6,
                            symbol: 'diamond'
                        }
                    },
                    {
                        name: 'Late',
                        data: [2, 1, 3, 1, 2, 0, 0],
                        color: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            },
                            stops: [
                                [0, '#f59e0b'],
                                [1, '#d97706']
                            ]
                        },
                        marker: {
                            fillColor: '#f59e0b',
                            lineColor: '#ffffff',
                            lineWidth: 3,
                            radius: 6,
                            symbol: 'square'
                        }
                    },
                    {
                        name: 'Half-day',
                        data: [0, 0, 1, 0, 0, 0, 0],
                        color: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            },
                            stops: [
                                [0, '#3b82f6'],
                                [1, '#2563eb']
                            ]
                        },
                        marker: {
                            fillColor: '#3b82f6',
                            lineColor: '#ffffff',
                            lineWidth: 3,
                            radius: 6,
                            symbol: 'triangle'
                        }
                    }
                ]
            };
            attendanceChartInstance = Highcharts.chart('attendanceChart', {
                chart: {
                    type: 'area',
                    backgroundColor: 'transparent',
                    style: {
                        fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                    }
                },
                title: {
                    text: 'Sample Attendance Data (Network Error)',
                    style: {
                        fontSize: '16px',
                        fontWeight: '600',
                        color: '#1f2937'
                    }
                },
                xAxis: {
                    categories: sampleData.days,
                    title: {
                        text: 'Day',
                        style: {
                            fontSize: '14px',
                            fontWeight: '500',
                            color: '#6b7280'
                        }
                    },
                    labels: {
                        style: {
                            fontSize: '12px',
                            color: '#6b7280'
                        }
                    },
                    lineColor: '#e5e7eb',
                    tickColor: '#e5e7eb',
                    gridLineColor: '#f3f4f6'
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Employees',
                        style: {
                            fontSize: '14px',
                            fontWeight: '500',
                            color: '#6b7280'
                        }
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
                    shared: true,
                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                    borderColor: '#e5e7eb',
                    borderRadius: 12,
                    shadow: true,
                    style: {
                        fontSize: '13px',
                        fontWeight: '500'
                    },
                    headerFormat: '<span style="font-size: 14px; font-weight: 700; color: #1f2937;">{point.key}</span><br/>',
                    pointFormat: '<span style="color: {point.color}; font-weight: 600;">●</span> {series.name}: <b>{point.y}</b> employees<br/>'
                },
                plotOptions: {
                    area: {
                        fillOpacity: 0.4,
                        marker: {
                            enabled: true,
                            radius: 6,
                            lineWidth: 3,
                            lineColor: '#ffffff',
                            symbol: 'circle'
                        },
                        lineWidth: 3,
                        states: {
                            hover: {
                                lineWidth: 4,
                                fillOpacity: 0.6
                            }
                        }
                    }
                },
                series: sampleData.series,
                legend: {
                    enabled: true,
                    align: 'center',
                    verticalAlign: 'bottom',
                    layout: 'horizontal',
                    itemStyle: {
                        fontSize: '13px',
                        fontWeight: '600',
                        color: '#374151'
                    },
                    itemHoverStyle: {
                        color: '#1f2937'
                    },
                    itemDistance: 15
                },
                credits: {
                    enabled: false
                }
            });
        });
}
// Department Wise Employees Donut Chart + Summary
function loadDepartmentWiseEmployees() {
    fetch('include/api/department-wise-salary-paid.php?details=1')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.departments.length > 0) {
                // Donut Chart
                const chartData = data.departments.map(dept => ({
                    name: dept.name,
                    y: dept.employees.length,
                    totalSalary: dept.employees.reduce((sum, emp) => sum + Number(emp.salary), 0)
                }));
                const colors = ['#00bfa5', '#02d6ba', '#06b6d4', '#0891b2', '#0d9488', '#14b8a6'];
                Highcharts.chart('deptEmpDonutChart', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent',
                        height: null,
                        width: null,
                        margin: [0, 0, 80, 0],
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
                    },
                    title: {
                        text: null
                    },
                    colors: colors,
                    plotOptions: {
                        pie: {
                            innerSize: '60%',
                            dataLabels: {
                                enabled: false
                            },
                            borderWidth: 0,
                            shadow: false,
                            center: ['50%', '50%']
                        }
                    },
                    tooltip: {
                        pointFormat: '<b>{point.name}</b><br/>Total Employees: <b>{point.y}</b><br/>Total Salary: <b>Rs. {point.totalSalary:,.0f}</b>',
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        borderColor: '#e5e7eb',
                        borderRadius: 8,
                        shadow: true,
                        style: {
                            fontSize: '13px'
                        }
                    },
                    series: [{
                        name: 'Employees',
                        colorByPoint: true,
                        data: chartData
                    }],
                    credits: {
                        enabled: false
                    },
                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 768
                            },
                            chartOptions: {
                                plotOptions: {
                                    pie: {
                                        innerSize: '50%'
                                    }
                                }
                            }
                        }]
                    }
                });
                // Custom Legend
                let legendHtml = '<div style="display: flex; flex-wrap: wrap; gap: 0.70rem; justify-content: center; margin-top: 1rem;">';
                chartData.forEach((dept, index) => {
                    const color = colors[index % colors.length];
                    legendHtml += `
                        <div style="display: flex; align-items: center; background: white; padding: 0.5rem 0.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border-left: 4px solid ${color}; font-size: 0.70rem; font-weight: 500; color: #374151;">
                            <div style="width: 8px; height: 8px; background: ${color}; border-radius: 50%; margin-right: 0.5rem;"></div>
                            ${dept.name} (${dept.y} employees)
                        </div>
                    `;
                });
                legendHtml += '</div>';
                // Add legend to the chart container
                const chartContainer = document.getElementById('deptEmpDonutChart');
                chartContainer.insertAdjacentHTML('afterend', legendHtml);
                // Enhanced Summary Box
                let summaryHtml = '';
                const lightColors = ['#f0fdfa', '#f0fdfa', '#e0f2fe', '#e0f2fe', '#f0fdfa', '#e0f2fe'];
                const borderColors = ['#99f6e4', '#99f6e4', '#7dd3fc', '#7dd3fc', '#99f6e4', '#7dd3fc'];
                const textColors = ['#0d9488', '#0d9488', '#0284c7', '#0284c7', '#0d9488', '#0284c7'];
                chartData.forEach((dept, index) => {
                    const bgColor = lightColors[index % lightColors.length];
                    const borderColor = borderColors[index % borderColors.length];
                    const textColor = textColors[index % textColors.length];
                    summaryHtml += `
                        <div onclick="showDepartmentEmployees('${dept.name}')" style="background: ${bgColor}; border: 1px solid ${borderColor}; padding: 1rem; border-radius: 12px; color: ${textColor}; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); transition: transform 0.2s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 2px 4px rgba(0, 0, 0, 0.05)'">
                            <div style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.4rem;">
                                <i class="fas fa-building me-2"></i>${dept.name}
                            </div>
                            <div style="font-size: 1rem; font-weight: 600; margin-bottom: 0.3rem;">
                                <i class="fas fa-users me-2"></i>Employees: ${dept.y}
                            </div>
                            <div style="font-size: 0.9rem; opacity: 0.8;">
                                <i class="fas fa-rupee-sign me-2"></i>Total Salary: Rs. ${dept.totalSalary.toLocaleString()}
                            </div>
                            <div style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.5rem; font-style: italic;">

                            </div>
                        </div>
                    `;
                });
                document.getElementById('deptEmpSummaryBox').innerHTML = summaryHtml;
            } else {
                // Enhanced No Data UI - Chart Area
                document.getElementById('deptEmpDonutChart').innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 3rem; border: 2px dashed #e2e8f0; border-radius: 16px; background:#f8fafc">
                        <div style="font-size: 3rem; color: #b0b5bd; margin-bottom: 1.5rem; opacity: 0.7;">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4 style="color: #374151; font-weight: 700; margin-bottom: 1rem; font-size: 1.5rem;">No Department Data Available</h4>
                        <p style="color: #6b7280; font-size: 1.1rem; line-height: 1.6; max-width: 400px;">There are currently no departments with active employees. Add departments and employees to see analytics here.</p>
                    </div>
                `;
                // Enhanced No Data UI - Summary Area
                document.getElementById('deptEmpSummaryBox').innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 3rem; border: 2px dashed #e2e8f0; border-radius: 16px; background: #f8fafc">
                        <div style="font-size: 4rem; color: #b0b5bd; margin-bottom: 1.5rem; opacity: 0.7;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h5 style="color: #374151; font-weight: 700; margin-bottom: 1rem; font-size: 1.3rem;">No Summary Available</h5>
                        <p style="color: #6b7280; font-size: 1rem; line-height: 1.6; max-width: 350px;">
                            Department-wise employee and salary summary will appear here once departments are added with active employees.
                        </p>
                    </div>
                `;
            }
        })
        .catch(error => {
            // Enhanced Error UI - Chart Area
            document.getElementById('deptEmpDonutChart').innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 3rem; border: 2px dashed #ef4444; border-radius: 16px; background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(239, 68, 68, 0.02));">
                    <div style="font-size: 5rem; color: #ef4444; margin-bottom: 1.5rem; opacity: 0.7;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem; font-size: 1.5rem;">Error Loading Data</h4>
                    <p style="color: #6b7280; font-size: 1.1rem; line-height: 1.6; max-width: 400px; margin-bottom: 1.5rem;">Unable to load department information. Please try again later.</p>
                    <button onclick="loadDepartmentWiseEmployees()" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; padding: 1rem 2rem; border-radius: 25px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: transform 0.2s ease; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);">
                        <i class="fas fa-redo me-2"></i>Retry
                    </button>
                </div>
            `;
            // Enhanced Error UI - Summary Area
            document.getElementById('deptEmpSummaryBox').innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 3rem; border: 2px dashed #ef4444; border-radius: 16px; background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(239, 68, 68, 0.02));">
                    <div style="font-size: 4rem; color: #ef4444; margin-bottom: 1.5rem; opacity: 0.7;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h5 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem; font-size: 1.3rem;">Connection Error</h5>
                    <p style="color: #6b7280; font-size: 1rem; line-height: 1.6; max-width: 350px;">
                        Failed to load department summary. Please check your connection and try again.
                    </p>
                </div>
            `;
        });
}
// Load leave requests data
function loadLeaveRequests() {
    fetch('include/api/leave-requests.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                // Count by status
                const statusCounts = {
                    pending: 0,
                    approved: 0,
                    rejected: 0
                };

                data.data.forEach(request => {
                    if (statusCounts.hasOwnProperty(request.status)) {
                        statusCounts[request.status]++;
                    }
                });
                // Create chart data
                const chartData = [{
                        name: 'Pending',
                        y: statusCounts.pending,
                        color: '#f59e0b'
                    },
                    {
                        name: 'Approved',
                        y: statusCounts.approved,
                        color: '#10b981'
                    },
                    {
                        name: 'Rejected',
                        y: statusCounts.rejected,
                        color: '#ef4444'
                    }
                ];
                // Create pie chart
                Highcharts.chart('leaveRequestsChart', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, sans-serif'
                        }
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '18px',
                            fontWeight: '600',
                            color: '#1f2937'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.98)',
                        borderColor: '#e5e7eb',
                        borderRadius: 12,
                        shadow: true,
                        style: {
                            fontSize: '13px',
                            fontWeight: '500'
                        },
                        pointFormat: '<span style="color: {point.color}; font-weight: 600;">●</span> {point.name}: <b>{point.y}</b> requests<br/>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.y}',
                                style: {
                                    fontSize: '12px',
                                    fontWeight: '500',
                                    color: '#374151'
                                }
                            },
                            showInLegend: true
                        }
                    },
                    series: [{
                        name: 'Leave Requests',
                        colorByPoint: true,
                        data: chartData
                    }],
                    legend: {
                        enabled: true,
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '12px',
                            fontWeight: '500',
                            color: '#6b7280'
                        },
                        itemHoverStyle: {
                            color: '#1f2937'
                        }
                    },
                    credits: {
                        enabled: false
                    }
                });
            } else {

                // Show empty state instead of blank chart
                const chartContainer = document.getElementById('leaveRequestsChart');
                if (chartContainer) {
                    chartContainer.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                            <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">No Leave Requests</div>
                            <div style="font-size: 1rem; color: #6b7280;">No leave requests found in the system</div>
                            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Employees can submit leave requests to see analytics here</div>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            // Show error state on fetch error
            const chartContainer = document.getElementById('leaveRequestsChart');
            if (chartContainer) {
                chartContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; text-align: center; color: #6b7280; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Error Loading Data</div>
                        <div style="font-size: 1rem; color: #6b7280;">Unable to load leave requests data</div>
                        <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.5rem;">Please check your connection and try again</div>
                    </div>
                `;
            }
        });
}
// Load Job Type Employees
function loadJobTypeEmployees() {
    const jobTypes = ['Internship', 'Probation', 'Permanent'];
    const colors = {
        'Internship': '#00bfa5',
        'Probation': '#00bfa5',
        'Permanent': '#00bfa5'
    };
    jobTypes.forEach(jobType => {
        fetch(`include/api/job-type-employees.php?job_type=${encodeURIComponent(jobType)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update count
                    const countElement = document.getElementById(jobType.toLowerCase() + 'Count');
                    if (countElement) {
                        countElement.textContent = data.count;
                    }
                    // Update employees list
                    const listElement = document.getElementById(jobType.toLowerCase() + 'EmployeesList');
                    if (listElement) {
                        if (data.employees && data.employees.length > 0) {
                            let html = '';
                            data.employees.forEach(employee => {
                                html += `
                                    <div class="employee-card-clickable" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; cursor: pointer; transition: all 0.2s ease;" 
                                         data-employee='${JSON.stringify(employee).replace(/'/g, "&apos;")}' 
                                         onclick="viewEmployeeFromJobType(this)">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 600; color: #1f2937; font-size: 0.9rem;">${createFullName(employee.first_name, employee.middle_name, employee.last_name)}</div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="background: ${colors[jobType]}; color: white; padding: 0.35rem 0.75rem; border-radius: 10px; font-size: 0.7rem; font-weight: 600;">${employee.job_type}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            listElement.innerHTML = html;

                            // Add hover effects for employee cards using event delegation
                            listElement.addEventListener('mouseenter', function(e) {
                                if (e.target.closest('.employee-card-clickable')) {
                                    const card = e.target.closest('.employee-card-clickable');
                                    card.style.background = '#f1f5f9';
                                    card.style.borderColor = '#00bfa5';
                                    card.style.transform = 'translateY(-1px)';
                                    card.style.boxShadow = '0 2px 8px rgba(0, 191, 165, 0.1)';
                                }
                            }, true);

                            listElement.addEventListener('mouseleave', function(e) {
                                if (e.target.closest('.employee-card-clickable')) {
                                    const card = e.target.closest('.employee-card-clickable');
                                    card.style.background = '#f8fafc';
                                    card.style.borderColor = '#e2e8f0';
                                    card.style.transform = 'translateY(0)';
                                    card.style.boxShadow = 'none';
                                }
                            }, true);
                        } else {
                            listElement.innerHTML = `
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; text-align: center; color: #6b7280;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">No ${jobType} Employees</div>
                                    <div style="font-size: 0.9rem;">Currently no employees with ${jobType} status</div>
                                </div>
                            `;
                        }
                    }
                } else {
                    const listElement = document.getElementById(jobType.toLowerCase() + 'EmployeesList');
                    if (listElement) {
                        listElement.innerHTML = `
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; text-align: center; color: #ef4444;">
                                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Error Loading Data</div>
                                <div style="font-size: 0.9rem;">Failed to load ${jobType} employees</div>
                            </div>
                        `;
                    }
                }
            })
            .catch(error => {
                const listElement = document.getElementById(jobType.toLowerCase() + 'EmployeesList');
                if (listElement) {
                    listElement.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; text-align: center; color: #ef4444;">
                            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">Network Error</div>
                            <div style="font-size: 0.9rem;">Unable to load ${jobType} employees</div>
                        </div>
                    `;
                }
            });
    });
}
// Function to view employee details from job type cards
function viewEmployeeFromJobType(element) {
    try {
        const employeeDataString = element.getAttribute('data-employee').replace(/&apos;/g, "'");
        const employee = JSON.parse(employeeDataString);

        // Populate the view employee modal with complete data
        document.getElementById('viewEmployeeFullName').textContent = createFullName(employee.first_name, employee.middle_name, employee.last_name) || 'N/A';
        document.getElementById('viewEmployeePosition').textContent = employee.position || 'N/A';

        // Personal Information
        document.getElementById('viewEmployeeGender').textContent = employee.gender || 'N/A';
        document.getElementById('viewEmployeeDOB').textContent = employee.date_of_birth || 'N/A';
        document.getElementById('viewEmployeeCNIC').textContent = employee.cnic || 'N/A';
        document.getElementById('viewEmployeePhone').textContent = employee.phone || 'N/A';
        document.getElementById('viewEmployeeEmail').textContent = employee.email || 'N/A';
        document.getElementById('viewEmployeeAddress').textContent = employee.address || 'N/A';
        document.getElementById('viewEmployeeEmergencyContact').textContent = employee.emergency_contact || 'N/A';
        document.getElementById('viewEmployeeEmergencyRelation').textContent = employee.emergency_relation || 'N/A';

        // Job Information
        document.getElementById('viewEmployeeDepartment').textContent = employee.department || 'N/A';
        document.getElementById('viewEmployeeSubDepartment').textContent = employee.sub_department || 'N/A';
        const lineManagerElement = document.getElementById('viewEmployeeLineManager');
        if (lineManagerElement) {
            lineManagerElement.textContent = employee.line_manager || 'N/A';
        }
        document.getElementById('viewEmployeeJoiningDate').textContent = employee.joining_date || 'N/A';
        // Format created_at to show only date
        const createdDate = employee.created_at ? new Date(employee.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : 'N/A';
        document.getElementById('viewEmployeeCreatedAt').textContent = createdDate;
        // Format timing to 12-hour format
        let timingText = 'N/A';
        if (employee.shift_name && employee.shift_start_time && employee.shift_end_time) {
            const startTime = new Date('1970-01-01T' + employee.shift_start_time + 'Z');
            const endTime = new Date('1970-01-01T' + employee.shift_end_time + 'Z');
            const startFormatted = startTime.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            const endFormatted = endTime.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            timingText = `${employee.shift_name} (${startFormatted} - ${endFormatted})`;
        }
        document.getElementById('viewEmployeeTiming').textContent = timingText;
        document.getElementById('viewEmployeeJobType').textContent = employee.job_type || 'N/A';
        document.getElementById('viewEmployeeSalary').textContent = employee.salary ? `Rs. ${employee.salary}` : 'N/A';

        // Bank Information
        document.getElementById('viewEmployeeBankName').textContent = employee.bank_name || 'N/A';
        document.getElementById('viewEmployeeAccountType').textContent = employee.account_type || 'N/A';
        document.getElementById('viewEmployeeAccountTitle').textContent = employee.account_title || 'N/A';
        document.getElementById('viewEmployeeAccountNumber').textContent = employee.account_number || 'N/A';
        document.getElementById('viewEmployeeBankBranch').textContent = employee.bank_branch || 'N/A';

        // Education Information
        document.getElementById('viewEmployeeQualificationInstitution').textContent = employee.qualification_institution || 'N/A';
        document.getElementById('viewEmployeeEducationPercentage').textContent = employee.education_percentage || 'N/A';
        document.getElementById('viewEmployeeSpecialization').textContent = employee.specialization || 'N/A';
        document.getElementById('viewEmployeeMaritalStatus').textContent = employee.marital_status || 'N/A';

        // Experience Information
        document.getElementById('viewEmployeeLastOrganization').textContent = employee.last_organization || 'N/A';
        document.getElementById('viewEmployeeLastDesignation').textContent = employee.last_designation || 'N/A';
        document.getElementById('viewEmployeeExperienceFromDate').textContent = employee.experience_from_date || 'N/A';
        document.getElementById('viewEmployeeExperienceToDate').textContent = employee.experience_to_date || 'N/A';

        // Profile Image
        const profileImg = document.getElementById('viewEmployeeProfileImg');
        if (employee.profile_img) {
            $('#viewEmployeeProfileImg').attr('src', '../' + employee.profile_img);
        } else {
            $('#viewEmployeeProfileImg').attr('src', '../assets/images/default-avatar.jpg');
        }

        // Load leave history
        loadEmployeeLeaveHistory(employee.emp_id);

        // Load documents
        loadEmployeeDocuments(employee.emp_id, employee.cv_attachment, employee.id_card_attachment, employee.other_documents);

        // Show the modal
        const viewModal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
        viewModal.show();

    } catch (error) {
        alert('Error loading employee details. Please try again.');
    }
}

// Function to load employee leave history
function loadEmployeeLeaveHistory(empId) {
    fetch(`include/api/employee-leave-history.php?emp_id=${empId}`)
        .then(response => response.json())
        .then(data => {
            const leaveHistoryContainer = document.getElementById('viewEmployeeLeaveHistory');
            if (data.success && data.leaves && data.leaves.length > 0) {
                // Count approved leaves
                const approvedLeaves = data.leaves.filter(leave => leave.status === 'approved');
                const totalDays = approvedLeaves.reduce((sum, leave) => {
                    const startDate = new Date(leave.start_date);
                    const endDate = new Date(leave.end_date);
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    return sum + diffDays;
                }, 0);

                // Update the label with total count
                const approvedLeavesLabel = document.getElementById('approvedLeavesLabel');
                if (approvedLeavesLabel) {
                    approvedLeavesLabel.innerHTML = `<i class="fas fa-calendar-times info-icon"></i> Approved Leaves (${totalDays} days)`;
                }

                // Group leaves by type and calculate days
                const leaveGroups = {};
                data.leaves.forEach(leave => {
                    const startDate = new Date(leave.start_date);
                    const endDate = new Date(leave.end_date);
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                    if (!leaveGroups[leave.leave_type]) {
                        leaveGroups[leave.leave_type] = {
                            type: leave.leave_type,
                            totalDays: 0,
                            leaves: []
                        };
                    }
                    leaveGroups[leave.leave_type].totalDays += diffDays;
                    leaveGroups[leave.leave_type].leaves.push(leave);
                });

                let leaveHistoryHtml = '<div class="row">';
                Object.values(leaveGroups).forEach(group => {
                    leaveHistoryHtml += `
                        <div class="col-sm-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center p-2" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; border: 1px solid #dee2e6;">
                                <span class="fw-medium" style="color: #495057;">${group.type}:</span>
                                <span class="badge" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600;">${group.totalDays} days</span>
                            </div>
                        </div>
                    `;
                });
                leaveHistoryHtml += '</div>';
                leaveHistoryContainer.innerHTML = leaveHistoryHtml;
            } else {
                leaveHistoryContainer.innerHTML = '<div class="text-center text-muted py-3">No leave data available</div>';
                const approvedLeavesLabel = document.getElementById('approvedLeavesLabel');
                if (approvedLeavesLabel) {
                    approvedLeavesLabel.innerHTML = '<i class="fas fa-calendar-times info-icon"></i> Approved Leaves (0 days)';
                }
            }
        })
        .catch(error => {
            document.getElementById('viewEmployeeLeaveHistory').innerHTML = '<div class="text-center text-muted py-3">Error loading leave history</div>';
        });
}

// Function to load employee documents
function loadEmployeeDocuments(empId, cvAttachment, idCardAttachment, otherDocuments) {
    const documentsContainer = document.getElementById('employeeDocumentsContainer');
    let documentsHtml = '';

    // Debug: Log the paths to understand the issue

    // CV Document
    if (cvAttachment) {
        const cvFileName = cvAttachment.split('/').pop();
        const cvExtension = cvFileName.split('.').pop().toLowerCase();
        const cvIcon = cvExtension === 'pdf' ? 'fa-file-pdf' : 'fa-file-word';

        // Create correct path - main directory ke uploads folder ka absolute path
        let cvPath = '../uploads/joining_documents/' + cvFileName;

        documentsHtml += `
            <div class="document-item mb-2">
                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                    <div class="d-flex align-items-center">
                        <i class="fas ${cvIcon} me-2"></i>
                        <span class="document-name">CV/Resume</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="${cvPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="${cvPath}" download="${cvFileName}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    // ID Card Document
    if (idCardAttachment) {
        const idCardFileName = idCardAttachment.split('/').pop();
        const idCardExtension = idCardFileName.split('.').pop().toLowerCase();
        let idCardIcon = 'fa-file-image';

        if (idCardExtension === 'pdf') {
            idCardIcon = 'fa-file-pdf';
        } else if (['jpg', 'jpeg', 'png'].includes(idCardExtension)) {
            idCardIcon = 'fa-file-image';
        }

        // Create correct path - main directory ke uploads folder ka absolute path
        let idCardPath = '../uploads/joining_documents/' + idCardFileName;

        documentsHtml += `
            <div class="document-item mb-2">
                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                    <div class="d-flex align-items-center">
                        <i class="fas ${idCardIcon} me-2"></i>
                        <span class="document-name">ID Card</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="${idCardPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="${idCardPath}" download="${idCardFileName}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    // Other Documents
    if (otherDocuments) {
        try {
            const otherDocs = JSON.parse(otherDocuments);
            otherDocs.forEach((doc, index) => {
                const fileName = doc.split('/').pop();
                const extension = fileName.split('.').pop().toLowerCase();
                let icon = 'fa-file';
                let color = 'text-secondary';

                if (extension === 'pdf') {
                    icon = 'fa-file-pdf';
                    color = 'text-danger';
                } else if (['doc', 'docx'].includes(extension)) {
                    icon = 'fa-file-word';
                    color = 'text-primary';
                } else if (['jpg', 'jpeg', 'png'].includes(extension)) {
                    icon = 'fa-file-image';
                    color = 'text-success';
                }

                // Create correct path - main directory ke uploads folder ka absolute path
                let docPath = '../uploads/joining_documents/' + fileName;

                documentsHtml += `
                    <div class="document-item mb-2">
                        <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas ${icon} me-2"></i>
                                <span class="document-name">Document ${index + 1}</span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="${docPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="${docPath}" download="${fileName}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
        } catch (error) {
        }
    }

    if (documentsHtml === '') {
        documentsHtml = '<div class="text-center text-muted py-3">No documents uploaded</div>';
    }

    documentsContainer.innerHTML = documentsHtml;
}

// Enhanced Highcharts Global Options
Highcharts.setOptions({
    colors: ['#00bfa5', '#02d6ba', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
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
            color: '#1f2937'
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

// Global variables to store employees data for filtering
let allEmployeesData = [];
let presentEmployeesData = [];
let leaveEmployeesData = [];

// Load departments for filter dropdown
function loadDepartmentsForFilter() {
    fetch('include/api/department.php')
        .then(response => response.json())
        .then(data => {
            const filterDropdown = document.getElementById('departmentFilter');
            if (data.success && data.data) {
                // Clear existing options except "All Departments"
                filterDropdown.innerHTML = '<option value="">All Departments</option>';

                // Add department options
                data.data.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.dept_name;
                    option.textContent = dept.dept_name;
                    filterDropdown.appendChild(option);
                });
            } else {
            }
        })
        .catch(error => {
        });
}

// Filter employees by department
function filterEmployeesByDepartment() {
    const selectedDepartment = document.getElementById('departmentFilter').value;
    const tableBody = document.getElementById('totalEmployeesTableBody');

    if (selectedDepartment === '') {
        // Show all employees
        populateModalTable('totalEmployeesTableBody', allEmployeesData, 'total_employees');
    } else {
        // Filter employees by selected department
        const filteredEmployees = allEmployeesData.filter(emp =>
            emp.department === selectedDepartment
        );
        populateModalTable('totalEmployeesTableBody', filteredEmployees, 'total_employees');
    }
}

// Clear department filter
function clearDepartmentFilter() {
    document.getElementById('departmentFilter').value = '';
    populateModalTable('totalEmployeesTableBody', allEmployeesData, 'total_employees');
}

// Load departments for Present Today filter dropdown
function loadDepartmentsForPresentFilter() {
    fetch('include/api/department.php')
        .then(response => response.json())
        .then(data => {
            const filterDropdown = document.getElementById('presentDepartmentFilter');
            if (data.success && data.data) {
                // Clear existing options except "All Departments"
                filterDropdown.innerHTML = '<option value="">All Departments</option>';

                // Add department options
                data.data.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.dept_name;
                    option.textContent = dept.dept_name;
                    filterDropdown.appendChild(option);
                });
            } else {
            }
        })
        .catch(error => {
        });
}

// Filter present employees by department
function filterPresentEmployeesByDepartment() {
    const selectedDepartment = document.getElementById('presentDepartmentFilter').value;
    const tableBody = document.getElementById('presentTodayTableBody');

    if (selectedDepartment === '') {
        // Show all present employees
        populateModalTable('presentTodayTableBody', presentEmployeesData, 'present_today');
    } else {
        // Filter present employees by selected department
        const filteredEmployees = presentEmployeesData.filter(emp =>
            emp.department === selectedDepartment
        );
        populateModalTable('presentTodayTableBody', filteredEmployees, 'present_today');
    }
}

// Clear present department filter
function clearPresentDepartmentFilter() {
    document.getElementById('presentDepartmentFilter').value = '';
    populateModalTable('presentTodayTableBody', presentEmployeesData, 'present_today');
}

// Load departments for Leave employees filter dropdown
function loadDepartmentsForLeaveFilter() {
    fetch('include/api/department.php')
        .then(response => response.json())
        .then(data => {
            const filterDropdown = document.getElementById('leaveDepartmentFilter');
            if (data.success && data.data) {
                // Clear existing options except "All Departments"
                filterDropdown.innerHTML = '<option value="">All Departments</option>';

                // Add department options
                data.data.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.dept_name;
                    option.textContent = dept.dept_name;
                    filterDropdown.appendChild(option);
                });
            } else {
            }
        })
        .catch(error => {
        });
}

// Filter leave employees by department
function filterLeaveEmployeesByDepartment() {
    const selectedDepartment = document.getElementById('leaveDepartmentFilter').value;
    const tableBody = document.getElementById('onLeaveTableBody');

    if (selectedDepartment === '') {
        // Show all leave employees
        populateModalTable('onLeaveTableBody', leaveEmployeesData, 'on_leave');
    } else {
        // Filter leave employees by selected department
        const filteredEmployees = leaveEmployeesData.filter(emp =>
            emp.department === selectedDepartment
        );
        populateModalTable('onLeaveTableBody', filteredEmployees, 'on_leave');
    }
}

// Clear leave department filter
function clearLeaveDepartmentFilter() {
    document.getElementById('leaveDepartmentFilter').value = '';
    populateModalTable('onLeaveTableBody', leaveEmployeesData, 'on_leave');
}

// Global variable to store department employees data
let currentDepartmentEmployees = [];

// Show Department Employees Modal
function showDepartmentEmployees(departmentName) {
    // Update modal title
    document.getElementById('departmentEmployeesModalLabel').innerHTML = `
        <i class="fas fa-users me-2"></i>${departmentName} - Employees
    `;

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('departmentEmployeesModal'));
    modal.show();

    // Reset content to loading state
    document.getElementById('departmentEmployeesContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading employees...</p>
        </div>
    `;

    // Fetch department employees
    fetch(`include/api/department-wise-salary-paid.php?details=1`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.departments) {
                // Find the specific department
                const department = data.departments.find(dept => dept.name === departmentName);

                if (department && department.employees.length > 0) {
                    // Build search filter and employees table
                    let tableHtml = `
                        <div class="mb-3 d-flex gap-3 align-items-end">
                            <div class="flex-grow-1">
                                <label class="form-label fw-semibold text-dark">Name</label>
                                <input type="text" class="form-control" id="employeeSearchFilter" placeholder="Enter Name" onkeyup="filterDepartmentEmployees()">
                            </div>
                            <div>
                                <button class="btn btn-primary px-4 py-2" type="button" onclick="clearEmployeeSearch()" style="background-color: #00bfa5; border-color: #00bfa5;">
                                    <i class="fas fa-times me-2"></i>Clear Filters
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Email</th>
                                        <th>Salary</th>
                                    </tr>
                                </thead>
                                <tbody id="departmentEmployeesTableBody">
                    `;

                    // Store employees data globally
                    currentDepartmentEmployees = department.employees;

                    tableHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    document.getElementById('departmentEmployeesContent').innerHTML = tableHtml;

                    // Populate table with all employees initially
                    populateDepartmentEmployeesTable(currentDepartmentEmployees);
                } else {
                    // No employees found
                    document.getElementById('departmentEmployeesContent').innerHTML = `
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-users text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                            <h5 class="text-muted">No Employees Found</h5>
                            <p class="text-muted">There are no employees in the ${departmentName} department.</p>
                        </div>
                    `;
                }
            } else {
                // API Error
                document.getElementById('departmentEmployeesContent').innerHTML = `
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="text-warning">Error Loading Data</h5>
                        <p class="text-muted">Unable to load department employees. Please try again.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('departmentEmployeesContent').innerHTML = `
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-danger">Network Error</h5>
                    <p class="text-muted">Unable to connect to server. Please check your connection.</p>
                </div>
            `;
        });
}

// Populate department employees table
function populateDepartmentEmployeesTable(employees) {
    const tableBody = document.getElementById('departmentEmployeesTableBody');
    if (!tableBody) return;

    let tableHtml = '';
    employees.forEach(emp => {
        tableHtml += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <div class="fw-semibold">${emp.name}</div>
                        </div>
                    </div>
                </td>
                <td>
                    ${emp.email || 'N/A'}
                </td>
                <td>
                    <i class="fas fa-rupee-sign me-1"></i>
                    ${emp.salary ? Number(emp.salary).toLocaleString('en-IN') : 'N/A'}
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = tableHtml;
}

// Filter department employees by search term
function filterDepartmentEmployees() {
    const searchTerm = document.getElementById('employeeSearchFilter').value.toLowerCase().trim();
    const tableBody = document.getElementById('departmentEmployeesTableBody');

    if (!searchTerm) {
        // Show all employees if search is empty
        populateDepartmentEmployeesTable(currentDepartmentEmployees);
    } else {
        // Filter employees by name or email
        const filteredEmployees = currentDepartmentEmployees.filter(emp =>
            (emp.name && emp.name.toLowerCase().includes(searchTerm)) ||
            (emp.email && emp.email.toLowerCase().includes(searchTerm))
        );

        if (filteredEmployees.length > 0) {
            populateDepartmentEmployeesTable(filteredEmployees);
        } else {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-search me-2"></i>
                            No employees found matching "${searchTerm}"
                        </div>
                    </td>
                </tr>
            `;
        }
    }
}

// Clear employee search
function clearEmployeeSearch() {
    document.getElementById('employeeSearchFilter').value = '';
    populateDepartmentEmployeesTable(currentDepartmentEmployees);
}