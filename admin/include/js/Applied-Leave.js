    // Helper function to create full name
    function createFullName(firstName, middleName, lastName) {
        return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
    }

    $(document).ready(function() {
        // Initialize DataTable
        const appliedLeaveTable = $('#appliedLeaveTable').DataTable({
            processing: true,
            serverSide: false,
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"top"Bf>rt<"bottom"ip>'.replace('f', ''),
            order: [
                [8, 'desc']
            ],
            columnDefs: [{
                    // From Date
                    targets: 4,
                    type: 'date-eu',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' +
                                ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                                date.getFullYear();
                        }
                        return data;
                    }
                },
                {
                    // To Date
                    targets: 5,
                    type: 'date-eu',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' +
                                ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                                date.getFullYear();
                        }
                        return data;
                    }
                },
                {
                    // Applied On
                    targets: 8,
                    type: 'date-eu',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' +
                                ('0' + (date.getMonth() + 1)).slice(-2) + '/' +
                                date.getFullYear();
                        }
                        return data;
                    }
                }
            ],
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
        appliedLeaveTable.buttons().container().appendTo('#exportButtonContainer');

        // Function to load leave data
        function loadLeaveData() {
            $.ajax({
                url: 'include/api/Applied-Leave.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        appliedLeaveTable.clear();
                        response.data.forEach(function(row) {
                            // Calculate days
                            let days = 0;
                            if (row.start_date && row.end_date) {
                                const start = new Date(row.start_date);
                                const end = new Date(row.end_date);
                                days = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                            }
                            // Document path ko simple relative path banate hain
                            let docPath = row.document_path;
                            if (docPath && !docPath.startsWith('http')) {
                                // Simple relative path using ../
                                docPath = '../' + (docPath.startsWith('/') ? docPath.substring(1) : docPath);
                            }
                            appliedLeaveTable.row.add([
                                row.emp_id || '',
                                createFullName(row.first_name, row.middle_name, row.last_name) || '',
                                row.department || '',
                                row.type_name || '',
                                row.start_date || '',
                                row.end_date || '',
                                days,
                                truncateWords(row.reason, 4),
                                row.created_at || '',
                                truncateWords(row.admin_comment, 4),
                                `<span class="attendance-status status-${row.status ? row.status.toLowerCase() : ''}">${row.status ? row.status.charAt(0).toUpperCase() + row.status.slice(1) : ''}</span>`,
                                docPath ? `<button type="button" class="btn btn-sm btn-outline-primary view-document" data-doc="${docPath}" title="View Document"><i class="fas fa-eye"></i></button>` : '-',
                                `<button type="button" class="btn btn-sm btn-outline-primary  btn-outline-warning edit-leave" data-leaveid="${row.leave_id}" data-status="${row.status}" data-comment="${row.admin_comment || ''}" title="Edit Leave"><i class="fas fa-edit"></i></button>`
                            ]);
                        });
                        appliedLeaveTable.draw();
                    }
                },
                error: function(xhr, status, error) {
                }
            });
        }

        // Load initial data
        loadLeaveData();

        // Function to apply filters
        function applyLeaveFilters() {
            const empId = $('#empIdFilter').val().toLowerCase();
            const empName = $('#nameFilter').val().toLowerCase();
            const department = $('#departmentFilter').val().toLowerCase();
            const status = $('#statusFilter').val().toLowerCase();

            // Clear previous filters
            appliedLeaveTable.search('').columns().search('');

            // Apply filters
            if (empId) {
                appliedLeaveTable.column(0).search(empId);
            }
            if (empName) {
                appliedLeaveTable.column(1).search(empName);
            }
            if (department) {
                appliedLeaveTable.column(2).search(department);
            }
            if (status) {
                appliedLeaveTable.column(10).search(status);
            }

            // Draw table with all filters
            appliedLeaveTable.draw();
        }

        // Add real-time filtering
        $('#empIdFilter, #nameFilter, #departmentFilter, #statusFilter').on('input change', function() {
            applyLeaveFilters();
        });

        // Reset filters
        $('#leaveFilterForm').on('reset', function() {
            appliedLeaveTable.search('').columns().search('').draw();
        });

        // Document view in modal
        $(document).on('click', '.view-document', function(e) {
            e.preventDefault();
            var docUrl = $(this).data('doc');
            var ext = docUrl.split('.').pop().toLowerCase();
            var html = '';
            if (['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp'].includes(ext)) {
                html = '<img src="' + docUrl + '" alt="Document" style="max-width:90%;max-height:80vh;">';
            } else if (ext === 'pdf') {
                html = '<embed src="' + docUrl + '" type="application/pdf" width="90%" height="650px">';
            } else {
                if ($('#documentModal').length > 0) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('documentModal'));
                    if (modal) modal.hide();
                }
                Swal.fire({
                    icon: 'info',
                    title: 'Preview Not Supported',
                    html: 'This file type is not supported for preview.<br><a href="' + docUrl + '" target="_blank">Click here to download.</a>',
                    showConfirmButton: true
                });
                return;
            }

            // Remove old modal if exists
            $('#documentModal').remove();
            // Add new modal dynamically
            $('body').append(`
            <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Document View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body" id="documentModalBody" style="text-align:center;">
                  </div>
                </div>
              </div>
            </div>`);
            $('#documentModalBody').html(html);
            var modalEl = document.getElementById('documentModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });

        // Edit leave button click
        $(document).on('click', '.edit-leave', function() {
            var leaveId = $(this).data('leaveid');
            var status = $(this).data('status');
            var comment = $(this).data('comment');

            $('#editLeaveId').val(leaveId);
            $('#editStatus').val(status);
            $('#editAdminComment').val(comment);

            $('#editLeaveModal').modal('show');
        });

        // Save leave changes
        $('#saveLeaveChanges').on('click', function() {
            var leaveId = $('#editLeaveId').val();
            var status = $('#editStatus').val();
            var comment = $('#editAdminComment').val();

            $.ajax({
                url: 'include/api/leave-requests.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    leave_id: leaveId,
                    status: status,
                    admin_comment: comment
                }),
                success: function(response) {
                    if (response.success) {
                        $('#editLeaveModal').modal('hide');
                        loadLeaveData(); // Reload table data
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Leave status updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.error || 'Failed to update leave status.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.'
                    });
                }
            });
        });

        const editDeptSelect = document.getElementById('editEmployeeDepartment');
        const newDeptSelect = document.getElementById('newEmployeeDepartment');
        if (editDeptSelect) {
            // editDeptSelect pe koi bhi operation yahan karein
        }
        if (newDeptSelect) {
            // newDeptSelect pe koi bhi operation yahan karein
        }

        $('#appliedLeaveTable tbody').on('click', 'tr', function(e) {
            // Ignore if click was on a button or inside a button
            if ($(e.target).closest('button').length > 0) return;

            var rowData = appliedLeaveTable.row(this).data();
            if (!rowData) return;

            // Table columns: [emp_id, emp_name, department, type_name, start_date, end_date, days, reason, created_at, admin_comment, document, status]
            // Get full text for reason and admin_comment from the original data (not truncated)
            var fullReason = rowData[7];
            var fullAdminComment = rowData[9];
            if (typeof rowData[7] === 'string' && rowData[7].includes('title="')) {
                // If truncated, extract full text from title attribute
                var match = rowData[7].match(/title=\"([^\"]*)\"/);
                if (match) fullReason = match[1];
            }
            if (typeof rowData[9] === 'string' && rowData[9].includes('title="')) {
                var match = rowData[9].match(/title=\"([^\"]*)\"/);
                if (match) fullAdminComment = match[1];
            }

            var html = `
            <div class="leave-details-modal-ui">
                <div class="detail-row"><span class="detail-label">Employee Name:</span><span class="detail-value">${rowData[1] || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Department:</span><span class="detail-value">${rowData[2] || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Leave Type:</span><span class="detail-value">${rowData[3] || ''}</span></div>
                <div class="detail-row"><span class="detail-label">From:</span><span class="detail-value">${formatDateDMY(rowData[4])}</span></div>
                <div class="detail-row"><span class="detail-label">To:</span><span class="detail-value">${formatDateDMY(rowData[5])}</span></div>
                <div class="detail-row"><span class="detail-label">Days:</span><span class="detail-value">${rowData[6] || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Status:</span><span class="detail-value">${$(rowData[10]).text() || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Reason:</span><span class="detail-value left-align" style="white-space:pre-line">${fullReason || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Admin Comment:</span><span class="detail-value left-align" style="white-space:pre-line">${fullAdminComment || ''}</span></div>
            </div>
        `;
            // Document link
            if (rowData[11] && rowData[11].includes('href=')) {
                html += `<div class="detail-row"><span class="detail-label">Document:</span> <span class="detail-value">${rowData[11]}</span></div>`;
            }

            $('#adminLeaveDetailsBody').html(html);
            var modal = new bootstrap.Modal(document.getElementById('adminLeaveDetailsModal'));
            modal.show();
        });
    });

    function truncateWords(text, wordLimit) {
        if (!text) return '';
        const words = text.split(' ');
        let shortText = words.slice(0, wordLimit).join(' ');
        if (words.length > wordLimit) shortText += '...';
        return `<span title="${text.replace(/\"/g, '&quot;')}">${shortText}</span>`;
    }

    function formatDateDMY(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const day = ('0' + d.getDate()).slice(-2);
        const month = ('0' + (d.getMonth() + 1)).slice(-2);
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }