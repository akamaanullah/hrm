<?php include "session_check.php"; ?>
<?php
require_once dirname(__DIR__) . '/config.php';
$emp_id = $_SESSION['emp_id'] ?? null;
$user = null;
if ($emp_id) {
    $sql = "SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.dept_id WHERE e.emp_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id]);
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch();
    }
}
include "header.php" ?>
<!-- Top Bar -->
<div class="topbar">
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="#" class="brand-logo">
            <img src="../assets/images/LOGO.png" alt="HRM Logo">
            <span>HRM</span>
        </a>
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <!-- Center Date Time Display -->
    <div class="topbar-center">
        <div class="datetime-display">
            <div class="date-info">
                <i class="fas fa-calendar-day"></i>
                <span id="currentDate">Loading...</span>
            </div>
            <div class="time-info">
                <i class="fas fa-clock"></i>
                <span id="currentTime">Loading...</span>
            </div>
        </div>
    </div>
    
    <div class="topbar-right">
        <div class="user-profile" onclick="toggleDropdown()">
            <div class="user-avatar">
                <img src="../<?php echo !empty($user['profile_img']) ? $user['profile_img'] : 'assets/images/default-avatar.jpg'; ?>" alt="User Avatar">
            </div>
            <span class="user-name"><?php echo !empty($user['first_name']) ? htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) : '-'; ?></span>
            <i class="fas fa-chevron-down"></i>
            <div class="dropdown-menu" id="userDropdown">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user-tie"></i>
                    Profile
                </a>
                <a href="../logout.php" class="dropdown-item">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Profile Panel -->
<div class="profile-panel" id="profilePanel">
    <div class="profile-header">
        <button class="profile-close" onclick="toggleProfilePanel()">
            <i class="fas fa-times"></i>
        </button>
        <div class="profile-cover">
            <img src="../assets/images/LOGO.png" alt="Profile Cover">
            <div class="profile-avatar">
                <img src="../<?php echo !empty($user['profile_img']) ? $user['profile_img'] : 'assets/images/default-avatar.jpg'; ?>" alt="User Avatar">
            </div>
            <div class="profile-info">
                <h3><?php echo !empty($user['first_name']) ? htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) : '-'; ?></h3>
                <button class="btn btn-edit-profile mt-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-user-edit me-2"></i>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>
    <div class="profile-content">
        <div class="profile-section">
            <h4>Personal Information</h4>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <span><?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : '-'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-phone"></i>
                <span><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '-'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php
                        if (!empty($user['address'])) {
                            $words = explode(' ', $user['address']);
                            $limited = implode(' ', array_slice($words, 0, 4));
                            echo htmlspecialchars($limited . (count($words) > 4 ? '...' : ''));
                        } else {
                            echo '-';
                        }
                        ?></span>
            </div>
        </div>
        <div class="profile-section">
            <h4>Work Information</h4>
            <div class="info-item">
                <i class="fas fa-id-card"></i>
                <span>Emp Id: <?php echo !empty($user['emp_id']) ? htmlspecialchars($user['emp_id']) : '-'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-user-tie"></i>
                <span>Job Title: <?php echo !empty($user['position']) ? htmlspecialchars($user['position']) : '-'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-building"></i>
                <span>Department: <?php echo !empty($user['dept_name']) ? htmlspecialchars($user['dept_name']) : '-'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Joined: <?php echo !empty($user['joining_date']) ? htmlspecialchars(date('M d, Y', strtotime($user['joining_date']))) : '-'; ?></span>
            </div>
        </div>
    </div>
