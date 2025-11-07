// Helper function to create full name
function createFullName(firstName, middleName, lastName) {
    return (firstName + ' ' + (middleName || '') + ' ' + (lastName || '')).replace(/\s+/g, ' ').trim();
}

$(document).ready(function() {
    // --- Initial Setup ---
    loadDepartments();
    loadLeaveTypes();
    loadLeaveRequests();

    // --- Polling Setup ---
    let pollingInterval = setInterval(loadLeaveRequests, 5000);
    let isPollingPaused = false;

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(pollingInterval);
        } else if (!isPollingPaused) {
            loadLeaveRequests(); // Load immediately on becoming visible
            pollingInterval = setInterval(loadLeaveRequests, 5000);
        }
    });

    $(document).on('focus', 'input, textarea, select', function() {
        if (!isPollingPaused) {
            clearInterval(pollingInterval);
            isPollingPaused = true;
        }
    }).on('blur', 'input, textarea, select', function() {
        if (isPollingPaused && !document.hidden) {
            isPollingPaused = false;
            pollingInterval = setInterval(loadLeaveRequests, 5000);
        }
    });

    // --- Filter Event Listeners (Setup once) ---
    const searchForm = $('#leaveRequestSearchForm');
    searchForm.on('input', '#employeeIdSearch', filterCards);
    searchForm.on('change', 'select', filterCards);
    searchForm.on('reset', function() {
        setTimeout(filterCards, 0);
    });
    searchForm.on('submit', e => e.preventDefault());

    // Show existing leave types in modal
    $('#addLeaveTypeModal').on('show.bs.modal', function () {
        loadExistingLeaveTypes();
    });

    // Delegate click event for deleting leave types
    $('#existingLeaveTypesList').on('click', '.delete-leave-type-btn', function() {
        const typeName = $(this).data('type-name');
        deleteLeaveType(typeName);
    });

    // --- Main Functions ---
    function loadLeaveRequests() {
        $.ajax({
            url: 'include/api/leave-requests.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const container = $('#leaveRequestsContainer');
                    const currentCards = new Map();
                    container.children('.leave-card').each(function() {
                        currentCards.set($(this).data('leaveid'), $(this));
                    });

                    const newCards = response.data.filter(req => req.status === 'pending');

                    const newCardIds = new Set(newCards.map(c => c.leave_id));

                    // Remove old cards that are not in the new data
                    currentCards.forEach((card, leaveId) => {
                        if (!newCardIds.has(leaveId)) {
                            card.remove();
                        }
                    });
                    
                    if (newCards.length === 0) {
                        container.empty();
                        $('#noDataMessage').show();
                        $('#leaveRequestsListCard').hide();
                    } else {
                         $('#noDataMessage').hide();
                         $('#leaveRequestsListCard').show();
                    }

                    // Add or update cards
                    newCards.forEach(function(req) {
                        const existingCard = container.find(`.leave-card[data-leaveid="${req.leave_id}"]`);
                        
                        let totalDays = 0;
                        if (req.start_date && req.end_date) {
                            totalDays = Math.floor((new Date(req.end_date) - new Date(req.start_date)) / 86400000) + 1;
                        }
                        
                        let statusClass = req.status === 'approved' ? 'status-present' : (req.status === 'rejected' ? 'status-absent' : 'status-late');
                        
                        // Document path ko simple relative path banate hain
                        let docPath = req.document_path;
                        if (docPath && !docPath.startsWith('http')) {
                            // Simple relative path using ../
                            docPath = '../' + (docPath.startsWith('/') ? docPath.substring(1) : docPath);
                        }
                        const viewDocBtn = docPath ? `<button type="button" class="btn btn-sm btn-outline-primary view-document" data-doc="${docPath}" title="View Document"><i class="fas fa-eye"></i></button>` : '';
                        const cardHTML = `
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Emp ID: ${req.emp_id}</h6>
                                    <div style="display:flex;align-items:center;gap:8px;flex-direction:row;">
                                        ${viewDocBtn}
                                        <span class="attendance-status ${statusClass}">${req.status.charAt(0).toUpperCase() + req.status.slice(1)}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h5 class="card-title">${createFullName(req.first_name, req.middle_name, req.last_name)}</h5>
                                    <p class="text-muted mb-1">${req.department || ''} Department</p>
                                </div>
                                <div class="mb-3">
                                    <p class="mb-1"><strong>Leave Type:</strong> ${req.type_name}</p>
                                    <p class="mb-1"><strong>From:</strong> ${req.start_date ? req.start_date.split(' ')[0] : ''}</p>
                                    <p class="mb-1"><strong>To:</strong> ${req.end_date ? req.end_date.split(' ')[0] : ''}</p>
                                    <p class="mb-1"><strong>Total Days:</strong> ${totalDays}</p>
                                    <p class="mb-1"><strong>Reason:</strong> <span style="white-space:pre-line">${truncateWords(req.reason, 10)}</span></p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <button type="button" class="btn btn-success btn-sm accept-btn" data-leaveid="${req.leave_id}" ${req.status !== 'pending' ? 'disabled' : ''}>
                                        <i class="fas fa-check me-1"></i> Accept
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm reject-btn" data-leaveid="${req.leave_id}" ${req.status !== 'pending' ? 'disabled' : ''}>
                                        <i class="fas fa-times me-1"></i> Reject
                                    </button>
                                </div>
                                <textarea class="form-control admin-comment-input" placeholder="Admin comment" rows="2" ${req.status !== 'pending' ? 'readonly' : ''}>${req.admin_comment || ''}</textarea>
                            </div>`;
                        
                        if (existingCard.length > 0) {
                            existingCard.html(cardHTML);
                        } else {
                            const newCard = $(`<div class="col-md-6 col-lg-4 leave-card" data-leaveid="${req.leave_id}" data-id="${req.emp_id}" data-name="${createFullName(req.first_name, req.middle_name, req.last_name)}" data-department="${req.department}" data-leave-type="${req.type_name}" data-reason="${encodeURIComponent(req.reason || '')}"></div>`);
                            newCard.html(`<div class="card h-100">${cardHTML}</div>`);
                            container.append(newCard);
                        }
                    });

                    filterCards();
                }
            },
            complete: function() {
                $('.accept-btn, .reject-btn').off('click');
                $('.accept-btn').on('click', function() {
                    const leaveId = $(this).data('leaveid');
                    const comment = $(this).closest('.card-body').find('.admin-comment-input').val();
                    updateLeaveStatus(leaveId, 'approved', comment);
                });
                $('.reject-btn').on('click', function() {
                    const leaveId = $(this).data('leaveid');
                    const comment = $(this).closest('.card-body').find('.admin-comment-input').val();
                    updateLeaveStatus(leaveId, 'rejected', comment);
                });
                
                // Update badge count after loading leave requests
                if (typeof updateLeaveRequestsBadge === 'function') {
                    updateLeaveRequestsBadge();
                }
            }
        });
    }

    function loadDepartments() {
        $.ajax({
            url: 'include/api/department.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const select = $('#departmentSearch');
                    select.find('option:not(:first)').remove();
                    const departmentNames = new Set(response.data.filter(d => d.status === 'active').map(d => d.dept_name));
                    departmentNames.forEach(name => select.append(`<option value="${name}">${name}</option>`));
                }
            }
        });
    }

    function loadLeaveTypes() {
        $.ajax({
            url: 'include/api/add-leave-type.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const select = $('#leaveTypeSearch');
                    select.find('option:not(:first)').remove();
                    response.data.forEach(type => select.append(`<option value="${type.type_name}">${type.type_name}</option>`));
                }
            }
        });
    }

    function filterCards() {
        const searchTerm = $('#employeeIdSearch').val().toLowerCase();
        const departmentValue = $('#departmentSearch').val();
        const leaveTypeValue = $('#leaveTypeSearch').val();
        let visibleCards = 0;

        $('.leave-card').each(function() {
            const card = $(this);
            const id = (card.data('id') || '').toString().toLowerCase();
            const name = (card.data('name') || '').toLowerCase();
            const department = card.data('department');
            const leaveType = card.data('leave-type');

            const matchesSearch = id.includes(searchTerm) || name.includes(searchTerm);
            const matchesDepartment = !departmentValue || department === departmentValue;
            const matchesLeaveType = !leaveTypeValue || leaveType === leaveTypeValue;

            if (matchesSearch && matchesDepartment && matchesLeaveType) {
                card.show();
                visibleCards++;
            } else {
                card.hide();
            }
        });

        if (visibleCards === 0) {
            $('#noDataMessage').show();
            $('#leaveRequestsListCard').hide();
        } else {
            $('#noDataMessage').hide();
            $('#leaveRequestsListCard').show();
        }
    }

    function updateLeaveStatus(leaveId, status, comment) {
        $.ajax({
            url: 'include/api/leave-requests.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ leave_id: leaveId, status: status, admin_comment: comment }),
            success: function(response) {
                if (response.success) {
                    showLeaveToast(`Leave ${status}!`, status === 'approved');
                    loadLeaveRequests();
                    // Update badge count after status change
                    if (typeof updateLeaveRequestsBadge === 'function') {
                        updateLeaveRequestsBadge();
                    }
                } else {
                    showLeaveToast('Status update failed!', false);
                }
            },
            error: () => showLeaveToast('Server error!', false)
        });
    }

    function showLeaveToast(msg, success) {
        const toastEl = $('#leaveToast');
        $('#leaveToastMsg').text(msg);
        toastEl.removeClass('text-bg-success text-bg-danger').addClass(success ? 'text-bg-success' : 'text-bg-danger');
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    }

    $('#saveLeaveTypeBtn').on('click', function() {
        const typeName = $('#leaveTypeName').val().trim();
        if (!typeName) {
            showLeaveToast('Leave type name required!', false);
            return;
        }
        $.ajax({
            url: 'include/api/add-leave-type.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ type_name: typeName }),
            success: function(response) {
                if (response.success) {
                    $('#addLeaveTypeModal').modal('hide');
                    $('#addLeaveTypeForm')[0].reset();
                    showLeaveToast('Leave type added!', true);
                    loadLeaveTypes();
                } else {
                    showLeaveToast(response.error || 'Failed to add leave type!', false);
                }
            },
            error: () => showLeaveToast('Server error!', false)
        });
    });

    function loadExistingLeaveTypes() {
        $.ajax({
            url: 'include/api/add-leave-type.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const list = $('#existingLeaveTypesList');
                list.empty();
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(type => {
                        html += `
                            <div class="dept-item">
                                <div class="dept-info">
                                    <div class="dept-icon">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </div>
                                    <span class="dept-name" style="text-transform: capitalize;">${type.type_name}</span>
                                </div>
                                <button type="button" class="btn-delete-dept delete-leave-type-btn" data-type-name="${type.type_name}" title="Delete ${type.type_name}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });
                    list.html(html);
                } else {
                    list.html('<div class="text-center text-muted py-3">No leave types found.</div>');
                }
            },
            error: function() {
                $('#existingLeaveTypesList').html('<div class="text-center text-danger py-3">Error loading types.</div>');
            }
        });
    }

    function deleteLeaveType(typeName) {
        $.ajax({
            url: 'include/api/add-leave-type.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ type_name: typeName }),
            success: function(response) {
                if (response.success) {
                    showLeaveToast('Leave type deleted successfully!', true);
                    loadExistingLeaveTypes(); // Refresh the list in the modal
                    loadLeaveTypes(); // Refresh the main filter dropdown
                } else {
                    showLeaveToast(response.error || 'Failed to delete leave type.', false);
                }
            },
            error: function() {
                showLeaveToast('Server error while deleting leave type.', false);
            }
        });
    }

    // Document view in modal
    $(document).on('click', '.view-document', function(e) {
        e.preventDefault();
        var docUrl = $(this).data('doc');
        var ext = docUrl.split('.').pop().toLowerCase();
        var html = '';
        // Modal HTML dynamically add karo agar nahi hai
        if($('#documentModal').length === 0) {
            $('body').append(`
            <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Document View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body" id="documentModalBody" style="text-align:center;"></div>
                </div>
              </div>
            </div>`);
        }
        if(['jpg','jpeg','png','gif','jfif','webp'].includes(ext)) {
            html = '<img src="'+docUrl+'" alt="Document" style="max-width:90%;max-height:80vh;">';
        } else if(ext === 'pdf') {
            html = '<embed src="'+docUrl+'" type="application/pdf" width="90%" height="650px">';
        } else {
            if($('#documentModal').length > 0) $('#documentModal').modal('hide');
            Swal.fire({
                icon: 'info',
                title: 'Preview Not Supported',
                html: 'This file type is not supported for preview.<br><a href="'+docUrl+'" target="_blank">Click here to download.</a>',
                showConfirmButton: true
            });
            return;
        }
        $('#documentModalBody').html(html);
        $('#documentModal').modal('show');
    });

    // Accept/Reject button pr click se card click na ho
    $(document).on('click', '.accept-btn, .reject-btn, .view-document, .admin-comment-input', function(e) {
        e.stopPropagation();
    });

    // Leave card pr click event
    $(document).on('click', '.leave-card', function(e) {
        // Agar user ne button ya textarea pr click kiya toh kuch na karo
        if ($(e.target).closest('.accept-btn, .reject-btn, .view-document, .admin-comment-input').length > 0) return;
        var card = $(this);
        var empId = card.data('id');
        var empName = card.data('name');
        var department = card.data('department');
        var leaveType = card.data('leave-type');
        var leaveId = card.data('leaveid');
        var reason = decodeURIComponent(card.data('reason') || '');
        // Card ke andar se details nikal lo
        var body = card.find('.card-body');
        var empPosition = body.find('.text-muted').text();
        var from = body.find('p:contains("From:")').text().replace('From:', '').trim();
        var to = body.find('p:contains("To:")').text().replace('To:', '').trim();
        var totalDays = body.find('p:contains("Total Days:")').text().replace('Total Days:', '').trim();
        var status = card.find('.attendance-status').text();
        var adminComment = body.find('.admin-comment-input').val();
        // Modal HTML agar nahi hai toh add karo
        if($('#leaveRequestDetailModal').length === 0) {
            $('body').append(`
            <div class="modal fade" id="leaveRequestDetailModal" tabindex="-1" aria-labelledby="leaveRequestDetailModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow" style="border-radius:12px; font-size:15px; font-family:inherit;">
                  <div class="modal-header" style="background:#17b6a3;border-top-left-radius:12px;border-top-right-radius:12px; padding: 0.75rem 1.25rem; min-height:48px;">
                    <h5 class="modal-title text-white fw-bold" id="leaveRequestDetailModalLabel" style="margin:0; font-size:1.25rem;">Leave Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="width:1.1rem;height:1.1rem;"></button>
                  </div>
                  <div class="modal-body" id="leaveRequestDetailModalBody" style="padding:1.25rem 1.5rem 1.25rem 1.5rem; border-radius:0 0 12px 12px;"></div>
                </div>
              </div>
            </div>`);
        }
        // Modal body content
        var html = `<div class="leave-details-modal-ui">
            <div class="detail-row"><span class="detail-label">Employee Name:</span><span class="detail-value">${empName || ''}</span></div>
            <div class="detail-row"><span class="detail-label">Department:</span><span class="detail-value">${department || ''}</span></div>
            <div class="detail-row"><span class="detail-label">Leave Type:</span><span class="detail-value">${leaveType || ''}</span></div>
            <div class="detail-row"><span class="detail-label">From:</span><span class="detail-value">${formatDateDMY(from)}</span></div>
            <div class="detail-row"><span class="detail-label">To:</span><span class="detail-value">${formatDateDMY(to)}</span></div>
            <div class="detail-row"><span class="detail-label">Days:</span><span class="detail-value">${totalDays || ''}</span></div>
            <div class="detail-row"><span class="detail-label">Status:</span><span class="detail-value">${status || ''}</span></div>
            <div class="detail-row"><span class="detail-label">Reason:</span><span class="detail-value left-align" style="white-space:pre-line">${reason || ''}</span></div>
        </div>`;
        $('#leaveRequestDetailModalBody').html(html);
        var modal = new bootstrap.Modal(document.getElementById('leaveRequestDetailModal'));
        modal.show();



    });
});

// Truncate words utility function
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