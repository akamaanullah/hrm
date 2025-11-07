// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

$(document).ready(function() {
    // Global array to store selected employee IDs
    var selectedEmployees = [];
    // Global variables for month/year selection
    var selectedPayslipMonth = '';
    var selectedPayslipYear = new Date().getFullYear();
    
    // Modal open hote hi active employees load karo aur departments bhi
    $('#autoPayslipModal').on('show.bs.modal', function() {
        loadDepartments();
        loadActiveEmployees();
        setCurrentMonthYear(); // Current month and year set karo
    });

    // Current month and year set karne ka function
    function setCurrentMonthYear() {
        var currentDate = new Date();
        var currentMonth = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // 01, 02, etc.
        var currentYear = currentDate.getFullYear();
        
        selectedPayslipMonth = currentMonth;
        selectedPayslipYear = currentYear;
        
        // Display current selection
        var monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $('#payslipMonthYearPicker').val(monthNames[currentMonth - 1] + ' ' + currentYear);
        $('#selectedPayslipMonth').val(currentMonth);
        $('#selectedPayslipYear').val(currentYear);
    }

    // Payslip Month Year Picker Button Click
    $('#payslipMonthYearBtn').on('click', function() {
        // Set current year in modal
        $('#payslipCurrentYear').text(selectedPayslipYear);
        // Highlight current month if any
        $('.payslip-month-btn').removeClass('btn-primary text-white').addClass('btn-light');
        if (selectedPayslipMonth) {
            $('.payslip-month-btn[data-month="' + selectedPayslipMonth + '"]')
                .removeClass('btn-light').addClass('btn-primary text-white');
        }
        $('#payslipMonthYearPickerModal').modal('show');
    });

    // Year navigation for payslip picker
    $('#payslipYearPrevBtn').on('click', function() {
        selectedPayslipYear--;
        $('#payslipCurrentYear').text(selectedPayslipYear);
        $('.payslip-month-btn').removeClass('btn-primary text-white').addClass('btn-light');
    });

    $('#payslipYearNextBtn').on('click', function() {
        selectedPayslipYear++;
        $('#payslipCurrentYear').text(selectedPayslipYear);
        $('.payslip-month-btn').removeClass('btn-primary text-white').addClass('btn-light');
    });

    // Allow typing year directly (optional feature)
    $('#payslipCurrentYear').on('click', function() {
        var newYear = prompt('Enter year:', selectedPayslipYear);
        if (newYear && !isNaN(newYear) && newYear >= 2020 && newYear <= 2030) {
            selectedPayslipYear = parseInt(newYear);
            $('#payslipCurrentYear').text(selectedPayslipYear);
            $('.payslip-month-btn').removeClass('btn-primary text-white').addClass('btn-light');
        }
    });

    // Month selection for payslip picker
    $(document).on('click', '.payslip-month-btn', function() {
        var month = $(this).data('month');
        selectedPayslipMonth = month;
        
        // Update UI
        $('.payslip-month-btn').removeClass('btn-primary text-white').addClass('btn-light');
        $(this).removeClass('btn-light').addClass('btn-primary text-white');
    });

    // Apply payslip month year selection
    $('#applyPayslipMonthYear').on('click', function() {
        if (!selectedPayslipMonth) {
            showPayrollToast('Please select a month!', 'danger');
            return;
        }
        
        var monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        var monthName = monthNames[parseInt(selectedPayslipMonth) - 1];
        
        $('#payslipMonthYearPicker').val(monthName + ' ' + selectedPayslipYear);
        $('#selectedPayslipMonth').val(selectedPayslipMonth);
        $('#selectedPayslipYear').val(selectedPayslipYear);
        
        $('#payslipMonthYearPickerModal').modal('hide');
    });

    // Checkbox select/unselect logic
    $(document).on('change', '#autoPayslipEmployeeTable tbody input[type="checkbox"]', function() {
        var empId = $(this).val();
        if ($(this).is(':checked')) {
            if (!selectedEmployees.includes(empId)) {
                selectedEmployees.push(empId);
            }
        } else {
            selectedEmployees = selectedEmployees.filter(function(id) { return id !== empId; });
        }
        // Uncheck Select All if any unchecked
        if (!$(this).is(':checked')) {
            $('#selectAllEmployees').prop('checked', false);
        }
    });

    // Select All logic (ab sirf filtered/visible rows pe chalega)
    $(document).on('change', '#selectAllEmployees', function() {
        var checked = $(this).is(':checked');
        $('#autoPayslipEmployeeTable tbody tr:visible input[type="checkbox"]').each(function() {
            $(this).prop('checked', checked);
            var empId = $(this).val();
            if (checked) {
                if (!selectedEmployees.includes(empId)) {
                    selectedEmployees.push(empId);
                }
            } else {
                selectedEmployees = selectedEmployees.filter(function(id) { return id !== empId; });
            }
        });
    });

    // Active employees load karne ka function
    function loadActiveEmployees() {
        $.ajax({
            url: 'include/api/employee.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tbody = '';
                if (Array.isArray(data)) {
                    data.forEach(function(emp) {
                        var checked = selectedEmployees.includes(emp.emp_id.toString()) ? 'checked' : '';
                        tbody += `<tr>
                            <td><input type="checkbox" class="emp-checkbox" value="${emp.emp_id}" ${checked}></td>
                            <td>${emp.emp_id}</td>
                            <td>${createFullName(emp.first_name, emp.middle_name, emp.last_name)}</td>
                            <td>${emp.position || ''}</td>
                            <td>${emp.department || ''}</td>
                            <td>${emp.salary || ''}</td>
                        </tr>`;
                    });
                }
                $('#autoPayslipEmployeeTable tbody').html(tbody);
                $('#selectAllEmployees').prop('checked', false);
            },
            error: function() {
                $('#autoPayslipEmployeeTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading employees</td></tr>');
            }
        });
    }

    // Department list load karo
    function loadDepartments() {
        $.ajax({
            url: 'include/api/department.php',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && Array.isArray(res.data)) {
                    var options = '<option value="">All Departments</option>';
                    res.data.forEach(function(dept) {
                        options += `<option value="${dept.dept_name}">${dept.dept_name}</option>`;
                    });
                    $('#filterDepartment').html(options);
                }
            }
        });
    }

    // Filter logic
    $(document).on('input change', '#filterEmpId, #filterEmpName, #filterDepartment', function() {
        filterEmployeeTable();
    });

    function filterEmployeeTable() {
        var empId = $('#filterEmpId').val().toLowerCase();
        var empName = $('#filterEmpName').val().toLowerCase();
        var dept = $('#filterDepartment').val().toLowerCase();
        $('#autoPayslipEmployeeTable tbody tr').each(function() {
            var row = $(this);
            var rowEmpId = row.find('td:eq(1)').text().toLowerCase();
            var rowName = row.find('td:eq(2)').text().toLowerCase();
            var rowDept = row.find('td:eq(4)').text().toLowerCase();
            var show = true;
            if (empId && !rowEmpId.includes(empId)) show = false;
            if (empName && !rowName.includes(empName)) show = false;
            if (dept && rowDept !== dept) show = false;
            row.toggle(show);
        });
    }

    // Generate Payslip button click (ab selectedEmployees array se IDs jayengi)
    $('#generatePayslipBtn').on('click', function() {
        if (selectedEmployees.length === 0) {
            showPayrollToast('Please select at least one employee!', 'danger');
            return;
        }
        
        // Month and Year select karo
        var selectedMonth = $('#selectedPayslipMonth').val();
        var selectedYear = $('#selectedPayslipYear').val();
        
        if (!selectedMonth) {
            showPayrollToast('Please select payslip month!', 'danger');
            return;
        }
        
        if (!selectedYear) {
            showPayrollToast('Please select payslip year!', 'danger');
            return;
        }
        
        // Backend call for payslip generation
        console.log('Sending data:', { 
            emp_ids: selectedEmployees, 
            month: selectedMonth, 
            year: selectedYear 
        });
        
        $.ajax({
            url: 'include/api/auto-payslip.php',
            type: 'POST',
            data: JSON.stringify({ 
                emp_ids: selectedEmployees, 
                month: selectedMonth, 
                year: selectedYear 
            }),
            contentType: 'application/json',
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    showPayrollToast('Payslips generated successfully!', 'success');
                    $('#autoPayslipModal').modal('hide');
                    selectedEmployees = []; // Reset after success
                    if (window.loadPayroll) window.loadPayroll(); // Real-time table update
                } else {
                    showPayrollToast(response.message || 'Payslip generation failed!', 'danger');
                    if (response.errors && response.errors.length > 0) {
                        console.error('Errors:', response.errors);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
                showPayrollToast('Server error! Payslip generation failed.', 'danger');
            }
        });
    });
}); 

// Toast function for payroll
function showPayrollToast(msg, type) {
    var toastEl = document.getElementById('payrollToast');
    var toastMsg = document.getElementById('payrollToastMsg');
    toastMsg.textContent = msg;
    toastEl.classList.remove('text-bg-success', 'text-bg-danger');
    toastEl.classList.add(type === 'danger' ? 'text-bg-danger' : 'text-bg-success');
    var toast = new bootstrap.Toast(toastEl, { delay: 2000 });
    toast.show();
} 