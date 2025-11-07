function handleLogout(event) {
    event.preventDefault();

    fetch('../api/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = '../login.php';
            } else {
                showProfileToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showProfileToast('An error occurred during logout', 'error');
        });
}

// Toast function for profile update
function showProfileToast(msg, type = 'success') {
    if (window.toastr) {
        toastr[type](msg);
    } else {
        // Fallback: simple toast
        var toast = document.createElement('div');
        toast.textContent = msg;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.background = (type === 'success') ? '#28a745' : '#dc3545';
        toast.style.color = 'white';
        toast.style.padding = '10px 20px';
        toast.style.borderRadius = '5px';
        toast.style.zIndex = 9999;
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.remove();
        }, 2000);
    }
}

document.getElementById('saveProfileChanges').addEventListener('click', function() {
    // Phone validation
    var phone = document.getElementById('editPhone').value;
    if (!/^[0-9]{11}$/.test(phone)) {
        showProfileToast('Phone number must be exactly 11 digits and contain only numbers', 'error');
        return;
    }

    var formData = new FormData();
    formData.append('first_name', document.getElementById('editFirstName').value);
    formData.append('middle_name', document.getElementById('editMiddleName').value);
    formData.append('last_name', document.getElementById('editLastName').value);
    formData.append('phone', phone);
    formData.append('address', document.getElementById('editAddress').value);

    var fileInput = document.getElementById('profilePicture');
    if (fileInput.files && fileInput.files[0]) {
        formData.append('profile_img', fileInput.files[0]);
    }

    fetch('include/api/update-profile.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showProfileToast('Profile updated successfully!', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showProfileToast(res.message || 'Update failed', 'error');
            }
        })
        .catch(err => {
            showProfileToast('Error: ' + err, 'error');
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

function handleLogout(event) {
    event.preventDefault();

    fetch('/hrm-dashboard/api/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = '../login.php';
            } else {
                showProfileToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showProfileToast('An error occurred during logout', 'error');
        });
}

document.getElementById('saveProfileChanges').addEventListener('click', function() {
    var formData = new FormData();
    formData.append('first_name', document.getElementById('editFirstName').value);
    formData.append('middle_name', document.getElementById('editMiddleName').value);
    formData.append('last_name', document.getElementById('editLastName').value);
    formData.append('phone', document.getElementById('editPhone').value);
    formData.append('address', document.getElementById('editAddress').value);

    var fileInput = document.getElementById('profilePicture');
    if (fileInput.files && fileInput.files[0]) {
        formData.append('profile_img', fileInput.files[0]);
    }

    fetch('include/api/update-profile.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showProfileToast('Profile updated successfully!', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showProfileToast(res.message || 'Update failed', 'error');
            }
        })
        .catch(err => {
            showProfileToast('Error: ' + err, 'error');
        });
});

function toggleProfilePanel() {
    var panel = document.getElementById('profilePanel');
    panel.style.display = 'block';
    panel.classList.toggle('show');
    if (!panel.classList.contains('show')) {
        setTimeout(function() {
            panel.style.display = 'none';
        }, 300);
    }
}

// Calendar variables
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

// Calendar functions
function toggleCalendar() {
    const calendarPopup = document.getElementById('calendarPopup');
    if (calendarPopup.style.display === 'block') {
        calendarPopup.style.display = 'none';
    } else {
        // Update calendar before showing
        updateCalendar();
        calendarPopup.style.display = 'block';
    }
}

