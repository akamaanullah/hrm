    let isToggledOpen = false; // Track if sidebar was opened by button
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarToggleBtn = document.querySelector('.sidebar-toggle i');

    // Create overlay div for mobile
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    function toggleSidebar() {
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            sidebarToggleBtn.classList.toggle('fa-times');
            sidebarToggleBtn.classList.toggle('fa-bars');
        } else {
            if (sidebar.classList.contains('collapsed')) {
                // Opening sidebar
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
                sidebarToggleBtn.classList.remove('fa-bars');
                sidebarToggleBtn.classList.add('fa-times');
                isToggledOpen = true;
            } else {
                // Closing sidebar
                sidebar.classList.add('collapsed');
                mainContent.classList.add('collapsed');
                sidebarToggleBtn.classList.remove('fa-times');
                sidebarToggleBtn.classList.add('fa-bars');
                isToggledOpen = false;
            }
        }

        // Check badge state when sidebar is toggled
        setTimeout(function() {
            if (typeof window.updateMessagesBadge === 'function') {
                window.updateMessagesBadge();
            }
        }, 100);
    }

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', () => {
        if (sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            sidebarToggleBtn.classList.remove('fa-times');
            sidebarToggleBtn.classList.add('fa-bars');
        }
    });

    // Handle window resize
    let windowWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        const newWindowWidth = window.innerWidth;
        const breakpoint = 768;

        // Check if we're crossing the mobile breakpoint
        if ((windowWidth < breakpoint && newWindowWidth >= breakpoint) ||
            (windowWidth >= breakpoint && newWindowWidth < breakpoint)) {
            // Reset sidebar state
            sidebar.classList.remove('show', 'collapsed');
            mainContent.classList.remove('collapsed');
            overlay.classList.remove('show');
            sidebarToggleBtn.classList.remove('fa-times');
            sidebarToggleBtn.classList.add('fa-bars');
            isToggledOpen = false;
        }

        windowWidth = newWindowWidth;
    });

    // Add hover functionality for desktop only
    if (window.innerWidth >= 768) {
        sidebar.addEventListener('mouseenter', function() {
            if (this.classList.contains('collapsed')) {
                this.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
                // Update badge when sidebar expands on hover
                setTimeout(function() {
                    if (typeof window.updateMessagesBadge === 'function') {
                        window.updateMessagesBadge();
                    }
                }, 100);
            }
        });

        sidebar.addEventListener('mouseleave', function() {
            if (!isToggledOpen && !this.classList.contains('collapsed')) {
                this.classList.add('collapsed');
                mainContent.classList.add('collapsed');
            }
        });
    }

    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');

        document.addEventListener('click', function(event) {
            const userProfile = event.target.closest('.user-profile');
            const dropdown = document.getElementById('userDropdown');

            if (!userProfile && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    }

    // attendance table

    function deleteAttendance(id) {
        if (confirm('Are you sure you want to delete this attendance record?')) {
            // Implement delete functionality
            alert('Deleting attendance record ' + id);
        }
    }

    function saveAttendance() {
        // Implement save functionality
        alert('Saving attendance record...');
        $('#addAttendanceModal').modal('hide');
    }

    // Sidebar functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle sidebar state change
        function handleSidebarState() {
            const sidebar = document.getElementById('mainSidebar');
            if (sidebar.classList.contains('collapsed')) {
                // Close all open submenus when sidebar is collapsed
                const openSubmenus = sidebar.querySelectorAll('.submenu.show');
                openSubmenus.forEach(submenu => {
                    submenu.classList.remove('show');
                    const trigger = document.querySelector(`[aria-controls="${submenu.id}"]`);
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        }

        // Watch for sidebar class changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    handleSidebarState();
                }
            });
        });

        const sidebar = document.getElementById('mainSidebar');
        if (sidebar) {
            observer.observe(sidebar, {
                attributes: true
            });
        }
    });



    // Leave Management Functions
    function viewLeave(id) {
        // Get row data
        const row = document.querySelector(`button[onclick="viewLeave(${id})"]`).closest('tr');
        const cells = row.getElementsByTagName('td');

        // Populate modal
        document.getElementById('viewEmployee').textContent = `${cells[0].textContent} - ${cells[1].textContent}`;
        document.getElementById('viewDepartment').textContent = cells[2].textContent;
        document.getElementById('viewLeaveType').textContent = cells[3].textContent;
        document.getElementById('viewDateRange').textContent = `${cells[4].textContent} to ${cells[5].textContent}`;
        document.getElementById('viewTotalDays').textContent = cells[6].textContent;
        document.getElementById('viewReason').textContent = cells[7].textContent;
        document.getElementById('viewAppliedDate').textContent = cells[8].textContent;
        document.getElementById('viewStatus').textContent = cells[9].querySelector('.badge').textContent;

        // Show/hide action buttons based on status
        const status = cells[9].querySelector('.badge').textContent;
        const modalActions = document.getElementById('modalActions');
        const remarkSection = document.getElementById('remarkSection');

        if (status === 'Pending') {
            modalActions.querySelector('.btn-success').style.display = 'block';
            modalActions.querySelector('.btn-danger').style.display = 'block';
            remarkSection.style.display = 'block';
        } else {
            modalActions.querySelector('.btn-success').style.display = 'none';
            modalActions.querySelector('.btn-danger').style.display = 'none';
            remarkSection.style.display = 'none';
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewLeaveModal'));
        modal.show();
    }
    // Leave Request Functions
    function viewLeaveRequest(id) {
        // Get leave request data
        const leaveRequest = getLeaveRequestData(id);

        // Populate modal with data
        document.getElementById('viewRequestEmployee').textContent = leaveRequest.employeeName;
        document.getElementById('viewRequestDepartment').textContent = leaveRequest.department;
        document.getElementById('viewRequestLeaveType').textContent = leaveRequest.leaveType;
        document.getElementById('viewRequestDateRange').textContent = `${leaveRequest.fromDate} to ${leaveRequest.toDate}`;
        document.getElementById('viewRequestTotalDays').textContent = leaveRequest.days;
        document.getElementById('viewRequestReason').textContent = leaveRequest.reason;
        document.getElementById('viewRequestAppliedDate').textContent = leaveRequest.appliedOn;

        // Clear previous remarks
        document.getElementById('requestAdminRemarks').value = '';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewLeaveRequestModal'));
        modal.show();
    }

    function approveLeaveRequest(id) {
        if (confirm('Are you sure you want to approve this leave request?')) {
            // In a real application, this would send an API request to approve the leave
            console.log(`Leave request ${id} approved`);

            // Update UI
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }

            // Show success message
            showNotification('Leave request approved successfully', 'success');
        }
    }

    function rejectLeaveRequest(id) {
        if (confirm('Are you sure you want to reject this leave request?')) {
            // In a real application, this would send an API request to reject the leave
            console.log(`Leave request ${id} rejected`);

            // Update UI
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }

            // Show success message
            showNotification('Leave request rejected successfully', 'danger');
        }
    }

    // Notification function
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.textContent = message;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Call this function when the page loads
    // document.addEventListener('DOMContentLoaded', function () {
    //     if (document.getElementById('leaveRequestsContainer')) {
    //         displayLeaveRequests();
    //     }
    // });




    // Add this script at the end of the file
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('editPayrollModal');
        const viewModal = document.getElementById('viewPayslipModal');

        if (editModal) {
            editModal.addEventListener('shown.bs.modal', function() {
                const firstInput = this.querySelector('input:not([readonly])');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        }

        if (viewModal) {
            viewModal.addEventListener('shown.bs.modal', function() {
                const printButton = this.querySelector('.btn-primary');
                if (printButton) {
                    printButton.focus();
                }
            });
        }
    });


    // Multi-step Form Handler
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.step-form');
        if (!form) return;

        const steps = form.querySelectorAll('.form-step');
        const progressBar = form.querySelector('.progress-bar');
        const stepIndicators = form.querySelectorAll('.step-indicator');
        const nextButtons = form.querySelectorAll('.next-step');
        const prevButtons = form.querySelectorAll('.prev-step');

        let currentStep = 0;

        function updateProgress() {
            const progress = ((currentStep) / (steps.length - 1)) * 100;
            if (progressBar) {
                progressBar.style.width = `${progress}%`;
            }

            // Update step indicators
            stepIndicators.forEach((indicator, index) => {
                if (index < currentStep) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                } else if (index === currentStep) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            });
        }

        function showStep(stepIndex) {
            steps.forEach((step, index) => {
                step.classList.toggle('active', index === stepIndex);
            });
            currentStep = stepIndex;
            updateProgress();
        }

        nextButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (currentStep < steps.length - 1) {
                    // Validate current step before proceeding
                    if (validateCurrentStep(currentStep)) {
                        showStep(currentStep + 1);
                    }
                }
            });
        });

        // Validation function for each step
        function validateCurrentStep(stepIndex) {
            const currentStepElement = steps[stepIndex];
            const requiredFields = currentStepElement.querySelectorAll('input[required], select[required]');

            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    // Show toast message
                    if (typeof showEmployeeToast === 'function') {
                        showEmployeeToast(`${field.previousElementSibling?.textContent || field.name || 'This field'} is required!`, 'error');
                    } else {
                        // Fallback alert if toast function not available
                        alert(`${field.previousElementSibling?.textContent || field.name || 'This field'} is required!`);
                    }
                    field.focus();
                    return false;
                }
            }
            return true;
        }

        prevButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (currentStep > 0) {
                    showStep(currentStep - 1);
                }
            });
        });

        // Initialize progress
        updateProgress();
    });

    // Calendar variables
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    // Calendar functions (user style)
    function toggleCalendar() {
        const calendarPopup = document.getElementById('calendarPopup');
        if (calendarPopup.style.display === 'block') {
            calendarPopup.style.display = 'none';
        } else {
            updateCalendar();
            calendarPopup.style.display = 'block';
        }
    }

    function updateCalendar() {
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        document.getElementById('currentMonth').textContent = monthNames[currentMonth] + " " + currentYear;
        const today = new Date();
        const todayDate = today.getDate();
        const todayMonth = today.getMonth();
        const todayYear = today.getFullYear();
        const calendarGrid = document.getElementById('calendarGrid');
        calendarGrid.innerHTML = '';
        const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        daysOfWeek.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            dayHeader.textContent = day;
            dayHeader.style.textAlign = 'center';
            dayHeader.style.fontWeight = 'bold';
            dayHeader.style.padding = '5px';
            calendarGrid.appendChild(dayHeader);
        });
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'day-cell empty';
            emptyCell.style.padding = '8px';
            calendarGrid.appendChild(emptyCell);
        }
        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'day-cell';
            dayCell.textContent = day;
            dayCell.style.padding = '8px';
            dayCell.style.textAlign = 'center';
            dayCell.style.cursor = 'pointer';
            if (day === todayDate && currentMonth === todayMonth && currentYear === todayYear) {
                dayCell.style.backgroundColor = 'var(--button-color, #00bfa5)';
                dayCell.style.color = 'white';
                dayCell.style.borderRadius = '50%';
            }
            dayCell.addEventListener('mouseover', function() {
                if (!(day === todayDate && currentMonth === todayMonth && currentYear === todayYear)) {
                    this.style.backgroundColor = '#f0f0f0';
                }
            });
            dayCell.addEventListener('mouseout', function() {
                if (!(day === todayDate && currentMonth === todayMonth && currentYear === todayYear)) {
                    this.style.backgroundColor = '';
                }
            });
            dayCell.addEventListener('click', function() {
                const selectedDate = new Date(currentYear, currentMonth, day);
                const formattedDate = day + ' ' + monthNames[currentMonth] + ' ' + currentYear;
                // Optionally show toast or do something
            });
            calendarGrid.appendChild(dayCell);
        }
    }

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar();
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Calendar toggle event
        const calendarIcon = document.querySelector('.calendar-icon');
        if (calendarIcon) {
            calendarIcon.addEventListener('click', function(e) {
                e.preventDefault();
                toggleCalendar();
            });
        }
        if (document.getElementById('calendarGrid')) {
            updateCalendar();
            const prevMonthBtn = document.getElementById('prevMonthBtn');
            const nextMonthBtn = document.getElementById('nextMonthBtn');
            if (prevMonthBtn) {
                prevMonthBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    prevMonth();
                });
            }
            if (nextMonthBtn) {
                nextMonthBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    nextMonth();
                });
            }
        }
    });

    document.addEventListener('click', function(event) {
        const calendarPopup = document.getElementById('calendarPopup');
        const calendarIcon = document.querySelector('.calendar-icon');
        if (calendarPopup && calendarPopup.style.display === 'block' &&
            !calendarPopup.contains(event.target) &&
            !calendarIcon.contains(event.target)) {
            calendarPopup.style.display = 'none';
        }
    });