var appliedLeaveTable;

// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

$(document).ready(function() {
    // Initialize DataTable first
    appliedLeaveTable = $('#appliedLeaveTable').DataTable({
        dom: 'tip',
        paging: true,
        searching: false,
        ordering: true,
        info: true,
        pageLength: 10,
        lengthChange: false,
        order: [[7, 'desc']], // Sort by Applied On column
        columns: [
            { data: 'emp_id' },
                {data: null,
                    render: function(data, type, row) {
                        return createFullName(row.first_name, row.middle_name, row.last_name);
                    }},
            { data: 'type_name' },
            { data: 'start_date', render: formatDate },
            { data: 'end_date', render: formatDate },
            { data: 'days' },
            { data: 'reason',
                render: function(data, type, row) {
                    if (!data) return '';
                    let words = data.split(' ');
                    let shortText = words.slice(0, 5).join(' ');
                    if (words.length > 5) shortText += '...';
                    return `<span title="${data.replace(/\"/g, '&quot;')}">${shortText}</span>`;
                }
            },
            { data: 'created_at', render: formatDate },
            { data: 'admin_comment', defaultContent: '',
                render: function(data, type, row) {
                    if (!data) return '';
                    let words = data.split(' ');
                    let shortText = words.slice(0, 5).join(' ');
                    if (words.length > 5) shortText += '...';
                    return `<span title="${data.replace(/\"/g, '&quot;')}">${shortText}</span>`;
                }
            },
            {
                data: 'document_path',
                render: function(data, type, row) {
                    if (data && data !== '') {
                        // Document path ko simple relative path banate hain
                        let docPath = row.document_path;
                        if (docPath && !docPath.startsWith('http')) {
                            // Simple relative path using ../
                            docPath = '../' + (docPath.startsWith('/') ? docPath.substring(1) : docPath);
                        }
                        return `<button type="button" class="btn btn-sm btn-outline-primary view-document" data-doc="${docPath}" title="View Document"><i class="fas fa-eye"></i></button>`;
                    } else {
                        return '-';
                    }
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    return `<span class="attendance-status status-${data.toLowerCase()}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    let actionsHtml = '';
                    
                    // Add edit button only for pending status
                    if (row.status.toLowerCase() === 'pending') {
                        actionsHtml += `<button type="button" class="btn btn-sm btn-outline-primary edit-leave" data-leave-id="${row.leave_id}" title="Edit Leave"><i class="fas fa-edit"></i></button>`;
                    } else {
                        actionsHtml += '<span class="text-muted">No actions</span>';
                    }
                    
                    return actionsHtml;
                }
            }
        ]
    });

    // Initial load
    loadLeaveHistory();

    // Set up polling every 3 seconds
    setInterval(loadLeaveHistory, 3000);

    function loadLeaveHistory() {
        $.ajax({
            url: 'include/api/leave_history.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) return;
                
                // Clear and reload data
                appliedLeaveTable.clear();
                appliedLeaveTable.rows.add(response.data);
                appliedLeaveTable.draw();
            },
            error: function(xhr, status, error) {
                console.error('Error fetching leave history:', error);
            }
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-GB');
    }

    function formatDateDMY(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        var day = ('0' + d.getDate()).slice(-2);
        var month = ('0' + (d.getMonth() + 1)).slice(-2);
        var year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Apply Leave button click pe modal open karo
    $('#applyLeaveBtn').on('click', function() {
        $('#applyLeaveModal').modal('show');
        loadLeaveTypes();
        // Reset form
        $('#applyLeaveForm')[0].reset();
        $('#applyLeaveForm').removeData('edit-mode');
    });

    // Edit Leave button click
    $(document).on('click', '.edit-leave', function() {
        var leaveId = $(this).data('leave-id');
        var rowData = appliedLeaveTable.row($(this).closest('tr')).data();
        
        $('#editLeaveModal').modal('show');
        $('#editLeaveForm').data('leave-id', leaveId);
        // Set fields after leave types are loaded
        loadLeaveTypes(function() {
            $('#editLeaveType').val(rowData.leave_type_id);
            $('#editStartDate').val(rowData.start_date);
            $('#editEndDate').val(rowData.end_date);
            $('#editReason').val(rowData.reason);
        });
    });

    // Leave types ko AJAX se load karo
    function loadLeaveTypes(callback) {
        $.ajax({
            url: 'include/api/get-leave-types.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var select = $('#leaveType');
                    var editSelect = $('#editLeaveType');
                    select.empty();
                    editSelect.empty();
                    select.append('<option value="">Select Leave Type</option>');
                    editSelect.append('<option value="">Keep current leave type</option>');
                    response.data.forEach(function(type) {
                        select.append('<option value="'+type.leave_type_id+'">'+type.type_name+'</option>');
                        editSelect.append('<option value="'+type.leave_type_id+'">'+type.type_name+'</option>');
                    });
                    if (callback) callback();
                }
            }
        });
    }

    // Apply Leave form submit
    $('#applyLeaveForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'include/api/leave_history.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#applyLeaveModal').modal('hide');
                    loadLeaveHistory();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Leave applied successfully!'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to apply leave'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Server error!'
                });
            }
        });
    });

    // Edit Leave form submit
    $('#editLeaveForm').on('submit', function(e) {
        e.preventDefault();
        var leaveId = $(this).data('leave-id');
        var formData = new FormData();
        formData.append('leave_id', leaveId);
        formData.append('action', 'update');

        // Only add fields that have been changed or filled
        var leaveType = $('#editLeaveType').val();
        var startDate = $('#editStartDate').val();
        var endDate = $('#editEndDate').val();
        var reason = $('#editReason').val();
        var document = $('#editDocument')[0].files[0];
        
        if (leaveType) formData.append('leave_type_id', leaveType);
        if (startDate) formData.append('start_date', startDate);
        if (endDate) formData.append('end_date', endDate);
        if (reason) formData.append('reason', reason);
        if (document) formData.append('document', document);
        
        $.ajax({
            url: 'include/api/leave_history.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editLeaveModal').modal('hide');
                    loadLeaveHistory();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Leave updated successfully!'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update leave'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Server error!'
                });
            }
        });
    });

    // Table row click for details (ignore clicks on buttons)
    $('#appliedLeaveTable tbody').on('click', 'tr', function(e) {
        // Ignore if click was on a button or inside a button
        if ($(e.target).closest('button').length > 0) return;

        var rowData = appliedLeaveTable.row(this).data();
        if (!rowData) return;

        // Build details HTML with applied leave jaisa UI design
        var html = `
            <div class="leave-details-modal-ui">
                <div class="detail-row"><span class="detail-label">Employee Name:</span><span class="detail-value">${createFullName(rowData.first_name, rowData.middle_name, rowData.last_name) || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Leave Type:</span><span class="detail-value">${rowData.type_name || ''}</span></div>
                <div class="detail-row"><span class="detail-label">From:</span><span class="detail-value">${formatDateDMY(rowData.start_date)}</span></div>
                <div class="detail-row"><span class="detail-label">To:</span><span class="detail-value">${formatDateDMY(rowData.end_date)}</span></div>
                <div class="detail-row"><span class="detail-label">Days:</span><span class="detail-value">${rowData.days || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Status:</span><span class="detail-value">${rowData.status || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Reason:</span><span class="detail-value left-align" style="white-space:pre-line">${rowData.reason || ''}</span></div>
                <div class="detail-row"><span class="detail-label">Admin Comment:</span><span class="detail-value left-align" style="white-space:pre-line">${rowData.admin_comment || ''}</span></div>
            </div>
        `;


        $('#leaveDetailsBody').html(html);
        var modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
        modal.show();
    });

    // File upload validation - sirf PDF aur images allow karo
    $('#document, #editDocument').on('change', function() {
        var file = this.files[0];
        if (file) {
            var fileName = file.name.toLowerCase();
            var allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            var fileExtension = fileName.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExtension)) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Type Not Allowed',
                    text: 'Only PDF and image files (JPG, PNG) are allowed to be uploaded.'
                });
                this.value = '';
                return;
            }
            
            // File size check (5MB limit)
            var maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'File size should be less than 5MB.'
                });
                this.value = '';
                return;
            }
        }
    });
});

function toggleDropdown() {
    var dropdown = document.getElementById('userDropdown');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
    }
}


// Document view in modal
$(document).on('click', '.view-document', function(e) {
    e.preventDefault();
    var docUrl = $(this).data('doc');
    var ext = docUrl.split('.').pop().toLowerCase();
    var html = '';
    if(['jpg','jpeg','png','gif','jfif','webp'].includes(ext)) {
        html = '<img src="'+docUrl+'" alt="Document" style="max-width:90%;max-height:80vh;">';
    } else if(ext === 'pdf') {
        html = '<embed src="'+docUrl+'" type="application/pdf" width="90%" height="650px">';
    } else if(ext === 'txt') {
        Swal.fire({
            icon: 'error',
            title: 'Not Allowed',
            text: 'Text files are not allowed to be viewed or downloaded.'
        });
        return;
    } else if(ext === 'zip') {
        Swal.fire({
            icon: 'error',
            title: 'Not Allowed',
            text: 'Zip files are not allowed to be viewed or downloaded.'
        });
        return;
    } else {
        if($('#documentModal').length > 0) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('documentModal'));
            if(modal) modal.hide();
        }
        Swal.fire({
            icon: 'info',
            title: 'Preview Not Supported',
            html: 'This file type is not supported for preview.<br><a href="'+docUrl+'" target="_blank">Click here to download.</a>',
            showConfirmButton: true
        });
        return;
    }
    // Remove old modal if exists
    $('#documentModal').remove();
    // Add new modal
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