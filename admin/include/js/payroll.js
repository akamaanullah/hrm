// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

$(document).ready(function() {
    // Initialize DataTable
    const payrollTable = $('#payrollTable').DataTable({
        processing: true,
        serverSide: false,
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        lengthChange: true,
        dom: '<"top"l>rt<"bottom"ip>',
        ordering: false, // Disable DataTable ordering to prevent conflict with custom grouping
        buttons: [{
            extend: 'collection',
            text: '<i class="fas fa-download me-1"></i> Export',
            className: 'btn btn-light text-secondary',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-2 text-success"></i>Export to Excel',
                    className: 'dropdown-item',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                    }
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-2 text-primary"></i>Export to CSV',
                    className: 'dropdown-item',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                    }
                }
            ]
        }],
        language: {
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Move export buttons to container
    payrollTable.buttons().container().appendTo('#exportButtonContainer');

    // Custom filter for month and year
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'payrollTable') return true;

        var monthFilter = $('#selectedMonth').val();
        var yearFilter = $('#selectedYear').val();
        var rowDate = data[11]; // Payment date column

        if (!monthFilter && !yearFilter) return true;
        if (!rowDate || rowDate === 'N/A' || rowDate === '-') return false;

        // Convert DD/MM/YYYY to extract month and year
        var parts = rowDate.split('/');
        if (parts.length === 3) {
            var day = parts[0];
            var month = parts[1];
            var year = parts[2];

            var monthMatch = !monthFilter || month === monthFilter;
            var yearMatch = !yearFilter || year === yearFilter;

            return monthMatch && yearMatch;
        }

        return false;
    });

    // Load initial data
    function loadPayroll() {
        $.ajax({
            url: 'include/api/payroll.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    payrollTable.clear();

                    // Group data by month and year
                    var groupedData = {};
                    var monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];

                    response.data.forEach(function(row) {
                        // Format the date
                        var formattedDate = 'N/A';
                        var monthKey = '';
                        if (row.payment_date) {
                            var date = new Date(row.payment_date);
                            formattedDate = ('0' + date.getDate()).slice(-2) + '/' +
                                ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                                date.getFullYear();

                            // Create month key for grouping
                            var month = date.getMonth() + 1;
                            var year = date.getFullYear();
                            monthKey = year + '-' + ('0' + month).slice(-2);
                        } else {
                            monthKey = 'No-Date';
                        }

                        // Add formatted date to row
                        row.formatted_date = formattedDate;

                        // Ensure month is present in row object for edit
                        if (!row.month && row.payment_date) {
                            try {
                                row.month = row.payment_date.split('-')[1];
                            } catch (e) {
                                row.month = '';
                            }
                        }

                        // Group by month
                        if (!groupedData[monthKey]) {
                            groupedData[monthKey] = [];
                        }
                        groupedData[monthKey].push(row);
                    });

                    // Sort months in descending order (newest first - December, November, October...)
                    var sortedMonths = Object.keys(groupedData).sort(function(a, b) {
                        if (a === 'No-Date') return 1;
                        if (b === 'No-Date') return -1;
                        return b.localeCompare(a); // This will sort 2025-12, 2025-11, 2025-10...
                    });

                    // Add grouped data to table (without headers)
                    sortedMonths.forEach(function(monthKey) {
                        var monthData = groupedData[monthKey];

                        // Add month data rows directly (no headers)
                        monthData.forEach(function(row) {
                            // Debug log for half-day data
                            if (row.emp_id == 60) {
                                console.log('Loading data for emp 60 - half_day_days:', row.half_day_days);
                            }

                            payrollTable.row.add([
                                row.emp_id || '',
                                createFullName(row.first_name, row.middle_name, row.last_name) || '',
                                row.position || 'N/A',
                                row.basic_salary || '0',
                                row.leave_days || '0',
                                row.late_days || '0',
                                row.half_day_days || '0',
                                row.total_earnings || '0',
                                row.total_deductions || '0',
                                row.net_salary || '0',
                                row.bank || 'N/A',
                                row.formatted_date,
                                `<button class="btn btn-sm btn-outline-primary" onclick="viewPayslip(this)" data-bs-toggle="modal" data-bs-target="#viewPayslipModal" data-row='${JSON.stringify(row)}'>
                                    <i class="fas fa-eye"></i>
                                </button>`,
                                `<button class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editPayrollModal" data-row='${JSON.stringify(row)}'>
                                    <i class="fas fa-edit"></i>
                                </button>`
                            ]);
                        });
                    });

                    payrollTable.draw();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading payroll data:', error);
            }
        });
    }

    // Load initial data
    loadPayroll();

    // Handle filter form submission
    $('#payrollFilterForm').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });

    // Add real-time filtering
    $('#employeeIdFilter, #nameFilter, #designationFilter').on('input', function() {
        applyFilters();
    });

    // Month Year Picker functionality
    let currentPickerYear = new Date().getFullYear();
    let selectedPickerMonth = '';
    let selectedPickerYear = '';

    // Initialize month year picker
    $('#currentYear').text(currentPickerYear);

    // Month Year Picker button click
    $('#monthYearBtn').on('click', function() {
        $('#monthYearPickerModal').modal('show');
    });

    // Year navigation
    $('#yearPrevBtn').on('click', function() {
        currentPickerYear--;
        $('#currentYear').text(currentPickerYear);
    });

    $('#yearNextBtn').on('click', function() {
        currentPickerYear++;
        $('#currentYear').text(currentPickerYear);
    });

    // Month selection with professional styling
    $('.month-btn').on('click', function() {
        // Reset all month buttons
        $('.month-btn').removeClass('btn-primary text-white').addClass('btn-light text-dark');

        // Highlight selected month
        $(this).removeClass('btn-light text-dark').addClass('btn-primary text-white');
        selectedPickerMonth = $(this).data('month');
    });

    // Add hover effects for month buttons
    $('.month-btn').hover(
        function() {
            if (!$(this).hasClass('btn-primary')) {
                $(this).removeClass('btn-light').addClass('btn-outline-primary');
            }
        },
        function() {
            if (!$(this).hasClass('btn-primary')) {
                $(this).removeClass('btn-outline-primary').addClass('btn-light');
            }
        }
    );

    // Clear selection function (for external use)
    function clearMonthYearSelection() {
        $('.month-btn').removeClass('btn-primary text-white').addClass('btn-light text-dark');
        selectedPickerMonth = '';
        selectedPickerYear = '';
        currentPickerYear = new Date().getFullYear();
        $('#currentYear').text(currentPickerYear);
    }

    // Apply filter
    $('#applyMonthYear').on('click', function() {
        selectedPickerYear = currentPickerYear.toString();

        if (selectedPickerMonth && selectedPickerYear) {
            var monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            var displayText = monthNames[parseInt(selectedPickerMonth)] + ' ' + selectedPickerYear;
            $('#monthYearPicker').val(displayText);

            // Set hidden values
            $('#selectedMonth').val(selectedPickerMonth);
            $('#selectedYear').val(selectedPickerYear);

            // Apply filter
            applyFilters();

            // Close modal
            $('#monthYearPickerModal').modal('hide');
        } else if (selectedPickerYear && !selectedPickerMonth) {
            $('#monthYearPicker').val(selectedPickerYear);
            $('#selectedMonth').val('');
            $('#selectedYear').val(selectedPickerYear);
            applyFilters();
            $('#monthYearPickerModal').modal('hide');
        }
    });

    // Function to apply filters
    function applyFilters() {
        const employeeId = $('#employeeIdFilter').val().toLowerCase();
        const name = $('#nameFilter').val().toLowerCase();
        const designation = $('#designationFilter').val().toLowerCase();

        // Clear previous filters
        payrollTable.search('').columns().search('');

        // Apply filters
        if (employeeId) {
            payrollTable.column(0).search(employeeId);
        }
        if (name) {
            payrollTable.column(1).search(name);
        }
        if (designation) {
            payrollTable.column(2).search(designation);
        }

        // Draw table with all filters (including custom month/year filter)
        payrollTable.draw();
    }

    // Reset filters
    $('#payrollFilterForm').on('reset', function() {
        payrollTable.search('').columns().search('').draw();
        $('#monthYearPicker').val(''); // Clear month year picker
        $('#selectedMonth').val(''); // Clear selected month
        $('#selectedYear').val(''); // Clear selected year
        payrollTable.draw();
    });

    // Edit Payroll Calculation Logic
    function calculateEditPayroll() {
        const basicSalary = parseFloat($('#editBasicSalary').val()) || 0;
        const fuelAllowance = parseFloat($('#editFuelAllowance').val()) || 0;
        const houseRentAllowance = parseFloat($('#editHouseRentAllowance').val()) || 0;
        const utilityAllowance = parseFloat($('#editUtilityAllowance').val()) || 0;
        const mobileAllowance = parseFloat($('#editMobileAllowance').val()) || 0;

        const providentFund = parseFloat($('#editProvidentFund').val()) || 0;
        const professionalTax = parseFloat($('#editProfessionalTax').val()) || 0;
        const loan = parseFloat($('#editLoan').val()) || 0;
        const totalLate = parseInt($('#editTotalLate').val()) || 0;
        const totalLeavesInput = parseInt($('#editTotalLeaves').val()) || 0;
        const totalHalfday = parseInt($('#editTotalHalfday').val()) || 0;

        // 3 late = 1 leave
        const lateToLeave = Math.floor(totalLate / 3);
        const totalLeaves = totalLeavesInput + lateToLeave;
        // per day salary ab gross (totalEarnings) se nikalo
        const totalEarnings = basicSalary + fuelAllowance + houseRentAllowance + utilityAllowance + mobileAllowance;
        const perDaySalary = totalEarnings / 30;
        const leaveDeduction = totalLeaves * perDaySalary;

        // Half-day deduction calculation (half-day = 0.5 day salary cut)
        const halfdayDeduction = totalHalfday * (perDaySalary * 0.5);

        // Deductions
        const totalDeductions = providentFund + professionalTax + loan + leaveDeduction + halfdayDeduction;
        // Net Salary
        const netSalary = totalEarnings - totalDeductions;

        $('#editTotalEarnings').val(totalEarnings.toFixed(2));
        $('#editNetSalary').val(netSalary.toFixed(2));
    }

    // Jab bhi relevant fields change ho, calculation karo
    $('#editBasicSalary, #editFuelAllowance, #editHouseRentAllowance, #editUtilityAllowance, #editMobileAllowance, #editProvidentFund, #editProfessionalTax, #editLoan, #editTotalLeaves, #editTotalLate, #editTotalHalfday').on('input', function() {
        calculateEditPayroll();
    });

    // Modal open hote hi calculation karo (agar values pehle se hain)
    $('#editPayrollModal').on('shown.bs.modal', function() {
        calculateEditPayroll();
    });

    // Edit Payroll Modal: edit-btn click pe data set karo
    $(document).on('click', '.edit-btn', function() {
        var row = $(this).data('row');
        if (!row) return;

        // Month ko int bana ke 2 digit format me set karo
        var month = row.month ? parseInt(row.month, 10) : '';
        $('#editMonth').val(month.toString().padStart(2, '0'));

        // Year ko hidden field me set karo (agar modal me nahi hai to data attribute pe rakh lo)
        $('#editPayrollForm').data('year', row.year ? parseInt(row.year, 10) : new Date().getFullYear());
        // Old month/year/emp_id bhi set karo
        $('#editPayrollForm').data('oldEmpId', row.emp_id);
        $('#editPayrollForm').data('oldMonth', month.toString().padStart(2, '0'));
        $('#editPayrollForm').data('oldYear', row.year ? parseInt(row.year, 10) : new Date().getFullYear());

        $('#editEmployeeId').val(row.emp_id || '');
        $('#editBasicSalary').val(row.basic_salary || '');
        $('#editFuelAllowance').val(row.fuel_allowance || '');
        $('#editHouseRentAllowance').val(row.house_rent_allowance || '');
        $('#editUtilityAllowance').val(row.utility_allowance || '');
        $('#editMobileAllowance').val(row.mobile_allowance || '');

        $('#editProvidentFund').val(row.provident_fund || '');
        $('#editLoan').val(row.loan || '');
        $('#editTotalLeaves').val(row.leave_days || '');
        $('#editTotalLate').val(row.late_days || '');
        $('#editTotalHalfday').val(row.half_day_days || '');
        console.log('Setting half-day value:', row.half_day_days, 'for employee:', row.emp_id);
        $('#editBank').val(row.bank || '');
        $('#editPaymentDate').val(row.payment_date || '');
        $('#editProfessionalTax').val(row.professional_tax || '');
        $('#editTotalEarnings').val(row.total_earnings || '0.00');
        $('#editNetSalary').val(row.net_salary || '0.00');
    });



    // Edit Payroll Save Changes
    $('#editPayrollForm').on('submit', function(e) {
        e.preventDefault();
        var year = $('#editPayrollForm').data('year') || new Date().getFullYear();
        var oldEmpId = $('#editPayrollForm').data('oldEmpId');
        var oldMonth = $('#editPayrollForm').data('oldMonth');
        var oldYear = $('#editPayrollForm').data('oldYear');
        var data = {
            emp_id: $('#editEmployeeId').val(),
            month: parseInt($('#editMonth').val(), 10),
            year: parseInt(year, 10),
            oldEmpId: oldEmpId,
            oldMonth: oldMonth,
            oldYear: oldYear,
            basic_salary: $('#editBasicSalary').val(),
            fuel_allowance: $('#editFuelAllowance').val(),
            house_rent_allowance: $('#editHouseRentAllowance').val(),
            utility_allowance: $('#editUtilityAllowance').val(),
            mobile_allowance: $('#editMobileAllowance').val(),

            provident_fund: $('#editProvidentFund').val(),
            loan: $('#editLoan').val(),
            leave_days: $('#editTotalLeaves').val(),
            late_days: $('#editTotalLate').val(),
            half_day_days: $('#editTotalHalfday').val(),
            bank: $('#editBank').val(),
            payment_date: $('#editPaymentDate').val(),
            professional_tax: $('#editProfessionalTax').val(),
            total_earnings: $('#editTotalEarnings').val(),
            net_salary: $('#editNetSalary').val(),
            action: 'update'
        };
        console.log('Submitting data with half-day:', data.half_day_days);
        $.ajax({
            url: 'include/api/payroll.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    $('#editPayrollModal').modal('hide');
                    showPayrollToast('Payroll updated successfully!', true);
                    // Refresh table data instead of page reload
                    loadPayroll();
                } else {
                    showPayrollToast(response.message || 'Update failed!', false);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr.responseText);
                console.log('Status:', status);
                console.log('Error:', error);
                showPayrollToast('Server error!', false);
            }
        });
    });

    // Toast function
    function showPayrollToast(msg, success) {
        var toastEl = document.getElementById('payrollToast');
        var toastMsg = document.getElementById('payrollToastMsg');
        toastMsg.textContent = msg;
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    window.loadPayroll = loadPayroll;
});

