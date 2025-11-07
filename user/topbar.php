<?php
// session_start(); // Removed to avoid duplicate session warnings
require_once dirname(__DIR__) . '/config.php';
$emp_id = $_SESSION['emp_id'] ?? null;
$employees = null;
if ($emp_id) {
    $sql = "SELECT e.*, e.designation as position, d.dept_name, s.shift_name, s.start_time as shift_start_time, s.end_time as shift_end_time FROM employees e LEFT JOIN departments d ON e.department_id = d.dept_id LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.emp_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id]);
    if ($stmt->rowCount() === 1) {
        $employees = $stmt->fetch();
    }
}
$profileImg = $employees['profile_img'] ?? '';
$profileImgPath = '';
if (!$profileImg) {
    $imgSrc = "../assets/images/default-avatar.jpg";
} else if (filter_var($profileImg, FILTER_VALIDATE_URL)) {
    $imgSrc = $profileImg;
} else {
    $profileImgPath = dirname(__DIR__) . '/assets/images/profile/' . basename($profileImg);
    if (!file_exists($profileImgPath) || !is_file($profileImgPath)) {
        $imgSrc = "../assets/images/default-avatar.jpg";
    } else {
        $imgSrc = "../assets/images/profile/" . basename($profileImg);
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
        <!-- Working Hours Display -->
        <div class="working-hours-display" id="workingHoursDisplay" style="display: none;">
            <div class="working-hours-info">
                <i class="fas fa-clock"></i>
                <span id="workingHoursText">00:00:00</span>
            </div>
        </div>
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
        <!-- Attendance Actions Dropdown -->
        <div class="attendance-dropdown">
            <button class="attendance-dropdown-btn">
                <i class="fas fa-clock"></i>
                Attendance Actions
            </button>
            <div class="attendance-dropdown-content">
                <a href="#" id="checkInBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Check In
                </a>
                <a href="#" id="checkOutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                    Check Out
                </a>
            </div>
        </div>
        <div class="user-profile" onclick="toggleDropdown()">
            <div class="user-avatar">
                <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
            </div>
            <span class="user-name"><?php echo $employees ? htmlspecialchars(trim(($employees['first_name'] ?? '') . ' ' . ($employees['middle_name'] ?? '') . ' ' . ($employees['last_name'] ?? ''))) : 'User'; ?></span>
            <i class="fas fa-chevron-down"></i>
            <div class="dropdown-menu" id="userDropdown">
                <a href="#" class="dropdown-item" onclick="toggleProfilePanel()">
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
                <img src="<?php echo $imgSrc; ?>" alt="User Avatar">
            </div>
            <div class="profile-info">
                <h3><?php echo $employees ? htmlspecialchars(trim(($employees['first_name'] ?? '') . ' ' . ($employees['middle_name'] ?? '') . ' ' . ($employees['last_name'] ?? ''))) : 'Full Name'; ?></h3>
                <button class="btn btn-edit-profile mt-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-user-edit me-2"></i>
                    Edit Profile
                </button>
                <button class="btn btn-edit-profile mt-2" data-bs-toggle="modal" data-bs-target="#ViewOwnProfileModal">
                    <i class="fas fa-user-edit me-2"></i>
                    View Profile
                </button>
            </div>
        </div>
    </div>
    <div class="profile-content">
        <div class="profile-section">
            <h4>Personal Information</h4>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <span><?php echo $employees ? htmlspecialchars($employees['email']) : 'Email'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-phone"></i>
                <span><?php echo $employees ? htmlspecialchars($employees['phone']) : 'Phone'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php
                        if ($employees && !empty($employees['address'])) {
                            $words = explode(' ', $employees['address']);
                            $limited = implode(' ', array_slice($words, 0, 4));
                            echo htmlspecialchars($limited . (count($words) > 4 ? '...' : ''));
                        } else {
                            echo 'Address';
                        }
                        ?></span>
            </div>
        </div>
        <div class="profile-section">
            <h4>Work Information</h4>
            <div class="info-item">
                <i class="fas fa-id-card"></i>
                <span>Emp Id: <?php echo $employees ? htmlspecialchars($employees['emp_id']) : 'Employee ID'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-user-tie"></i>
                <span>Job Title: <?php echo $employees ? htmlspecialchars($employees['position']) : 'Position'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-building"></i>
                <span>Department: <?php echo $employees && !empty($employees['dept_name']) ? htmlspecialchars($employees['dept_name']) : 'N/A'; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Joined: <?php echo $employees ? htmlspecialchars(date('M d, Y', strtotime($employees['joining_date']))) : 'Join Date'; ?></span>
            </div>
            <?php
            // Shift info formatting
            $shiftInfo = '-';
            if ($employees && !empty($employees['shift_name'])) {
                function formatTime12($t)
                {
                    if (!$t) return '';
                    list($h, $m, $s) = explode(':', $t);
                    $h = intval($h);
                    $ampm = $h >= 12 ? 'pm' : 'am';
                    $h = $h % 12;
                    if ($h == 0) $h = 12;
                    return $h . ':' . $m . ' ' . $ampm;
                }
                $start = formatTime12($employees['shift_start_time']);
                $end = formatTime12($employees['shift_end_time']);
                $shiftInfo = htmlspecialchars($employees['shift_name']) . " ($start - $end)";
            }
            ?>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>Shift: <?php echo $shiftInfo; ?></span>
            </div>
        </div>
    </div>
</div>
<!-- View Own Profile Modal -->
<div class="modal fade employee-profile-modal" id="ViewOwnProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="profile-cover">
                    <img src="../assets/images/LOGO.png" alt="Cover">
                </div>
                <button type="button" class="btn-close btn-close-white modal-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Profile Image Section -->
                <div class="profile-image-wrapper">
                    <div class="profile-image-container">
                        <img id="viewEmployeeProfileImg"
                            src="../assets/images/default-avatar.jpg"
                            class="profile-image" alt="User Avatar">
                    </div>
                </div>
                <div class="text-center mb-4">
                    <h4 class="mb-1 fw-bold"><?php echo $employees ? htmlspecialchars(trim(($employees['first_name'] ?? '') . ' ' . ($employees['middle_name'] ?? '') . ' ' . ($employees['last_name'] ?? ''))) : 'User'; ?></h4>
                    <p class="text-muted mb-2" id="viewEmployeePosition"></p>
                </div>
                <!-- Information Sections -->
                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Personal Information</h5>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user-friends info-icon"></i> Gender</label>
                                        <p class="info-value" id="viewEmployeeGender"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-alt info-icon"></i> Date of Birth</label>
                                        <p class="info-value" id="viewEmployeeDOB"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-id-card info-icon"></i> ID card Number</label>
                                        <p class="info-value" id="viewEmployeeCNIC"></p>
                                    </div>
                                </div>
                                <!-- <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-praying-hands info-icon"></i> Religion</label>
                                        <p class="info-value" id="viewEmployeeReligion"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-heart info-icon"></i> Marital Status</label>
                                        <p class="info-value" id="viewEmployeeMaritalStatus"></p>
                                    </div>
                                </div> -->
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-phone info-icon"></i> Phone</label>
                                        <p class="info-value" id="viewEmployeePhone"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-envelope info-icon"></i> Email</label>
                                        <p class="info-value" id="viewEmployeeEmail"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-map-marker-alt info-icon"></i> Address</label>
                                        <p class="info-value" id="viewEmployeeAddress"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-phone-square-alt info-icon"></i> Emergency Contact</label>
                                        <p class="info-value" id="viewEmployeeEmergencyContact"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-phone info-icon"></i> Emergency Contact Relation</label>
                                        <p class="info-value" id="viewEmployeeEmergencyRelation"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Job Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Job Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> Department</label>
                                        <p class="info-value" id="viewEmployeeDepartment"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-sitemap info-icon"></i> Sub Department</label>
                                        <p class="info-value" id="viewEmployeeSubDepartment"></p>
                                    </div>
                                </div>
                                <!-- <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user-tie info-icon"></i> Reporting Manager</label>
                                        <p class="info-value" id="viewEmployeeLineManager"></p>
                                    </div>
                                </div> -->
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-check info-icon"></i> Joining Date</label>
                                        <p class="info-value" id="viewEmployeeJoiningDate"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-clock info-icon"></i> Timing</label>
                                        <p class="info-value" id="viewEmployeeTiming"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-briefcase info-icon"></i> Job Type</label>
                                        <p class="info-value" id="viewEmployeeJobType"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-money-bill-wave info-icon"></i> Salary</label>
                                        <p class="info-value" id="viewEmployeeSalary"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Bank Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Bank Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-university info-icon"></i> Bank Name</label>
                                        <p class="info-value" id="viewEmployeeBankName"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-credit-card info-icon"></i> Bank Type</label>
                                        <p class="info-value" id="viewEmployeeAccountType"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user info-icon"></i> Account Title</label>
                                        <p class="info-value" id="viewEmployeeAccountTitle"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-code-branch info-icon"></i> Bank Branch</label>
                                        <p class="info-value" id="viewEmployeeBankBranch"></p>
                                    </div>
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-hashtag info-icon"></i> Account Number</label>
                                        <p class="info-value" id="viewEmployeeAccountNumber"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Education Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Education Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-graduation-cap info-icon"></i> Qualification
                                        </label>
                                        <p class="info-value" id="viewEmployeeQualificationInstitution"></p>
                                    </div>
                                </div>
                                <!-- <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-day info-icon"></i> Education From Date</label>
                                        <p class="info-value" id="viewEmployeeEducationFromDate"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-day info-icon"></i> Education To Date</label>
                                        <p class="info-value" id="viewEmployeeEducationToDate"></p>
                                    </div>
                                </div> -->
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-book-open info-icon"></i> Degree / Certification</label>
                                        <p class="info-value" id="viewEmployeeEducationPercentage"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> Professional Expertise</label>
                                        <p class="info-value" id="viewEmployeeSpecialization"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> College / University</label>
                                        <p class="info-value" id="viewEmployeeMaritalStatus"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Experience Information -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Experience Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-building info-icon"></i> Last Employer</label>
                                        <p class="info-value" id="viewEmployeeLastOrganization"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-user-tie info-icon"></i> Last Job Title</label>
                                        <p class="info-value" id="viewEmployeeLastDesignation"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-check info-icon"></i> Experience From
                                            Date</label>
                                        <p class="info-value" id="viewEmployeeExperienceFromDate"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <div class="info-card"><label class="info-label"><i
                                                class="fas fa-calendar-check info-icon"></i> Experience To Date</label>
                                        <p class="info-value" id="viewEmployeeExperienceToDate"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Leave History -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Leave History</h5>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-card">
                                        <label class="info-label" id="approvedLeavesLabel"><i class="fas fa-calendar-times info-icon"></i> Approved Leaves (0 days)</label>
                                        <div id="viewEmployeeLeaveHistory" class="mt-2">
                                            <div class="text-center text-muted py-3">No leave data available</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Documents Section -->
                    <div class="col-12">
                        <div class="info-section">
                            <h5 class="section-title"><span class="section-title-bar"></span>Uploaded Documents</h5>
                            <div id="ownDocumentsContainer">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading documents...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                        <img src="<?php echo $imgSrc; ?>" class="preview-image" alt="Profile Picture" id="profilePreview">
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
                                    <input type="text" class="form-control" id="editFirstName" value="<?php echo $employees ? htmlspecialchars($employees['first_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="editMiddleName" value="<?php echo $employees ? htmlspecialchars($employees['middle_name']) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editLastName" value="<?php echo $employees ? htmlspecialchars($employees['last_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="editPhone" maxlength="11" pattern="[0-9]{11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="<?php echo $employees ? htmlspecialchars($employees['phone']) : 'Phone'; ?>" required>
                                    <small class="text-muted">Only numbers allowed (11 digits)</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" id="editAddress" value="<?php echo $employees ? htmlspecialchars($employees['address']) : 'Address'; ?>" required>
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
<!-- Toast Notification for Topbar -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="topbarToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="topbarToastMsg">
                Check in successful!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script src="include/js/topbar.js"></script>
<script>
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
    // Toast function for topbar
    function showTopbarToast(msg, type) {
        const toastEl = document.getElementById('topbarToast');
        const toastMsg = document.getElementById('topbarToastMsg');
        
        // Check if elements exist
        if (!toastEl || !toastMsg) {
            console.log('Topbar toast elements not found, using fallback');
            return false;
        }
        
        try {
            toastMsg.textContent = msg;
            toastEl.classList.remove('text-bg-success', 'text-bg-danger');
            toastEl.classList.add(type === 'danger' ? 'text-bg-danger' : 'text-bg-success');
            const toast = new bootstrap.Toast(toastEl, {
                delay: 2000
            });
            toast.show();
            return true;
        } catch (error) {
            console.log('Topbar toast failed:', error);
            return false;
        }
    }
    // Working Hours Timer
    let workingHoursTimer = null;
    let checkInTime = null;
    // Start working hours timer
    function startWorkingHoursTimer(checkInDateTime) {
        checkInTime = new Date(checkInDateTime);
        const display = document.getElementById('workingHoursDisplay');
        const text = document.getElementById('workingHoursText');
        display.style.display = 'flex';
        workingHoursTimer = setInterval(function() {
            const now = new Date();
            const diff = now - checkInTime;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            const timeString =
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
            text.textContent = timeString;
        }, 1000);
    }
    // Stop working hours timer
    function stopWorkingHoursTimer() {
        if (workingHoursTimer) {
            clearInterval(workingHoursTimer);
            workingHoursTimer = null;
        }
        const display = document.getElementById('workingHoursDisplay');
        display.style.display = 'none';
        checkInTime = null;
    }
    // Check current attendance status and start timer if needed
    function checkAndStartWorkingHoursTimer() {
        $.ajax({
            url: 'include/api/userattendance.php?action=current_status',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.is_checked_in) {
                    startWorkingHoursTimer(response.data.check_in_time);
                } else {
                    stopWorkingHoursTimer();
                }
            },
            error: function() {
                console.log('Error checking current attendance status');
            }
        });
    }
    // Date Time Display Functions
    function updateDateTime() {
        const now = new Date();
        // Update date
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const dateString = now.toLocaleDateString('en-US', dateOptions);
        document.getElementById('currentDate').textContent = dateString;
        // Update time
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        const timeString = now.toLocaleTimeString('en-US', timeOptions);
        document.getElementById('currentTime').textContent = timeString;
    }
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
    // Helper function to create full name
    function createFullName(firstName, middleName, lastName) {
        return (firstName + ' ' + (middleName || '') + ' ' + lastName).replace(/\s+/g, ' ').trim();
    }
    // Load own profile data
    function loadOwnProfile() {
        $.ajax({
            url: 'include/api/view-own-profile.php',
            method: 'GET',
            success: function(response) {
                console.log('Full API Response:', response);
                if (response.success && response.data) {
                    const emp = response.data;
                    console.log('Debug Info:', response.debug);
                    // Set profile image
                    let profileImgSrc = '../assets/images/default-avatar.jpg';
                    if (emp.profile_img) {
                        if (emp.profile_img.startsWith('uploads/')) {
                            profileImgSrc = '../' + emp.profile_img;
                        } else if (emp.profile_img.startsWith('../')) {
                            profileImgSrc = emp.profile_img;
                        } else {
                            profileImgSrc = '../' + emp.profile_img;
                        }
                    }
                    $('#viewEmployeeProfileImg').attr('src', profileImgSrc);
                    // Set basic info
                    $('#viewEmployeeFullName').text(createFullName(emp.first_name || '', emp.middle_name || '', emp.last_name || ''));
                    $('#viewEmployeePosition').text(emp.position || '');
                    // Personal Information
                    $('#viewEmployeeGender').text(emp.gender || '');
                    $('#viewEmployeeDOB').text(formatDate(emp.date_of_birth) || '');
                    $('#viewEmployeeCNIC').text(emp.cnic || '');
                    $('#viewEmployeePhone').text(emp.phone || '');
                    $('#viewEmployeeEmail').text(emp.email || '');
                    $('#viewEmployeeAddress').text(emp.address || '');
                    $('#viewEmployeeEmergencyContact').text(emp.emergency_contact || '');
                    $('#viewEmployeeEmergencyRelation').text(emp.emergency_relation || '');
                    // Job Information
                    $('#viewEmployeeDepartment').text(emp.department || '');
                    $('#viewEmployeeSubDepartment').text(emp.sub_department || '');
                    $('#viewEmployeeLineManager').text(emp.line_manager || '');
                    $('#viewEmployeeJoiningDate').text(formatDate(emp.joining_date) || '');
                    $('#viewEmployeeTiming').text(emp.timing || '');
                    $('#viewEmployeeJobType').text(emp.job_type || '');
                    $('#viewEmployeeSalary').text(emp.salary || '');
                    // Bank Information
                    $('#viewEmployeeBankName').text(emp.bank_name || '');
                    $('#viewEmployeeAccountType').text(emp.account_type || '');
                    $('#viewEmployeeAccountTitle').text(emp.account_title || '');
                    $('#viewEmployeeBankBranch').text(emp.bank_branch || '');
                    $('#viewEmployeeAccountNumber').text(emp.account_number || '');
                    // Education Information
                    $('#viewEmployeeQualificationInstitution').text(emp.qualification_institution || '');
                    $('#viewEmployeeSpecialization').text(emp.specialization || '');
                    $('#viewEmployeeEducationPercentage').text(emp.education_percentage || '');
                    $('#viewEmployeeMaritalStatus').text(emp.marital_status || '');
                    // Experience Information
                    $('#viewEmployeeLastOrganization').text(emp.last_organization || '');
                    $('#viewEmployeeLastDesignation').text(emp.last_designation || '');
                    $('#viewEmployeeExperienceFromDate').text(formatDate(emp.experience_from_date) || '');
                    $('#viewEmployeeExperienceToDate').text(formatDate(emp.experience_to_date) || '');
                    // Documents Information
                    displayOwnDocuments(emp.cv_attachment, emp.id_card_attachment, emp.other_documents);
                    // Load leave history
                    loadEmployeeLeaveHistory(emp.emp_id);
                }
            },
            error: function() {
                console.log('Error loading profile data');
            }
        });
    }
    // Display own documents
    function displayOwnDocuments(cvAttachment, idCardAttachment, otherDocuments) {
        let documentsHtml = '';
    
        console.log('CV Attachment:', cvAttachment);
        console.log('ID Card Attachment:', idCardAttachment);
        console.log('Other Documents:', otherDocuments);
        
        // CV Document
        if (cvAttachment) {
            // Check if path already contains uploads folder
            let cvPath;
            if (cvAttachment.startsWith('uploads/joining_documents/')) {
                cvPath = `../${cvAttachment}`;
            } else if (cvAttachment.startsWith('uploads/')) {
                cvPath = `../${cvAttachment}`;
            } else {
                cvPath = `../uploads/joining_documents/${cvAttachment}`;
            }
            console.log('CV Path:', cvPath);
            documentsHtml += `
                <div class="document-item mb-2">
                    <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            <span class="document-name">Resume</span>
                        </div>
                        <a href="${cvPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            `;
        }
        
        // ID Card Document
        if (idCardAttachment) {
            // Check if path already contains uploads folder
            let idCardPath;
            if (idCardAttachment.startsWith('uploads/joining_documents/')) {
                idCardPath = `../${idCardAttachment}`;
            } else if (idCardAttachment.startsWith('uploads/')) {
                idCardPath = `../${idCardAttachment}`;
            } else {
                idCardPath = `../uploads/joining_documents/${idCardAttachment}`;
            }
            console.log('ID Card Path:', idCardPath);
            
            // Get file extension for icon
            const fileExtension = idCardAttachment.split('.').pop().toLowerCase();
            let iconClass = 'fas fa-file';
            if (fileExtension === 'pdf') {
                iconClass = 'fas fa-file-pdf text-danger';
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                iconClass = 'fas fa-file-image text-success';
            }
            
            documentsHtml += `
                <div class="document-item mb-2">
                    <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                        <div class="d-flex align-items-center">
                            <i class="${iconClass} me-2"></i>
                            <span class="document-name">ID Card</span>
                        </div>
                        <a href="${idCardPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            `;
        }
        if (otherDocuments) {
            try {
                const documents = JSON.parse(otherDocuments);
                if (Array.isArray(documents)) {
                    documents.forEach((doc, index) => {
                        const fileExtension = doc.split('.').pop().toLowerCase();
                        let iconClass = 'fas fa-file';
                        if (fileExtension === 'pdf') iconClass = 'fas fa-file-pdf text-danger';
                        else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) iconClass = 'fas fa-file-image text-success';
                        else if (['doc', 'docx'].includes(fileExtension)) iconClass = 'fas fa-file-word text-primary';
                        // Check if path already contains uploads folder
                        let docPath;
                        if (doc.startsWith('uploads/joining_documents/')) {
                            docPath = `../${doc}`;
                        } else if (doc.startsWith('uploads/')) {
                            docPath = `../${doc}`;
                        } else {
                            docPath = `../uploads/joining_documents/${doc}`;
                        }
                        console.log('Document Path:', docPath);
                        documentsHtml += `
                            <div class="document-item mb-2">
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="${iconClass} me-2"></i>
                                        <span class="document-name">Document ${index + 1}</span>
                                    </div>
                                    <a href="${docPath}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (e) {
                console.log('Error parsing other documents:', e);
            }
        }
        if (!cvAttachment && !idCardAttachment && !otherDocuments) {
            documentsHtml = `
                <div class="text-center text-muted py-3">
                    <i class="fas fa-file-slash fa-2x mb-2"></i>
                    <p class="mb-0">No documents uploaded</p>
                </div>
            `;
        }
        $('#ownDocumentsContainer').html(documentsHtml);
    }
    // Initialize date time display and working hours timer on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Update date time immediately
        updateDateTime();
        // Update date time every second
        setInterval(updateDateTime, 1000);
        // Check and start working hours timer
        checkAndStartWorkingHoursTimer();
        // Check working hours status every 30 seconds (for overnight shifts)
        setInterval(checkAndStartWorkingHoursTimer, 30000);
        // Load profile data when View Profile modal is opened
        $('#ViewOwnProfileModal').on('show.bs.modal', function() {
            loadOwnProfile();
        });
    });
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
                console.error('Error loading leave history:', error);
                $('#viewEmployeeLeaveHistory').html('<div class="text-center text-danger py-3">Error loading leave history</div>');
            }
        });
    }
</script>