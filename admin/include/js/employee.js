window.addEmployeeFormListenerAttached = window.addEmployeeFormListenerAttached || false;
window.currentEditEmpId = window.currentEditEmpId || null;

// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

document.addEventListener('DOMContentLoaded', function() {
    // Remove modal backdrop when modal is closed
    const modals = ['addEmployeeModal', 'editEmployeeModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });
        }
    });

    // Helper function to reload employee table
    function loadEmployees(showDeleted = false) {
        let url = 'include/api/employee.php';
        if (showDeleted) {
            url += '?deleted=1';
        }
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const tbody = document.querySelector('#employeeTable tbody');
                if (!tbody) return;
                tbody.innerHTML = '';

                // DataTable destroy karo agar pehle se initialized hai
                if ($.fn.DataTable.isDataTable('#employeeTable')) {
                    $('#employeeTable').DataTable().clear().destroy();
                }

                // Check if data is an array
                if (!Array.isArray(data)) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading employees. Please check database connection.</td></tr>';
                    return;
                }

                // Helper function to format time in 12-hour format with am/pm
                function formatTime12(timeStr) {
                    if (!timeStr) return '';
                    let [hours, minutes] = timeStr.split(':');
                    hours = parseInt(hours);
                    minutes = parseInt(minutes);
                    if (isNaN(hours) || isNaN(minutes)) return '';
                    let ampm = hours >= 12 ? 'pm' : 'am';
                    hours = hours % 12;
                    hours = hours ? hours : 12;
                    minutes = minutes < 10 ? '0' + minutes : minutes;
                    return `${hours}:${minutes} ${ampm}`;
                }

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No employees found.</td></tr>';
                    return;
                }

                data.forEach(emp => {
                    const tr = document.createElement('tr');
                    let shiftInfo = '-';
                    if (emp.shift_name && emp.shift_start_time && emp.shift_end_time) {
                        const start = formatTime12(emp.shift_start_time);
                        const end = formatTime12(emp.shift_end_time);
                        shiftInfo = `${emp.shift_name} (${start} - ${end})`;
                    }

                    // Check if this is a deleted employee
                    const isDeleted = showDeleted;

                    let actionButtons = '';
                    if (isDeleted) {
                        // For deleted employees, show restore button
                        actionButtons = `
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal" data-emp-id="${emp.emp_id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary restore-btn-employee" data-emp-id="${emp.emp_id}">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        // For active employees, show edit and delete buttons
                        actionButtons = `
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal" data-emp-id="${emp.emp_id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" data-emp-id="${emp.emp_id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn-employee" data-emp-id="${emp.emp_id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }

                    tr.innerHTML = `
                        <td>${emp.emp_id || ''}</td>
                        <td>${createFullName(emp.first_name, emp.middle_name, emp.last_name) || ''}</td>
                        <td>${emp.email || ''}</td>
                        <td>${emp.address ? emp.address.split(' ').slice(0, 3).join(' ') + (emp.address.split(' ').length > 3 ? '...' : '') : ''}</td>
                        <td>${emp.phone || ''}</td>
                        <td>${emp.position || ''}</td>
                        <td>${emp.department || ''}</td>
                        <td>${shiftInfo}</td>
                        <td>
                            ${actionButtons}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Ab DataTable initialize karein
                $('#employeeTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    order: [
                        [0, 'desc']
                    ],
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
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                                }
                            },
                            {
                                extend: 'csv',
                                text: '<i class="fas fa-file-csv me-2 text-primary"></i>Export to CSV',
                                className: 'dropdown-item',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                                }
                            }
                        ]
                    }],
                    language: {
                        lengthMenu: "Show _MENU_ entries",
                        search: "Search:",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });

                // Move export buttons to custom container
                $('#employeeTable').DataTable().buttons().container().appendTo('#exportButtonContainer');

                // Function to apply all filters at once
                function applyFilters() {
                    const table = $('#employeeTable').DataTable();
                    const employeeId = $('#employeeIdFilter').val();
                    const name = $('#nameFilter').val();
                    const department = $('#departmentFilter').val();

                    table.column(0).search(employeeId)
                        .column(1).search(name)
                        .column(6).search(department)
                        .draw();
                }

                // Bind events for real-time filtering
                $('#employeeIdFilter, #nameFilter').on('input', applyFilters);
                $('#departmentFilter').on('change', applyFilters);

                // Handle form reset to clear filters
                $('#employeeFilterForm').on('reset', function() {
                    // Timeout to allow form reset before applying filters
                    setTimeout(function() {
                        applyFilters();
                    }, 0);
                });

                // Remove old event handlers
                $('#employeeFilterForm').off('submit');
            })
            .catch(error => {
                const tbody = document.querySelector('#employeeTable tbody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading employees. Please check database connection and try again later.</td></tr>';
                }
            });
    }


    // Delete button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn-employee')) {
            const button = e.target.closest('.delete-btn-employee');
            const empId = button.getAttribute('data-emp-id');

            if (!empId) {
                return;
            }

            // SweetAlert2 confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this employee!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('include/api/employee.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'soft_delete',
                                emp_id: empId
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                button.closest('tr').remove();
                                showEmployeeToast('Employee deleted successfully!', 'delete');
                            } else {
                                showEmployeeToast('Error: ' + (res.message || 'Unknown error'), 'delete');
                            }
                        })
                        .catch(err => {
                            showEmployeeToast('Error: ' + err, 'delete');
                        });
                }
            });
        }
    });

    // Restore button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.restore-btn-employee')) {
            const button = e.target.closest('.restore-btn-employee');
            const empId = button.getAttribute('data-emp-id');

            if (!empId) {
                return;
            }

            // SweetAlert2 confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to restore this employee!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('include/api/employee.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'restore',
                                emp_id: empId
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                button.closest('tr').remove();
                                showEmployeeToast('Employee restored successfully!', 'restore');
                            } else {
                                showEmployeeToast('Error: ' + (res.message || 'Unknown error'), 'restore');
                            }
                        })
                        .catch(err => {
                            showEmployeeToast('Error: ' + err, 'restore');
                        });
                }
            });
        }
    });

    // Button click events for Deleted/Active Employees
    const showDeletedBtn = document.getElementById('showDeletedEmployeesBtn');
    const showActiveBtn = document.getElementById('showActiveEmployeesBtn');
    if (showDeletedBtn && showActiveBtn) {
        showDeletedBtn.addEventListener('click', function() {
            loadEmployees(true);
            showDeletedBtn.classList.add('d-none');
            showActiveBtn.classList.remove('d-none');
        });
        showActiveBtn.addEventListener('click', function() {
            loadEmployees(false);
            showActiveBtn.classList.add('d-none');
            showDeletedBtn.classList.remove('d-none');
        });
    }

    // Initial load
    loadEmployees(false);

    // Add Employee Form Submission
    const addEmployeeForm = document.getElementById('addEmployeeForm');

    function handleAddEmployeeSubmit(e) {
        e.preventDefault();

        // Validation function
        function validateField(fieldId, fieldName) {
            const field = document.getElementById(fieldId);
            const value = field.value.trim();
            if (!value) {
                showEmployeeToast(`${fieldName} field is required!`, 'error');
                field.focus();
                return false;
            }
            return true;
        }

        // Validation function for file fields
        function validateFileField(fieldId, fieldName) {
            const field = document.getElementById(fieldId);
            if (!field.files || field.files.length === 0) {
                showEmployeeToast(`${fieldName} is required!`, 'error');
                field.focus();
                return false;
            }
            return true;
        }

        // Validate all required fields
        const requiredFields = [{
                id: 'newEmployeeFirstName',
                name: 'First Name'
            },
            {
                id: 'newEmployeeLastName',
                name: 'Last Name'
            },
            {
                id: 'newEmployeeGender',
                name: 'Gender'
            },
            {
                id: 'newEmployeeDOB',
                name: 'Date of Birth'
            },
            {
                id: 'newEmployeePhone',
                name: 'Phone'
            },
            {
                id: 'newEmployeeEmail',
                name: 'Email'
            },
            {
                id: 'newEmployeeAddress',
                name: 'Address'
            },
            {
                id: 'newEmployeePassword',
                name: 'Password'
            },
            {
                id: 'newEmployeeCNIC',
                name: 'CNIC'
            },
            {
                id: 'newEmployeeEmergencyContact',
                name: 'Emergency Contact'
            },
            {
                id: 'newEmployeeEmergencyRelation',
                name: 'Emergency Relation'
            },
            {
                id: 'newEmployeePosition',
                name: 'Position'
            },
            // {
            //     id: 'newEmployeeLineManager',
            //     name: 'Reporting'
            // },
            {
                id: 'newEmployeeDepartment',
                name: 'Department'
            },
            {
                id: 'newEmployeeSubDepartment',
                name: 'Sub Department'
            },
            {
                id: 'newEmployeeBankName',
                name: 'Bank Name'
            },
            {
                id: 'newEmployeeAccountType',
                name: 'Account Type'
            },
            {
                id: 'newEmployeeAccountTitle',
                name: 'Account Title'
            },
            {
                id: 'newEmployeeAccountNumber',
                name: 'Account Number'
            },
            {
                id: 'newEmployeeBankBranch',
                name: 'Bank Branch'
            },
            {
                id: 'newEmployeeSalary',
                name: 'Salary'
            },
            {
                id: 'newEmployeeJoiningDate',
                name: 'Joining Date'
            },
            {
                id: 'newEmployeeQualificationInstitution',
                name: 'Qualification'
            },
            // {
            //     id: 'newEmployeeEducationPercentage',
            //     name: 'Education Percentage'
            // },
            {
                id: 'newEmployeeSpecialization',
                name: 'Professional Expertise'
            },
            {
                id: 'newEmployeeMaritalStatus',
                name: 'Institution'
            },
            {
                id: 'newEmployeeLastOrganization',
                name: 'Last Employer'
            },
            {
                id: 'newEmployeeLastDesignation',
                name: 'Last Designation'
            },
            {
                id: 'newEmployeeShiftTiming',
                name: 'Shift Timing'
            }
        ];

        // Check each required field
        for (let field of requiredFields) {
            if (!validateField(field.id, field.name)) {
                return; // Stop if any field is invalid
            }
        }

        // Validate ID Card Attachment (required)
        if (!validateFileField('newEmployeeIDCardAttachment', 'ID Card Attachment')) {
            return; // Stop if ID card is not provided
        }
        
        // Email validation before submit
        const email = document.getElementById('newEmployeeEmail').value.trim();
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (!email || !emailRegex.test(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.'
            });
            return;
        }
        
        // Check if email is already registered
        const emailFeedbackText = $('#addEmailFeedback').text();
        if (emailFeedbackText === 'This email is already registered') {
            Swal.fire({
                icon: 'error',
                title: 'Duplicate Email',
                text: 'This email is already registered. Please use a different email.'
            });
            return;
        }

        const submitBtn = addEmployeeForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        // Get form values
        const first_name = document.getElementById('newEmployeeFirstName').value;
        const middle_name = document.getElementById('newEmployeeMiddleName').value;
        const last_name = document.getElementById('newEmployeeLastName').value;
        const gender = document.getElementById('newEmployeeGender').value;
        const date_of_birth = document.getElementById('newEmployeeDOB').value;
        const phone = document.getElementById('newEmployeePhone').value;
        const address = document.getElementById('newEmployeeAddress').value;
        const password = document.getElementById('newEmployeePassword').value;
        const cnic = document.getElementById('newEmployeeCNIC').value;
        const emergency_contact = document.getElementById('newEmployeeEmergencyContact').value;
        const emergency_relation = document.getElementById('newEmployeeEmergencyRelation').value;
        const position = document.getElementById('newEmployeePosition').value;
        const lineManagerElement = document.getElementById('newEmployeeLineManager');
        const line_manager = lineManagerElement ? lineManagerElement.value : '';
        const department_id = document.getElementById('newEmployeeDepartment').value;
        const sub_department = document.getElementById('newEmployeeSubDepartment').value;
        const bank_name = document.getElementById('newEmployeeBankName').value;
        const account_title = document.getElementById('newEmployeeAccountTitle').value;
        const account_number = document.getElementById('newEmployeeAccountNumber').value;
        const bank_branch = document.getElementById('newEmployeeBankBranch').value;
        const salary = document.getElementById('newEmployeeSalary').value;
        const joining_date = document.getElementById('newEmployeeJoiningDate').value;
        const qualification_institution = document.getElementById('newEmployeeQualificationInstitution').value;
        const education_percentage = document.getElementById('newEmployeeEducationPercentage').value;
        const specialization = document.getElementById('newEmployeeSpecialization').value;
        const marital_status = document.getElementById('newEmployeeMaritalStatus').value;
        const last_organization = document.getElementById('newEmployeeLastOrganization').value;
        const last_designation = document.getElementById('newEmployeeLastDesignation').value;
        const experience_from_date = document.getElementById('newEmployeeExperienceFromDate').value;
        const experience_to_date = document.getElementById('newEmployeeExperienceToDate').value;
        const job_type = document.getElementById('newEmployeeJobType').value;
        const shift_id = document.getElementById('newEmployeeShiftTiming').value;
        const account_type = document.getElementById('newEmployeeAccountType').value;

        // Handle file uploads
        const formData = new FormData();

        // Add all form data
        formData.append('first_name', first_name);
        formData.append('middle_name', middle_name);
        formData.append('last_name', last_name);
        formData.append('gender', gender);
        formData.append('date_of_birth', date_of_birth);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('address', address);
        formData.append('cnic', cnic);
        formData.append('emergency_contact', emergency_contact);
        formData.append('emergency_relation', emergency_relation);
        formData.append('position', position);
        formData.append('line_manager', line_manager);
        formData.append('department_id', department_id);
        formData.append('sub_department', sub_department);
        formData.append('bank_name', bank_name);
        formData.append('account_title', account_title);
        formData.append('account_number', account_number);
        formData.append('account_type', account_type);
        formData.append('bank_branch', bank_branch);
        formData.append('salary', salary);
        formData.append('joining_date', joining_date);
        formData.append('qualification_institution', qualification_institution);
        formData.append('education_percentage', education_percentage);
        formData.append('specialization', specialization);
        formData.append('marital_status', marital_status);
        formData.append('last_organization', last_organization);
        formData.append('last_designation', last_designation);
        formData.append('experience_from_date', experience_from_date);
        formData.append('experience_to_date', experience_to_date);
        formData.append('job_type', job_type);
        formData.append('shift_id', shift_id);

        // Add file uploads
        const cvFile = document.getElementById('newEmployeeCVAttachment').files[0];
        const idCardFile = document.getElementById('newEmployeeIDCardAttachment').files[0];
        const otherFiles = document.getElementById('newEmployeeOtherDocuments').files;

        if (cvFile) {
            formData.append('cv_attachment', cvFile);
        }

        if (idCardFile) {
            formData.append('id_card_attachment', idCardFile);
        }

        for (let i = 0; i < otherFiles.length; i++) {
            formData.append('other_documents[]', otherFiles[i]);
        }

        fetch('include/api/employee.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(res => {
                if (res.success) {
                    showEmployeeToast('Employee added successfully!', 'add');
                    addEmployeeForm.reset();
                    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addEmployeeModal'));
                    modal.hide();
                    loadEmployees();
                } else {
                    showEmployeeToast('Failed to add employee: ' + (res.message || 'Unknown error'), 'add');
                }
            })
            .catch(err => {
                showEmployeeToast('Error: ' + err, 'add');
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
            });
    }
    if (addEmployeeForm && !window.addEmployeeFormListenerAttached) {
        addEmployeeForm.addEventListener('submit', handleAddEmployeeSubmit);
        window.addEmployeeFormListenerAttached = true;
    }

    // View button click: modal me data set karo
    $(document).on('click', '[data-bs-target="#viewEmployeeModal"]', function() {
        var empId = $(this).data('emp-id');

        // Check if we're currently showing deleted employees
        var showDeleted = $('#showDeletedEmployeesBtn').hasClass('d-none');
        var apiUrl = 'include/api/employee.php';
        if (showDeleted) {
            apiUrl += '?deleted=1';
        }

        // Get employee data from API
        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(response) {
                var emp = response.find(e => e.emp_id == empId);
                if (emp) {
                    $('#viewEmployeeFullName').text(createFullName(emp.first_name, emp.middle_name, emp.last_name) || '');
                    $('#viewEmployeeMiddleName').text(emp.middle_name || '');
                    $('#viewEmployeeGender').text(emp.gender || '');
                    $('#viewEmployeeEmail').text(emp.email || '');
                    $('#viewEmployeeDOB').text(formatDate(emp.date_of_birth) || '');
                    $('#viewEmployeePhone').text(emp.phone || '');
                    $('#viewEmployeeAddress').text(emp.address || '');
                    $('#viewEmployeePosition').text(emp.position || '');
                    $('#viewEmployeeLineManager').text(emp.line_manager || '');
                    $('#viewEmployeeDepartment').text(emp.department || '');
                    $('#viewEmployeeSubDepartment').text(emp.sub_department || '');
                    $('#viewEmployeeBankName').text(emp.bank_name || '');
                    $('#viewEmployeeAccountType').text(emp.account_type || '');
                    $('#viewEmployeeAccountTitle').text(emp.account_title || '');
                    $('#viewEmployeeAccountNumber').text(emp.account_number || '');
                    $('#viewEmployeeBankBranch').text(emp.bank_branch || '');
                    $('#viewEmployeeEducationPercentage').text(emp.education_percentage || '');
                    $('#viewEmployeeSpecialization').text(emp.specialization || '');
                    $('#viewEmployeeLastOrganization').text(emp.last_organization || '');
                    $('#viewEmployeeLastDesignation').text(emp.last_designation || '');
                    $('#viewEmployeeExperienceFromDate').text(formatDate(emp.experience_from_date) || '');
                    $('#viewEmployeeExperienceToDate').text(formatDate(emp.experience_to_date) || '');
                    $('#viewEmployeeJoiningDate').text(formatDate(emp.joining_date) || '');
                    $('#viewEmployeeCreatedAt').text(formatDate(emp.created_at) || '');
                    $('#viewEmployeeCNIC').text(emp.cnic || '');
                    $('#viewEmployeeEmergencyContact').text(emp.emergency_contact || '');
                    $('#viewEmployeeEmergencyRelation').text(emp.emergency_relation || '');
                    $('#viewEmployeeSalary').text(emp.salary || '');
                    $('#viewEmployeeJobType').text(emp.job_type || '');
                    // Timing show karo
                    let timingText = '';
                    if (emp.shift_name && emp.shift_start_time && emp.shift_end_time) {
                        const start = formatTime12(emp.shift_start_time);
                        const end = formatTime12(emp.shift_end_time);
                        timingText = `${emp.shift_name} (${start} - ${end})`;
                    }
                    $('#viewEmployeeTiming').text(timingText);
                    $('#viewEmployeeStatus').text(emp.status || '');
                    $('#viewEmployeeQualificationInstitution').text(emp.qualification_institution || '');
                    $('#viewEmployeeMaritalStatus').text(emp.marital_status || '');
                    // Profile image set karo
                    if (emp.profile_img) {
                        $('#viewEmployeeProfileImg').attr('src', '../' + emp.profile_img);
                    } else {
                        $('#viewEmployeeProfileImg').attr('src', '../assets/images/default-avatar.jpg');
                    }

                    // Set current employee data for document loading
                    window.currentEmployeeData = emp;

                    // Load leave history for this employee
                    loadEmployeeLeaveHistory(empId);

                    // Load employee documents
                    loadEmployeeDocuments(empId);
                } else {
                }
            },
            error: function(xhr, status, error) {
            }
        });
    });

    // Load departments function
    function loadDepartmentsInDropdown(callback) {
        fetch('include/api/department.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.data)) {
                    const editDeptSelect = document.getElementById('editEmployeeDepartment');
                    const newDeptSelect = document.getElementById('newEmployeeDepartment');

                    if (editDeptSelect) {
                        editDeptSelect.innerHTML = '<option value="">Select Department</option>';
                    }
                    if (newDeptSelect) {
                        newDeptSelect.innerHTML = '<option value="">Select Department</option>';
                    }

                    // Add all active departments to both dropdowns
                    data.data.forEach(dept => {
                        if (dept.status === 'active') {
                            const option = `<option value="${dept.dept_id}">${dept.dept_name}</option>`;
                            if (editDeptSelect) {
                                editDeptSelect.insertAdjacentHTML('beforeend', option);
                            }
                            if (newDeptSelect) {
                                newDeptSelect.insertAdjacentHTML('beforeend', option);
                            }
                        }
                    });
                }
                if (typeof callback === 'function') callback();
            })
            .catch(error => {
            });
    }

    // Page load par department dropdowns ko database se load karo
    loadDepartmentsInDropdown();

    // Edit button click: modal me data set karo
    $(document).on('click', '[data-bs-target="#editEmployeeModal"]', function() {
        var empId = $(this).data('emp-id');

        // Check if we're currently showing deleted employees
        var showDeleted = $('#showDeletedEmployeesBtn').hasClass('d-none');
        var apiUrl = 'include/api/employee.php';
        if (showDeleted) {
            apiUrl += '?deleted=1';
        }

        // Get employee data from API
        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(response) {
                var emp = response.find(e => e.emp_id == empId);
                if (emp) {
                    window.currentEditEmpId = emp.emp_id;
                    window.currentEditOriginalEmail = emp.email || '';
                    $('#editEmployeeId').val(emp.emp_id);
                    $('#editEmployeeFirstName').val(emp.first_name || '');
                    $('#editEmployeeMiddleName').val(emp.middle_name || '');
                    $('#editEmployeeLastName').val(emp.last_name || '');
                    $('#editEmployeeGender').val(emp.gender || '');
                    $('#editEmployeeEmail').val(emp.email || '');
                    $('#editEmployeeDOB').val(emp.date_of_birth || '');
                    $('#editEmployeePhone').val(emp.phone || '');
                    $('#editEmployeeAddress').val(emp.address || '');
                    
                    // Clear password field (password is hashed in database, can't be retrieved)
                    $('#editEmployeePassword').val('');
                    $('#editEmployeePosition').val(emp.position || '');
                    $('#editEmployeeLineManager').val(emp.line_manager || '');
                    $('#editEmployeeSubDepartment').val(emp.sub_department || '');
                    $('#editEmployeeBankName').val(emp.bank_name || '');
                    $('#editEmployeeAccountTitle').val(emp.account_title || '');
                    $('#editEmployeeAccountNumber').val(emp.account_number || '');
                    $('#editEmployeeAccountType').val(emp.account_type);
                    $('#editEmployeeBankBranch').val(emp.bank_branch || '');
                    $('#editEmployeeEducationPercentage').val(emp.education_percentage || '');
                    $('#editEmployeeSpecialization').val(emp.specialization || '');
                    $('#editEmployeeLastOrganization').val(emp.last_organization || '');
                    $('#editEmployeeLastDesignation').val(emp.last_designation || '');
                    $('#editEmployeeExperienceFromDate').val(emp.experience_from_date || '');
                    $('#editEmployeeExperienceToDate').val(emp.experience_to_date || '');
                    $('#editEmployeeJoiningDate').val(emp.joining_date || '');
                    $('#editEmployeeCNIC').val(emp.cnic || '');
                    $('#editEmployeeEmergencyContact').val(emp.emergency_contact || '');
                    $('#editEmployeeEmergencyRelation').val(emp.emergency_relation || '');
                    $('#editEmployeeMaritalStatus').val(emp.marital_status || '');
                    $('#editEmployeeSalary').val(emp.salary || '');
                    $('#editEmployeeStatus').val(emp.status || '');
                    $('#editEmployeeQualificationInstitution').val(emp.qualification_institution || '');
                    // Department & Shift ko dropdown load hone ke baad set karo
                    loadDepartmentsInDropdown(function() {
                        var deptId = emp.department_id ? String(emp.department_id) : '';
                        setTimeout(function() {
                            $('#editEmployeeDepartment').val(deptId);
                        }, 200);
                    });
                    loadShiftsDropdown(function() {
                        $('#editEmployeeJobType').val(emp.job_type || '');
                        $('#editEmployeeShiftTiming').val(emp.shift_id || '');
                    });

                    // Set current employee data for document loading
                    window.currentEmployeeData = emp;

                    // Load existing documents
                    loadEmployeeDocumentsForEdit(emp.emp_id);
                } else {
                }
            },
            error: function(xhr, status, error) {
            }
        });
    });

    // Edit form submit: update request
    const editEmployeeForm = document.getElementById('editEmployeeForm');
    if (editEmployeeForm) {
        editEmployeeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!window.currentEditEmpId) return;
            
            // Email validation before submit
            const email = document.getElementById('editEmployeeEmail').value.trim();
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            if (!email || !emailRegex.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.'
                });
                return;
            }
            
            // Check if email is already registered (and changed from original)
            const emailFeedbackText = $('#editEmailFeedback').text();
            if (emailFeedbackText === 'This email is already registered') {
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Email',
                    text: 'This email is already registered. Please use a different email.'
                });
                return;
            }

            // Handle file uploads with FormData
            const formData = new FormData();

            // Add all form data
            formData.append('action', 'update');
            formData.append('emp_id', window.currentEditEmpId);
            formData.append('first_name', document.getElementById('editEmployeeFirstName').value);
            formData.append('middle_name', document.getElementById('editEmployeeMiddleName').value);
            formData.append('last_name', document.getElementById('editEmployeeLastName').value);
            formData.append('gender', document.getElementById('editEmployeeGender').value);
            formData.append('date_of_birth', document.getElementById('editEmployeeDOB').value);
            formData.append('phone', document.getElementById('editEmployeePhone').value);
            formData.append('email', document.getElementById('editEmployeeEmail').value);
            formData.append('address', document.getElementById('editEmployeeAddress').value);
            formData.append('cnic', document.getElementById('editEmployeeCNIC').value);
            formData.append('emergency_contact', document.getElementById('editEmployeeEmergencyContact').value);
            formData.append('emergency_relation', document.getElementById('editEmployeeEmergencyRelation').value);
            
            // Password field - only if provided
            const passwordField = document.getElementById('editEmployeePassword');
            if (passwordField && passwordField.value.trim() !== '') {
                formData.append('password', passwordField.value);
            }
            
            formData.append('position', document.getElementById('editEmployeePosition').value);
            const editLineManagerElement = document.getElementById('editEmployeeLineManager');
            formData.append('line_manager', editLineManagerElement ? editLineManagerElement.value : '');
            formData.append('department_id', document.getElementById('editEmployeeDepartment').value);
            formData.append('sub_department', document.getElementById('editEmployeeSubDepartment').value);
            formData.append('bank_name', document.getElementById('editEmployeeBankName').value);
            formData.append('account_title', document.getElementById('editEmployeeAccountTitle').value);
            formData.append('account_number', document.getElementById('editEmployeeAccountNumber').value);
            formData.append('account_type', document.getElementById('editEmployeeAccountType').value);
            formData.append('bank_branch', document.getElementById('editEmployeeBankBranch').value);
            formData.append('salary', document.getElementById('editEmployeeSalary').value);
            formData.append('joining_date', document.getElementById('editEmployeeJoiningDate').value);
            formData.append('qualification_institution', document.getElementById('editEmployeeQualificationInstitution').value);
            formData.append('education_percentage', document.getElementById('editEmployeeEducationPercentage').value);
            formData.append('specialization', document.getElementById('editEmployeeSpecialization').value);
            formData.append('last_organization', document.getElementById('editEmployeeLastOrganization').value);
            formData.append('last_designation', document.getElementById('editEmployeeLastDesignation').value);
            formData.append('experience_from_date', document.getElementById('editEmployeeExperienceFromDate').value);
            formData.append('experience_to_date', document.getElementById('editEmployeeExperienceToDate').value);
            formData.append('job_type', document.getElementById('editEmployeeJobType').value);
            formData.append('shift_id', document.getElementById('editEmployeeShiftTiming').value);
            formData.append('marital_status', document.getElementById('editEmployeeMaritalStatus').value);

            // Add file uploads if new files are selected
            const cvFile = document.getElementById('editEmployeeCVAttachment').files[0];
            const idCardFile = document.getElementById('editEmployeeIDCardAttachment').files[0];
            const otherFiles = document.getElementById('editEmployeeOtherDocuments').files;

            if (cvFile) {
                formData.append('cv_attachment', cvFile);
            }

            if (idCardFile) {
                formData.append('id_card_attachment', idCardFile);
            }

            for (let i = 0; i < otherFiles.length; i++) {
                formData.append('other_documents[]', otherFiles[i]);
            }

            fetch('include/api/employee.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                })
                .then(res => {
                    if (res.success) {
                        showEmployeeToast('Employee updated successfully!', 'edit');
                        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editEmployeeModal'));
                        modal.hide();
                        loadEmployees();
                    } else {
                        showEmployeeToast('Failed to update employee: ' + (res.message || 'Unknown error'), 'edit');
                    }
                })
                .catch(err => {
                    showEmployeeToast('Error: ' + err, 'edit');
                });
        });
    }

    loadDepartmentsForFilter();
});

// Function to load departments into filter dropdown
function loadDepartmentsForFilter() {
    fetch('include/api/department.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                const deptFilterSelect = document.getElementById('departmentFilter');
                if (!deptFilterSelect) return;

                let optionsHTML = '<option value="">All Departments</option>';
                const departmentNames = new Set();

                data.data.forEach(dept => {
                    if (dept.status === 'active') {
                        departmentNames.add(dept.dept_name);
                    }
                });

                departmentNames.forEach(name => {
                    optionsHTML += `<option value="${name}">${name}</option>`;
                });

                deptFilterSelect.innerHTML = optionsHTML;
            }
        })
        .catch(error => {
        });
}

// Toast function
function showEmployeeToast(msg, actionType) {
    var toastEl = document.getElementById('employeeToast');
    var toastMsg = document.getElementById('employeeToastMsg');
    toastMsg.textContent = msg;
    toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
    if (actionType === 'delete' || actionType === 'error') {
        toastEl.classList.add('text-bg-danger');
    } else if (actionType === 'restore') {
        toastEl.classList.add('text-bg-warning');
    } else {
        toastEl.classList.add('text-bg-success');
    }
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Make toast function globally available
window.showEmployeeToast = showEmployeeToast;

// Format date to dd/mm/yyyy
function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00' || dateString === 'null') {
        return '';
    }

    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
        return '';
    }

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${day}/${month}/${year}`;
}

// Load employee leave history
function loadEmployeeLeaveHistory(empId) {
    $.ajax({
        url: 'include/api/employee-leave-history.php?emp_id=' + empId,
        method: 'GET',
        success: function(response) {
            if (response.success && response.leaves) {
                const leaves = response.leaves;
                let leaveHistoryHtml = '';

                // Count approved leaves
                const approvedLeaves = leaves.filter(leave => leave.status === 'approved');
                const totalDays = approvedLeaves.reduce((sum, leave) => {
                    const startDate = new Date(leave.start_date);
                    const endDate = new Date(leave.end_date);
                    const diffTime = Math.abs(endDate - startDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    return sum + diffDays;
                }, 0);

                // Update the label with total count
                $('#approvedLeavesLabel').html(`<i class="fas fa-calendar-times info-icon"></i> Approved Leaves (${totalDays} days)`);

                if (leaves.length > 0) {
                    // Group leaves by type and calculate days
                    const leaveGroups = {};
                    leaves.forEach(function(leave) {
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

                    leaveHistoryHtml = '<div class="row">';
                    Object.values(leaveGroups).forEach(function(group) {
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
                } else {
                    leaveHistoryHtml = '<div class="text-center text-muted py-3">No leave data available</div>';
                    $('#approvedLeavesLabel').html('<i class="fas fa-calendar-times info-icon"></i> Approved Leaves (0 days)');
                }

                $('#viewEmployeeLeaveHistory').html(leaveHistoryHtml);
            } else {
                $('#viewEmployeeLeaveHistory').html('<div class="text-center text-muted py-3">No leave data available</div>');
                $('#approvedLeavesLabel').html('<i class="fas fa-calendar-times info-icon"></i> Approved Leaves (0 days)');
            }
        },
        error: function(xhr, status, error) {
            $('#viewEmployeeLeaveHistory').html('<div class="text-center text-danger py-3">Error loading leave history</div>');
        }
    });
}

// Input validation for Phone, CNIC, Emergency Contact (Add & Edit Employee)
function setEmployeeInputValidation() {
    // Helper to allow only numbers
    function onlyNumberInput(e) {
        if (!/\d/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Tab') {
            e.preventDefault();
        }
    }
    // Phone & Emergency Contact: 11 digits only
    $('#newEmployeePhone, #newEmployeeEmergencyContact, #editEmployeePhone, #editEmployeeEmergencyContact').on('keypress', function(e) {
        onlyNumberInput(e);
        if (this.value.length >= 11 && e.key !== 'Backspace' && e.key !== 'Delete') {
            e.preventDefault();
        }
    });
    // CNIC: 13 digits only
    $('#newEmployeeCNIC, #editEmployeeCNIC').on('keypress', function(e) {
        onlyNumberInput(e);
        if (this.value.length >= 13 && e.key !== 'Backspace' && e.key !== 'Delete') {
            e.preventDefault();
        }
    });
    // Paste event: remove non-digits and limit length
    $('#newEmployeePhone, #editEmployeePhone, #newEmployeeEmergencyContact, #editEmployeeEmergencyContact').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });
    $('#newEmployeeCNIC, #editEmployeeCNIC').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 13);
    });
}

$(document).ready(function() {
    setEmployeeInputValidation();
    setupFileInputHandlers();
});

// File input handlers for document uploads
function setupFileInputHandlers() {
    // CV Attachment handler (Add Employee)
    $('#newEmployeeCVAttachment').on('change', function() {
        const file = this.files[0];
        const display = $(this).siblings('.file-input-display').find('.file-name-display');

        if (file) {
            display.text(file.name).css('color', '#495057');
        } else {
            display.text('No file chosen').css('color', '#6c757d');
        }
    });

    // ID Card Attachment handler (Add Employee)
    $('#newEmployeeIDCardAttachment').on('change', function() {
        const file = this.files[0];
        const display = $(this).siblings('.file-input-display').find('.file-name-display');

        if (file) {
            display.text(file.name).css('color', '#495057');
        } else {
            display.text('No file chosen').css('color', '#6c757d');
        }
    });

    // Other Documents handler (Add Employee)
    $('#newEmployeeOtherDocuments').on('change', function() {
        const files = this.files;
        const display = $(this).siblings('.file-input-display').find('.file-name-display');

        if (files.length > 0) {
            if (files.length === 1) {
                display.text(files[0].name).css('color', '#495057');
            } else {
                display.text(`${files.length} files selected`).css('color', '#495057');
            }
        } else {
            display.text('No file chosen').css('color', '#6c757d');
        }
    });

    // CV Attachment handler (Edit Employee)
    $('#editEmployeeCVAttachment').on('change', function() {
        const file = this.files[0];
        const display = $('#editEmployeeCVDisplay');

        if (file) {
            display.text(file.name).css('color', '#495057');
        } else {
            // Keep existing file name if no new file selected
            const existingName = display.data('original-name');
            if (existingName) {
                display.text(existingName).css('color', '#495057');
            } else {
                display.text('No file chosen').css('color', '#6c757d');
            }
        }
    });

    // ID Card Attachment handler (Edit Employee)
    $('#editEmployeeIDCardAttachment').on('change', function() {
        const file = this.files[0];
        const display = $('#editEmployeeIDCardDisplay');

        if (file) {
            display.text(file.name).css('color', '#495057');
        } else {
            // Keep existing file name if no new file selected
            const existingName = display.data('original-name');
            if (existingName) {
                display.text(existingName).css('color', '#495057');
            } else {
                display.text('No file chosen').css('color', '#6c757d');
            }
        }
    });

    // Other Documents handler (Edit Employee)
    $('#editEmployeeOtherDocuments').on('change', function() {
        const files = this.files;
        const display = $('#editEmployeeOtherDocsDisplay');

        if (files.length > 0) {
            if (files.length === 1) {
                display.text(files[0].name).css('color', '#495057');
            } else {
                display.text(`${files.length} files selected`).css('color', '#495057');
            }
        } else {
            // Keep existing file names if no new files selected
            const existingNames = display.data('original-names');
            if (existingNames) {
                display.text(existingNames).css('color', '#495057');
            } else {
                display.text('No file chosen').css('color', '#6c757d');
            }
        }
    });
}

// Load employee documents for edit modal - now uses employees table
function loadEmployeeDocumentsForEdit(empId) {
    // Get employee data from the current employee object
    const emp = window.currentEmployeeData;

    if (emp) {
        // Display CV file if exists
        if (emp.cv_attachment && emp.cv_attachment.trim() !== '') {
            const fileName = emp.cv_attachment.split('/').pop();
            $('#editEmployeeCVDisplay').text(fileName)
                .css('color', '#495057')
                .data('original-name', fileName);
        } else {
            $('#editEmployeeCVDisplay').text('No file chosen')
                .css('color', '#6c757d')
                .removeData('original-name');
        }

        // Display ID Card file if exists
        if (emp.id_card_attachment && emp.id_card_attachment.trim() !== '') {
            const fileName = emp.id_card_attachment.split('/').pop();
            $('#editEmployeeIDCardDisplay').text(fileName)
                .css('color', '#495057')
                .data('original-name', fileName);
        } else {
            $('#editEmployeeIDCardDisplay').text('No file chosen')
                .css('color', '#6c757d')
                .removeData('original-name');
        }

        // Display other documents if exist
        if (emp.other_documents && emp.other_documents.trim() !== '') {
            try {
                const otherDocs = JSON.parse(emp.other_documents);
                if (Array.isArray(otherDocs) && otherDocs.length > 0) {
                    let displayText = '';
                    if (otherDocs.length === 1) {
                        displayText = otherDocs[0].split('/').pop();
                    } else {
                        displayText = `${otherDocs.length} files: ${otherDocs.map(f => f.split('/').pop()).join(', ')}`;
                    }
                    $('#editEmployeeOtherDocsDisplay').text(displayText)
                        .css('color', '#495057')
                        .data('original-names', displayText);
                } else {
                    $('#editEmployeeOtherDocsDisplay').text('No file chosen')
                        .css('color', '#6c757d')
                        .removeData('original-names');
                }
            } catch (e) {
                $('#editEmployeeOtherDocsDisplay').text('No file chosen')
                    .css('color', '#6c757d')
                    .removeData('original-names');
            }
        } else {
            $('#editEmployeeOtherDocsDisplay').text('No file chosen')
                .css('color', '#6c757d')
                .removeData('original-names');
        }
    } else {
        $('#editEmployeeCVDisplay').text('Error loading employee data')
            .css('color', '#dc3545');
        $('#editEmployeeIDCardDisplay').text('Error loading employee data')
            .css('color', '#dc3545');
        $('#editEmployeeOtherDocsDisplay').text('Error loading employee data')
            .css('color', '#dc3545');
    }
}

// --- Shifts JS ---
function formatDateTime(datetimeStr) {
    // "2025-06-25 21:00:00" => "25-06-2025 21:00:00"
    if (!datetimeStr) return '';
    const [datePart, timePart] = datetimeStr.split(' ');
    if (!datePart || !timePart) return datetimeStr;
    const [year, month, day] = datePart.split('-');
    return `${day}-${month}-${year} ${timePart}`;
}

function formatTime12(timeStr) {
    if (!timeStr) return '';
    let [hours, minutes] = timeStr.split(':');
    hours = parseInt(hours);
    minutes = parseInt(minutes);
    if (isNaN(hours) || isNaN(minutes)) return '';
    let ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    return `${hours}:${minutes} ${ampm}`;
}

function renderShiftsTable(shifts) {
    const tbody = document.getElementById('shiftsTableBody');
    tbody.innerHTML = '';
    shifts.forEach(shift => {
        const start = formatTime12(shift.start_time);
        const end = formatTime12(shift.end_time);
        tbody.innerHTML += `
            <tr>
                <td>${shift.name}</td>
                <td>${start}</td>
                <td>${end}</td>
                <td>${shift.grace_period} min</td>
                <td>${shift.half_day_hours} hrs</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary edit-shift-btn" data-id="${shift.id}" data-name="${shift.name}" data-start_time="${shift.start_time}" data-end_time="${shift.end_time}" data-grace_period="${shift.grace_period}" data-half_day_hours="${shift.half_day_hours}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-outline-danger btn-sm delete-shift-btn" data-id="${shift.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
}

function loadShifts() {
    fetch('include/api/shifts.php')
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                renderShiftsTable(res.data);
            }
        });
}

window.addShiftForm = window.addShiftForm || document.getElementById('addShiftForm');
if (window.addShiftForm) {
    window.addShiftForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = window.addShiftForm.dataset.editId || null;
        const shift_name = document.getElementById('shiftName').value;
        const startTimeValue = document.getElementById('startTime').value;
        const endTimeValue = document.getElementById('endTime').value;
        const today = new Date().toISOString().slice(0, 10);
        if (!startTimeValue || !endTimeValue) {
            alert('Start time aur end time required hain!');
            return;
        }
        const start_time = today + ' ' + startTimeValue + ':00';
        const end_time = today + ' ' + endTimeValue + ':00';
        const grace_time = document.getElementById('graceTime').value;
        const halfday_hours = document.getElementById('halfdayHours').value;
        const body = id ? {
            id,
            shift_name,
            start_time,
            end_time,
            grace_time,
            halfday_hours
        } : {
            shift_name,
            start_time,
            end_time,
            grace_time,
            halfday_hours
        };
        fetch('include/api/shifts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    window.addShiftForm.reset();
                    delete window.addShiftForm.dataset.editId;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('addShiftModal')).hide();
                    setTimeout(() => {
                        loadShifts();
                        refreshAllShiftDropdowns();
                    }, 500);
                } else {
                    alert('Error: ' + (res.message || 'Failed to save shift'));
                }
            });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadShifts();
    // Edit and delete shift button handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-shift-btn')) {
            const btn = e.target.closest('.edit-shift-btn');
            document.getElementById('shiftName').value = btn.dataset.name;
            document.getElementById('startTime').value = btn.dataset.start_time;
            document.getElementById('endTime').value = btn.dataset.end_time;
            document.getElementById('graceTime').value = btn.dataset.grace_period;
            document.getElementById('halfdayHours').value = btn.dataset.half_day_hours;
            window.addShiftForm.dataset.editId = btn.dataset.id;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addShiftModal')).show();
        }
        if (e.target.closest('.delete-shift-btn')) {
            const id = e.target.closest('.delete-shift-btn').dataset.id;
            // SweetAlert2 confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this shift!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('include/api/shifts.php', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `id=${id}`
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                loadShifts();
                                refreshAllShiftDropdowns();
                                Swal.fire('Deleted!', 'Shift has been deleted.', 'success');
                            } else {
                                Swal.fire('Error!', res.message || 'Failed to delete shift', 'error');
                            }
                        });
                }
            });
        }
    });
});

function formatTime(datetimeStr) {
    // "2025-06-25 21:00:00" => "08:30pm"
    const date = new Date(datetimeStr.replace(' ', 'T'));
    let hours = date.getHours();
    let minutes = date.getMinutes();
    let ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 ko 12 banao
    minutes = minutes < 10 ? '0' + minutes : minutes;
    return `${hours}:${minutes}${ampm}`;
}

function loadShiftsDropdown(callback) {
    fetch('include/api/shifts.php')
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                // Dono dropdowns ko populate karo
                const dropdowns = [
                    document.getElementById('newEmployeeShiftTiming'),
                    document.getElementById('editEmployeeShiftTiming')
                ];
                dropdowns.forEach(dropdown => {
                    if (dropdown) {
                        dropdown.innerHTML = '<option value="">Select Shift</option>';
                        res.data.forEach(shift => {
                            const start = formatTime12(shift.start_time);
                            const end = formatTime12(shift.end_time);
                            dropdown.innerHTML += `<option value="${shift.id}">${shift.name} (${start} - ${end})</option>`;
                        });
                    }
                });
                if (typeof callback === 'function') callback();
            }
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadShiftsDropdown('newEmployeeShiftTiming');
    loadShiftsDropdown('editEmployeeShiftTiming');
});

// Shift add/edit/delete ke baad bhi dropdown update karo
function refreshAllShiftDropdowns() {
    loadShiftsDropdown('newEmployeeShiftTiming');
    loadShiftsDropdown('editEmployeeShiftTiming');
}

// Account Type ke hisaab se Account Number validation
$('#newEmployeeAccountType').on('change', function() {
    var type = $(this).val();
    var $acc = $('#newEmployeeAccountNumber');
    $acc.val('');
    $acc.attr('maxlength', '');
    $acc.off('input keypress');

    if (type) {
        $acc.prop('disabled', false);
        if (type === 'IBAN number') {
            $acc.attr('maxlength', 24);
            $acc.on('input', function() {
                this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 24);
            });
        } else if (type === 'IBFT number') {
            $acc.attr('maxlength', 20);
            $acc.on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 20);
            });
        } else if (type === 'Mobile Banking') {
            $acc.attr('maxlength', 11);
            $acc.on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });
        }
    } else {
        $acc.prop('disabled', true);
    }
});

// Edit Employee Modal: Account Type ke hisaab se Account Number validation
$('#editEmployeeAccountType').on('change', function() {
    var type = $(this).val();
    var $acc = $('#editEmployeeAccountNumber');
    $acc.val('');
    $acc.attr('maxlength', '');
    $acc.off('input keypress');

    if (type) {
        $acc.prop('disabled', false);
        if (type === 'IBAN number') {
            $acc.attr('maxlength', 24);
            $acc.on('input', function() {
                this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 24);
            });
        } else if (type === 'IBFT number') {
            $acc.attr('maxlength', 20);
            $acc.on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 20);
            });
        } else if (type === 'Mobile Banking') {
            $acc.attr('maxlength', 11);
            $acc.on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });
        }
    } else {
        $acc.prop('disabled', true);
    }
});

// Page load par Account Number fields ko disable karo
$(document).ready(function() {
    $('#newEmployeeAccountNumber').prop('disabled', true);
    $('#editEmployeeAccountNumber').prop('disabled', true);
});

// Load employee documents function - now uses employees table directly
function loadEmployeeDocuments(empId) {
    // This function now gets data from employees table via the employee data
    // that's already loaded in the edit form
    const employeeData = window.currentEmployeeData;

    if (employeeData) {
        let documentsHtml = '';

        // Display CV if exists
        if (employeeData.cv_attachment && employeeData.cv_attachment.trim() !== '') {
            const cvPath = employeeData.cv_attachment;
            const fileName = cvPath.split('/').pop();

            // Check if path already contains uploads folder
            let filePath;
            if (cvPath.startsWith('uploads/')) {
                filePath = `../${cvPath}`;
            } else {
                filePath = `../uploads/joining_documents/${cvPath}`;
            }

            documentsHtml += `
                <div class="document-item mb-2">
                    <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            <span class="document-name">CV/Resume</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="${filePath}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="${filePath}" download="${fileName}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }

        // Display ID Card attachment if exists
        if (employeeData.id_card_attachment && employeeData.id_card_attachment.trim() !== '') {
            const idCardPath = employeeData.id_card_attachment;
            const idCardFileName = idCardPath.split('/').pop();

            // Check if path already contains uploads folder
            let idCardFilePath;
            if (idCardPath.startsWith('uploads/')) {
                idCardFilePath = `../${idCardPath}`;
            } else {
                idCardFilePath = `../uploads/joining_documents/${idCardPath}`;
            }

            // Get file extension for icon
            const fileExtension = idCardPath.split('.').pop().toLowerCase();
            let iconClass = 'fas fa-file';
            if (fileExtension === 'pdf') iconClass = 'fas fa-file-pdf text-danger';
            else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) iconClass = 'fas fa-file-image text-success';
            else if (['doc', 'docx'].includes(fileExtension)) iconClass = 'fas fa-file-word text-primary';

            documentsHtml += `
                <div class="document-item mb-2">
                    <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                        <div class="d-flex align-items-center">
                            <i class="${iconClass} me-2"></i>
                            <span class="document-name">ID Card</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="${idCardFilePath}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="${idCardFilePath}" download="${idCardFileName}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }

        // Display other documents if exist
        if (employeeData.other_documents && employeeData.other_documents.trim() !== '') {
            try {
                const otherDocs = JSON.parse(employeeData.other_documents);
                if (Array.isArray(otherDocs) && otherDocs.length > 0) {
                    otherDocs.forEach((doc, index) => {
                        const fileExtension = doc.split('.').pop().toLowerCase();
                        let iconClass = 'fas fa-file';
                        if (fileExtension === 'pdf') iconClass = 'fas fa-file-pdf text-danger';
                        else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) iconClass = 'fas fa-file-image text-success';
                        else if (['doc', 'docx'].includes(fileExtension)) iconClass = 'fas fa-file-word text-primary';

                        // Check if path already contains uploads folder
                        let docPath;
                        if (doc.startsWith('uploads/')) {
                            docPath = `../${doc}`;
                        } else {
                            docPath = `../uploads/joining_documents/${doc}`;
                        }

                        const docFileName = doc.split('/').pop();

                        documentsHtml += `
                            <div class="document-item mb-2">
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="${iconClass} me-2"></i>
                                        <span class="document-name">Document ${index + 1}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="${docPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="${docPath}" download="${docFileName}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (e) {
            }
        }

        if (documentsHtml) {
            $('#employeeDocumentsContainer').html(documentsHtml);
        } else {
            $('#employeeDocumentsContainer').html(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-file-slash fa-2x mb-2"></i>
                    <p class="mb-0">No documents uploaded</p>
                </div>
            `);
        }
    } else {
        $('#employeeDocumentsContainer').html(`
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <p class="text-muted">Error loading employee data</p>
            </div>
        `);
    }
}

// Get file icon based on file extension
function getFileIcon(filename) {
    const extension = filename.split('.').pop().toLowerCase();

    switch (extension) {
        case 'pdf':
            return 'fas fa-file-pdf text-danger';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word text-primary';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'jfif':
        case 'webp':
            return 'fas fa-file-image text-success';
        default:
            return 'fas fa-file text-secondary';
    }
}

// Download document function
function downloadDocument(documentId) {
    $.ajax({
        url: 'include/api/employee-documents.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'download',
            document_id: documentId
        }),
        xhrFields: {
            responseType: 'blob'
        },
        success: function(data, status, xhr) {
            const filename = xhr.getResponseHeader('Content-Disposition');
            const blob = new Blob([data]);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename ? filename.split('filename=')[1].replace(/"/g, '') : 'document';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },
        error: function(xhr, status, error) {
            alert('Error downloading document');
        }
    });
}

// ==================== EMAIL VALIDATION ====================

// Edit Employee Email Validation
$(document).ready(function() {
    const editEmailInput = $('#editEmployeeEmail');
    const editEmailFeedback = $('#editEmailFeedback');
    let editEmailCheckTimeout;
    let editOriginalEmail = '';
    
    // Store original email when modal opens (will be updated by employee.js)
    $('#editEmployeeModal').on('shown.bs.modal', function() {
        editOriginalEmail = window.currentEditOriginalEmail || editEmailInput.val() || '';
        editEmailFeedback.text('').css('color', '');
        editEmailInput.css('borderColor', '');
    });
    
    if (editEmailInput.length) {
        editEmailInput.on('input', function() {
            const email = $(this).val().trim();
            
            clearTimeout(editEmailCheckTimeout);
            
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            if (email === '') {
                editEmailFeedback.text('').css('color', '');
                $(this).css('borderColor', '');
                return;
            }
            
            if (!emailRegex.test(email)) {
                editEmailFeedback.text('Invalid email format').css('color', '#dc3545');
                $(this).css('borderColor', '#dc3545');
                return;
            }
            
            // Skip check if unchanged
            if (email === editOriginalEmail) {
                editEmailFeedback.text('Current email (unchanged)').css('color', '#6c757d');
                $(this).css('borderColor', '#6c757d');
                return;
            }
            
            // Check duplicate with debounce
            editEmailCheckTimeout = setTimeout(function() {
                const empId = window.currentEditEmpId || '';
                fetch('../check-email.php?email=' + encodeURIComponent(email) + '&exclude_emp_id=' + empId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            editEmailFeedback.text('This email is already registered').css('color', '#dc3545');
                            editEmailInput.css('borderColor', '#dc3545');
                        } else {
                            editEmailFeedback.text('Email is available ✓').css('color', '#28a745');
                            editEmailInput.css('borderColor', '#28a745');
                        }
                    })
                    .catch(error => {
                    });
            }, 500);
        });
    }
});

// Add Employee Email Validation  
$(document).ready(function() {
    const addEmailInput = $('#newEmployeeEmail');
    const addEmailFeedback = $('#addEmailFeedback');
    let addEmailCheckTimeout;
    
    // Reset when modal opens
    $('#addEmployeeModal').on('show.bs.modal', function() {
        addEmailFeedback.text('').css('color', '');
        addEmailInput.css('borderColor', '');
    });
    
    if (addEmailInput.length) {
        addEmailInput.on('input', function() {
            const email = $(this).val().trim();
            
            clearTimeout(addEmailCheckTimeout);
            
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            if (email === '') {
                addEmailFeedback.text('').css('color', '');
                $(this).css('borderColor', '');
                return;
            }
            
            if (!emailRegex.test(email)) {
                addEmailFeedback.text('Invalid email format').css('color', '#dc3545');
                $(this).css('borderColor', '#dc3545');
                return;
            }
            
            // Check duplicate with debounce
            addEmailCheckTimeout = setTimeout(function() {
                fetch('../check-email.php?email=' + encodeURIComponent(email))
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            addEmailFeedback.text('This email is already registered').css('color', '#dc3545');
                            addEmailInput.css('borderColor', '#dc3545');
                        } else {
                            addEmailFeedback.text('Email is available ✓').css('color', '#28a745');
                            addEmailInput.css('borderColor', '#28a745');
                        }
                    })
                    .catch(error => {
                    });
            }, 500);
        });
    }
});