// Number to words function (simple, for PKR)
function numberToWords(num) {
    var a = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
    ];
    var b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    if ((num = num.toString()).length > 9) return 'Overflow';
    var n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return '';
    var str = '';
    str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' Crore ' : '';
    str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' Lac ' : '';
    str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' Thousand ' : '';
    str += (n[4] != 0) ? (a[Number(n[4])] + ' Hundred ') : '';
    str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + ' ' : '';
    return str.trim() + ' Rupees Only.';
}

// View Payslip Modal (admin side) - user panel ki tarah
window.viewPayslip = function(button) {
    const rowData = $(button).data('row');
    if (!rowData) return;

    // Earnings
    $('#basicSalary').text(rowData.basic_salary || '0');
    $('#fuelAllowance').text(rowData.fuel_allowance || '0');
    $('#houseRentAllowance').text(rowData.house_rent_allowance || '0');
    $('#utilityAllowance').text(rowData.utility_allowance || '0');
    $('#mobileAllowance').text(rowData.mobile_allowance || '0');

    $('#totalEarnings').text(rowData.total_earnings || '0');
    $('#grossEarnings').text(rowData.total_earnings || '0');

    // Deductions
    $('#providentFund').text(rowData.provident_fund || '0');
    $('#professionalTax').text(rowData.professional_tax || '0');
    $('#loan').text(rowData.loan || '0');
    $('#totalLeaves').text(rowData.leave_days || '0');
    $('#totalLate').text(rowData.late_days || '0');
    $('#totalHalfday').text(rowData.half_day_days || '0');
    $('#totalDeductions').text(rowData.total_deductions || '0');
    $('#totalDeduction').text(rowData.total_deductions || '0');

    // Net Salary
    $('#netSalary').text(rowData.net_salary || '0');

    // Employee info
    const fullName = (rowData.first_name || '') + ' ' + (rowData.middle_name || '') + ' ' + (rowData.last_name || '');
    $('#empName').text(fullName.trim() || '');
    $('#empDesignation').text(rowData.position || '');
    $('#empDepartment').text(rowData.department || '');
    $('#joiningDate').text(rowData.joining_date || '');
    $('#empBank').text(rowData.bank || '');
    $('#payPeriod').text(rowData.payment_date || '');
    $('#workedDays').text(rowData.worked_days || '');

    // Net Salary in numbers and words
    var netSalary = rowData.net_salary || '0';
    var netSalaryNum = parseInt(netSalary.replace(/,/g, '')) || 0;
    $(".amount-in-words h6").text('Amount: Rs. ' + netSalary);
    $(".amount-in-words p").text(numberToWords(netSalaryNum));
};

