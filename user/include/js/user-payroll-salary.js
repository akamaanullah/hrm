// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

var payrollTableInstance;

$(document).ready(function () {
    loadPayrollData(); // Payroll table load karein

    // Set up polling for real-time updates
    let pollingInterval = setInterval(loadPayrollData, 3000);
    let isPollingPaused = false;

    // Clear interval when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(pollingInterval);
        } else {
            if (!isPollingPaused) {
                pollingInterval = setInterval(loadPayrollData, 3000);
            }
        }
    });

    // Pause polling when any input is focused
    $(document).on('focus', 'input, textarea, select', function() {
        if (!isPollingPaused) {
            clearInterval(pollingInterval);
            isPollingPaused = true;
        }
    });

    // Resume polling when input loses focus
    $(document).on('blur', 'input, textarea, select', function() {
        if (isPollingPaused && !document.hidden) {
            pollingInterval = setInterval(loadPayrollData, 3000);
            isPollingPaused = false;
        }
    });
});

function loadPayrollData() {
    $.ajax({
        url: 'include/api/user-payroll-salary.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            // Sort data by payment_date in descending order
            data.sort(function(a, b) {
                var dateA = new Date(a.payment_date || '1900-01-01');
                var dateB = new Date(b.payment_date || '1900-01-01');
                return dateB - dateA;
            });

            // Check if DataTable exists
            if ($.fn.DataTable.isDataTable('#payrollTable')) {
                // Get current DataTable instance
                var table = $('#payrollTable').DataTable();
                
                // Clear existing data
                table.clear();
                
                // Add new data
                data.forEach(function (row) {
                    // Format the date
                    var formattedDate = '-';
                    if (row.payment_date) {
                        var date = new Date(row.payment_date);
                        formattedDate = ('0' + date.getDate()).slice(-2) + '/' +
                                      ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                                      date.getFullYear();
                    }

                    var rowData = [
                        row.emp_id || '-',
                        createFullName(row.first_name, row.middle_name, row.last_name) || '-',
                        row.designation || '-',
                        row.basic_salary || '-',
                        row.leave_days !== undefined && row.leave_days !== null ? row.leave_days : '-',
                        row.late_days !== undefined && row.late_days !== null ? row.late_days : '-',
                        row.half_day_days !== undefined && row.half_day_days !== null ? row.half_day_days : '-',
                        row.total_earnings || '-',
                        row.total_deductions || '-',
                        row.net_salary || '-',
                        row.bank || '-',
                        formattedDate,
                        '<button type="button" class="btn btn-outline-primary btn-sm payslip-view-btn" style="border-radius:8px;display:flex;align-items:center;justify-content:center;padding:8px 8px;" onclick="viewPayslip(this)" data-bs-toggle="modal" data-bs-target="#viewPayslipModal" data-row=\'' + JSON.stringify(row) + '\'><i class="fas fa-eye"></i></button>'
                    ];
                    table.row.add(rowData);
                });
                
                // Redraw table with sorting
                table.order([11, 'desc']).draw();
            } else {
                // First time load - initialize DataTable
                var tableBody = $('#payrollTable tbody');
                tableBody.empty();

                data.forEach(function (row) {
                    // Format the date
                    var formattedDate = '-';
                    if (row.payment_date) {
                        var date = new Date(row.payment_date);
                        formattedDate = ('0' + date.getDate()).slice(-2) + '/' +
                                      ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                                      date.getFullYear();
                    }

                    var rowHtml = '<tr>';
                    rowHtml += '<td>' + (row.emp_id || '-') + '</td>';
                    rowHtml += '<td>' + (createFullName(row.first_name, row.middle_name, row.last_name) || '-') + '</td>';
                    rowHtml += '<td>' + (row.designation || '-') + '</td>';
                    rowHtml += '<td>' + (row.basic_salary || '-') + '</td>';
                    rowHtml += '<td>' + (row.leave_days !== undefined && row.leave_days !== null ? row.leave_days : '-') + '</td>';
                    rowHtml += '<td>' + (row.late_days !== undefined && row.late_days !== null ? row.late_days : '-') + '</td>';
                    rowHtml += '<td>' + (row.half_day_days !== undefined && row.half_day_days !== null ? row.half_day_days : '-') + '</td>';
                    rowHtml += '<td>' + (row.total_earnings || '-') + '</td>';
                    rowHtml += '<td>' + (row.total_deductions || '-') + '</td>';
                    rowHtml += '<td>' + (row.net_salary || '-') + '</td>';
                    rowHtml += '<td>' + (row.bank || '-') + '</td>';
                    rowHtml += '<td>' + formattedDate + '</td>';
                    rowHtml += '<td><button type="button" class="btn btn-outline-primary btn-sm payslip-view-btn" style="border-radius:8px;display:flex;align-items:center;justify-content:center;padding:8px 8px;" onclick="viewPayslip(this)" data-bs-toggle="modal" data-bs-target="#viewPayslipModal" data-row=\'' + JSON.stringify(row) + '\'><i class="fas fa-eye"></i></button></td>';
                    rowHtml += '</tr>';

                    tableBody.append(rowHtml);
                });

                // Initialize DataTable
                payrollTableInstance = $('#payrollTable').DataTable({
                    dom: 'tip',
                    paging: true,
                    searching: false,
                    ordering: true,
                    info: true,
                    pageLength: 10,
                    lengthChange: false,
                    order: [[12, 'desc']],
                    columnDefs: [
                        {
                            targets: 12, // Date column index
                            type: 'date-eu', // European date format (dd/mm/yyyy)
                            render: function(data, type, row) {
                                if (type === 'sort') {
                                    // Convert dd/mm/yyyy to YYYY-MM-DD for proper sorting
                                    if (data && data !== '-') {
                                        var parts = data.split('/');
                                        return parts[2] + parts[1] + parts[0];
                                    }
                                    return '';
                                }
                                return data;
                            }
                        }
                    ]
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Data load nahi ho saka: " + error);
        }
    });
}

function viewPayslip(btn) {
    var row = $(btn).data('row');
    if (!row) return;

    // Employee Info
    $('#empName').text(createFullName(row.first_name, row.middle_name, row.last_name) || row.emp_id || '');
    $('#empDesignation').text(row.designation || '');
    $('#empDepartment').text(row.department || '');
    $('#joiningDate').text(row.joining_date || '');
    $('#empBank').text(row.bank || '');
    $('#payPeriod').text(row.payment_date || '');

    // Salary Fields
    $('#basicSalary').text(row.basic_salary || '');
    $('#fuelAllowance').text(row.fuel_allowance || '');
    $('#houseRentAllowance').text(row.house_rent_allowance || '');
    $('#utilityAllowance').text(row.utility_allowance || '');
    $('#mobileAllowance').text(row.mobile_allowance || '');
    
    $('#providentFund').text(row.provident_fund || '');
    $('#professionalTax').text(row.professional_tax || '');
    $('#loan').text(row.loan || '');
    $('#totalLeaves').text(row.leave_days !== undefined ? row.leave_days : '');
    $('#totalLate').text(row.late_days !== undefined ? row.late_days : '');
    $('#totalHalfday').text(row.half_day_days !== undefined ? row.half_day_days : '');
    $('#totalEarnings').text(row.total_earnings || '');
    $('#grossEarnings').text(row.total_earnings !== undefined ? row.total_earnings : '');
    $('#totalDeductions').text(row.total_deductions !== undefined ? row.total_deductions : '');
    $('#totalDeduction').text(row.total_deductions !== undefined ? row.total_deductions : '');
    $('#netSalary').text(row.net_salary || '');

    // Amount in words
    var amountInWords = convertNumberToWords(parseInt(row.net_salary || 0));
    $('.amount-in-words h6').text('Amount: Rs. ' + (row.net_salary || ''));
    $('.amount-in-words p').text(amountInWords);
}

function convertNumberToWords(amount) {
    var a = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
    ];
    var b = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    if ((amount = amount.toString()).length > 9) return 'Overflow';
    var n = ('000000000' + amount).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{3})$/);
    if (!n) return; 
    var str = '';
    str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' Crore ' : '';
    str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' Lac ' : '';
    str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' Thousand ' : '';
    str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + ' ' : '';
    return str.replace(/\s+/g, ' ').trim() + ' Rupees Only';
}

function printPayslip() {
    var printContents = document.querySelector('.payslip-container').outerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // Page ko reload kar dein taake sab wapas aa jaye
}