function updateCalendar() {
    // Update month and year display
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    document.getElementById('currentMonth').textContent = monthNames[currentMonth] + " " + currentYear;

    // Get current date for highlighting today
    const today = new Date();
    const todayDate = today.getDate();
    const todayMonth = today.getMonth();
    const todayYear = today.getFullYear();

    // Clear existing calendar days
    const calendarGrid = document.getElementById('calendarGrid');
    calendarGrid.innerHTML = '';

    // Create day headers (Sun, Mon, etc)
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

    // Get first day of month and number of days in month
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    // Add empty cells for days before the 1st of the month
    for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'day-cell empty';
        emptyCell.style.padding = '8px';
        calendarGrid.appendChild(emptyCell);
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'day-cell';
        dayCell.textContent = day;
        dayCell.style.padding = '8px';
        dayCell.style.textAlign = 'center';
        dayCell.style.cursor = 'pointer';

        // Highlight today
        if (day === todayDate && currentMonth === todayMonth && currentYear === todayYear) {
            dayCell.style.backgroundColor = 'var(--button-color, #00bfa5)';
            dayCell.style.color = 'white';
            dayCell.style.borderRadius = '50%';
        }

        // Add hover effect
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

        // Add click event
        dayCell.addEventListener('click', function() {
            const selectedDate = new Date(currentYear, currentMonth, day);
            const formattedDate = day + ' ' + monthNames[currentMonth] + ' ' + currentYear;
            showProfileToast('Selected date: ' + formattedDate, 'info');
            // You can do something with the selected date here
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

// Close calendar when clicking outside
document.addEventListener('click', function(event) {
    const calendarPopup = document.getElementById('calendarPopup');
    const calendarIcon = document.querySelector('.calendar-icon');

    if (calendarPopup && calendarPopup.style.display === 'block' &&
        !calendarPopup.contains(event.target) &&
        !calendarIcon.contains(event.target)) {
        calendarPopup.style.display = 'none';
    }
});

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Calendar toggle event
    const calendarToggle = document.getElementById('calendarToggle');
    if (calendarToggle) {
        calendarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleCalendar();
        });
    }

    // Initialize calendar if calendar elements exist
    if (document.getElementById('calendarGrid')) {
        updateCalendar();

        // Add support for calendar navigation buttons
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

    var dropArea = document.getElementById('profileImageDrop');
    var fileInput = document.getElementById('profilePicture');
    var previewImg = document.getElementById('profilePreview');

    // Click to open file dialog
    dropArea.addEventListener('click', function(e) {
        if (e.target.classList.contains('file-input')) return;
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (fileInput.files && fileInput.files[0]) {
            var file = fileInput.files[0];
            var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                showProfileToast('Only JPG, JPEG, PNG images allowed!', 'error');
                fileInput.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Drag & drop events
    dropArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropArea.classList.add('dragover');
    });
    dropArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropArea.classList.remove('dragover');
    });
    dropArea.addEventListener('drop', function(e) {
        e.preventDefault();
        dropArea.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            var file = e.dataTransfer.files[0];
            var allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                showProfileToast('Only JPG, JPEG, PNG images allowed!', 'error');
                return;
            }
            fileInput.files = e.dataTransfer.files;
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    function uploadProfileImage(file) {
        var formData = new FormData();
        formData.append('first_name', document.getElementById('editFirstName').value);
    formData.append('middle_name', document.getElementById('editMiddleName').value);
    formData.append('last_name', document.getElementById('editLastName').value);
        formData.append('phone', document.getElementById('editPhone').value);
        formData.append('address', document.getElementById('editAddress').value);
        formData.append('profile_img', file);

        fetch('include/api/update-profile.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showProfileToast('Profile updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showProfileToast(res.message || 'Image upload failed', 'error');
                }
            })
            .catch(err => {
                showProfileToast('Error: ' + err, 'error');
            });
    }
});

// Make calendar functions globally available
window.toggleCalendar = toggleCalendar;
window.prevMonth = prevMonth;
window.nextMonth = nextMonth;

document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.querySelector('.sidebar-toggle');
    var sidebar = document.getElementById('mainSidebar');
    var mainContent = document.querySelector('.main-content');
    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        });
    }
});

let isToggledOpen = false; // Track if sidebar was opened by button
const sidebar = document.getElementById('mainSidebar');
const mainContent = document.querySelector('.main-content');
const sidebarToggleBtn = document.querySelector('.sidebar-toggle i');

// Create overlay div for mobile
const overlay = document.createElement('div');
overlay.className = 'sidebar-overlay';
document.body.appendChild(overlay);

// Sidebar Toggle Functionality
function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarToggleBtn = document.querySelector('.sidebar-toggle i');

    if (!sidebar || !mainContent || !sidebarToggleBtn) {
        console.error('Required elements not found');
        return;
    }

    // Toggle collapsed state
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');

    // Toggle icon
    if (sidebar.classList.contains('collapsed')) {
        sidebarToggleBtn.classList.remove('fa-times');
        sidebarToggleBtn.classList.add('fa-bars');
    } else {
        sidebarToggleBtn.classList.remove('fa-bars');
        sidebarToggleBtn.classList.add('fa-times');
    }
}

// Initialize sidebar functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');

    if (sidebar && mainContent) {
        // Add hover functionality
        sidebar.addEventListener('mouseenter', function() {
            if (this.classList.contains('collapsed')) {
                this.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
            }
        });

        sidebar.addEventListener('mouseleave', function() {
            if (!this.classList.contains('collapsed')) {
                this.classList.add('collapsed');
                mainContent.classList.add('collapsed');
            }
        });
    }
});

// Initialize sidebar functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Create overlay if it doesn't exist
    if (!document.querySelector('.sidebar-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    // Add click event to overlay to close sidebar on mobile
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                toggleSidebar();
            }
        });
    }

    // Add window resize handler
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        if (window.innerWidth >= 768 && sidebar && overlay) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
});

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
    const sidebar = document.getElementById('mainSidebar');
    if (sidebar) {
        sidebar.addEventListener('mouseenter', function() {
            if (this.classList.contains('collapsed')) {
                this.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
            }
        });

        sidebar.addEventListener('mouseleave', function() {
            if (!isToggledOpen && !this.classList.contains('collapsed')) {
                this.classList.add('collapsed');
                mainContent.classList.add('collapsed');
            }
        });
    }
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