</div>
<!-- Edit Profile Modal -->
<div class="modal fade profile-edit-modal" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="row">
                        <!-- Profile Images -->
                        <div class="col-12 form-section">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">Profile Picture</label>
                                    <div class="profile-image-upload" id="profileImageDrop">
                                        <img src="../<?php echo !empty($user['profile_img']) ? $user['profile_img'] : 'assets/images/default-avatar.jpg'; ?>" class="preview-image" alt="Profile Picture" id="profilePreview">
                                        <input type="file" class="file-input" id="profilePicture" accept="image/*"
                                            hidden>
                                        <div class="upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Drag & Drop or Click to Upload</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Personal Information -->
                        <div class="col-12 form-section">
                            <h6 class="section-header">Personal Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editFirstName" value="<?php echo !empty($user['first_name']) ? htmlspecialchars($user['first_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="editMiddleName" value="<?php echo !empty($user['middle_name']) ? htmlspecialchars($user['middle_name']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editLastName" value="<?php echo !empty($user['last_name']) ? htmlspecialchars($user['last_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="editPhone" value="<?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" required maxlength="11">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" id="editAddress" value="<?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProfileChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Calendar Popup (User style) -->
<div class="calendar-popup" id="calendarPopup" style="display: none; position: absolute; top: 60px; right: 80px; z-index: 1000; background: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); padding: 15px; width: 300px;">
    <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <button class="btn btn-sm" id="prevMonthBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h5 id="currentMonth" style="margin: 0;">March 2024</h5>
        <button class="btn btn-sm" id="nextMonthBtn">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <div class="calendar-body">
        <div class="calendar-grid" id="calendarGrid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
            <!-- Days will be populated by JavaScript -->
        </div>
    </div>
</div>
<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="profileToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="profileToastMsg">
                Profile updated successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
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
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during logout');
            });
    }
    document.getElementById('saveProfileChanges').addEventListener('click', function() {
        const phone = document.getElementById('editPhone').value.trim();
        const phoneRegex = /^0\d{10,11}$/;
        if (!phoneRegex.test(phone)) {
            showProfileToast('Please enter a valid phone number (11-12 digits starting with 0)', false);
            document.getElementById('editPhone').focus();
            return;
        }
        const data = {
            first_name: document.getElementById('editFirstName').value,
            middle_name: document.getElementById('editMiddleName').value,
            last_name: document.getElementById('editLastName').value,
            phone: phone,
            address: document.getElementById('editAddress').value
        };
        fetch('include/api/update-profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showProfileToast('Profile updated successfully!', true);
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showProfileToast(res.message || 'Update failed', false);
                }
            })
            .catch(err => {
                showProfileToast('Error: ' + err, false);
            });
    });

    function showProfileToast(msg, success) {
        var toastEl = document.getElementById('profileToast');
        var toastMsg = document.getElementById('profileToastMsg');
        toastMsg.textContent = msg;
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
    document.getElementById('profilePicture').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var formData = new FormData();
        formData.append('profile_img', file);
        fetch('include/api/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // Nayi image preview update karo
                    document.getElementById('profilePreview').src = '../' + res.profile_img;
                } else {
                    showProfileToast(res.message || 'Image update failed', false);
                }
            });
    });
    document.getElementById('editPhone').addEventListener('input', function(e) {
        // Sirf digits allow karo aur 11 se zyada na hone do
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
    // Profile Panel Toggle
    function toggleProfilePanel() {
        const profilePanel = document.getElementById('profilePanel');
        profilePanel.classList.toggle('show');

        // Close dropdown when opening profile panel
        const dropdown = document.getElementById('userDropdown');
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
    // Close profile panel when clicking outside
    document.addEventListener('click', function(event) {
        const profilePanel = document.getElementById('profilePanel');
        const profileLink = event.target.closest('.dropdown-item');

        if (!profileLink && !event.target.closest('.profile-panel') && profilePanel.classList.contains('show')) {
            profilePanel.classList.remove('show');
        }
    });
    // Update profile link click handler
    document.querySelector('.dropdown-item[href="#"]').addEventListener('click', function(e) {
        e.preventDefault();
        toggleProfilePanel();
    });
    // Image Upload Functionality
    function setupImageUpload(dropZoneId, inputId, previewId) {
        const dropZone = document.getElementById(dropZoneId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);
        // Handle click to upload
        dropZone.addEventListener('click', () => {
            input.click();
        });
        // Handle file input change
        input.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('drag-over');
        }

        function unhighlight(e) {
            dropZone.classList.remove('drag-over');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            if (files.length) {
                const file = files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                const allowedExts = ['jpg', 'jpeg', 'png'];
                const fileExt = file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(file.type) || !allowedExts.includes(fileExt)) {
                    showProfileToast('Only JPG, JPEG, PNG images allowed!', false);
                    input.value = '';
                    return;
                }
                // Pehle preview update karo
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
                // Ab image ko backend par upload karo
                var formData = new FormData();
                formData.append('profile_img', file);
                fetch('include/api/update-profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            // Nayi image preview update karo (agar zarurat ho)
                            preview.src = '../' + res.profile_img;
                        } else {
                            showProfileToast(res.message || 'Image update failed', false);
                        }
                    });
            }
        }
    }
    // Initialize upload zones
    document.addEventListener('DOMContentLoaded', function() {
        setupImageUpload('profileImageDrop', 'profilePicture', 'profilePreview');
        
        // Initialize date and time display
        updateDateTime();
        setInterval(updateDateTime, 1000); // Update every second
    });
    
    // Helper function to create full name
    function createFullName(firstName, middleName, lastName) {
        return (firstName + ' ' + (middleName || '') + ' ' + lastName).replace(/\s+/g, ' ').trim();
    }

    // Function to update date and time
    function updateDateTime() {
        const now = new Date();
        
        // Format date
        const dateOptions = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const formattedDate = now.toLocaleDateString('en-US', dateOptions);
        
        // Format time
        const timeOptions = { 
            hour: '2-digit', 
            minute: '2-digit', 
            hour12: true 
        };
        const formattedTime = now.toLocaleTimeString('en-US', timeOptions);
        
        // Update the display
        const dateElement = document.getElementById('currentDate');
        const timeElement = document.getElementById('currentTime');
        
        if (dateElement) {
            dateElement.textContent = formattedDate;
        }
        if (timeElement) {
            timeElement.textContent = formattedTime;
        }
    }
</script>