function printPayslip() {
    var payslipContent = document.getElementById('payslipContent').innerHTML;
    var printWindow = window.open('', '', 'width=900,height=650');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Payslip</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
            <link href="../assets/css/style.css" rel="stylesheet">
            <style>
                @media print {
                    @page { margin: 0; size: A4; }
                    body { margin: 2cm; }
                    .btn, .modal-header, .modal-footer { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class="container-fluid">
                ${payslipContent}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(function() {
        printWindow.focus();
        printWindow.print();
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    }, 500);
}

// user side se copy karein



var payrollTableInstance;

$(document).ready(function() {
    loadPayroll(); // Payroll table load karein with custom filters
});

function loadPayrollData() {
    $.ajax({
        url: 'include/api/payroll.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Sort data by payment_date in descending order
            // data.sort(function(a, b) {
            //     var dateA = new Date(a.payment_date || '1900-01-01');
            //     var dateB = new Date(b.payment_date || '1900-01-01');
            //     return dateB - dateA;
            // });

            // Check if DataTable exists and get current state
            var currentPage = 0;
            if ($.fn.DataTable.isDataTable('#payrollTable')) {
                var currentTable = $('#payrollTable').DataTable();
                currentPage = currentTable.page();
                currentTable.destroy();
            }

            // Clear table body
            var tableBody = $('#payrollTable tbody');
            tableBody.empty();

            // Group data by month and year
            var groupedData = {};
            var monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            data.data.forEach(function(row) {
                // Format the date
                var formattedDate = '-';
                var monthKey = '';
                if (row.payment_date) {
                    var date = new Date(row.payment_date);
                    formattedDate = ('0' + date.getDate()).slice(-2) + '/' +
                        ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                        date.getFullYear();

                    // Create month key for grouping
                    var month = date.getMonth() + 1;
                    var year = date.getFullYear();
                    monthKey = year + '-' + ('0' + month).slice(-2);
                } else {
                    monthKey = 'No-Date';
                }

                // Add formatted date to row
                row.formatted_date = formattedDate;

                // Group by month
                if (!groupedData[monthKey]) {
                    groupedData[monthKey] = [];
                }
                groupedData[monthKey].push(row);
            });

            // Sort months in descending order (newest first - December, November, October...)
            var sortedMonths = Object.keys(groupedData).sort(function(a, b) {
                if (a === 'No-Date') return 1;
                if (b === 'No-Date') return -1;
                return b.localeCompare(a); // This will sort 2025-12, 2025-11, 2025-10...
            });

            // Add grouped data to table (without headers)
            sortedMonths.forEach(function(monthKey) {
                var monthData = groupedData[monthKey];

                // Add month data rows directly (no headers)
                monthData.forEach(function(row) {
                    var rowHtml = '<tr>';
                    rowHtml += '<td>' + (row.emp_id || '-') + '</td>';
                    rowHtml += '<td>' + (row.name || '-') + '</td>';
                    rowHtml += '<td>' + (row.position || '-') + '</td>';
                    rowHtml += '<td>' + (row.basic_salary || '-') + '</td>';
                    rowHtml += '<td>' + (row.leave_days !== undefined && row.leave_days !== null ? row.leave_days : '-') + '</td>';
                    rowHtml += '<td>' + (row.late_days !== undefined && row.late_days !== null ? row.late_days : '-') + '</td>';
                    rowHtml += '<td>' + (row.half_day_days !== undefined && row.half_day_days !== null ? row.half_day_days : '-') + '</td>';
                    rowHtml += '<td>' + (row.total_earnings || '-') + '</td>';
                    rowHtml += '<td>' + (row.total_deductions || '-') + '</td>';
                    rowHtml += '<td>' + (row.net_salary || '-') + '</td>';
                    rowHtml += '<td>' + (row.bank || '-') + '</td>';
                    rowHtml += '<td>' + row.formatted_date + '</td>';
                    rowHtml += '<td><button type="button" class="btn btn-outline-primary btn-sm payslip-view-btn" style="border-radius:8px;display:flex;align-items:center;justify-content:center;padding:8px 8px;" onclick="viewPayslip(this)" data-bs-toggle="modal" data-bs-target="#viewPayslipModal" data-row=\'' + JSON.stringify(row) + '\'><i class="fas fa-eye"></i></button></td>';
                    rowHtml += '<td><button class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal" data-bs-target="#editPayrollModal" data-row=\'' + JSON.stringify(row) + '\'><i class="fas fa-edit"></i></button></td>';
                    rowHtml += '</tr>';

                    tableBody.append(rowHtml);
                });
            });

            // Add custom filter for month and year (same as loadPayroll function)
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'payrollTable') return true;

                var monthFilter = $('#selectedMonth').val();
                var yearFilter = $('#selectedYear').val();
                var rowDate = data[11]; // Payment date column

                if (!monthFilter && !yearFilter) return true;
                if (!rowDate || rowDate === 'N/A' || rowDate === '-') return false;

                // Convert DD/MM/YYYY to extract month and year
                var parts = rowDate.split('/');
                if (parts.length === 3) {
                    var day = parts[0];
                    var month = parts[1];
                    var year = parts[2];

                    var monthMatch = !monthFilter || month === monthFilter;
                    var yearMatch = !yearFilter || year === yearFilter;

                    return monthMatch && yearMatch;
                }

                return false;
            });

            // Initialize DataTable
            payrollTableInstance = $('#payrollTable').DataTable({
                dom: '<"top"l>rt<"bottom"ip>',
                paging: true,
                searching: false,
                ordering: false, // Disable DataTable ordering to prevent conflict with custom grouping
                info: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                lengthChange: true,
                // Remove order since we're using custom grouping
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fas fa-download me-1"></i> Export',
                    className: 'btn btn-light text-secondary',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-2 text-success"></i>Export to Excel',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                            }
                        },
                        {
                            extend: 'csv',
                            text: '<i class="fas fa-file-csv me-2 text-primary"></i>Export to CSV',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                            }
                        }
                    ]
                }],
                order: [
                    [12, 'desc'],
                    [0, 'asc']
                ],
                stateSave: true, // Save pagination state
                language: {
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            // Move export buttons to container
            payrollTableInstance.buttons().container().appendTo('#exportButtonContainer');

            // Month group toggle functionality removed since no headers

            // Restore to previous page if it exists
            if (currentPage > 0) {
                payrollTableInstance.page(currentPage).draw('page');
            }
        },
        // error: function (xhr, status, error) {
        //     console.error("Data load nahi ho saka: " + error);
        // }
    });
}

function viewPayslip(btn) {
    var row = $(btn).data('row');
    if (!row) return;

    // Clear previous values first
    $('#totalHalfday').empty();

    // Employee Info
    const fullName = (row.first_name || '') + ' ' + (row.middle_name || '') + ' ' + (row.last_name || '');
    $('#empName').text(fullName.trim() || row.emp_id || '');
    $('#empDesignation').text(row.designation || row.position || '');
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
    // Force set half-day count
    var halfDayValue = row.half_day_days !== undefined ? row.half_day_days : '0';
    console.log('Setting half-day to:', halfDayValue);
    console.log('Row data:', row);

    // Try multiple methods
    $('#totalHalfday').text(halfDayValue);
    $('#totalHalfday').html(halfDayValue);

    // Also try direct DOM manipulation
    var halfDayElement = document.getElementById('totalHalfday');
    if (halfDayElement) {
        halfDayElement.textContent = halfDayValue;
        console.log('Half-day element found and updated');
    } else {
        console.log('Half-day element not found');
    }
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